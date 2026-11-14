<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CargoTrace - Global Shipment Visibility Platform</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script defer src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .g4{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px}
        .g3{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px}
        .gd{display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:18px}
        .c{background:#fff;padding:16px;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 1px 3px rgba(0,0,0,.05);margin-bottom:16px}
        .sc{background:#f8fafc;padding:12px;border:1px solid #e2e8f0;border-radius:8px}
        .nl{display:block;text-decoration:none;color:inherit;background:#f8fafc;padding:12px 14px;border:1px solid #e2e8f0;border-radius:8px;transition:.2s}
        .nl:hover{border-color:#0891b2;background:#f0f9ff}
        table{width:100%;border-collapse:collapse;font-size:13px;text-align:left}
        th,td{padding:9px 12px;border-bottom:1px solid #e2e8f0;vertical-align:top}
        th{background:#f1f5f9;color:#475569;font-weight:bold}
        .b{padding:3px 8px;border-radius:4px;font-size:11px;font-weight:bold;display:inline-block}
        .bc{background:#cffafe;color:#155e75}.bg{background:#d1fae5;color:#065f46}.br{background:#ffe4e6;color:#9f1239}.ba{background:#fef3c7;color:#b45309}
        .i-ship{background:#0891b2;color:#fff;border-radius:50%;text-align:center;line-height:28px;font-size:14px;border:2px solid #fff;box-shadow:0 2px 5px rgba(0,0,0,.3)}
        .i-plane{background:#059669;color:#fff;border-radius:50%;text-align:center;line-height:28px;font-size:14px;border:2px solid #fff;box-shadow:0 2px 5px rgba(0,0,0,.3)}
        .tp{display:none}.tp.active{display:block}
        .tl-box{border-left:2px solid #0891b2;margin-left:10px;padding-left:18px;display:flex;flex-direction:column;gap:14px}
        .tl-item{position:relative;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0}
        .tl-dot{position:absolute;left:-25px;top:14px;width:12px;height:12px;border-radius:50%;background:#0891b2;border:2px solid #fff;box-shadow:0 0 0 2px #0891b2}
        .barcode{height:36px;width:100%;background:repeating-linear-gradient(90deg,#0f172a 0 2px,transparent 2px 5px,#0f172a 5px 9px,transparent 9px 11px,#0f172a 11px 12px,transparent 12px 16px);margin:8px 0 4px}
        .prog-wrap{width:100%;height:10px;background:#e2e8f0;border-radius:99px;overflow:hidden;margin:8px 0}
        .prog-fill{height:100%;background:linear-gradient(90deg,#0891b2,#059669);width:0%;transition:width .35s ease}
        @media print{
            body *{visibility:hidden}
            #receipt-modal,#receipt-modal *{visibility:visible}
            #receipt-modal{position:fixed;inset:0;background:#fff!important;display:flex!important;align-items:flex-start;justify-content:center;padding-top:16px}
            .no-print{display:none!important}
        }
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
            <button type="button" onclick="switchTab('notifications')" id="btn-notifications" class="nb"><i class="bi bi-bell"></i><span>Notifications</span><span id="notif-badge" style="margin-left:auto;background:#e11d48;color:#fff;font-size:10px;padding:1px 6px;border-radius:10px">3</span></button>
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
            <div id="tab-dashboard" class="tp active">
                <div id="role-banner" class="bg" style="width:100%;padding:8px 14px;border-radius:8px;margin-bottom:14px;font-size:12px">🔑 Mode Akses: <b>Administrator</b> — Rekapitulasi volume paket Harian, Bulanan, & Tahunan.</div>

                <div id="admin-kpi-box" class="g4">
                    <div class="c" style="border-top:3px solid #0891b2;margin-bottom:0">
                        <p style="font-size:11px;color:#64748b"><i class="bi bi-calendar-day" style="color:#0891b2"></i> PAKET HARI INI</p>
                        <p id="kpi-daily" style="font-size:22px;font-weight:bold;color:#0f172a;margin:4px 0">14 Paket</p>
                        <span style="font-size:11px;color:#059669">▲ +3 dari kemarin</span>
                    </div>
                    <div class="c" style="border-top:3px solid #059669;margin-bottom:0">
                        <p style="font-size:11px;color:#64748b"><i class="bi bi-calendar-month" style="color:#059669"></i> PAKET BULAN INI (NOV)</p>
                        <p id="kpi-monthly" style="font-size:22px;font-weight:bold;color:#0f172a;margin:4px 0">129 Paket</p>
                        <span style="font-size:11px;color:#059669">▲ +12.4% vs Okt 2026</span>
                    </div>
                    <div class="c" style="border-top:3px solid #6366f1;margin-bottom:0">
                        <p style="font-size:11px;color:#64748b"><i class="bi bi-calendar3" style="color:#6366f1"></i> PAKET TAHUN INI (2026)</p>
                        <p id="kpi-yearly" style="font-size:22px;font-weight:bold;color:#0f172a;margin:4px 0">1.541 Paket</p>
                        <span style="font-size:11px;color:#0891b2">Target Tahunan: 1.800</span>
                    </div>
                    <div class="c" style="border-top:3px solid #f59e0b;margin-bottom:0">
                        <p style="font-size:11px;color:#64748b"><i class="bi bi-cash-stack" style="color:#d97706"></i> OMZET BULAN INI</p>
                        <p style="font-size:20px;font-weight:bold;color:#059669;margin:4px 0">Rp 482,5 Jt</p>
                        <span style="font-size:11px;color:#64748b">Sea & Air Cargo</span>
                    </div>
                </div>

                <div id="customer-kpi-box" class="g4" style="display:none">
                    <div class="c" style="border-top:3px solid #0f172a;margin-bottom:0"><p style="font-size:11px;color:#64748b">TOTAL PAKET SAYA</p><p id="cust-stat-total" style="font-size:22px;font-weight:bold;margin:4px 0">1</p></div>
                    <div class="c" style="border-top:3px solid #d97706;margin-bottom:0"><p style="font-size:11px;color:#d97706">MENUNGGU ACC</p><p id="cust-stat-pending" style="font-size:22px;font-weight:bold;color:#b45309;margin:4px 0">0</p></div>
                    <div class="c" style="border-top:3px solid #0891b2;margin-bottom:0"><p style="font-size:11px;color:#0891b2">DALAM PERJALANAN</p><p id="cust-stat-transit" style="font-size:22px;font-weight:bold;color:#0e7490;margin:4px 0">1</p></div>
                    <div class="c" style="border-top:3px solid #059669;margin-bottom:0"><p style="font-size:11px;color:#059669">SUDAH SAMPAI</p><p id="cust-stat-delivered" style="font-size:22px;font-weight:bold;color:#047857;margin:4px 0">0</p></div>
                </div>
                <div id="customer-quick-track" class="c" style="display:none;border-left:4px solid #0891b2">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px">
                        <div><h3 style="font-size:13px;color:#0f172a"><i class="bi bi-search" style="color:#0891b2"></i> Quick Track Resi Cepat</h3><p id="qt-hint" style="font-size:11px;color:#64748b">Masukkan No. Resi paket Anda untuk melihat progres & estimasi tanggal tiba</p></div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            <input type="text" id="quick-track-input" placeholder="Masukkan No. Resi..." style="padding:7px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px;width:190px">
                            <button type="button" onclick="quickTrack()" style="background:#0891b2;color:#fff;border:none;padding:7px 14px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer">Lacak Paket</button>
                            <button type="button" onclick="resetQuickTrack()" style="background:#e2e8f0;color:#334155;border:none;padding:7px 10px;border-radius:6px;font-size:12px;cursor:pointer">Semua Rute</button>
                        </div>
                    </div>
                    <div id="quick-track-result" style="display:none;margin-top:12px;padding:12px;background:#f8fafc;border:1px solid #cbd5e1;border-radius:8px">
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;flex-wrap:wrap;gap:6px">
                            <b id="qt-title">-</b>
                            <div><span id="qt-eta" class="b ba" style="margin-right:4px">ETA: -</span><span id="qt-badge" class="b bc">IN TRANSIT</span></div>
                        </div>
                        <div class="prog-wrap"><div id="qt-bar" class="prog-fill" style="width:50%"></div></div>
                        <div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b">
                            <span id="qt-orig">Asal: -</span>
                            <span id="qt-mid">Dalam Perjalanan</span>
                            <span id="qt-dest">Tujuan: -</span>
                        </div>
                    </div>
                </div>

                <div id="internal-stats-box" class="g5">
                    <div class="c"><p style="font-size:11px;color:#64748b">TOTAL SHIPMENTS</p><p id="stat-total" style="font-size:22px;font-weight:bold">129</p></div>
                    <div class="c"><p style="font-size:11px;color:#0891b2">IN TRANSIT</p><p id="stat-transit" style="font-size:22px;font-weight:bold;color:#0e7490">85</p></div>
                    <div class="c"><p style="font-size:11px;color:#059669">DELIVERED</p><p id="stat-delivered" style="font-size:22px;font-weight:bold;color:#047857">36</p></div>
                    <div class="c"><p style="font-size:11px;color:#e11d48">DELAYED</p><p id="stat-delayed" style="font-size:22px;font-weight:bold;color:#be123c">8</p></div>
                    <div class="c"><p style="font-size:11px;color:#d97706">EXCEPTIONS</p><p id="stat-exceptions" style="font-size:22px;font-weight:bold;color:#b45309">4</p></div>
                </div>
                <div class="gd">
                    <div id="analytics-card" class="c"><h3 style="font-size:12px;color:#475569;margin-bottom:10px">SHIPMENT ANALYTICS (Q4 2026)</h3><div style="height:165px"><canvas id="shipmentChart"></canvas></div></div>
                    <div id="weather-card" class="c"><h3 style="font-size:12px;color:#475569;margin-bottom:10px">PORT & HUB WEATHER</h3>
                        <div style="font-size:12px;display:flex;flex-direction:column;gap:7px">
                            <div class="sc" style="padding:7px;display:flex;justify-content:space-between"><span>Tanjung Priok, ID</span><b style="color:#0891b2">29.5°C Cerah</b></div>
                            <div class="sc" style="padding:7px;display:flex;justify-content:space-between"><span>Singapore Hub</span><b style="color:#0891b2">30.1°C Normal</b></div>
                            <div class="sc" style="padding:7px;display:flex;justify-content:space-between"><span>Rotterdam Port</span><b style="color:#e11d48">12.4°C Badai</b></div>
                            <div class="sc" style="padding:7px;display:flex;justify-content:space-between"><span>Narita Cargo Hub</span><b style="color:#059669">16.8°C Cerah</b></div>
                        </div>
                    </div>
                </div>
                <div class="c">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;flex-wrap:wrap;gap:8px">
                        <h3 id="map-dash-title" style="font-size:12px;color:#475569">Live Global Tracking Map (Sea Vessel & Air Cargo)</h3>
                        <button type="button" class="sim-btn" onclick="toggleSim()" style="background:#0f172a;color:#fff;border:none;padding:6px 12px;border-radius:6px;font-size:11px;font-weight:bold;cursor:pointer"><i class="bi bi-play-fill"></i> ▶ Simulasi Gerak Live</button>
                    </div>
                    <div id="map" style="width:100%;height:260px;border-radius:8px;margin-bottom:8px"></div>
                    <div style="display:flex;gap:18px;font-size:12px;color:#475569">
                        <span><i class="bi bi-ship" style="color:#0891b2"></i> Kapal Kargo (Vessel)</span>
                        <span><i class="bi bi-airplane" style="color:#059669"></i> Pesawat Kargo (Air Freight)</span>
                        <span><b style="color:#0891b2">---</b> Jalur Laut</span>
                        <span><b style="color:#059669">---</b> Jalur Udara</span>
                    </div>
                </div>
            </div>
            <div id="tab-shipments" class="tp">
                <div class="c">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px">
                        <h3 id="ship-table-title">Master Data Shipments (Sea & Air Cargo)</h3>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            <button type="button" onclick="exportCSV()" style="background:#0f172a;color:#fff;border:none;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer"><i class="bi bi-file-earmark-spreadsheet"></i> Export Excel / CSV</button>
                            <button type="button" id="btn-add-shipment" onclick="openAddModal()" style="display:none;background:#059669;color:#fff;border:none;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer"><i class="bi bi-plus-lg"></i> + Tambah Kiriman Barang</button>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap">
                        <input type="text" id="search-ship" onkeyup="renderTables()" placeholder="🔍 Cari No. Resi, Pengirim, Penerima, atau Barang..." style="flex:2;min-width:200px;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                        <select id="filter-status" onchange="renderTables()" style="flex:1;min-width:160px;padding:8px 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                            <option value="ALL">Semua Status Paket</option>
                            <option value="MENUNGGU ACC">Pending / Menunggu ACC</option>
                            <option value="IN TRANSIT">In Transit / Airborne</option>
                            <option value="DELAYED">Delayed / Terkendala</option>
                            <option value="SAMPAI">Delivered / Sudah Sampai</option>
                            <option value="DITOLAK">Ditolak / Dibatalkan</option>
                        </select>
                    </div>
                    <table>
                        <thead>
                            <tr><th>No Resi</th><th>Pengirim & Penerima</th><th>Barang & Mode</th><th>Rute, Carrier & ETA</th><th>Biaya (IDR)</th><th>Status, Kendala & POD</th></tr>
                        </thead>
                        <tbody id="shipment-table-body"></tbody>
                    </table>
                </div>
            </div>
            <div id="tab-tracking" class="tp">
                <div class="c">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:8px">
                        <h3>Live Vessel & Aircraft Telemetry Radar</h3>
                        <button type="button" class="sim-btn" onclick="toggleSim()" style="background:#0f172a;color:#fff;border:none;padding:6px 12px;border-radius:6px;font-size:11px;font-weight:bold;cursor:pointer"><i class="bi bi-play-fill"></i> ▶ Simulasi Gerak Live</button>
                    </div>
                    <div id="map-tracking" style="width:100%;height:340px;border-radius:8px"></div>
                </div>
                <div class="c">
                    <h3 style="margin-bottom:14px"><i class="bi bi-clock-history" style="color:#0891b2"></i> Riwayat Perjalanan & Bukti Serah Terima (Live Timeline)</h3>
                    <div id="timeline-list" class="tl-box">
                        <div class="tl-item" data-owner="customer"><span class="tl-dot" style="background:#059669;box-shadow:0 0 0 2px #059669"></span><div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b"><b>CT-2026-003 • Pengirim: Saimah Store → Nadia Putri (ETA: 16 Nov 2026)</b><span>Hari ini, 20:10 WIB</span></div><p style="font-size:13px;margin-top:3px"><b>In Transit (Udara):</b> Paket Skincare (4 Kg) berangkat dari Hub Medan menuju Jakarta.</p></div>
                        <div class="tl-item" data-owner="corporate"><span class="tl-dot" style="background:#059669;box-shadow:0 0 0 2px #059669"></span><div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b"><b>CT-2026-002 • Garuda Cargo (ETA: 16 Nov 2026)</b><span>Hari ini, 19:45 WIB</span></div><p style="font-size:13px;margin-top:3px"><b>Airborne (Dalam Penerbangan):</b> Pesawat kargo melintasi Laut China Selatan menuju Narita Tokyo.</p></div>
                        <div class="tl-item" data-owner="corporate"><span class="tl-dot"></span><div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b"><b>CT-2026-001 • Nusantara Ocean (ETA: 28 Nov 2026)</b><span>Hari ini, 16:20 WIB</span></div><p style="font-size:13px;margin-top:3px"><b>In Transit (Pelayaran):</b> Kapal kontainer melewati hub transit Selat Singapura menuju Rotterdam.</p></div>
                    </div>
                </div>
            </div>

            <div id="tab-exceptions" class="tp"><div class="c">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px">
                    <div><h3>Shipment Exceptions & Incident Logs</h3><p style="font-size:11px;color:#64748b">Laporan kendala cuaca, pelabuhan, atau bea cukai yang mempengaruhi estimasi tiba</p></div>
                    <button type="button" onclick="openIncidentModal(null)" style="background:#e11d48;color:#fff;border:none;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer"><i class="bi bi-exclamation-octagon"></i> + Lapor Kendala Kargo</button>
                </div>
                <table>
                    <thead>
                        <tr><th>ID Insiden</th><th>No Resi</th><th>Jenis Kendala</th><th>Severity</th><th>Lokasi</th><th>Penanganan & Aksi</th></tr>
                    </thead>
                    <tbody id="exceptions-table-body">
                        <tr id="inc-row-INC-8801"><td><b>INC-8801</b></td><td><b>CT-2026-001</b></td><td>Typhoon Weather & High Swells</td><td><span class="b br">HIGH</span></td><td>North Sea</td><td id="inc-act-INC-8801"><b style="color:#b45309">Rerouting Active</b></td></tr>
                        <tr id="inc-row-INC-8802"><td><b>INC-8802</b></td><td><b>CT-2026-003</b></td><td>Port Berth Congestion (+24h)</td><td><span class="b ba">MEDIUM</span></td><td>Shanghai</td><td id="inc-act-INC-8802"><b style="color:#0891b2">Queue Adjusted</b></td></tr>
                    </tbody>
                </table>
            </div></div>
            <div id="tab-carriers" class="tp"><div class="c"><h3 style="margin-bottom:12px">Global Sea & Air Carriers Directory</h3>
                <div class="g3">
                    <div class="sc"><b><i class="bi bi-ship" style="color:#0891b2"></i> Maersk Line (MAEU)</b><p style="font-size:12px;color:#64748b;margin-top:4px">Sea • 700+ Vessels • SLA: <b>96.4%</b></p></div>
                    <div class="sc"><b><i class="bi bi-ship" style="color:#0891b2"></i> Evergreen Marine (EGLV)</b><p style="font-size:12px;color:#64748b;margin-top:4px">Sea • 210+ Vessels • SLA: <b>94.1%</b></p></div>
                    <div class="sc"><b><i class="bi bi-airplane" style="color:#059669"></i> Garuda Cargo (GA)</b><p style="font-size:12px;color:#64748b;margin-top:4px">Air • 75+ Aircraft • SLA: <b>97.2%</b></p></div>
                </div>
            </div></div>

            <div id="tab-reports" class="tp">
                <div class="g3">
                    <div class="c"><p style="font-size:11px;color:#64748b">CARGO VOLUME (Q4 2026)</p><p style="font-size:22px;font-weight:bold;color:#0e7490">1,248 TEUs / 420 T</p></div>
                    <div class="c"><p style="font-size:11px;color:#64748b">ON-TIME DELIVERY</p><p style="font-size:22px;font-weight:bold;color:#059669">94.8%</p></div>
                    <div class="c"><p style="font-size:11px;color:#64748b">CUSTOMS CLEARANCE</p><p style="font-size:22px;font-weight:bold;color:#0891b2">3.4 Hours</p></div>
                </div>
                <div class="c">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:12px">
                        <div><h3>Laporan Rekapitulasi Pengiriman, ETA & Bukti Serah Terima (POD)</h3><p style="font-size:11px;color:#64748b">Unduh laporan lengkap berformat CSV (siap buka di Microsoft Excel)</p></div>
                        <button type="button" onclick="exportCSV()" style="background:#059669;color:#fff;border:none;padding:9px 16px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer"><i class="bi bi-download"></i> Download Laporan Excel (.CSV)</button>
                    </div>
                    <table>
                        <thead>
                            <tr><th>No Resi</th><th>Pengirim → Penerima</th><th>Rute</th><th>Estimasi Tiba (ETA)</th><th>Status</th><th>Bukti Serah Terima (POD)</th></tr>
                        </thead>
                        <tbody id="report-table-body"></tbody>
                    </table>
                </div>
            </div>
            <div id="tab-currency" class="tp">
                <div class="c" style="border-left:4px solid #059669">
                    <h3 style="margin-bottom:10px"><i class="bi bi-calculator" style="color:#059669"></i> Kalkulator Konversi Tarif Ongkir (IDR ke Mata Uang Asing)</h3>
                    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:end">
                        <div style="flex:2;min-width:180px">
                            <label style="font-size:11px;font-weight:bold;color:#475569">NOMINAL TARIF (IDR)</label>
                            <input type="number" id="curr-idr-input" value="1500000" oninput="convertCurrency()" style="width:100%;padding:8px 12px;margin-top:4px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                        </div>
                        <div style="flex:1;min-width:160px">
                            <label style="font-size:11px;font-weight:bold;color:#475569">MATA UANG TUJUAN</label>
                            <select id="curr-target" onchange="convertCurrency()" style="width:100%;padding:8px 10px;margin-top:4px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                                <option value="USD">USD — US Dollar ($)</option>
                                <option value="MYR">MYR — Malaysian Ringgit (RM)</option>
                                <option value="SGD">SGD — Singapore Dollar (S$)</option>
                                <option value="EUR">EUR — Euro (€)</option>
                                <option value="JPY">JPY — Japanese Yen (¥)</option>
                                <option value="CNY">CNY — Chinese Yuan (¥)</option>
                                <option value="GBP">GBP — British Pound (£)</option>
                                <option value="AUD">AUD — Australian Dollar (A$)</option>
                                <option value="KRW">KRW — South Korean Won (₩)</option>
                                <option value="SAR">SAR — Saudi Riyal (SR)</option>
                            </select>
                        </div>
                        <div style="flex:2;min-width:180px">
                            <label style="font-size:11px;font-weight:bold;color:#059669">HASIL KONVERSI</label>
                            <input type="text" id="curr-result" readonly value="$ 94.64 USD" style="width:100%;padding:8px 12px;margin-top:4px;border:1px solid #059669;background:#f0fdf4;font-weight:bold;color:#065f46;border-radius:6px;font-size:14px">
                        </div>
                    </div>
                </div>
                <div class="c"><h3 style="margin-bottom:12px">Multi-Currency Global Freight Exchange Rates (Base: IDR)</h3>
                    <table>
                        <tr><th>Pair</th><th>Mata Uang / Negara</th><th>Kurs Beli (IDR)</th><th>Perubahan 24j</th><th>Status</th></tr>
                        <tr><td><b>USD / IDR</b></td><td>US Dollar (Amerika Serikat)</td><td style="color:#059669;font-weight:bold">Rp 15.850</td><td style="color:#059669">▲ +0.25%</td><td><span class="b bg">Live</span></td></tr>
                        <tr><td><b>MYR / IDR</b></td><td>Malaysian Ringgit (Malaysia)</td><td style="color:#059669;font-weight:bold">Rp 3.620</td><td style="color:#059669">▲ +0.18%</td><td><span class="b bg">Live</span></td></tr>
                        <tr><td><b>SGD / IDR</b></td><td>Singapore Dollar (Singapura)</td><td style="color:#059669;font-weight:bold">Rp 11.840</td><td style="color:#059669">▲ +0.14%</td><td><span class="b bg">Live</span></td></tr>
                        <tr><td><b>EUR / IDR</b></td><td>Eurozone Euro (Uni Eropa)</td><td style="color:#059669;font-weight:bold">Rp 17.120</td><td style="color:#e11d48">▼ -0.12%</td><td><span class="b bg">Live</span></td></tr>
                        <tr><td><b>JPY / IDR</b></td><td>Japanese Yen (Jepang)</td><td style="color:#059669;font-weight:bold">Rp 104,50</td><td style="color:#059669">▲ +0.31%</td><td><span class="b bg">Live</span></td></tr>
                        <tr><td><b>CNY / IDR</b></td><td>Chinese Yuan / RMB (Tiongkok)</td><td style="color:#059669;font-weight:bold">Rp 2.190</td><td style="color:#e11d48">▼ -0.08%</td><td><span class="b bg">Live</span></td></tr>
                        <tr><td><b>GBP / IDR</b></td><td>British Pound Sterling (Inggris)</td><td style="color:#059669;font-weight:bold">Rp 20.150</td><td style="color:#059669">▲ +0.22%</td><td><span class="b bg">Live</span></td></tr>
                        <tr><td><b>AUD / IDR</b></td><td>Australian Dollar (Australia)</td><td style="color:#059669;font-weight:bold">Rp 10.420</td><td style="color:#e11d48">▼ -0.15%</td><td><span class="b bg">Live</span></td></tr>
                        <tr><td><b>KRW / IDR</b></td><td>South Korean Won (Korea Selatan)</td><td style="color:#059669;font-weight:bold">Rp 11,45</td><td style="color:#059669">▲ +0.09%</td><td><span class="b bg">Live</span></td></tr>
                        <tr><td><b>SAR / IDR</b></td><td>Saudi Riyal (Arab Saudi)</td><td style="color:#059669;font-weight:bold">Rp 4.225</td><td style="color:#059669">▲ +0.19%</td><td><span class="b bg">Live</span></td></tr>
                    </table>
                </div>
            </div>
            <div id="tab-audit-logs" class="tp"><div class="c"><h3 style="margin-bottom:12px">System Security & Audit Logs</h3>
                <table>
                    <tr><th>Timestamp</th><th>User</th><th>Activity</th><th>IP</th></tr>
                    <tr><td>2026-11-14 21:55</td><td><b>Saimah Admin</b></td><td>Activated Auto-ETA & Interactive Exception Reporting</td><td>127.0.0.1</td></tr>
                </table>
            </div></div>

            <div id="tab-notifications" class="tp"><div class="c">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <h3>Shipment & System Notifications</h3>
                    <span class="b bc">REAL-TIME ALERTS</span>
                </div>
                <div id="notification-list" style="display:flex;flex-direction:column;gap:10px">
                    <div class="bg" data-owner="customer" style="padding:12px;border-radius:8px;font-size:13px"><b>✅ Paket Anda CT-2026-003:</b> Paket Skincare (4 Kg) untuk Nadia Putri dalam penerbangan Medan → Jakarta (ETA: 16 Nov 2026).</div>
                    <div class="bg" data-owner="corporate" style="padding:12px;border-radius:8px;font-size:13px"><b>✅ Status Pengiriman CT-2026-002:</b> Paket Semiconductor telah lepas landas menuju Tokyo (ETA: 16 Nov 2026).</div>
                    <div class="bc" data-owner="corporate" style="padding:12px;border-radius:8px;font-size:13px"><b>🚢 Info Pelayaran CT-2026-001:</b> Kapal Nusantara Ocean melewati Selat Singapura menuju Rotterdam (ETA: 28 Nov 2026).</div>
                </div>
            </div></div>

            <div id="tab-world-news" class="tp"><div class="c">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:14px">
                    <div><h3 style="font-size:15px">Global Breaking World News (Live Real-Time)</h3><span id="news-status" class="b br" style="margin-top:4px">CONNECTING LIVE FEED...</span></div>
                </div>
                <div id="news-container" style="display:flex;flex-direction:column;gap:10px"></div>
            </div></div>

            <div id="tab-weather" class="tp"><div class="c">
                <h3 style="margin-bottom:12px">Maritime & Aviation Weather Forecast (Real-Time)</h3>
                <table>
                    <tr><th>Port / Airport Hub</th><th>Condition</th><th>Temp</th><th>Status</th></tr>
                    <tr><td><b>Tanjung Priok, Jakarta</b></td><td>☀️ Cerah Berawan</td><td>29.5°C</td><td><span class="b bg">Normal</span></td></tr>
                    <tr><td><b>Port of Rotterdam, NL</b></td><td>⛈️ Badai & Hujan</td><td>12.4°C</td><td><span class="b br">Warning</span></td></tr>
                    <tr><td><b>Narita Cargo Airport, JP</b></td><td>☀️ Cerah</td><td>16.8°C</td><td><span class="b bg">Clear Sky</span></td></tr>
                </table>
            </div></div>

            <div id="tab-users" class="tp"><div class="c"><h3 style="margin-bottom:12px">User Management & Role Access</h3>
                <table>
                    <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr>
                    <tr><td>250180017</td><td><b>Saimah Admin</b></td><td>admin@cargotrace.com</td><td><span class="b bg">ADMINISTRATOR</span></td></tr>
                    <tr><td>OP-01</td><td><b>Operator Logistik</b></td><td>operator@cargotrace.com</td><td><span class="b bc">OPERATOR</span></td></tr>
                    <tr><td>CS-01</td><td><b>Customer BSN</b></td><td>customer@cargotrace.com</td><td><span class="b ba">CUSTOMER</span></td></tr>
                </table>
            </div></div>
        </div>
    </main>
    <div id="incident-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);z-index:99998;align-items:center;justify-content:center">
        <div style="background:#fff;padding:24px;border-radius:12px;width:420px;max-width:95vw;box-shadow:0 10px 25px rgba(0,0,0,.3);text-align:left">
            <h3 style="margin-bottom:4px;color:#e11d48"><i class="bi bi-exclamation-triangle-fill"></i> Form Lapor Kendala Pengiriman</h3>
            <p style="font-size:11px;color:#64748b;margin-bottom:12px">Melaporkan kendala akan mengubah status kargo menjadi DELAYED & memperbarui ETA</p>
            <label style="font-size:11px;font-weight:bold;color:#475569">PILIH NO. RESI KARGO</label>
            <select id="inc-resi-select" style="width:100%;padding:8px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px"></select>
            <label style="font-size:11px;font-weight:bold;color:#475569">JENIS KENDALA / HAMBATAN</label>
            <select id="inc-type" style="width:100%;padding:8px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px">
                <option value="Cuaca Badai & Gelombang Tinggi">Cuaca Badai & Gelombang Tinggi</option>
                <option value="Antrean Sandar Pelabuhan (+24 Jam)">Antrean Sandar Pelabuhan (+24 Jam)</option>
                <option value="Pemeriksaan Bea Cukai (Customs Hold)">Pemeriksaan Bea Cukai (Customs Hold)</option>
                <option value="Penundaan Jadwal Penerbangan Kargo">Penundaan Jadwal Penerbangan Kargo</option>
                <option value="Kendala Teknis Armada / Kontainer">Kendala Teknis Armada / Kontainer</option>
            </select>
            <div style="display:flex;gap:10px">
                <div style="flex:1">
                    <label style="font-size:11px;font-weight:bold;color:#475569">TINGKAT URGENSI</label>
                    <select id="inc-severity" style="width:100%;padding:8px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px">
                        <option value="HIGH">HIGH (Tinggi)</option>
                        <option value="MEDIUM">MEDIUM (Sedang)</option>
                        <option value="LOW">LOW (Ringan)</option>
                    </select>
                </div>
                <div style="flex:1">
                    <label style="font-size:11px;font-weight:bold;color:#475569">LOKASI KEJADIAN</label>
                    <input type="text" id="inc-location" placeholder="Contoh: Selat Malaka" style="width:100%;padding:8px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px">
                </div>
            </div>
            <label style="font-size:11px;font-weight:bold;color:#475569">TINDAKAN PENANGANAN & TAMBAHAN HARI ETA</label>
            <input type="text" id="inc-action" value="Rerouting jalur aman (+1 Hari ETA)" style="width:100%;padding:8px;margin:4px 0 16px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px">
            <div style="display:flex;gap:10px">
                <button type="button" onclick="document.getElementById('incident-modal').style.display='none'" style="flex:1;background:#e2e8f0;color:#1e293b;border:none;padding:9px;border-radius:6px;font-weight:bold;cursor:pointer">Batal</button>
                <button type="button" onclick="saveNewIncident()" style="flex:1;background:#e11d48;color:#fff;border:none;padding:9px;border-radius:6px;font-weight:bold;cursor:pointer">Kirim Laporan Kendala</button>
            </div>
        </div>
    </div>

    <div id="pod-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);z-index:99998;align-items:center;justify-content:center">
        <div style="background:#fff;padding:24px;border-radius:12px;width:400px;max-width:95vw;box-shadow:0 10px 25px rgba(0,0,0,.3);text-align:left">
            <h3 style="margin-bottom:4px;color:#059669"><i class="bi bi-check2-circle"></i> Bukti Serah Terima (POD)</h3>
            <p id="pod-resi-label" style="font-size:12px;color:#64748b;margin-bottom:12px">No. Resi: CT-2026-001</p>
            <label style="font-size:11px;font-weight:bold;color:#475569">NAMA PENERIMA PAKET DI LOKASI</label>
            <input type="text" id="pod-recipient" placeholder="Contoh: Nadia Putri (Penerima) / Budi (Security)" style="width:100%;padding:8px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px">
            <label style="font-size:11px;font-weight:bold;color:#475569">KONDISI & CATATAN SERAH TERIMA</label>
            <input type="text" id="pod-note" value="Paket diterima lengkap, segel utuh & baik" style="width:100%;padding:8px;margin:4px 0 16px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px">
            <div style="display:flex;gap:10px">
                <button type="button" onclick="document.getElementById('pod-modal').style.display='none'" style="flex:1;background:#e2e8f0;color:#1e293b;border:none;padding:9px;border-radius:6px;font-weight:bold;cursor:pointer">Batal</button>
                <button type="button" onclick="confirmPodDelivery()" style="flex:1;background:#059669;color:#fff;border:none;padding:9px;border-radius:6px;font-weight:bold;cursor:pointer">Simpan & Tandai Sampai</button>
            </div>
        </div>
    </div>
    <div id="receipt-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);z-index:99998;align-items:center;justify-content:center">
        <div style="background:#fff;padding:22px;border-radius:12px;width:410px;max-width:95vw;border:2px dashed #94a3b8;box-shadow:0 10px 25px rgba(0,0,0,.3);text-align:left">
            <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid #e2e8f0;padding-bottom:8px;margin-bottom:10px">
                <div><b style="font-size:15px;color:#0f172a">CARGOTRACE WAYBILL</b><p style="font-size:10px;color:#64748b">OFFICIAL LOGISTICS RECEIPT</p></div>
                <span id="rec-mode" class="b bc">AIR FREIGHT</span>
            </div>
            <div class="barcode"></div>
            <p id="rec-resi" style="text-align:center;font-family:monospace;font-weight:bold;font-size:14px;letter-spacing:2px;margin-bottom:10px">CT-2026-001</p>
            <div style="font-size:12px;display:flex;flex-direction:column;gap:5px;background:#f8fafc;padding:11px;border-radius:8px;border:1px solid #e2e8f0">
                <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Pengirim:</span><b id="rec-sender">-</b></div>
                <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Penerima:</span><b id="rec-receiver">-</b></div>
                <div style="font-size:11px;color:#475569;border-bottom:1px dashed #cbd5e1;padding-bottom:5px;margin-bottom:2px">Alamat: <span id="rec-address">-</span></div>
                <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Isi Barang:</span><b id="rec-item">-</b></div>
                <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Carrier & Rute:</span><b id="rec-route">-</b></div>
                <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Estimasi Tiba (ETA):</span><b id="rec-eta" style="color:#0891b2">-</b></div>
                <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Total Tarif:</span><b id="rec-cost" style="color:#059669">-</b></div>
                <div style="display:flex;justify-content:space-between"><span style="color:#64748b">Status:</span><b id="rec-status">-</b></div>
                <div style="margin-top:4px;padding:6px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:5px;font-size:11px;color:#065f46"><b>Bukti Serah (POD):</b> <span id="rec-pod">-</span></div>
            </div>
            <div class="no-print" style="display:flex;gap:10px;margin-top:14px">
                <button type="button" onclick="document.getElementById('receipt-modal').style.display='none'" style="flex:1;background:#e2e8f0;color:#1e293b;border:none;padding:9px;border-radius:6px;font-weight:bold;cursor:pointer">Tutup</button>
                <button type="button" onclick="window.print()" style="flex:1;background:#0f172a;color:#fff;border:none;padding:9px;border-radius:6px;font-weight:bold;cursor:pointer"><i class="bi bi-printer"></i> Print / PDF</button>
            </div>
        </div>
    </div>

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
        let currentRole='Administrator',map=null,tMap=null,chart=null,totalShip=129,inTransit=85,delivered=36,delayedCount=8,exceptionCount=4,simTimer=null,focusedResi=null,podTargetResi=null;
        let dailyShip=14,monthlyShip=129,yearlyShip=1541,mapMarkers={},tMapMarkers={};
        const shipmentsData=[
            {owner:'corporate',resi:'CT-2026-001',sender:'PT Astra Mesin (021-88912)',receiver:'EuroTech BV (+31-10442)',address:'Havenstraat 45, Rotterdam, NL',item:'Komponen Mesin (450 Kg)',mode:'Sea',carrier:'Nusantara Ocean',orig:'Tanjung Priok',dest:'Rotterdam',eta:'28 Nov 2026',cost:'Rp 11.250.000',origCoord:[-6.1045,106.8800],destCoord:[51.9244,4.4777],status:'IN TRANSIT',progress:55,incident:'',pod:'Belum diserahterimakan'},
            {owner:'corporate',resi:'CT-2026-002',sender:'CV Nusantara Chip (021-55210)',receiver:'Tokyo Micro Corp (+81-3551)',address:'Minato-ku 2-14, Tokyo, JP',item:'Semiconductor (120 Kg)',mode:'Air',carrier:'Garuda Cargo',orig:'Soekarno-Hatta',dest:'Tokyo',eta:'16 Nov 2026',cost:'Rp 9.000.000',origCoord:[-6.1256,106.6558],destCoord:[35.6762,139.6503],status:'IN TRANSIT',progress:65,incident:'',pod:'Belum diserahterimakan'},
            {owner:'customer',resi:'CT-2026-003',sender:'Saimah Store (0812-6411-90)',receiver:'Nadia Putri (0813-7700-21)',address:'Jl. Sudirman Kav. 21, Jakarta Selatan',item:'Paket Skincare (4 Kg)',mode:'Air',carrier:'Garuda Cargo',orig:'Medan',dest:'Jakarta',eta:'16 Nov 2026',cost:'Rp 300.000',origCoord:[3.5952,98.6722],destCoord:[-6.2088,106.8456],status:'IN TRANSIT',progress:45,incident:'',pod:'Belum diserahterimakan'}
        ];
        const cityCoords={'medan':[3.5952,98.6722],'kuala lumpur':[3.1390,101.6869],'jakarta':[-6.2088,106.8456],'tanjung priok':[-6.1045,106.8800],'soekarno-hatta':[-6.1256,106.6558],'surabaya':[-7.2575,112.7521],'singapore':[1.2902,103.8519],'changi':[1.3644,103.9915],'tokyo':[35.6762,139.6503],'narita':[35.7720,140.3929],'rotterdam':[51.9244,4.4777],'shanghai':[31.2304,121.4737],'aceh':[5.5483,95.3238],'banda aceh':[5.5483,95.3238],'batam':[1.0456,104.0305],'makassar':[-5.1477,119.4327],'bali':[-8.4095,115.1889],'bangkok':[13.7563,100.5018],'hong kong':[22.3193,114.1694],'seoul':[37.5665,126.9780],'dubai':[25.2048,55.2708],'london':[51.5072,-0.1276],'los angeles':[34.0522,-118.2437]};
        function cleanStr(v){return String(v||'').replace(/[<>"']/g,'').trim();}
        function getCoord(name){const k=name.toLowerCase().trim();for(const c in cityCoords){if(k.includes(c))return cityCoords[c];}return [2.5+(Math.random()*4-2),104.0+(Math.random()*8-4)];}
        function getInterpPos(s){const p=Math.min(100,Math.max(0,s.progress))/100;return [s.origCoord[0]+(s.destCoord[0]-s.origCoord[0])*p,s.origCoord[1]+(s.destCoord[1]-s.origCoord[1])*p];}
        const allTabs=['dashboard','shipments','tracking','exceptions','carriers','reports','currency','audit-logs','notifications','world-news','weather','users'];
        const roleMenus={'Administrator':allTabs,'Logistics Operator':['dashboard','shipments','tracking','exceptions','carriers','reports','notifications','world-news','weather'],'Customer':['dashboard','shipments','tracking','notifications','weather']};

        function renderTables(){
            const q=(document.getElementById('search-ship')?.value||'').toLowerCase();
            const stFilter=document.getElementById('filter-status')?.value||'ALL';
            const list=currentRole==='Customer'?shipmentsData.filter(x=>x.owner==='customer'):shipmentsData;
            const shipBody=document.getElementById('shipment-table-body');
            const repBody=document.getElementById('report-table-body');
            if(shipBody){
                shipBody.innerHTML=list.filter(s=>{
                    const txt=`${s.resi} ${s.sender} ${s.receiver} ${s.item} ${s.orig} ${s.dest}`.toLowerCase();
                    return txt.includes(q)&&(stFilter==='ALL'||s.status===stFilter);
                }).map(s=>{
                    const bCls=s.status==='SAMPAI'?'bg':(s.status==='DITOLAK'||s.status==='DELAYED'?'br':(s.status==='MENUNGGU ACC'?'ba':'bc'));
                    const mBadge=s.mode==='Sea'?'<span style="font-size:11px;color:#0891b2"><i class="bi bi-ship"></i> Sea Freight</span>':'<span style="font-size:11px;color:#059669"><i class="bi bi-airplane"></i> Air Freight</span>';
                    const opBtns=currentRole==='Logistics Operator'?`<button type="button" onclick="accShipment('${s.resi}')" style="background:#0891b2;color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:10px;cursor:pointer">ACC</button><button type="button" onclick="openIncidentModal('${s.resi}')" style="background:#f59e0b;color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:10px;cursor:pointer">Kendala</button><button type="button" onclick="openPodModal('${s.resi}')" style="background:#059669;color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:10px;cursor:pointer">Sampai (POD)</button><button type="button" onclick="rejectShipment('${s.resi}')" style="background:#e11d48;color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:10px;cursor:pointer">Tolak</button>`:'';
                    const incHtml=s.incident?`<div style="font-size:11px;color:#be123c;margin-top:3px"><i class="bi bi-exclamation-triangle-fill"></i> ${s.incident}</div>`:'';
                    const podHtml=s.status==='SAMPAI'?`<div style="font-size:11px;color:#059669;margin-top:3px"><i class="bi bi-check2-all"></i> POD: <b>${s.pod}</b></div>`:'';
                    return `<tr><td><b>${s.resi}</b></td><td><span style="font-size:11px;color:#64748b">Dari:</span> <b>${s.sender}</b><br><span style="font-size:11px;color:#64748b">Ke:</span> <b>${s.receiver}</b></td><td><b>${s.item}</b><br>${mBadge}</td><td>${s.orig} → ${s.dest}<br><span style="font-size:11px;color:#64748b">${s.carrier}</span><br><span style="font-size:11px;color:#0e7490;font-weight:bold"><i class="bi bi-calendar-event"></i> ETA: ${s.eta}</span></td><td><b>${s.cost}</b></td><td><span class="b ${bCls}">${s.status}</span>${incHtml}${podHtml}<div style="margin-top:6px;display:flex;gap:4px;flex-wrap:wrap">${opBtns}<button type="button" onclick="openReceipt('${s.resi}')" style="background:#0f172a;color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:10px;cursor:pointer"><i class="bi bi-printer"></i> Resi</button></div></td></tr>`;
                }).join('');
            }
            if(repBody){
                repBody.innerHTML=list.map(s=>{
                    const bCls=s.status==='SAMPAI'?'bg':(s.status==='DITOLAK'||s.status==='DELAYED'?'br':(s.status==='MENUNGGU ACC'?'ba':'bc'));
                    return `<tr><td><b>${s.resi}</b></td><td>${s.sender} → ${s.receiver}</td><td>${s.orig} → ${s.dest}</td><td>${s.eta}</td><td><span class="b ${bCls}">${s.status}</span></td><td>${s.status==='SAMPAI'?s.pod:'-'}</td></tr>`;
                }).join('');
            }
        }

        function drawMap(id,storeObj,onlyResi=null){
            try{
                if(typeof L==='undefined')return null;
                Object.keys(storeObj).forEach(k=>delete storeObj[k]);
                const sIcon=L.divIcon({className:'i-ship',html:'<i class="bi bi-ship"></i>',iconSize:[30,30],iconAnchor:[15,15]});
                const pIcon=L.divIcon({className:'i-plane',html:'<i class="bi bi-airplane"></i>',iconSize:[30,30],iconAnchor:[15,15]});
                const m=L.map(id).setView([2.5,103.5],4);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(m);
                let focusBounds=null;
                shipmentsData.forEach(s=>{
                    if(s.status==='DITOLAK')return;
                    if(currentRole==='Customer'&&s.owner!=='customer')return;
                    if(onlyResi&&s.resi!==onlyResi)return;
                    const col=s.mode==='Sea'?'#0891b2':'#059669',ic=s.mode==='Sea'?sIcon:pIcon;
                    L.polyline([s.origCoord,s.destCoord],{color:col,weight:3.5,dashArray:'6,6'}).addTo(m);
                    L.circleMarker(s.origCoord,{radius:5,color:col,fillOpacity:1}).addTo(m).bindTooltip("Asal: "+s.orig);
                    L.circleMarker(s.destCoord,{radius:6,color:'#e11d48',fillOpacity:1}).addTo(m).bindTooltip("Tujuan: "+s.dest);
                    const mk=L.marker(getInterpPos(s),{icon:ic}).addTo(m).bindPopup(`<b>${s.resi} (${s.item})</b><br>ETA: <b>${s.eta}</b><br>${s.orig} → ${s.dest}<br>Status: <b>${s.status} (${s.progress}%)</b>`);
                    storeObj[s.resi]=mk;
                    if(onlyResi===s.resi||currentRole==='Customer'){focusBounds=L.latLngBounds([s.origCoord,s.destCoord]);setTimeout(()=>mk.openPopup(),200);}
                });
                if(focusBounds)m.fitBounds(focusBounds,{padding:[45,45]});
                return m;
            }catch(e){console.error(e);return null;}
        }
        function toggleSim(){
            const btns=document.querySelectorAll('.sim-btn');
            if(simTimer){
                clearInterval(simTimer);simTimer=null;
                btns.forEach(b=>{b.style.background='#0f172a';b.innerHTML='<i class="bi bi-play-fill"></i> ▶ Simulasi Gerak Live';});
            }else{
                simTimer=setInterval(simTick,450);
                btns.forEach(b=>{b.style.background='#059669';b.innerHTML='<i class="bi bi-pause-fill"></i> ⏸ Hentikan Simulasi';});
            }
        }
        function simTick(){
            let anyMoving=false;
            shipmentsData.forEach(s=>{
                if(s.status==='IN TRANSIT'&&s.progress<100){
                    anyMoving=true;s.progress=Math.min(100,s.progress+3);
                    const pos=getInterpPos(s);
                    if(mapMarkers[s.resi]){mapMarkers[s.resi].setLatLng(pos);mapMarkers[s.resi].setPopupContent(`<b>${s.resi} (${s.item})</b><br>ETA: <b>${s.eta}</b><br>${s.orig} → ${s.dest}<br>Status: <b>${s.status} (${s.progress}%)</b>`);}
                    if(tMapMarkers[s.resi]){tMapMarkers[s.resi].setLatLng(pos);tMapMarkers[s.resi].setPopupContent(`<b>${s.resi} (${s.item})</b><br>ETA: <b>${s.eta}</b><br>${s.orig} → ${s.dest}<br>Status: <b>${s.status} (${s.progress}%)</b>`);}
                    if(focusedResi===s.resi)updateQuickTrackUI(s);
                    if(s.progress>=100)finalizeDelivery(s.resi,s.receiver+' (Tiba di Tujuan)',true);
                }
            });
            if(!anyMoving&&simTimer)toggleSim();
        }
        function formatEta(days){const d=new Date();d.setDate(d.getDate()+days);return d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'});}
        function calcTariffAndEta(){
            const w=parseFloat(document.getElementById('new-weight').value)||0,mode=document.getElementById('new-mode').value;
            const total=w*(mode==='Air'?75000:25000);
            document.getElementById('new-cost').value=total>0?'Rp '+total.toLocaleString('id-ID'):'';
            document.getElementById('new-eta').value=formatEta(mode==='Air'?2:14)+(mode==='Air'?' (2 Hari)':' (14 Hari)');
        }
        function refreshStatsAndChart(){
            document.getElementById('stat-total').innerText=totalShip;document.getElementById('stat-transit').innerText=inTransit;
            document.getElementById('stat-delivered').innerText=delivered;document.getElementById('stat-delayed').innerText=delayedCount;document.getElementById('stat-exceptions').innerText=exceptionCount;
            if(chart){chart.data.datasets[0].data=[inTransit,delivered,delayedCount,exceptionCount];chart.update();}
        }
        function exportCSV(){
            const list=currentRole==='Customer'?shipmentsData.filter(x=>x.owner==='customer'):shipmentsData;
            const headers=['No Resi','Pengirim','Penerima','Alamat','Barang','Mode','Carrier','Asal','Tujuan','ETA','Biaya','Status','POD'];
            const rows=list.map(s=>[s.resi,s.sender,s.receiver,s.address,s.item,s.mode,s.carrier,s.orig,s.dest,s.eta,s.cost,s.status,s.pod].map(v=>`"${String(v).replace(/"/g,'""')}"`).join(','));
            const blob=new Blob(['\uFEFF'+headers.join(',')+'\n'+rows.join('\n')],{type:'text/csv;charset=utf-8;'});
            const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='Laporan_CargoTrace_2026.csv';document.body.appendChild(a);a.click();document.body.removeChild(a);
        }
        function convertCurrency(){
            const idr=parseFloat(document.getElementById('curr-idr-input').value)||0,target=document.getElementById('curr-target').value;
            const rates={USD:[15850,'$'],MYR:[3620,'RM'],SGD:[11840,'S$'],EUR:[17120,'€'],JPY:[104.5,'¥'],CNY:[2190,'¥'],GBP:[20150,'£'],AUD:[10420,'A$'],KRW:[11.45,'₩'],SAR:[4225,'SR']};
            const [r,sym]=rates[target]||[15850,'$'];
            document.getElementById('curr-result').value=`${sym} ${(idr/r).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})} ${target}`;
        }
        function quickTrack(){
            const q=document.getElementById('quick-track-input').value.trim().toUpperCase(),found=shipmentsData.find(x=>x.resi.toUpperCase()===q);
            if(!found){alert('Nomor Resi tidak ditemukan!');return;}
            if(currentRole==='Customer'&&found.owner!=='customer'){alert('Akses Ditolak: Nomor Resi '+q+' bukan milik akun Anda!');return;}
            focusedResi=found.resi;updateQuickTrackUI(found);
            if(map){map.remove();map=null;}
            map=drawMap('map',mapMarkers,found.resi);
        }
        function updateQuickTrackUI(s){
            document.getElementById('quick-track-result').style.display='block';
            document.getElementById('qt-title').innerText=`${s.resi} • ${s.item} → ${s.receiver}`;
            document.getElementById('qt-eta').innerText='ETA: '+s.eta;
            const bd=document.getElementById('qt-badge');bd.innerText=`${s.status} (${s.progress}%)`;
            bd.className=s.status==='SAMPAI'?'b bg':(s.status==='DITOLAK'||s.status==='DELAYED'?'b br':'b bc');
            document.getElementById('qt-bar').style.width=s.progress+'%';
            document.getElementById('qt-orig').innerText='Asal: '+s.orig;
            document.getElementById('qt-mid').innerText=s.progress>=100?'POD: '+s.pod:`Progres Rute: ${s.progress}%`;
            document.getElementById('qt-dest').innerText='Tujuan: '+s.dest;
        }
        function resetQuickTrack(){
            focusedResi=null;document.getElementById('quick-track-input').value='';document.getElementById('quick-track-result').style.display='none';
            if(map){map.remove();map=null;}
            map=drawMap('map',mapMarkers,null);
        }
        function updateCustomerStats(){
            const mine=shipmentsData.filter(x=>x.owner==='customer');
            document.getElementById('cust-stat-total').innerText=mine.length;
            document.getElementById('cust-stat-pending').innerText=mine.filter(x=>x.status==='MENUNGGU ACC').length;
            document.getElementById('cust-stat-transit').innerText=mine.filter(x=>x.status==='IN TRANSIT'||x.status==='DELAYED').length;
            document.getElementById('cust-stat-delivered').innerText=mine.filter(x=>x.status==='SAMPAI').length;
        }
        async function loadNews(){
            const box=document.getElementById('news-container'),badge=document.getElementById('news-status');if(!box)return;
            box.innerHTML='<p style="font-size:13px;color:#64748b">⏳ Mengambil berita dunia real-time terbaru...</p>';
            try{
                const r=await fetch('https://api.rss2json.com/v1/api.json?rss_url='+encodeURIComponent('https://feeds.bbci.co.uk/news/world/rss.xml'));
                const d=await r.json();
                if(d.status==='ok'&&d.items.length){
                    badge.innerText='🔴 LIVE FEED • UPDATED '+new Date().toLocaleTimeString('id-ID');
                    box.innerHTML=d.items.slice(0,5).map(i=>`<a href="${i.link}" target="_blank" rel="noopener noreferrer" class="nl"><div style="display:flex;justify-content:space-between;margin-bottom:4px"><span style="font-size:11px;color:#0891b2;font-weight:bold">BBC LIVE • ${i.pubDate}</span><span style="font-size:11px;color:#0891b2;font-weight:bold">Baca Artikel <i class="bi bi-box-arrow-up-right"></i></span></div><h4 style="font-size:14px;margin-bottom:4px">${i.title}</h4><p style="font-size:12px;color:#64748b">${i.description.replace(/<[^>]*>?/gm,'').slice(0,140)}...</p></a>`).join('');
                }else throw new Error();
            }catch(e){badge.innerText='DIRECT GLOBAL NEWS PORTAL';box.innerHTML=`<a href="https://www.bbc.com/news/world" target="_blank" class="nl"><b>BBC World News — Breaking Global Headlines ↗</b></a>`;}
        }
        function applyRoleVisibility(){
            let visibleNotifs=0;
            document.querySelectorAll('#notification-list > div').forEach(el=>{const show=currentRole!=='Customer'||el.getAttribute('data-owner')==='customer';el.style.display=show?'block':'none';if(show)visibleNotifs++;});
            document.getElementById('notif-badge').innerText=visibleNotifs;
            document.querySelectorAll('#timeline-list > div').forEach(el=>{el.style.display=(currentRole!=='Customer'||el.getAttribute('data-owner')==='customer')?'block':'none';});
            renderTables();updateCustomerStats();
        }
        function initDash(){
            try{
                if(map){map.remove();map=null}
                map=drawMap('map',mapMarkers,focusedResi);loadNews();applyRoleVisibility();
                if(!chart&&typeof Chart!=='undefined'){
                    chart=new Chart(document.getElementById('shipmentChart').getContext('2d'),{type:'bar',data:{labels:['In Transit','Delivered','Delayed','Exceptions'],datasets:[{label:'Shipments',data:[inTransit,delivered,delayedCount,exceptionCount],backgroundColor:['#0891b2','#059669','#e11d48','#d97706']}]},options:{responsive:true,maintainAspectRatio:false,scales:{y:{beginAtZero:true}}}});
                }
            }catch(e){console.error(e);}
        }
        function switchTab(t){
            allTabs.forEach(x=>{const el=document.getElementById('tab-'+x),btn=document.getElementById('btn-'+x);if(el)el.classList.remove('active');if(btn)btn.classList.remove('active');});
            const targetTab=document.getElementById('tab-'+t),targetBtn=document.getElementById('btn-'+t);
            if(targetTab)targetTab.classList.add('active');
            if(targetBtn)targetBtn.classList.add('active');
            document.getElementById('page-title').innerText=t.replace('-',' ').toUpperCase()+" Management";
            if(t==='dashboard')setTimeout(initDash,50);
            if(t==='tracking')setTimeout(()=>{if(tMap)tMap.remove();tMap=drawMap('map-tracking',tMapMarkers,null)},50);
            if(t==='world-news')loadNews();
            if(t==='shipments'||t==='reports')renderTables();
        }
        function addTimeline(resi,title,desc,color='#0891b2',owner='customer'){
            const list=document.getElementById('timeline-list'),now=new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
            const div=document.createElement('div');div.className='tl-item';div.setAttribute('data-owner',owner);
            div.innerHTML=`<span class="tl-dot" style="background:${color};box-shadow:0 0 0 2px ${color}"></span><div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b"><b>${resi}</b><span>Baru saja, ${now} WIB</span></div><p style="font-size:13px;margin-top:3px"><b>${title}:</b> ${desc}</p>`;
            list.prepend(div);applyRoleVisibility();
        }
        function pushNotif(title,msg,cls,owner='customer'){
            const list=document.getElementById('notification-list'),div=document.createElement('div');
            div.className=cls;div.setAttribute('data-owner',owner);div.style.cssText='padding:12px;border-radius:8px;font-size:13px';
            div.innerHTML=`<b>${title}:</b> ${msg}`;list.prepend(div);applyRoleVisibility();
        }
        function syncRoleEmail(){
            const r=document.getElementById('login-role').value,em=document.getElementById('login-email');
            em.value=r==='Administrator'?'admin@cargotrace.com':(r==='Logistics Operator'?'operator@cargotrace.com':'customer@cargotrace.com');
        }
        function handleLogout(){
            if(confirm('Yakin ingin logout dari sistem CargoTrace?')){
                syncRoleEmail();document.getElementById('login-pass').value='';
                document.getElementById('login-err').style.display='none';document.getElementById('login-overlay').style.display='flex';
            }
        }
        function handleLogin(){
            const r=document.getElementById('login-role').value,em=document.getElementById('login-email').value.trim(),pw=document.getElementById('login-pass').value,err=document.getElementById('login-err');
            if(!em||pw!=='password123'){err.innerText='Email atau password salah (Gunakan password123).';err.style.display='block';return;}
            err.style.display='none';currentRole=r;
            const allowed=roleMenus[r]||allTabs;
            allTabs.forEach(x=>{const b=document.getElementById('btn-'+x);if(b)b.style.display=allowed.includes(x)?'flex':'none';});
            const hb=document.getElementById('user-name'),hs=document.getElementById('user-role'),bn=document.getElementById('role-banner');
            const btnAdd=document.getElementById('btn-add-shipment'),adminBox=document.getElementById('admin-kpi-box'),custBox=document.getElementById('customer-kpi-box'),qtBox=document.getElementById('customer-quick-track');
            const intStats=document.getElementById('internal-stats-box'),anaCard=document.getElementById('analytics-card'),wCard=document.getElementById('weather-card');
            if(r==='Administrator'){
                hb.innerText='Saimah Admin (250180017)';hs.innerText='ADMINISTRATOR';hs.className='b bg';
                bn.className='bg';bn.innerHTML='🔑 Mode Akses: <b>Administrator</b> — Rekapitulasi volume paket Harian, Bulanan, & Tahunan.';
                btnAdd.style.display='none';adminBox.style.display='grid';custBox.style.display='none';qtBox.style.display='none';
                intStats.style.display='grid';anaCard.style.display='block';wCard.style.gridColumn='auto';
                document.getElementById('map-dash-title').innerText='Live Global Tracking Map (Sea Vessel & Air Cargo)';
                document.getElementById('ship-table-title').innerText='Master Data Shipments (Sea & Air Cargo)';
            }else if(r==='Logistics Operator'){
                hb.innerText='Operator Logistik';hs.innerText='LOGISTICS OPERATOR';hs.className='b bc';
                bn.className='bc';bn.innerHTML='🚚 Mode Akses: <b>Logistics Operator</b> — Verifikasi (ACC), Lapor Kendala, & Bukti Serah (POD).';
                btnAdd.style.display='none';adminBox.style.display='none';custBox.style.display='none';qtBox.style.display='block';
                intStats.style.display='grid';anaCard.style.display='block';wCard.style.gridColumn='auto';
                document.getElementById('qt-hint').innerText='Masukkan No. Resi pelanggan mana pun untuk melacak (Contoh: CT-2026-001)';
                document.getElementById('map-dash-title').innerText='Live Global Tracking Map (Sea Vessel & Air Cargo)';
                document.getElementById('ship-table-title').innerText='Master Data Shipments (Semua Pelanggan)';
            }else{
                hb.innerText='Customer BSN';hs.innerText='CUSTOMER';hs.className='b ba';
                bn.className='ba';bn.innerHTML='📦 Mode Akses: <b>Customer</b> — Menampilkan data & pelacakan khusus paket milik Anda.';
                btnAdd.style.display='inline-block';adminBox.style.display='none';custBox.style.display='grid';qtBox.style.display='block';
                intStats.style.display='none';anaCard.style.display='none';wCard.style.gridColumn='1 / -1';
                document.getElementById('qt-hint').innerText='Lacak paket milik Anda saja (Contoh: CT-2026-003 atau resi baru Anda)';
                document.getElementById('map-dash-title').innerText='Peta Pelacakan Paket Saya';
                document.getElementById('ship-table-title').innerText='Daftar Pengiriman Paket Saya';
            }
            resetQuickTrack();applyRoleVisibility();
            document.getElementById('login-overlay').style.display='none';
            switchTab('dashboard');
        }
        function openAddModal(){calcTariffAndEta();document.getElementById('add-modal').style.display='flex';}
        function closeAddModal(){document.getElementById('add-modal').style.display='none';}
        function saveNewShipment(){
            const sender=cleanStr(document.getElementById('new-sender').value),receiver=cleanStr(document.getElementById('new-receiver').value),address=cleanStr(document.getElementById('new-address').value);
            const item=cleanStr(document.getElementById('new-item').value),w=cleanStr(document.getElementById('new-weight').value),mode=document.getElementById('new-mode').value;
            const carrier=cleanStr(document.getElementById('new-carrier').value),orig=cleanStr(document.getElementById('new-origin').value),dest=cleanStr(document.getElementById('new-dest').value),cost=cleanStr(document.getElementById('new-cost').value);
            if(!sender||!receiver||!address||!item||!w||!carrier||!orig||!dest||!cost){alert('Semua kolom identitas pengirim, penerima, & barang wajib diisi!');return;}
            const resi='CT-2026-0'+Math.floor(10+Math.random()*90),eta=formatEta(mode==='Air'?2:14);
            shipmentsData.unshift({owner:'customer',resi,sender,receiver,address,item:item+' ('+w+' Kg)',mode,carrier,orig,dest,eta,cost,origCoord:getCoord(orig),destCoord:getCoord(dest),status:'MENUNGGU ACC',progress:5,incident:'',pod:'Belum diserahterimakan'});
            totalShip++;inTransit++;dailyShip++;monthlyShip++;yearlyShip++;
            refreshStatsAndChart();
            document.getElementById('kpi-daily').innerText=dailyShip+' Paket';document.getElementById('kpi-monthly').innerText=monthlyShip+' Paket';document.getElementById('kpi-yearly').innerText=yearlyShip.toLocaleString('id-ID')+' Paket';
            addTimeline(resi,'Pesanan Dibuat',`Dari ${sender} untuk ${receiver}. Estimasi Tiba (ETA): ${eta}.`,'#d97706','customer');
            pushNotif('📦 Pesanan Baru '+resi,`Kiriman untuk ${receiver} (${orig} → ${dest}) dengan ETA ${eta} menunggu ACC.`,'ba','customer');
            alert('Pesanan berhasil dibuat!\nNo Resi: '+resi+'\nEstimasi Tiba: '+eta);
            closeAddModal();
            ['new-sender','new-receiver','new-address','new-item','new-weight','new-carrier','new-origin','new-dest','new-cost'].forEach(id=>document.getElementById(id).value='');
        }
        function openIncidentModal(preResi=null){
            const sel=document.getElementById('inc-resi-select');
            const activeList=shipmentsData.filter(x=>x.status!=='SAMPAI'&&x.status!=='DITOLAK');
            sel.innerHTML=activeList.length?activeList.map(s=>`<option value="${s.resi}">${s.resi} — ${s.item} (${s.orig} → ${s.dest})</option>`).join(''):'<option value="">-- Tidak ada kargo aktif --</option>';
            if(preResi)sel.value=preResi;
            document.getElementById('inc-location').value='';
            document.getElementById('incident-modal').style.display='flex';
        }
        function saveNewIncident(){
            const resi=document.getElementById('inc-resi-select').value,type=cleanStr(document.getElementById('inc-type').value),sev=document.getElementById('inc-severity').value;
            const loc=cleanStr(document.getElementById('inc-location').value),act=cleanStr(document.getElementById('inc-action').value)||'Rerouting & Penjadwalan Ulang';
            if(!resi||!loc){alert('Pilih No. Resi dan isi Lokasi Kejadian kendala!');return;}
            const s=shipmentsData.find(x=>x.resi===resi),owner=s?s.owner:'customer',incId='INC-'+Math.floor(8803+Math.random()*900);
            if(s){
                s.status='DELAYED';s.incident=`${type} di ${loc}`;
                if(!s.eta.includes('+1 Hari'))s.eta=s.eta+' (+1 Hari Delay)';
                if(focusedResi===resi)updateQuickTrackUI(s);
            }
            const sevCls=sev==='HIGH'?'br':(sev==='MEDIUM'?'ba':'bc');
            const tr=document.createElement('tr');
            tr.innerHTML=`<td><b>${incId}</b></td><td><b>${resi}</b></td><td>${type}</td><td><span class="b ${sevCls}">${sev}</span></td><td>${loc}</td><td id="inc-act-${incId}"><b style="color:#b45309">${act}</b> <button type="button" onclick="resolveIncident('${incId}','${resi}')" style="margin-left:6px;background:#059669;color:#fff;border:none;padding:3px 7px;border-radius:4px;font-size:10px;cursor:pointer">Selesaikan</button></td>`;
            document.getElementById('exceptions-table-body').prepend(tr);
            delayedCount++;exceptionCount++;refreshStatsAndChart();
            addTimeline(resi,'Kendala Dilaporkan ('+incId+')',`${type} di ${loc}. Tindakan: ${act}.`,'#e11d48',owner);
            pushNotif('⚠️ Kendala Pengiriman '+resi,`${type} di ${loc}. Penanganan: ${act}.`,'br',owner);
            document.getElementById('incident-modal').style.display='none';
            alert('Laporan kendala '+incId+' untuk resi '+resi+' berhasil dicatat!');
        }
        function resolveIncident(incId,resi){
            const actCell=document.getElementById('inc-act-'+incId);if(actCell)actCell.innerHTML='<span class="b bg">✓ RESOLVED / SELESAI</span>';
            const s=shipmentsData.find(x=>x.resi===resi),owner=s?s.owner:'customer';
            if(s&&s.status==='DELAYED'){s.status='IN TRANSIT';s.incident='';if(focusedResi===resi)updateQuickTrackUI(s);}
            if(delayedCount>0)delayedCount--;
            refreshStatsAndChart();
            addTimeline(resi,'Kendala Teratasi ('+incId+')','Hambatan selesai ditangani, kargo kembali normal.','#0891b2',owner);
            pushNotif('✅ Kendala Teratasi '+resi,'Pengiriman kembali normal menuju tujuan.','bc',owner);
        }
        function accShipment(resi){
            const s=shipmentsData.find(x=>x.resi===resi),owner=s?s.owner:'customer';
            if(s){s.status='IN TRANSIT';if(s.progress<15)s.progress=15;}
            addTimeline(resi,'Di-ACC & Berangkat','Dokumen disetujui Operator, kargo bergerak menuju '+(s?s.receiver:'tujuan')+'.','#0891b2',owner);
            pushNotif('✅ Pesanan Disetujui '+resi,'Pengiriman telah di-ACC oleh Operator dan sedang dalam perjalanan.','bc',owner);
            if(!simTimer)toggleSim();
            alert('Pengiriman '+resi+' di-ACC!');
        }
        function openPodModal(resi){
            podTargetResi=resi;document.getElementById('pod-resi-label').innerText='No. Resi: '+resi;
            document.getElementById('pod-recipient').value='';document.getElementById('pod-modal').style.display='flex';
        }
        function confirmPodDelivery(){
            const rec=cleanStr(document.getElementById('pod-recipient').value),note=cleanStr(document.getElementById('pod-note').value)||'Diterima lengkap & baik';
            if(!rec){alert('Nama penerima di lokasi wajib diisi sebagai bukti serah terima!');return;}
            const now=new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
            finalizeDelivery(podTargetResi,`${rec} (${note} • ${now} WIB)`,false);
            document.getElementById('pod-modal').style.display='none';
        }
        function finalizeDelivery(resi,podSummary,silent=false){
            const s=shipmentsData.find(x=>x.resi===resi),owner=s?s.owner:'customer';
            if(s&&s.status==='SAMPAI')return;
            if(s){
                if(s.status==='DELAYED'&&delayedCount>0)delayedCount--;
                s.status='SAMPAI';s.progress=100;s.incident='';s.pod=podSummary;
                if(mapMarkers[resi])mapMarkers[resi].setLatLng(s.destCoord);
                if(tMapMarkers[resi])tMapMarkers[resi].setLatLng(s.destCoord);
                if(focusedResi===resi)updateQuickTrackUI(s);
            }
            delivered++;if(inTransit>0)inTransit--;
            refreshStatsAndChart();
            addTimeline(resi,'Bukti Serah Terima (POD) Tercatat','Paket diterima oleh: '+podSummary,'#059669',owner);
            pushNotif('🎉 Paket Sudah Sampai '+resi,'Serah terima selesai. Diterima oleh: '+podSummary,'bg',owner);
            if(!silent)alert('Bukti Serah Terima (POD) untuk '+resi+' berhasil disimpan!');
        }
        function rejectShipment(resi){
            const s=shipmentsData.find(x=>x.resi===resi),owner=s?s.owner:'customer';
            if(s){s.status='DITOLAK';s.progress=0;s.pod='Dibatalkan';}
            addTimeline(resi,'Pengiriman Dibatalkan','Pesanan ditolak oleh Operator logistik.','#e11d48',owner);
            pushNotif('❌ Pesanan Ditolak '+resi,'Pengiriman dibatalkan oleh Operator logistik.','br',owner);
            alert('Pengiriman '+resi+' ditolak.');
        }
        function openReceipt(resi){
            const s=shipmentsData.find(x=>x.resi===resi);if(!s)return;
            document.getElementById('rec-resi').innerText=s.resi;document.getElementById('rec-mode').innerText=s.mode.toUpperCase()+' FREIGHT';
            document.getElementById('rec-sender').innerText=s.sender;document.getElementById('rec-receiver').innerText=s.receiver;
            document.getElementById('rec-address').innerText=s.address;document.getElementById('rec-item').innerText=s.item;
            document.getElementById('rec-route').innerText=s.carrier+' ('+s.orig+' → '+s.dest+')';document.getElementById('rec-eta').innerText=s.eta;
            document.getElementById('rec-cost').innerText=s.cost;document.getElementById('rec-status').innerText=s.status;
            document.getElementById('rec-pod').innerText=s.pod;document.getElementById('receipt-modal').style.display='flex';
        }
        window.addEventListener('load', initDash);
    </script>
</body>
</html>