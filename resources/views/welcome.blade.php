<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CargoTrace - Global Shipment Visibility Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased flex h-screen overflow-hidden">

    <!-- Sidebar 11 Menu Lengkap -->
    <aside class="w-64 bg-[#0f172a] text-slate-300 flex flex-col shrink-0 overflow-y-auto">
        <div class="p-4 flex items-center space-x-3 border-b border-slate-800">
            <div class="bg-cyan-500 text-white p-2 rounded-lg font-bold text-xl">CT</div>
            <div>
                <h1 class="font-bold text-white tracking-wide text-lg">CargoTrace</h1>
                <p class="text-[10px] text-slate-400 uppercase font-semibold">LOGISTICS SYSTEM</p>
            </div>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            <a href="/dashboard" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg bg-cyan-600 text-white font-medium">
                <i class="bi bi-house-door text-lg"></i><span>Dashboard</span>
            </a>
            <a href="/shipments" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
                <i class="bi bi-box-seam text-lg"></i><span>Shipments</span>
            </a>
            <a href="/tracking" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
                <i class="bi bi-crosshair text-lg"></i><span>Tracking</span>
            </a>
            <a href="/exceptions" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
                <i class="bi bi-exclamation-triangle text-lg"></i><span>Exceptions</span>
            </a>
            <a href="/carriers" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
                <i class="bi bi-truck text-lg"></i><span>Carriers</span>
            </a>
            <a href="/users" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
                <i class="bi bi-person-gear text-lg"></i><span>User & Config</span>
            </a>
            <a href="/reports" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
                <i class="bi bi-graph-up-arrow text-lg"></i><span>Reports</span>
            </a>
            <a href="/audit-logs" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
                <i class="bi bi-journal-text text-lg"></i><span>Audit Logs</span>
            </a>
            <a href="/notifications" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
                <i class="bi bi-bell text-lg"></i><span>Notifications</span>
            </a>
            <a href="/currency" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
                <i class="bi bi-currency-exchange text-lg"></i><span>Currency</span>
            </a>
            <a href="/world-news" class="flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 transition">
                <i class="bi bi-globe text-lg"></i><span>World News</span>
            </a>
        </nav>
    </aside>

    <!-- Header & Konten utama -->
    <main class="flex-1 flex flex-col overflow-y-auto bg-slate-50">
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center shadow-sm">
            <div>
                <h2 class="text-xl font-bold text-slate-800">System Operational Dashboard</h2>
                <p class="text-xs text-slate-500">Global Logistics Tracking & Vessel Visibility Platform</p>
            </div>
            <div class="text-right">
                <span class="text-[10px] text-slate-400 block uppercase tracking-wider font-semibold">CURRENT USER</span>
                <span class="font-bold text-slate-700 text-sm">Saimah Admin</span>
                <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-0.5 rounded font-bold ml-2">ADMINISTRATOR</span>
            </div>
        </header>

        <div class="p-6 space-y-6">
            <!-- 5 Stats Cards -->
            <div class="grid grid-cols-5 gap-4">
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-slate-400 uppercase">TOTAL SHIPMENTS</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">5</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-cyan-600 uppercase">IN TRANSIT</p>
                    <p class="text-2xl font-bold text-cyan-700 mt-1">3</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-emerald-600 uppercase">DELIVERED</p>
                    <p class="text-2xl font-bold text-emerald-700 mt-1">1</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-rose-600 uppercase">DELAYED</p>
                    <p class="text-2xl font-bold text-rose-700 mt-1">2</p>
                </div>
                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <p class="text-xs font-semibold text-amber-600 uppercase">OPEN EXCEPTIONS</p>
                    <p class="text-2xl font-bold text-amber-700 mt-1">1</p>
                </div>
            </div>

            <!-- Currency Rates Widget -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">LIVE MULTI-CURRENCY EXCHANGE RATES (BASE: USD TO TARGET)</h3>
                <div class="grid grid-cols-8 gap-2 text-center text-xs">
                    <div class="bg-slate-50 p-2 rounded border border-slate-100"><span class="block text-slate-400">USD/IDR</span><span class="font-bold text-slate-700">Rp 15.850</span></div>
                    <div class="bg-slate-50 p-2 rounded border border-slate-100"><span class="block text-slate-400">USD/EUR</span><span class="font-bold text-slate-700">€ 0.92</span></div>
                    <div class="bg-slate-50 p-2 rounded border border-slate-100"><span class="block text-slate-400">USD/SGD</span><span class="font-bold text-slate-700">S$ 1.34</span></div>
                    <div class="bg-slate-50 p-2 rounded border border-slate-100"><span class="block text-slate-400">USD/JPY</span><span class="font-bold text-slate-700">¥ 153.4</span></div>
                    <div class="bg-slate-50 p-2 rounded border border-slate-100"><span class="block text-slate-400">USD/GBP</span><span class="font-bold text-slate-700">£ 0.77</span></div>
                    <div class="bg-slate-50 p-2 rounded border border-slate-100"><span class="block text-slate-400">USD/AUD</span><span class="font-bold text-slate-700">A$ 1.51</span></div>
                    <div class="bg-slate-50 p-2 rounded border border-slate-100"><span class="block text-slate-400">USD/CNY</span><span class="font-bold text-slate-700">¥ 7.18</span></div>
                    <div class="bg-slate-50 p-2 rounded border border-slate-100"><span class="block text-slate-400">USD/MYR</span><span class="font-bold text-slate-700">RM 4.37</span></div>
                </div>
            </div>

            <!-- Global Port Weather Widget -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">LIVE GLOBAL PORT WEATHER CONDITIONS</h3>
                <div class="grid grid-cols-4 gap-4 text-xs">
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-slate-700">Tanjung Priok (Jakarta)</p>
                            <p class="text-slate-400 text-[10px]">Global Maritime Hub</p>
                        </div>
                        <div class="text-right"><span class="text-lg font-bold text-cyan-600">29.5°C</span><p class="text-slate-400 text-[10px]">Partly Cloudy</p></div>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-slate-700">Singapore Port</p>
                            <p class="text-slate-400 text-[10px]">Global Maritime Hub</p>
                        </div>
                        <div class="text-right"><span class="text-lg font-bold text-cyan-600">30.1°C</span><p class="text-slate-400 text-[10px]">Thundershower</p></div>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-slate-700">Port of Rotterdam</p>
                            <p class="text-slate-400 text-[10px]">European Gateway</p>
                        </div>
                        <div class="text-right"><span class="text-lg font-bold text-cyan-600">12.4°C</span><p class="text-slate-400 text-[10px]">Overcast</p></div>
                    </div>
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-slate-700">Port of Hamburg</p>
                            <p class="text-slate-400 text-[10px]">European Hub</p>
                        </div>
                        <div class="text-right"><span class="text-lg font-bold text-cyan-600">10.8°C</span><p class="text-slate-400 text-[10px]">Overcast</p></div>
                    </div>
                </div>
            </div>

            <!-- Leaflet Tracking Map -->
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Live Global Shipment Tracking Map</h3>
                <div id="map" class="w-full h-80 rounded-lg"></div>
            </div>
        </div>
    </main>

    <script>
        const map = L.map('map').setView([-2.5489, 118.0149], 4);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        L.marker([-6.1018, 106.8833]).addTo(map).bindPopup("<b>Tanjung Priok, Jakarta</b><br>Shipment #CT-DEMO-0001").openPopup();
        L.marker([1.29027, 103.851959]).addTo(map).bindPopup("<b>Singapore Port</b><br>Shipment #CT-DEMO-0002");
        L.marker([51.9244, 4.4777]).addTo(map).bindPopup("<b>Port of Rotterdam</b>");
    </script>
</body>
</html>