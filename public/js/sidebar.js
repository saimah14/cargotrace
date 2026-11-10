(function () {
  'use strict';

  // ---------- CSS: sidebar + tema ----------
  const css = `
  /* Sidebar */
  #ctSide{position:fixed;top:0;left:0;bottom:0;width:220px;background:linear-gradient(180deg,#0b1a2e,#0f2a4a);display:none;flex-direction:column;gap:4px;padding:16px 12px;overflow-y:auto;z-index:1000;box-sizing:border-box;box-shadow:2px 0 16px rgba(11,26,46,.25)}
  #ctSide .brand{color:#fff;font-weight:800;font-size:20px;padding:6px 10px 18px;letter-spacing:.3px}
  #ctSide .brand span{color:#38bdf8}
  #ctSide a{display:flex;gap:12px;align-items:center;padding:11px 14px;border-radius:12px;color:#b6c4d6;text-decoration:none;font-size:14px;cursor:pointer;transition:background .15s,color .15s}
  #ctSide a:hover{background:rgba(56,189,248,.1);color:#fff}
  #ctSide a.on{background:rgba(56,189,248,.18);color:#38bdf8;font-weight:600}
  body.ct-side{padding-left:220px}
  .ct-off{display:none!important}

  /* Tema */
  body{background:linear-gradient(180deg,#e9eff8 0,#f5f7fb 380px);font-family:'Inter','Segoe UI',system-ui,Arial,sans-serif}
  header{background:linear-gradient(90deg,#0b1a2e,#12355b 60%,#0e7490);padding:14px 28px;box-shadow:0 2px 14px rgba(11,26,46,.25)}
  header strong{font-size:18px;letter-spacing:.3px}
  main{max-width:1180px;margin:26px auto;padding:0 20px}
  .card{border-radius:18px;padding:22px;box-shadow:0 4px 20px rgba(15,23,42,.06);border:1px solid #e6ecf5}
  h2{font-size:18px;color:#0f2a4a}
  h2 small{font-weight:400;color:#64748b;font-size:12px}
  .stat{background:linear-gradient(135deg,#f8fbff,#edf4fc);border:1px solid #dfe9f5;border-radius:14px;padding:16px;font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#64748b}
  .stat b{font-size:30px;color:#0f2a4a;text-transform:none;letter-spacing:0;margin-bottom:2px}
  .stat span{text-transform:none;letter-spacing:0;font-size:12px;color:#94a3b8}
  button{background:linear-gradient(135deg,#12355b,#0e7490);border-radius:10px;font-weight:600;transition:box-shadow .2s,transform .1s}
  button:hover:not(:disabled){box-shadow:0 4px 14px rgba(14,116,144,.35)}
  button:active:not(:disabled){transform:translateY(1px)}
  input,select{border-radius:10px;border:1px solid #d3dcea;background:#fff}
  input:focus,select:focus{outline:none;border-color:#0e7490;box-shadow:0 0 0 3px rgba(14,116,144,.15)}
  thead th{background:#f0f5fb;color:#475569;font-size:11px;text-transform:uppercase;letter-spacing:.5px}
  th,td{padding:12px 10px}
  tr[data-id]:hover,tr[data-sid]:hover{background:#eef6fe}
  .badge{background:#e0ecf8;color:#12355b;font-weight:600}
  .badge.late{background:#fde2e2;color:#a61b1b}
  #map{border-radius:14px;border:1px solid #dbe4f0;height:420px}
  #login{margin:80px auto;padding:32px;border-radius:20px;box-shadow:0 12px 40px rgba(15,23,42,.12)}
  #login h1{color:#0f2a4a}
  .hint{margin:0 0 10px;color:#64748b}
  @media(max-width:800px){
    #ctSide{position:static;width:auto;flex-direction:row;flex-wrap:wrap}
    body.ct-side{padding-left:0}
  }
  @media print{#ctSide{display:none!important}body.ct-side{padding-left:0}}
  `;
  const styleEl = document.createElement('style');
  styleEl.textContent = css;
  document.head.appendChild(styleEl);

  // ---------- Menu ----------
  const ALL = ['admin', 'operator', 'customer'];
  const STAFF = ['admin', 'operator'];
  const MENU = [
    { id: 'dashboard', icon: '🏠', label: 'Dashboard', roles: ALL },
    { id: 'shipments', icon: '📦', label: 'Shipments', roles: ALL },
    { id: 'tracking', icon: '🎯', label: 'Tracking', roles: ALL },
    { id: 'exceptions', icon: '⚠️', label: 'Exceptions', roles: STAFF },
    { id: 'carriers', icon: '🚚', label: 'Carriers', roles: STAFF },
    { id: 'users', icon: '👥', label: 'User & Config', roles: ['admin'] },
    { id: 'reports', icon: '📈', label: 'Reports', roles: STAFF },
    { id: 'audit', icon: '📝', label: 'Audit Logs', roles: ['admin'] },
    { id: 'notifications', icon: '🔔', label: 'Notifications', roles: ALL },
    { id: 'currency', icon: '💲', label: 'Currency', roles: ALL },
    { id: 'news', icon: '🌐', label: 'World News', roles: ALL }
  ];

  const appEl = document.getElementById('app');
  const main = appEl.querySelector('main');
  const nav = document.createElement('nav');
  nav.id = 'ctSide';
  document.body.appendChild(nav);

  const cardOf = id => { const e = document.getElementById(id); return e ? (e.closest('.card') || e) : null; };
  function panel(id) {
    let p = document.getElementById(id);
    if (!p) { p = document.createElement('div'); p.id = id; p.className = 'card'; main.appendChild(p); }
    return p;
  }

  // Kartu mana yang tampil di tiap menu (kartu lama dipakai ulang, tidak diubah)
  const C = {
    map: () => document.getElementById('mapCard'),
    detail: () => document.getElementById('detail'),
    dash: () => document.getElementById('dashCard'),
    audit: () => document.getElementById('auditCard'),
    ships: () => cardOf('shipRows'),
    notifs: () => cardOf('notifs'),
    news: () => cardOf('news'),
    exc: () => panel('ctExc'),
    carr: () => panel('ctCarr'),
    users: () => panel('ctUsers'),
    rep: () => panel('ctRep'),
    cur: () => panel('ctCur')
  };
  const VIEWS = {
    dashboard: ['map', 'detail', 'dash'],
    shipments: ['ships', 'detail'],
    tracking: ['map', 'detail', 'ships'],
    exceptions: ['exc', 'detail'],
    carriers: ['carr'],
    users: ['users'],
    reports: ['rep', 'dash'],
    audit: ['audit'],
    notifications: ['notifs'],
    currency: ['cur'],
    news: ['news']
  };

  function show(view) {
    const keep = new Set(VIEWS[view].map(k => C[k]()));
    main.querySelectorAll(':scope > .card').forEach(c => c.classList.toggle('ct-off', !keep.has(c)));
    nav.querySelectorAll('a').forEach(a => a.classList.toggle('on', a.dataset.v === view));

    if (keep.has(C.map()) && typeof map !== 'undefined' && map) {
      setTimeout(() => map.invalidateSize(), 50);
    }
    if (view === 'tracking' && typeof selectedId !== 'undefined' && selectedId) openDetail(selectedId, true);
    if (view === 'exceptions') loadExc();
    if (view === 'carriers') loadCarriers();
    if (view === 'users') loadUsers();
    if (view === 'reports') loadReport();
    if (view === 'currency') loadCurrency();
    window.scrollTo({ top: 0 });
  }

  // ---------- Panel: Exceptions ----------
  async function loadExc() {
    const p = panel('ctExc');
    p.innerHTML = '<h2>Exceptions</h2><p>Loading...</p>';
    try {
      const d = await api('/shipments');
      const rows = [];
      for (const s of d.data) {
        const ex = await api('/shipments/' + s.id + '/exceptions');
        (Array.isArray(ex) ? ex : (ex.data || [])).forEach(x => rows.push({ s, x }));
      }
      rows.sort((a, b) => new Date(b.x.detected_at) - new Date(a.x.detected_at));
      const open = rows.filter(r => r.x.status !== 'RESOLVED').length;
      p.innerHTML = '<h2>Exceptions <small>(' + open + ' open of ' + rows.length + ', click a row for details)</small></h2>' +
        '<div class="tbl"><table><thead><tr><th>Shipment</th><th>Type</th><th>Severity</th><th>Status</th><th>Detected</th><th>Resolved</th></tr></thead><tbody>' +
        (rows.length ? rows.map(r => '<tr data-sid="' + esc(r.s.id) + '" style="cursor:pointer"><td>' + esc(r.s.shipment_number) +
          '</td><td>' + esc(r.x.type) + '</td><td>' + esc(r.x.severity) + '</td><td><span class="badge ' + (r.x.status === 'RESOLVED' ? '' : 'late') + '">' + esc(r.x.status) +
          '</span></td><td>' + esc(fmt(r.x.detected_at)) + '</td><td>' + esc(fmt(r.x.resolved_at)) + '</td></tr>').join('')
          : '<tr><td colspan="6">No exceptions.</td></tr>') + '</tbody></table></div>';
      p.onclick = e => {
        const tr = e.target.closest('tr[data-sid]');
        if (tr) { show('tracking'); openDetail(tr.dataset.sid); }
      };
    } catch (e) {
      p.innerHTML = '<h2>Exceptions</h2><p class="err">' + esc(e.message) + '</p>';
    }
  }

  // ---------- Panel: Carriers ----------
  async function loadCarriers() {
    const p = panel('ctCarr');
    p.innerHTML = '<h2>Carriers</h2><p>Loading...</p>';
    try {
      const d = await api('/carriers');
      const list = Array.isArray(d) ? d : (d.data || []);
      const skip = ['created_at', 'updated_at', 'deleted_at'];
      const cols = list.length ? Object.keys(list[0]).filter(k => !skip.includes(k) && typeof list[0][k] !== 'object') : [];
      p.innerHTML = '<h2>Carriers <small>(' + list.length + ')</small></h2><div class="tbl"><table><thead><tr>' +
        cols.map(c => '<th>' + esc(c.replace('_', ' ')) + '</th>').join('') + '</tr></thead><tbody>' +
        (list.length ? list.map(r => '<tr>' + cols.map(c => '<td>' + esc(r[c]) + '</td>').join('') + '</tr>').join('')
          : '<tr><td>No carriers.</td></tr>') + '</tbody></table></div>';
    } catch (e) {
      p.innerHTML = '<h2>Carriers</h2><p class="err">' + esc(e.message) + '</p>';
    }
  }

  // ---------- Panel: User & Config ----------
  async function loadUsers() {
    const p = panel('ctUsers');
    const cfg = '<div class="sec"><h3>Configuration (Group A scenario)</h3>' +
      'Delay threshold: <b>' + esc(THRESHOLD) + ' hours</b> (set in ExceptionController::DELAY_THRESHOLD_HOURS)<br>' +
      'Severity rule: 0-6 h LOW, over 6-24 h MEDIUM, over 24 h HIGH<br>' +
      'Signed in as: <b>' + esc(me.name) + '</b> (' + esc(me.email) + ', ' + esc(me.role) + ')</div>';
    p.innerHTML = '<h2>User &amp; Config</h2>' + cfg + '<div class="sec"><h3>Users</h3><p>Loading...</p></div>';
    try {
      const d = await api('/users');
      const list = Array.isArray(d) ? d : (d.data || []);
      const roleSel = u => '<select data-uid="' + esc(u.id) + '"' + (u.id === me.id ? ' disabled title="You cannot change your own role"' : '') + '>' +
        ['admin', 'operator', 'customer'].map(r => '<option' + (u.role === r ? ' selected' : '') + '>' + r + '</option>').join('') + '</select>';
      p.innerHTML = '<h2>User &amp; Config</h2>' + cfg + '<div class="sec"><h3>Users (' + list.length + ')</h3><div class="tbl"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th></tr></thead><tbody>' +
        list.map(u => '<tr><td>' + esc(u.name) + '</td><td>' + esc(u.email) + '</td><td>' + roleSel(u) + '</td></tr>').join('') +
        '</tbody></table></div><p id="ctUserMsg" class="err"></p></div>';
      p.onchange = async e => {
        const s = e.target.closest('select[data-uid]');
        if (!s) return;
        const msg = document.getElementById('ctUserMsg');
        try {
          await api('/users/' + s.dataset.uid + '/role', { method: 'PATCH', body: JSON.stringify({ role: s.value }) });
          msg.className = ''; msg.textContent = 'Role updated.';
          if (typeof loadAudit === 'function') loadAudit();
        } catch (err) {
          msg.className = 'err'; msg.textContent = errText(err);
          loadUsers();
        }
      };
    } catch (e) {
      p.innerHTML = '<h2>User &amp; Config</h2>' + cfg +
        '<div class="sec"><h3>Users</h3><p class="err">' + esc(e.message) + '</p></div>';
    }
  }

  // ---------- Panel: Reports ----------
  async function loadReport() {
    const p = panel('ctRep');
    p.innerHTML = '<h2>Reports</h2><p>Loading...</p>';
    try {
      const d = await api('/dashboard');
      const total = d.total_shipments || 0, delayed = d.delayed_shipments || 0;
      const rate = total ? (delayed / total * 100).toFixed(1) : '0.0';
      const tbl = (title, obj) => '<div class="sec"><h3>' + title + '</h3><table><tbody>' +
        Object.entries(obj || {}).map(([k, v]) => '<tr><td>' + esc(String(k).replace('_', ' ')) + '</td><td><b>' + esc(v) + '</b></td></tr>').join('') +
        '</tbody></table></div>';
      p.innerHTML = '<div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap"><h2>Reports</h2><button class="sm" onclick="window.print()">Print</button></div>' +
        '<div class="sec"><h3>Summary</h3><table><tbody>' +
        '<tr><td>Total shipments</td><td><b>' + esc(total) + '</b></td></tr>' +
        '<tr><td>Delayed shipments</td><td><b>' + esc(delayed) + '</b></td></tr>' +
        '<tr><td>Delay rate</td><td><b>' + rate + '%</b></td></tr>' +
        '<tr><td>Open exceptions</td><td><b>' + esc(d.open_exceptions || 0) + '</b></td></tr></tbody></table></div>' +
        tbl('Shipments by status', d.shipments_by_status) + tbl('Shipments by carrier', d.shipments_by_carrier);
    } catch (e) {
      p.innerHTML = '<h2>Reports</h2><p class="err">' + esc(e.message) + '</p>';
    }
  }

  // ---------- Panel: Currency ----------
  const CUR = {
    IDR: ['Rp', 'Indonesian Rupiah'], EUR: ['€', 'Euro'], SGD: ['S$', 'Singapore Dollar'], JPY: ['¥', 'Japanese Yen'],
    GBP: ['£', 'British Pound'], AUD: ['A$', 'Australian Dollar'], CNY: ['¥', 'Chinese Yuan'], MYR: ['RM', 'Malaysian Ringgit']
  };
  async function loadCurrency() {
    const p = panel('ctCur');
    p.innerHTML = '<h2>Currency</h2><p>Loading...</p>';
    try {
      const d = await api('/currencies');
      const rates = d.rates || {};
      const codes = Object.keys(rates);
      p.innerHTML = '<h2>Live exchange rates <small>(base USD)</small></h2>' +
        '<div class="grid">' + codes.map(c => '<div class="stat">USD / ' + esc(c) + '<b>' + esc((CUR[c] ? CUR[c][0] + ' ' : '') + num(rates[c])) + '</b><span>' + esc(CUR[c] ? CUR[c][1] : '') + '</span></div>').join('') + '</div>' +
        '<div class="sec"><h3>Converter</h3><input id="ctAmt" type="number" min="0" value="100" style="width:140px"> USD = <b id="ctOut"></b> ' +
        '<select id="ctTo">' + codes.map(c => '<option>' + esc(c) + '</option>').join('') + '</select></div>' +
        '<p><small>' + esc(d.source) + ' &middot; rates date ' + esc(d.date) + (d.stale ? ' &middot; <span class="err">cached (service unavailable)</span>' : '') + '</small></p>';
      const conv = () => {
        const amt = parseFloat(document.getElementById('ctAmt').value) || 0;
        const to = document.getElementById('ctTo').value;
        document.getElementById('ctOut').textContent = (CUR[to] ? CUR[to][0] + ' ' : '') + num(amt * rates[to]);
      };
      p.oninput = conv; p.onchange = conv; conv();
    } catch (e) {
      p.innerHTML = '<h2>Currency</h2><p class="err">' + esc(e.message) + '</p>';
    }
  }

  // ---------- Build & sync ----------
  let builtFor = null;
  function build() {
    const role = me.role;
    nav.innerHTML = '<div class="brand">Cargo<span>Trace</span></div>' +
      MENU.filter(m => m.roles.includes(role)).map(m => '<a data-v="' + m.id + '"><span>' + m.icon + '</span>' + esc(m.label) + '</a>').join('');
    builtFor = role + ':' + (me.id || '');

    // Catatan kecil di kartu Notifications untuk admin/operator
    const nc = C.notifs();
    if (nc && !document.getElementById('ctNotifHint') && STAFF.includes(role)) {
      const h = document.createElement('p');
      h.id = 'ctNotifHint';
      h.className = 'hint';
      h.innerHTML = '<small>Notifications are sent to the customer who owns the shipment (for example when a delay passes the ' + esc(THRESHOLD) + ' h threshold). Log in as a customer account to see them.</small>';
      nc.insertBefore(h, document.getElementById('notifs'));
    }
    show('dashboard');
  }

  nav.addEventListener('click', e => {
    const a = e.target.closest('a[data-v]');
    if (a) show(a.dataset.v);
  });

  function sync() {
    const visible = !appEl.classList.contains('hide') && typeof me !== 'undefined' && me;
    nav.style.display = visible ? 'flex' : 'none';
    document.body.classList.toggle('ct-side', !!visible);
    if (visible) {
      if (builtFor !== me.role + ':' + (me.id || '')) build();
    } else {
      builtFor = null;
    }
  }

  new MutationObserver(sync).observe(appEl, { attributes: true, attributeFilter: ['class'] });
  sync();
})();