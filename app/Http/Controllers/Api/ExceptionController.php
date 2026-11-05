<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ExceptionController extends Controller
{
    // Parameter skenario Group A: delay jika ETA lewat > 12 jam dari jadwal.
    private const DELAY_THRESHOLD_HOURS = 12;

    // Transisi status exception
    private const TRANSITIONS = [
        'OPEN' => ['INVESTIGATING', 'RESOLVED'],
        'INVESTIGATING' => ['RESOLVED'],
        'RESOLVED' => [],
    ];

    public function index(Request $request, Shipment $shipment): JsonResponse
    {
        $user = $request->user();

        if ($user->role === User::ROLE_CUSTOMER && $shipment->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return response()->json($shipment->exceptions()->latest('detected_at')->get());
    }

    public function store(Request $request, Shipment $shipment): JsonResponse
    {
        if ($shipment->status === Shipment::DELIVERED) {
            return response()->json([
                'message' => 'Cannot add exceptions to a delivered shipment.',
            ], 409);
        }

        $data = $request->validate([
            'type' => ['required', Rule::in(ShipmentException::TYPES)],
            'severity' => ['required', Rule::in(ShipmentException::SEVERITIES)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'delay_hours' => ['nullable', 'integer', 'min:0', 'max:720'],
        ]);

        $exception = DB::transaction(function () use ($request, $shipment, $data) {
            $exception = $shipment->exceptions()->create([
                'type' => $data['type'],
                'severity' => $data['severity'],
                'status' => 'OPEN',
                'notes' => $data['notes'] ?? null,
                'detected_at' => now(),
            ]);

            // Catat juga di timeline tracking sebagai event exception
            $shipment->trackingEvents()->create([
                'created_by' => $request->user()->id,
                'event_type' => $data['type'],
                'occurred_at' => now(),
                'description' => $data['notes'] ?? "Exception: {$data['type']}",
                'is_exception' => true,
            ]);

            $delay = (int) ($data['delay_hours'] ?? 0);
            if ($delay > 0) {
                $this->applyDelay($shipment, $delay);
            }

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'exception.created',
                'entity_type' => 'shipment',
                'entity_id' => $shipment->id,
                'changes' => [
                    'type' => $data['type'],
                    'severity' => $data['severity'],
                    'delay_hours' => $delay,
                ],
            ]);

            return $exception;
        });

        return response()->json([
            'exception' => $exception,
            'shipment' => $shipment->fresh()->only([
                'id', 'shipment_number', 'status',
                'expected_arrival', 'estimated_arrival', 'is_delayed',
            ]),
        ], 201);
    }

    public function updateStatus(Request $request, ShipmentException $exception): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(ShipmentException::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $from = $exception->status;
        $to = $data['status'];

        if (! in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            return response()->json([
                'message' => "Invalid exception status transition from {$from} to {$to}.",
            ], 409);
        }

        DB::transaction(function () use ($request, $exception, $data, $from, $to) {
            $exception->status = $to;

            if ($to === 'RESOLVED') {
                $exception->resolved_at = now();
            }

            if (! empty($data['notes'])) {
                $exception->notes = trim($exception->notes."\n".$data['notes']);
            }

            $exception->save();

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'exception.status_changed',
                'entity_type' => 'shipment_exception',
                'entity_id' => $exception->id,
                'changes' => ['from' => $from, 'to' => $to],
            ]);
        });

        return response()->json($exception->fresh());
    }

    // Geser ETA, tandai delayed bila melewati batas, dan beri tahu customer.
    private function applyDelay(Shipment $shipment, int $hours): void
    {
        $base = $shipment->estimated_arrival ?? $shipment->expected_arrival;
        $estimated = $base->copy()->addHours($hours);

        $overHours = ($estimated->timestamp - $shipment->expected_arrival->timestamp) / 3600;
        $nowDelayed = $overHours > self::DELAY_THRESHOLD_HOURS;
        $wasDelayed = $shipment->is_delayed;

        $shipment->estimated_arrival = $estimated;
        $shipment->is_delayed = $nowDelayed;
        $shipment->save();

        // Notifikasi hanya saat berubah dari tidak delayed menjadi delayed
        if ($nowDelayed && ! $wasDelayed) {
            UserNotification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'message' => "Shipment {$shipment->shipment_number} is delayed. New ETA: {$estimated->toDateTimeString()}.",
            ]);
        }
    }
}