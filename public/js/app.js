const API='/api';
const THRESHOLD=12; // harus sama dengan DELAY_THRESHOLD_HOURS di ExceptionController
const NEXT={CREATED:'BOOKED',BOOKED:'PICKED_UP',PICKED_UP:'IN_TRANSIT',IN_TRANSIT:'DELIVERED'};
const TYPES=['CUSTOMS_HOLD','WEATHER_DELAY','PORT_CONGESTION','DAMAGED','MISROUTED','DOCUMENTATION_ISSUE'];
let token=localStorage.getItem('ct_token');
let me=null,locs=[],page=1,selectedId=null,map,layer;
let chStatus=null,chDelay=null;
const $=id=>document.getElementById(id);
const esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const fmt=x=>x?new Date(x).toLocaleString():'-';
const num=x=>Number(x).toLocaleString('id-ID',{maximumFractionDigits:2});
const isStaff=()=>me&&['admin','operator'].includes(me.role);
const errText=e=>e.data&&e.data.errors?Object.values(e.data.errors).flat().join(' '):e.message;

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
  token=null;me=null;selectedId=null;
  localStorage.removeItem('ct_token');
  localStorage.removeItem('ct_user');
  $('detail').classList.add('hide');
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
  }catch(err){$('loginErr').textContent=errText(err);}
});

$('logoutBtn').addEventListener('click',async()=>{
  try{await api('/logout',{method:'POST'});}catch(e){}
  clearSession();
});

$('readAllBtn').addEventListener('click',async()=>{
  try{await api('/notifications/read-all',{method:'POST'});loadNotifs();}catch(e){}
});

$('fGo').addEventListener('click',()=>loadShipments(1));
$('shipRows').addEventListener('click',e=>{
  const tr=e.target.closest('tr[data-id]');
  if(tr)openDetail(tr.dataset.id);
});
$('pager').addEventListener('click',e=>{
  if(e.target.dataset.p)loadShipments(parseInt(e.target.dataset.p));
});

function start(){
  me=JSON.parse(localStorage.getItem('ct_user')||'null');
  if(!token||!me){clearSession();return;}
  $('login').classList.add('hide');
  $('app').classList.remove('hide');
  $('who').textContent=me.name+' ('+me.role+')';
  $('dashCard').classList.toggle('hide',!isStaff());
  if(isStaff()){loadDashboard();loadLocs();}
  loadShipments(1);loadRates();loadNews();loadNotifs();loadMap();
}

async function loadLocs(){
  try{locs=(await api('/locations')).data;}catch(e){}
}

async function loadDashboard(){
  try{
    const d=await api('/dashboard');
    const items=[['Total',d.total_shipments],...Object.entries(d.shipments_by_status),['Delayed',d.delayed_shipments],['Open exceptions',d.open_exceptions]];
    $('stats').innerHTML=items.map(([k,v])=>`<div class="stat"><b>${esc(v)}</b>${esc(k.replace('_',' '))}</div>`).join('');
    drawCharts(d);
  }catch(e){$('stats').textContent=e.message;}
}

/* ---------- Charts (Chart.js) ---------- */
function drawCharts(d){
  if(typeof Chart==='undefined')return; // CDN gagal: kartu angka tetap tampil
  if(chStatus)chStatus.destroy();
  if(chDelay)chDelay.destroy();

  const st=d.shipments_by_status||{};
  chStatus=new Chart($('chStatus'),{
    type:'bar',
    data:{labels:Object.keys(st).map(k=>k.replace('_',' ')),datasets:[{data:Object.values(st),backgroundColor:'#12355b'}]},
    options:{maintainAspectRatio:false,plugins:{legend:{display:false},title:{display:true,text:'Shipments by status'}},scales:{y:{beginAtZero:true,ticks:{precision:0}}}}
  });

  const total=d.total_shipments||0,delayed=d.delayed_shipments||0;
  const rate=total?(delayed/total*100).toFixed(1):'0.0';
  chDelay=new Chart($('chDelay'),{
    type:'doughnut',
    data:{labels:['Not delayed','Delayed'],datasets:[{data:[Math.max(0,total-delayed),delayed],backgroundColor:['#2f855a','#a61b1b']}]},
    options:{maintainAspectRatio:false,plugins:{title:{display:true,text:'Delay rate: '+rate+'% | Open exceptions: '+(d.open_exceptions||0)}}}
  });
}

/* ---------- Weather (Open-Meteo) ---------- */
async function loadWeather(lat,lon){
  const el=$('wx');
  if(!el)return;
  try{
    const d=await api('/weather?lat='+encodeURIComponent(lat)+'&lon='+encodeURIComponent(lon));
    el.innerHTML=`<b>${esc(d.temperature_c)}&deg;C</b> &middot; wind ${esc(d.wind_kmh)} km/h &middot; rain ${esc(d.precipitation_mm)} mm<br><small>${esc(d.source)} &middot; observed ${esc(d.observed_at)} UTC${d.stale?' &middot; cached (service unavailable)':''}. Informational only, not a delay prediction.</small>`;
  }catch(e){el.textContent='Weather unavailable.';}
}

async function loadShipments(p){
  if(p)page=p;
  const q=new URLSearchParams({page});
  if($('fSearch').value.trim())q.set('search',$('fSearch').value.trim());
  if($('fStatus').value)q.set('status',$('fStatus').value);
  if($('fDelay').value!=='')q.set('is_delayed',$('fDelay').value);
  try{
    const d=await api('/shipments?'+q);
    $('shipRows').innerHTML=d.data.length?d.data.map(s=>`<tr data-id="${s.id}"><td>${esc(s.shipment_number)}</td><td>${esc(s.carrier&&s.carrier.name)}</td><td>${esc(s.origin&&s.origin.name)} &rarr; ${esc(s.destination&&s.destination.name)}</td><td><span class="badge">${esc(s.status)}</span>${s.is_delayed?' <span class="badge late">DELAYED</span>':''}</td><td>${esc(fmt(s.estimated_arrival||s.expected_arrival))}</td></tr>`).join(''):'<tr><td colspan="5">No shipments found.</td></tr>';
    $('pager').innerHTML=`<button data-p="${d.current_page-1}" ${d.current_page<=1?'disabled':''}>Prev</button><span>Page ${d.current_page} of ${d.last_page}</span><button data-p="${d.current_page+1}" ${d.current_page>=d.last_page?'disabled':''}>Next</button>`;
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

/* ---------- Map ---------- */
function initMap(){
  if(typeof L==='undefined'){$('map').textContent='Map library failed to load.';return false;}
  if(!map){
    map=L.map('map').setView([5,80],3);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{maxZoom:18,attribution:'&copy; OpenStreetMap contributors'}).addTo(map);
    layer=L.layerGroup().addTo(map);
  }
  map.invalidateSize();
  layer.clearLayers();
  return true;
}
const dot=(p,color,r,html)=>L.circleMarker(p,{radius:r,color,fillOpacity:0.8}).bindPopup(html).addTo(layer);

async function loadMap(){
  if(!initMap())return;
  try{
    const d=await api('/shipments');
    const bounds=[];
    for(const s of d.data.slice(0,10)){
      const o=s.origin,t=s.destination;
      if(!o||!t)continue;
      const a=[o.latitude,o.longitude],b=[t.latitude,t.longitude];
      L.polyline([a,b],{color:'#12355b',weight:2,dashArray:'6 6'}).addTo(layer);
      dot(a,'#2f855a',5,'Origin: '+esc(o.name));
      dot(b,'#a61b1b',5,'Destination: '+esc(t.name));
      bounds.push(a,b);
      try{
        const ev=await api('/shipments/'+s.id+'/events');
        const last=[...ev].reverse().find(e=>e.latitude!=null&&e.longitude!=null);
        if(last){
          const p=[last.latitude,last.longitude];
          dot(p,'#d69e2e',8,'<b>'+esc(s.shipment_number)+'</b><br>'+esc(last.event_type)+'<br>'+esc(fmt(last.occurred_at)));
          bounds.push(p);
        }
      }catch(e){}
    }
    if(bounds.length)map.fitBounds(bounds,{padding:[30,30]});
  }catch(e){$('map').insertAdjacentHTML('beforeend','<p class="err">'+esc(e.message)+'</p>');}
}

function drawDetailMap(s,ev){
  if(!initMap())return;
  const bounds=[];
  if(s.origin){const p=[s.origin.latitude,s.origin.longitude];dot(p,'#2f855a',6,'Origin: '+esc(s.origin.name));bounds.push(p);}
  if(s.destination){const p=[s.destination.latitude,s.destination.longitude];dot(p,'#a61b1b',6,'Destination: '+esc(s.destination.name));bounds.push(p);}
  const pts=ev.filter(e=>e.latitude!=null&&e.longitude!=null);
  if(pts.length>1)L.polyline(pts.map(e=>[e.latitude,e.longitude]),{color:'#d69e2e',weight:3}).addTo(layer);
  pts.forEach((e,i)=>{
    const p=[e.latitude,e.longitude];
    dot(p,e.is_exception?'#a61b1b':'#d69e2e',i===pts.length-1?9:5,'<b>'+esc(e.event_type)+'</b><br>'+esc(fmt(e.occurred_at))+'<br>'+esc(e.location?e.location.name:''));
    bounds.push(p);
  });
  if(bounds.length)map.fitBounds(bounds,{padding:[30,30],maxZoom:6});
}

/* ---------- Shipment detail ---------- */
async function openDetail(id,quiet){
  selectedId=id;
  const box=$('detail');
  box.classList.remove('hide');
  if(!quiet){box.innerHTML='Loading...';$('mapCard').scrollIntoView({behavior:'smooth'});}
  try{
    const [s,ev]=await Promise.all([api('/shipments/'+id),api('/shipments/'+id+'/events')]);
    box.innerHTML=renderDetail(s,ev);
    drawDetailMap(s,ev);
    const lp=[...ev].reverse().find(e=>e.latitude!=null&&e.longitude!=null);
    if(lp)loadWeather(lp.latitude,lp.longitude);
  }catch(e){box.innerHTML='<p class="err">'+esc(e.message)+'</p>';}
}

function renderDetail(s,ev){
  const exp=new Date(s.expected_arrival);
  const est=s.estimated_arrival?new Date(s.estimated_arrival):null;
  const dh=est?Math.max(0,(est-exp)/36e5):0;
  const sev=dh>24?'HIGH':dh>6?'MEDIUM':'LOW';
  const last=[...ev].reverse().find(e=>e.latitude!=null&&e.longitude!=null);
  const staff=isStaff();

  const timeline=ev.length?ev.map(e=>`<li class="${e.is_exception?'ex':''}"><b>${esc(e.event_type)}</b> <small>${esc(fmt(e.occurred_at))}</small><br>${esc(e.location?e.location.name:'')} ${esc(e.description||'')}</li>`).join(''):'<li>No events yet.</li>';

  const excs=(s.exceptions||[]).map(x=>{
    const to=x.status==='OPEN'?'INVESTIGATING':'RESOLVED';
    const btn=staff&&x.status!=='RESOLVED'?` <button class="sm" data-act="exc" data-id="${x.id}" data-to="${to}">${x.status==='OPEN'?'Investigate':'Resolve'}</button>`:'';
    return `<li><span class="badge ${x.status==='RESOLVED'?'':'late'}">${esc(x.type)}</span> ${esc(x.severity)} &middot; ${esc(x.status)} <small>detected ${esc(fmt(x.detected_at))}${x.resolved_at?' &middot; resolved '+esc(fmt(x.resolved_at)):''}</small>${x.notes?'<br>'+esc(x.notes):''}${btn}</li>`;
  }).join('')||'<li>None.</li>';

  const items=(s.items||[]).map(i=>`<li>${esc(i.description)} &times; ${esc(i.quantity)} (${esc(i.weight_kg)} kg)</li>`).join('')||'<li>None.</li>';

  let actions='';
  if(staff){
    const lo='<option value="">Location (optional)</option>'+locs.map(l=>`<option value="${l.id}">${esc(l.name)}</option>`).join('');
    const nxt=NEXT[s.status];
    actions=`<div class="act"><h3>Update status</h3>${nxt?`<select id="stLoc">${lo}</select> <input id="stDesc" placeholder="Description"> <button data-act="status" data-to="${nxt}">Set ${nxt}</button>`:'<small>Delivered: final status.</small>'}</div>
    <div class="act"><h3>Add tracking event</h3><input id="evType" placeholder="Event type, e.g. DEPARTED_PORT"> <select id="evLoc">${lo}</select> <input id="evDesc" placeholder="Description"> <button data-act="event">Add event</button></div>
    <div class="act"><h3>Report exception</h3><select id="exType">${TYPES.map(t=>`<option>${t}</option>`).join('')}</select> <input id="exHours" type="number" min="0" value="0" title="Delay hours" style="width:110px"> <select id="exSev"><option>LOW</option><option>MEDIUM</option><option>HIGH</option></select> <input id="exNotes" placeholder="Notes"> <button data-act="exception">Add exception</button><br><small>Delay hours shift the ETA. Severity rule: 0-6 h LOW, over 6-24 h MEDIUM, over 24 h HIGH.</small></div>
    <p id="actMsg" class="err"></p>`;
  }

  return `<div style="display:flex;justify-content:space-between;gap:8px;flex-wrap:wrap"><h2>${esc(s.shipment_number)} <span class="badge">${esc(s.status)}</span>${s.is_delayed?' <span class="badge late">DELAYED</span>':''}</h2><button class="sm" data-act="all">Close / show all on map</button></div>
    <p>${esc(s.carrier&&s.carrier.name)} &middot; ${esc(s.origin&&s.origin.name)} &rarr; ${esc(s.destination&&s.destination.name)}${s.customer?' &middot; Customer: '+esc(s.customer.name):''}<br>${esc(s.cargo_type)}</p>
    <div class="sec"><h3>ETA and delay</h3>Expected: ${esc(fmt(exp))} &middot; Estimated: ${est?esc(fmt(est)):'-'}<br>Delay: <b>${dh.toFixed(1)} h</b> (threshold ${THRESHOLD} h) &rarr; ${s.is_delayed?'<span class="err">DELAYED, severity '+sev+'</span>':'on schedule'}</div>
    <div class="sec"><h3>Current location</h3>${last?esc(last.location?last.location.name:last.latitude+', '+last.longitude)+' <small>as of '+esc(fmt(last.occurred_at))+'</small>':'No location yet.'}</div>
    ${last?'<div class="sec"><h3>Weather at current location</h3><div id="wx">Loading...</div></div>':''}
    <div class="sec"><h3>Timeline</h3><ul>${timeline}</ul></div>
    <div class="sec"><h3>Exceptions</h3><ul>${excs}</ul></div>
    <div class="sec"><h3>Items</h3><ul>${items}</ul></div>
    ${actions}`;
}

$('detail').addEventListener('input',e=>{
  if(e.target.id==='exHours'){
    const h=parseInt(e.target.value)||0;
    $('exSev').value=h>24?'HIGH':h>6?'MEDIUM':'LOW';
  }
});

$('detail').addEventListener('click',async e=>{
  const b=e.target.closest('[data-act]');
  if(!b)return;
  const act=b.dataset.act,id=selectedId,v=k=>$(k)?$(k).value:'';
  const post=(path,body,method='POST')=>api(path,{method,body:JSON.stringify(body)});
  let call;
  if(act==='all'){selectedId=null;$('detail').classList.add('hide');loadMap();return;}
  if(act==='status')call=post('/shipments/'+id+'/status',{status:b.dataset.to,location_id:v('stLoc')||null,description:v('stDesc')||null});
  else if(act==='event')call=post('/shipments/'+id+'/events',{event_type:v('evType').trim(),location_id:v('evLoc')||null,description:v('evDesc')||null,occurred_at:new Date(Date.now()-5000).toISOString()});
  else if(act==='exception')call=post('/shipments/'+id+'/exceptions',{type:v('exType'),severity:v('exSev'),notes:v('exNotes')||null,delay_hours:parseInt(v('exHours'))||0});
  else if(act==='exc')call=post('/exceptions/'+b.dataset.id+'/status',{status:b.dataset.to},'PATCH');
  if(!call)return;
  try{
    await call;
    loadShipments();loadNotifs();
    if(isStaff())loadDashboard();
    openDetail(id,true);
  }catch(err){
    if($('actMsg'))$('actMsg').textContent=errText(err);
  }
});

setInterval(()=>{if(token){loadRates();loadNews();}},300000);
start();