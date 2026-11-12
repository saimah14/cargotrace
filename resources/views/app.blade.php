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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased flex h-screen overflow-hidden">

    <!-- Sidebar 11 Menu -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col h-full shrink-0 z-50">
        <div class="p-4 flex items-center space-x-3 border-b border-slate-800">
            <div class="bg-cyan-500 text-white p-2 rounded-lg font-bold text-lg">CT</div>
            <div>
                <h1 class="font-bold text-white text-base">CargoTrace</h1>
                <p class="text-[10px] text-slate-400 uppercase">LOGISTICS SYSTEM</p>
            </div>
        </div>

        <nav class="flex-1 p-3 space-y-1 overflow-y-auto">
            <button type="button" onclick="switchTab('dashboard')" id="btn-dashboard" class="w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg bg-cyan-600 text-white font-medium cursor-pointer transition">
                <i class="bi bi-house-door text-lg"></i><span>Dashboard</span>
            </button>
            <button type="button" onclick="switchTab('shipments')" id="btn-shipments" class="w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 cursor-pointer transition">
                <i class="bi bi-box-seam text-lg"></i><span>Shipments</span>
            </button>
            <button type="button" onclick="switchTab('tracking')" id="btn-tracking" class="w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 cursor-pointer transition">
                <i class="bi bi-crosshair text-lg"></i><span>Tracking</span>
            </button>
            <button type="button" onclick="switchTab('exceptions')" id="btn-exceptions" class="w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 cursor-pointer transition">
                <i class="bi bi-exclamation-triangle text-lg"></i><span>Exceptions</span>
            </button>
            <button type="button" onclick="switchTab('carriers')" id="btn-carriers" class="w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 cursor-pointer transition">
                <i class="bi bi-truck text-lg"></i><span>Carriers</span>
            </button>
            <button type="button" onclick="switchTab('users')" id="btn-users" class="w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 cursor-pointer transition">
                <i class="bi bi-person-gear text-lg"></i><span>User & Config</span>
            </button>
            <button type="button" onclick="switchTab('reports')" id="btn-reports" class="w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 cursor-pointer transition">
                <i class="bi bi-graph-up-arrow text-lg"></i><span>Reports</span>
            </button>
            <button type="button" onclick="switchTab('audit-logs')" id="btn-audit-logs" class="w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 cursor-pointer transition">
                <i class="bi bi-journal-text text-lg"></i><span>Audit Logs</span>
            </button>
            <button type="button" onclick="switchTab('notifications')" id="btn-notifications" class="w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 cursor-pointer transition">
                <i class="bi bi-bell text-lg"></i><span>Notifications</span>
            </button>
            <button type="button" onclick="switchTab('currency')" id="btn-currency" class="w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 cursor-pointer transition">
                <i class="bi bi-currency-exchange text-lg"></i><span>Currency</span>
            </button>
            <button type="button" onclick="switchTab('world-news')" id="btn-world-news" class="w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 cursor-pointer transition">
                <i class="bi bi-globe text-lg"></i><span>World News</span>
            </button>
        </nav>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-y-auto bg-slate-50">
        <header class="bg-white border-b border-slate-200 px-6 py-4 flex justify-between items-center shadow-sm">
            <div>
                <h2 id="page-title" class="text-lg font-bold text-slate-800">System Operational Dashboard</h2>
                <p class="text-xs text-slate-500">Global Logistics Tracking & Vessel Visibility Platform</p>
            </div>
            <div class="text-right">
                <span class="text-[9px] text-slate-400 block uppercase font-semibold">CURRENT USER</span>
                <span class="font-bold text-slate-700 text-sm">Saimah (250180017)</span>
                <span class="bg-emerald-100 text-emerald-800 text-xs px-2 py-0.5 rounded font-bold ml-2">ADMINISTRATOR</span>
            </div>
        </header>

        <div class="p-6">
            <!-- 1. DASHBOARD -->
            <div id="tab-dashboard" class="space-y-6">
                <div class="grid grid-cols-5 gap-4">
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm"><p class="text-xs font-semibold text-slate-400">TOTAL SHIPMENTS</p><p class="text-2xl font-bold text-slate-800 mt-1">5</p></div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm"><p class="text-xs font-semibold text-cyan-600">IN TRANSIT</p><p class="text-2xl font-bold text-cyan-700 mt-1">3</p></div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm"><p class="text-xs font-semibold text-emerald-600">DELIVERED</p><p class="text-2xl font-bold text-emerald-700 mt-1">1</p></div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm"><p class="text-xs font-semibold text-rose-600">DELAYED</p><p class="text-2xl font-bold text-rose-700 mt-1">2</p></div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm"><p class="text-xs font-semibold text-amber-600">OPEN EXCEPTIONS</p><p class="text-2xl font-bold text-amber-700 mt-1">1</p></div>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    <div class="col-span-2 bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                        <h3 class="text-xs font-bold text-slate-500 mb-3">SHIPMENT STATUS ANALYTICS</h3>
                        <div class="h-48"><canvas id="shipmentChart"></canvas></div>
                    </div>
                    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                        <h3 class="text-xs font-bold text-slate-500 mb-3">PORT WEATHER</h3>
                        <div class="space-y-2 text-xs">
                            <div class="bg-slate-50 p-2 rounded flex justify-between"><span>Tanjung Priok</span><b>29.5°C</b></div>
                            <div class="bg-slate-50 p-2 rounded flex justify-between"><span>Singapore</span><b>30.1°C</b></div>
                            <div class="bg-slate-50 p-2 rounded flex justify-between"><span>Rotterdam</span><b>12.4°C</b></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
                    <h3 class="text-xs font-bold text-slate-500 mb-3">Live Global Shipment Tracking Map</h3>
                    <div id="map" class="w-full h-72 rounded-lg"></div>
                </div>
            </div>

            <!-- 2. SHIPMENTS -->
            <div id="tab-shipments" class="hidden bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base font-bold mb-4 text-slate-800">Master Data Shipments</h3>
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-600 border-b"><tr><th class="p-3">Shipment No</th><th class="p-3">Carrier</th><th class="p-3">Origin</th><th class="p-3">Destination</th><th class="p-3">Status</th></tr></thead>
                    <tbody class="divide-y">
                        <tr><td class="p-3 font-semibold text-cyan-600">CT-DEMO-0001</td><td class="p-3">Nusantara Ocean Line</td><td class="p-3">Tanjung Priok</td><td class="p-3">Rotterdam</td><td class="p-3"><span class="bg-cyan-100 text-cyan-800 px-2 py-1 rounded text-xs font-bold">IN TRANSIT</span></td></tr>
                    </tbody>
                </table>
            </div>

            <!-- 3. TRACKING -->
            <div id="tab-tracking" class="hidden bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base font-bold mb-4 text-slate-800">Live Vessel Tracking</h3>
                <div id="map-tracking" class="w-full h-96 rounded-lg"></div>
            </div>

            <!-- 4. EXCEPTIONS -->
            <div id="tab-exceptions" class="hidden bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base font-bold mb-4 text-slate-800">Shipment Exceptions</h3>
                <p class="text-sm text-slate-600">Typhoon warning near Port of Rotterdam (12 Hours delay).</p>
            </div>

            <!-- 5. CARRIERS -->
            <div id="tab-carriers" class="hidden bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base font-bold mb-4 text-slate-800">Logistics Carriers</h3>
                <p class="text-sm text-slate-600">1. Maersk Line | 2. Evergreen Marine | 3. Nusantara Ocean Line</p>
            </div>

            <!-- 6. USERS -->
            <div id="tab-users" class="hidden bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base font-bold mb-4 text-slate-800">User Management (3 Levels)</h3>
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-100 text-slate-600 border-b"><tr><th class="p-3">NIM / ID</th><th class="p-3">Name</th><th class="p-3">Role</th></tr></thead>
                    <tbody class="divide-y">
                        <tr><td class="p-3">250180017</td><td class="p-3 font-bold">Saimah</td><td class="p-3">Administrator</td></tr>
                        <tr><td class="p-3">250180888</td><td class="p-3 font-bold">Operator A1</td><td class="p-3">Logistics Operator</td></tr>
                        <tr><td class="p-3">250180999</td><td class="p-3 font-bold">Dosen Penguji</td><td class="p-3">Supervisor / Auditor</td></tr>
                    </tbody>
                </table>
            </div>

            <!-- 7. REPORTS -->
            <div id="tab-reports" class="hidden bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base font-bold mb-4 text-slate-800">Analytics Reports</h3>
                <p class="text-sm text-slate-600">Monthly Volume: 1,248 TEUs | On-Time Rate: 94.2%</p>
            </div>

            <!-- 8. AUDIT LOGS -->
            <div id="tab-audit-logs" class="hidden bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base font-bold mb-4 text-slate-800">Audit Logs</h3>
                <p class="text-sm text-slate-600">[OK] Saimah (250180017) authenticated successfully.</p>
            </div>

            <!-- 9. NOTIFICATIONS -->
            <div id="tab-notifications" class="hidden bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base font-bold mb-4 text-slate-800">Notifications</h3>
                <p class="text-sm text-amber-800 bg-amber-50 p-3 rounded">⚠️ Weather Alert: Typhoon warning near Rotterdam.</p>
            </div>

            <!-- 10. CURRENCY -->
            <div id="tab-currency" class="hidden bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base font-bold mb-4 text-slate-800">Currency Exchange Management</h3>
                <p class="text-sm text-slate-600">USD/IDR: Rp 15.850 | USD/EUR: € 0.92 (Live Sync)</p>
            </div>

            <!-- 11. WORLD NEWS -->
            <div id="tab-world-news" class="hidden bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
                <h3 class="text-base font-bold mb-4 text-slate-800">World News</h3>
                <p class="text-sm text-slate-600">Global Supply Chain Normalizes in Q4 2026.</p>
            </div>
        </div>
    </main>

    <script>
        let map = null;
        let trackingMap = null;
        let chartInstance = null;

        function initDashboard() {
            if (!map) {
                map = L.map('map').setView([2.5, 108.0], 5);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                L.marker([-6.1018, 106.8833]).addTo(map).bindPopup("<b>Tanjung Priok, Jakarta</b>");
                L.marker([1.29027, 103.851959]).addTo(map).bindPopup("<b>Singapore Port</b>");
                L.polyline([[-6.1018, 106.8833], [1.29027, 103.851959], [3.5, 107.0], [6.0, 110.0]], {color: '#0891b2', weight: 3, dashArray: '5, 5'}).addTo(map);
            } else {
                map.invalidateSize();
            }

            if (!chartInstance) {
                const ctx = document.getElementById('shipmentChart').getContext('2d');
                chartInstance = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['In Transit', 'Delivered', 'Delayed', 'Exceptions'],
                        datasets: [{
                            label: 'Volume',
                            data: [3, 1, 2, 1],
                            backgroundColor: ['#0891b2', '#059669', '#e11d48', '#d97706']
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
                });
            }
        }

        function switchTab(tabName) {
            const tabs = ['dashboard', 'shipments', 'tracking', 'exceptions', 'carriers', 'users', 'reports', 'audit-logs', 'notifications', 'currency', 'world-news'];
            
            tabs.forEach(t => {
                const el = document.getElementById('tab-' + t);
                if (el) el.classList.add('hidden');
                const btn = document.getElementById('btn-' + t);
                if (btn) {
                    btn.className = "w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg hover:bg-slate-800 text-slate-300 cursor-pointer transition";
                }
            });

            const activeTab = document.getElementById('tab-' + tabName);
            if (activeTab) activeTab.classList.remove('hidden');

            const activeBtn = document.getElementById('btn-' + tabName);
            if (activeBtn) {
                activeBtn.className = "w-full text-left flex items-center space-x-3 px-3 py-2.5 rounded-lg bg-cyan-600 text-white font-medium cursor-pointer transition";
            }

            document.getElementById('page-title').innerText = tabName.replace('-', ' ').toUpperCase() + " Management";

            if (tabName === 'dashboard') {
                setTimeout(initDashboard, 50);
            }

            if (tabName === 'tracking') {
                setTimeout(() => {
                    if (!trackingMap) {
                        trackingMap = L.map('map-tracking').setView([2.5, 108.0], 5);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(trackingMap);
                        L.marker([-6.1018, 106.8833]).addTo(trackingMap).bindPopup("<b>Tanjung Priok, Jakarta</b>");
                        L.marker([1.29027, 103.851959]).addTo(trackingMap).bindPopup("<b>Singapore Port</b>");
                        L.polyline([[-6.1018, 106.8833], [1.29027, 103.851959], [3.5, 107.0], [6.0, 110.0]], {color: '#0891b2', weight: 3, dashArray: '5, 5'}).addTo(trackingMap);
                    } else {
                        trackingMap.invalidateSize();
                    }
                }, 50);
            }
        }

        window.onload = function() {
            initDashboard();
        };
    </script>
</body>
</html>