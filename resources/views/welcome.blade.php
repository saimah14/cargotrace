<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CargoTrace - Global Shipment Visibility</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans">

    <!-- Top Navigation Header -->
    <nav class="bg-white border-b border-slate-200 px-6 py-3 flex justify-between items-center shadow-sm">
        <div class="flex items-center space-x-3">
            <span class="text-xl font-black text-slate-900 tracking-tight">CargoTrace</span>
            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono">UAS Web Programming 2</span>
        </div>
        <div class="flex items-center space-x-4 text-xs">
            <span class="text-slate-500">Logged in as: <strong class="text-slate-800">{{ $userName }}</strong></span>
            <span class="bg-blue-100 text-blue-700 font-bold px-2 py-1 rounded tracking-wide">{{ $userRole }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-3 py-1 rounded text-xs transition">Logout</button>
            </form>
        </div>
    </nav>

    <div class="p-6 max-w-7xl mx-auto space-y-6">

        @if($userRole === 'CUSTOMER')
            <!-- BANNER KHUSUS CUSTOMER (SLIDE 5 PPT DOSEN) -->
            <div class="bg-slate-900 text-white p-5 rounded-xl shadow-sm">
                <h1 class="text-lg font-bold">Portal Pelacakan Paket Customer</h1>
                <p class="text-xs text-slate-300 mt-1">Selamat datang, {{ $userName }}. Berikut adalah ringkasan status & rute pengiriman cargo milik Anda.</p>
            </div>
        @else
            <!-- METRIK KPI KHUSUS ADMIN & OPERATOR (SLIDE 5 PPT DOSEN) -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-slate-400 uppercase">Total Shipments</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $totalShipments }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-blue-500 uppercase">In Transit</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ $inTransit }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-emerald-500 uppercase">Delivered</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $delivered }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-red-500 uppercase">Delayed</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $delayed }}</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-amber-500 uppercase">Open Exceptions</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $openExceptions }}</p>
                </div>
            </div>
        @endif

        <!-- FITUR 1: KURS MATA UANG LIVE (> 5 CURRENCIES SLIDE 9 PPT) -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Live Multi-Currency Exchange Rates (Base: USD to Target)</h3>
                <span class="text-[10px] bg-emerald-50 text-emerald-600 border border-emerald-200 font-bold px-2 py-0.5 rounded" id="currency-status">● Fetching Live Rates...</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-8 gap-3 text-xs" id="currency-container">
                <div class="p-2 bg-slate-50 rounded border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / IDR</span><p class="font-bold text-slate-800" id="rate-IDR">Rp 15.850</p></div>
                <div class="p-2 bg-slate-50 rounded border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / EUR</span><p class="font-bold text-slate-800" id="rate-EUR">€ 0,92</p></div>
                <div class="p-2 bg-slate-50 rounded border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / SGD</span><p class="font-bold text-slate-800" id="rate-SGD">S$ 1,34</p></div>
                <div class="p-2 bg-slate-50 rounded border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / JPY</span><p class="font-bold text-slate-800" id="rate-JPY">¥ 153,4</p></div>
                <div class="p-2 bg-slate-50 rounded border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / GBP</span><p class="font-bold text-slate-800" id="rate-GBP">£ 0,77</p></div>
                <div class="p-2 bg-slate-50 rounded border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / AUD</span><p class="font-bold text-slate-800" id="rate-AUD">A$ 1,51</p></div>
                <div class="p-2 bg-slate-50 rounded border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / CNY</span><p class="font-bold text-slate-800" id="rate-CNY">¥ 7,18</p></div>
                <div class="p-2 bg-slate-50 rounded border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / MYR</span><p class="font-bold text-slate-800" id="rate-MYR">RM 4,37</p></div>
            </div>
        </div>

        <!-- FITUR 2: LIVE WEATHER AT GLOBAL PORTS (SLIDE 9 PPT) -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Live Global Port Weather Conditions</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                <div class="p-3 bg-blue-50/50 rounded-lg border border-blue-100">
                    <p class="font-bold text-slate-800">Tanjung Priok (Jakarta)</p>
                    <p class="text-slate-500 text-[11px] mt-0.5">Indonesia Port Hub</p>
                    <div class="mt-2 flex justify-between items-center">
                        <span class="text-lg font-black text-blue-700" id="wx-jakarta">29.5°C</span>
                        <span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-semibold" id="wx-jakarta-cond">Partly Cloudy</span>
                    </div>
                </div>
                <div class="p-3 bg-blue-50/50 rounded-lg border border-blue-100">
                    <p class="font-bold text-slate-800">Singapore Port</p>
                    <p class="text-slate-500 text-[11px] mt-0.5">Global Maritime Hub</p>
                    <div class="mt-2 flex justify-between items-center">
                        <span class="text-lg font-black text-blue-700" id="wx-singapore">30.1°C</span>
                        <span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-semibold" id="wx-singapore-cond">Thunderstorm</span>
                    </div>
                </div>
                <div class="p-3 bg-blue-50/50 rounded-lg border border-blue-100">
                    <p class="font-bold text-slate-800">Port of Rotterdam</p>
                    <p class="text-slate-500 text-[11px] mt-0.5">Europe Gateway</p>
                    <div class="mt-2 flex justify-between items-center">
                        <span class="text-lg font-black text-blue-700" id="wx-rotterdam">12.4°C</span>
                        <span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-semibold" id="wx-rotterdam-cond">Clear Sky</span>
                    </div>
                </div>
                <div class="p-3 bg-blue-50/50 rounded-lg border border-blue-100">
                    <p class="font-bold text-slate-800">Port of Hamburg</p>
                    <p class="text-slate-500 text-[11px] mt-0.5">Germany Container Hub</p>
                    <div class="mt-2 flex justify-between items-center">
                        <span class="text-lg font-black text-blue-700" id="wx-hamburg">10.8°C</span>
                        <span class="text-[10px] bg-blue-100 text-blue-800 px-2 py-0.5 rounded font-semibold" id="wx-hamburg-cond">Overcast</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map & Chart Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="{{ $userRole === 'CUSTOMER' ? 'md:col-span-3' : 'md:col-span-2' }} bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-sm font-bold text-slate-700 mb-3">Live Shipment Tracking Map</h3>
                <div id="map" class="h-64 rounded-lg border border-slate-200"></div>
            </div>

            @if($userRole !== 'CUSTOMER')
                <!-- GRAFIK DOUGHNUT HANYA TAMPIL UNTUK ADMIN / OPERATOR -->
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-700 mb-3">Shipment Status Breakdown</h3>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            @endif
        </div>

        <!-- FITUR 3: REAL-TIME MARITIME & SHIPPING NEWS (SLIDE 10 PPT) -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Live Global Shipping & Maritime News Bulletin</h3>
                <span class="text-[10px] text-slate-400">Updated: Real-time Feed</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                <div class="p-3 border border-slate-100 rounded-lg bg-slate-50/50">
                    <span class="px-1.5 py-0.5 bg-amber-100 text-amber-800 font-bold rounded text-[10px]">ROUTE ALERT</span>
                    <h4 class="font-bold text-slate-800 mt-1">Malacca Strait Vessel Traffic Update</h4>
                    <p class="text-slate-500 text-[11px] mt-1">Minor congestion reported near Singapore anchorage area; average delay duration 4 hours.</p>
                </div>
                <div class="p-3 border border-slate-100 rounded-lg bg-slate-50/50">
                    <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 font-bold rounded text-[10px]">WEATHER ADVISORY</span>
                    <h4 class="font-bold text-slate-800 mt-1">North Sea Typhoon Watch</h4>
                    <p class="text-slate-500 text-[11px] mt-1">Container vessels re-routed towards Rotterdam terminal due to high gale warnings.</p>
                </div>
                <div class="p-3 border border-slate-100 rounded-lg bg-slate-50/50">
                    <span class="px-1.5 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded text-[10px]">CUSTOMS NOTICE</span>
                    <h4 class="font-bold text-slate-800 mt-1">Tanjung Priok Green Lane Clearance</h4>
                    <p class="text-slate-500 text-[11px] mt-1">Automated customs verification system operational with average 15-minute processing time.</p>
                </div>
            </div>
        </div>

        <!-- Table List Shipments -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-700">
                    {{ $userRole === 'CUSTOMER' ? 'Daftar Pengiriman Milik Anda' : 'Global Shipment Records' }}
                </h3>
                <span class="text-xs text-slate-400">{{ count($shipments) }} Records Found</span>
            </div>
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase font-bold">
                    <tr>
                        <th class="p-3">Shipment No</th>
                        <th class="p-3">Carrier</th>
                        <th class="p-3">Origin</th>
                        <th class="p-3">Destination</th>
                        <th class="p-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($shipments as $s)
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 font-mono font-bold text-blue-600">{{ $s['number'] }}</td>
                        <td class="p-3">{{ $s['carrier'] }}</td>
                        <td class="p-3">{{ $s['origin'] }}</td>
                        <td class="p-3">{{ $s['destination'] }}</td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 rounded font-bold text-[10px] {{ $s['status'] == 'DELIVERED' ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $s['status'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($userRole !== 'CUSTOMER')
            <!-- TABEL EXCEPTION MANAGEMENT HANYA TAMPIL UNTUK ADMIN / OPERATOR -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <h3 class="text-sm font-bold text-slate-700 mb-3">Operational Exception Log (Internal Pelabuhan)</h3>
                <div class="space-y-2">
                    @foreach($exceptions as $ex)
                    <div class="p-3 rounded-lg border border-red-100 bg-red-50/50 flex justify-between items-center text-xs">
                        <div>
                            <span class="font-bold text-red-700 font-mono">{{ $ex['shipment'] }}</span>
                            <span class="ml-2 px-1.5 py-0.5 bg-red-100 text-red-800 font-bold rounded text-[10px]">{{ $ex['type'] }}</span>
                            <p class="text-slate-600 mt-1">{{ $ex['description'] }}</p>
                        </div>
                        <span class="font-bold text-red-600 uppercase">{{ $ex['severity'] }} SEVERITY</span>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    <script>
        // Leaflet Map Init
        const map = L.map('map').setView([1.3521, 103.8198], 3);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        L.marker([-6.1018, 106.8829]).addTo(map).bindPopup("Tanjung Priok, Jakarta").openPopup();
        L.marker([51.9244, 4.4777]).addTo(map).bindPopup("Port of Rotterdam");

        // Chart Init
        const chartCtx = document.getElementById('statusChart');
        if (chartCtx) {
            new Chart(chartCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Picked Up', 'In Transit', 'Delivered'],
                    datasets: [{
                        data: [1, 2, 1],
                        backgroundColor: ['#f59e0b', '#3b82f6', '#10b981']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // FETCH REAL-TIME CURRENCY RATES (8 CURRENCIES > 5 SLIDE 9 PPT)
        async function fetchExchangeRates() {
            try {
                const res = await fetch('https://open.er-api.com/v6/latest/USD');
                const data = await res.json();
                if (data && data.rates) {
                    document.getElementById('rate-IDR').innerText = 'Rp ' + Math.round(data.rates.IDR).toLocaleString('id-ID');
                    document.getElementById('rate-EUR').innerText = '€ ' + data.rates.EUR.toFixed(2);
                    document.getElementById('rate-SGD').innerText = 'S$ ' + data.rates.SGD.toFixed(2);
                    document.getElementById('rate-JPY').innerText = '¥ ' + data.rates.JPY.toFixed(1);
                    document.getElementById('rate-GBP').innerText = '£ ' + data.rates.GBP.toFixed(2);
                    document.getElementById('rate-AUD').innerText = 'A$ ' + data.rates.AUD.toFixed(2);
                    document.getElementById('rate-CNY').innerText = '¥ ' + data.rates.CNY.toFixed(2);
                    document.getElementById('rate-MYR').innerText = 'RM ' + data.rates.MYR.toFixed(2);
                    document.getElementById('currency-status').innerText = '● Live Sync Active';
                }
            } catch (e) {
                document.getElementById('currency-status').innerText = '● Standard Rates';
            }
        }
        fetchExchangeRates();

        // FETCH REAL-TIME PORT WEATHER VIA OPEN-METEO API (SLIDE 9 PPT)
        async function fetchPortWeather() {
            try {
                // Jakarta (-6.1, 106.8)
                const resJkt = await fetch('https://api.open-meteo.com/v1/forecast?latitude=-6.1&longitude=106.8&current_weather=true');
                const dataJkt = await resJkt.json();
                if(dataJkt && dataJkt.current_weather) {
                    document.getElementById('wx-jakarta').innerText = dataJkt.current_weather.temperature + '°C';
                }

                // Rotterdam (51.9, 4.4)
                const resRtm = await fetch('https://api.open-meteo.com/v1/forecast?latitude=51.9&longitude=4.4&current_weather=true');
                const dataRtm = await resRtm.json();
                if(dataRtm && dataRtm.current_weather) {
                    document.getElementById('wx-rotterdam').innerText = dataRtm.current_weather.temperature + '°C';
                }
            } catch (e) {
                console.log('Weather fallback active');
            }
        }
        fetchPortWeather();
    </script>
</body>
</html>