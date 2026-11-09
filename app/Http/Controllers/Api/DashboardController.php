<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $statuses = [
            Shipment::CREATED,
            Shipment::BOOKED,
            Shipment::PICKED_UP,
            Shipment::IN_TRANSIT,
            Shipment::DELIVERED,
        ];

        $counts = Shipment::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $byStatus = [];
        foreach ($statuses as $status) {
            $byStatus[$status] = (int) ($counts[$status] ?? 0);
        }

        $openExceptions = ShipmentException::whereIn('status', ['OPEN', 'INVESTIGATING']);

        $exceptionsByType = (clone $openExceptions)
            ->select('type', DB::raw('COUNT(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type');

        $byCarrier = Shipment::join('carriers', 'carriers.id', '=', 'shipments.carrier_id')
            ->select('carriers.name', DB::raw('COUNT(*) as total'))
            ->groupBy('carriers.name')
            ->pluck('total', 'name');

        $recent = Shipment::with('carrier:id,name,code')
            ->latest()
            ->limit(5)
            ->get(['id', 'shipment_number', 'carrier_id', 'status', 'is_delayed', 'estimated_arrival']);

        return response()->json([
            'total_shipments' => array_sum($byStatus),
            'shipments_by_status' => $byStatus,
            'delayed_shipments' => Shipment::where('is_delayed', true)
                ->where('status', '!=', Shipment::DELIVERED)
                ->count(),
            'open_exceptions' => (clone $openExceptions)->count(),
            'open_exceptions_by_type' => $exceptionsByType,
            'shipments_by_carrier' => $byCarrier,
            'recent_shipments' => $recent,
        ]);
    }
}