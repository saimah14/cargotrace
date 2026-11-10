<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CargoTrace - Tracking Resi</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <style>
        .leaflet-container img { max-width: none !important; max-height: none !important; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans flex min-h-screen">

    <!-- SIDEBAR NAVIGASI (Tema Hitam CargoTrace) -->
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
            <a href="/" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/60 hover:text-slate-200 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="/tracking" class="flex items-center space-x-3 px-3.5 py-2.5 rounded-xl bg-cyan-600/20 text-cyan-400 font-bold border border-cyan-500/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>Tracking</span>
            </a>
        </nav>
    </aside>

    <!-- KONTEN UTAMA TRACKING -->
    <main class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex justify-between items-center sticky top-0 z-40">
            <div>
                <h1 class="text-lg font-black text-slate-800 tracking-tight">Shipment Tracking Portal</h1>
                <p class="text-xs text-slate-500">Lacak lokasi paket & status perjalanan secara real-time</p>
            </div>
            <a href="/" class="text-xs bg-slate-100 hover:bg-slate-200 font-bold text-slate-700 px-3.5 py-1.5 rounded-lg transition border border-slate-300">
                &larr; Kembali ke Dashboard
            </a>
        </header>

        <div class="p-8 space-y-6 max-w-5xl">
            <!-- Form Input Resi -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs">
                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">Cari Nomor Resi / Waybill</h3>
                <form action="/tracking" method="GET" class="flex gap-3">
                    <input type="text" name="resi" value="{{ request('resi', 'CT-DEMO-0001') }}" placeholder="Masukkan Nomor Resi (Contoh: CT-DEMO-0001)..." 
                           class="flex-1 border border-slate-300 rounded-xl px-4 py-2.5 text-xs focus:ring-2 focus:ring-cyan-500 focus:outline-none font-mono">
                    <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold px-6 py-2.5 rounded-xl text-xs shadow-xs transition">
                        Lacak Paket
                    </button>
                </form>
            </div>

            <!-- Result Cards (Status & Map) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-xs space-y-6">
                <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nomor Resi</span>
                        <h2 class="text-lg font-black text-cyan-600 font-mono">{{ request('resi', 'CT-DEMO-0001') }}</h2>
                    </div>
                    <div class="text-right">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status Pengiriman</span>
                        <span class="block px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full font-bold text-xs mt-0.5">IN TRANSIT (Dalam Perjalanan)</span>
                    </div>
                </div>

                <!-- Info Ringkas Rute -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 text-[10px] block font-bold">ASAL (ORIGIN)</span>
                        <strong class="text-slate-800 text-sm">Tanjung Priok, Jakarta</strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 text-[10px] block font-bold">POSISI SEKARANG</span>
                        <strong class="text-indigo-600 text-sm">Port of Singapore</strong>
                    </div>
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200/60">
                        <span class="text-slate-400 text-[10px] block font-bold">TUJUAN (DESTINATION)</span>
                        <strong class="text-slate-800 text-sm">Port of Rotterdam</strong>
                    </div>
                </div>

                <!-- Peta Rute Khusus -->
                <div>
                    <h3 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Visualisasi Rute Kapal</h3>
                    <div id="tracking-map" style="height: 300px; width: 100%;" class="rounded-xl border border-slate-200"></div>
                </div>
            </div>
        </div>
    </main>

    <script>
        window.addEventListener('load', function () {
            if (typeof L !== 'undefined') {
                const map = L.map('tracking-map').setView([1.3521, 103.8198], 3);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(map);

                const p1 = [-6.1018, 106.8829];
                const p2 = [1.3521, 103.8198];
                const p3 = [51.9244, 4.4777];

                L.marker(p1).addTo(map).bindPopup("Origin: Tanjung Priok");
                L.marker(p2).addTo(map).bindPopup("Current: Singapore Port").openPopup();
                L.marker(p3).addTo(map).bindPopup("Destination: Rotterdam");

                L.polyline([p1, p2, p3], { color: '#4f46e5', weight: 3, dashArray: '6, 6' }).addTo(map);
                setTimeout(() => { map.invalidateSize(); }, 200);
            }
        });
    </script>
</body>
</html>