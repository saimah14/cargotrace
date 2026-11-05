<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Shipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CarrierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Carrier::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json($query->orderBy('name')->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:10', 'unique:carriers,code'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $carrier = Carrier::create($data);

        return response()->json($carrier, 201);
    }

    public function show(Carrier $carrier): JsonResponse
    {
        return response()->json($carrier);
    }

    public function update(Request $request, Carrier $carrier): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'code' => [
                'sometimes', 'required', 'string', 'max:10',
                Rule::unique('carriers', 'code')->ignore($carrier->id),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $carrier->update($data);

        return response()->json($carrier);
    }

    public function destroy(Carrier $carrier): JsonResponse
    {
        // Carrier yang masih dipakai shipment tidak boleh dihapus.
        if (Shipment::where('carrier_id', $carrier->id)->exists()) {
            return response()->json([
                'message' => 'Carrier is used by existing shipments and cannot be deleted.',
            ], 409);
        }

        $carrier->delete();

        return response()->json(['message' => 'Carrier deleted.']);
    }
}