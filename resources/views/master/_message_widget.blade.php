{{-- Include tepat SEBELUM <li class="nav-item"> yang memuat id="openProfileDrawer" --}}
<li class="nav-item" id="nw-message-nav" style="position:relative;display:flex;align-items:center;margin-right:14px">
    <button type="button" id="nw-message-toggle" aria-label="Buka pesan" title="Pesan" style="position:relative;background:none;border:0;cursor:pointer;padding:8px;color:#344054">
        <i class="material-icons" style="font-size:25px;vertical-align:middle">chat_bubble_outline</i>
        <span id="nw-message-badge" style="display:none;position:absolute;top:0;right:0;background:#dc3545;color:white;border-radius:20px;font-size:10px;padding:1px 5px">0</span>
    </button>
</li>
<div id="nw-message-backdrop" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.3);z-index:10040"></div>
<aside id="nw-message-panel" role="dialog" aria-label="Pesan NewWicker" style="position:fixed;right:0;top:0;bottom:0;width:min(420px,100vw);background:white;z-index:10041;box-shadow:-5px 0 30px #0002;transform:translateX(105%);transition:transform .25s;display:flex;flex-direction:column;font-family:inherit">
    <header style="padding:16px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center">
        <div><strong style="font-size:18px">Pesan</strong><div style="font-size:12px;color:#667085">NewWicker Internal Messages</div></div>
        <button type="button" id="nw-message-close" aria-label="Tutup pesan" style="border:0;background:#f2f4f7;border-radius:8px;padding:6px 10px;cursor:pointer">✕</button>
    </header>
    <div style="padding:12px;border-bottom:1px solid #eee"><input id="nw-message-search" type="search" placeholder="Cari nama karyawan..." aria-label="Cari pengguna" style="width:100%;padding:10px;border:1px solid #d0d5dd;border-radius:9px"></div>
    <div id="nw-message-users" style="overflow:auto;flex:1;padding:8px"><div style="color:#667085;padding:16px;text-align:center">Memuat percakapan...</div></div>
    <section id="nw-message-chat" style="display:none;flex:1;min-height:0;flex-direction:column">
        <div style="padding:12px;border-bottom:1px solid #eee;display:flex;align-items:center;gap:10px"><button type="button" id="nw-message-back" style="border:0;background:none;cursor:pointer;font-size:20px">←</button><strong id="nw-message-recipient"></strong></div>
        <div id="nw-message-history" aria-live="polite" style="flex:1;overflow:auto;padding:14px;display:flex;flex-direction:column;gap:8px"></div>
        <form id="nw-message-form" style="padding:12px;display:flex;gap:8px;border-top:1px solid #eee">
            <input id="nw-message-text" maxlength="2000" required autocomplete="off" placeholder="Tulis pesan..." style="min-width:0;flex:1;padding:10px;border:1px solid #d0d5dd;border-radius:9px">
            <button type="submit" id="nw-message-send" style="background:#2563eb;color:#fff;border:0;border-radius:9px;padding:0 14px;cursor:pointer">Kirim</button>
        </form>
    </section>
</aside>
<style>
#nw-message-users button{width:100%;text-align:left;padding:12px;border:0;border-bottom:1px solid #f2f4f7;background:white;cursor:pointer}
#nw-message-users button:hover{background:#f8fafc}
.nw-chat-bubble{max-width:83%;padding:9px 12px;border-radius:12px;overflow-wrap:anywhere;white-space:pre-wrap;font-size:13px}
.nw-chat-mine{align-self:flex-end;background:#dbeafe;color:#1e3a8a}
.nw-chat-theirs{align-self:flex-start;background:#f2f4f7;color:#344054}
</style>
<script>
(function(){
  'use strict';
  if(window.__nwMessagesInitialized)return;
  window.__nwMessagesInitialized=true;
  const base=@json(url('/internal-messages'));
  const token=document.querySelector('meta[name="csrf-token"]')?.content||'';
  const $=id=>document.getElementById(id);
  let activeId=null,users=[],poll=null;
  const safe=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  async function api(path,opts={}){
    const res=await fetch(base+path,{credentials:'same-origin',headers:{'Accept':'application/json','Content-Type':'application/json','X-CSRF-TOKEN':token},...opts});
    const data=await res.json().catch(()=>({}));
    if(!res.ok)throw Error(data.message||'Gagal mengambil data ('+res.status+')');
    return data;
  }
  function error(container,e){container.textContent=e.message||'Gagal memuat data';container.style.color='#b42318'}
  async function refreshBadge(){try{const d=await api('/unread');const n=Number(d.count)||0;$('nw-message-badge').textContent=n>99?'99+':n;$('nw-message-badge').style.display=n?'block':'none'}catch(e){console.warn('Message unread:',e.message)}}
  async function loadUsers(){
    try{const d=await api('/users');users=d.users||[];renderUsers();}catch(e){error($('nw-message-users'),e)}
  }
  function renderUsers(){
    const term=$('nw-message-search').value.toLowerCase();const root=$('nw-message-users');root.innerHTML='';
    const matches=users.filter(u=>String(u.name||'').toLowerCase().includes(term));
    if(!matches.length){root.textContent='Tidak ada pengguna yang ditemukan.';return}
    matches.forEach(u=>{const b=document.createElement('button');b.type='button';b.innerHTML='<strong>'+safe(u.name)+'</strong>'+(u.unread?'<span style="float:right;background:#dc3545;color:white;border-radius:15px;padding:0 6px">'+Number(u.unread)+'</span>':'');b.addEventListener('click',()=>openChat(u));root.appendChild(b)})
  }
  async function openChat(u){activeId=u.id;$('nw-message-recipient').textContent=u.name;$('nw-message-users').style.display='none';$('nw-message-chat').style.display='flex';await loadChat()}
  async function loadChat(){if(!activeId)return;try{
    const d=await api('/conversation/'+encodeURIComponent(activeId));const root=$('nw-message-history');root.innerHTML='';
    (d.messages||[]).forEach(m=>{const div=document.createElement('div');div.className='nw-chat-bubble '+(m.mine?'nw-chat-mine':'nw-chat-theirs');div.textContent=m.body;root.appendChild(div)});
    root.scrollTop=root.scrollHeight;refreshBadge();
  }catch(e){error($('nw-message-history'),e)}}
  function back(){activeId=null;$('nw-message-chat').style.display='none';$('nw-message-users').style.display='block';loadUsers()}
  function close(){ $('nw-message-panel').style.transform='translateX(105%)';$('nw-message-backdrop').style.display='none';clearInterval(poll);poll=null }
  function open(){ $('nw-message-backdrop').style.display='block';$('nw-message-panel').style.transform='translateX(0)';loadUsers();refreshBadge();if(!poll)poll=setInterval(()=>{activeId?loadChat():loadUsers();refreshBadge()},15000) }
  $('nw-message-toggle').addEventListener('click',open);
  $('nw-message-close').addEventListener('click',close);
  $('nw-message-backdrop').addEventListener('click',close);
  $('nw-message-back').addEventListener('click',back);
  $('nw-message-search').addEventListener('input',renderUsers);
  $('nw-message-form').addEventListener('submit',async e=>{e.preventDefault();const input=$('nw-message-text');const body=input.value.trim();if(!body||!activeId)return;const btn=$('nw-message-send');btn.disabled=true;try{await api('/send',{method:'POST',body:JSON.stringify({recipient_id:activeId,body})});input.value='';await loadChat()}catch(err){alert(err.message)}finally{btn.disabled=false}});
  document.addEventListener('keydown',e=>{if(e.key==='Escape')close()});
  refreshBadge();
})();
</script>
