<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>CargoTrace</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<style>
  body{font-family:system-ui,Arial,sans-serif;margin:0;background:#f4f6f8;color:#1f2933}
  header{background:#12355b;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap}
  main{max-width:1100px;margin:20px auto;padding:0 16px}
  .card{background:#fff;border-radius:10px;padding:16px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,.1)}
  .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:12px}
  .stat{background:#f4f6f8;border-radius:8px;padding:12px;font-size:13px}
  .stat b{display:block;font-size:26px}
  .tbl{overflow-x:auto}
  table{width:100%;border-collapse:collapse;font-size:14px}
  th,td{text-align:left;padding:8px;border-bottom:1px solid #e4e7eb;white-space:nowrap}
  .badge{padding:2px 8px;border-radius:99px;font-size:12px;background:#e4e7eb}
  .late{background:#fde2e2;color:#a61b1b}
  input,button{padding:10px;font-size:15px;border-radius:6px;border:1px solid #cbd2d9}
  button{background:#12355b;color:#fff;border:0;cursor:pointer}
  #login{max-width:360px;margin:60px auto}
  #login input{width:100%;box-sizing:border-box;margin-bottom:10px}
  #map{height:380px;border-radius:8px}
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
    <div class="card">
      <h2>Shipment map</h2>
      <div id="map"></div>
    </div>

    <div id="dashCard" class="card hide">
      <h2>Dashboard</h2>
      <div id="stats" class="grid"></div>
    </div>

    <div class="card">
      <h2>Live exchange rates</h2>
      <div id="rates">Loading...</div>
    </div>

    <div class="card">
      <h2>Shipments</h2>
      <div class="tbl">
        <table>
          <thead><tr><th>Number</th><th>Carrier</th><th>Route</th><th>Status</th><th>ETA</th></tr></thead>
          <tbody id="shipRows"><tr><td colspan="5">Loading...</td></tr></tbody>
        </table>
      </div>
    </div>

    <div class="card">
      <h2>Notifications (<span id="unread">0</span> unread) <button id="readAllBtn">Mark all read</button></h2>
      <ul id="notifs"></ul>
    </div>

    <div class="card">
      <h2>Shipping news</h2>
      <ul id="news"><li>Loading...</li></ul>
    </div>
  </main>
</div>

@verbatim
<script>
const API='/api';
let token=localStorage.getItem('ct_token');
const $=id=>document.getElementById(id);
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const fmt=x=>x?new Date(x).toLocaleString():'-';
const num=x=>Number(x).toLocaleString('id-ID',{maximumFractionDigits:2});

async function api(path,opts={}){
  const headers={'Accept':'application/json','Content-Type':'application/json'};
  if(token)headers.Authorization='Bearer '+token;
  const res=await fetch(API+path,{...opts,headers});
  const data=await res.json().catch(()=>({}));
  if(res.status===401&&path!=='/login'){clearSession();throw new Error('Session expired, please log in again.');}
  if(!res.ok)throw Object.assign(new Error(data.message||'Request failed'),{data});
  return data;
}

function clearSession(){
  token=null;
  localStorage.removeItem('ct_token');
  localStorage.removeItem('ct_user');
  $('app').classList.add('hide');
  $('login').classList.remove('hide');
}

$('loginForm').addEventListener('submit',async e=>{
  e.preventDefault();
  $('loginErr').textContent='';
  try{
    const d=await api('/login',{method:'POST',body:JSON.stringify({email:$('email').value,password:$('password').value})});
    token=d.token;
    localStorage.setItem('ct_token',token);
    localStorage.setItem('ct_user',JSON.stringify(d.user));
    start();
  }catch(err){
    $('loginErr').textContent=err.data&&err.data.errors?Object.values(err.data.errors).flat().join(' '):err.message;
  }
});

$('logoutBtn').addEventListener('click',async()=>{
  try{await api('/logout',{method:'POST'});}catch(e){}
  clearSession();
});

$('readAllBtn').addEventListener('click',async()=>{
  try{await api('/notifications/read-all',{method:'POST'});loadNotifs();}catch(e){}
});

function start(){
  const user=JSON.parse(localStorage.getItem('ct_user')||'null');
  if(!token||!user){clearSession();return;}
  $('login').classList.add('hide');
  $('app').classList.remove('hide');
  $('who').textContent=user.name+' ('+user.role+')';
  const staff=['admin','operator'].includes(user.role);
  $('dashCard').classList.toggle('hide',!staff);
  if(staff)loadDashboard();
  loadShipments();loadRates();loadNews();loadNotifs();
}

async function loadDashboard(){
  try{
    const d=await api('/dashboard');
    const items=[['Total',d.total_shipments],...Object.entries(d.shipments_by_status),['Delayed',d.delayed_shipments],['Open exceptions',d.open_exceptions]];
    $('stats').innerHTML=items.map(([k,v])=>`<div class="stat"><b>${esc(v)}</b>${esc(k.replace('_',' '))}</div>`).join('');
  }catch(e){$('stats').textContent=e.message;}
}

async function loadShipments(){
  try{
    const d=await api('/shipments');
    if(!d.data.length){$('shipRows').innerHTML='<tr><td colspan="5">No shipments yet.</td></tr>';return;}
    $('shipRows').innerHTML=d.data.map(s=>`<tr><td>${esc(s.shipment_number)}</td><td>${esc(s.carrier&&s.carrier.name)}</td><td>${esc(s.origin&&s.origin.name)} &rarr; ${esc(s.destination&&s.destination.name)}</td><td><span class="badge">${esc(s.status)}</span>${s.is_delayed?' <span class="badge late">DELAYED</span>':''}</td><td>${esc(fmt(s.estimated_arrival||s.expected_arrival))}</td></tr>`).join('');
  }catch(e){$('shipRows').innerHTML='<tr><td colspan="5" class="err">'+esc(e.message)+'</td></tr>';}
}

async function loadRates(){
  try{
    const d=await api('/rates');
    $('rates').innerHTML=`1 USD = <b>${num(d.rates.IDR)}</b> IDR &middot; 1 EUR = <b>${num(d.idr_per_eur)}</b> IDR<br><small>${esc(d.source)} &middot; rates date ${esc(d.date)}${d.stale?' &middot; <span class="err">cached (service unavailable)</span>':''}</small>`;
  }catch(e){$('rates').textContent=e.message;}
}

async function loadNews(){
  try{
    const d=await api('/news');
    $('news').innerHTML=d.items.map(n=>{
      const safe=/^https?:\/\//.test(n.link)?n.link:'#';
      return `<li><a href="${esc(safe)}" target="_blank" rel="noopener noreferrer">${esc(n.title)}</a> <small>${esc(fmt(n.published_at))}</small></li>`;
    }).join('')+(d.stale?'<li class="err">Showing cached news (service unavailable)</li>':'');
  }catch(e){$('news').innerHTML='<li class="err">'+esc(e.message)+'</li>';}
}

async function loadNotifs(){
  try{
    const d=await api('/notifications');
    $('unread').textContent=d.unread_count;
    const list=d.notifications.data;
    $('notifs').innerHTML=list.length?list.map(n=>`<li class="${n.read_at?'':'unread'}">${esc(n.message)} <small>${esc(fmt(n.created_at))}</small></li>`).join(''):'<li>No notifications.</li>';
  }catch(e){$('notifs').innerHTML='<li class="err">'+esc(e.message)+'</li>';}
}

let map, layer;

async function loadMap(){
  if(typeof L==='undefined'){$('map').textContent='Map library failed to load.';return;}
  if(!map){
    map=L.map('map').setView([5,80],3);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:18,attribution:'&copy; OpenStreetMap contributors'}).addTo(map);
    layer=L.layerGroup().addTo(map);
  }
  map.invalidateSize();
  layer.clearLayers();
  try{
    const d=await api('/shipments');
    const bounds=[];
    for(const s of d.data.slice(0,10)){
      const o=s.origin,t=s.destination;
      if(!o||!t)continue;
      const a=[o.latitude,o.longitude],b=[t.latitude,t.longitude];
      L.polyline([a,b],{color:'#12355b',weight:2,dashArray:'6 6'}).addTo(layer);
      L.circleMarker(a,{radius:5,color:'#2f855a'}).bindPopup('Origin: '+esc(o.name)).addTo(layer);
      L.circleMarker(b,{radius:5,color:'#a61b1b'}).bindPopup('Destination: '+esc(t.name)).addTo(layer);
      bounds.push(a,b);
      try{
        const ev=await api('/shipments/'+s.id+'/events');
        const last=[...ev].reverse().find(e=>e.latitude!=null&&e.longitude!=null);
        if(last){
          const p=[last.latitude,last.longitude];
          L.circleMarker(p,{radius:8,color:'#d69e2e',fillOpacity:0.9}).bindPopup('<b>'+esc(s.shipment_number)+'</b><br>'+esc(last.event_type)+'<br>'+esc(fmt(last.occurred_at))).addTo(layer);
          bounds.push(p);
        }
      }catch(e){}
    }
    if(bounds.length)map.fitBounds(bounds,{padding:[30,30]});
  }catch(e){$('map').insertAdjacentHTML('beforeend','<p class="err">'+esc(e.message)+'</p>');}
}

const _start=start;
start=function(){_start();loadMap();};

setInterval(()=>{if(token){loadRates();loadNews();}},300000);
start();
</script>
@endverbatim

</body>
</html>