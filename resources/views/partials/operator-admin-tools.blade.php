@if(auth()->check() && (auth()->user()->isOperator() || auth()->user()->isAdmin()))
@php
    $opShipments = \App\Models\Shipment::with(['originLocation', 'destinationLocation'])
        ->orderByDesc('id')
        ->limit(30)
        ->get();
@endphp

<div id="ct-operator-suite" style="max-width:1280px;margin:20px auto 36px;padding:16px;background:#ffffff;border:1px solid #cbd5e1;border-radius:12px;">
    <div style="margin-bottom:14px;">
        <h3 style="margin:0 0 4px;font-size:16px;font-weight:700;color:#0f172a;">Logistics Operator — Operasional Shipment</h3>
        <p style="margin:0;font-size:12px;color:#475569;">Shipment operasional, buat shipment, perbarui shipment, dan tambah tracking event.</p>
    </div>
    <div style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;margin-bottom:16px;">
        <strong style="display:block;font-size:13px;color:#0f172a;margin-bottom:8px;">Shipment Operasional</strong>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;text-align:left;">
                <thead>
                    <tr style="border-bottom:1px solid #cbd5e1;">
                        <th style="padding:6px;">ID</th>
                        <th style="padding:6px;">No Resi</th>
                        <th style="padding:6px;">Kargo</th>
                        <th style="padding:6px;">Rute</th>
                        <th style="padding:6px;">Status</th>
                        <th style="padding:6px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($opShipments as $ship)
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td style="padding:6px;">#{{ $ship->id }}</td>
                        <td style="padding:6px;font-weight:700;">{{ $ship->shipment_number }}</td>
                        <td style="padding:6px;">{{ $ship->cargo_type }}</td>
                        <td style="padding:6px;">{{ $ship->originLocation->code ?? '-' }} &rarr; {{ $ship->destinationLocation->code ?? '-' }}</td>
                        <td style="padding:6px;font-weight:600;">{{ $ship->current_status }}</td>
                        <td style="padding:6px;">
                            <button type="button" onclick="ctSelectShipment({{ $ship->id }})" style="padding:3px 8px;border:none;border-radius:4px;background:#0f172a;color:#fff;cursor:pointer;">Pilih</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;">
        <form id="ct-form-create" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;display:flex;flex-direction:column;gap:8px;">
            @csrf
            <strong style="font-size:13px;color:#0f172a;">Buat Shipment</strong>
            <input type="text" name="shipment_number" required placeholder="Shipment Number (CT-2026-101)" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="number" name="customer_id" required value="3" placeholder="Customer ID" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="number" name="carrier_id" required value="1" placeholder="Carrier ID" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="number" name="origin_location_id" required value="1" placeholder="Origin Location ID" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="number" name="destination_location_id" required value="2" placeholder="Destination Location ID" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="text" name="cargo_type" required placeholder="Cargo Type" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="number" step="0.01" name="weight_kg" value="10" placeholder="Weight (kg)" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="number" step="0.001" name="volume_m3" value="0.2" placeholder="Volume (m3)" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <button type="submit" style="background:#0f172a;color:#fff;border:none;border-radius:6px;padding:8px;font-size:12px;font-weight:700;cursor:pointer;">Simpan Shipment</button>
            <div id="ct-msg-create" style="font-size:12px;"></div>
        </form>

        <form id="ct-form-update" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;display:flex;flex-direction:column;gap:8px;">
            @csrf
            <strong style="font-size:13px;color:#0f172a;">Perbarui Shipment</strong>
            <input type="number" id="ct-id-update" name="shipment_id" required placeholder="Shipment ID" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <select name="status" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
                <option value="BOOKED">BOOKED</option>
                <option value="PICKED_UP">PICKED_UP</option>
                <option value="IN_TRANSIT">IN_TRANSIT</option>
                <option value="DELIVERED">DELIVERED</option>
            </select>
            <input type="datetime-local" name="estimated_arrival_at" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="text" name="delay_reason" placeholder="Catatan / Alasan Delay" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <button type="submit" style="background:#d97706;color:#fff;border:none;border-radius:6px;padding:8px;font-size:12px;font-weight:700;cursor:pointer;">Perbarui Shipment</button>
            <div id="ct-msg-update" style="font-size:12px;"></div>
        </form>
        <form id="ct-form-tracking" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;display:flex;flex-direction:column;gap:8px;">
            @csrf
            <strong style="font-size:13px;color:#0f172a;">Tambah Tracking Event</strong>
            <input type="number" id="ct-id-tracking" name="shipment_id" required placeholder="Shipment ID" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <select name="event_type" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
                <option value="BOOKED">BOOKED</option>
                <option value="PICKED_UP">PICKED_UP</option>
                <option value="IN_TRANSIT" selected>IN_TRANSIT</option>
                <option value="DELIVERED">DELIVERED</option>
                <option value="EXCEPTION">EXCEPTION</option>
            </select>
            <input type="text" name="location_name" placeholder="Lokasi Kejadian" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="datetime-local" name="occurred_at" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="text" name="description" placeholder="Deskripsi Event" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:6px;padding:8px;font-size:12px;font-weight:700;cursor:pointer;">Tambah Tracking Event</button>
            <div id="ct-msg-tracking" style="font-size:12px;"></div>
        </form>
    </div>
</div>
<script>
function ctSelectShipment(id) {
    const u = document.getElementById('ct-id-update');
    const t = document.getElementById('ct-id-tracking');
    if (u) u.value = id;
    if (t) t.value = id;
}

document.addEventListener('DOMContentLoaded', () => {
    const hdr = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
    };
    const showMsg = (id, ok, txt) => {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = txt;
        el.style.color = ok ? '#059669' : '#dc2626';
        el.style.fontWeight = '600';
    };

    document.getElementById('ct-form-create')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const res = await fetch('/api/shipments', { method: 'POST', headers: hdr, body: JSON.stringify(Object.fromEntries(new FormData(e.target).entries())) });
        const d = await res.json();
        showMsg('ct-msg-create', res.ok, res.ok ? `Shipment tersimpan (#${d.data?.id ?? ''})` : (d.message || 'Gagal menyimpan'));
    });

    document.getElementById('ct-form-update')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const id = fd.get('shipment_id');
        const body = Object.fromEntries(fd.entries());
        delete body.shipment_id;
        const res = await fetch(`/api/shipments/${id}`, { method: 'PATCH', headers: hdr, body: JSON.stringify(body) });
        const d = await res.json();
        showMsg('ct-msg-update', res.ok, res.ok ? `Shipment #${id} diperbarui` : (d.message || 'Gagal memperbarui'));
    });

    document.getElementById('ct-form-tracking')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const id = fd.get('shipment_id');
        const body = Object.fromEntries(fd.entries());
        delete body.shipment_id;
        const res = await fetch(`/api/shipments/${id}/tracking`, { method: 'POST', headers: hdr, body: JSON.stringify(body) });
        const d = await res.json();
        showMsg('ct-msg-tracking', res.ok, res.ok ? `Tracking event tersimpan (#${id})` : (d.message || 'Gagal menambah event'));
    });
});
</script>
@endif