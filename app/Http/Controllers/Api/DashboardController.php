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
        $userName =$user->name;
        $userRole = strtoupper($user->role ?? 'CUSTOMER');

        $allShipments = [
            [
                'id' => 1, 'number' => 'CT-DEMO-0001', 'carrier' => 'Nusantara Ocean Line', 
                'origin' => 'Tanjung Priok, Jakarta', 'destination' => 'Port of Rotterdam', 
                'status' => 'IN_TRANSIT', 'is_delayed' => false, 'owner' => 'customer@cargotrace.com',
                'departure' => '2026-11-01', 'eta' => '2026-11-15', 'cargo_type' => 'Electronics (20ft Dry)', 'weight' => '14,200 kg'
            ],
            [
                'id' => 2, 'number' => 'CT-DEMO-0002', 'carrier' => 'Straits Freight Co', 
                'origin' => 'Singapore Port', 'destination' => 'Port of Hamburg', 
                'status' => 'IN_TRANSIT', 'is_delayed' => true, 'owner' => 'customer@cargotrace.com',
                'departure' => '2026-11-03', 'eta' => '2026-11-18', 'cargo_type' => 'Textiles & Apparel', 'weight' => '18,500 kg'
            ],
            [
                'id' => 3, 'number' => 'CT-DEMO-0003', 'carrier' => 'Eurasia Cargo Lines', 
                'origin' => 'Port Klang', 'destination' => 'Port of Rotterdam', 
                'status' => 'IN_TRANSIT', 'is_delayed' => true, 'owner' => 'other@cargotrace.com',
                'departure' => '2026-11-02', 'eta' => '2026-11-16', 'cargo_type' => 'Automotive Parts', 'weight' => '22,100 kg'
            ],
            [
                'id' => 4, 'number' => 'CT-DEMO-0004', 'carrier' => 'Nusantara Ocean Line', 
                'origin' => 'Tanjung Priok, Jakarta', 'destination' => 'Port of Hamburg', 
                'status' => 'DELIVERED', 'is_delayed' => false, 'owner' => 'customer@cargotrace.com',
                'departure' => '2026-10-20', 'eta' => '2026-11-05', 'cargo_type' => 'Raw Materials', 'weight' => '30,000 kg'
            ],
            [
                'id' => 5, 'number' => 'CT-DEMO-0005', 'carrier' => 'Straits Freight Co', 
                'origin' => 'Colombo Port', 'destination' => 'Port of Rotterdam', 
                'status' => 'PICKED_UP', 'is_delayed' => false, 'owner' => 'other@cargotrace.com',
                'departure' => '2026-11-08', 'eta' => '2026-11-25', 'cargo_type' => 'Spices & Agricultural Products', 'weight' => '12,000 kg'
            ],
        ];

        // Filter Otorisasi Peran Sesuai PPT
        if ($userRole === 'CUSTOMER') {
            $shipments = array_values(array_filter($allShipments, fn($s) =>$s['owner'] === $user->email));$exceptions = [];
        } else {
            $shipments =$allShipments;
            $exceptions = [
                ['id' => 101, 'shipment' => 'CT-DEMO-0002', 'type' => 'CUSTOMS_HOLD', 'severity' => 'HIGH', 'status' => 'OPEN', 'reported' => '2026-11-07 14:29:13', 'description' => 'Container flagged for physical inspection at port verification unit.'],
                ['id' => 102, 'shipment' => 'CT-DEMO-0003', 'type' => 'WEATHER_DELAY', 'severity' => 'MEDIUM', 'status' => 'INVESTIGATING', 'reported' => '2026-11-08 09:15:00', 'description' => 'Severe typhoons causing 18-hour vessel re-routing.'],
                ['id' => 103, 'shipment' => 'CT-DEMO-0001', 'type' => 'PORT_CONGESTION', 'severity' => 'LOW', 'status' => 'RESOLVED', 'reported' => '2026-11-05 11:00:00', 'description' => 'Berth delay resolved; vessel anchored successfully.']
            ];
        }

        $totalShipments = count($shipments);
        $inTransit      = count(array_filter($shipments, fn($s) =>$s['status'] === 'IN_TRANSIT'));
        $delivered      = count(array_filter($shipments, fn($s) =>$s['status'] === 'DELIVERED'));
        $delayed        = count(array_filter($shipments, fn($s) =>$s['is_delayed']));
        $openExceptions = count(array_filter($exceptions, fn($e) =>$e['status'] === 'OPEN'));

        $statusCounts   = [
            'CREATED' => 0, 'BOOKED' => 0, 'PICKED_UP' => 1, 
            'IN_TRANSIT' => $inTransit, 'DELIVERED' =>$delivered
        ];

        $carriers = [
            ['code' => 'NOL', 'name' => 'Nusantara Ocean Line', 'country' => 'Indonesia', 'fleet' => 24, 'contact' => 'ops@nusantara-ocean.co.id'],
            ['code' => 'SFC', 'name' => 'Straits Freight Co', 'country' => 'Singapore', 'fleet' => 45, 'contact' => 'dispatch@straitsfreight.sg'],
            ['code' => 'ECL', 'name' => 'Eurasia Cargo Lines', 'country' => 'Netherlands', 'fleet' => 60, 'contact' => 'support@eurasiacargo.nl']
        ];

        $usersList = [
            ['name' => 'Saimah Admin', 'email' => 'admin@cargotrace.com', 'role' => 'Administrator', 'status' => 'Active'],
            ['name' => 'Operator Logistik', 'email' => 'operator@cargotrace.com', 'role' => 'Logistics Operator', 'status' => 'Active'],
            ['name' => 'Customer BSN', 'email' => 'customer@cargotrace.com', 'role' => 'Customer', 'status' => 'Active']
        ];

        $auditLogs = [
            ['actor' => 'Saimah Admin', 'action' => 'UPDATE_STATUS', 'entity' => 'Shipment CT-DEMO-0002', 'time' => '2026-11-09 16:42:10', 'ip' => '127.0.0.1'],
            ['actor' => 'Operator Logistik', 'action' => 'ADD_TRACKING_EVENT', 'entity' => 'Shipment CT-DEMO-0001', 'time' => '2026-11-09 14:15:33', 'ip' => '127.0.0.1'],
            ['actor' => 'Saimah Admin', 'action' => 'RESOLVE_EXCEPTION', 'entity' => 'Exception #103', 'time' => '2026-11-08 10:05:12', 'ip' => '127.0.0.1']
        ];

        $notifications = [
            ['title' => 'Shipment Delayed', 'text' => 'Shipment CT-DEMO-0002 is delayed due to Customs Hold.', 'time' => '2 jam lalu', 'unread' => true],
            ['title' => 'Status Updated', 'text' => 'Shipment CT-DEMO-0004 marked as DELIVERED.', 'time' => '1 hari lalu', 'unread' => false],
            ['title' => 'Weather Alert', 'text' => 'Typhoon alert near Malacca Strait affecting route CT-DEMO-0003.', 'time' => '2 hari lalu', 'unread' => false]
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
            'carriers',
            'usersList',
            'auditLogs',
            'notifications'
        ));
    }
}