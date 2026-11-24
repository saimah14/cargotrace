<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShipmentController extends Controller
{
    private const RELATIONS = [
        'customer:id,name,email',
        'carrier:id,name,code',
        'origin:id,name,country,latitude,longitude',
        'destination:id,name,country,latitude,longitude',
        'items:id,shipment_id,description,quantity,weight_kg,volume_cbm',
        'latestTrackingEvent',
        'latestTrackingEvent.location:id,name,country,latitude,longitude',
    ];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Shipment::with(self::RELATIONS);

        // Customer hanya boleh melihat shipment miliknya sendiri
        if ($user->hasRole(User::ROLE_CUSTOMER)) {
            $query->where('customer_id', $user->id);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($carrierId = $request->query('carrier_id')) {
            $query->where('carrier_id', $carrierId);
        }

        if ($request->has('is_delayed')) {
            $query->where('is_delayed', $request->boolean('is_delayed'));
        }

        if ($search = $request->query('search')) {
            $query->where('shipment_number', 'like', "%{$search}%");
        }

        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

        return response()->json($query->latest()->paginate($perPage));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => [
                'required',
                Rule::exists('users', 'id')->whereIn('role', [User::ROLE_CUSTOMER, 'Customer']),
            ],
            'carrier_id' => ['required', Rule::exists('carriers', 'id')->where('is_active', true)],
            'origin_id' => ['nullable', 'required_without:origin_name', 'exists:locations,id'],
            'origin_name' => ['nullable', 'required_without:origin_id', 'string', 'max:150'],
            'origin_country' => ['required_with:origin_name', 'string', 'max:80'],
            'origin_latitude' => ['required_with:origin_name', 'numeric', 'between:-90,90'],
            'origin_longitude' => ['required_with:origin_name', 'numeric', 'between:-180,180'],
            'destination_id' => ['nullable', 'required_without:destination_name', 'exists:locations,id'],
            'destination_name' => ['nullable', 'required_without:destination_id', 'string', 'max:150'],
            'destination_country' => ['required_with:destination_name', 'string', 'max:80'],
            'destination_latitude' => ['required_with:destination_name', 'numeric', 'between:-90,90'],
            'destination_longitude' => ['required_with:destination_name', 'numeric', 'between:-180,180'],
            'cargo_type' => ['required', 'string', 'max:60'],
            'transport_mode' => ['nullable', Rule::in(['SEA', 'AIR'])],
            'sender_name' => ['nullable', 'string', 'max:100'],
            'sender_phone' => ['nullable', 'string', 'max:30'],
            'origin_address' => ['nullable', 'string', 'max:255'],
            'receiver_name' => ['nullable', 'string', 'max:100'],
            'receiver_phone' => ['nullable', 'string', 'max:30'],
            'destination_address' => ['nullable', 'string', 'max:255'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after:expected_departure'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.weight_kg' => ['required', 'numeric', 'gt:0'],
            'items.*.volume_cbm' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $shipment = DB::transaction(function () use ($data, $request) {
            foreach (['origin', 'destination'] as $point) {
                if (isset($data[$point.'_name'])) {
                    $location = Location::firstOrCreate(
                        [
                            'name' => $data[$point.'_name'],
                            'country' => $data[$point.'_country'],
                        ],
                        [
                            'latitude' => $data[$point.'_latitude'],
                            'longitude' => $data[$point.'_longitude'],
                        ]
                    );
                    $data[$point.'_id'] = $location->id;
                }

                unset(
                    $data[$point.'_name'],
                    $data[$point.'_country'],
                    $data[$point.'_latitude'],
                    $data[$point.'_longitude']
                );
            }

            if ((int) $data['origin_id'] === (int) $data['destination_id']) {
                throw ValidationException::withMessages([
                    'destination_id' => ['Origin and destination must be different locations.'],
                ]);
            }

            $items = $data['items'];
            unset($data['items']);

            $data['shipment_number'] = $this->generateNumber();

            $shipment = Shipment::create($data);
            $shipment->items()->createMany($items);
            $shipment->trackingEvents()->create([
                'created_by' => $request->user()->id,
                'event_type' => 'CREATED',
                'location_id' => $shipment->origin_id,
                'occurred_at' => now(),
                'description' => 'Shipment dibuat dan menunggu proses operasional.',
            ]);

            UserNotification::create([
                'user_id' => $shipment->customer_id,
                'shipment_id' => $shipment->id,
                'message' => "Shipment {$shipment->shipment_number} sudah dibuat dan menunggu proses operasional.",
            ]);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'shipment.created',
                'entity_type' => 'shipment',
                'entity_id' => $shipment->id,
                'changes' => [
                    'shipment_number' => $shipment->shipment_number,
                    'customer_id' => $shipment->customer_id,
                    'cargo_type' => $shipment->cargo_type,
                    'status' => $shipment->status,
                ],
            ]);

            return $shipment;
        });

        $shipment->refresh()->load(self::RELATIONS);

        return response()->json($shipment, 201);
    }

    public function show(Request $request, Shipment $shipment): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole(User::ROLE_CUSTOMER) && $shipment->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $shipment->load([...self::RELATIONS, 'trackingEvents', 'exceptions']);

        return response()->json($shipment);
    }

    public function update(Request $request, Shipment $shipment): JsonResponse
    {
        // Shipment yang sudah berjalan tidak boleh diubah datanya
        if (! in_array($shipment->status, [Shipment::CREATED, Shipment::BOOKED], true)) {
            return response()->json([
                'message' => 'Only CREATED or BOOKED shipments can be edited.',
            ], 409);
        }

        $data = $request->validate([
            'carrier_id' => ['sometimes', Rule::exists('carriers', 'id')->where('is_active', true)],
            'origin_id' => ['sometimes', 'exists:locations,id'],
            'destination_id' => ['sometimes', 'exists:locations,id'],
            'cargo_type' => ['sometimes', 'string', 'max:60'],
            'expected_departure' => ['sometimes', 'date'],
            'expected_arrival' => ['sometimes', 'date'],
        ]);

        $origin = $data['origin_id'] ?? $shipment->origin_id;
        $destination = $data['destination_id'] ?? $shipment->destination_id;
        $departure = Carbon::parse($data['expected_departure'] ?? $shipment->expected_departure);
        $arrival = Carbon::parse($data['expected_arrival'] ?? $shipment->expected_arrival);

        if ($origin === $destination || $arrival->lte($departure)) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['shipment' => ['Origin and destination must differ, and arrival must be after departure.']],
            ], 422);
        }

        $before = $shipment->only(array_keys($data));
        DB::transaction(function () use ($request, $shipment, $data, $before) {
            $shipment->update($data);

            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'shipment.updated',
                'entity_type' => 'shipment',
                'entity_id' => $shipment->id,
                'changes' => [
                    'before' => $before,
                    'after' => $shipment->only(array_keys($data)),
                ],
            ]);
        });

        return response()->json($shipment->load(self::RELATIONS));
    }

    public function destroy(Request $request, Shipment $shipment): JsonResponse
    {
        if ($shipment->status !== Shipment::CREATED) {
            return response()->json([
                'message' => 'Only CREATED shipments can be deleted.',
            ], 409);
        }

        DB::transaction(function () use ($request, $shipment) {
            AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'shipment.deleted',
                'entity_type' => 'shipment',
                'entity_id' => $shipment->id,
                'changes' => ['shipment_number' => $shipment->shipment_number],
            ]);

            $shipment->delete();
        });

        return response()->json(['message' => 'Shipment deleted.']);
    }

    private function generateNumber(): string
    {
        do {
            $number = 'CT-'.now()->format('Ymd').'-'.random_int(10000, 99999);
        } while (Shipment::where('shipment_number', $number)->exists());

        return $number;
    }
}
