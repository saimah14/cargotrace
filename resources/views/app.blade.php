<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        #btn-add-shipment[hidden]{display:none!important}
        .bc{background:#cffafe;color:#155e75}.bg{background:#d1fae5;color:#065f46}.br{background:#ffe4e6;color:#9f1239}.ba{background:#fef3c7;color:#b45309}
        .i-ship,.i-plane{display:flex;align-items:center;justify-content:center;color:#fff;border-radius:50%;font-size:17px;border:2px solid #fff;box-shadow:0 2px 5px rgba(0,0,0,.3)}
        .i-ship{background:#0891b2}.i-plane{background:#059669}
        .tp{display:none}.tp.active{display:block}
        .tl-box{border-left:2px solid #0891b2;margin-left:10px;padding-left:18px;display:flex;flex-direction:column;gap:14px}
        .tl-item{position:relative;background:#f8fafc;padding:10px 14px;border-radius:8px;border:1px solid #e2e8f0}
        .tl-dot{position:absolute;left:-25px;top:14px;width:12px;height:12px;border-radius:50%;background:#0891b2;border:2px solid #fff;box-shadow:0 0 0 2px #0891b2}
        .barcode{height:36px;width:100%;background:repeating-linear-gradient(90deg,#0f172a 0 2px,transparent 2px 5px,#0f172a 5px 9px,transparent 9px 11px,#0f172a 11px 12px,transparent 12px 16px);margin:8px 0 4px}
        .prog-wrap{width:100%;height:10px;background:#e2e8f0;border-radius:99px;overflow:hidden;margin:8px 0}
        .prog-fill{height:100%;background:linear-gradient(90deg,#0891b2,#059669);width:0%;transition:width .35s ease}
        :root{color-scheme:light;--ink:#0f172a;--muted:#64748b;--line:#e2e8f0;--accent:#0891b2;--surface:#fff}
        body{font-family:Inter,"Segoe UI",Roboto,Arial,sans-serif;background:#eef3f8;color:#1e293b}
        aside{background:linear-gradient(180deg,#0b172b 0%,#10233c 100%);box-shadow:8px 0 28px rgba(15,23,42,.08)}
        .sh{padding:20px 16px;border-bottom-color:rgba(148,163,184,.16)}
        .lb{background:linear-gradient(135deg,#22d3ee,#0891b2);box-shadow:0 6px 16px rgba(8,145,178,.28)}
        .sn{padding:14px 10px}
        .nb{min-height:40px;padding:10px 12px;border:1px solid transparent;transition:background .18s ease,color .18s ease,border-color .18s ease,transform .18s ease}
        .nb:hover{background:rgba(148,163,184,.12);transform:translateX(2px)}
        .nb.active{background:linear-gradient(100deg,#0891b2,#0e7490);box-shadow:0 5px 14px rgba(8,145,178,.2)}
        main{background:radial-gradient(ellipse at 85% 0%,rgba(14,165,233,.055),transparent 36%),#f5f8fc}
        header{position:sticky;top:0;z-index:1000;min-height:74px;padding:14px 28px;box-shadow:0 4px 16px rgba(15,23,42,.035)}
        header h2{color:var(--ink);letter-spacing:-.025em}
        .cb{width:100%;max-width:1680px;margin:0 auto;padding:24px 28px}
        .c{border-color:#e5ebf2;border-radius:14px;box-shadow:0 5px 18px rgba(15,23,42,.045);transition:box-shadow .2s ease,border-color .2s ease}
        .c:hover{border-color:#d6e3ed;box-shadow:0 8px 24px rgba(15,23,42,.065)}
        .sc{border-radius:10px}
        .nl{border-radius:10px}
        h3{letter-spacing:-.012em}
        input,select,textarea{font:inherit;transition:border-color .18s ease,box-shadow .18s ease}
        input:focus,select:focus,textarea:focus{outline:none!important;border-color:#0891b2!important;box-shadow:0 0 0 3px rgba(8,145,178,.14)!important}
        button{font-family:inherit;transition:filter .18s ease,box-shadow .18s ease,transform .18s ease}
        button:hover{filter:brightness(.96);box-shadow:0 4px 12px rgba(15,23,42,.12)}
        button:active{transform:translateY(1px)}
        button:focus-visible,a:focus-visible{outline:3px solid rgba(8,145,178,.45);outline-offset:2px}
        .b{border-radius:999px;padding:4px 9px;letter-spacing:.015em}
        .ct-delay-card{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:-2px 0 16px;padding:12px 14px;border:1px solid #fecaca;border-left:4px solid #e11d48;border-radius:10px;background:linear-gradient(90deg,#fff7f7,#fff)}
        .ct-delay-card strong{color:#9f1239;font-size:12px}
        .ct-delay-card p{color:#64748b;font-size:11px;margin-top:3px}
        .ct-delay-eta{display:inline-flex;flex-direction:column;gap:2px;padding:6px 8px;border-radius:8px;background:#fff1f2;border:1px solid #fecdd3;color:#9f1239;font-size:10px}
        .ct-delay-eta b{font-size:11px}
        table{border-spacing:0}
        th{font-size:11px;text-transform:uppercase;letter-spacing:.045em}
        tbody tr{transition:background .15s ease}
        tbody tr:hover{background:#f8fbfd}
        td{color:#334155}
        #map,#map-tracking{border:1px solid #dbe5ed;box-shadow:inset 0 0 0 1px rgba(255,255,255,.55)}
        .tp.active{animation:tab-enter .22s ease-out}
        @keyframes tab-enter{from{opacity:.72;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}
        .news-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,320px),1fr));gap:16px}
        .news-card{padding:0;overflow:hidden;border-radius:14px;background:#fff}
        .news-image-wrap{position:relative;display:grid;place-items:center;aspect-ratio:16/8.5;overflow:hidden;background:linear-gradient(135deg,#0f2742,#087e8b)}
        .news-image-fallback{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;gap:12px;color:#e0f2fe;background:radial-gradient(circle at 75% 30%,rgba(34,211,238,.32),transparent 30%),linear-gradient(135deg,#102a43,#087e8b);font-size:13px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
        .news-image-fallback i{font-size:36px;color:#67e8f9}
        .news-photo{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;transition:transform .35s ease}
        .news-card:hover .news-photo{transform:scale(1.035)}
        .news-content{padding:15px 16px 17px}
        .news-meta{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:9px}
        .news-source,.news-date{font-size:10px;color:#0e7490;font-weight:700}
        .news-date{color:#64748b;font-weight:500;white-space:nowrap}
        .news-title{font-size:15px;line-height:1.45;color:#0f172a;margin-bottom:7px}
        .news-summary{font-size:12px;line-height:1.6;color:#64748b}
        .news-read{display:inline-flex;align-items:center;gap:5px;margin-top:12px;color:#0891b2;font-size:11px;font-weight:700}
        .currency-summary{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin:14px 0 18px}
        .currency-highlight{min-width:0;padding:14px 15px;border:1px solid #e2e8f0;border-radius:12px;background:linear-gradient(145deg,#fff,#f8fbfd);text-align:left;cursor:pointer}
        .currency-highlight:hover{border-color:#67e8f9;background:#f0fdff}
        .currency-highlight-top{display:flex;justify-content:space-between;align-items:center;gap:8px}
        .currency-code{display:inline-flex;align-items:center;justify-content:center;min-width:42px;padding:4px 8px;border-radius:7px;background:#ecfeff;color:#0e7490;font-size:11px;font-weight:800}
        .currency-highlight-country{overflow:hidden;color:#64748b;font-size:11px;text-overflow:ellipsis;white-space:nowrap}
        .currency-highlight-rate{margin-top:10px;color:#0f172a;font-size:17px;font-weight:800;letter-spacing:-.02em}
        .currency-highlight-caption{margin-top:3px;color:#64748b;font-size:10px}
        .currency-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px}
        .currency-search-wrap{position:relative;flex:1;min-width:220px}
        .currency-search-wrap i{position:absolute;top:50%;left:12px;transform:translateY(-50%);color:#94a3b8}
        .currency-search-wrap input{padding-left:36px!important;margin-bottom:0!important}
        .currency-table{width:100%;min-width:0!important;table-layout:fixed}
        .currency-table th:nth-child(1),.currency-table td:nth-child(1){width:14%}
        .currency-table th:nth-child(2),.currency-table td:nth-child(2){width:25%}
        .currency-table th:nth-child(3),.currency-table td:nth-child(3){width:37%}
        .currency-table th:nth-child(4),.currency-table td:nth-child(4){width:24%}
        .currency-country{color:#475569;line-height:1.45}
        .currency-country-more{display:inline-block;margin-left:4px;color:#0891b2;font-size:11px;font-weight:700;white-space:nowrap}
        .currency-pagination{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:12px;padding-top:12px;border-top:1px solid #e2e8f0}
        .currency-pagination button{min-width:82px;padding:7px 10px;border:1px solid #cbd5e1;border-radius:7px;background:#fff;color:#334155;font-size:12px;font-weight:700}
        .currency-pagination button:disabled{opacity:.45;cursor:not-allowed;box-shadow:none}
        ::selection{background:rgba(8,145,178,.18);color:#0f172a}
        *{scrollbar-color:#cbd5e1 transparent;scrollbar-width:thin}
        @media(max-width:1100px){
            .g5{grid-template-columns:repeat(3,minmax(0,1fr))}
            .g4{grid-template-columns:repeat(2,minmax(0,1fr))}
            .cb{padding:20px}
            header{padding:12px 20px}
        }
        @media(max-width:760px){
            body{height:100vh;height:100dvh;width:100%;flex-direction:column}
            aside{width:100%;min-width:0;height:auto;flex:0 0 auto;box-shadow:0 5px 18px rgba(15,23,42,.12)}
            .sh{padding:10px 14px}
            .sn{display:flex;flex-direction:row;gap:6px;padding:7px 10px;overflow-x:auto;overflow-y:hidden}
            .nb{width:auto;min-width:max-content;min-height:36px;padding:8px 10px;font-size:12px}
            .nb:hover{transform:none}
            main{height:auto;min-height:0;flex:1}
            header{position:relative;align-items:flex-start;gap:10px;padding:12px 16px}
            header>div:last-child{flex-wrap:wrap;justify-content:flex-end;font-size:12px}
            .cb{padding:16px}
            .gd{grid-template-columns:1fr}
            table{min-width:760px}
            .c:has(> table){overflow-x:auto}
        }
        @media(max-width:520px){
            header{flex-direction:column}
            header>div:last-child{justify-content:flex-start}
            .cb{padding:12px}
            .g5,.g4,.g3{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
            .g5>.c,.g4>.c,.g3>.c{padding:12px}
            .c{padding:13px;border-radius:12px}
            #map{height:230px!important}
            .sim-btn{max-width:100%}
            .news-grid{grid-template-columns:1fr}
            .currency-summary{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
            .currency-highlight{padding:11px}
            .currency-highlight-rate{font-size:15px}
            .currency-table th:nth-child(2),.currency-table td:nth-child(2){display:none}
            .currency-table th:nth-child(1),.currency-table td:nth-child(1){width:22%}
            .currency-table th:nth-child(3),.currency-table td:nth-child(3){width:44%}
            .currency-table th:nth-child(4),.currency-table td:nth-child(4){width:34%}
            .currency-pagination button{min-width:72px}
        }
        @media(max-width:360px){
            .g5,.g4,.g3{grid-template-columns:1fr}
        }
        @media(prefers-reduced-motion:reduce){
            *,*::before,*::after{scroll-behavior:auto!important;animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important}
        }
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
                <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display:none">@csrf</form>
            </div>
        </header>

        <div class="cb">
            <div id="tab-dashboard" class="tp active">
                <div id="role-banner" class="bg" style="width:100%;padding:8px 14px;border-radius:8px;margin-bottom:14px;font-size:12px">🔑 Mode Akses: <b>Administrator</b> — Rekapitulasi volume paket Harian, Bulanan, & Tahunan.</div>
                <p id="dashboard-data-mode" style="display:none;color:#92400e;background:#fef3c7;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px"></p>
                <p id="dashboard-load-error" style="display:none;color:#9f1239;background:#ffe4e6;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px"></p>

                <div id="admin-kpi-box" class="g4">
                    <div class="c" style="border-top:3px solid #0891b2;margin-bottom:0">
                        <p style="font-size:11px;color:#64748b"><i class="bi bi-calendar-day" style="color:#0891b2"></i> PAKET HARI INI</p>
                        <p id="kpi-daily" style="font-size:22px;font-weight:bold;color:#0f172a;margin:4px 0">14 Paket</p>
                        <span id="kpi-daily-detail" style="font-size:11px;color:#059669">Data demo</span>
                    </div>
                    <div class="c" style="border-top:3px solid #059669;margin-bottom:0">
                        <p id="kpi-month-label" style="font-size:11px;color:#64748b"><i class="bi bi-calendar-month" style="color:#059669"></i> PAKET BULAN INI</p>
                        <p id="kpi-monthly" style="font-size:22px;font-weight:bold;color:#0f172a;margin:4px 0">129 Paket</p>
                        <span id="kpi-monthly-detail" style="font-size:11px;color:#059669">Data demo</span>
                    </div>
                    <div class="c" style="border-top:3px solid #6366f1;margin-bottom:0">
                        <p id="kpi-year-label" style="font-size:11px;color:#64748b"><i class="bi bi-calendar3" style="color:#6366f1"></i> PAKET TAHUN INI</p>
                        <p id="kpi-yearly" style="font-size:22px;font-weight:bold;color:#0f172a;margin:4px 0">1.541 Paket</p>
                        <span id="kpi-yearly-detail" style="font-size:11px;color:#0891b2">Data demo</span>
                    </div>
                    <div class="c" style="border-top:3px solid #f59e0b;margin-bottom:0">
                        <p style="font-size:11px;color:#64748b"><i class="bi bi-cash-stack" style="color:#d97706"></i> OMZET BULAN INI</p>
                        <p id="kpi-revenue" style="font-size:20px;font-weight:bold;color:#059669;margin:4px 0">Rp 482,5 Jt</p>
                        <span id="kpi-revenue-detail" style="font-size:11px;color:#64748b">Data demo</span>
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
                <div id="delay-watch-list"></div>
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
                        <div><h3 id="ship-table-title">Master Data Shipments (Sea & Air Cargo)</h3><p style="font-size:11px;color:#64748b;margin-top:3px">ACC dilakukan oleh Administrator atau Logistics Operator. Setelah disetujui, kapal/pesawat mulai bergerak.</p></div>
                        <div style="display:flex;gap:8px;flex-wrap:wrap">
                            <button type="button" onclick="exportCSV()" style="background:#0f172a;color:#fff;border:none;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer"><i class="bi bi-file-earmark-spreadsheet"></i> Export Excel / CSV</button>
                            <button type="button" id="btn-add-shipment" hidden style="background:#059669;color:#fff;border:none;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer"><i class="bi bi-plus-lg"></i> + Tambah Kiriman Barang</button>
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
                    <p id="shipments-load-error" style="display:none;color:#9f1239;background:#ffe4e6;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px"></p>
                    <table>
                        <thead>
                            <tr><th>No Resi</th><th>Pengirim & Penerima</th><th>Barang & Mode</th><th>Rute, Carrier & ETA</th><th>Biaya (IDR)</th><th>Status, Kendala & POD</th></tr>
                        </thead>
                        <tbody id="shipment-table-body"></tbody>
                    </table>
                </div>
                @include('partials.operator-admin-tools')
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
                    <p id="tracking-load-error" style="display:none;color:#9f1239;background:#ffe4e6;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px"></p>
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
                    <button type="button" id="btn-report-incident" onclick="openIncidentModal(null)" style="background:#e11d48;color:#fff;border:none;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer"><i class="bi bi-exclamation-octagon"></i> + Lapor Kendala Kargo</button>
                </div>
                <p id="exceptions-load-error" style="display:none;color:#9f1239;background:#ffe4e6;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px"></p>
                <table>
                    <thead>
                        <tr><th>ID Insiden</th><th>No Resi</th><th>Jenis Kendala</th><th>Severity</th><th>Status</th><th>Waktu Terdeteksi</th><th>Aksi</th></tr>
                    </thead>
                    <tbody id="exceptions-table-body">
                        @if(auth()->user()->hasRole('admin', 'operator'))
                            <tr data-demo-row="true"><td><b>INC-8801</b></td><td><b>CT-2026-001</b></td><td>Typhoon Weather & High Swells</td><td><span class="b br">HIGH</span></td><td><span class="b ba">Ditangani</span></td><td>Hari ini, 16:20 WIB</td><td>—</td></tr>
                            <tr data-demo-row="true"><td><b>INC-8802</b></td><td><b>CT-2026-003</b></td><td>Port Berth Congestion (+24h)</td><td><span class="b ba">MEDIUM</span></td><td><span class="b ba">Ditangani</span></td><td>Hari ini, 20:10 WIB</td><td>—</td></tr>
                        @endif
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
                                <option value="USD">USD — United States Dollar ($)</option>
                            </select>
                        </div>
                        <div style="flex:2;min-width:180px">
                            <label style="font-size:11px;font-weight:bold;color:#059669">HASIL KONVERSI</label>
                            <input type="text" id="curr-result" readonly value="Memuat kurs terbaru..." style="width:100%;padding:8px 12px;margin-top:4px;border:1px solid #059669;background:#f0fdf4;font-weight:bold;color:#065f46;border-radius:6px;font-size:14px">
                        </div>
                    </div>
                </div>
                <div class="c">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap">
                        <div>
                            <h3 style="font-size:16px">Kurs Mata Uang Dunia</h3>
                            <p id="currency-updated" style="font-size:12px;color:#64748b;margin-top:5px">Mengambil data kurs terbaru...</p>
                        </div>
                        <button type="button" id="currency-refresh" onclick="loadCurrencyRates(true)" style="background:#059669;color:#fff;border:none;padding:8px 12px;border-radius:7px;font-size:12px;font-weight:bold;cursor:pointer"><i class="bi bi-arrow-clockwise"></i> Perbarui Kurs</button>
                    </div>
                    <div id="currency-summary" class="currency-summary"></div>
                    <div class="currency-toolbar">
                        <div class="currency-search-wrap">
                            <i class="bi bi-search"></i>
                            <input type="search" id="currency-search" oninput="searchCurrencyRates()" placeholder="Cari negara atau mata uang, misalnya Malaysia / Japan / MYR / JPY" aria-label="Cari negara atau kode mata uang" style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:7px;font-size:13px">
                        </div>
                        <span id="currency-result-count" style="font-size:11px;color:#64748b;white-space:nowrap"></span>
                    </div>
                    <table class="currency-table">
                        <thead><tr><th>Kode</th><th>Mata Uang</th><th>Negara / Wilayah</th><th>1 unit → IDR</th></tr></thead>
                        <tbody id="currency-rates-body"><tr><td colspan="4" style="color:#64748b;text-align:center;padding:18px">Memuat kurs terbaru...</td></tr></tbody>
                    </table>
                    <div class="currency-pagination">
                        <button type="button" id="currency-prev" onclick="changeCurrencyPage(-1)" disabled><i class="bi bi-chevron-left"></i> Sebelumnya</button>
                        <span id="currency-page-info" style="font-size:11px;color:#64748b;text-align:center"></span>
                        <button type="button" id="currency-next" onclick="changeCurrencyPage(1)" disabled>Berikutnya <i class="bi bi-chevron-right"></i></button>
                    </div>
                    <p style="font-size:11px;color:#64748b;margin-top:10px">Sumber: FXRatesAPI. Kurs dicek ulang otomatis setiap menit; data merupakan kurs referensi, bukan kurs transaksi bank.</p>
                </div>
            </div>
            <div id="tab-audit-logs" class="tp"><div class="c"><h3 style="margin-bottom:12px">System Security & Audit Logs</h3>
                <p id="audit-load-error" style="display:none;color:#9f1239;background:#ffe4e6;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px"></p>
                <table>
                    <thead><tr><th>Timestamp</th><th>User</th><th>Activity</th><th>Entity</th><th>Changes</th></tr></thead>
                    <tbody id="audit-table-body"></tbody>
                </table>
            </div></div>

            <div id="tab-notifications" class="tp"><div class="c">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <h3>Shipment & System Notifications</h3>
                    <span class="b bc">REAL-TIME ALERTS</span>
                </div>
                <p id="notification-load-error" style="display:none;color:#9f1239;background:#ffe4e6;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px"></p>
                <div id="notification-list" style="display:flex;flex-direction:column;gap:10px">
                    <div class="bg" data-owner="customer" data-demo="true" style="padding:12px;border-radius:8px;font-size:13px"><b>✅ Paket Anda CT-2026-003:</b> Paket Skincare (4 Kg) untuk Nadia Putri dalam penerbangan Medan → Jakarta (ETA: 16 Nov 2026).</div>
                    <div class="bg" data-owner="corporate" data-demo="true" style="padding:12px;border-radius:8px;font-size:13px"><b>✅ Status Pengiriman CT-2026-002:</b> Paket Semiconductor telah lepas landas menuju Tokyo (ETA: 16 Nov 2026).</div>
                    <div class="bc" data-owner="corporate" data-demo="true" style="padding:12px;border-radius:8px;font-size:13px"><b>🚢 Info Pelayaran CT-2026-001:</b> Kapal Nusantara Ocean melewati Selat Singapura menuju Rotterdam (ETA: 28 Nov 2026).</div>
                </div>
            </div></div>

            <div id="tab-world-news" class="tp"><div class="c">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:14px">
                    <div><h3 style="font-size:15px">Global Breaking World News (Live Real-Time)</h3><span id="news-status" class="b br" style="margin-top:4px">CONNECTING LIVE FEED...</span></div>
                </div>
                <div id="news-container" class="news-grid"></div>
            </div></div>

            <div id="tab-weather" class="tp"><div class="c">
                <h3 style="margin-bottom:6px">Cuaca Global (Semua Negara)</h3>
                <p style="font-size:12px;color:#64748b;margin-bottom:14px">Cari nama negara atau kota untuk melihat kondisi cuaca terkini.</p>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
                    <input type="search" id="weather-location-query" list="weather-country-options" placeholder="Contoh: Indonesia, Jepang, atau Rotterdam" aria-label="Nama negara atau kota" style="flex:1;min-width:220px;padding:8px 12px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                    <datalist id="weather-country-options"></datalist>
                    <button type="button" onclick="loadCountryWeather()" style="background:#0891b2;color:#fff;border:none;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer">Cari Cuaca</button>
                </div>
                <p id="weather-country-list-status" style="font-size:11px;color:#64748b;margin-bottom:10px"></p>
                <p id="weather-load-error" style="display:none;color:#9f1239;background:#ffe4e6;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px"></p>
                <div id="weather-result" aria-live="polite" style="padding:14px;border:1px solid #e2e8f0;border-radius:8px;background:#f8fafc;color:#64748b;font-size:13px">Masukkan negara atau kota untuk memuat cuaca terkini.</div>
                <p style="font-size:11px;color:#64748b;margin-top:10px">Sumber data: Open-Meteo. Lokasi dicari melalui OpenStreetMap.</p>
            </div></div>

            <div id="tab-users" class="tp"><div class="c"><h3 style="margin-bottom:12px">User Management & Role Access</h3>
                <p id="users-load-error" style="display:none;color:#9f1239;background:#ffe4e6;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px"></p>
                <table>
                    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Exception Pengiriman</th><th>Konfigurasi Role</th></tr></thead>
                    <tbody id="users-table-body">
                        <tr><td>250180017</td><td><b>Saimah Admin</b></td><td>admin@cargotrace.com</td><td><span class="b bg">ADMINISTRATOR</span></td><td>Tidak ada exception terkait.</td></tr>
                        <tr><td>OP-01</td><td><b>Operator Logistik</b></td><td>operator@cargotrace.com</td><td><span class="b bc">OPERATOR</span></td><td>Tidak ada exception terkait.</td></tr>
                        <tr><td>CS-01</td><td><b>Customer BSN</b></td><td>customer@cargotrace.com</td><td><span class="b ba">CUSTOMER</span></td><td>CT-2026-003 — Port Berth Congestion (penanganan berjalan)</td></tr>
                    </tbody>
                </table>
            </div></div>
        </div>
    </main>
    <div id="shipment-status-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.65);z-index:99998;align-items:center;justify-content:center;padding:16px;">
        <div style="background:#fff;padding:22px;border-radius:12px;width:380px;max-width:95vw;box-shadow:0 10px 25px rgba(0,0,0,.3);">
            <h3 style="margin-bottom:5px;color:#0f172a;">Perbarui status kiriman</h3>
            <p id="quick-status-resi" style="font-size:12px;color:#64748b;margin-bottom:12px;"></p>
            <div style="padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;">
                <div>Status sekarang: <b id="quick-status-current"></b></div>
                <div style="margin-top:5px;">Status berikutnya: <b id="quick-status-next" style="color:#0891b2"></b></div>
            </div>
            <p style="font-size:11px;color:#64748b;margin-top:8px;">Tekan tombol untuk melanjutkan kiriman ke tahap berikutnya.</p>
            <p id="quick-status-error" role="status" style="display:none;color:#9f1239;background:#ffe4e6;padding:8px;border-radius:6px;font-size:12px;margin:10px 0;"></p>
            <div style="display:flex;gap:8px;margin-top:14px;">
                <button type="button" onclick="document.getElementById('shipment-status-modal').style.display='none'" style="flex:1;background:#e2e8f0;color:#1e293b;border:none;padding:9px;border-radius:6px;font-weight:bold;cursor:pointer;">Batal</button>
                <button type="button" id="quick-status-save" onclick="saveNextShipmentStatus()" style="flex:1;background:#0891b2;color:#fff;border:none;padding:9px;border-radius:6px;font-weight:bold;cursor:pointer;">Lanjutkan</button>
            </div>
        </div>
    </div>
    <div id="incident-modal" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.65);z-index:99998;align-items:center;justify-content:center">
        <div style="background:#fff;padding:24px;border-radius:12px;width:420px;max-width:95vw;box-shadow:0 10px 25px rgba(0,0,0,.3);text-align:left">
            <h3 style="margin-bottom:4px;color:#e11d48"><i class="bi bi-exclamation-triangle-fill"></i> Form Lapor Kendala Pengiriman</h3>
            <p style="font-size:11px;color:#64748b;margin-bottom:12px">Melaporkan kendala akan mengubah status kargo menjadi DELAYED & memperbarui ETA</p>
            <label style="font-size:11px;font-weight:bold;color:#475569">PILIH NO. RESI KARGO</label>
            <select id="inc-resi-select" style="width:100%;padding:8px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px"></select>
            <label style="font-size:11px;font-weight:bold;color:#475569">JENIS KENDALA / HAMBATAN</label>
            <select id="inc-type" style="width:100%;padding:8px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px">
                <option value="WEATHER_DELAY">Cuaca buruk / keterlambatan cuaca</option>
                <option value="PORT_CONGESTION">Kepadatan pelabuhan</option>
                <option value="CUSTOMS_HOLD">Penahanan bea cukai</option>
                <option value="DAMAGED">Barang rusak</option>
                <option value="MISROUTED">Salah rute</option>
                <option value="DOCUMENTATION_ISSUE">Masalah dokumen</option>
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
            <input type="text" id="inc-action" value="Rerouting jalur aman" style="width:100%;padding:8px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px">
            <label style="font-size:11px;font-weight:bold;color:#475569">ESTIMASI PENUNDAAN</label>
            <select id="inc-delay-hours" style="width:100%;padding:8px;margin:4px 0 16px;border:1px solid #cbd5e1;border-radius:6px;font-size:12px">
                <option value="6">6 jam</option>
                <option value="12">12 jam</option>
                <option value="24" selected>24 jam / 1 hari</option>
                <option value="48">48 jam / 2 hari</option>
                <option value="72">72 jam / 3 hari</option>
                <option value="168">7 hari</option>
            </select>
            <p id="incident-error" style="display:none;color:#9f1239;background:#ffe4e6;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px"></p>
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
        <div style="background:#fff;padding:28px;border-radius:12px;width:min(360px,calc(100vw - 32px));max-height:calc(100vh - 32px);overflow-y:auto;text-align:left;box-shadow:0 10px 25px rgba(0,0,0,.3)">
            <div style="text-align:center;margin-bottom:16px">
                <div style="background:#06b6d4;color:#fff;width:44px;height:44px;line-height:44px;border-radius:10px;font-weight:bold;margin:0 auto 10px">CT</div>
                <h3 style="margin-bottom:4px">CargoTrace Login</h3>
                <p style="font-size:12px;color:#64748b">Global Shipment Visibility Platform</p>
            </div>
            <p id="login-err" style="display:none;background:#ffe4e6;color:#9f1239;padding:8px;border-radius:6px;font-size:12px;margin-bottom:10px;text-align:center"></p>
            <div id="login-form">
                <label style="font-size:11px;font-weight:bold;color:#475569">EMAIL</label>
                <input type="email" id="login-email" placeholder="Masukkan email" style="width:100%;padding:8px 10px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                <label style="font-size:11px;font-weight:bold;color:#475569">PASSWORD</label>
                <input type="password" id="login-pass" placeholder="Masukkan password" style="width:100%;padding:8px 10px;margin:4px 0 16px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                <button type="button" onclick="handleLogin()" style="width:100%;background:#0891b2;color:#fff;border:none;padding:10px;border-radius:6px;font-weight:bold;cursor:pointer">Masuk ke Sistem</button>
                <p style="font-size:12px;color:#64748b;text-align:center;margin-top:16px">Belum punya akun? <button type="button" onclick="showCreateAccount()" style="background:none;border:none;color:#0891b2;font-weight:bold;cursor:pointer">Buat akun baru</button></p>
            </div>
            <div id="create-account-form" style="display:none">
                <label style="font-size:11px;font-weight:bold;color:#475569">NAMA LENGKAP</label>
                <input type="text" id="register-name" maxlength="100" autocomplete="name" placeholder="Masukkan nama lengkap" style="width:100%;padding:8px 10px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                <label style="font-size:11px;font-weight:bold;color:#475569">EMAIL</label>
                <input type="email" id="register-email" maxlength="150" autocomplete="email" placeholder="Masukkan email" style="width:100%;padding:8px 10px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                <label style="font-size:11px;font-weight:bold;color:#475569">PASSWORD (MINIMAL 8 KARAKTER)</label>
                <input type="password" id="register-pass" minlength="8" autocomplete="new-password" placeholder="Buat password" style="width:100%;padding:8px 10px;margin:4px 0 10px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                <label style="font-size:11px;font-weight:bold;color:#475569">KONFIRMASI PASSWORD</label>
                <input type="password" id="register-pass-confirm" minlength="8" autocomplete="new-password" placeholder="Ulangi password" style="width:100%;padding:8px 10px;margin:4px 0 16px;border:1px solid #cbd5e1;border-radius:6px;font-size:13px">
                <button id="register-submit" type="button" onclick="handleRegister()" style="width:100%;background:#0891b2;color:#fff;border:none;padding:10px;border-radius:6px;font-weight:bold;cursor:pointer">Buat Akun</button>
                <p style="font-size:12px;color:#64748b;text-align:center;margin-top:16px">Sudah punya akun? <button type="button" onclick="showLogin()" style="background:none;border:none;color:#0891b2;font-weight:bold;cursor:pointer">Masuk</button></p>
            </div>
        </div>
    </div>
    <script>
        const authenticatedUser={
            id:@json($authenticatedUser->id),
            name:@json($authenticatedUser->name),
            email:@json($authenticatedUser->email),
            role:@json($authenticatedUser->role)
        };
        const apiBaseUrl=@json(rtrim(request()->getBaseUrl(), '/') . '/api');
        const authenticatedRole=String(authenticatedUser.role||'customer').toLowerCase();
        let currentRole=authenticatedRole==='admin'||authenticatedRole==='administrator'?'Administrator':(authenticatedRole==='operator'||authenticatedRole==='logistics operator'?'Logistics Operator':'Customer');
        let currentAuthToken=@json($apiToken),map=null,tMap=null,chart=null,totalShip=129,inTransit=84,delivered=36,delayedCount=1,exceptionCount=4,simTimer=null,focusedResi=null,podTargetResi=null,lastCreatedResi=null;
        let dailyShip=14,monthlyShip=129,yearlyShip=1541,mapMarkers={},tMapMarkers={},quickStatusShipmentId=null,quickStatusNext=null;
        let fxRates=null,fxCurrencies={},fxRateDate=null,fxRatesLoading=false,fxPage=1,fxRefreshTimer=null,weatherCountriesLoaded=false,weatherCountriesLoading=false;
        const fxPageSize=12;
        const shipmentsData=[
            {owner:'corporate',resi:'CT-2026-001',sender:'PT Astra Mesin (021-88912)',receiver:'EuroTech BV (+31-10442)',address:'Havenstraat 45, Rotterdam, NL',item:'Komponen Mesin (450 Kg)',mode:'Sea',carrier:'Nusantara Ocean',orig:'Tanjung Priok',dest:'Rotterdam',eta:'29 Nov 2026 (+24 jam)',cost:'Rp 11.250.000',origCoord:[-6.1045,106.8800],destCoord:[51.9244,4.4777],status:'DELAYED',progress:55,incident:'Cuaca Badai & Gelombang Tinggi di North Sea',delayHours:24,originalEta:'28 Nov 2026',pod:'Belum diserahterimakan'},
            {owner:'corporate',resi:'CT-2026-002',sender:'CV Nusantara Chip (021-55210)',receiver:'Tokyo Micro Corp (+81-3551)',address:'Minato-ku 2-14, Tokyo, JP',item:'Semiconductor (120 Kg)',mode:'Air',carrier:'Garuda Cargo',orig:'Soekarno-Hatta',dest:'Tokyo',eta:'16 Nov 2026',cost:'Rp 9.000.000',origCoord:[-6.1256,106.6558],destCoord:[35.6762,139.6503],status:'IN TRANSIT',progress:65,incident:'',pod:'Belum diserahterimakan'},
            {owner:'customer',resi:'CT-2026-003',sender:'Saimah Store (0812-6411-90)',receiver:'Nadia Putri (0813-7700-21)',address:'Jl. Sudirman Kav. 21, Jakarta Selatan',item:'Paket Skincare (4 Kg)',mode:'Air',carrier:'Garuda Cargo',orig:'Medan',dest:'Jakarta',eta:'16 Nov 2026',cost:'Rp 300.000',origCoord:[3.5952,98.6722],destCoord:[-6.2088,106.8456],status:'IN TRANSIT',progress:45,incident:'',pod:'Belum diserahterimakan'}
        ];
        const cityCoords={'medan':[3.5952,98.6722],'kuala lumpur':[3.1390,101.6869],'malaysia':[3.1390,101.6869],'port klang':[2.9999,101.3920],'penang':[5.4141,100.3288],'belawan':[3.7820,98.7130],'jakarta':[-6.2088,106.8456],'tanjung priok':[-6.1045,106.8800],'soekarno-hatta':[-6.1256,106.6558],'surabaya':[-7.2575,112.7521],'singapore':[1.2902,103.8519],'changi':[1.3644,103.9915],'tokyo':[35.6762,139.6503],'narita':[35.7720,140.3929],'rotterdam':[51.9244,4.4777],'shanghai':[31.2304,121.4737],'china':[31.2304,121.4737],'beijing':[39.9042,116.4074],'guangzhou':[23.1291,113.2644],'shenzhen':[22.5431,114.0579],'aceh':[5.5483,95.3238],'banda aceh':[5.5483,95.3238],'batam':[1.0456,104.0305],'makassar':[-5.1477,119.4327],'bali':[-8.4095,115.1889],'bangkok':[13.7563,100.5018],'hong kong':[22.3193,114.1694],'seoul':[37.5665,126.9780],'dubai':[25.2048,55.2708],'london':[51.5072,-0.1276],'los angeles':[34.0522,-118.2437]};
        function cleanStr(v){return String(v||'').replace(/[<>"']/g,'').trim();}
        function escapeHtml(value){return String(value??'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));}
        function getCoord(name,mode='Air'){
            const k=String(name||'').toLowerCase().trim(),sea=mode==='Sea'||mode==='Kapal';
            if(!k)return null;
            if(/^(china|tiongkok)(?:\s*\([^)]+\))?$/.test(k))return sea?[31.35,121.60]:[31.1443,121.8083];
            if(/^(malaysia)(?:\s*\([^)]+\))?$/.test(k))return sea?cityCoords['port klang']:[2.7456,101.7099];
            if(k.includes('shanghai'))return sea?[31.35,121.60]:[31.1443,121.8083];
            if(k.includes('beijing'))return sea?[39.0,117.7]:[40.0799,116.6031];
            if(k.includes('guangzhou'))return sea?[22.73,113.55]:[23.3924,113.2988];
            if(k.includes('shenzhen'))return sea?[22.55,114.27]:[22.6393,113.8107];
            if(k.includes('kuala lumpur')||/(^|[^a-z])kl([^a-z]|$)/.test(k))return sea?cityCoords['port klang']:[2.7456,101.7099];
            if(k.includes('medan'))return sea?cityCoords['belawan']:[3.6422,98.8853];
            for(const c in cityCoords){if(c!=='china'&&c!=='malaysia'&&k.includes(c))return cityCoords[c];}
            return null;
        }
        const locationCoordCache=new Map();
        async function resolveCoord(name,mode='Air'){
            const local=getCoord(name,mode);
            if(local)return local;
            const key=String(name||'').trim().toLowerCase();
            if(!key)return null;
            if(locationCoordCache.has(key))return locationCoordCache.get(key);
            try{
                const params=new URLSearchParams({q:key,format:'jsonv2',limit:'1','accept-language':'id'});
                const response=await fetch('https://nominatim.openstreetmap.org/search?'+params.toString(),{
                    headers:{'Accept':'application/json'}
                });
                if(!response.ok)return null;
                const results=await response.json();
                if(!results.length)return null;
                const coord=[Number(results[0].lat),Number(results[0].lon)];
                if(!Number.isFinite(coord[0])||!Number.isFinite(coord[1]))return null;
                locationCoordCache.set(key,coord);
                return coord;
            }catch(error){
                console.warn('Pencarian koordinat gagal:',name,error);
                return null;
            }
        }
        function getRouteCoords(s){
            const start=s.origCoord,end=s.destCoord,from=String(s.orig||'').toLowerCase(),to=String(s.dest||'').toLowerCase();
            if(s.mode==='Sea'){
                const malacca=/medan|belawan|kuala lumpur|(^|[^a-z])kl([^a-z]|$)|malaysia|port klang|singapore/.test(from);
                const eastAsia=/china|shanghai|beijing|guangzhou|shenzhen|hong kong/.test(to);
                const reverseMalacca=/medan|belawan|kuala lumpur|(^|[^a-z])kl([^a-z]|$)|malaysia|port klang|singapore/.test(to);
                const reverseEastAsia=/china|shanghai|beijing|guangzhou|shenzhen|hong kong/.test(from);
                const via=[[1.25,104.0],[5.5,109.0],[10.0,112.5],[16.0,115.5],[22.0,118.5]];
                if(malacca&&eastAsia)return [start].concat(via,[end]);
                if(reverseMalacca&&reverseEastAsia)return [start].concat(via.slice().reverse(),[end]);
                return [start,end];
            }
            const toVector=([lat,lng])=>{const p=lat*Math.PI/180,l=lng*Math.PI/180;return [Math.cos(p)*Math.cos(l),Math.cos(p)*Math.sin(l),Math.sin(p)];};
            const a=toVector(start),b=toVector(end),dot=Math.max(-1,Math.min(1,a[0]*b[0]+a[1]*b[1]+a[2]*b[2])),omega=Math.acos(dot);
            if(omega<1e-6)return [start,end];
            const sinOmega=Math.sin(omega),points=[];
            for(let i=0;i<=40;i++){
                const t=i/40,wa=Math.sin((1-t)*omega)/sinOmega,wb=Math.sin(t*omega)/sinOmega;
                const x=wa*a[0]+wb*b[0],y=wa*a[1]+wb*b[1],z=wa*a[2]+wb*b[2];
                points.push([Math.atan2(z,Math.sqrt(x*x+y*y))*180/Math.PI,Math.atan2(y,x)*180/Math.PI]);
            }
            return points;
        }
        function getInterpPos(s){
            const route=getRouteCoords(s),p=Math.min(100,Math.max(0,s.progress))/100*(route.length-1),index=Math.min(Math.floor(p),route.length-2),fraction=p-index;
            return [route[index][0]+(route[index+1][0]-route[index][0])*fraction,route[index][1]+(route[index+1][1]-route[index][1])*fraction];
        }
        const allTabs=['dashboard','shipments','tracking','exceptions','carriers','reports','currency','audit-logs','notifications','world-news','weather','users'];
        const roleMenus={'Administrator':allTabs,'Logistics Operator':['dashboard','shipments','tracking','exceptions','carriers','reports','notifications','world-news','weather'],'Customer':['dashboard','shipments','tracking','exceptions','notifications','weather']};

        function renderTables(){
            const q=(document.getElementById('search-ship')?.value||'').toLowerCase();
            const stFilter=document.getElementById('filter-status')?.value||'ALL';
            const list=currentRole==='Customer'?shipmentsData.filter(x=>x.owner==='customer'):shipmentsData;
            const shipBody=document.getElementById('shipment-table-body');
            const repBody=document.getElementById('report-table-body');
            if(shipBody){
                const filtered=list.filter(s=>{
                    const txt=`${s.resi} ${s.sender} ${s.receiver} ${s.item} ${s.orig} ${s.dest}`.toLowerCase();
                    return txt.includes(q)&&(stFilter==='ALL'||s.status===stFilter);
                });
                shipBody.innerHTML=filtered.length?filtered.map(s=>{
                    const bCls=s.status==='SAMPAI'?'bg':(s.status==='DITOLAK'||s.status==='DELAYED'?'br':(s.status==='MENUNGGU ACC'?'ba':'bc'));
                    const mBadge=s.mode==='Sea'?'<span style="font-size:11px;color:#0891b2"><i class="bi bi-ship"></i> Sea Freight</span>':'<span style="font-size:11px;color:#059669"><i class="bi bi-airplane"></i> Air Freight</span>';
                    const resi=escapeHtml(s.resi),resiArg=escapeHtml(JSON.stringify(String(s.resi)).replace(/</g,'\\u003c'));
                    const canOperate=currentRole==='Administrator'||currentRole==='Logistics Operator';
                    const updateButton=canOperate&&s.id?`<button type="button" onclick="openShipmentStatus(${Number(s.id)})" style="background:#0891b2;color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:10px;cursor:pointer">Perbarui status</button>`:'';
                    const demoButtons=canOperate&&!s.id?`${s.status==='MENUNGGU ACC'?`<button type="button" onclick="accShipment(${resiArg})" style="background:#0891b2;color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:10px;cursor:pointer">ACC</button><button type="button" onclick="rejectShipment(${resiArg})" style="background:#e11d48;color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:10px;cursor:pointer">Tolak</button>`:''}${s.status!=='SAMPAI'&&s.status!=='DITOLAK'?`<button type="button" onclick="openIncidentModal(${resiArg})" style="background:#f59e0b;color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:10px;cursor:pointer">Kendala</button>`:''}${s.status==='IN TRANSIT'||s.status==='DELAYED'?`<button type="button" onclick="openPodModal(${resiArg})" style="background:#059669;color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:10px;cursor:pointer">Sampai (POD)</button>`:''}`:'';
                    const opBtns=`${updateButton}${demoButtons}`;
                    const approvalHtml=s.status==='MENUNGGU ACC'?'<div style="font-size:11px;color:#b45309;margin-top:3px">Menunggu ACC Administrator / Operator</div>':(s.approvedBy?`<div style="font-size:11px;color:#059669;margin-top:3px">Di-ACC oleh ${escapeHtml(s.approvedBy)}</div>`:'');
                    const incHtml=s.incident?`<div style="font-size:11px;color:#be123c;margin-top:3px"><i class="bi bi-exclamation-triangle-fill"></i> ${escapeHtml(s.incident)}</div>`:'';
                    const etaHtml=s.status==='DELAYED'
                        ? `<span class="ct-delay-eta"><span>ETA asli: ${escapeHtml(s.originalEta||s.eta)}</span><b><i class="bi bi-clock-history"></i> ETA baru: ${escapeHtml(s.eta)}</b></span>`
                        : `<span style="font-size:11px;color:#0e7490;font-weight:bold"><i class="bi bi-calendar-event"></i> ETA: ${escapeHtml(s.eta)}</span>`;
                    const podHtml=s.status==='SAMPAI'?`<div style="font-size:11px;color:#059669;margin-top:3px"><i class="bi bi-check2-all"></i> POD: <b>${escapeHtml(s.pod)}</b></div>`:'';
                    return `<tr><td><b>${resi}</b></td><td><span style="font-size:11px;color:#64748b">Dari:</span> <b>${escapeHtml(s.sender)}</b><br><span style="font-size:11px;color:#64748b">Ke:</span> <b>${escapeHtml(s.receiver)}</b></td><td><b>${escapeHtml(s.item)}</b><br>${mBadge}</td><td>${escapeHtml(s.orig)} → ${escapeHtml(s.dest)}<br><span style="font-size:11px;color:#64748b">${escapeHtml(s.carrier)}</span><br>${etaHtml}</td><td><b>${escapeHtml(s.cost)}</b></td><td><span class="b ${bCls}">${escapeHtml(s.status)}</span>${approvalHtml}${incHtml}${podHtml}<div style="margin-top:6px;display:flex;gap:4px;flex-wrap:wrap">${opBtns}<button type="button" onclick="openReceipt(${resiArg})" style="background:#0f172a;color:#fff;border:none;padding:3px 6px;border-radius:4px;font-size:10px;cursor:pointer"><i class="bi bi-printer"></i> Resi</button></div></td></tr>`;
                }).join(''):'<tr><td colspan="6" style="text-align:center;color:#64748b">Tidak ada shipment yang sesuai.</td></tr>';
            }
            if(repBody){
                repBody.innerHTML=list.length?list.map(s=>{
                    const bCls=s.status==='SAMPAI'?'bg':(s.status==='DITOLAK'||s.status==='DELAYED'?'br':(s.status==='MENUNGGU ACC'?'ba':'bc'));
                    return `<tr><td><b>${escapeHtml(s.resi)}</b></td><td>${escapeHtml(s.sender)} → ${escapeHtml(s.receiver)}</td><td>${escapeHtml(s.orig)} → ${escapeHtml(s.dest)}</td><td>${escapeHtml(s.eta)}</td><td><span class="b ${bCls}">${escapeHtml(s.status)}</span></td><td>${s.status==='SAMPAI'?escapeHtml(s.pod):'-'}</td></tr>`;
                }).join(''):'<tr><td colspan="6" style="text-align:center;color:#64748b">Tidak ada data pengiriman.</td></tr>';
            }
        }

        function addShipmentToMap(s,m,storeObj){
            const col=s.mode==='Sea'?'#0891b2':'#059669';
            const icon=L.divIcon({className:s.mode==='Sea'?'i-ship':'i-plane',html:s.mode==='Sea'?'<span aria-label="Kapal kargo">🚢</span>':'<span aria-label="Pesawat kargo">✈</span>',iconSize:[30,30],iconAnchor:[15,15]});
            L.polyline(getRouteCoords(s),{color:col,weight:3.5,dashArray:'6,6'}).addTo(m);
            L.circleMarker(s.origCoord,{radius:5,color:col,fillOpacity:1}).addTo(m).bindTooltip("Asal: "+s.orig);
            L.circleMarker(s.destCoord,{radius:6,color:'#e11d48',fillOpacity:1}).addTo(m).bindTooltip("Tujuan: "+s.dest);
            const marker=L.marker(s.currentCoord||getInterpPos(s),{icon}).addTo(m).bindPopup(`<b>${escapeHtml(s.resi)} (${escapeHtml(s.item)})</b><br>ETA: <b>${escapeHtml(s.eta)}</b><br>${escapeHtml(s.orig)} → ${escapeHtml(s.dest)}${s.currentLocation?`<br>Lokasi terkini: ${escapeHtml(s.currentLocation)}`:''}<br>Status: <b>${escapeHtml(s.status)} (${s.progress}%)</b>`);
            storeObj[s.resi]=marker;
            return marker;
        }
        function drawMap(id,storeObj,onlyResi=null){
            try{
                if(typeof L==='undefined')return null;
                Object.keys(storeObj).forEach(k=>delete storeObj[k]);
                const m=L.map(id).setView([2.5,103.5],4);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(m);
                let focusBounds=null;
                shipmentsData.forEach(s=>{
                    if(s.status==='DITOLAK')return;
                    if(currentRole==='Customer'&&s.owner!=='customer')return;
                    if(onlyResi&&s.resi!==onlyResi)return;
                    const marker=addShipmentToMap(s,m,storeObj);
                    if(onlyResi===s.resi||currentRole==='Customer'){focusBounds=L.latLngBounds([s.origCoord,s.destCoord]);setTimeout(()=>marker.openPopup(),200);}
                });
                if(focusBounds)m.fitBounds(focusBounds,{padding:[45,45]});
                return m;
            }catch(e){console.error(e);return null;}
        }
        function focusShipmentRoute(m,storeObj,resi){
            const s=shipmentsData.find(x=>x.resi===resi);
            if(!m||!s)return;
            m.fitBounds(L.latLngBounds(getRouteCoords(s)),{padding:[35,35],maxZoom:6});
            if(storeObj[resi])setTimeout(()=>storeObj[resi].openPopup(),200);
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
        function refreshStatsAndChart(){
            document.getElementById('stat-total').innerText=totalShip;document.getElementById('stat-transit').innerText=inTransit;
            document.getElementById('stat-delivered').innerText=delivered;document.getElementById('stat-delayed').innerText=delayedCount;document.getElementById('stat-exceptions').innerText=exceptionCount;
            if(chart){chart.data.datasets[0].data=[inTransit,delivered,delayedCount,exceptionCount];chart.update();}
            renderDelayWatch();
        }
        async function loadDashboardStats(){
            const error=document.getElementById('dashboard-load-error');
            error.style.display='none';
            try{
                const response=await fetch(apiBaseUrl+'/dashboard',{headers:{'Accept':'application/json','Authorization':'Bearer '+currentAuthToken}});
                const stats=await response.json();
                if(!response.ok)throw new Error(stats.message||'Gagal memuat rekap shipment.');
                const dataMode=document.getElementById('dashboard-data-mode');
                if(Number(stats.total_shipments)===0){
                    dataMode.innerText='Belum ada shipment tersimpan di database. Tabel dan peta menampilkan data demo.';
                    dataMode.style.display='block';
                    return;
                }
                dataMode.style.display='none';
                dailyShip=Number(stats.shipments_today)||0;
                monthlyShip=Number(stats.shipments_this_month)||0;
                yearlyShip=Number(stats.shipments_this_year)||0;
                document.getElementById('kpi-daily').innerText=dailyShip.toLocaleString('id-ID')+' Paket';
                document.getElementById('kpi-monthly').innerText=monthlyShip.toLocaleString('id-ID')+' Paket';
                document.getElementById('kpi-yearly').innerText=yearlyShip.toLocaleString('id-ID')+' Paket';
                document.getElementById('kpi-month-label').innerHTML=`<i class="bi bi-calendar-month" style="color:#059669"></i> PAKET BULAN INI (${new Date().toLocaleDateString('id-ID',{month:'short'}).toUpperCase()})`;
                document.getElementById('kpi-year-label').innerHTML=`<i class="bi bi-calendar3" style="color:#6366f1"></i> PAKET TAHUN INI (${new Date().getFullYear()})`;
                document.getElementById('kpi-daily-detail').innerText='Jumlah shipment tersimpan hari ini';
                document.getElementById('kpi-monthly-detail').innerText='Jumlah shipment tersimpan bulan ini';
                document.getElementById('kpi-yearly-detail').innerText='Jumlah shipment tersimpan tahun berjalan';
                document.getElementById('kpi-revenue').innerText='Rp '+(Number(stats.revenue_this_month)||0).toLocaleString('id-ID');
                document.getElementById('kpi-revenue-detail').innerText='Total biaya shipment bulan ini';
                totalShip=Number(stats.total_shipments)||0;
                const statuses=stats.shipments_by_status||{};
                inTransit=(Number(statuses.PICKED_UP)||0)+(Number(statuses.IN_TRANSIT)||0);
                delivered=Number(statuses.DELIVERED)||0;
                delayedCount=Number(stats.delayed_shipments)||0;
                exceptionCount=Number(stats.open_exceptions)||0;
                refreshStatsAndChart();
            }catch(loadError){
                error.innerText=loadError.message||'Gagal memuat rekap shipment.';
                error.style.display='block';
            }
        }
        function weatherDescription(code){
            const value=Number(code);
            if(value===0)return 'Cerah';
            if([1,2,3].includes(value))return 'Berawan';
            if([45,48].includes(value))return 'Berkabut';
            if(value>=51&&value<=57)return 'Gerimis';
            if(value>=61&&value<=67||value>=80&&value<=82)return 'Hujan';
            if(value>=71&&value<=77||value===85||value===86)return 'Salju';
            if(value>=95)return 'Badai petir';
            return 'Kondisi berubah-ubah';
        }
        async function loadCountryWeather(){
            const query=document.getElementById('weather-location-query').value.trim();
            const error=document.getElementById('weather-load-error'),resultBox=document.getElementById('weather-result');
            error.style.display='none';
            if(!query){
                error.innerText='Masukkan nama negara atau kota terlebih dahulu.';
                error.style.display='block';
                return;
            }
            resultBox.innerText='Mencari lokasi dan memuat cuaca...';
            try{
                const coord=await resolveCoord(query);
                if(!coord)throw new Error('Lokasi tidak ditemukan. Coba nama negara atau kota yang lebih lengkap.');
                const params=new URLSearchParams({lat:String(coord[0]),lon:String(coord[1])});
                const response=await fetch(apiBaseUrl+'/weather?'+params.toString(),{
                    headers:{'Accept':'application/json','Authorization':'Bearer '+currentAuthToken}
                });
                const weather=await response.json();
                if(!response.ok)throw new Error(weather.message||'Data cuaca tidak dapat dimuat.');
                const temperature=Number(weather.temperature_c);
                const observed=weather.observed_at?new Date(weather.observed_at).toLocaleString('id-ID'):'—';
                resultBox.style.color='#0f172a';
                resultBox.innerHTML=`<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap"><div><b style="font-size:15px">${escapeHtml(query)}</b><p style="font-size:12px;color:#64748b;margin-top:4px">${escapeHtml(weather.source||'Open-Meteo')} • ${escapeHtml(observed)}${weather.stale?' • data cache':''}</p></div><div style="text-align:right"><b style="font-size:24px;color:#0891b2">${Number.isFinite(temperature)?temperature.toFixed(1)+'°C':'—'}</b><p style="font-size:12px;color:#475569">${weatherDescription(weather.weather_code)}</p></div></div><div style="display:flex;gap:18px;flex-wrap:wrap;margin-top:12px;font-size:12px;color:#475569"><span>Angin: ${weather.wind_kmh===null?'—':escapeHtml(weather.wind_kmh)+' km/jam'}</span><span>Curah hujan: ${weather.precipitation_mm===null?'—':escapeHtml(weather.precipitation_mm)+' mm'}</span></div>`;
            }catch(weatherError){
                resultBox.innerText='Cuaca belum tersedia.';
                error.innerText=weatherError.message||'Data cuaca tidak dapat dimuat.';
                error.style.display='block';
            }
        }
        async function loadWeatherCountries(){
            if(weatherCountriesLoaded||weatherCountriesLoading)return;
            weatherCountriesLoading=true;
            const status=document.getElementById('weather-country-list-status');
            status.innerText='Memuat daftar negara...';
            try{
                const response=await fetch(apiBaseUrl+'/countries',{headers:{'Accept':'application/json','Authorization':'Bearer '+currentAuthToken}});
                const data=await response.json();
                if(!response.ok)throw new Error(data.message||'Daftar negara tidak dapat dimuat.');
                const countries=Array.isArray(data.countries)?data.countries:[];
                document.getElementById('weather-country-options').innerHTML=countries.map(country=>`<option value="${escapeHtml(country)}">`).join('');
                weatherCountriesLoaded=countries.length>0;
                status.innerText=weatherCountriesLoaded?`${countries.length} nama negara/alias tersedia. Kota juga dapat dicari.`:'Daftar negara kosong; kota tetap dapat dicari.';
            }catch(loadError){
                status.innerText='Daftar negara tidak dapat dimuat; nama negara atau kota tetap bisa diketik manual.';
                console.warn(loadError);
            }finally{
                weatherCountriesLoading=false;
            }
        }
        function renderDelayWatch(){
            const box=document.getElementById('delay-watch-list');
            if(!box)return;
            const delayed=shipmentsData.filter(s=>s.status==='DELAYED');
            box.innerHTML=delayed.length?delayed.map(s=>`
                <div class="ct-delay-card">
                    <div><strong><i class="bi bi-exclamation-diamond-fill"></i> ${s.resi} — Aktif terlambat</strong>
                    <p>${s.incident||'Kendala operasional'} • ${s.orig} → ${s.dest}</p></div>
                    <button type="button" onclick="switchTab('shipments');renderTables()" style="background:#fff;color:#9f1239;border:1px solid #fecaca;padding:7px 10px;border-radius:7px;font-size:11px;font-weight:bold;cursor:pointer">Lihat & tindak lanjuti</button>
                </div>`).join(''):'';
        }
        function exportCSV(){
            const list=currentRole==='Customer'?shipmentsData.filter(x=>x.owner==='customer'):shipmentsData;
            const headers=['No Resi','Pengirim','Penerima','Alamat','Barang','Mode','Carrier','Asal','Tujuan','ETA','Biaya','Status','POD'];
            const rows=list.map(s=>[s.resi,s.sender,s.receiver,s.address,s.item,s.mode,s.carrier,s.orig,s.dest,s.eta,s.cost,s.status,s.pod].map(v=>`"${String(v).replace(/"/g,'""')}"`).join(','));
            const blob=new Blob(['\uFEFF'+headers.join(',')+'\n'+rows.join('\n')],{type:'text/csv;charset=utf-8;'});
            const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download='Laporan_CargoTrace_2026.csv';document.body.appendChild(a);a.click();document.body.removeChild(a);
        }
        function idrPerUnit(code){
            if(!fxRates)return null;
            const foreign=Number(fxRates[code]);
            if(!Number.isFinite(foreign)||foreign<=0)return null;
            return 1/foreign;
        }
        function renderCurrencyRates(){
            const body=document.getElementById('currency-rates-body');
            if(!fxRates)return;
            const search=(document.getElementById('currency-search')?.value||'').trim().toLowerCase();
            const searchAliases={'jepang':'japan','tiongkok':'china','amerika serikat':'united states','inggris':'united kingdom','korea selatan':'south korea','belanda':'netherlands','jerman':'germany','prancis':'france','perancis':'france','singapura':'singapore','selandia baru':'new zealand','afrika selatan':'south africa','arab saudi':'saudi arabia','brasil':'brazil','meksiko':'mexico','kanada':'canada','swiss':'switzerland','filipina':'philippines'};
            const normalizedSearch=searchAliases[search]||search;
            const select=document.getElementById('curr-target');
            const previous=select.value;
            const available=Object.keys(fxRates).filter(code=>idrPerUnit(code)!==null).sort((a,b)=>{
                const countryA=(fxCurrencies[a]?.countries||[])[0]||a,countryB=(fxCurrencies[b]?.countries||[])[0]||b;
                return countryA.localeCompare(countryB);
            });
            const featured=document.getElementById('currency-summary');
            const featuredCountries={USD:'Amerika Serikat',MYR:'Malaysia',JPY:'Japan',EUR:'Zona Euro'};
            featured.innerHTML=['USD','MYR','JPY','EUR'].filter(code=>available.includes(code)).map(code=>{
                const info=fxCurrencies[code]||{},rate=idrPerUnit(code),country=featuredCountries[code];
                return `<button type="button" class="currency-highlight" onclick="selectCurrency('${code}')"><span class="currency-highlight-top"><span class="currency-code">${code}</span><span class="currency-highlight-country">${country}</span></span><div class="currency-highlight-rate">Rp ${rate.toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2})}</div><div class="currency-highlight-caption">Nilai 1 ${code}</div></button>`;
            }).join('');
            select.innerHTML=available.filter(code=>code!=='IDR').map(code=>{
                const info=fxCurrencies[code]||{};
                return `<option value="${code}">${code} — ${info.name||code}${info.symbol?' ('+info.symbol+')':''}</option>`;
            }).join('');
            select.value=available.includes(previous)?previous:'USD';
            const filtered=available.filter(code=>{
                const info=fxCurrencies[code]||{};
                const searchable=`${code} ${info.name||''} ${(info.countries||[]).join(' ')} ${(info.aliases||[]).join(' ')}`.toLowerCase();
                return searchable.includes(search)||searchable.includes(normalizedSearch);
            });
            const pageCount=Math.max(1,Math.ceil(filtered.length/fxPageSize));
            fxPage=Math.min(fxPage,pageCount);
            const pageItems=filtered.slice((fxPage-1)*fxPageSize,fxPage*fxPageSize);
            body.innerHTML=pageItems.length?pageItems.map(code=>{
                const info=fxCurrencies[code]||{};
                const countries=info.countries||[];
                const countryNames=countries.length>2
                    ?`${countries.length} negara / wilayah`
                    :countries.join(', ')||'Wilayah mata uang khusus';
                const rate=idrPerUnit(code);
                return `<tr><td><span class="currency-code">${code}</span></td><td>${info.name||code}</td><td class="currency-country">${countryNames}</td><td style="font-weight:bold;color:#047857">Rp ${rate.toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2})}</td></tr>`;
            }).join(''):'<tr><td colspan="4" style="color:#64748b;text-align:center;padding:18px">Negara atau mata uang tidak ditemukan pada daftar kurs.</td></tr>';
            const start=filtered.length?(fxPage-1)*fxPageSize+1:0,end=Math.min(fxPage*fxPageSize,filtered.length);
            document.getElementById('currency-result-count').innerText=`${filtered.length} mata uang`;
            document.getElementById('currency-page-info').innerText=filtered.length?`Menampilkan ${start}–${end} dari ${filtered.length} • halaman ${fxPage}/${pageCount}`:'Tidak ada hasil';
            document.getElementById('currency-prev').disabled=fxPage<=1;
            document.getElementById('currency-next').disabled=fxPage>=pageCount;
            convertCurrency();
        }
        function searchCurrencyRates(){fxPage=1;renderCurrencyRates();}
        function changeCurrencyPage(step){fxPage+=step;renderCurrencyRates();}
        function selectCurrency(code){
            document.getElementById('curr-target').value=code;
            convertCurrency();
        }
        function convertCurrency(){
            const input=document.getElementById('curr-idr-input'),result=document.getElementById('curr-result');
            const amount=Number(input.value),target=document.getElementById('curr-target').value;
            const rate=idrPerUnit(target),info=fxCurrencies[target]||{};
            if(!fxRates||!Number.isFinite(amount)||amount<0||!rate){
                result.value=fxRates?'Masukkan nominal IDR yang valid':'Kurs terbaru belum tersedia';
                return;
            }
            result.value=`${info.symbol||target} ${(amount/rate).toLocaleString('id-ID',{minimumFractionDigits:2,maximumFractionDigits:2})} ${target}`;
        }
        async function loadCurrencyRates(forceRefresh=false){
            if(fxRatesLoading)return;
            fxRatesLoading=true;
            const status=document.getElementById('currency-updated'),button=document.getElementById('currency-refresh');
            button.disabled=true;
            button.innerHTML='<i class="bi bi-arrow-clockwise"></i> Memuat...';
            if(!fxRates)status.innerText='Mengambil kurs terbaru...';
            try{
                const response=await fetch(apiBaseUrl+'/rates'+(forceRefresh?'?refresh=1':''),{
                    headers:{'Accept':'application/json','Authorization':'Bearer '+currentAuthToken},
                    cache:'no-store'
                });
                if(!response.ok)throw new Error(`HTTP ${response.status}`);
                const data=await response.json();
                if(!data.rates||!Number.isFinite(Number(data.rates.IDR))||!Number.isFinite(Number(data.rates.MYR))||!Number.isFinite(Number(data.rates.JPY))||!data.currencies){
                    throw new Error('Data kurs tidak lengkap.');
                }
                fxRates=data.rates;
                fxCurrencies=data.currencies;
                fxRateDate=data.date||null;
                renderCurrencyRates();
                const updatedAt=data.updated_at?new Date(data.updated_at).toLocaleString('id-ID',{day:'numeric',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit',timeZone:'Asia/Jakarta'}):fxRateDate||'waktu tidak tersedia';
                status.innerText=`${Object.keys(fxRates).length} mata uang • update ${updatedAt} WIB${data.stale?' • data tersimpan':''}`;
            }catch(error){
                console.error('Gagal mengambil kurs mata uang.',error);
                status.innerText=fxRates
                    ?`Gagal memperbarui. Masih memakai kurs ${fxRateDate||'terakhir tersimpan'}.`
                    :'Kurs tidak tersedia. Periksa koneksi lalu coba perbarui kembali.';
                if(!fxRates)document.getElementById('currency-rates-body').innerHTML='<tr><td colspan="4" style="color:#b91c1c;text-align:center;padding:18px">Data kurs belum dapat dimuat. Coba tombol Perbarui Kurs lagi.</td></tr>';
            }finally{
                fxRatesLoading=false;
                button.disabled=false;
                button.innerHTML='<i class="bi bi-arrow-clockwise"></i> Perbarui Kurs';
            }
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
                    const escapeHtml=value=>String(value||'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
                    const safeUrl=value=>{
                        try{
                            const url=new URL(String(value||''),window.location.href);
                            return url.protocol==='http:'||url.protocol==='https:'?url.href:'';
                        }catch(error){return '';}
                    };
                    box.innerHTML=d.items.slice(0,5).map(item=>{
                        const description=String(item.description||'');
                        const embeddedImage=description.match(/<img[^>]+src=["']([^"']+)["']/i);
                        const imageUrl=safeUrl(item.thumbnail||(item.enclosure&&(item.enclosure.thumbnail||item.enclosure.link))||(embeddedImage&&embeddedImage[1]));
                        const articleUrl=safeUrl(item.link)||'https://www.bbc.com/news/world';
                        const title=String(item.title||'Berita dunia terbaru');
                        const summary=description.replace(/<[^>]*>/g,' ').replace(/\s+/g,' ').trim();
                        const image=imageUrl?`<img class="news-photo" src="${escapeHtml(imageUrl)}" alt="" loading="lazy">`:'';
                        return `<a href="${escapeHtml(articleUrl)}" target="_blank" rel="noopener noreferrer" class="nl news-card"><div class="news-image-wrap"><div class="news-image-fallback"><i class="bi bi-globe-americas"></i><span>Global News</span></div>${image}</div><div class="news-content"><div class="news-meta"><span class="news-source">BBC WORLD • LIVE</span><span class="news-date">${escapeHtml(item.pubDate||'')}</span></div><h4 class="news-title">${escapeHtml(title)}</h4><p class="news-summary">${escapeHtml(summary.slice(0,160))}${summary.length>160?'…':''}</p><span class="news-read">Baca artikel <i class="bi bi-box-arrow-up-right"></i></span></div></a>`;
                    }).join('');
                    box.querySelectorAll('.news-photo').forEach(image=>{
                        image.addEventListener('error',()=>image.remove(),{once:true});
                    });
                }else throw new Error();
            }catch(e){
                badge.innerText='DIRECT GLOBAL NEWS PORTAL';
                box.innerHTML='<a href="https://www.bbc.com/news/world" target="_blank" rel="noopener noreferrer" class="nl news-card"><div class="news-image-wrap"><div class="news-image-fallback"><i class="bi bi-globe-americas"></i><span>Global News</span></div></div><div class="news-content"><span class="news-source">BBC WORLD</span><h4 class="news-title">Breaking Global Headlines</h4><span class="news-read">Buka portal berita <i class="bi bi-box-arrow-up-right"></i></span></div></a>';
            }
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
                map=drawMap('map',mapMarkers,focusedResi);
                if(lastCreatedResi){focusShipmentRoute(map,mapMarkers,lastCreatedResi);lastCreatedResi=null;}
                loadNews();applyRoleVisibility();
                renderDelayWatch();
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
            if(t==='tracking')setTimeout(()=>{if(tMap)tMap.remove();tMap=drawMap('map-tracking',tMapMarkers,null);if(lastCreatedResi){focusShipmentRoute(tMap,tMapMarkers,lastCreatedResi);lastCreatedResi=null;}},50);
            if(t==='world-news')loadNews();
            if(t==='weather')loadWeatherCountries();
            if(t==='users'&&currentRole==='Administrator'&&currentAuthToken)loadUsers();
            if(t==='exceptions'&&currentAuthToken)loadExceptions();
            if(t==='tracking'&&currentAuthToken)loadTrackingTimeline();
            if(t==='notifications'&&currentAuthToken)loadNotifications();
            if(t==='audit-logs'&&currentRole==='Administrator'&&currentAuthToken)loadAuditLogs();
            if(t==='currency'){
                if(!fxRates)loadCurrencyRates();
                if(!fxRefreshTimer)fxRefreshTimer=setInterval(()=>{
                    if(document.visibilityState==='visible'&&document.getElementById('tab-currency').classList.contains('active'))loadCurrencyRates();
                },60000);
            }else if(fxRefreshTimer){
                clearInterval(fxRefreshTimer);fxRefreshTimer=null;
            }
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
        function showCreateAccount(){
            document.getElementById('login-form').style.display='none';
            document.getElementById('create-account-form').style.display='block';
            document.getElementById('login-err').style.display='none';
        }
        function showLogin(){
            document.getElementById('create-account-form').style.display='none';
            document.getElementById('login-form').style.display='block';
            document.getElementById('login-err').style.display='none';
        }
        function ensureAddShipmentButton(role){
            const actions=document.querySelector('#tab-shipments .c > div:first-child > div:last-child');
            let btn=document.getElementById('btn-add-shipment');

            if(role==='Customer'){
                if(btn)btn.remove();
                document.body.dataset.role='Customer';
                return;
            }

            document.body.dataset.role=role;
            if(!btn&&actions){
                btn=document.createElement('button');
                btn.type='button';
                btn.id='btn-add-shipment';
                btn.style.cssText='background:#059669;color:#fff;border:none;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:bold;cursor:pointer';
                btn.innerHTML='<i class="bi bi-plus-lg"></i> + Tambah Kiriman Barang';
                actions.appendChild(btn);
            }
            if(btn){
                btn.hidden=false;
                btn.style.setProperty('display','inline-block','important');
            }
        }
        function handleLogout(){
            document.getElementById('logout-form').submit();
        }
        async function handleLogin(){
            const em=document.getElementById('login-email').value.trim(),pw=document.getElementById('login-pass').value,err=document.getElementById('login-err');
            if(!em||!pw){err.innerText='Email dan password wajib diisi.';err.style.display='block';return;}
            try{
                const response=await fetch(apiBaseUrl+'/login',{
                    method:'POST',
                    headers:{'Content-Type':'application/json','Accept':'application/json'},
                    body:JSON.stringify({email:em,password:pw})
                });
                if(response.ok){
                    const data=await response.json(),accountRole=data.user&&data.user.role;
                    const role=accountRole==='admin'||accountRole==='Administrator'?'Administrator':(accountRole==='operator'||accountRole==='Logistics Operator'?'Logistics Operator':'Customer');
                    completeLogin(role,data.token);
                    return;
                }
                if(response.status===401){
                    err.innerText='Email atau password salah.';
                }else{
                    const data=await response.json();
                    err.innerText=data.message||'Gagal masuk. Silakan coba lagi.';
                }
                err.style.display='block';
            }catch(error){
                err.innerText='Tidak dapat terhubung ke server. Silakan coba lagi.';
                err.style.display='block';
                return;
            }
        }
        async function handleRegister(){
            const name=document.getElementById('register-name').value.trim(),email=document.getElementById('register-email').value.trim(),password=document.getElementById('register-pass').value,confirmation=document.getElementById('register-pass-confirm').value,err=document.getElementById('login-err'),button=document.getElementById('register-submit');
            if(!name||!email||!password||!confirmation){err.innerText='Semua kolom wajib diisi.';err.style.display='block';return;}
            if(password.length<8){err.innerText='Password minimal 8 karakter.';err.style.display='block';return;}
            if(password!==confirmation){err.innerText='Konfirmasi password tidak cocok.';err.style.display='block';return;}
            button.disabled=true;
            button.innerText='Membuat akun...';
            try{
                const response=await fetch(apiBaseUrl+'/register',{
                    method:'POST',
                    headers:{'Content-Type':'application/json','Accept':'application/json'},
                    body:JSON.stringify({name,email,password,password_confirmation:confirmation})
                });
                const data=await response.json();
                if(!response.ok){
                    const validationError=data.errors&&Object.values(data.errors).flat()[0];
                    err.innerText=validationError||data.message||'Gagal membuat akun. Silakan coba lagi.';
                    err.style.display='block';
                    return;
                }
                err.style.display='none';
                completeLogin('Customer',data.token);
            }catch(error){
                err.innerText='Tidak dapat terhubung ke server. Silakan coba lagi.';
                err.style.display='block';
            }finally{
                button.disabled=false;
                button.innerText='Buat Akun';
            }
        }
        function completeLogin(r,token=null){
            document.getElementById('login-err').style.display='none';currentRole=r;currentAuthToken=token;
            const allowed=roleMenus[r]||allTabs;
            allTabs.forEach(x=>{const b=document.getElementById('btn-'+x);if(b)b.style.display=allowed.includes(x)?'flex':'none';});
            const reportButton=document.getElementById('btn-report-incident');
            if(reportButton)reportButton.style.display=r==='Customer'?'none':'inline-block';
            const hb=document.getElementById('user-name'),hs=document.getElementById('user-role'),bn=document.getElementById('role-banner');
            const btnAdd=document.getElementById('btn-add-shipment'),adminBox=document.getElementById('admin-kpi-box'),custBox=document.getElementById('customer-kpi-box'),qtBox=document.getElementById('customer-quick-track');
            const intStats=document.getElementById('internal-stats-box'),anaCard=document.getElementById('analytics-card'),wCard=document.getElementById('weather-card');
            if(r==='Administrator'){
                ensureAddShipmentButton(r);
                hb.innerText=authenticatedUser.name;hs.innerText='ADMINISTRATOR';hs.className='b bg';
                bn.className='bg';bn.innerHTML='🔑 Mode Akses: <b>Administrator</b> — Rekapitulasi volume paket Harian, Bulanan, & Tahunan.';
                adminBox.style.display='grid';custBox.style.display='none';qtBox.style.display='none';
                intStats.style.display='grid';anaCard.style.display='block';wCard.style.gridColumn='auto';
                document.getElementById('map-dash-title').innerText='Live Global Tracking Map (Sea Vessel & Air Cargo)';
                document.getElementById('ship-table-title').innerText='Master Data Shipments (Sea & Air Cargo)';
            }else if(r==='Logistics Operator'){
                ensureAddShipmentButton(r);
                hb.innerText=authenticatedUser.name;hs.innerText='LOGISTICS OPERATOR';hs.className='b bc';
                bn.className='bc';bn.innerHTML='🚚 Mode Akses: <b>Logistics Operator</b> — Verifikasi (ACC), Lapor Kendala, & Bukti Serah (POD).';
                adminBox.style.display='none';custBox.style.display='none';qtBox.style.display='block';
                intStats.style.display='grid';anaCard.style.display='block';wCard.style.gridColumn='auto';
                document.getElementById('qt-hint').innerText='Masukkan No. Resi pelanggan mana pun untuk melacak (Contoh: CT-2026-001)';
                document.getElementById('map-dash-title').innerText='Live Global Tracking Map (Sea Vessel & Air Cargo)';
                document.getElementById('ship-table-title').innerText='Master Data Shipments (Semua Pelanggan)';
            }else{
                ensureAddShipmentButton(r);
                hb.innerText=authenticatedUser.name;hs.innerText='CUSTOMER';hs.className='b ba';
                bn.className='ba';bn.innerHTML='📦 Mode Akses: <b>Customer</b> — Menampilkan data & pelacakan khusus paket milik Anda.';
                adminBox.style.display='none';custBox.style.display='grid';qtBox.style.display='block';
                intStats.style.display='none';anaCard.style.display='none';wCard.style.gridColumn='1 / -1';
                document.getElementById('qt-hint').innerText='Lacak paket milik Anda saja (Contoh: CT-2026-003 atau resi baru Anda)';
                document.getElementById('map-dash-title').innerText='Peta Pelacakan Paket Saya';
                document.getElementById('ship-table-title').innerText='Daftar Pengiriman Paket Saya';
            }
            resetQuickTrack();applyRoleVisibility();
            document.getElementById('login-overlay').style.display='none';
            switchTab('dashboard');
        }
        document.addEventListener('DOMContentLoaded',()=>{
            if(currentRole==='Customer'){
                shipmentsData.splice(0,shipmentsData.length);
                document.getElementById('timeline-list').innerHTML='';
                document.getElementById('notification-list').innerHTML='';
            }
            completeLogin(currentRole,currentAuthToken);
            loadShipmentsFromApi();
            if(currentRole!=='Customer')loadDashboardStats();
            setInterval(()=>{
                if(document.visibilityState!=='visible')return;
                loadShipmentsFromApi();
                if(currentRole!=='Customer')loadDashboardStats();
                if(document.getElementById('tab-exceptions').classList.contains('active'))loadExceptions();
                if(document.getElementById('tab-tracking').classList.contains('active'))loadTrackingTimeline();
                if(document.getElementById('tab-notifications').classList.contains('active'))loadNotifications();
                if(currentRole==='Administrator'&&document.getElementById('tab-audit-logs').classList.contains('active'))loadAuditLogs();
            },15000);
        });
        async function loadShipmentsFromApi(){
            const error=document.getElementById('shipments-load-error');
            error.style.display='none';
            try{
                const records=[];
                let page=1,lastPage=1;
                do{
                    const response=await fetch(apiBaseUrl+'/shipments?page='+page+'&per_page=100',{
                        headers:{'Accept':'application/json','Authorization':'Bearer '+currentAuthToken}
                    });
                    const result=await response.json();
                    if(!response.ok)throw new Error(result.message||'Gagal memuat shipment.');
                    if(!Array.isArray(result.data))throw new Error('Format data shipment tidak valid.');
                    records.push(...result.data);
                    lastPage=Number(result.last_page)||1;
                    page++;
                }while(page<=lastPage);
                const statusLabels={CREATED:'MENUNGGU ACC',BOOKED:'MENUNGGU ACC',PICKED_UP:'IN TRANSIT',IN_TRANSIT:'IN TRANSIT',DELIVERED:'SAMPAI'};
                const progressByStatus={CREATED:0,BOOKED:10,PICKED_UP:25,IN_TRANSIT:60,DELIVERED:100};
                const formatDate=value=>{
                    if(!value)return '—';
                    const parsed=new Date(value);
                    return Number.isNaN(parsed.valueOf())?'—':parsed.toLocaleString('id-ID');
                };
                const mapped=records.map(shipment=>{
                    const status=shipment.is_delayed?'DELAYED':(statusLabels[shipment.status]||shipment.status);
                    const origin=shipment.origin||{},destination=shipment.destination||{};
                    const latest=shipment.latest_tracking_event||{};
                    const latestLocation=latest.location||{};
                    const numericCoord=value=>value===null||value===undefined||value===''?null:Number(value);
                    const currentLat=numericCoord(latest.latitude??latestLocation.latitude);
                    const currentLon=numericCoord(latest.longitude??latestLocation.longitude);
                    const items=Array.isArray(shipment.items)?shipment.items:[];
                    const weight=items.reduce((sum,item)=>sum+(Number(item.weight_kg)||0),0);
                    const cost=Number(shipment.cost);
                    return {
                        id:shipment.id,
                        owner:Number(shipment.customer_id)===Number(authenticatedUser.id)?'customer':'corporate',
                        resi:shipment.shipment_number,
                        sender:[shipment.sender_name||shipment.customer&&shipment.customer.name||'Customer',shipment.sender_phone].filter(Boolean).join(' '),
                        receiver:[shipment.receiver_name||destination.name||'—',shipment.receiver_phone].filter(Boolean).join(' '),
                        address:shipment.destination_address||destination.name||'—',
                        item:shipment.cargo_type+(weight>0?' ('+weight.toLocaleString('id-ID')+' Kg)':''),
                        mode:String(shipment.transport_mode||'SEA').toUpperCase()==='AIR'?'Air':'Sea',
                        carrier:shipment.carrier&&shipment.carrier.name||'—',
                        orig:origin.name||'—',
                        dest:destination.name||'—',
                        eta:formatDate(shipment.estimated_arrival||shipment.expected_arrival),
                        cost:Number.isFinite(cost)&&cost>0?'Rp '+cost.toLocaleString('id-ID'):'—',
                        origCoord:[Number(origin.latitude)||0,Number(origin.longitude)||0],
                        destCoord:[Number(destination.latitude)||0,Number(destination.longitude)||0],
                        currentCoord:Number.isFinite(currentLat)&&Number.isFinite(currentLon)?[currentLat,currentLon]:null,
                        currentLocation:latestLocation.name||'',
                        status,
                        progress:progressByStatus[shipment.status]||0,
                        incident:shipment.is_delayed?'Pengiriman mengalami keterlambatan':'',
                        delayHours:0,
                        originalEta:shipment.is_delayed?formatDate(shipment.expected_arrival):'',
                        pod:shipment.status==='DELIVERED'?'Sudah diserahterimakan':'Belum diserahterimakan'
                    };
                });
                ['ct-id-update','ct-id-tracking'].forEach(selectId=>{
                    const select=document.getElementById(selectId);
                    if(!select)return;
                    const selected=select.value;
                    select.innerHTML='<option value="">Pilih shipment</option>'+mapped.filter(shipment=>shipment.id).map(shipment=>`<option value="${Number(shipment.id)}">${escapeHtml(shipment.resi)}</option>`).join('');
                    if(mapped.some(shipment=>String(shipment.id)===selected))select.value=selected;
                });
                let changed=false;
                if(mapped.length||currentRole==='Customer'){
                    changed=JSON.stringify(shipmentsData)!==JSON.stringify(mapped);
                    if(changed)shipmentsData.splice(0,shipmentsData.length,...mapped);
                }
                if(changed){
                    applyRoleVisibility();
                    if(document.getElementById('tab-dashboard').classList.contains('active'))setTimeout(initDash,50);
                    if(document.getElementById('tab-tracking').classList.contains('active')){
                        setTimeout(()=>{if(tMap)tMap.remove();tMap=drawMap('map-tracking',tMapMarkers,null);},50);
                    }
                }
            }catch(loadError){
                error.innerText=loadError.message||'Gagal memuat shipment.';
                error.style.display='block';
            }
        }
        async function openShipmentStatus(shipmentId){
            const modal=document.getElementById('shipment-status-modal');
            const error=document.getElementById('quick-status-error');
            const button=document.getElementById('quick-status-save');
            const labels={CREATED:'Baru dibuat',BOOKED:'Menunggu pengiriman',PICKED_UP:'Sudah dijemput',IN_TRANSIT:'Dalam perjalanan',DELIVERED:'Sudah sampai'};
            const nextStatus={CREATED:'BOOKED',BOOKED:'PICKED_UP',PICKED_UP:'IN_TRANSIT',IN_TRANSIT:'DELIVERED'};
            quickStatusShipmentId=shipmentId;
            quickStatusNext=null;
            error.style.display='none';
            button.disabled=true;
            button.innerText='Memuat...';
            modal.style.display='flex';
            try{
                const response=await fetch(apiBaseUrl+'/shipments/'+shipmentId,{
                    headers:{'Accept':'application/json','Authorization':'Bearer '+currentAuthToken}
                });
                const shipment=await response.json();
                if(!response.ok)throw new Error(shipment.message||'Detail kiriman tidak dapat dimuat.');
                quickStatusNext=nextStatus[shipment.status]||null;
                document.getElementById('quick-status-resi').innerText='No. resi: '+shipment.shipment_number;
                document.getElementById('quick-status-current').innerText=labels[shipment.status]||shipment.status;
                document.getElementById('quick-status-next').innerText=quickStatusNext?(labels[quickStatusNext]||quickStatusNext):'Tidak ada (sudah selesai)';
                button.disabled=!quickStatusNext;
                button.innerText=quickStatusNext?'Lanjutkan':'Selesai';
            }catch(loadError){
                error.innerText=loadError.message||'Detail kiriman tidak dapat dimuat.';
                error.style.display='block';
                button.disabled=true;
                button.innerText='Lanjutkan';
            }
        }
        async function saveNextShipmentStatus(){
            if(!quickStatusShipmentId||!quickStatusNext)return;
            const button=document.getElementById('quick-status-save');
            const error=document.getElementById('quick-status-error');
            button.disabled=true;
            button.innerText='Menyimpan...';
            error.style.display='none';
            try{
                const response=await fetch(apiBaseUrl+'/shipments/'+quickStatusShipmentId+'/status',{
                    method:'POST',
                    headers:{'Accept':'application/json','Content-Type':'application/json','Authorization':'Bearer '+currentAuthToken},
                    body:JSON.stringify({status:quickStatusNext})
                });
                const result=await response.json();
                if(!response.ok)throw new Error(result.message||'Status kiriman gagal diperbarui.');
                document.getElementById('shipment-status-modal').style.display='none';
                await loadShipmentsFromApi();
                await loadTrackingTimeline();
                if(currentRole!=='Customer')await loadDashboardStats();
            }catch(saveError){
                error.innerText=saveError.message||'Status kiriman gagal diperbarui.';
                error.style.display='block';
                button.disabled=false;
                button.innerText='Lanjutkan';
            }
        }
        async function loadUsers(){
            const body=document.getElementById('users-table-body'),error=document.getElementById('users-load-error');
            error.style.display='none';
            body.innerHTML='<tr><td colspan="6" style="text-align:center;color:#64748b">Memuat data user dan exception...</td></tr>';
            try{
                const response=await fetch(apiBaseUrl+'/users',{
                    headers:{'Accept':'application/json','Authorization':'Bearer '+currentAuthToken}
                });
                const users=await response.json();
                if(!response.ok)throw new Error(users.message||'Gagal memuat data user.');
                if(!Array.isArray(users))throw new Error('Format data user tidak valid.');
                const typeLabels={
                    CUSTOMS_HOLD:'Penahanan Bea Cukai',
                    WEATHER_DELAY:'Keterlambatan Cuaca',
                    PORT_CONGESTION:'Kepadatan Pelabuhan',
                    DAMAGED:'Barang Rusak',
                    MISROUTED:'Salah Rute',
                    DOCUMENTATION_ISSUE:'Masalah Dokumen'
                };
                const statusLabels={OPEN:'Terbuka',INVESTIGATING:'Ditangani',RESOLVED:'Selesai'};
                const severityClasses={LOW:'bg',MEDIUM:'ba',HIGH:'br'};
                body.innerHTML=users.map(user=>{
                    const exceptions=Array.isArray(user.exceptions)?user.exceptions:[];
                    const exceptionHtml=exceptions.length?exceptions.map(exception=>{
                        const shipment=escapeHtml(exception.shipment_number);
                        const type=escapeHtml(typeLabels[exception.type]||exception.type);
                        const status=escapeHtml(statusLabels[exception.status]||exception.status);
                        const severity=escapeHtml(exception.severity);
                        const severityClass=severityClasses[exception.severity]||'bc';
                        return `<div style="margin-bottom:5px"><b>${shipment}</b> — ${type}<br><span class="b ${severityClass}">${severity}</span> <span class="b bc">${status}</span></div>`;
                    }).join(''):'Tidak ada exception terkait.';
                    const roleName=String(user.role||'').toUpperCase();
                    const role=roleName==='ADMINISTRATOR'?'ADMIN':(roleName==='LOGISTICS OPERATOR'?'OPERATOR':roleName);
                    const roleClass=role==='ADMIN'?'bg':(role==='OPERATOR'?'bc':'ba');
                    const roleValue=role==='ADMIN'?'admin':(role==='OPERATOR'?'operator':'customer');
                    const roleOptions=['admin','operator','customer'].map(value=>`<option value="${value}"${value===roleValue?' selected':''}>${value}</option>`).join('');
                    const isSelf=Number(user.id)===Number(authenticatedUser.id);
                    return `<tr><td>${escapeHtml(user.id)}</td><td><b>${escapeHtml(user.name)}</b></td><td>${escapeHtml(user.email)}</td><td><span class="b ${roleClass}">${escapeHtml(role)}</span></td><td>${exceptionHtml}</td><td><select id="user-role-${Number(user.id)}" ${isSelf?'disabled':''} style="padding:5px;border:1px solid #cbd5e1;border-radius:6px">${roleOptions}</select> <button type="button" onclick="updateUserRole(${Number(user.id)})" ${isSelf?'disabled':''} style="padding:5px 8px;border:0;border-radius:6px;background:#0891b2;color:#fff">Simpan</button></td></tr>`;
                }).join('');
            }catch(errorLoading){
                body.innerHTML='<tr><td colspan="6" style="text-align:center;color:#64748b">Data user tidak dapat dimuat.</td></tr>';
                error.innerText=errorLoading.message||'Gagal memuat exception user.';
                error.style.display='block';
            }
        }
        async function updateUserRole(userId){
            const role=document.getElementById('user-role-'+userId).value,error=document.getElementById('users-load-error');
            error.style.display='none';
            try{
                const response=await fetch(apiBaseUrl+'/users/'+userId+'/role',{
                    method:'PATCH',
                    headers:{'Accept':'application/json','Content-Type':'application/json','Authorization':'Bearer '+currentAuthToken},
                    body:JSON.stringify({role})
                });
                const result=await response.json();
                if(!response.ok)throw new Error(result.message||'Role user gagal diperbarui.');
                await loadUsers();
            }catch(updateError){
                error.innerText=updateError.message||'Role user gagal diperbarui.';
                error.style.display='block';
            }
        }
        async function loadExceptions(){
            const body=document.getElementById('exceptions-table-body'),error=document.getElementById('exceptions-load-error');
            error.style.display='none';
            if(!body.querySelector('[data-demo-row="true"]'))body.innerHTML='<tr><td colspan="7" style="text-align:center;color:#64748b">Memuat exception...</td></tr>';
            try{
                const response=await fetch(apiBaseUrl+'/exceptions',{headers:{'Accept':'application/json','Authorization':'Bearer '+currentAuthToken}});
                const result=await response.json();
                if(!response.ok)throw new Error(result.message||'Gagal memuat exception.');
                const exceptions=Array.isArray(result.data)?result.data:[];
                const typeLabels={
                    CUSTOMS_HOLD:'Penahanan Bea Cukai',
                    WEATHER_DELAY:'Keterlambatan Cuaca',
                    PORT_CONGESTION:'Kepadatan Pelabuhan',
                    DAMAGED:'Barang Rusak',
                    MISROUTED:'Salah Rute',
                    DOCUMENTATION_ISSUE:'Masalah Dokumen'
                };
                const statusLabels={OPEN:'Terbuka',INVESTIGATING:'Ditangani',RESOLVED:'Selesai'};
                const severityClasses={LOW:'bg',MEDIUM:'ba',HIGH:'br'};
                if(exceptions.length||currentRole==='Customer'){
                    body.innerHTML=exceptions.length?exceptions.map(exception=>{
                        const nextStatus=exception.status==='OPEN'?'INVESTIGATING':(exception.status==='INVESTIGATING'?'RESOLVED':null);
                        const action=nextStatus&&currentRole!=='Customer'?`<button type="button" onclick="updateExceptionStatus(${Number(exception.id)},'${nextStatus}')" style="background:#0891b2;color:#fff;border:0;border-radius:5px;padding:5px 8px">${nextStatus==='RESOLVED'?'Selesaikan':'Tindak lanjuti'}</button>`:'—';
                        const detectedAt=exception.detected_at?new Date(exception.detected_at).toLocaleString('id-ID'):'—';
                        return `<tr><td><b>#${escapeHtml(exception.id)}</b></td><td><b>${escapeHtml(exception.shipment&&exception.shipment.shipment_number||'—')}</b></td><td>${escapeHtml(typeLabels[exception.type]||exception.type)}</td><td><span class="b ${severityClasses[exception.severity]||'bc'}">${escapeHtml(exception.severity)}</span></td><td><span class="b ${exception.status==='RESOLVED'?'bg':'ba'}">${escapeHtml(statusLabels[exception.status]||exception.status)}</span></td><td>${escapeHtml(detectedAt)}</td><td>${action}</td></tr>`;
                    }).join(''):'<tr><td colspan="7" style="text-align:center;color:#64748b">Tidak ada exception untuk akun ini.</td></tr>';
                }
            }catch(loadError){
                if(!body.querySelector('[data-demo-row="true"]'))body.innerHTML='<tr><td colspan="7" style="text-align:center;color:#64748b">Exception tidak dapat dimuat.</td></tr>';
                error.innerText=loadError.message||'Gagal memuat exception.';
                error.style.display='block';
            }
        }
        async function updateExceptionStatus(exceptionId,status){
            const error=document.getElementById('exceptions-load-error');
            error.style.display='none';
            try{
                const response=await fetch(apiBaseUrl+'/exceptions/'+exceptionId,{
                    method:'PATCH',
                    headers:{'Accept':'application/json','Content-Type':'application/json','Authorization':'Bearer '+currentAuthToken},
                    body:JSON.stringify({status})
                });
                const result=await response.json();
                if(!response.ok)throw new Error(result.message||'Status exception gagal diperbarui.');
                await loadExceptions();
                await loadDashboardStats();
            }catch(updateError){
                error.innerText=updateError.message||'Status exception gagal diperbarui.';
                error.style.display='block';
            }
        }
        async function loadTrackingTimeline(){
            const list=document.getElementById('timeline-list'),error=document.getElementById('tracking-load-error');
            error.style.display='none';
            if(!list.querySelector('.tl-item'))list.innerHTML='<p style="font-size:12px;color:#64748b">Memuat riwayat tracking...</p>';
            try{
                const events=[];
                let page=1,lastPage=1;
                do{
                    const response=await fetch(apiBaseUrl+'/tracking?page='+page+'&per_page=100',{
                        headers:{'Accept':'application/json','Authorization':'Bearer '+currentAuthToken}
                    });
                    const result=await response.json();
                    if(!response.ok)throw new Error(result.message||'Gagal memuat riwayat tracking.');
                    if(!Array.isArray(result.data))throw new Error('Format data tracking tidak valid.');
                    events.push(...result.data);
                    lastPage=Number(result.last_page)||1;
                    page++;
                }while(page<=lastPage);
                if(events.length||currentRole==='Customer'){
                    list.innerHTML=events.length?events.map(event=>{
                        const color=event.is_exception?'#e11d48':'#059669';
                        const when=event.occurred_at?new Date(event.occurred_at).toLocaleString('id-ID'):'—';
                        const shipment=event.shipment&&event.shipment.shipment_number||'Shipment';
                        const location=event.location&&event.location.name||'Lokasi tidak dicatat';
                        const owner=currentRole==='Customer'?'customer':'corporate';
                        return `<div class="tl-item" data-owner="${owner}"><span class="tl-dot" style="background:${color};box-shadow:0 0 0 2px ${color}"></span><div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b"><b>${escapeHtml(shipment)} • ${escapeHtml(location)}</b><span>${escapeHtml(when)}</span></div><p style="font-size:13px;margin-top:3px"><b>${escapeHtml(event.event_type)}</b>: ${escapeHtml(event.description||'Tidak ada deskripsi.')}</p></div>`;
                    }).join(''):'<p style="font-size:12px;color:#64748b">Belum ada riwayat tracking untuk shipment Anda.</p>';
                }
            }catch(loadError){
                if(!list.querySelector('.tl-item'))list.innerHTML='';
                error.innerText=loadError.message||'Gagal memuat riwayat tracking.';
                error.style.display='block';
            }
        }
        async function loadNotifications(){
            const list=document.getElementById('notification-list'),error=document.getElementById('notification-load-error');
            error.style.display='none';
            if(!list.querySelector('[data-demo="true"]'))list.innerHTML='<p style="font-size:12px;color:#64748b">Memuat notifikasi...</p>';
            try{
                const response=await fetch(apiBaseUrl+'/notifications',{headers:{'Accept':'application/json','Authorization':'Bearer '+currentAuthToken}});
                const result=await response.json();
                if(!response.ok)throw new Error(result.message||'Gagal memuat notifikasi.');
                const notifications=result.notifications&&result.notifications.data||[];
                document.getElementById('notif-badge').innerText=Number(result.unread_count)||0;
                if(notifications.length||currentRole==='Customer'){
                    list.innerHTML=notifications.length?notifications.map(notification=>{
                        const shipment=notification.shipment&&notification.shipment.shipment_number;
                        const time=notification.created_at?new Date(notification.created_at).toLocaleString('id-ID'):'';
                        return `<div class="${notification.read_at?'bc':'bg'}" data-owner="customer" style="padding:12px;border-radius:8px;font-size:13px"><b>${shipment?escapeHtml(shipment)+': ':''}</b>${escapeHtml(notification.message)}<p style="font-size:10px;color:#64748b;margin-top:5px">${escapeHtml(time)}</p></div>`;
                    }).join(''):'<p style="font-size:12px;color:#64748b">Belum ada notifikasi untuk akun ini.</p>';
                }
                applyRoleVisibility();
            }catch(loadError){
                if(!list.querySelector('[data-demo="true"]'))list.innerHTML='';
                error.innerText=loadError.message||'Gagal memuat notifikasi.';
                error.style.display='block';
            }
        }
        async function loadAuditLogs(){
            const body=document.getElementById('audit-table-body'),error=document.getElementById('audit-load-error');
            error.style.display='none';
            body.innerHTML='<tr><td colspan="5" style="text-align:center;color:#64748b">Memuat audit log...</td></tr>';
            try{
                const response=await fetch(apiBaseUrl+'/audit-logs',{headers:{'Accept':'application/json','Authorization':'Bearer '+currentAuthToken}});
                const result=await response.json();
                if(!response.ok)throw new Error(result.message||'Gagal memuat audit log.');
                const logs=Array.isArray(result.data)?result.data:[];
                body.innerHTML=logs.length?logs.map(log=>{
                    const changes=log.changes?JSON.stringify(log.changes):'—';
                    const time=log.created_at?new Date(log.created_at).toLocaleString('id-ID'):'—';
                    return `<tr><td>${escapeHtml(time)}</td><td>${escapeHtml(log.user&&log.user.name||'—')}</td><td>${escapeHtml(log.action)}</td><td>${escapeHtml(log.entity_type)} #${escapeHtml(log.entity_id)}</td><td>${escapeHtml(changes)}</td></tr>`;
                }).join(''):'<tr><td colspan="5" style="text-align:center;color:#64748b">Belum ada audit log.</td></tr>';
            }catch(loadError){
                body.innerHTML='<tr><td colspan="5" style="text-align:center;color:#64748b">Audit log tidak dapat dimuat.</td></tr>';
                error.innerText=loadError.message||'Gagal memuat audit log.';
                error.style.display='block';
            }
        }
        function openIncidentModal(preResi=null){
            const sel=document.getElementById('inc-resi-select'),error=document.getElementById('incident-error');
            error.style.display='none';
            const activeShipments=shipmentsData.filter(shipment=>shipment.status!=='SAMPAI'&&shipment.status!=='DITOLAK');
            sel.innerHTML=activeShipments.length?activeShipments.map(shipment=>{
                const value=shipment.id?'db:'+Number(shipment.id):'demo:'+escapeHtml(shipment.resi);
                return `<option value="${value}">${escapeHtml(shipment.resi)} — ${escapeHtml(shipment.item)} (${escapeHtml(shipment.orig)} → ${escapeHtml(shipment.dest)})</option>`;
            }).join(''):'<option value="">-- Tidak ada kargo aktif --</option>';
            if(preResi){
                const match=activeShipments.find(shipment=>shipment.resi===preResi);
                if(match)sel.value=match.id?'db:'+Number(match.id):'demo:'+match.resi;
            }
            document.getElementById('inc-location').value='';
            document.getElementById('incident-modal').style.display='flex';
        }
        async function saveNewIncident(){
            const selection=document.getElementById('inc-resi-select').value,type=document.getElementById('inc-type').value,severity=document.getElementById('inc-severity').value;
            const location=cleanStr(document.getElementById('inc-location').value),action=cleanStr(document.getElementById('inc-action').value)||'Rerouting dan penjadwalan ulang';
            const error=document.getElementById('incident-error');
            error.style.display='none';
            if(!selection||!location){error.innerText='Pilih shipment dan isi lokasi kejadian.';error.style.display='block';return;}
            const isDatabaseShipment=selection.indexOf('db:')===0;
            const shipment=isDatabaseShipment
                ?shipmentsData.find(item=>Number(item.id)===Number(selection.slice(3)))
                :shipmentsData.find(item=>item.resi===selection.slice(5));
            if(!shipment){error.innerText='Shipment yang dipilih tidak ditemukan.';error.style.display='block';return;}
            if(isDatabaseShipment){
                try{
                    const response=await fetch(apiBaseUrl+'/shipments/'+Number(shipment.id)+'/exceptions',{
                        method:'POST',
                        headers:{'Accept':'application/json','Content-Type':'application/json','Authorization':'Bearer '+currentAuthToken},
                        body:JSON.stringify({
                            type,
                            severity,
                            notes:`${action} di ${location}`,
                            delay_hours:Number(document.getElementById('inc-delay-hours').value)
                        })
                    });
                    const result=await response.json();
                    if(!response.ok)throw new Error(result.message||'Laporan kendala gagal disimpan.');
                    document.getElementById('incident-modal').style.display='none';
                    await loadShipmentsFromApi();
                    await loadExceptions();
                    await loadDashboardStats();
                    alert('Laporan kendala berhasil dicatat untuk shipment '+result.shipment.shipment_number+'.');
                }catch(saveError){
                    error.innerText=saveError.message||'Laporan kendala gagal disimpan.';
                    error.style.display='block';
                }
                return;
            }
            const resi=shipment.resi,owner=shipment.owner,delayHours=Number(document.getElementById('inc-delay-hours').value)||0;
            const incId='INC-'+Math.floor(8803+Math.random()*900);
            shipment.originalEta=shipment.originalEta||shipment.eta;
            shipment.status='DELAYED';
            shipment.incident=`${type} di ${location}`;
            shipment.delayHours=delayHours;
            shipment.eta=`${shipment.originalEta} (+${delayHours} jam)`;
            if(focusedResi===resi)updateQuickTrackUI(shipment);
            const severityClass=severity==='HIGH'?'br':(severity==='MEDIUM'?'ba':'bc');
            const detectedAt=new Date().toLocaleString('id-ID');
            const row=document.createElement('tr');
            row.dataset.demoRow='true';
            row.innerHTML=`<td><b>${incId}</b></td><td><b>${escapeHtml(resi)}</b></td><td>${escapeHtml(type)}</td><td><span class="b ${severityClass}">${escapeHtml(severity)}</span></td><td><span class="b ba">Terbuka</span></td><td>${escapeHtml(detectedAt)}</td><td id="inc-act-${incId}"><button type="button" onclick="resolveIncident('${incId}','${escapeHtml(resi)}')" style="background:#059669;color:#fff;border:none;padding:3px 7px;border-radius:4px;font-size:10px;cursor:pointer">Selesaikan</button></td>`;
            document.getElementById('exceptions-table-body').prepend(row);
            delayedCount++;exceptionCount++;refreshStatsAndChart();renderTables();renderDelayWatch();
            addTimeline(resi,'Kendala Dilaporkan ('+incId+')',`${type} di ${location}. Tindakan: ${action}.`,'#e11d48',owner);
            pushNotif('⚠️ Kendala Pengiriman '+resi,`${type} di ${location}. Penanganan: ${action}.`,'br',owner);
            document.getElementById('incident-modal').style.display='none';
            alert('Laporan kendala '+incId+' untuk resi '+resi+' berhasil dicatat!');
        }
        function resolveIncident(incId,resi){
            const actCell=document.getElementById('inc-act-'+incId);if(actCell)actCell.innerHTML='<span class="b bg">✓ RESOLVED / SELESAI</span>';
            const s=shipmentsData.find(x=>x.resi===resi),owner=s?s.owner:'customer';
            if(s&&s.status==='DELAYED'){
                s.status='IN TRANSIT';s.incident='';s.delayHours=0;s.eta=s.originalEta||s.eta.replace(/\s*\(.+\)$/,'');
                s.originalEta='';
                renderTables();
                if(focusedResi===resi)updateQuickTrackUI(s);
            }
            if(delayedCount>0)delayedCount--;
            refreshStatsAndChart();
            addTimeline(resi,'Kendala Teratasi ('+incId+')','Hambatan selesai ditangani, kargo kembali normal.','#0891b2',owner);
            pushNotif('✅ Kendala Teratasi '+resi,'Pengiriman kembali normal menuju tujuan.','bc',owner);
        }
        function accShipment(resi){
            const s=shipmentsData.find(x=>x.resi===resi);
            if(!s||s.status!=='MENUNGGU ACC')return;
            if(currentRole!=='Administrator'&&currentRole!=='Logistics Operator'){
                alert('Hanya Administrator atau Logistics Operator yang dapat melakukan ACC.');
                return;
            }
            const owner=s.owner||'customer';
            s.status='IN TRANSIT';s.approvedBy=currentRole;
            if(s.progress<15)s.progress=15;
            inTransit++;
            refreshStatsAndChart();
            renderTables();
            addTimeline(resi,'Di-ACC & Berangkat','Dokumen disetujui oleh '+currentRole+', kargo bergerak menuju '+s.receiver+'.','#0891b2',owner);
            pushNotif('✅ Pesanan Disetujui '+resi,'Pengiriman telah di-ACC oleh '+currentRole+' dan sedang dalam perjalanan.','bc',owner);
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
    <script>
document.addEventListener('DOMContentLoaded', () => {
  const applyRoleVisibility = () => {
    const bodyText = document.body.innerText || '';
    const isOperator = /LOGISTICS\s*OPERATOR/i.test(bodyText);
    const isAdmin = /ADMINISTRATOR/i.test(bodyText);
    const isCustomer = /CUSTOMER/i.test(bodyText) && !isOperator && !isAdmin;

    const addBtn = Array.from(document.querySelectorAll('button, a')).find(el =>
      /tambah\s*kiriman\s*barang/i.test((el.textContent || '').trim())
    );

    if (addBtn) {
      if (isOperator || isAdmin) {
        addBtn.hidden = false;
        addBtn.style.setProperty('display', 'inline-block', 'important');
      } else {
        addBtn.remove();
      }
    }
  };

  applyRoleVisibility();
  const obs = new MutationObserver(applyRoleVisibility);
  obs.observe(document.body, { childList: true, subtree: true, characterData: true });
});
</script>
<script>
document.addEventListener('click', function (e) {
  const btn = e.target.closest('button, a');
  if (!btn) return;
  const txt = (btn.innerText || btn.textContent || '').trim();
  if (!/tambah\s*kiriman\s*barang/i.test(txt)) return;

  if (typeof currentRole !== 'undefined' && currentRole === 'Customer') {
    e.preventDefault();
    e.stopPropagation();
    return;
  }

  e.preventDefault();
  e.stopPropagation();

  let modal = document.getElementById('ct-full-add-shipment-modal');
  if (!modal) {
    modal = document.createElement('div');
    modal.id = 'ct-full-add-shipment-modal';
    modal.style.cssText = 'position:fixed;inset:0;background:rgba(15,23,42,.55);z-index:99999;display:flex;align-items:center;justify-content:center;padding:14px;overflow-y:auto;';

    const selectMarkup = (name, id, label) => {
      const source = document.querySelector(`#ct-shipment-select-options select[name="${name}"]`);
      if (!source) return `<p style="grid-column:span 2;color:#b91c1c;font-size:12px;">Data ${label.toLowerCase()} belum tersedia.</p>`;
      const select = source.cloneNode(true);
      select.id = id;
      select.name = name;
      select.required = true;
      select.style.cssText = 'width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:12px;background:#fff;';
      return `<div style="grid-column:span 2;"><label style="display:block;font-size:11px;color:#475569;margin-bottom:2px;">${label}</label>${select.outerHTML}</div>`;
    };
    const textInput = (name, placeholder, label) => `
      <div>
        <label style="display:block;font-size:11px;color:#475569;margin-bottom:2px;">${label}</label>
        <input type="text" name="${name}" required maxlength="150" placeholder="${placeholder}" style="width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:12px;">
      </div>`;
    const customerSelect = selectMarkup('customer_id', 'ct-modal-customer', 'Akun customer pemilik shipment');
    const carrierSelect = selectMarkup('carrier_id', 'ct-modal-carrier', 'Carrier');

    modal.innerHTML = `
      <div style="background:#fff;border-radius:12px;padding:18px;max-width:560px;width:100%;max-height:90vh;overflow-y:auto;box-shadow:0 20px 40px rgba(0,0,0,.25);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
          <strong style="font-size:15px;color:#0f172a;">Tambah Kiriman Barang</strong>
          <button type="button" id="ct-close-full-add" style="border:none;background:#f1f5f9;color:#0f172a;border-radius:6px;padding:4px 10px;cursor:pointer;font-weight:700;">✕</button>
        </div>
        <form id="ct-full-add-form" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
          <div style="grid-column:span 2;padding:8px;background:#f8fafc;border-radius:6px;font-size:12px;color:#475569;">Cara singkat: pilih customer dan moda, isi pengirim/penerima, kota asal/tujuan, lalu barang dan berat. Estimasi tiba, tarif, dan nomor resi dibuat otomatis.</div>
          ${customerSelect}
          <div>
            <label style="display:block;font-size:11px;color:#475569;margin-bottom:2px;">Moda Pengiriman</label>
            <select name="transport_mode" id="ct-mode-select" required style="width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:12px;background:#fff;">
              <option value="SEA">Kapal (Sea Freight)</option>
              <option value="AIR">Pesawat (Air Cargo)</option>
            </select>
          </div>
          ${carrierSelect}

          <input type="text" name="sender_name" required maxlength="100" placeholder="Nama pengirim" style="border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:12px;">
          <input type="text" name="receiver_name" required maxlength="100" placeholder="Nama penerima" style="border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:12px;">
          ${textInput('origin_name', 'Contoh: Jakarta', 'Kota asal')}
          <div>
            <label style="display:block;font-size:11px;color:#475569;margin-bottom:2px;">Negara asal</label>
            <input type="text" name="origin_country" list="ct-country-options" required maxlength="80" value="Indonesia" placeholder="Pilih atau ketik negara" style="width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:12px;">
          </div>
          ${textInput('destination_name', 'Contoh: Tokyo', 'Kota tujuan')}
          <div>
            <label style="display:block;font-size:11px;color:#475569;margin-bottom:2px;">Negara tujuan</label>
            <input type="text" name="destination_country" list="ct-country-options" required maxlength="80" placeholder="Pilih atau ketik negara" style="width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:12px;">
          </div>
          <div id="ct-modal-country-status" role="status" style="grid-column:span 2;font-size:11px;color:#64748b;">Pilih negara dari daftar atau ketik namanya. Kota bisa diketik manual.</div>
          <input type="text" name="cargo_type" required maxlength="60" placeholder="Barang (contoh: Paket Skincare)" style="border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:12px;">

          <div style="display:flex;align-items:center;gap:6px;border:1px solid #cbd5e1;border-radius:6px;padding:4px 8px;background:#fff;">
            <input type="number" step="0.1" min="0.1" name="weight_kg" id="ct-weight-input" value="1" required placeholder="Berat" style="width:100%;border:none;outline:none;font-size:12px;">
            <strong style="font-size:12px;color:#0f172a;">Kg</strong>
          </div>

          <div style="grid-column:span 2;">
            <label style="display:block;font-size:11px;color:#475569;margin-bottom:2px;">Harga / Biaya IDR (Otomatis per Kg &amp; Moda, bisa diubah)</label>
            <input type="number" name="price" id="ct-price-input" required value="25000" style="width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:6px;padding:7px 10px;font-size:12px;">
          </div>

          <button type="submit" style="grid-column:span 2;margin-top:4px;background:#15803d;color:#fff;border:none;border-radius:6px;padding:9px;font-size:12px;font-weight:700;cursor:pointer;">Simpan &amp; Buat Resi</button>
          <div id="ct-full-add-msg" style="grid-column:span 2;font-size:12px;font-weight:600;"></div>
        </form>
      </div>
    `;
    document.body.appendChild(modal);
    const countryCount = document.querySelectorAll('#ct-country-options option').length;
    if (countryCount) {
      modal.querySelector('#ct-modal-country-status').textContent = countryCount + ' negara tersedia. Kota bisa diketik manual.';
    }

    const weightEl = modal.querySelector('#ct-weight-input');
    const modeEl = modal.querySelector('#ct-mode-select');
    const priceEl = modal.querySelector('#ct-price-input');

    const recalc = () => {
      const kg = Math.max(0.1, parseFloat(weightEl.value || '1') || 1);
      const tarif = /pesawat|air/i.test(modeEl.value || '') ? 45000 : 25000;
      priceEl.value = Math.round(kg * tarif);
    };

    weightEl.addEventListener('input', recalc);
    modeEl.addEventListener('change', recalc);

    modal.querySelector('#ct-close-full-add').addEventListener('click', () => {
      modal.style.display = 'none';
    });

    modal.querySelector('#ct-full-add-form').addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const form = ev.currentTarget;
      const d = Object.fromEntries(new FormData(form).entries());
      const msgEl = modal.querySelector('#ct-full-add-msg');
      const kg = parseFloat(d.weight_kg || '1') || 1;
      const isAir = d.transport_mode === 'AIR';
      const departure = new Date();
      const arrival = new Date(departure.getTime() + (isAir ? 2 : 14) * 24 * 60 * 60 * 1000);
      const submitButton = form.querySelector('button[type="submit"]');
      submitButton.disabled = true;
      msgEl.style.color = '#475569';
      msgEl.textContent = 'Mencari kota lalu menyimpan shipment...';
      try {
        const mode = isAir ? 'Air' : 'Sea';
        const origin = await resolveCoord(d.origin_name + ', ' + d.origin_country, mode);
        if (!origin) throw new Error('Kota asal tidak ditemukan. Periksa nama kota dan negara.');
        await new Promise(resolve => setTimeout(resolve, 1100));
        const destination = await resolveCoord(d.destination_name + ', ' + d.destination_country, mode);
        if (!destination) throw new Error('Kota tujuan tidak ditemukan. Periksa nama kota dan negara.');
        const body = {
          customer_id: Number(d.customer_id),
          carrier_id: Number(d.carrier_id),
          origin_name: d.origin_name.trim(),
          origin_country: d.origin_country.trim(),
          origin_latitude: origin[0],
          origin_longitude: origin[1],
          destination_name: d.destination_name.trim(),
          destination_country: d.destination_country.trim(),
          destination_latitude: destination[0],
          destination_longitude: destination[1],
          cargo_type: d.cargo_type.trim(),
          transport_mode: d.transport_mode,
          sender_name: d.sender_name.trim(),
          receiver_name: d.receiver_name.trim(),
          origin_address: d.origin_name.trim() + ', ' + d.origin_country.trim(),
          destination_address: d.destination_name.trim() + ', ' + d.destination_country.trim(),
          cost: Number(d.price || 0),
          expected_departure: departure.toISOString(),
          expected_arrival: arrival.toISOString(),
          items: [{
            description: d.cargo_type.trim(),
            quantity: 1,
            weight_kg: kg
          }]
        };
        const response = await fetch(apiBaseUrl + '/shipments', {
          method: 'POST',
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + currentAuthToken
          },
          body: JSON.stringify(body)
        });
        const result = await response.json();
        if (!response.ok) {
          const validationError = result.errors ? Object.values(result.errors).flat()[0] : null;
          throw new Error(validationError || result.message || 'Shipment gagal disimpan.');
        }
        lastCreatedResi = result.shipment_number;
        await loadShipmentsFromApi();
        await loadDashboardStats();
        await loadTrackingTimeline();
        msgEl.style.color = '#059669';
        msgEl.textContent = `Resi ${result.shipment_number} tersimpan dan sudah ditampilkan untuk customer yang dipilih.`;
        form.reset();
        recalc();
      } catch (saveError) {
        msgEl.style.color = '#dc2626';
        msgEl.textContent = saveError.message || 'Shipment gagal disimpan.';
      } finally {
        submitButton.disabled = false;
      }
    });
  } else {
    modal.style.display = 'flex';
  }
}, true);
</script>
<script>
(function () {
  var KENDARAAN_3 = {
    'Bus': { ikon: '🚌', warna: '#d97706' },
    'Kapal': { ikon: '🚢', warna: '#0891b2' },
    'Pesawat': { ikon: '✈', warna: '#059669' }
  };

  var modeTerpilih = 'Bus';

  function ambilPetaLeaflet() {
    if (window.ctMainMap && typeof window.ctMainMap.addLayer === 'function') {
      return window.ctMainMap;
    }
    var el = document.querySelector('.leaflet-container');
    return (el && el._leaflet_map) ? el._leaflet_map : null;
  }

  function buatPinKendaraan(latlng, jenis) {
    var map = ambilPetaLeaflet();
    if (!map || typeof L === 'undefined') return;

    var cfg = KENDARAAN_3[jenis] || KENDARAAN_3['Bus'];
    var htmlPin =
      '<div style="text-align:center;">' +
        '<div style="width:34px;height:34px;border-radius:50%;background:' + cfg.warna + ';color:#fff;display:flex;align-items:center;justify-content:center;font-size:16px;border:2px solid #fff;box-shadow:0 3px 8px rgba(0,0,0,.35);">' + cfg.ikon + '</div>' +
        '<span style="background:#0f172a;color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:4px;">' + jenis + '</span>' +
      '</div>';

    L.marker([latlng.lat, latlng.lng], {
      icon: L.divIcon({
        className: 'ct-pin-3mode',
        html: htmlPin,
        iconSize: [48, 48],
        iconAnchor: [24, 24]
      })
    }).addTo(map).bindPopup('<strong>' + jenis + '</strong><br>' + latlng.lat.toFixed(4) + ', ' + latlng.lng.toFixed(4)).openPopup();
  }

  function pasangKontrol3Kendaraan() {
    var wadah = document.querySelector('.leaflet-container');
    if (!wadah || document.getElementById('ct-3mode-tracking-bar')) return;

    var bar = document.createElement('div');
    bar.id = 'ct-3mode-tracking-bar';
    bar.style.cssText = 'position:absolute;top:58px;left:50%;transform:translateX(-50%);z-index:1000;background:#ffffff;border:1px solid #cbd5e1;border-radius:999px;padding:4px 8px;box-shadow:0 4px 12px rgba(15,23,42,.16);display:flex;gap:6px;align-items:center;font-size:11px;font-weight:700;';

    ['Bus', 'Kapal', 'Pesawat'].forEach(function (nama) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = KENDARAAN_3[nama].ikon + ' ' + nama;
      btn.dataset.mode = nama;
      var aktif = nama === modeTerpilih;
      btn.style.cssText = 'border:none;border-radius:999px;padding:5px 10px;cursor:pointer;font-size:11px;font-weight:700;background:' + (aktif ? KENDARAAN_3[nama].warna : '#f1f5f9') + ';color:' + (aktif ? '#fff' : '#0f172a') + ';';

      btn.addEventListener('click', function (ev) {
        ev.stopPropagation();
        modeTerpilih = nama;
        bar.querySelectorAll('button').forEach(function (b) {
          var m = b.dataset.mode;
          var isSel = m === modeTerpilih;
          b.style.background = isSel ? KENDARAAN_3[m].warna : '#f1f5f9';
          b.style.color = isSel ? '#fff' : '#0f172a';
        });

        var map = ambilPetaLeaflet();
        if (map && typeof map.getCenter === 'function') {
          buatPinKendaraan(map.getCenter(), modeTerpilih);
        }
      });

      bar.appendChild(btn);
    });

    wadah.appendChild(bar);

    var map = ambilPetaLeaflet();
    if (map && !map._ct3ModeClickBound) {
      map._ct3ModeClickBound = true;
      map.on('click', function (e) {
        buatPinKendaraan(e.latlng, modeTerpilih);
      });
    }
  }
var TITIK_KOTA = {
    'medan': [3.5952, 98.6722],
    'lhokseumawe': [5.1801, 97.1507],
    'banda aceh': [5.5483, 95.3238],
    'penang': [5.4141, 100.3288],
    'kuala lumpur': [3.1390, 101.6869],
    'singapore': [1.3521, 103.8198],
    'palembang': [-2.9761, 104.7754],
    'jakarta': [-6.2088, 106.8456],
    'surabaya': [-7.2575, 112.7521],
    'tokyo': [35.7720, 140.3929],
    'rotterdam': [51.9244, 4.4777]
  };

  function jarakKm(a, b) {
    var R = 6371;
    var dLat = (b[0] - a[0]) * Math.PI / 180;
    var dLng = (b[1] - a[1]) * Math.PI / 180;
    var x = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(a[0] * Math.PI / 180) * Math.cos(b[0] * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return R * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
  }

  function pilihModeOtomatis(asalCoord, tujuanCoord, preferJauh) {
    var km = jarakKm(asalCoord, tujuanCoord);
    if (km <= 600) {
      return { mode: 'Bus', km: Math.round(km) };
    }
    return { mode: preferJauh || 'Pesawat', km: Math.round(km) };
  }
  function gambarGarisDanIkon(asalCoord, tujuanCoord, mode, labelRute) {
    var map = ambilPetaLeaflet();
    if (!map || typeof L === 'undefined') return;

    var cfg = KENDARAAN_3[mode] || KENDARAAN_3['Bus'];
    var garis = L.polyline([asalCoord, tujuanCoord], {
      color: cfg.warna,
      weight: 3.5,
      dashArray: mode === 'Bus' ? '' : '6,6'
    }).addTo(map);

    var tengah = [
      (asalCoord[0] + tujuanCoord[0]) / 2,
      (asalCoord[1] + tujuanCoord[1]) / 2
    ];

    L.marker(tengah, {
      icon: L.divIcon({
        className: 'ct-rute-pin',
        html: '<div style="background:' + cfg.warna + ';color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;border:2px solid #fff;box-shadow:0 3px 8px rgba(0,0,0,.35);">' + cfg.ikon + '</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 16]
      })
    }).addTo(map).bindPopup('<strong>' + mode + '</strong><br>' + (labelRute || '')).openPopup();

    map.fitBounds(garis.getBounds(), { padding: [30, 30] });
  }

  function hubungkanKlikDenganGaris() {
    var map = ambilPetaLeaflet();
    if (!map || map._ctGarisSiap) return;
    map._ctGarisSiap = true;

    var titikSebelumnya = null;

    map.on('click', function (e) {
      var tujuan = [e.latlng.lat, e.latlng.lng];
      var asal = (modeTerpilih === 'Bus')
        ? TITIK_KOTA['medan']
        : (titikSebelumnya || TITIK_KOTA['medan']);

      var hasil = pilihModeOtomatis(asal, tujuan, modeTerpilih === 'Bus' ? 'Pesawat' : modeTerpilih);
      modeTerpilih = hasil.mode;
      gambarGarisDanIkon(asal, tujuan, hasil.mode, 'Jarak ~' + hasil.km + ' km');
      titikSebelumnya = tujuan;
    });
  }

  setInterval(hubungkanKlikDenganGaris, 800);
  var TITIK_KOTA = {
    'medan': [3.5952, 98.6722],
    'lhokseumawe': [5.1801, 97.1507],
    'banda aceh': [5.5483, 95.3238],
    'penang': [5.4141, 100.3288],
    'kuala lumpur': [3.1390, 101.6869],
    'singapore': [1.3521, 103.8198],
    'palembang': [-2.9761, 104.7754],
    'jakarta': [-6.2088, 106.8456],
    'surabaya': [-7.2575, 112.7521],
    'tokyo': [35.7720, 140.3929],
    'rotterdam': [51.9244, 4.4777]
  };

  function jarakKm(a, b) {
    var R = 6371;
    var dLat = (b[0] - a[0]) * Math.PI / 180;
    var dLng = (b[1] - a[1]) * Math.PI / 180;
    var x = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(a[0] * Math.PI / 180) * Math.cos(b[0] * Math.PI / 180) *
            Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return R * 2 * Math.atan2(Math.sqrt(x), Math.sqrt(1 - x));
  }

  function pilihModeOtomatis(asalCoord, tujuanCoord, preferJauh) {
    var km = jarakKm(asalCoord, tujuanCoord);
    if (km <= 600) {
      return { mode: 'Bus', km: Math.round(km) };
    }
    return { mode: preferJauh || 'Pesawat', km: Math.round(km) };
  }

  function gambarGarisDanIkon(asalCoord, tujuanCoord, mode, labelRute) {
    var map = ambilPetaLeaflet();
    if (!map || typeof L === 'undefined') return;

    var cfg = KENDARAAN_3[mode] || KENDARAAN_3['Bus'];
    var garis = L.polyline([asalCoord, tujuanCoord], {
      color: cfg.warna,
      weight: 3.5,
      dashArray: mode === 'Bus' ? '' : '6,6'
    }).addTo(map);

    var tengah = [
      (asalCoord[0] + tujuanCoord[0]) / 2,
      (asalCoord[1] + tujuanCoord[1]) / 2
    ];

    L.marker(tengah, {
      icon: L.divIcon({
        className: 'ct-rute-pin',
        html: '<div style="background:' + cfg.warna + ';color:#fff;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:15px;border:2px solid #fff;box-shadow:0 3px 8px rgba(0,0,0,.35);">' + cfg.ikon + '</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 16]
      })
    }).addTo(map).bindPopup('<strong>' + mode + '</strong><br>' + (labelRute || '')).openPopup();

    map.fitBounds(garis.getBounds(), { padding: [30, 30] });
  }

  function hubungkanKlikDenganGaris() {
    var map = ambilPetaLeaflet();
    if (!map || map._ctGarisSiap) return;
    map._ctGarisSiap = true;

    var titikSebelumnya = null;

    map.on('click', function (e) {
      var tujuan = [e.latlng.lat, e.latlng.lng];
      var asal = (modeTerpilih === 'Bus')
        ? TITIK_KOTA['medan']
        : (titikSebelumnya || TITIK_KOTA['medan']);

      var hasil = pilihModeOtomatis(asal, tujuan, modeTerpilih === 'Bus' ? 'Pesawat' : modeTerpilih);
      modeTerpilih = hasil.mode;
      gambarGarisDanIkon(asal, tujuan, hasil.mode, 'Jarak ~' + hasil.km + ' km');
      titikSebelumnya = tujuan;
    });
  }

  function pastikanOverlayRute() {
    var petaEl = document.querySelector('.leaflet-container') ||
                 document.querySelector('[class*="map"]') ||
                 document.body;
    if (!petaEl) return null;

    var ov = document.getElementById('ct-overlay-garis-3mode');
    if (!ov) {
      ov = document.createElement('div');
      ov.id = 'ct-overlay-garis-3mode';
      ov.style.cssText = 'position:fixed;inset:0;pointer-events:none;z-index:2147483645;overflow:hidden;';
      document.body.appendChild(ov);
    }
    return ov;
  }
</script>
<script>
(function () {
  var TITIK = {
    'Medan': [3.5952, 98.6722],
    'Banda Aceh': [5.5483, 95.3238],
    'Tebing Tinggi': [3.3285, 99.1625],
    'Pekanbaru': [0.5071, 101.4478],
    'Port Klang (Malaysia)': [2.9999, 101.3920],
    'Penang (Malaysia)': [5.4141, 100.3288],
    'Belawan (Indonesia)': [3.7820, 98.7130],
    'Tanjung Priok (Indonesia)': [-6.1030, 106.8830],
    'KUL Kuala Lumpur': [2.7456, 101.7099],
    'KNO Medan': [3.6422, 98.8853],
    'CGK Jakarta': [-6.1256, 106.6559]
  };

  var RUTE_MODA = {
    'Bus': {
      ikon: '🚌', warna: '#d97706', dash: null, tipe: 'jalan',
      jalur: [
        { dari: 'Medan', ke: 'Banda Aceh', lewat: [] },
        { dari: 'Medan', ke: 'Tebing Tinggi', lewat: [] },
        { dari: 'Medan', ke: 'Pekanbaru', lewat: [] }
      ]
    },
    'Kapal': {
      ikon: '🚢', warna: '#0284c7', dash: '6, 6', tipe: 'laut',
      jalur: [
        { dari: 'Port Klang (Malaysia)', ke: 'Belawan (Indonesia)',
          lewat: [[3.5000, 100.4000], [3.8500, 99.4500]] },
        { dari: 'Penang (Malaysia)', ke: 'Tanjung Priok (Indonesia)',
          lewat: [[4.2000, 99.9000], [2.4000, 101.7000], [1.3000, 103.6000],
                  [0.6000, 104.3000], [-3.8000, 106.4000]] }
      ]
    },
    'Pesawat': {
      ikon: '✈️', warna: '#059669', dash: '8, 6', tipe: 'udara',
      jalur: [
        { dari: 'KUL Kuala Lumpur', ke: 'KNO Medan', lewat: [] },
        { dari: 'KUL Kuala Lumpur', ke: 'CGK Jakarta', lewat: [] }
      ]
    }
  };

  var aktif = 'Kapal';
  var layerGrup = null;
  var tokenGambar = 0;
  var timerAnim = [];
  var cacheJalan = {};

  /* ===== 1. TANGKAP OBJEK PETA LEAFLET ===== */
  (function hookLeaflet() {
    if (typeof L === 'undefined' || !L.Map) return;
    // Untuk peta yang dibuat SETELAH script ini
    L.Map.addInitHook(function () {
      if (!window.ctMainMap) window.ctMainMap = this;
    });
    // Untuk peta yang SUDAH ada: tangkap saat peta bergerak/zoom/memuat tile
    var asli = L.Map.prototype.fire;
    L.Map.prototype.fire = function () {
      if (!window.ctMainMap && this._loaded && this.getContainer) {
        window.ctMainMap = this;
      }
      return asli.apply(this, arguments);
    };
  })();

  function cariLeafletMap() {
    if (window.ctMainMap && typeof window.ctMainMap.addLayer === 'function') {
      return window.ctMainMap;
    }
    // Cadangan: cari variabel global yang berisi peta Leaflet
    for (var k in window) {
      try {
        var o = window[k];
        if (o && typeof o === 'object' && o instanceof L.Map) {
          window.ctMainMap = o;
          return o;
        }
      } catch (e) {}
    }
    return null;
  }

  /* ===== 2. UTILITAS ===== */
  function buatPinBadge(teks, warna, ikon) {
    return L.divIcon({
      className: 'ct-pin-bersih',
      html:
        '<div style="display:inline-flex;align-items:center;gap:4px;background:' + warna + ';color:#fff;' +
        'padding:2px 7px;border-radius:999px;font-size:10px;font-weight:700;white-space:nowrap;' +
        'box-shadow:0 2px 6px rgba(0,0,0,.35);border:1.5px solid #fff;transform:translate(-50%,-50%);">' +
        '<span>' + ikon + '</span><span>' + teks + '</span></div>',
      iconSize: [0, 0],
      iconAnchor: [0, 0]
    });
  }

  function buatIkonBergerak(ikon, warna) {
    return L.divIcon({
      className: 'ct-pin-bersih',
      html:
        '<div style="width:30px;height:30px;border-radius:50%;background:#fff;border:3px solid ' + warna + ';' +
        'display:flex;align-items:center;justify-content:center;font-size:16px;' +
        'box-shadow:0 3px 8px rgba(0,0,0,.4);transform:translate(-50%,-50%);">' + ikon + '</div>',
      iconSize: [0, 0],
      iconAnchor: [0, 0]
    });
  }

  // Garis lengkung untuk pesawat
  function busurUdara(a, b) {
    var n = 60, hasil = [];
    var midLat = (a[0] + b[0]) / 2, midLng = (a[1] + b[1]) / 2;
    var dLat = b[0] - a[0], dLng = b[1] - a[1];
    var lengkung = 0.18; // makin besar makin melengkung
    var cLat = midLat + (-dLng) * lengkung;
    var cLng = midLng + (dLat) * lengkung;
    for (var i = 0; i <= n; i++) {
      var t = i / n, u = 1 - t;
      hasil.push([
        u * u * a[0] + 2 * u * t * cLat + t * t * b[0],
        u * u * a[1] + 2 * u * t * cLng + t * t * b[1]
      ]);
    }
    return hasil;
  }

  // Rute jalan sungguhan (OSRM), fallback garis lurus
  function rutejalan(a, b) {
    var key = a.join(',') + '>' + b.join(',');
    if (cacheJalan[key]) return Promise.resolve(cacheJalan[key]);
    var url = 'https://router.project-osrm.org/route/v1/driving/' +
      a[1] + ',' + a[0] + ';' + b[1] + ',' + b[0] +
      '?overview=full&geometries=geojson';
    return fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (d && d.routes && d.routes[0]) {
          var pts = d.routes[0].geometry.coordinates.map(function (c) { return [c[1], c[0]]; });
          cacheJalan[key] = pts;
          return pts;
        }
        return [a, b];
      })
      .catch(function () { return [a, b]; });
  }

  function bangunJalur(cfg, item) {
    var a = TITIK[item.dari], b = TITIK[item.ke];
    if (cfg.tipe === 'jalan') return rutejalan(a, b);
    if (cfg.tipe === 'udara') return Promise.resolve(busurUdara(a, b));
    return Promise.resolve([a].concat(item.lewat || []).concat([b])); // laut
  }

  function hentikanAnim() {
    timerAnim.forEach(function (t) { clearInterval(t); });
    timerAnim = [];
  }

  function animasikan(marker, pts) {
    if (pts.length < 2) return;
    var langkah = Math.max(1, Math.floor(pts.length / 250));
    var i = 0;
    var t = setInterval(function () {
      i += langkah;
      if (i >= pts.length) i = 0;
      marker.setLatLng(pts[i]);
    }, 80);
    timerAnim.push(t);
  }

  /* ===== 3. GAMBAR RUTE ===== */
  function gambarRuteModa(namaModa) {
    var map = cariLeafletMap();
    if (!map || typeof L === 'undefined') return;

    if (!layerGrup) layerGrup = L.layerGroup().addTo(map);
    layerGrup.clearLayers();
    hentikanAnim();

    var token = ++tokenGambar;
    var cfg = RUTE_MODA[namaModa] || RUTE_MODA['Kapal'];

    var tugas = cfg.jalur.map(function (item) {
      if (!TITIK[item.dari] || !TITIK[item.ke]) return Promise.resolve(null);
      return bangunJalur(cfg, item).then(function (pts) { return { item: item, pts: pts }; });
    });

    Promise.all(tugas).then(function (hasil) {
      if (token !== tokenGambar) return; // sudah ganti moda, abaikan
      var semua = [];

      hasil.forEach(function (h) {
        if (!h) return;
        var item = h.item, pts = h.pts;
        semua = semua.concat(pts);

        L.polyline(pts, {
          color: cfg.warna, weight: 4, opacity: 0.92,
          dashArray: cfg.dash || null, smoothFactor: 1.2
        }).addTo(layerGrup);

        L.marker(TITIK[item.dari], {
          icon: buatPinBadge(item.dari, '#0f172a', cfg.ikon)
        }).addTo(layerGrup);

        L.marker(TITIK[item.ke], {
          icon: buatPinBadge(namaModa + ' → ' + item.ke, cfg.warna, cfg.ikon)
        }).addTo(layerGrup);

        var kendaraan = L.marker(pts[0], {
          icon: buatIkonBergerak(cfg.ikon, cfg.warna),
          zIndexOffset: 1000
        }).addTo(layerGrup);
        animasikan(kendaraan, pts);
      });

      if (semua.length > 0) {
        map.fitBounds(L.latLngBounds(semua), { padding: [28, 28], maxZoom: 7 });
      }
    });
  }

  /* ===== 4. TOMBOL PILIH MODA ===== */
  function warnaiTombol(bar) {
    bar.querySelectorAll('button').forEach(function (btn) {
      var m = btn.dataset.mode, c = RUTE_MODA[m], sel = (m === aktif);
      btn.style.background = sel ? c.warna : '#f1f5f9';
      btn.style.color = sel ? '#fff' : '#0f172a';
    });
  }

  function pasangKontrol() {
    var map = cariLeafletMap();
    if (!map) return;
    var wadah = map.getContainer();
    if (!wadah || wadah.querySelector('#ct-bar-3moda-peta')) return;

    var bar = document.createElement('div');
    bar.id = 'ct-bar-3moda-peta';
    bar.style.cssText =
      'position:absolute;bottom:14px;left:50%;transform:translateX(-50%);z-index:1000;' +
      'background:#ffffff;border:1px solid #cbd5e1;border-radius:999px;' +
      'padding:4px 8px;box-shadow:0 4px 12px rgba(15,23,42,.22);' +
      'display:flex;gap:6px;align-items:center;font-size:11px;font-weight:700;';

    // Cegah klik tombol ikut menggeser/zoom peta
    L.DomEvent.disableClickPropagation(bar);
    L.DomEvent.disableScrollPropagation(bar);

    ['Bus', 'Kapal', 'Pesawat'].forEach(function (m) {
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.dataset.mode = m;
      btn.textContent = RUTE_MODA[m].ikon + ' ' + m;
      btn.style.cssText = 'border:none;border-radius:999px;padding:5px 11px;cursor:pointer;font-size:11px;font-weight:700;';
      btn.onclick = function (ev) {
        ev.preventDefault();
        ev.stopPropagation();
        aktif = m;
        warnaiTombol(bar);
        gambarRuteModa(m);
      };
      bar.appendChild(btn);
    });

    wadah.appendChild(bar);
    warnaiTombol(bar);
    gambarRuteModa(aktif);
  }

  setInterval(pasangKontrol, 600);
})();
</script>
<script>
(function () {
  var KOTA_KOORD = {
    'medan': [3.5952, 98.6722],
    'kualanamu': [3.6422, 98.8853],
    'kno': [3.6422, 98.8853],
    'belawan': [3.7820, 98.7130],
    'jakarta': [-6.2088, 106.8456],
    'cgk': [-6.1256, 106.6559],
    'tanjung priok': [-6.1030, 106.8830],
    'surabaya': [-7.2575, 112.7521],
    'tanjung perak': [-7.2020, 112.7350],
    'kuala lumpur': [3.1390, 101.6869],
    'kul': [2.7456, 101.7099],
    'port klang': [2.9999, 101.3920],
    'penang': [5.4141, 100.3288],
    'singapore': [1.3521, 103.8198],
    'tokyo': [35.7720, 140.3929],
    'nrt': [35.7720, 140.3929]
  };

  function normalisasiMode(val) {
    var s = (val || '').toLowerCase();
    if (s.indexOf('pesawat') !== -1 || s.indexOf('plane') !== -1 || s.indexOf('air') !== -1 || s.indexOf('udara') !== -1) {
      return 'Pesawat';
    }
    if (s.indexOf('kapal') !== -1 || s.indexOf('ship') !== -1 || s.indexOf('sea') !== -1 || s.indexOf('laut') !== -1) {
      return 'Kapal';
    }
    return 'Bus';
  }

  function cariKoord(teks, cadangan) {
    var k = (teks || '').toLowerCase();
    for (var nama in KOTA_KOORD) {
      if (k.indexOf(nama) !== -1) {
        return KOTA_KOORD[nama];
      }
    }
    return cadangan;
  }

  function ambilPeta() {
    if (window.ctMainMap && typeof window.ctMainMap.addLayer === 'function') return window.ctMainMap;
    var el = document.querySelector('.leaflet-container');
    return (el && el._leaflet_map) ? el._leaflet_map : null;
  }

  function tampilkanDiMaps(modeRaw, asalTeks, tujuanTeks) {
    var map = ambilPeta();
    if (!map || typeof L === 'undefined') return;

    var mode = normalisasiMode(modeRaw);
    var warna = mode === 'Pesawat' ? '#059669' : (mode === 'Kapal' ? '#0284c7' : '#d97706');
    var ikon = mode === 'Pesawat' ? '✈' : (mode === 'Kapal' ? '🚢' : '🚌');

    var shipmentMode = mode === 'Kapal' ? 'Sea' : 'Air';
    var pAsal = getCoord(asalTeks, shipmentMode);
    var pTuj = getCoord(tujuanTeks, shipmentMode);
    if (!pAsal || !pTuj) {
      console.warn('Rute peta dilewati karena koordinat asal/tujuan tidak dikenali.', asalTeks, tujuanTeks);
      return;
    }

    var garis = L.polyline(getRouteCoords({
      orig: asalTeks, dest: tujuanTeks, origCoord: pAsal, destCoord: pTuj, mode: shipmentMode
    }), {
      color: warna,
      weight: 3.5,
      dashArray: mode === 'Bus' ? null : '6, 6'
    }).addTo(map);

    L.circleMarker(pTuj, {
      radius: 7,
      color: '#ffffff',
      weight: 2,
      fillColor: warna,
      fillOpacity: 1
    }).addTo(map).bindPopup('<strong>' + ikon + ' ' + mode + '</strong><br>' + (asalTeks || 'Asal') + ' → ' + (tujuanTeks || 'Tujuan')).openPopup();

    map.fitBounds(garis.getBounds(), { padding: [25, 25], maxZoom: 7 });
  }

  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (!form || form.tagName !== 'FORM' || form.id === 'ct-full-add-form') return;

    var modeVal = '';
    var asalVal = '';
    var tujuanVal = '';

    form.querySelectorAll('input, select, textarea').forEach(function (field) {
      var nama = (field.name || field.id || '').toLowerCase();
      var val = (field.value || '').trim();
      if (!val) return;

      if (nama.indexOf('mode') !== -1 || nama.indexOf('type') !== -1 || nama.indexOf('kendaraan') !== -1 || nama.indexOf('transport') !== -1 || field.tagName === 'SELECT') {
        if (!modeVal) modeVal = val;
      }
      if (nama.indexOf('origin') !== -1 || nama.indexOf('asal') !== -1 || nama.indexOf('from') !== -1) {
        asalVal = val;
      }
      if (nama.indexOf('dest') !== -1 || nama.indexOf('tujuan') !== -1 || nama.indexOf('to') !== -1) {
        tujuanVal = val;
      }
    });

    if (modeVal || asalVal || tujuanVal) {
      setTimeout(function () {
        tampilkanDiMaps(modeVal, asalVal, tujuanVal);
      }, 250);
    }
  }, true);
})();
</script>
<script>
(function () {
  var SIMPAN_KEY = 'ct_rute_tersimpan_v1';

  // 1. Jika di halaman ada teks "(CT-...): asal -> tujuan (Jalur ...)", simpan ke localStorage
  function cekDanSimpanDariTeks() {
    var txt = document.body.innerText || '';
    var m = txt.match(/\((CT-[^\)]+)\):\s*([^\n→-]+?)\s*(?:→|->|—)\s*([^\n(]+)\(([^)]+)\)/i);
    if (m) {
      var dataBaru = {
        resi: m[1].trim(),
        asal: m[2].trim(),
        tujuan: m[3].trim(),
        jalur: m[4].trim()
      };
      localStorage.setItem(SIMPAN_KEY, JSON.stringify(dataBaru));
    }
  }

  // 2. Gambar ke kotak peta (baik dari hasil simpan maupun data terakhir)
  function gambarRuteTersimpan() {
    var raw = localStorage.getItem(SIMPAN_KEY);
    // Jika belum sempat ke-save sebelumnya, pakai rute terakhir yang kamu input: Medan -> Kuala Lumpur
    var info = raw ? JSON.parse(raw) : {
      resi: 'CT-2026-9823',
      asal: 'Medan',
      tujuan: 'Kuala Lumpur',
      jalur: 'Jalur Laut'
    };

    var kotak = null;
    document.querySelectorAll('div').forEach(function (el) {
      var r = el.getBoundingClientRect();
      if (!kotak && r.width > 320 && r.height > 160 && r.top > 100 && r.bottom < window.innerHeight - 20) {
        if (el.querySelector('img, canvas, svg, .leaflet-tile-pane')) kotak = el;
      }
    });
    if (!kotak) return;

    if (getComputedStyle(kotak).position === 'static') kotak.style.position = 'relative';
    kotak.style.overflow = 'hidden';

    var lama = kotak.querySelector('#ct-rute-final-box');
    if (lama) lama.remove();

    var w = kotak.clientWidth;
    var h = kotak.clientHeight;
    if (!w || !h) return;

    var laut = /laut|kapal|sea/i.test(info.jalur || '');
    var warna = laut ? '#0284c7' : '#059669';
    var ikon = laut ? '🚢' : '✈';

    // Posisi Medan -> Selat Malaka -> Kuala Lumpur sesuai peta yang tampil di layarmu
    var x1 = w * 0.45, y1 = h * 0.46;
    var xm = w * 0.50, ym = h * 0.48;
    var x2 = w * 0.54, y2 = h * 0.52;

    var ns = 'http://www.w3.org/2000/svg';
    var box = document.createElement('div');
    box.id = 'ct-rute-final-box';
    box.style.cssText = 'position:absolute;inset:0;pointer-events:none;z-index:999;';

    var svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('viewBox', '0 0 ' + w + ' ' + h);
    svg.style.cssText = 'width:100%;height:100%;display:block;';

    var line = document.createElementNS(ns, 'polyline');
    line.setAttribute('points', x1 + ',' + y1 + ' ' + xm + ',' + ym + ' ' + x2 + ',' + y2);
    line.setAttribute('fill', 'none');
    line.setAttribute('stroke', warna);
    line.setAttribute('stroke-width', '4');
    line.setAttribute('stroke-dasharray', laut ? '6,5' : 'none');
    svg.appendChild(line);
    box.appendChild(svg);

    var pin = document.createElement('div');
    pin.style.cssText =
      'position:absolute;left:' + x2 + 'px;top:' + y2 + 'px;transform:translate(10px,-50%);' +
      'background:' + warna + ';color:#fff;padding:3px 9px;border-radius:999px;' +
      'font-size:11px;font-weight:700;white-space:nowrap;box-shadow:0 2px 6px rgba(0,0,0,.35);border:1.5px solid #fff;';
    pin.textContent = ikon + ' ' + info.asal + ' → ' + info.tujuan + ' (' + info.resi + ')';
    box.appendChild(pin);

    kotak.appendChild(box);
  }
<script>
(function () {
  var SIMPAN_KEY = 'ct_rute_tersimpan_v1';
  var KOORDINAT = {
    'medan': [3.5952, 98.6722],
    'belawan': [3.7820, 98.7130],
    'kuala lumpur': [3.1390, 101.6869],
    'port klang': [2.9999, 101.3920],
    'penang': [5.4141, 100.3288],
    'jakarta': [-6.2088, 106.8456],
    'surabaya': [-7.2575, 112.7521],
    'singapore': [1.3521, 103.8198],
    'tokyo': [35.7720, 140.3929],
    'rotterdam': [51.9244, 4.4777]
  };

  function cariKoord(nama, cadangan) {
    var s = (nama || '').toLowerCase();
    for (var k in KOORDINAT) {
      if (s.indexOf(k) !== -1) return KOORDINAT[k];
    }
    return cadangan;
  }

  function cariLeafletAktif() {
    var containers = document.querySelectorAll('.leaflet-container');
    for (var i = 0; i < containers.length; i++) {
      var el = containers[i];
      if (el._leaflet_map && typeof el._leaflet_map.latLngToContainerPoint === 'function') {
        return el._leaflet_map;
      }
    }
    for (var prop in window) {
      try {
        var obj = window[prop];
        if (obj && typeof obj === 'object' && typeof obj.latLngToContainerPoint === 'function' && typeof obj.getContainer === 'function') {
          return obj;
        }
      } catch (e) {}
    }
    return null;
  }

  function sinkronTeksKeStorage() {
    var txt = document.body.innerText || '';
    var m = txt.match(/\((CT-[^\)]+)\):\s*([^\n→-]+?)\s*(?:→|->|—)\s*([^\n(]+)\(([^)]+)\)/i);
    if (m) {
      localStorage.setItem(SIMPAN_KEY, JSON.stringify({
        resi: m[1].trim(),
        asal: m[2].trim(),
        tujuan: m[3].trim(),
        jalur: m[4].trim()
      }));
    }
  }

  function gambarNempelPeta() {
    var map = cariLeafletAktif();
    if (!map) return;

    var container = map.getContainer();
    if (!container) return;

    var raw = localStorage.getItem(SIMPAN_KEY);
    var info = raw ? JSON.parse(raw) : {
      resi: 'CT-2026-9823',
      asal: 'Medan',
      tujuan: 'Kuala Lumpur',
      jalur: 'Jalur Laut'
    };

    var pAsal = cariKoord(info.asal, KOORDINAT['medan']);
    var pTujuan = cariKoord(info.tujuan, KOORDINAT['kuala lumpur']);
    var isLaut = /laut|kapal|sea/i.test(info.jalur || '');

    var titikLatLng = (isLaut && /medan/i.test(info.asal) && /kuala/i.test(info.tujuan))
      ? [pAsal, [3.4500, 100.3500], pTujuan]
      : [pAsal, pTujuan];

    var titikPx = titikLatLng.map(function (ll) {
      return map.latLngToContainerPoint(ll);
    });

    var lama = container.querySelector('#ct-rute-nempel-koordinat');
    if (lama) lama.remove();

    var w = container.clientWidth;
    var h = container.clientHeight;
    if (!w || !h) return;

    var warna = isLaut ? '#0284c7' : '#059669';
    var ikon = isLaut ? '🚢' : '✈';

    var ns = 'http://www.w3.org/2000/svg';
    var layer = document.createElement('div');
    layer.id = 'ct-rute-nempel-koordinat';
    layer.style.cssText = 'position:absolute;inset:0;pointer-events:none;z-index:999;overflow:hidden;';

    var svg = document.createElementNS(ns, 'svg');
    svg.setAttribute('viewBox', '0 0 ' + w + ' ' + h);
    svg.style.cssText = 'width:100%;height:100%;display:block;';

    var pointsStr = titikPx.map(function (pt) {
      return pt.x + ',' + pt.y;
    }).join(' ');

    var polyline = document.createElementNS(ns, 'polyline');
    polyline.setAttribute('points', pointsStr);
    polyline.setAttribute('fill', 'none');
    polyline.setAttribute('stroke', warna);
    polyline.setAttribute('stroke-width', '3.5');
    polyline.setAttribute('stroke-dasharray', isLaut ? '6,5' : 'none');
    svg.appendChild(polyline);
    layer.appendChild(svg);

    var ujung = titikPx[titikPx.length - 1];
    var badge = document.createElement('div');
    badge.style.cssText =
      'position:absolute;left:' + ujung.x + 'px;top:' + ujung.y + 'px;transform:translate(-50%,-125%);' +
      'background:' + warna + ';color:#fff;padding:3px 9px;border-radius:999px;' +
      'font-size:11px;font-weight:700;white-space:nowrap;box-shadow:0 2px 6px rgba(0,0,0,.35);border:1.5px solid #fff;';
    badge.textContent = ikon + ' ' + info.asal + ' → ' + info.tujuan + ' (' + info.resi + ')';
    layer.appendChild(badge);

    container.appendChild(layer);

    if (!map._ctPasangListenerNempel) {
      map._ctPasangListenerNempel = true;
      map.on('move zoom moveend zoomend viewreset', function () {
        gambarNempelPeta();
      });
    }
  }

  setInterval(function () {
    sinkronTeksKeStorage();
    gambarNempelPeta();
  }, 500);
})();
</script>
</body>
</html>