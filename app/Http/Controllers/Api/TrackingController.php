<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TrackingController extends Controller
{
    // Aturan transisi status: hanya boleh maju satu langkah.
    private const TRANSITIONS = [
        Shipment::CREATED => [Shipment::BOOKED],
        Shipment::BOOKED => [Shipment::PICKED_UP],
        Shipment::PICKED_UP => [Shipment::IN_TRANSIT],
        Shipment::IN_TRANSIT => [Shipment::DELIVERED],
        Shipment::DELIVERED => [],
    ];

    public function index(Request $request, Shipment $shipment): JsonResponse
    {
        $user = $request->user();

        if ($user->role === User::ROLE_CUSTOMER && $shipment->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $events = $shipment->trackingEvents()
            ->with('location:id,name,country')
            ->get();

        return response()->json($events);
    }

    public function updateStatus(Request $request, Shipment $shipment): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::TRANSITIONS))],
            'location_id' => ['nullable', 'exists:locations,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'occurred_at' => ['nullable', 'date', 'before_or_equal:now'],
        ]);

        $from = $shipment->status;
        $to = $data['status'];

        if (! in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            return response()->json([
                'message' => "Invalid status transition from {$from} to {$to}.",
            ], 409);
        }

        $occurredAt = isset($data['occurred_at']) ? Carbon::parse($data['occurred_at']) : now();

        if ($error = $this->chronologyError($shipment, $occurredAt)) {
            return $error;
        }

        DB::transaction(function () use ($request, $shipment, $data, $from, $to, $occurredAt) {
            $shipment->status = $to; // status sengaja tidak fillable
            $shipment->save();

            $this->createEvent($shipment, $request->user(), [
                'event_type' => $to,
                'location_id' => $data['location_id'] ?? null,
                'description' => $data['description'] ?? "Status changed from {$from} to {$to}",
                'occurred_at' => $occurredAt,
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'shipment.status_changed',
                'entity_type' => 'shipment',
                'entity_id' => $shipment->id,
                'changes' => ['from' => $from, 'to' => $to],
            ]);
        });

        return response()->json($shipment->fresh()->load('trackingEvents'));
    }

    public function store(Request $request, Shipment $shipment): JsonResponse
    {
        if ($shipment->status === Shipment::DELIVERED) {
            return response()->json([
                'message' => 'Cannot add events to a delivered shipment.',
            ], 409);
        }

        $data = $request->validate([
            'event_type' => ['required', 'string', 'max:30'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => ['nullable', 'string', 'max:255'],
            'occurred_at' => ['required', 'date', 'before_or_equal:now'],
            'is_exception' => ['sometimes', 'boolean'],
        ]);

        $occurredAt = Carbon::parse($data['occurred_at']);

        if ($error = $this->chronologyError($shipment, $occurredAt)) {
            return $error;
        }

        $event = $this->createEvent($shipment, $request->user(), $data + ['occurred_at' => $occurredAt]);

        return response()->json($event->load('location:id,name,country'), 201);
    }

    // Event baru tidak boleh lebih tua dari event terakhir.
    private function chronologyError(Shipment $shipment, Carbon $occurredAt): ?JsonResponse
    {
        $last = $shipment->trackingEvents()->max('occurred_at');

        if ($last && $occurredAt->lt(Carbon::parse($last))) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['occurred_at' => ['Event time cannot be earlier than the latest event.']],
            ], 422);
        }

        return null;
    }

    private function createEvent(Shipment $shipment, User $user, array $data): TrackingEvent
    {
        // Kalau lokasi dipilih dan koordinat kosong, pakai koordinat lokasi.
        if (! empty($data['location_id']) && empty($data['latitude'])) {
            $location = Location::find($data['location_id']);
            $data['latitude'] = $location->latitude;
            $data['longitude'] = $location->longitude;
        }

        return $shipment->trackingEvents()->getRelated()->create($data + [
            'shipment_id' => $shipment->id,
            'created_by' => $user->id,
        ]);
    }
}