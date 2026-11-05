<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\Shipment;
use App\Models\TrackingEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Location::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($country = $request->query('country')) {
            $query->where('country', $country);
        }

        return response()->json($query->orderBy('name')->paginate(15));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'country' => ['required', 'string', 'max:80'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        // Nama + negara tidak boleh kembar
        if (Location::where('name', $data['name'])->where('country', $data['country'])->exists()) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['name' => ['This location already exists in that country.']],
            ], 422);
        }

        $location = Location::create($data);

        return response()->json($location, 201);
    }

    public function show(Location $location): JsonResponse
    {
        return response()->json($location);
    }

    public function update(Request $request, Location $location): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'country' => ['sometimes', 'required', 'string', 'max:80'],
            'latitude' => ['sometimes', 'required', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'required', 'numeric', 'between:-180,180'],
        ]);

        $name = $data['name'] ?? $location->name;
        $country = $data['country'] ?? $location->country;

        $duplicate = Location::where('name', $name)
            ->where('country', $country)
            ->where('id', '!=', $location->id)
            ->exists();

        if ($duplicate) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => ['name' => ['This location already exists in that country.']],
            ], 422);
        }

        $location->update($data);

        return response()->json($location);
    }

    public function destroy(Location $location): JsonResponse
    {
        // Lokasi yang masih dipakai shipment atau tracking event tidak boleh dihapus.
        $usedByShipment = Shipment::where('origin_id', $location->id)
            ->orWhere('destination_id', $location->id)
            ->exists();

        $usedByEvent = TrackingEvent::where('location_id', $location->id)->exists();

        if ($usedByShipment || $usedByEvent) {
            return response()->json([
                'message' => 'Location is in use and cannot be deleted.',
            ], 409);
        }

        $location->delete();

        return response()->json(['message' => 'Location deleted.']);
    }
}