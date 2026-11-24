@if(auth()->check() && auth()->user()->hasRole('operator', 'admin'))
@php
    $opShipments = \App\Models\Shipment::with(['origin', 'destination'])
        ->orderByDesc('id')
        ->limit(30)
        ->get();
    $opCustomers = \App\Models\User::whereIn('role', ['customer', 'Customer'])->orderBy('name')->get(['id', 'name', 'email']);
    $opCarriers = \App\Models\Carrier::where('is_active', true)->orderBy('name')->get(['id', 'name']);
    $opLocations = \App\Models\Location::orderBy('name')->get(['id', 'name', 'country']);
@endphp

<div id="ct-shipment-select-options" hidden>
    <select name="customer_id">
        <option value="">Pilih customer</option>
        @foreach($opCustomers as $customer)<option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>@endforeach
    </select>
    <select name="carrier_id">
        <option value="">Pilih carrier</option>
        @foreach($opCarriers as $carrier)<option value="{{ $carrier->id }}">{{ $carrier->name }}</option>@endforeach
    </select>
</div>
<datalist id="ct-country-options"></datalist>

<details id="ct-operator-suite" style="max-width:1280px;margin:20px auto 36px;padding:16px;background:#ffffff;border:1px solid #cbd5e1;border-radius:12px;">
    <summary style="cursor:pointer;color:#0f172a;">
        <strong style="font-size:16px;">Fitur tambahan</strong>
        <span style="display:block;margin-top:4px;font-size:12px;color:#475569;">Pengaturan lanjutan untuk admin/operator. Tidak perlu dibuka untuk kirim barang biasa.</span>
    </summary>
    <div style="margin-top:14px;">
    <div style="display:grid;grid-template-columns:minmax(280px,1fr);gap:14px;">
        <details id="ct-advanced-settings" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;">
            <summary style="cursor:pointer;font-size:13px;font-weight:700;color:#334155;">Edit detail dan tambah tracking manual</summary>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:14px;margin-top:12px;">
        <form id="ct-form-update" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;display:flex;flex-direction:column;gap:8px;">
            <strong style="font-size:13px;color:#0f172a;">Perbarui Detail Shipment</strong>
            <select id="ct-id-update" name="shipment_id" required onchange="loadShipmentIntoEdit(this.value)" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
                <option value="">Pilih shipment</option>
                @foreach($opShipments as $ship)<option value="{{ $ship->id }}">{{ $ship->shipment_number }}</option>@endforeach
            </select>
            <select name="carrier_id" required style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
                @foreach($opCarriers as $carrier)<option value="{{ $carrier->id }}">{{ $carrier->name }}</option>@endforeach
            </select>
            <select name="origin_id" required style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
                @foreach($opLocations as $location)<option value="{{ $location->id }}">{{ $location->name }}, {{ $location->country }}</option>@endforeach
            </select>
            <select name="destination_id" required style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
                @foreach($opLocations as $location)<option value="{{ $location->id }}">{{ $location->name }}, {{ $location->country }}</option>@endforeach
            </select>
            <input type="text" name="cargo_type" maxlength="60" required placeholder="Jenis kargo" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="datetime-local" name="expected_departure" aria-label="Jadwal keberangkatan baru" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="datetime-local" name="expected_arrival" aria-label="Estimasi tiba baru" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <button type="submit" style="background:#d97706;color:#fff;border:none;border-radius:6px;padding:8px;font-size:12px;font-weight:700;cursor:pointer;">Perbarui Shipment</button>
            <div id="ct-msg-update" role="status" style="font-size:12px;"></div>
        </form>

        <form id="ct-form-tracking" style="border:1px solid #e2e8f0;border-radius:10px;padding:12px;display:flex;flex-direction:column;gap:8px;">
            <strong style="font-size:13px;color:#0f172a;">Tambah Tracking Event</strong>
            <select id="ct-id-tracking" name="shipment_id" required style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
                <option value="">Pilih shipment</option>
                @foreach($opShipments as $ship)<option value="{{ $ship->id }}">{{ $ship->shipment_number }}</option>@endforeach
            </select>
            <input type="text" name="event_type" maxlength="30" required placeholder="Jenis event (contoh: DEPARTED)" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <select name="location_id" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
                <option value="">Pilih lokasi (opsional)</option>
                @foreach($opLocations as $location)<option value="{{ $location->id }}">{{ $location->name }}, {{ $location->country }}</option>@endforeach
            </select>
            <input type="datetime-local" name="occurred_at" required aria-label="Waktu event" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <input type="text" name="description" maxlength="255" placeholder="Deskripsi event" style="border:1px solid #cbd5e1;border-radius:6px;padding:6px 8px;font-size:12px;">
            <button type="submit" style="background:#2563eb;color:#fff;border:none;border-radius:6px;padding:8px;font-size:12px;font-weight:700;cursor:pointer;">Tambah Tracking Event</button>
            <div id="ct-msg-tracking" role="status" style="font-size:12px;"></div>
        </form>
            </div>
        </details>
    </div>
    </div>
</details>
<script>
const shipmentsApiUrl = @json(rtrim(request()->getBaseUrl(), '/') . '/api/shipments');

document.addEventListener('DOMContentLoaded', function () {
    const token = @json($apiToken);
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    };
    const showMessage = function (id, ok, text) {
        const element = document.getElementById(id);
        if (!element) return;
        element.textContent = text;
        element.style.color = ok ? '#059669' : '#dc2626';
        element.style.fontWeight = '600';
    };
    const errorMessage = function (data, fallback) {
        const errors = data && data.errors ? Object.values(data.errors).flat() : [];
        return errors[0] || (data && data.message) || fallback;
    };
    const submit = async function (url, method, body, messageId, successMessage) {
        try {
            const response = await fetch(url, { method: method, headers: headers, body: JSON.stringify(body) });
            const result = await response.json();
            if (!response.ok) throw new Error(errorMessage(result, 'Permintaan gagal.'));
            showMessage(messageId, true, successMessage);
            if (typeof loadShipmentsFromApi === 'function') await loadShipmentsFromApi();
            if (typeof loadTrackingTimeline === 'function') await loadTrackingTimeline();
            if (typeof loadDashboardStats === 'function') await loadDashboardStats();
            if (typeof loadAuditLogs === 'function' && document.getElementById('tab-audit-logs').classList.contains('active')) await loadAuditLogs();
            return result;
        } catch (error) {
            showMessage(messageId, false, error.message || 'Permintaan gagal.');
            return null;
        }
    };

    window.loadShipmentIntoEdit = async function (id) {
        const message = document.getElementById('ct-msg-update');
        if (!id) return;
        try {
            const response = await fetch(shipmentsApiUrl + '/' + id, { headers: headers });
            const shipment = await response.json();
            if (!response.ok) throw new Error(shipment.message || 'Detail kiriman tidak dapat dimuat.');
            const form = document.getElementById('ct-form-update');
            form.elements.carrier_id.value = shipment.carrier_id;
            form.elements.origin_id.value = shipment.origin_id;
            form.elements.destination_id.value = shipment.destination_id;
            form.elements.cargo_type.value = shipment.cargo_type;
            const toLocalDateTime = function (value) {
                const date = new Date(value);
                return new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
            };
            form.elements.expected_departure.value = toLocalDateTime(shipment.expected_departure);
            form.elements.expected_arrival.value = toLocalDateTime(shipment.expected_arrival);
        } catch (error) {
            showMessage('ct-msg-update', false, error.message || 'Detail kiriman tidak dapat dimuat.');
        }
    };

    fetch(apiBaseUrl + '/countries', { headers: headers })
        .then(async function (response) {
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Daftar negara tidak dapat dimuat.');
            const countries = Array.isArray(result.countries) ? result.countries : [];
            const options = document.getElementById('ct-country-options');
            countries.forEach(function (country) {
                const option = document.createElement('option');
                option.value = country;
                options.appendChild(option);
            });
            const countryStatus = document.getElementById('ct-modal-country-status');
            if (countryStatus) {
                countryStatus.textContent = countries.length
                    ? countries.length + ' negara tersedia. Kota bisa diketik manual.'
                    : 'Daftar negara kosong; nama negara tetap bisa diketik manual.';
            }
        })
        .catch(function (error) {
            const countryStatus = document.getElementById('ct-modal-country-status');
            if (countryStatus) countryStatus.textContent = 'Daftar negara tidak dapat dimuat; nama negara tetap bisa diketik manual.';
            console.warn(error);
        });

    document.querySelectorAll('#ct-form-tracking input[name="occurred_at"]').forEach(function (input) {
        const date = new Date();
        input.value = new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    });

    document.getElementById('ct-form-update').addEventListener('submit', async function (event) {
        event.preventDefault();
        const form = Object.fromEntries(new FormData(event.currentTarget).entries());
        const id = form.shipment_id;
        delete form.shipment_id;
        Object.keys(form).forEach(function (key) { if (form[key] === '') delete form[key]; });
        await submit(shipmentsApiUrl + '/' + id, 'PATCH', form, 'ct-msg-update', 'Detail shipment berhasil diperbarui.');
    });

    document.getElementById('ct-form-tracking').addEventListener('submit', async function (event) {
        event.preventDefault();
        const form = Object.fromEntries(new FormData(event.currentTarget).entries());
        const id = form.shipment_id;
        delete form.shipment_id;
        if (form.location_id === '') delete form.location_id;
        await submit(shipmentsApiUrl + '/' + id + '/tracking', 'POST', form, 'ct-msg-tracking', 'Tracking event berhasil ditambahkan.');
    });
});
</script>
@endif
