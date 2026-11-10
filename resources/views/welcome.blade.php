<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CargoTrace - Global Shipment Visibility</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        .leaflet-container img {
            max-width: none !important;
            max-height: none !important;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 font-sans">

    <!-- Header Navigation -->
    <nav class="bg-slate-900 border-b border-slate-800 px-6 py-3.5 flex justify-between items-center shadow-md sticky top-0 z-50">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-black text-sm shadow-sm">
                CT
            </div>
            <span class="text-xl font-black text-white tracking-tight">Cargo<span class="text-indigo-400">Trace</span></span>
            <span class="text-[11px] bg-slate-800 text-indigo-300 px-2.5 py-0.5 rounded-full font-semibold border border-slate-700">UAS Web Programming 2</span>
        </div>
        <div class="flex items-center space-x-4 text-xs">
            <span class="text-slate-300">Logged in as: <strong class="text-white">{{ $userName }}</strong></span>
            <span class="bg-indigo-950 text-indigo-300 font-bold px-3 py-1 rounded-md border border-indigo-800/60 tracking-wide">{{ $userRole }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-1.5 rounded-md text-xs shadow-sm transition">Logout</button>
            </form>
        </div>
    </nav>

    <div class="p-6 max-w-7xl mx-auto space-y-6">

        @if($userRole === 'CUSTOMER')
            <!-- BANNER CUSTOMER PORTAL -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-6 rounded-xl shadow-md border border-indigo-900/50">
                <h1 class="text-xl font-bold tracking-tight">Portal Pelacakan Paket Customer</h1>
                <p class="text-xs text-indigo-200 mt-1">Selamat datang kembali, <strong>{{ $userName }}</strong>. Berikut adalah ringkasan status & rute pengiriman cargo milik Anda.</p>
            </div>
        @else
            <!-- METRIK KPI ADMIN & OPERATOR -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Shipments</p>
                    <p class="text-2xl font-black text-slate-900 mt-1">{{ $totalShipments }}</p>
                </div>
                <div class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-indigo-500 uppercase tracking-wider">In Transit</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1">{{ $inTransit }}</p>
                </div>
                <div class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-emerald-500 uppercase tracking-wider">Delivered</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">{{ $delivered }}</p>
                </div>
                <div class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-rose-500 uppercase tracking-wider">Delayed</p>
                    <p class="text-2xl font-black text-rose-600 mt-1">{{ $delayed }}</p>
                </div>
                <div class="bg-white p-4.5 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-[11px] font-bold text-amber-500 uppercase tracking-wider">Open Exceptions</p>
                    <p class="text-2xl font-black text-amber-600 mt-1">{{ $openExceptions }}</p>
                </div>
            </div>
        @endif

        <!-- LIVE MULTI-CURRENCY EXCHANGE RATES -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Live Multi-Currency Exchange Rates (Base: USD)</h3>
                <span class="text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold px-2.5 py-0.5 rounded-md" id="currency-status">● Live Syncing...</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-8 gap-3 text-xs" id="currency-container">
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / IDR</span><p class="font-bold text-slate-900 mt-0.5" id="rate-IDR">Rp 15.850</p></div>
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / EUR</span><p class="font-bold text-slate-900 mt-0.5" id="rate-EUR">€ 0,92</p></div>
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / SGD</span><p class="font-bold text-slate-900 mt-0.5" id="rate-SGD">S$ 1,34</p></div>
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / JPY</span><p class="font-bold text-slate-900 mt-0.5" id="rate-JPY">¥ 153,4</p></div>
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / GBP</span><p class="font-bold text-slate-900 mt-0.5" id="rate-GBP">£ 0,77</p></div>
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / AUD</span><p class="font-bold text-slate-900 mt-0.5" id="rate-AUD">A$ 1,51</p></div>
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / CNY</span><p class="font-bold text-slate-900 mt-0.5" id="rate-CNY">¥ 7,18</p></div>
                <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / MYR</span><p class="font-bold text-slate-900 mt-0.5" id="rate-MYR">RM 4,37</p></div>
            </div>
        </div>

        <!-- LIVE PORT WEATHER -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Live Global Port Weather Conditions</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                    <p class="font-bold text-slate-800">Tanjung Priok (Jakarta)</p>
                    <p class="text-slate-400 text-[11px] mt-0.5">Indonesia Port Hub</p>
                    <div class="mt-2.5 flex justify-between items-center">
                        <span class="text-xl font-black text-indigo-600" id="wx-jakarta">29.5°C</span>
                        <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-bold">Partly Cloudy</span>
                    </div>
                </div>
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                    <p class="font-bold text-slate-800">Singapore Port</p>
                    <p class="text-slate-400 text-[11px] mt-0.5">Global Maritime Hub</p>
                    <div class="mt-2.5 flex justify-between items-center">
                        <span class="text-xl font-black text-indigo-600" id="wx-singapore">30.1°C</span>
                        <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-bold">Thunderstorm</span>
                    </div>
                </div>
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                    <p class="font-bold text-slate-800">Port of Rotterdam</p>
                    <p class="text-slate-400 text-[11px] mt-0.5">Europe Gateway</p>
                    <div class="mt-2.5 flex justify-between items-center">
                        <span class="text-xl font-black text-indigo-600" id="wx-rotterdam">12.4°C</span>
                        <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-bold">Clear Sky</span>
                    </div>
                </div>
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/80">
                    <p class="font-bold text-slate-800">Port of Hamburg</p>
                    <p class="text-slate-400 text-[11px] mt-0.5">Germany Container Hub</p>
                    <div class="mt-2.5 flex justify-between items-center">
                        <span class="text-xl font-black text-indigo-600" id="wx-hamburg">10.8°C</span>
                        <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded font-bold">Overcast</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PETA LEAFLET & CHART SECTION -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="{{ $userRole === 'CUSTOMER' ? 'md:col-span-3' : 'md:col-span-2' }} bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-sm font-bold text-slate-800">Live Shipment Tracking Map</h3>
                    <span class="text-[10px] bg-indigo-50 text-indigo-700 font-bold px-2 py-0.5 rounded border border-indigo-200">OpenStreetMap Live</span>
                </div>
                <div id="map" style="height: 350px; width: 100%;" class="rounded-xl border border-slate-200"></div>
            </div>

            @if($userRole !== 'CUSTOMER')
                <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-sm font-bold text-slate-800 mb-3">Shipment Status Breakdown</h3>
                    <div style="height: 300px; width: 100%;" class="flex items-center justify-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            @endif
        </div>

        <!-- MARITIME NEWS BULLETIN -->
        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
            <div class="flex justify-between items-center mb-3">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Live Global Shipping & Maritime News Bulletin</h3>
                <span class="text-[10px] text-slate-400 font-semibold">Updated Real-Time</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5 text-xs">
                <div class="p-3.5 border border-slate-200/80 rounded-xl bg-slate-50">
                    <span class="px-2 py-0.5 bg-amber-100 text-amber-800 font-bold rounded text-[10px]">ROUTE ALERT</span>
                    <h4 class="font-bold text-slate-800 mt-1.5">Malacca Strait Vessel Traffic</h4>
                    <p class="text-slate-500 text-[11px] mt-1 leading-relaxed">Minor congestion near Singapore anchorage; average delay duration 4 hours.</p>
                </div>
                <div class="p-3.5 border border-slate-200/80 rounded-xl bg-slate-50">
                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 font-bold rounded text-[10px]">WEATHER ADVISORY</span>
                    <h4 class="font-bold text-slate-800 mt-1.5">North Sea Typhoon Watch</h4>
                    <p class="text-slate-500 text-[11px] mt-1 leading-relaxed">Container vessels re-routed towards Rotterdam terminal due to high gale warnings.</p>
                </div>
                <div class="p-3.5 border border-slate-200/80 rounded-xl bg-slate-50">
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 font-bold rounded text-[10px]">CUSTOMS NOTICE</span>
                    <h4 class="font-bold text-slate-800 mt-1.5">Tanjung Priok Green Lane Clearance</h4>
                    <p class="text-slate-500 text-[11px] mt-1 leading-relaxed">Automated customs verification operational with 15-minute processing average.</p>
                </div>
            </div>
        </div>

        <!-- TABLE LIST SHIPMENTS -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                <h3 class="text-sm font-bold text-slate-800">
                    {{ $userRole === 'CUSTOMER' ? 'Daftar Pengiriman Milik Anda' : 'Global Shipment Records' }}
                </h3>
                <span class="text-xs bg-slate-100 text-slate-600 font-bold px-2.5 py-0.5 rounded border border-slate-200">{{ count($shipments) }} Records</span>
            </div>
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase font-bold text-[10px]">
                    <tr>
                        <th class="p-3.5">Shipment No</th>
                        <th class="p-3.5">Carrier</th>
                        <th class="p-3.5">Origin</th>
                        <th class="p-3.5">Destination</th>
                        <th class="p-3.5">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($shipments as $s)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3.5 font-mono font-bold text-indigo-600">{{ $s['number'] }}</td>
                        <td class="p-3.5 font-medium text-slate-700">{{ $s['carrier'] }}</td>
                        <td class="p-3.5 text-slate-600">{{ $s['origin'] }}</td>
                        <td class="p-3.5 text-slate-600">{{ $s['destination'] }}</td>
                        <td class="p-3.5">
                            <span class="px-2.5 py-0.5 rounded-full font-bold text-[10px] {{ $s['status'] == 'DELIVERED' ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-100 text-indigo-700' }}">
                                {{ $s['status'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($userRole !== 'CUSTOMER')
            <!-- TABEL EXCEPTION MANAGEMENT -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-sm font-bold text-slate-800 mb-3">Operational Exception Log (Internal Pelabuhan)</h3>
                <div class="space-y-2.5">
                    @foreach($exceptions as $ex)
                    <div class="p-3.5 rounded-xl border border-rose-200 bg-rose-50/50 flex justify-between items-center text-xs">
                        <div>
                            <span class="font-bold text-rose-700 font-mono">{{ $ex['shipment'] }}</span>
                            <span class="ml-2 px-2 py-0.5 bg-rose-100 text-rose-800 font-bold rounded text-[10px]">{{ $ex['type'] }}</span>
                            <p class="text-slate-600 mt-1">{{ $ex['description'] }}</p>
                        </div>
                        <span class="font-bold text-rose-600 uppercase text-[10px] bg-rose-100 px-2 py-1 rounded">{{ $ex['severity'] }} SEVERITY</span>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>

    <script>
        window.addEventListener('load', function () {
            if (typeof L !== 'undefined') {
                const map = L.map('map').setView([1.3521, 103.8198], 3);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(map);

                const p1 = [-6.1018, 106.8829];
                const p2 = [1.3521, 103.8198];
                const p3 = [51.9244, 4.4777];

                L.marker(p1).addTo(map).bindPopup("<b>Tanjung Priok, Jakarta</b><br>Origin Port").openPopup();
                L.marker(p2).addTo(map).bindPopup("<b>Singapore Port</b><br>Transit Port");
                L.marker(p3).addTo(map).bindPopup("<b>Port of Rotterdam</b><br>Destination Port");

                L.polyline([p1, p2, p3], { color: '#4f46e5', weight: 3, dashArray: '6, 6' }).addTo(map);

                setTimeout(() => { map.invalidateSize(); }, 200);
            }

            const chartCtx = document.getElementById('statusChart');
            if (chartCtx && typeof Chart !== 'undefined') {
                new Chart(chartCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Picked Up', 'In Transit', 'Delivered'],
                        datasets: [{
                            data: [1, 2, 1],
                            backgroundColor: ['#f59e0b', '#4f46e5', '#10b981'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
                    }
                });
            }
        });

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

        async function fetchPortWeather() {
            try {
                const resJkt = await fetch('https://api.open-meteo.com/v1/forecast?latitude=-6.1&longitude=106.8&current_weather=true');
                const dataJkt = await resJkt.json();
                if(dataJkt && dataJkt.current_weather) {
                    document.getElementById('wx-jakarta').innerText = dataJkt.current_weather.temperature + '°C';
                }

                const resRtm = await fetch('http