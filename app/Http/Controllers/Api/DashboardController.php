<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shipment;
use App\Models\ShipmentException;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user     = Auth::user();
        $userName = $user->name;
        $userRole = strtoupper($user->role ?? 'CUSTOMER');

        $allShipments = [
            ['id' => 1, 'number' => 'CT-DEMO-0001', 'carrier' => 'Nusantara Ocean Line', 'origin' => 'Tanjung Priok, Jakarta', 'destination' => 'Port of Rotterdam', 'status' => 'IN_TRANSIT', 'is_delayed' => false, 'owner' => 'customer@cargotrace.com'],
            ['id' => 2, 'number' => 'CT-DEMO-0002', 'carrier' => 'Straits Freight Co', 'origin' => 'Singapore Port', 'destination' => 'Port of Hamburg', 'status' => 'IN_TRANSIT', 'is_delayed' => true, 'owner' => 'customer@cargotrace.com'],
            ['id' => 3, 'number' => 'CT-DEMO-0003', 'carrier' => 'Eurasia Cargo Lines', 'origin' => 'Port Klang', 'destination' => 'Port of Rotterdam', 'status' => 'IN_TRANSIT', 'is_delayed' => true, 'owner' => 'other@cargotrace.com'],
            ['id' => 4, 'number' => 'CT-DEMO-0004', 'carrier' => 'Nusantara Ocean Line', 'origin' => 'Tanjung Priok, Jakarta', 'destination' => 'Port of Hamburg', 'status' => 'DELIVERED', 'is_delayed' => false, 'owner' => 'customer@cargotrace.com'],
            ['id' => 5, 'number' => 'CT-DEMO-0005', 'carrier' => 'Straits Freight Co', 'origin' => 'Colombo Port', 'destination' => 'Port of Rotterdam', 'status' => 'PICKED_UP', 'is_delayed' => false, 'owner' => 'other@cargotrace.com'],
        ];

        if ($userRole === 'CUSTOMER') {
            $shipments = array_values(array_filter($allShipments, function($s) use ($user) {
                return $s['owner'] === $user->email;
            }));
            $totalShipments = count($shipments);
            $inTransit      = count(array_filter($shipments, fn($s) => $s['status'] === 'IN_TRANSIT'));
            $delivered      = count(array_filter($shipments, fn($s) => $s['status'] === 'DELIVERED'));
            $delayed        = count(array_filter($shipments, fn($s) => $s['is_delayed']));
            $openExceptions = 0;
            $statusCounts   = ['CREATED' => 0, 'BOOKED' => 0, 'PICKED_UP' => 0, 'IN_TRANSIT' => $inTransit, 'DELIVERED' => $delivered];
            $exceptions     = [];
        } else {
            $shipments      = $allShipments;
            $totalShipments = count($shipments);
            $inTransit      = 2;
            $delivered      = 1;
            $delayed        = 2;
            $openExceptions = 2;
            $statusCounts   = ['CREATED' => 0, 'BOOKED' => 0, 'PICKED_UP' => 1, 'IN_TRANSIT' => 2, 'DELIVERED' => 1];
            $exceptions     = [
                ['shipment' => 'CT-DEMO-0002', 'type' => 'CUSTOMS_HOLD', 'severity' => 'HIGH', 'status' => 'OPEN', 'reported' => '2026-11-07 14:29:13', 'description' => 'Container flagged for physical inspection at port verification unit.'],
                ['shipment' => 'CT-DEMO-0003', 'type' => 'WEATHER_DELAY', 'severity' => 'MEDIUM', 'status' => 'INVESTIGATING', 'reported' => '2026-11-08 09:15:00', 'description' => 'Severe typhoons causing 18-hour vessel re-routing.']
            ];
        }

        $notifications = [
            ['text' => 'Shipment CT-DEMO-0002 is delayed. New ETA: 2026-11-13 13:29:13.', 'time' => '11/7/2026, 2:29:13 PM']
        ];

        return view('welcome', compact(
            'userName',
            'userRole',
            'totalShipments',
            'inTransit',
            'delivered',
            'delayed',
            'openExceptions',
            'statusCounts',
            'shipments',
            'exceptions',
            'notifications'
        ));
    }
}