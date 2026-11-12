(function () {
  'use strict';

  const nav = document.getElementById('ctSide');
  const appEl = document.getElementById('app');
  if (!nav || !appEl) return;
  const header = appEl.querySelector('header');

  // ---------- CSS ----------
  const css = `
  /* Sidebar */
  #ctSide{background:linear-gradient(180deg,#071a3a 0,#0a2a5e 100%);padding:18px 14px;gap:3px}
  #ctSide .brand{font-size:22px;padding:4px 10px 8px}
  #ctSide .ct-role{margin:0 10px 12px;display:inline-block;align-self:flex-start;background:rgba(56,189,248,.15);color:#7dd3fc;font-size:10px;font-weight:700;letter-spacing:.8px;padding:4px 10px;border-radius:99px;border:1px solid rgba(56,189,248,.35)}
  #ctSide .grp{color:#6b87b8;font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;padding:14px 12px 6px}
  #ctSide a{border-radius:12px;border:1px solid transparent;color:#c3d3ea}
  #ctSide a:hover{background:rgba(56,189,248,.1);color:#fff}
  #ctSide a.on{background:linear-gradient(90deg,rgba(14,165,233,.38),rgba(14,165,233,.14));border-color:rgba(56,189,248,.55);color:#e0f2fe;box-shadow:0 0 18px rgba(14,165,233,.25)}
  #ctSide a.ct-logout{margin-top:auto;color:#fca5a5;background:rgba(244,63,94,.1);border-color:rgba(244,63,94,.25);justify-content:center}
  #ctSide a.ct-logout:hover{background:rgba(244,63,94,.22);color:#fff}

  /* Header */
  header{background:#fff!important;color:#0f2a4a;border-bottom:1px solid #e2e8f0;box-shadow:none;padding:14px 28px;gap:16px}
  header strong{font-size:20px;color:#0f2a4a;white-space:nowrap}
  #ctSearch{flex:1;max-width:480px;padding:11px 16px;border-radius:12px;background:#f1f5f9;border:1px solid #e2e8f0;font-size:14px}
  #who{color:#475569;font-size:13px;font-weight:600}
  #logoutBtn{background:#fee2e2;color:#be123c;box-shadow:none;padding:8px 14px;font-size:13px}

  /* Peta */
  #map{height:470px;border-radius:16px}
  .ct-vwrap{background:none;border:0}
  .ct-v{position:absolute;left:-15px;top:-15px;display:flex;align-items:center;gap:6px;white-space:nowrap}
  .ct-v i{font-style:normal;width:30px;height:30px;border-radius:50%;background:#fff;border:2px solid #0891b2;box-shadow:0 3px 10px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;font-size:16px}
  .ct-v.late i{border-color:#e11d48;box-shadow:0 0 0 4px rgba(225,29,72,.25),0 3px 10px rgba(0,0,0,.3)}
  .ct-v b{background:#0f2a4a;color:#fff;font-size:10px;font-weight:700;padding:3px 9px;border-radius:99px;box-shadow:0 2px 6px rgba(0,0,0,.3)}
  .ct-legend{background:rgba(255,255,255,.95);padding:8px 12px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.2);font-size:11px;line-height:1.9;color:#334155}
  .ct-legend i{display:inline-block;width:10px;height:10px;border-radius:50%;margin-right:6px}
  .ct-legend s{display:inline-block;width:22px;border-top:2px dashed #0e7490;margin-right:6px;vertical-align:middle;text-decoration:none}
  .ct-legend u{display:inline-block;width:22px;border-top:3px solid #f59e0b;margin-right:6px;vertical-align:middle;text-decoration:none}
  `;
  const st = document.createElement('style');
  st.textContent = css;
  document.head.appendChild(st);

  // ---------- Sidebar: grup menu, badge role, logout ----------
  const GROUPS = [
    ['Main', ['dashboard']],
    ['Global Shipments', ['shipments', 'tracking', 'exceptions']],
    ['Resources', ['carriers', 'users', 'reports']],
    ['Monitoring', ['audit', 'notifications', 'currency', 'news']]
  ];

  function decorate() {
    if (!nav.querySelector('a[data-v]') || nav.querySelector('.grp')) return;
    GROUPS.forEach(g => {
      const first = g[1].map(id => nav.querySelector('a[data-v="' + id + '"]')).find(Boolean);
      if (first) {
        const h = document.createElement('div');
        h.className = 'grp';
        h.textContent = g[0];
        nav.insertBefore(h, first);
      }
    });
    const brand = nav.querySelector('.brand');
    if (brand && typeof me !== 'undefined' && me) {
      const r = document.createElement('div');
      r.className = 'ct-role';
      r.textContent = '● ' + String(me.role).toUpperCase();
      brand.insertAdjacentElement('afterend', r);
    }
    if (!nav.querySelector('.ct-logout')) {
      const l = document.createElement('a');
      l.className = 'ct-logout';
      l.innerHTML = '<span>⎋</span>Log out';
      nav.appendChild(l);
    }
  }
  new MutationObserver(decorate).observe(nav, { childList: true });
  decorate();

  nav.addEventListener('click', e => {
    if (e.target.closest('.ct-logout')) document.getElementById('logoutBtn').click();
  });

  // ---------- Header: judul halaman + pencarian ----------
  const titleEl = header ? header.querySelector('strong') : null;
  function syncTitle() {
    const on = nav.querySelector('a.on');
    if (titleEl && on && on.lastChild) titleEl.textContent = on.lastChild.textContent.trim();
  }
  new MutationObserver(syncTitle).observe(nav, { attributes: true, subtree: true, attributeFilter: ['class'] });

  if (header && titleEl && !document.getElementById('ctSearch')) {
    const input = document.createElement('input');
    input.id = 'ctSearch';
    input.placeholder = '🔍  Search shipment number...';
    titleEl.insertAdjacentElement('afterend', input);
    input.addEventListener('keydown', e => {
      if (e.key !== 'Enter') return;
      document.getElementById('fSearch').value = input.value.trim();
      const link = nav.querySelector('a[data-v="shipments"]');
      if (link) link.click();
      loadShipments(1);
    });
  }

  const mapH = document.querySelector('#mapCard h2');
  if (mapH) mapH.innerHTML = 'Global Shipment Map <small>Active cargo, vessels and planned routes</small>';

  // ---------- Peta ----------
  let proLayer = null, legendOn = false, popupOn = false, gen = 0;

  function arc(a, b, n) {
    n = n || 40;
    const dlat = b[0] - a[0], dlng = b[1] - a[1];
    const len = Math.hypot(dlat, dlng) || 1;
    const c = [(a[0] + b[0]) / 2 + (-dlng / len) * len * 0.2, (a[1] + b[1]) / 2 + (dlat / len) * len * 0.2];
    const pts = [];
    for (let i = 0; i <= n; i++) {
      const t = i / n, u = 1 - t;
      pts.push([u * u * a[0] + 2 * u * t * c[0] + t * t * b[0], u * u * a[1] + 2 * u * t * c[1] + t * t * b[1]]);
    }
    return pts;
  }

  function prep() {
    if (typeof map === 'undefined' || !map) return;
    if (typeof layer !== 'undefined' && layer && map.hasLayer(layer)) map.removeLayer(layer); // sembunyikan gambar lama
    if (!proLayer) proLayer = L.layerGroup().addTo(map);
    proLayer.clearLayers();
    if (!legendOn) {
      const c = L.control({ position: 'bottomleft' });
      c.onAdd = () => {
        const d = L.DomUtil.create('div', 'ct-legend');
        d.innerHTML = '<i style="background:#16a34a"></i>Origin &nbsp; <i style="background:#e11d48"></i>Destination &nbsp; 🚢 Vessel<br><s></s>Planned route &nbsp; <u></u>Route traveled';
        return d;
      };
      c.addTo(map);
      legendOn = true;
    }
    if (!popupOn) {
      map.on('popupopen', e => {
        const a = e.popup.getElement() && e.popup.getElement().querySelector('.ct-open');
        if (a) a.onclick = ev => { ev.preventDefault(); openDetail(a.dataset.id); };
      });
      popupOn = true;
    }
  }

  const baseInit = initMap;
  window.initMap = function () {
    const ok = baseInit();
    if (ok) prep();
    return ok;
  };

  const pts = ev => (ev || []).filter(e => e.latitude != null && e.longitude != null).map(e => [+e.latitude, +e.longitude]);
  const pin = (p, color, text) => L.circleMarker(p, { radius: 6, color: '#fff', weight: 2, fillColor: color, fillOpacity: 1 }).addTo(proLayer).bindPopup(esc(text));

  function drawShip(s, ev) {
    const o = s.origin, t = s.destination, bounds = [];
    if (!o || !t) return bounds;
    const a = [+o.latitude, +o.longitude], b = [+t.latitude, +t.longitude];
    L.polyline(arc(a, b), { color: '#0e7490', weight: 2, dashArray: '6 8', opacity: 0.85 }).addTo(proLayer);
    pin(a, '#16a34a', 'Origin: ' + o.name);
    pin(b, '#e11d48', 'Destination: ' + t.name);
    const path = pts(ev);
    if (path.length > 1) L.polyline(path, { color: '#f59e0b', weight: 3 }).addTo(proLayer);
    const cur = path.length ? path[path.length - 1] : a;
    const late = !!s.is_delayed;
    const html = '<b>' + esc(s.shipment_number) + '</b><br>' + esc(s.carrier && s.carrier.name) + '<br>' + esc(o.name) + ' &rarr; ' + esc(t.name) +
      '<br>Status: ' + esc(s.status) + (late ? ' (DELAYED)' : '') + '<br>ETA: ' + esc(fmt(s.estimated_arrival || s.expected_arrival)) +
      '<br><a href="#" class="ct-open" data-id="' + esc(s.id) + '">View details</a>';
    L.marker(cur, {
      icon: L.divIcon({ className: 'ct-vwrap', html: '<div class="ct-v' + (late ? ' late' : '') + '"><i>🚢</i><b>' + esc(s.shipment_number) + '</b></div>', iconSize: [0, 0] })
    }).addTo(proLayer).bindPopup(html);
    bounds.push(a, b, cur);
    return bounds;
  }

  window.loadMap = async function () {
    if (!initMap()) return;
    const run = ++gen;
    try {
      const d = await api('/shipments');
      const rows = d.data.slice(0, 12);
      const evs = await Promise.all(rows.map(s => api('/shipments/' + s.id + '/events').catch(() => [])));
      if (run !== gen) return;
      const bounds = [];
      rows.forEach((s, i) => bounds.push(...drawShip(s, evs[i])));
      if (bounds.length) map.fitBounds(bounds, { padding: [40, 40] });
    } catch (e) { /* peta tetap tampil tanpa rute */ }
  };

  window.drawDetailMap = function (s, ev) {
    if (!initMap()) return;
    gen++;
    const bounds = drawShip(s, ev);
    (ev || []).filter(e => e.latitude != null && e.longitude != null).forEach(e => {
      L.circleMarker([+e.latitude, +e.longitude], { radius: 4, color: e.is_exception ? '#e11d48' : '#f59e0b', fillColor: '#fff', fillOpacity: 1, weight: 2 })
        .addTo(proLayer)
        .bindPopup('<b>' + esc(e.event_type) + '</b><br>' + esc(fmt(e.occurred_at)) + '<br>' + esc(e.location ? e.location.name : ''));
    });
    if (bounds.length) map.fitBounds(bounds, { padding: [50, 50], maxZoom: 6 });
  };

  // Halaman di-reload dengan sesi login yang masih ada: gambar ulang peta gaya baru
  if (typeof map !== 'undefined' && map) { prep(); window.loadMap(); }
})();