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

    public function rates(Request $request): JsonResponse
    {
        if ($request->boolean('refresh')) {
            Cache::forget('fx_rates_v5');
        }

        return $this->cachedFetch('fx_rates_v5', 60, fn () => $this->fetchRates());
    }

    public function news(): JsonResponse
    {
        return $this->cachedFetch('shipping_news', 900, fn () => $this->fetchNews());
    }

    public function countries(): JsonResponse
    {
        return $this->cachedFetch('world_countries_v2', 604800, function () {
            return [
                'countries' => $this->fetchCountryNames(),
                'source' => 'REST Countries',
            ];
        });
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
            ->timeout(10)
            ->retry(2, 300)
            ->get('https://api.fxratesapi.com/latest', ['base' => 'IDR'])
            ->throw()
            ->json();

        $rates = $json['rates'] ?? null;

        if (($json['success'] ?? false) !== true
            || ($json['base'] ?? null) !== 'IDR'
            || ! is_array($rates)
            || empty($rates['IDR'])
            || empty($rates['MYR'])
            || empty($rates['JPY'])) {
            throw new \RuntimeException('Unexpected response from rates API.');
        }

        $currencies = Cache::remember(
            'currency_country_map_v2',
            604800,
            fn () => $this->fetchCurrencyCountryMap()
        );
        $rates = array_intersect_key($rates, $currencies);
        $currencies = array_intersect_key($currencies, $rates);

        return [
            'base' => 'IDR',
            'date' => isset($json['timestamp'])
                ? gmdate('Y-m-d', (int) $json['timestamp'])
                : null,
            'updated_at' => $json['date'] ?? null,
            'rates' => $rates,
            'currencies' => $currencies,
            'source' => 'FXRatesAPI',
        ];
    }

    private function fetchCurrencyCountryMap(): array
    {
        $countries = Http::withOptions(['verify' => self::VERIFY_SSL])
            ->withHeaders(['User-Agent' => 'CargoTrace/1.0'])
            ->timeout(12)
            ->retry(2, 300)
            ->get('https://raw.githubusercontent.com/mledoze/countries/master/countries.json')
            ->throw()
            ->json();

        if (! is_array($countries)) {
            throw new \RuntimeException('Unexpected response from country metadata source.');
        }

        $currencies = [];

        foreach ($countries as $country) {
            $countryName = $country['name']['common'] ?? null;
            if (! is_string($countryName) || $countryName === '') {
                continue;
            }

            $localName = $country['translations']['ind']['common'] ?? null;

            foreach (($country['currencies'] ?? []) as $code => $currency) {
                if (! is_array($currency)) {
                    continue;
                }

                $currencies[$code] ??= [
                    'name' => $currency['name'] ?? $code,
                    'symbol' => $currency['symbol'] ?? $code,
                    'countries' => [],
                    'aliases' => [],
                ];
                $currencies[$code]['countries'][$countryName] = $countryName;

                if (is_string($localName) && $localName !== '' && $localName !== $countryName) {
                    $currencies[$code]['aliases'][$localName] = $localName;
                }
            }
        }

        foreach ($currencies as &$currency) {
            $currency['countries'] = array_values($currency['countries']);
            $currency['aliases'] = array_values($currency['aliases']);
        }
        unset($currency);

        return $currencies;
    }

    private function fetchCountryNames(): array
    {
        $countries = Http::withOptions(['verify' => self::VERIFY_SSL])
            ->withHeaders(['User-Agent' => 'CargoTrace/1.0'])
            ->timeout(12)
            ->retry(2, 300)
            ->get('https://raw.githubusercontent.com/mledoze/countries/master/countries.json')
            ->throw()
            ->json();

        if (! is_array($countries)) {
            throw new \RuntimeException('Unexpected response from country metadata source.');
        }

        $names = [];

        foreach ($countries as $country) {
            $commonName = $country['name']['common'] ?? null;
            if (! is_string($commonName) || $commonName === '') {
                continue;
            }

            $names[$commonName] = $commonName;
            $localName = $country['translations']['ind']['common'] ?? null;
            if (is_string($localName) && $localName !== '') {
                $names[$localName] = $localName;
            }
        }

        $names = array_values($names);
        sort($names, SORT_NATURAL | SORT_FLAG_CASE);

        return $names;
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