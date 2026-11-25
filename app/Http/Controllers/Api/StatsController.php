<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\ShipmentException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class StatsController extends Controller
{
    // Data untuk kartu angka dan grafik dashboard (JSON, bukan view).
    public function dashboard(): JsonResponse
    {
        $month = [now()->startOfMonth(), now()->endOfMonth()];
        $byStatus = array_fill_keys(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'], 0);

        $rows = Shipment::select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        foreach ($rows as $row) {
            $byStatus[$row->status] = (int) $row->total;
        }

        $delayedQuery = Shipment::with('carrier:id,name')
            ->where(function ($query) {
                $query->where('is_delayed', true)
                    ->orWhere(function ($query) {
                        $query->whereNotIn('status', [Shipment::DELIVERED])
                            ->whereColumn('estimated_arrival', '>', 'expected_arrival');
                    });
            });
        $delayedCount = (clone $delayedQuery)->count();
        $delayed = $delayedQuery
            ->latest()
            ->limit(10)
            ->get()
            ->map(function (Shipment $shipment) {
                return [
                    'id' => $shipment->id,
                    'shipment_number' => $shipment->shipment_number,
                    'status' => $shipment->status,
                    'expected_arrival' => $shipment->expected_arrival,
                    'estimated_arrival' => $shipment->estimated_arrival ?? $shipment->expected_arrival,
                    'carrier' => $shipment->carrier?->name,
                ];
            });

        $byCarrier = [];
        foreach (Shipment::with('carrier:id,name')->get() as $shipment) {
            $name = $shipment->carrier->name ?? 'Unknown';
            $byCarrier[$name] = ($byCarrier[$name] ?? 0) + 1;
        }

        return response()->json([
            'total_shipments' => Shipment::count(),
            'shipments_today' => Shipment::whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->count(),
            'shipments_this_month' => Shipment::whereBetween('created_at', $month)->count(),
            'shipments_this_year' => Shipment::whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()])->count(),
            'revenue_this_month' => Shipment::whereBetween('created_at', $month)->sum('cost'),
            'shipments_by_status' => $byStatus,
            'shipments_by_carrier' => (object) $byCarrier,
            'delayed_shipments' => $delayedCount,
            'delayed_watchlist' => $delayed,
            'open_exceptions' => ShipmentException::where('status', '!=', 'RESOLVED')->count(),
        ]);
    }

    // Kurs beberapa mata uang (base USD) lewat Laravel HTTP Client.
    public function currencies(): JsonResponse
    {
        $symbols = ['IDR', 'EUR', 'SGD', 'JPY', 'GBP', 'AUD', 'CNY', 'MYR'];
        $cacheKey = 'ct_currencies_usd';

        try {
            $res = Http::timeout(6)->acceptJson()->get('https://api.frankfurter.dev/v1/latest', [
                'base' => 'USD',
                'symbols' => implode(',', $symbols),
            ]);

            if ($res->successful() && ! empty($res->json('rates'))) {
                $payload = [
                    'base' => 'USD',
                    'date' => $res->json('date'),
                    'rates' => $res->json('rates'),
                    'source' => 'Frankfurter (European Central Bank)',
                    'stale' => false,
                ];
                Cache::put($cacheKey, $payload, now()->addDay());

                return response()->json($payload);
            }
        } catch (\Throwable $e) {
            // jatuh ke cache di bawah
        }

        if ($cached = Cache::get($cacheKey)) {
            $cached['stale'] = true;

            return response()->json($cached);
        }

        return response()->json(['message' => 'Exchange rate service unavailable.'], 503);
    }
}
