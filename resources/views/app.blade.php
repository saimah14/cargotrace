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
<body class="bg-slate-100 text-slate-800 font-sans antialiased h-screen w-screen overflow-hidden flex">

    <!-- Sidebar 11 Menu Lengkap -->
    <div style="width: 260px; background-color: #0f172a; color: #cbd5e1; height: 100vh; overflow-y: auto; display: flex; flex-direction: column; z-index: 9999; position: relative;">
        <div style="padding: 16px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid #1e293b;">
            <div style="background: #06b6d4; color: white; padding: 8px; border-radius: 8px; font-weight: bold;">CT</div>
            <div>
                <h1 style="font-weight: bold; color: white; font-size: 16px; margin: 0;">CargoTrace</h1>
                <p style="font-size: 10px; color: #94a3b8; margin: 0; text-transform: uppercase;">LOGISTICS SYSTEM</p>
            </div>
        </div>

        <div style="padding: 16px; display: flex; flex-direction: column; gap: 4px;">
            <button onclick="switchTab('dashboard')" id="btn-dashboard" style="width: 100%; text-align: left; padding: 10px 12px; border-radius: 8px; background: #0891b2; color: white; border: none; cursor: pointer; font-weight: 500; display: flex; align-items: center; gap: 10px;"><i class="bi bi-house-door"></i> Dashboard</button>
            <button onclick="switchTab('shipments')" id="btn-shipments" style="width: 100%; text-align: left; padding: 10px 12px; border-radius: 8px; background: transparent; color: #cbd5e1; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px;"><i class="bi bi-box-seam"></i> Shipments</button>
            <button onclick="switchTab('tracking')" id="btn-tracking" style="width: 100%; text-align: left; padding: 10px 12px; border-radius: 8px; background: transparent; color: #cbd5e1; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px;"><i class="bi bi-crosshair"></i> Tracking</button>
            <button onclick="switchTab('exceptions')" id="btn-exceptions" style="width: 100%; text-align: left; padding: 10px 12px; border-radius: 8px; background: transparent; color: #cbd5e1; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px;"><i class="bi bi-exclamation-triangle"></i> Exceptions</button>
            <button onclick="switchTab('carriers')" id="btn-carriers" style="width: 100%; text-align: left; padding: 10px 12px; border-radius: 8px; background: transparent; color: #cbd5e1; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px;"><i class="bi bi-truck"></i> Carriers</button>
            <button onclick="switchTab('users')" id="btn-users" style="width: 100%; text-align: left; padding: 10px 12px; border-radius: 8px; background: transparent; color: #cbd5e1; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px;"><i class="bi bi-person-gear"></i> User & Config</button>
            <button onclick="switchTab('reports')" id="btn-reports" style="width: 100%; text-align: left; padding: 10px 12px; border-radius: 8px; background: transparent; color: #cbd5e1; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px;"><i class="bi bi-graph-up-arrow"></i> Reports</button>
            <button onclick="switchTab('audit-logs')" id="btn-audit-logs" style="width: 100%; text-align: left; padding: 10px 12px; border-radius: 8px; background: transparent; color: #cbd5e1; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px;"><i class="bi bi-journal-text"></i> Audit Logs</button>
            <button onclick="switchTab('notifications')" id="btn-notifications" style="width: 100%; text-align: left; padding: 10px 12px; border-radius: 8px; background: transparent; color: #cbd5e1; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px;"><i class="bi bi-bell"></i> Notifications</button>
            <button onclick="switchTab('currency')" id="btn-currency" style="width: 100%; text-align: left; padding: 10px 12px; border-radius: 8px; background: transparent; color: #cbd5e1; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px;"><i class="bi bi-currency-exchange"></i> Currency</button>
            <button onclick="switchTab('world-news')" id="btn-world-news" style="width: 100%; text-align: left; padding: 10px 12px; border-radius: 8px; background: transparent; color: #cbd5e1; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px;"><i class="bi bi-globe"></i> World News</button>
        </div>
    </div>

    <!-- Main Content Area -->
    <div style="flex: 1; display: flex; flex-direction: column; height: 100vh; overflow-y: auto; background: #f8fafc;">
        <header style="background: white; border-bottom: 1px solid #e2e8f0; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2 id="page-title" style="font-size: 18px; font-weight: bold; color: #1e293b; margin: 0;">System Operational Dashboard</h2>
                <p style="font-size: 11px; color: #64748b; margin: 0;">Global Logistics Tracking & Vessel Visibility Platform</p>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 9px; color: #94a3b8; display: block; text-transform: uppercase; font-weight: 600;">CURRENT USER</span>
                <span style="font-weight: bold; color: #334155; font-size: 13px;">Saimah Admin</span>
                <span style="background: #d1fae5; color: #065f46; font-size: 11px; padding: 2px 6px; border-radius: 4px; font-weight: bold; margin-left: 6px;">ADMINISTRATOR</span>
            </div>
        </header>

        <div style="padding: 24px;">
            <!-- 1. DASHBOARD -->
            <div id="tab-dashboard" style="display: block;">
                <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; margin-bottom: 24px;">
                    <div style="background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;"><p style="font-size: 11px; color: #94a3b8; font-weight: bold; margin: 0;">TOTAL SHIPMENTS</p><p style="font-size: 22px; font-weight: bold; color: #1e293b; margin: 4px 0 0 0;">5</p></div>
                    <div style="background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;"><p style="font-size: 11px; color: #0891b2; font-weight: bold; margin: 0;">IN TRANSIT</p><p style="font-size: 22px; font-weight: bold; color: #0e7490; margin: 4px 0 0 0;">3</p></div>
                    <div style="background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;"><p style="font-size: 11px; color: #059669; font-weight: bold; margin: 0;">DELIVERED</p><p style="font-size: 22px; font-weight: bold; color: #047857; margin: 4px 0 0 0;">1</p></div>
                    <div style="background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;"><p style="font-size: 11px; color: #e11d48; font-weight: bold; margin: 0;">DELAYED</p><p style="font-size: 22px; font-weight: bold; color: #be123c; margin: 4px 0 0 0;">2</p></div>
                    <div style="background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;"><p style="font-size: 11px; color: #d97706; font-weight: bold; margin: 0;">OPEN EXCEPTIONS</p><p style="font-size: 22px; font-weight: bold; color: #b45309; margin: 4px 0 0 0;">1</p></div>
                </div>
                <div style="background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 24px;">
                    <h3 style="font-size: 11px; font-weight: bold; color: #64748b; margin-bottom: 12px;">LIVE MULTI-CURRENCY EXCHANGE RATES</h3>
                    <div style="display: grid; grid-template-columns: repeat(8, 1fr); gap: 8px; text-align: center; font-size: 11px;">
                        <div style="background: #f8fafc; padding: 8px; border-radius: 6px;"><span style="color: #94a3b8; display: block;">USD/IDR</span><b style="color: #334155;">Rp 15.850</b></div>
                        <div style="background: #f8fafc; padding: 8px; border-radius: 6px;"><span style="color: #94a3b8; display: block;">USD/EUR</span><b style="color: #334155;">€ 0.92</b></div>
                        <div style="background: #f8fafc; padding: 8px; border-radius: 6px;"><span style="color: #94a3b8; display: block;">USD/SGD</span><b style="color: #334155;">S$ 1.34</b></div>
                        <div style="background: #f8fafc; padding: 8px; border-radius: 6px;"><span style="color: #94a3b8; display: block;">USD/JPY</span><b style="color: #334155;">¥ 153.4</b></div>
                        <div style="background: #f8fafc; padding: 8px; border-radius: 6px;"><span style="color: #94a3b8; display: block;">USD/GBP</span><b style="color: #334155;">£ 0.77</b></div>
                        <div style="background: #f8fafc; padding: 8px; border-radius: 6px;"><span style="color: #94a3b8; display: block;">USD/AUD</span><b style="color: #334155;">A$ 1.51</b></div>
                        <div style="background: #f8fafc; padding: 8px; border-radius: 6px;"><span style="color: #94a3b8; display: block;">USD/CNY</span><b style="color: #334155;">¥ 7.18</b></div>
                        <div style="background: #f8fafc; padding: 8px; border-radius: 6px;"><span style="color: #94a3b8; display: block;">USD/MYR</span><b style="color: #334155;">RM 4.37</b></div>
                    </div>
                </div>
                <div style="background: white; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 11px; font-weight: bold; color: #64748b; margin-bottom: 12px;">Live Global Shipment Tracking Map</h3>
                    <div id="map" style="width: 100%; height: 320px; border-radius: 8px;"></div>
                </div>
            </div>

            <!-- 2. SHIPMENTS -->
            <div id="tab-shipments" style="display: none;">
                <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;">Master Data Shipments</h3>
                    <table style="width: 100%; text-align: left; font-size: 13px; border-collapse: collapse;">
                        <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;"><th style="padding: 10px;">Shipment No</th><th style="padding: 10px;">Carrier</th><th style="padding: 10px;">Origin</th><th style="padding: 10px;">Destination</th><th style="padding: 10px;">Status</th></tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 10px; color: #0891b2; font-weight: bold;">CT-DEMO-0001</td><td style="padding: 10px;">Nusantara Ocean Line</td><td style="padding: 10px;">Tanjung Priok</td><td style="padding: 10px;">Rotterdam</td><td style="padding: 10px;"><span style="background: #cffafe; color: #155e75; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">IN TRANSIT</span></td></tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 10px; color: #0891b2; font-weight: bold;">CT-DEMO-0002</td><td style="padding: 10px;">Evergreen Marine</td><td style="padding: 10px;">Singapore</td><td style="padding: 10px;">Hamburg</td><td style="padding: 10px;"><span style="background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 11px;">DELIVERED</span></td></tr>
                    </table>
                </div>
            </div>

            <!-- 3. TRACKING -->
            <div id="tab-tracking" style="display: none;">
                <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;">Live Vessel Tracking</h3>
                    <div id="map-tracking" style="width: 100%; height: 400px; border-radius: 8px;"></div>
                </div>
            </div>

            <!-- 4. EXCEPTIONS -->
            <div id="tab-exceptions" style="display: none;">
                <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;">Shipment Exceptions</h3>
                    <table style="width: 100%; text-align: left; font-size: 13px; border-collapse: collapse;">
                        <tr style="background: #f1f5f9; border-bottom: 1px solid #cbd5e1;"><th style="padding: 10px;">Shipment No</th><th style="padding: 10px;">Type</th><th style="padding: 10px;">Severity</th><th style="padding: 10px;">Delay</th></tr>
                        <tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 10px; font-weight: bold;">CT-DEMO-0001</td><td style="padding: 10px;">Typhoon Weather</td><td style="padding: 10px;"><span style="background: #ffe4e6; color: #9f1239; padding: 2px 6px; border-radius: 4px; font-weight: bold;">HIGH</span></td><td style="padding: 10px;">12 Hours</td></tr>
                    </table>
                </div>
            </div>

            <!-- 5. CARRIERS -->
            <div id="tab-carriers" style="display: none;">
                <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;">Logistics Carriers</h3>
                    <p style="font-size: 13px; color: #475569;">1. Maersk Line (MAEU)<br>2. Evergreen Marine (EGLV)<br>3. Nusantara Ocean Line (NOL)</p>
                </div>
            </div>

            <!-- 6. USERS -->
            <div id="tab-users" style="display: none;">
                <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;">User & Config</h3>
                    <p style="font-size: 13px; color: #475569;">Administrator: Saimah Admin (admin@cargotrace.com)</p>
                </div>
            </div>

            <!-- 7. REPORTS -->
            <div id="tab-reports" style="display: none;">
                <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;">Analytics Reports</h3>
                    <p style="font-size: 13px; color: #475569;">Monthly Volume: 1,248 TEUs | On-Time Rate: 94.2%</p>
                </div>
            </div>

            <!-- 8. AUDIT LOGS -->
            <div id="tab-audit-logs" style="display: none;">
                <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;">Audit Logs</h3>
                    <p style="font-size: 13px; color: #475569;">[OK] Saimah Admin logged in successfully.</p>
                </div>
            </div>

            <!-- 9. NOTIFICATIONS -->
            <div id="tab-notifications" style="display: none;">
                <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;">Notifications</h3>
                    <p style="font-size: 13px; color: #b45309; background: #fef3c7; padding: 10px; border-radius: 6px;">⚠️ Weather Alert: Typhoon warning near Rotterdam.</p>
                </div>
            </div>

            <!-- 10. CURRENCY -->
            <div id="tab-currency" style="display: none;">
                <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;">Currency Exchange Management</h3>
                    <p style="font-size: 13px; color: #475569;">Live sync active with base USD.</p>
                </div>
            </div>

            <!-- 11. WORLD NEWS -->
            <div id="tab-world-news" style="display: none;">
                <div style="background: white; padding: 24px; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 16px;">World News</h3>
                    <p style="font-size: 13px; color: #475569;">Global Supply Chain Normalizes in Q4 2026.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let mapInitialized = false;
        let trackingMapInit = false;

        function initMap() {
            if (!mapInitialized) {
                const map = L.map('map').setView([-2.5489, 118.0149], 4);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                L.marker([-6.1018, 106.8833]).addTo(map).bindPopup("<b>Tanjung Priok, Jakarta</b>").openPopup();
                L.marker([1.29027, 103.851959]).addTo(map).bindPopup("<b>Singapore Port</b>");
                mapInitialized = true;
            }
        }

        function switchTab(tabName) {
            const tabs = ['dashboard', 'shipments', 'tracking', 'exceptions', 'carriers', 'users', 'reports', 'audit-logs', 'notifications', 'currency', 'world-news'];
            tabs.forEach(t => {
                const el = document.getElementById('tab-' + t);
                if (el) el.style.display = 'none';
            });

            tabs.forEach(t => {
                const btn = document.getElementById('btn-' + t);
                if (btn) {
                    btn.style.background = 'transparent';
                    btn.style.color = '#cbd5e1';
                }
            });

            const activeTab = document.getElementById('tab-' + tabName);
            if (activeTab) activeTab.style.display = 'block';

            const activeBtn = document.getElementById('btn-' + tabName);
            if (activeBtn) {
                activeBtn.style.background = '#0891b2';
                activeBtn.style.color = 'white';
            }

            document.getElementById('page-title').innerText = tabName.replace('-', ' ').toUpperCase() + " Management";

            if (tabName === 'tracking' && !trackingMapInit) {
                setTimeout(() => {
                    const tMap = L.map('map-tracking').setView([-2.5489, 118.0149], 4);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(tMap);
                    L.marker([-6.1018, 106.8833]).addTo(tMap).bindPopup("<b>Tanjung Priok, Jakarta</b>");
                    trackingMapInit = true;
                }, 200);
            }
        }

        window.onload = function() {
            initMap();
        };
    </script>
</body>
</html>