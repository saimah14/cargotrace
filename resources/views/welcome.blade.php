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
        .leaflet-container img { max-width: none !important; max-height: none !important; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans flex min-h-screen">

    <!-- SIDEBAR NAVIGASI SEPERTI DESAIN PPT -->
    <aside class="w-64 bg-[#0a1224] text-slate-300 flex flex-col flex-shrink-0 min-h-screen border-r border-slate-800 sticky top-0 h-screen overflow-y-auto">
        <div class="p-5 flex items-center space-x-3 border-b border-slate-800/80">
            <div class="w-9 h-9 rounded-xl bg-cyan-500 flex items-center justify-center text-slate-950 font-black text-base shadow-lg shadow-cyan-500/20">
                CT
            </div>
            <div>
                <span class="text-xl font-black text-white tracking-tight">Cargo<span class="text-cyan-400">Trace</span></span>
                <span class="block text-[10px] text-slate-400 font-semibold tracking-wider uppercase">Logistics System</span>
            </div>
        </div>

        <nav class="p-3 space-y-1 text-xs font-medium flex-1">
            <a href="#" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl bg-cyan-600/20 text-cyan-400 font-bold border border-cyan-500/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Shipments</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Tracking</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>Exceptions</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Carriers</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                <span>User & Config</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Reports</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <span>Audit Logs</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span>Notifications</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Currency</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                <span>World News</span>
            </a>
        </nav>
    </aside>

    <!-- KONTEN UTAMA MAIN DASHBOARD -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">

        <!-- Top Bar Navigation -->
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-40 shadow-xs">
            <div>
                <h1 class="text-lg font-black text-slate-800 tracking-tight">System Operational Dashboard</h1>
                <p class="text-xs text-slate-500">Global Logistics Tracking & Vessel Visibility Platform</p>
            </div>
            <div class="flex items-center space-x-4 text-xs">
                <div class="text-right">
                    <span class="block text-slate-400 text-[10px]">CURRENT USER</span>
                    <strong class="text-slate-800 font-bold"><?php echo isset($userName) ? $userName : 'Saimah Admin'; ?></strong>
                </div>
                <span class="bg-cyan-50 text-cyan-700 font-bold px-3 py-1 rounded-md border border-cyan-200 tracking-wide"><?php echo isset($userRole) ? $userRole : 'ADMINISTRATOR'; ?></span>
                <form method="POST" action="<?php echo route('logout'); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-1.5 rounded-lg text-xs shadow-xs transition">Logout</button>
                </form>
            </div>
        </header>

        <div class="p-8 space-y-6">

            <!-- METRIK KPI STAT CARDS -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Shipments</p>
                    <p class="text-2xl font-black text-slate-900 mt-1"><?php echo isset($totalShipments) ? $totalShipments : 5; ?></p>
                </div>
                <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs">
                    <p class="text-[11px] font-bold text-indigo-500 uppercase tracking-wider">In Transit</p>
                    <p class="text-2xl font-black text-indigo-600 mt-1"><?php echo isset($inTransit) ? $inTransit : 2; ?></p>
                </div>
                <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs">
                    <p class="text-[11px] font-bold text-emerald-500 uppercase tracking-wider">Delivered</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1"><?php echo isset($delivered) ? $delivered : 2; ?></p>
                </div>
                <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs">
                    <p class="text-[11px] font-bold text-rose-500 uppercase tracking-wider">Delayed</p>
                    <p class="text-2xl font-black text-rose-600 mt-1"><?php echo isset($delayed) ? $delayed : 0; ?></p>
                </div>
                <div class="bg-white p-4.5 rounded-2xl border border-slate-200/80 shadow-xs">
                    <p class="text-[11px] font-bold text-amber-500 uppercase tracking-wider">Open Exceptions</p>
                    <p class="text-2xl font-black text-amber-600 mt-1"><?php echo isset($openExceptions) ? $openExceptions : 1; ?></p>
                </div>
            </div>

            <!-- LIVE MULTI-CURRENCY EXCHANGE RATES -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Live Multi-Currency Exchange Rates (Base: USD to Target)</h3>
                    <span class="text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold px-2.5 py-0.5 rounded-md" id="currency-status">● Live Syncing...</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-8 gap-3 text-xs" id="currency-container">
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / IDR</span><p class="font-bold text-slate-900 mt-0.5" id="rate-IDR">Rp 15.850</p></div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / EUR</span><p class="font-bold text-slate-900 mt-0.5" id="rate-EUR">€ 0.92</p></div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / SGD</span><p class="font-bold text-slate-900 mt-0.5" id="rate-SGD">S$ 1.34</p></div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / JPY</span><p class="font-bold text-slate-900 mt-0.5" id="rate-JPY">¥ 153.4</p></div>
                    <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / GBP</span><p class="font-bold text-slate-900 mt-0.5" id="rate-GBP">£ 0.77</p></div>
                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / AUD</span><p class="font-bold text-slate-900 mt-0.5" id="rate-AUD">A$ 1.51</p></div>
                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / CNY</span><p class="font-bold text-slate-900 mt-0.5" id="rate-CNY">¥ 7.18</p></div>
                    <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100"><span class="text-slate-400 block text-[10px]">USD / MYR</span><p class="font-bold text-slate-900 mt-0.5" id="rate-MYR">RM 4.37</p></div>
                </div>
            </div>

            <!-- LIVE PORT WEATHER -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
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
                <div class="md:col-span-2 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-bold text-slate-800">Live Global Shipment Tracking Map</h3>
                        <span class="text-[10px] bg-indigo-50 text-indigo-700 font-bold px-2 py-0.5 rounded border border-indigo-200">OpenStreetMap Active</span>
                    </div>
                    <div id="map" style="height: 350px; width: 100%;" class="rounded-xl border border-slate-200"></div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                    <h3 class="text-sm font-bold text-slate-800 mb-3">Shipment Status Breakdown</h3>
                    <div style="height: 300px; width: 100%;" class="flex items-center justify-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- MARITIME NEWS BULLETIN -->
            <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
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
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-slate-800">Global Shipment Records</h3>
                    <span class="text-xs bg-slate-100 text-slate-600 font-bold px-2.5 py-0.5 rounded border border-slate-200">5 Records</span>
                </div>
                <table class="w-f