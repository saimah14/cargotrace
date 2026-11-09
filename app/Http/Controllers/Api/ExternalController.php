<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExternalController extends Controller
{
    // Ubah ke false HANYA di laptop jika muncul "cURL error 60" (masalah sertifikat XAMPP).
    private const VERIFY_SSL = true;

    private const CURRENCIES = ['IDR', 'EUR', 'SGD', 'MYR', 'CNY'];

    public function rates(): JsonResponse
    {
        return $this->cachedFetch('fx_rates', 3600, fn () => $this->fetchRates());
    }

    public function news(): JsonResponse
    {
        return $this->cachedFetch('shipping_news', 900, fn () => $this->fetchNews());
    }

    // Cuaca di lokasi shipment (Open-Meteo, tanpa API key). Hanya informasi, bukan prediksi delay.
    public function weather(Request $request): JsonResponse
    {
        $v = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lon' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $lat = round((float) $v['lat'], 2);
        $lon = round((float) $v['lon'], 2);

        return $this->cachedFetch(
            sprintf('weather_%s_%s', $lat, $lon),
            1800,
            fn () => $this->fetchWeather($lat, $lon)
        );
    }

    // Pakai cache; kalau API gagal, kembalikan data terakhir yang berhasil (stale).
    private function cachedFetch(string $key, int $ttl, callable $fetch): JsonResponse
    {
        $cached = Cache::get($key);

        if ($cached) {
            return response()->json($cached + ['stale' => false]);
        }

        try {
            $data = $fetch();
            $data['fetched_at'] = now()->toIso8601String();

            Cache::put($key, $data, $ttl);
            Cache::forever($key.'_last_good', $data);

            return response()->json($data + ['stale' => false]);
        } catch (Throwable $e) {
            Log::warning("External API failed for {$key}: ".$e->getMessage());

            $old = Cache::get($key.'_last_good');

            if ($old) {
                return response()->json($old + ['stale' => true]);
            }

            return response()->json([
                'message' => 'External service unavailable. Please try again later.',
            ], 503);
        }
    }

    private function fetchWeather(float $lat, float $lon): array
    {
        $json = Http::withOptions(['verify' => self::VERIFY_SSL])
            ->timeout(8)
            ->retry(2, 300)
            ->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lon,
                'current' => 'temperature_2m,wind_speed_10m,precipitation,weather_code',
                'timezone' => 'UTC',
            ])
            ->throw()
            ->json();

        $c = $json['current'] ?? null;

        if (! is_array($c) || ! isset($c['temperature_2m'])) {
            throw new \RuntimeException('Unexpected response from weather API.');
        }

        return [
            'latitude' => $lat,
            'longitude' => $lon,
            'temperature_c' => $c['temperature_2m'],
            'wind_kmh' => $c['wind_speed_10m'] ?? null,
            'precipitation_mm' => $c['precipitation'] ?? null,
            'weather_code' => $c['weather_code'] ?? null,
            'observed_at' => $c['time'] ?? null,
            'source' => 'Open-Meteo',
        ];
    }

    private function fetchRates(): array
    {
        $json = Http::withOptions(['verify' => self::VERIFY_SSL])
            ->timeout(8)
            ->retry(2, 300)
            ->get('https://api.frankfurter.dev/v1/latest', [
                'base' => 'USD',
                'symbols' => implode(',', self::CURRENCIES),
            ])
            ->throw()
            ->json();

        $rates = $json['rates'] ?? [];

        if (empty($rates['IDR']) || empty($rates['EUR'])) {
            throw new \RuntimeException('Unexpected response from rates API.');
        }

        return [
            'base' => $json['base'] ?? 'USD',
            'date' => $json['date'] ?? null,
            'rates' => $rates,
            'idr_per_eur' => round($rates['IDR'] / $rates['EUR'], 2),
            'source' => 'Frankfurter (European Central Bank)',
        ];
    }

    private function fetchNews(): array
    {
        $body = Http::withOptions(['verify' => self::VERIFY_SSL])
            ->withHeaders(['User-Agent' => 'CargoTrace/1.0'])
            ->timeout(8)
            ->retry(2, 300)
            ->get('https://news.google.com/rss/search', [
                'q' => 'container shipping OR freight OR port congestion',
                'hl' => 'en-US',
                'gl' => 'US',
                'ceid' => 'US:en',
            ])
            ->throw()
            ->body();

        $xml = @simplexml_load_string($body);

        if ($xml === false || ! isset($xml->channel->item)) {
            throw new \RuntimeException('Unexpected response from news feed.');
        }

        $items = [];

        foreach ($xml->channel->item as $item) {
            $items[] = [
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'source' => (string) $item->source,
                'published_at' => date('c', strtotime((string) $item->pubDate)),
            ];

            if (count($items) >= 10) {
                break;
            }
        }

        return [
            'items' => $items,
            'source' => 'Google News RSS',
        ];
    }
}