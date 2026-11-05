<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ShipmentController extends Controller
{
    private const RELATIONS = [
        'customer:id,name,email',
        'carrier:id,name,code',
        'origin:id,name,country,latitude,longitude',
        'destination:id,name,country,latitude,longitude',
    ];

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Shipment::with(self::RELATIONS);

        // Customer hanya boleh melihat shipment miliknya sendiri
        if ($user->role === User::ROLE_CUSTOMER) {
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

        return response()->json($query->latest()->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', User::ROLE_CUSTOMER),
            ],
            'carrier_id' => ['required', Rule::exists('carriers', 'id')->where('is_active', true)],
            'origin_id' => ['required', 'exists:locations,id'],
            'destination_id' => ['required', 'exists:locations,id', 'different:origin_id'],
            'cargo_type' => ['required', 'string', 'max:60'],
            'expected_departure' => ['required', 'date'],
            'expected_arrival' => ['required', 'date', 'after:expected_departure'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.weight_kg' => ['required', 'numeric', 'gt:0'],
            'items.*.volume_cbm' => ['nullable', 'numeric', 'gt:0'],
        ]);

        $shipment = DB::transaction(function () use ($data) {
            $items = $data['items'];
            unset($data['items']);

            $data['shipment_number'] = $this->generateNumber();

            $shipment = Shipment::create($data);
            $shipment->items()->createMany($items);

            return $shipment;
        });

        $shipment->refresh()->load([...self::RELATIONS, 'items']);

        return response()->json($shipment, 201);
    }

    public function show(Request $request, Shipment $shipment): JsonResponse
    {
        $user = $request->user();

        if ($user->role === User::ROLE_CUSTOMER && $shipment->customer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $shipment->load([...self::RELATIONS, 'items', 'trackingEvents', 'exceptions']);

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

        $shipment->update($data);

        return response()->json($shipment->load(self::RELATIONS));
    }

    public function destroy(Shipment $shipment): JsonResponse
    {
        if ($shipment->status !== Shipment::CREATED) {
            return response()->json([
                'message' => 'Only CREATED shipments can be deleted.',
            ], 409);
        }

        $shipment->delete();

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
