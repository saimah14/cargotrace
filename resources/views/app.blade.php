<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CargoTrace</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>
  body{font-family:system-ui,Arial,sans-serif;margin:0;background:#f4f6f8;color:#1f2933}
  header{background:#12355b;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
  main{max-width:1100px;margin:20px auto;padding:0 16px}
  .card{background:#fff;border-radius:10px;padding:16px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,.1)}
  .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px}
  .stat{background:#f4f6f8;border-radius:8px;padding:12px;font-size:13px}
  .stat b{display:block;font-size:26px}
  .charts{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:14px}
  .charts>div{position:relative;height:220px}
  .tbl{overflow-x:auto}
  table{width:100%;border-collapse:collapse;font-size:14px}
  th,td{text-align:left;padding:8px;border-bottom:1px solid #e4e7eb;white-space:nowrap}
  tr[data-id]{cursor:pointer}
  tr[data-id]:hover{background:#f4f6f8}
  .badge{padding:2px 8px;border-radius:99px;font-size:12px;background:#e4e7eb}
  .late{background:#fde2e2;color:#a61b1b}
  input,select,button{padding:10px;font-size:15px;border-radius:6px;border:1px solid #cbd2d9}
  button{background:#12355b;color:#fff;border:0;cursor:pointer}
  button:disabled{opacity:.5}
  button.sm{padding:3px 8px;font-size:12px}
  #login{max-width:360px;margin:60px auto}
  #login input{width:100%;box-sizing:border-box;margin-bottom:10px}
  #map{height:380px;border-radius:8px}
  .bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:10px}
  .act{border-top:1px solid #e4e7eb;padding-top:10px;margin-top:12px}
  .act h3,.sec h3{font-size:14px;margin:0 0 6px}
  .sec{margin-top:12px}
  .ex{color:#a61b1b}
  h1,h2{margin:0 0 10px}
  h2{font-size:17px}
  ul{padding-left:18px;margin:0}
  li{margin-bottom:6px}
  .unread{font-weight:600}
  .hide{display:none}
  .err{color:#a61b1b;font-size:14px}
</style>
</head>
<body>

<div id="login" class="card">
  <h1>CargoTrace</h1>
  <p>Log in to continue.</p>
  <form id="loginForm">
    <input id="email" type="email" placeholder="Email" required>
    <input id="password" type="password" placeholder="Password" required>
    <button type="submit">Log in</button>
    <p id="loginErr" class="err"></p>
  </form>
</div>

<div id="app" class="hide">
  <header>
    <strong>CargoTrace</strong>
    <span><span id="who"></span> <button id="logoutBtn">Log out</button></span>
  </header>
  <main>
    <div id="mapCard" class="card">
      <h2>Shipment map</h2>
      <div id="map"></div>
    </div>

    <div id="detail" class="card hide"></div>

    <div id="dashCard" class="card hide">
      <h2>Dashboard</h2>
      <div id="stats" class="grid"></div>
      <div class="charts">
        <div><canvas id="chStatus"></canvas></div>
        <div><canvas id="chDelay"></canvas></div>
        <div><canvas id="chCarrier"></canvas></div>
      </div>
    </div>

    <div class="card">
      <h2>Live exchange rates</h2>
      <div id="rates">Loading...</div>
    </div>

    <div class="card">
      <h2>Shipments <small>(click a row for details)</small></h2>
      <div class="bar">
        <input id="fSearch" placeholder="Search number">
        <select id="fStatus">
          <option value="">All status</option>
          <option>CREATED</option><option>BOOKED</option><option>PICKED_UP</option><option>IN_TRANSIT</option><option>DELIVERED</option>
        </select>
        <select id="fDelay">
          <option value="">Any delay</option>
          <option value="1">Delayed</option>
          <option value="0">Not delayed</option>
        </select>
        <button id="fGo">Filter</button>
      </div>
      <div class="tbl">
        <table>
          <thead><tr><th>Number</th><th>Carrier</th><th>Route</th><th>Status</th><th>ETA</th></tr></thead>
          <tbody id="shipRows"><tr><td colspan="5">Loading...</td></tr></tbody>
        </table>
      </div>
      <div id="pager" class="bar" style="margin-top:10px"></div>
    </div>

    <div class="card">
      <h2>Notifications (<span id="unread">0</span> unread) <button id="readAllBtn">Mark all read</button></h2>
      <ul id="notifs"></ul>
    </div>

    <div id="auditCard" class="card hide">
      <h2>Audit log <small>(admin only)</small></h2>
      <div class="tbl">
        <table>
          <thead><tr><th>Time</th><th>User</th><th>Action</th><th>Entity</th><th>Changes</th></tr></thead>
          <tbody id="auditRows"><tr><td colspan="5">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <h2>Shipping news</h2>
      <ul id="news"><li>Loading...</li></ul>
    </div>
  </main>
</div>

<script src="/js/app.js"></script>
</body>
</html>