<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CargoTrace - Global Shipment Visibility Platform</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:sans-serif;background:#f1f5f9;color:#1e293b;display:flex;height:100vh;width:100vw;overflow:hidden}
        aside{width:240px;min-width:240px;background:#0f172a;color:#cbd5e1;height:100vh;display:flex;flex-direction:column;z-index:9999}
        .sh{padding:16px;display:flex;align-items:center;gap:12px;border-bottom:1px solid #1e293b}
        .lb{background:#06b6d4;color:#fff;padding:8px 12px;border-radius:8px;font-weight:bold}
        .sn{flex:1;padding:10px;overflow-y:auto;display:flex;flex-direction:column;gap:4px}
        .nb{width:100%;text-align:left;padding:9px 12px;border-radius:8px;background:transparent;color:#cbd5e1;border:none;cursor:pointer;font-size:13px;display:flex;align-items:center;gap:10px}
        .nb:hover{background:#1e293b;color:#fff}
        .nb.active{background:#0891b2;color:#fff;font-weight:bold}
        main{flex:1;display:flex;flex-direction:column;height:100vh;overflow-y:auto;background:#f8fafc}
        header{background:#fff;border-bottom:1px solid #e2e8f0;padding:14px 24px;display:flex;justify-content:space-between;align-items:center}
        .cb{padding:20px 24px}
        .g5{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:18px}
        .g3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px}
        .gd{display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:18px}
        .c{background:#fff;padding:16px;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:16px}
        .sc{background:#f8fafc;padding:12px;border:1px solid #e2e8f0;border-radius:8px}
        .nl{display:block;text-decoration:none;color:inherit;background:#f8fafc;padding:12px 14px;border:1px solid #e2e8f0;border-radius:8px;transition:.2s}
        .nl:hover{border-color:#0891b2;background:#f0f9ff}
        table{width:100%;border-collapse:collapse;font-size:13px;text-align:left}
        th,td{padding:9px 12px;border-bottom:1px solid #e2e8f0}
        th{background:#f1f5f9;color:#475569;font-weight:bold}
        .b{padding:3px 8px;border-radius:4px;font-size:11px;font-weight:bold;display:inline-block}
        .bc{background:#cffafe;color:#155e75}.bg{background:#d1fae5;color:#065f46}.br{background:#ffe4e6;color:#9f1239}.ba{background:#fef3c7;color:#b45309}
        .i-ship{background:#0891b2;color:#fff;border-radius:50%;text-align:center;line-height:28px;font-size:14px;border:2px solid #fff;box-shadow:0 2px 5px rgba(0,0,0,.3)}
        .i-plane{background:#059669;color:#fff;border-radius:50%;text-align:center;line-height:28px;font-size:14px;border:2px solid #fff;box-shadow:0 2px 5px rgba(0,0,0,.3)}
        .tp{display:none}.tp.active{display:block}
    </style>
</head>
<body>
    <aside>
        <div class="sh"><div class="lb">CT</div><div><h1 style="color:#fff;font-size:15px">CargoTrace</h1><p style="font-size:9px;color:#94a3b8">LOGISTICS SYSTEM</p></div></div>
        <nav class="sn">
            <button type="button" onclick="switchTab('dashboard')" id="btn-dashboard" class="nb active"><i class="bi bi-house-door"></i><span>Dashboard</span></button>
            <button type="button" onclick="switchTab('shipments')" id="btn-shipments" class="nb"><i class="bi bi-box-seam"></i><span>Shipments</span></button>
            <button type="button" onclick="switchTab('tracking')" id="btn-tracking" class="nb"><i class="bi bi-crosshair"></i><span>Tracking</span></button>
            <button type="button" onclick="switchTab('exceptions')" id="btn-exceptions" class="nb"><i class="bi bi-exclamation-triangle"></i><span>Exceptions</span></button>
            <button type="button" onclick="switchTab('carriers')" id="btn-carriers" class="nb"><i class="bi bi-truck"></i><span>Carriers</span></button>
            <button type="button" onclick="switchTab('reports')" id="btn-reports" class="nb"><i class="bi bi-graph-up-arrow"></i><span>Reports</span></button>
            <button type="button" onclick="switchTab('currency')" id="btn-currency" class="nb"><i class="bi bi-currency-exchange"></i><span>Currency</span></button>
            <button type="button" onclick="switchTab('audit-logs')" id="btn-audit-logs" class="nb"><i class="bi bi-journal-text"></i><span>Audit Logs</span></button>
            <button type="button" onclick="switchTab('notifications')" id="btn-notifications" class="nb"><i class="bi bi-bell"></i><span>Notifications</span></button>
            <button type="button" onclick="switchTab('world-news')" id="btn-world-news" class="nb"><i class="bi bi-globe"></i><span>World News</span></button>
            <button type="button" onclick="switchTab('weather')" id="btn-weather" class="nb"><i class="bi bi-cloud-sun"></i><span>Weather</span></button>
            <button type="button" onclick="switchTab('users')" id="btn-users" class="nb"><i class="bi bi-person-gear"></i><span>User & Config</span></button>
        </nav>
    </aside>

    <main>
        <header>
            <div><h2 id="page-title" style="font-size:16px">Dashboard Management</h2><p style="font-size:11px;color:#64748b">Global Sea & Air Cargo Visibility Platform</p></div>
            <div style="display:flex;align-items:center;gap:10px">
                <b id="user-name">Saimah Admin (250180017)</b> <span id="user-role" class="b bg">ADMINISTRATOR</span>
                <button type="button" onclick="handleLogout()" style="background:#ffe4e6;color:#9f1239;border:1px solid #fecdd3;padding:5px 12px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </div>
        </header>

        <div class="cb">
            <!-- 1. DASHBOARD -->
            <div id="tab-dashboard" class="tp active">
                <div id="role-banner" class="bg" style="width:100%;padding:8px 14px;border-radius:8px;margin-bottom:14px;font-size:12px">🔑 Mode Akses: <b>Administrator</b> — Akses penuh ke seluruh 12 modul sistem, keuangan, & audit log.</div>
                <div class="g5">
                    <div class="c"><p style="font-size:11px;color:#64748b">TOTAL SHIPMENTS</p><p style="font-size:22px;font-weight:bold">128</p></div>
                    <div class="c"><p style="font-size:11px;color:#0891b2">IN TRANSIT</p><p style="font-size:22px;font-weight:bold;color:#0e7490">84</p></div>
                    <div class="c"><p style="font-size:11px;color:#059669">DELIVERED</p><p style="font-size:22px;font-weight:bold;color:#047857">36</p></div>
                    <div class="c"><p style="font-size:11px;color:#e11d48">DELAYED</p><p style="font-size:22px;font-weight:bold;color:#be123c">8</p></div>
                    <div class="c"><p style="font-size:11px;color:#d97706">EXCEPTIONS</p><p style="font-size:22px;font-weight:bold;color:#b45309">4</p></div>
                </div>
                <div class="gd">
                    <div class="c"><h3 style="font-size:12px;color:#475569;margin-bottom:10px">SHIPMENT ANALYTICS (Q4 2026)</h3><div style="height:165px"><canvas id="shipmentChart"></canvas></div></div>
                    <div class="c"><h3 style="font-size:12px;color:#475569;margin-bottom:10px">PORT & HUB WEATHER</h3>
                        <div style="font-size:12px;display:flex;flex-direction:column;gap:7px">
                            <div class="sc" style="padding:7px;display:flex;justify-content:space-between"><span>Tanjung Priok, ID</span><b style="color:#0891b2">29.5°C Cerah</b></div>
                            <div class="sc" style="padding:7px;display:flex;justify-content:space-between"><span>Singapore Hub</span><b style="color:#0891b2">30.1°C Normal</b></div>
                            <div class="sc" style="padding:7px;display:flex;justify-content:space-between"><span>Rotterdam Port</span><b style="color:#e11d48">12.4°C Badai</b></div>
                            <div class="sc" style="padding:7px;display:flex;justify-content:space-between"><span>Narita Cargo Hub</span><b style="color:#059669">16.8°C Cerah</b></div>
                        </div>
                    </div>
                </div>
                <div class="c">
                    <h3 style="font-size:12px;color:#475569;margin-bottom:8px">Live Global Tracking Map (Sea Vessel & Air Cargo)</h3>
                    <div id="map" style="width:100%;height:260px;border-radius:8px;margin-bottom:8px"></div>
                    <div style="display:flex;gap:18px;font-size:12px;color:#475569">
                        <span><i class="bi bi-ship" style="color:#0891b2"></i> Kapal Kargo (Vessel)</span>
                        <span><i class="bi bi-airplane" style="color:#059669"></i> Pesawat Kargo (Air Freight)</span>
                        <span><b style="color:#0891b2">---</b> Jalur Laut</span>
                        <span><b style="color:#059669">---</b> Jalur Udara</span>
                    </div>
                </div>
            </div>
            <!-- 2. SHIPMENTS -->
            <div id="tab-shipments" class="tp"><div class="c"><h3 style="margin-bottom:12px">Master Data Shipments (Sea & Air)</h3>
                <table>
                    <tr><th>No</th><th>Mode</th><th>Carrier</th><th>Origin</th><th>Destination</th><th>ETA</th><th>Status</th></tr>
                    <tr><td><b>CT-2026-001</b></td><td><i class="bi bi-ship" style="color:#0891b2"></i> Sea</td><td>Nusantara Ocean</td><td>Tanjung Priok</td><td>Rotterdam</td><td>2026-11-28</td><td><span class="b bc">IN TRANSIT</span></td></tr>
                    <tr><td><b>CT-2026-002</b></td><td><i class="bi bi-airplane" style="color:#059669"></i> Air</td><td>Garuda Cargo</td><td>Soekarno-Hatta</td><td>Narita Tokyo</td><td>2026-11-13</td><td><span class="b bc">AIRBORNE</span></td></tr>
                    <tr><td><b>CT-2026-003</b></td><td><i class="bi bi-ship" style="color:#0891b2"></i> Sea</td><td>Maersk Line</td><td>Surabaya</td><td>Shanghai</td><td>2026-11-19</td><td><span class="b br">DELAYED</span></td></tr>
                    <tr><td><b>CT-2026-004</b></td><td><i class="bi bi-airplane" style="color:#059669"></i> Air</td><td>SIA Cargo</td><td>Changi SG</td><td>Frankfurt</td><td>2026-11-12</td><td><span class="b bg">DELIVERED</span></td></tr>
                    <tr><td><b>CT-2026-005</b></td><td><i class="bi bi-ship" style="color:#0891b2"></i> Sea</td><td>Evergreen Marine</td><td>Port Klang</td><td>Los Angeles</td><td>2026-12-04</td><td><span class="b bc">IN TRANSIT</span></td></tr>
                </table>
            </div></div>

            <!-- 3. TRACKING -->
            <div id="tab-tracking" class="tp"><div class="c"><h3 style="margin-bottom:12px">Live Vessel & Aircraft Telemetry Radar</h3><div id="map-tracking" style="width:100%;height:410px;border-radius:8px"></div></div></div>

            <!-- 4. EXCEPTIONS -->
            <div id="tab-exceptions" class="tp"><div class="c"><h3 style="margin-bottom:12px">Shipment Exceptions & Incident Logs</h3>
                <table>
                    <tr><th>ID</th><th>Shipment</th><th>Issue Type</th><th>Severity</th><th>Location</th><th>Action</th></tr>
                    <tr><td>INC-8801</td><td><b>CT-2026-001</b></td><td>Typhoon Weather & High Swells</td><td><span class="b br">HIGH</span></td><td>North Sea</td><td><b style="color:#b45309">Rerouting Active</b></td></tr>
                    <tr><td>INC-8802</td><td><b>CT-2026-003</b></td><td>Port Berth Congestion (+24h)</td><td><span class="b ba">MEDIUM</span></td><td>Shanghai</td><td><b style="color:#0891b2">Queue Adjusted</b></td></tr>
                    <tr><td>INC-8803</td><td><b>CT-2026-002</b></td><td>Airspace Turbulence Diversion</td><td><span class="b ba">MEDIUM</span></td><td>South China Sea</td><td><b style="color:#059669">Flight Cleared</b></td></tr>
                    <tr><td>INC-8804</td><td><b>CT-2026-005</b></td><td>Customs Manifest Verification</td><td><span class="b bc">LOW</span></td><td>Port Klang</td><td><b style="color:#059669">Resolved</b></td></tr>
                </table>
            </div></div>

            <!-- 5. CARRIERS -->
            <div id="tab-carriers" class="tp"><div class="c"><h3 style="margin-bottom:12px">Global Sea & Air Carriers Directory</h3>
                <div class="g3">
                    <div class="sc"><b><i class="bi bi-ship" style="color:#0891b2"></i> Maersk Line (MAEU)</b><p style="font-size:12px;color:#64748b;margin-top:4px">Sea • 700+ Vessels • SLA: <b>96.4%</b></p></div>
                    <div class="sc"><b><i class="bi bi-ship" style="color:#0891b2"></i> Evergreen Marine (EGLV)</b><p style="font-size:12px;color:#64748b;margin-top:4px">Sea • 210+ Vessels • SLA: <b>94.1%</b></p></div>
                    <div class="sc"><b><i class="bi bi-ship" style="color:#0891b2"></i> Nusantara Ocean (NOL)</b><p style="font-size:12px;color:#64748b;margin-top:4px">Sea • 48 Vessels • SLA: <b>98.0%</b></p></div>
                    <div class="sc"><b><i class="bi bi-airplane" style="color:#059669"></i> Garuda Cargo (GA)</b><p style="font-size:12px;color:#64748b;margin-top:4px">Air • 75+ Aircraft • SLA: <b>97.2%</b></p></div>
                    <div class="sc"><b><i class="bi bi-airplane" style="color:#059669"></i> Singapore Airlines Cargo</b><p style="font-size:12px;color:#64748b;margin-top:4px">Air • 140+ Aircraft • SLA: <b>98.5%</b></p></div>
                    <div class="sc"><b><i class="bi bi-airplane" style="color:#059669"></i> DHL Aviation Express</b><p style="font-size:12px;color:#64748b;margin-top:4px">Air • 300+ Aircraft • SLA: <b>97.8%</b></p></div>
                </div>
            </div></div>
            <!-- 6. REPORTS -->
            <div id="tab-reports" class="tp">
                <div class="g3">
                    <div class="c"><p style="font-size:11px;color:#64748b">CARGO VOLUME (Q4)</p><p style="font-size:22px;font-weight:bold;color:#0e7490">1,248 TEUs / 420 T</p></div>
                    <div class="c"><p style="font-size:11px;color:#64748b">ON-TIME DELIVERY</p><p style="font-size:22px;font-weight:bold;color:#059669">94.8%</p></div>
                    <div class="c"><p style="font-size:11px;color:#64748b">CUSTOMS CLEARANCE</p><p style="font-size:22px;font-weight:bold;color:#0891b2">3.4 Hours</p></div>
                </div>
                <div class="c"><h3 style="margin-bottom:12px">Operational KPI & SLA Benchmark Report</h3>
                    <table>
                        <tr><th>Performance Metric</th><th>Actual Q4</th><th>Target SLA</th><th>Status</th></tr>
                        <tr><td>Sea Vessel Fuel Efficiency</td><td>89.2%</td><td>85.0%</td><td><span class="b bg">Optimal</span></td></tr>
                        <tr><td>Air Cargo Turnaround Time</td><td>2.1 Hours</td><td>3.0 Hours</td><td><span class="b bg">Exceeds Target</span></td></tr>
                        <tr><td>Port Container Dwell Time</td><td>14.2 Hours</td><td>18.0 Hours</td><td><span class="b bg">Optimal</span></td></tr>
                        <tr><td>Zero-Damage Delivery Rate</td><td>99.4%</td><td>98.5%</td><td><span class="b bg">Excellent</span></td></tr>
                    </table>
                </div>
            </div>

            <!-- 7. CURRENCY -->
            <div id="tab-currency" class="tp"><div class="c"><h3 style="margin-bottom:12px">Multi-Currency Freight Rates (Base: USD)</h3>
                <table>
                    <tr><th>Pair</th><th>Currency</th><th>Rate</th><th>24h Trend</th><th>Status</th></tr>
                    <tr><td><b>USD / IDR</b></td><td>Indonesian Rupiah</td><td style="color:#059669;font-weight:bold">Rp 15.850</td><td style="color:#059669">▲ +0.25%</td><td><span class="b bg">Live</span></td></tr>
                    <tr><td><b>USD / EUR</b></td><td>Eurozone Euro</td><td style="color:#059669;font-weight:bold">€ 0,92</td><td style="color:#e11d48">▼ -0.12%</td><td><span class="b bg">Live</span></td></tr>
                    <tr><td><b>USD / SGD</b></td><td>Singapore Dollar</td><td style="color:#059669;font-weight:bold">S$ 1,34</td><td style="color:#059669">▲ +0.05%</td><td><span class="b bg">Live</span></td></tr>
                    <tr><td><b>USD / JPY</b></td><td>Japanese Yen</td><td style="color:#059669;font-weight:bold">¥ 153,40</td><td style="color:#e11d48">▼ -0.40%</td><td><span class="b bg">Live</span></td></tr>
                    <tr><td><b>USD / MYR</b></td><td>Malaysian Ringgit</td><td style="color:#059669;font-weight:bold">RM 4,37</td><td style="color:#059669">▲ +0.10%</td><td><span class="b bg">Live</span></td></tr>
                    <tr><td><b>USD / GBP</b></td><td>British Pound</td><td style="color:#059669;font-weight:bold">£ 0,79</td><td style="color:#059669">▲ +0.18%</td><td><span class="b bg">Live</span></td></tr>
                    <tr><td><b>USD / CNY</b></td><td>Chinese Yuan</td><td style="color:#059669;font-weight:bold">¥ 7,18</td><td style="color:#e11d48">▼ -0.06%</td><td><span class="b bg">Live</span></td></tr>
                </table>
            </div></div>

            <!-- 8. AUDIT LOGS -->
            <div id="tab-audit-logs" class="tp"><div class="c"><h3 style="margin-bottom:12px">System Security & Audit Logs</h3>
                <table>
                    <tr><th>Timestamp</th><th>User</th><th>Activity</th><th>IP</th></tr>
                    <tr><td>2026-11-12 23:15</td><td><b>Saimah Admin</b></td><td>Initialized Global Sea & Air Telemetry</td><td>127.0.0.1</td></tr>
                    <tr><td>2026-11-12 22:40</td><td><b>Operator Logistik</b></td><td>Updated Air Cargo CT-2026-002 Airborne</td><td>127.0.0.1</td></tr>
                    <tr><td>2026-11-12 21:10</td><td><b>Customer BSN</b></td><td>Downloaded Bill of Lading #CT-001</td><td>127.0.0.1</td></tr>
                </table>
            </div></div>

            <!-- 9. NOTIFICATIONS -->
            <div id="tab-notifications" class="tp"><div class="c" style="display:flex;flex-direction:column;gap:10px"><h3>System Alerts & Notifications</h3>
                <div class="ba" style="padding:12px;border-radius:8px;font-size:13px"><b>⚠️ Maritime Weather:</b> High swells near Port of Rotterdam affecting CT-2026-001.</div>
                <div class="bc" style="padding:12px;border-radius:8px;font-size:13px"><b>✈️ Air Cargo:</b> Flight CT-2026-002 (Jakarta → Tokyo) cruising on schedule at 35,000 ft.</div>
                <div class="bg" style="padding:12px;border-radius:8px;font-size:13px"><b>✅ FX Sync:</b> All 7 multi-currency exchange rates synchronized automatically.</div>
            </div></div>

            <!-- 10. REAL-TIME WORLD NEWS -->
            <div id="tab-world-news" class="tp"><div class="c">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:14px">
                    <div><h3 style="font-size:15px">Global Breaking World News (Live Real-Time)</h3><span id="news-status" class="b br" style="margin-top:4px">CONNECTING LIVE FEED...</span></div>
                    <div style="display:flex;gap:6px">
                        <button type="button" onclick="loadNews('world')" style="background:#0891b2;color:#fff;border:none;padding:6px 10px;border-radius:6px;font-size:12px;cursor:pointer">🌍 World Headlines</button>
                        <button type="button" onclick="loadNews('business')" style="background:#0f172a;color:#fff;border:none;padding:6px 10px;border-radius:6px;font-size:12px;cursor:pointer">🚢 Trade & Economy</button>
                    </div>
                </div>
                <div id="news-container" style="display:flex;flex-direction:column;gap:10px"></div>
            </div></div>
            <!-- 11. WEATHER -->
            <div id="tab-weather" class="tp"><div class="c">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <h3>Maritime & Aviation Weather Forecast (Real-Time)</h3>
                    <button type="button" onclick="alert('Data cuaca pelabuhan & bandara berhasil diperbarui!')" style="background:#0891b2;color:#fff;border:none;padding:6px 12px;border-radius:6px;font-size:12px;cursor:pointer"><i class="bi bi-arrow-clockwise"></i> Refresh Cuaca</button>
                </div>
                <table>
                    <tr><th>Port / Airport Hub</th><th>Condition</th><th>Temp</th><th>Wind Speed</th><th>Wave / Visibility</th><th>Status</th></tr>
                    <tr><td><b>Tanjung Priok, Jakarta</b></td><td>☀️ Cerah Berawan</td><td>29.5°C</td><td>12 Knot</td><td>0.8 m (Aman)</td><td><span class="b bg">Normal</span></td></tr>
                    <tr><td><b>Singapore Strait Hub</b></td><td>⛅ Berawan</td><td>30.1°C</td><td>14 Knot</td><td>1.0 m (Aman)</td><td><span class="b bg">Normal</span></td></tr>
                    <tr><td><b>Port of Rotterdam, NL</b></td><td>⛈️ Badai & Hujan</td><td>12.4°C</td><td>38 Knot</td><td>4.2 m (Tinggi)</td><td><span class="b br">Warning</span></td></tr>
                    <tr><td><b>Narita Cargo Airport, JP</b></td><td>☀️ Cerah</td><td>16.8°C</td><td>9 Knot</td><td>10 km (Jelas)</td><td><span class="b bg">Clear Sky</span></td></tr>
                    <tr><td><b>Port of Shanghai, CN</b></td><td>🌫️ Kabut Tipis</td><td>18.2°C</td><td>16 Knot</td><td>1.5 m (Sedang)</td><td><span class="b ba">Caution</span></td></tr>
                </table>
            </div></div>

            <!-- 12. USERS -->
            <div id="tab-users" class="tp"><div class="c"><h3 style="margin-bottom:12px">User Management & Role Access</h3>
                <table>
                    <tr><th>ID / NIM</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>
                    <tr><td>250180017</td><td><b>Saimah Admin</b></td><td>admin@cargotrace.com</td><td><span class="b bg">ADMINISTRATOR</span></td><td>Active</td></tr>
                    <tr><td>OP-202601</td><td><b>Operator Logistik</b></td><td>operator@cargotrace.com</td><td><span class="b bc">LOGISTICS OPERATOR</span></td><td>Active</td></tr>
                    <tr><td>CS-202602</td><td><b>Customer BSN</b></td><td>customer@cargotrace.com</td><td><span class="b ba">CUSTOMER</span></td><td>Active</td></tr>
                </table>
            </div></div>
        </div>
    </main>

    <!-- LAYAR LOGIN -->
    <div id="login-overlay" style="display:none;position:fixed;inset:0;background:#0f172a;z-index:99999;align-items:center;justify-content:center">
        <div style="background:#fff;padding:28px;border-radius:12px;width:360px;text-align:left;box-shadow:0 10px 25px rgba(0,0,0,.3)">
            <div style="text-align:center;margin-bottom:16px">
                <div style="background:#06b6d4;color:#fff;width:44px;height:44px;line-height:44px;border-radius:10px;font-weight:bold;margin:0 auto 10px">CT</div>
                <h3 style="margin-bottom:4px">CargoTrace Login</h3>
                <p style="font-size:12px;color:#64748b">Global Shipment Visibility Platform</p>
            </div>
            <p id="login-err" style="display:none;background:#ffe4e6;color:#9f1239;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px;text-align:center"></p>
            <label style="font-size:11px;font-weight:bold;color:#475569">ROLE AKSES</label>
            <select id="login-role" onchange="syncRoleEmail()" style="width:100%;padding:8px 10px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                <option value="Administrator">Administrator</option>
                <option value="Logistics Operator">Logistics Operator</option>
                <option value="Customer">Customer</option>
            </select>
            <label style="font-size:11px;font-weight:bold;color:#475569">EMAIL</label>
            <input type="email" id="login-email" placeholder="Masukkan email" style="width:100%;padding:8px 10px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
            <label style="font-size:11px;font-weight:bold;color:#475569">PASSWORD</label>
            <input type="password" id="login-pass" placeholder="Masukkan password" style="width:100%;padding:8px 10px;margin:4px 0 16px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
            <button type="button" onclick="handleLogin()" style="width:100%;background:#0891b2;color:#fff;border:none;padding:10px;border-radius:6px;font-weight:bold;cursor:pointer">Masuk ke Sistem</button>
        </div>
    </div>

    <script>
        let map=null,tMap=null,chart=null;
        const allTabs=['dashboard','shipments','tracking','exceptions','carriers','reports','currency','audit-logs','notifications','world-news','weather','users'];
        const roleMenus={
            'Administrator':allTabs,
            'Logistics Operator':['dashboard','shipments','tracking','exceptions','carriers','notifications','world-news','weather'],
            'Customer':['dashboard','shipments','tracking','notifications','weather']
        };
        const sIcon=L.divIcon({className:'i-ship',html:'<i class="bi bi-ship"></i>',iconSize:[30,30],iconAnchor:[15,15]});
        const pIcon=L.divIcon({className:'i-plane',html:'<i class="bi bi-airplane"></i>',iconSize:[30,30],iconAnchor:[15,15]});
        function drawMap(id){
            const m=L.map(id).setView([2.0,108.5],5);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(m);
            L.marker([1.29,103.85],{icon:sIcon}).addTo(m).bindPopup("<b>Kapal CT-2026-001</b><br>Nusantara Ocean");
            L.marker([4.2,109.5],{icon:pIcon}).addTo(m).bindPopup("<b>Pesawat CT-2026-002</b><br>Garuda Cargo");
            L.polyline([[-6.10,106.88],[1.29,103.85],[5.40,100.30]],{color:'#0891b2',weight:3,dashArray:'6,6'}).addTo(m);
            L.polyline([[-6.12,106.65],[4.2,109.5],[14.50,121.00]],{color:'#059669',weight:3,dashArray:'6,6'}).addTo(m);
            return m;
        }
        async function loadNews(cat='world'){
            const box=document.getElementById('news-container'),badge=document.getElementById('news-status');
            box.innerHTML='<p style="font-size:13px;color:#64748b">⏳ Mengambil berita dunia real-time terbaru...</p>';
            const rss=cat==='business'?'https://feeds.bbci.co.uk/news/business/rss.xml':'https://feeds.bbci.co.uk/news/world/rss.xml';
            try{
                const r=await fetch('https://api.rss2json.com/v1/api.json?rss_url='+encodeURIComponent(rss));
                const d=await r.json();
                if(d.status==='ok'&&d.items.length){
                    badge.innerText='🔴 LIVE FEED • UPDATED '+new Date().toLocaleTimeString('id-ID');
                    box.innerHTML=d.items.slice(0,6).map(i=>`<a href="${i.link}" target="_blank" rel="noopener noreferrer" class="nl"><div style="display:flex;justify-content:space-between;margin-bottom:4px"><span style="font-size:11px;color:#0891b2;font-weight:bold">BBC LIVE • ${i.pubDate}</span><span style="font-size:11px;color:#0891b2;font-weight:bold">Baca Artikel <i class="bi bi-box-arrow-up-right"></i></span></div><h4 style="font-size:14px;margin-bottom:4px">${i.title}</h4><p style="font-size:12px;color:#64748b">${i.description.replace(/<[^>]*>?/gm,'').slice(0,145)}...</p></a>`).join('');
                }else throw new Error();
            }catch(e){
                badge.innerText='DIRECT GLOBAL NEWS PORTAL';
                box.innerHTML=`<a href="https://www.bbc.com/news/world" target="_blank" class="nl"><b>BBC World News — Breaking Global Headlines ↗</b><p style="font-size:12px;color:#64748b;margin-top:4px">Klik untuk membaca berita dunia terkini secara langsung.</p></a><a href="https://www.reuters.com/world/" target="_blank" class="nl"><b>Reuters World — Live International News ↗</b><p style="font-size:12px;color:#64748b;margin-top:4px">Klik untuk membuka portal berita internasional Reuters.</p></a>`;
            }
        }
        function initDash(){
            if(map){map.remove();map=null}
            map=drawMap('map');
            loadNews('world');
            if(!chart){
                chart=new Chart(document.getElementById('shipmentChart').getContext('2d'),{
                    type:'bar',
                    data:{labels:['In Transit','Delivered','Delayed','Exceptions'],datasets:[{label:'Shipments',data:[84,36,8,4],backgroundColor:['#0891b2','#059669','#e11d48','#d97706']}]},
                    options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true}}}
                });
            }
        }
        function switchTab(t){
            allTabs.forEach(x=>{
                document.getElementById('tab-'+x).classList.remove('active');
                document.getElementById('btn-'+x).classList.remove('active');
            });
            document.getElementById('tab-'+t).classList.add('active');
            document.getElementById('btn-'+t).classList.add('active');
            document.getElementById('page-title').innerText=t.replace('-',' ').toUpperCase()+" Management";
            if(t==='dashboard')setTimeout(initDash,50);
            if(t==='tracking')setTimeout(()=>{if(tMap)tMap.remove();tMap=drawMap('map-tracking')},50);
            if(t==='world-news')loadNews('world');
        }
        function syncRoleEmail(){
            const r=document.getElementById('login-role').value,em=document.getElementById('login-email');
            if(r==='Administrator')em.value='admin@cargotrace.com';
            else if(r==='Logistics Operator')em.value='operator@cargotrace.com';
            else em.value='customer@cargotrace.com';
        }
        function handleLogout(){
            if(confirm('Yakin ingin logout dari sistem CargoTrace?')){
                document.getElementById('login-email').value='';
                document.getElementById('login-pass').value='';
                document.getElementById('login-err').style.display='none';
                document.getElementById('login-overlay').style.display='flex';
            }
        }
        function handleLogin(){
            const r=document.getElementById('login-role').value,em=document.getElementById('login-email').value.trim(),pw=document.getElementById('login-pass').value,err=document.getElementById('login-err');
            if(!em||pw!=='password123'){
                err.innerText='Email atau password yang Anda masukkan salah.';
                err.style.display='block';
                return;
            }
            err.style.display='none';
            const allowed=roleMenus[r]||allTabs;
            allTabs.forEach(x=>{document.getElementById('btn-'+x).style.display=allowed.includes(x)?'flex':'none'});
            const hb=document.getElementById('user-name'),hs=document.getElementById('user-role'),bn=document.getElementById('role-banner');
            if(r==='Administrator'){hb.innerText='Saimah Admin (250180017)';hs.innerText='ADMINISTRATOR';hs.className='b bg';bn.className='bg';bn.innerHTML='🔑 Mode Akses: <b>Administrator</b> — Akses penuh ke seluruh modul sistem.';}
            else if(r==='Logistics Operator'){hb.innerText='Operator Logistik';hs.innerText='LOGISTICS OPERATOR';hs.className='b bc';bn.className='bc';bn.innerHTML='🚚 Mode Akses: <b>Logistics Operator</b> — Akses operasional pengiriman & insiden.';}
            else{hb.innerText='Customer BSN';hs.innerText='CUSTOMER';hs.className='b ba';bn.className='ba';bn.innerHTML='📦 Mode Akses: <b>Customer</b> — Akses pemantauan status kargo & cuaca.';}
            document.getElementById('login-overlay').style.display='none';
            switchTab('dashboard');
        }
        window.onload=initDash;
    </script>
</body>
</html>