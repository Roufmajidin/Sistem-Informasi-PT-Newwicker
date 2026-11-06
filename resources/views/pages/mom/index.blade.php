@extends('master.master')

@section('content')

<div class="mom-page" id="momApp" data-store-url="{{ route('mom.autosave') }}" data-mom-id="{{ $mom->id ?? '' }}" data-csrf="{{ csrf_token() }}" data-ping-url="{{ route('mom.ping') }}">

    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:14px"><strong id="momRecordId">MoM #{{ $mom->id ?? 'Baru' }}</strong><span id="momNetworkStatus" role="status" aria-live="polite">Memeriksa koneksi...</span><span id="momSaveStatus" role="status" aria-live="polite">Belum ada perubahan</span></div>

    {{-- Navigation tabs: separate routes preserve existing autosave and edit logic --}}

    <nav class="mom-nav-tabs" aria-label="Navigasi Minutes of Meeting">

        <a class="mom-nav-tab is-active" href="{{ $mom ? route('mom.edit', $mom) : route('mom.index') }}" aria-current="page">

            <i class="fa fa-edit" aria-hidden="true"></i> {{ $mom ? 'Edit MoM' : 'Form MoM' }}

        </a>

        <a class="mom-nav-tab" href="{{ route('mom.list') }}" id="momListTab">

            <i class="fa fa-list-alt" aria-hidden="true"></i> Daftar MoM

        </a>

    </nav>

    <div class="mom-topbar">

        <div class="mom-heading">

            <div><h5>Minutes of Meeting</h5><p></p></div>

        </div>

        <div class="mom-top-actions">

            <button type="button" class="mom-btn mom-btn-outline" id="momPreviewTop"><i class="fa fa-eye"></i> Preview</button>

            <button type="button" class="mom-btn mom-btn-primary" id="momSaveTop"><i class="fa fa-floppy-o"></i> Save Draft</button>

            <button type="button" class="mom-btn mom-btn-dark" id="momCopyTop"><i class="fa fa-files-o"></i> Copy MoM</button>

        </div>

    </div>

    <div class="mom-section">

        <h2 class="mom-section-title">Meeting Information</h2>

        <div class="mom-info-grid">

            <label class="mom-field"><span><i class="fa fa-calendar"></i> Day / Date</span><input type="date" id="momDate" class="mom-input" value="{{ date('Y-m-d') }}"></label>

            <label class="mom-field"><span><i class="fa fa-calendar-check-o"></i> Start Month</span><input type="month" id="momMonth" class="mom-input" value="{{ date('Y-m') }}"></label>

            <label class="mom-field"><span><i class="fa fa-file-text-o"></i> Subject</span><input type="text" id="momSubject" class="mom-input" placeholder="Example: Weekly Production Meeting"></label>

        </div>

    </div>

    <div class="mom-section">

        <div class="mom-section-head"><div><h2 class="mom-section-title">Attendees</h2><p>List of people who attended the meeting.</p></div><button type="button" class="mom-btn mom-btn-outline" id="momAddPerson"><i class="fa fa-plus"></i> Add Person</button></div>

        <div class="mom-table-scroll"><table class="mom-table mom-attendee-table"><thead><tr><th class="mom-no">No.</th><th>Name</th><th>Position</th><th class="mom-action">Action</th></tr></thead><tbody id="momAttendeesBody"></tbody></table></div>

    </div>

    <div class="mom-section">

        <div class="mom-section-head"><div><h2 class="mom-section-title">Subject / Description / Uraian</h2><p>Meeting discussion details.</p></div></div>

        <div class="mom-table-scroll"><table class="mom-table mom-discussion-table"><thead><tr><th class="mom-no">No.</th><th class="mom-person">Person / Position</th><th class="mom-description">Description / Uraian</th><th class="mom-notes">Notes</th><th class="mom-action">Action</th></tr></thead><tbody id="momDiscussionsBody"></tbody></table></div>

        <div class="mom-add-row"><button type="button" class="mom-btn mom-btn-outline" id="momAddDiscussion"><i class="fa fa-plus"></i> Add Row</button></div>

    </div>

    <div class="mom-section">

        <h2 class="mom-section-title">Recorded By</h2>

        <div class="mom-info-grid mom-record-grid">

            <label class="mom-field"><span><i class="fa fa-user-circle"></i> Recorded By (Login)</span><input type="text" id="momRecordedBy" class="mom-input" value="{{ auth()->user()->name ?? 'Guest' }}" readonly></label>

            <label class="mom-field"><span><i class="fa fa-briefcase"></i> Position / Department</span><select id="momRecordedPosition" class="mom-input"><option value="">-- Select Position --</option><option value="HRD">HRD</option><option value="Produksi">Produksi</option><option value="Marketing">Marketing</option><option value="Finance">Finance</option></select></label>

        </div>

    </div>

    <div class="mom-footer"><span><i class="fa fa-info-circle"></i> Pastikan semua data sudah sesuai sebelum menyimpan.</span><div class="mom-footer-actions"><button type="button" class="mom-btn mom-btn-primary" id="momSaveBottom"><i class="fa fa-floppy-o"></i> Save Draft</button><button type="button" class="mom-btn mom-btn-dark" id="momCopyBottom"><i class="fa fa-files-o"></i> Copy MoM</button></div></div>

</div>

<div class="mom-preview-overlay" id="momPreviewOverlay" aria-hidden="true"><div class="mom-preview-panel" role="dialog" aria-modal="true" aria-labelledby="momPreviewTitle"><div class="mom-preview-head"><h2 id="momPreviewTitle">Minutes of Meeting — Preview</h2><div><button type="button" class="mom-btn mom-btn-outline" id="momPrint"><i class="fa fa-print"></i> Print</button><button type="button" class="mom-btn mom-btn-dark" id="momClosePreview"><i class="fa fa-times"></i> Close</button></div></div><div id="momPreviewContent" class="mom-preview-content"></div></div></div>

<style>

    /* MoM navigation tabs */

    .mom-nav-tabs { display:flex; align-items:stretch; gap:5px; border-bottom:2px solid #d8e4f4; margin-bottom:20px; overflow-x:auto; }

    .mom-nav-tab { display:inline-flex; align-items:center; gap:9px; padding:12px 19px; text-decoration:none !important; white-space:nowrap; font-size:14px; font-weight:700; color:#536987 !important; border:1px solid transparent; border-bottom:3px solid transparent; border-radius:9px 9px 0 0; transition:background .15s,color .15s; }

    .mom-nav-tab:hover { color:#1559be !important; background:#eaf1fc; }

    .mom-nav-tab.is-active { background:#fff; color:#1258c5 !important; border-color:#d8e4f4; border-bottom-color:#1760d5; }

    @media(max-width:560px) { .mom-nav-tab { flex:1; justify-content:center; padding:11px 12px; } }

/* Scoped styles: will not change master.master or other pages. */

.mom-page{--mom-blue:#1760d5;--mom-navy:#112c53;--mom-border:#dce5f2;--mom-muted:#64748b;--mom-bg:#f3f6fc;font-family:inherit;color:#142747;padding:24px 26px 30px;background:var(--mom-bg);min-height:calc(100vh - 60px)}

.mom-page *,.mom-preview-overlay *{box-sizing:border-box}

.mom-topbar{display:flex;justify-content:space-between;align-items:center;gap:18px;margin-bottom:20px;flex-wrap:wrap}

.mom-heading{display:flex;align-items:center;gap:18px}.mom-heading-icon{width:76px;height:76px;background:#102e57;color:#fff;border-radius:12px;display:grid;place-items:center;font-size:32px}.mom-heading h1{font-size:27px;line-height:1.2;margin:0;font-weight:750;color:#102544}.mom-heading p{margin:4px 0 0;color:#263d60;font-size:15px}

.mom-top-actions,.mom-footer-actions{display:flex;gap:10px;flex-wrap:wrap}.mom-btn{border:1px solid transparent;border-radius:7px;min-height:38px;padding:9px 16px;font-size:13px;font-weight:650;display:inline-flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;white-space:nowrap;transition:background .15s,box-shadow .15s}.mom-btn:hover{filter:brightness(.96)}.mom-btn-outline{background:#fff;border-color:#a9c3f6;color:#1258c5}.mom-btn-primary{background:#1260d9;color:white}.mom-btn-dark{background:#102e62;color:#fff}

.mom-section{background:#fff;border:1px solid var(--mom-border);border-radius:11px;padding:16px;margin-bottom:14px;box-shadow:0 1px 4px rgba(15,35,75,.025)}.mom-section-title{border-left:4px solid #1760e4;padding-left:12px;font-size:17px;font-weight:750;color:#142747;margin:2px 0 17px}.mom-section-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}.mom-section-head .mom-section-title{margin-bottom:4px}.mom-section-head p{margin:0 0 0 16px;font-size:12px;color:var(--mom-muted)}

.mom-info-grid{display:grid;grid-template-columns:1fr 1fr 2fr;gap:18px}.mom-field{display:flex;flex-direction:column;gap:8px;min-width:0}.mom-field>span{font-size:13px;font-weight:650}.mom-field>span i{margin-right:6px;color:#213e69}.mom-input,.mom-table input,.mom-table select,.mom-table textarea{font:inherit;font-size:13px;width:100%;background:#fff;color:#193354;border:1px solid #cedbee;border-radius:6px;padding:9px 11px;outline:none;min-width:0}.mom-input:focus,.mom-table input:focus,.mom-table select:focus,.mom-table textarea:focus{border-color:#4385ee;box-shadow:0 0 0 2px rgba(67,133,238,.1)}.mom-table textarea{min-height:75px;resize:vertical;line-height:1.5}.mom-table-scroll{width:100%;overflow-x:auto;border:1px solid var(--mom-border);border-radius:6px}.mom-table{border-collapse:collapse;width:100%;table-layout:fixed;background:white}.mom-table th{background:#eaf0fa;font-weight:700;font-size:13px;text-align:left;padding:12px 13px;color:#1d3150;border-right:1px solid #dbe5f3}.mom-table td{padding:10px 12px;border-top:1px solid #e0e8f3;border-right:1px solid #e0e8f3;vertical-align:top;font-size:13px}.mom-table th:last-child,.mom-table td:last-child{border-right:0}.mom-table .mom-no{width:6%;text-align:center}.mom-table .mom-action{width:9%;text-align:center}.mom-table td:first-child{text-align:center;vertical-align:middle;font-weight:650}.mom-attendee-table{min-width:700px}.mom-attendee-table th:nth-child(2){width:35%}.mom-attendee-table th:nth-child(3){width:50%}.mom-discussion-table{min-width:1100px}.mom-discussion-table .mom-no{width:5%}.mom-discussion-table .mom-person{width:19%}.mom-discussion-table .mom-description{width:47%}.mom-discussion-table .mom-notes{width:21%}.mom-discussion-table .mom-action{width:8%}.mom-record-grid{grid-template-columns:1fr 1fr}.mom-record-grid .mom-field:last-child{grid-column:auto}.mom-record-grid input[readonly]{background:#f1f5f9;cursor:not-allowed}.mom-position-hint{display:block;margin-top:5px;color:#5d7292;font-size:11px}.mom-delete{background:#fff0f0;border:0;color:#e12b37;border-radius:5px;width:30px;height:30px;cursor:pointer}.mom-delete:hover{background:#ffe1e4}.mom-add-row{margin-top:12px}.mom-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:8px 0 0}.mom-footer>span{font-size:12px;color:#536987}.mom-footer>span i{font-size:16px;color:#2360b9;margin-right:6px}

.mom-preview-overlay{position:fixed;inset:0;z-index:99999;background:rgba(8,25,48,.68);display:none;padding:24px;overflow-y:auto}.mom-preview-overlay.is-open{display:block}.mom-preview-panel{max-width:1100px;margin:auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 20px 70px rgba(0,0,0,.3)}.mom-preview-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 22px;background:#f0f4fb;border-bottom:1px solid #dce5f2}.mom-preview-head h2{font-size:18px;margin:0}.mom-preview-head>div{display:flex;gap:8px}.mom-preview-content{padding:30px;color:#172b48}.mom-preview-content h1{text-align:center;font-size:23px;margin:0 0 3px}.mom-preview-content .mom-company{text-align:center;font-size:13px;margin-bottom:25px}.mom-preview-content h3{font-size:15px;margin:24px 0 10px}.mom-preview-content table{border-collapse:collapse;width:100%;font-size:12px;table-layout:fixed}
.mom-preview-content th,.mom-preview-content td{border:1px solid #cbd7e8;padding:10px;vertical-align:top;text-align:left;min-width:0;white-space:normal;overflow-wrap:anywhere;word-break:break-word}
.mom-preview-content th{background:#eaf0fa;color:#1d3150;font-weight:700}
.mom-preview-content ul{margin:0;padding-left:17px;max-width:100%}
.mom-preview-content li{margin:3px 0;white-space:normal;overflow-wrap:anywhere;word-break:break-word}
/* Fixed proportions keep long descriptions from forcing other columns narrower. */
.mom-preview-content table:nth-of-type(3) th:nth-child(1),.mom-preview-content table:nth-of-type(3) td:nth-child(1){width:5%}
.mom-preview-content table:nth-of-type(3) th:nth-child(2),.mom-preview-content table:nth-of-type(3) td:nth-child(2){width:17%}
.mom-preview-content table:nth-of-type(3) th:nth-child(3),.mom-preview-content table:nth-of-type(3) td:nth-child(3){width:58%}
.mom-preview-content table:nth-of-type(3) th:nth-child(4),.mom-preview-content table:nth-of-type(3) td:nth-child(4){width:20%}

@media(max-width:900px){.mom-info-grid{grid-template-columns:1fr 1fr}.mom-info-grid .mom-field:last-child{grid-column:1/-1}.mom-page{padding:18px 12px}.mom-heading-icon{width:55px;height:55px;font-size:24px}.mom-heading h1{font-size:22px}}@media(max-width:560px){.mom-info-grid{grid-template-columns:1fr}.mom-info-grid .mom-field:last-child{grid-column:auto}.mom-top-actions{width:100%}.mom-top-actions .mom-btn{flex:1}.mom-section{padding:12px}.mom-preview-overlay{padding:8px}.mom-preview-head{flex-wrap:wrap}.mom-preview-content{padding:15px}}

@page {
    size: A4 landscape;
    margin: 8mm;
}

@page {
    size: A4 landscape;
    margin: 8mm;
}

@media print {
    html,
    body {
        width: 100% !important;
        min-width: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Hide the application, but keep the preview document printable. */
    body * {
        visibility: hidden !important;
    }

    #momPreviewOverlay,
    #momPreviewOverlay * {
        visibility: visible !important;
    }

    #momPreviewOverlay {
        display: block !important;
        position: absolute !important;
        inset: 0 !important;
        width: 100% !important;
        height: auto !important;
        min-height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        background: #fff !important;
    }

    .mom-preview-panel {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        overflow: visible !important;
    }

    .mom-preview-head {
        display: none !important;
    }

    .mom-preview-content {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
        color: #172b48 !important;
    }

    .mom-preview-content table {
        width: 100% !important;
        table-layout: fixed !important;
        border-collapse: collapse !important;
        page-break-inside: auto;
    }

    .mom-preview-content thead {
        display: table-header-group;
    }

    .mom-preview-content tr {
        page-break-inside: avoid;
        break-inside: avoid;
    }

    .mom-preview-content th,
    .mom-preview-content td {
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        word-break: break-word !important;
        min-width: 0 !important;
        vertical-align: top !important;
    }

    .mom-preview-content table {
        width: 100% !important;
        table-layout: fixed !important;
    }

    /* Pastikan tulisan header tabel tetap gelap dan terbaca saat print/PDF. */
    .mom-preview-content th {
        color: #1d3150 !important;
        background-color: #eaf0fa !important;
        font-weight: 700 !important;
        opacity: 1 !important;
        -webkit-text-fill-color: #1d3150 !important;
    }

    .mom-preview-content table:nth-of-type(3) th:nth-child(1),
    .mom-preview-content table:nth-of-type(3) td:nth-child(1) {
        width: 5% !important;
    }

    .mom-preview-content table:nth-of-type(3) th:nth-child(2),
    .mom-preview-content table:nth-of-type(3) td:nth-child(2) {
        width: 17% !important;
    }

    .mom-preview-content table:nth-of-type(3) th:nth-child(3),
    .mom-preview-content table:nth-of-type(3) td:nth-child(3) {
        width: 58% !important;
    }

    .mom-preview-content table:nth-of-type(3) th:nth-child(4),
    .mom-preview-content table:nth-of-type(3) td:nth-child(4) {
        width: 20% !important;
    }

    .mom-preview-content h1,
    .mom-preview-content h3 {
        page-break-after: avoid;
        break-after: avoid;
    }
}


    </style>

<script>

(function(){

    'use strict';

    const root=document.getElementById('momApp');

    if(!root)return;

    const $=id=>document.getElementById(id);

    const userKey = @json(auth()->id());

    const initialMom = @json($mom ?? null);

    let momId = initialMom?.id ? Number(initialMom.id) : (root.dataset.momId ? Number(root.dataset.momId) : null);

    const newStorageKey = 'newwicker_mom_autosave_'+userKey+'_new';

    const newClientKeyStorage = 'newwicker_mom_client_key_'+userKey+'_new';

    const recordStorageKey = id => 'newwicker_mom_autosave_'+userKey+'_'+id;

    const recordClientKeyStorage = id => 'newwicker_mom_client_key_'+userKey+'_'+id;

    let storageKey = momId ? recordStorageKey(momId) : newStorageKey;

    let clientKeyStorage = momId ? recordClientKeyStorage(momId) : newClientKeyStorage;

    let clientKey = null;

    if (momId) {

        // The database is authoritative for an existing MoM; never reuse the "new" draft key.

        clientKey = initialMom?.client_key || null;

        if (clientKey) localStorage.setItem(clientKeyStorage, clientKey);

    } else {

        clientKey = localStorage.getItem(newClientKeyStorage);

        if (!clientKey) {

            clientKey = window.crypto?.randomUUID?.() || ('mom-'+Date.now()+'-'+Math.random().toString(36).slice(2));

            localStorage.setItem(newClientKeyStorage, clientKey);

        }

    }

    let timer=null, saving=false, dirty=false, revision=0;

    const netStatus=$('momNetworkStatus'), saveStatus=$('momSaveStatus');

    let serverVerified=null, verificationInFlight=null, lastNetworkMessage='';

    function setNetwork(message){

        lastNetworkMessage=message;

        netStatus.textContent=message;

        netStatus.style.fontWeight='600';

        netStatus.style.color=message.startsWith('Online —')?'#167047':message.startsWith('Memeriksa')?'#64748b':'#bd4a24';

    }

    function setSave(message){saveStatus.textContent=message;}

    function persistLocal(){

        try{

            const value=JSON.stringify({data:getData(),id:momId,at:Date.now(),clientKey});

            localStorage.setItem(storageKey,value);

            if(momId) localStorage.setItem('newwicker_mom_autosave_'+userKey+'_'+momId,value);

        }catch(e){setSave('Penyimpanan lokal tidak tersedia');}

    }

    function markDirty(){

        revision++;dirty=true;persistLocal();setSave('Perubahan belum tersinkron');

        clearTimeout(timer);timer=setTimeout(()=>save(false),1600);

    }

    // Three states: null = checking, true = verified, false = unavailable.

    // Retry transient failures before reporting an unreachable server.

    async function verifyServer(){

        if(!navigator.onLine){serverVerified=false;setNetwork('Offline / no internet');return false;}

        if(verificationInFlight)return verificationInFlight;

        if(serverVerified!==true)setNetwork('Memeriksa koneksi server...');

        verificationInFlight=(async()=>{

            for(let attempt=0;attempt<2;attempt++){

                if(!navigator.onLine){serverVerified=false;setNetwork('Offline / no internet');return false;}

                const controller=new AbortController();

                const timeout=setTimeout(()=>controller.abort(),10000);

                try{

                    const response=await fetch(root.dataset.pingUrl,{

                        method:'GET',credentials:'same-origin',cache:'no-store',

                        headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},

                        signal:controller.signal

                    });

                    if(!response.ok)throw new Error('HTTP '+response.status);

                    const data=await response.json();

                    if(data.success!==true)throw new Error('Respons ping tidak valid');

                    serverVerified=true;setNetwork('Online — server terhubung');

                    return true;

                }catch(error){

                    console.warn('MoM ping attempt '+(attempt+1)+' failed:',error);

                    if(attempt===0){

                        if(serverVerified!==true)setNetwork('Memeriksa koneksi server...');

                        await new Promise(resolve=>setTimeout(resolve,1200));

                    }

                }finally{clearTimeout(timeout);}

            }

            if(!navigator.onLine){serverVerified=false;setNetwork('Offline / no internet');}

            else if(serverVerified!==true){serverVerified=false;setNetwork('');}

            return serverVerified===true;

        })();

        try{return await verificationInFlight;}

        finally{verificationInFlight=null;}

    }

    window.addEventListener('offline',()=>{

        serverVerified=false;setNetwork('Offline / no internet');

        if(dirty){persistLocal();setSave('Tersimpan lokal, menunggu koneksi');}

    });

    window.addEventListener('online',async()=>{

        if(await verifyServer() && dirty)save(false);

    });

    setNetwork(navigator.onLine?'Memeriksa koneksi server...':'Offline / no internet');

    let nextPerson=1,nextDiscussion=1;

    let attendees=[];

    let discussions=[];

    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[c]));

    const pad=n=>String(n).padStart(2,'0');

    const dateText=d=>{if(!d)return '-';const a=d.split('-');return a.length===3?`${a[2]}/${a[1]}/${a[0]}`:d};

    const getPerson=id=>attendees.find(p=>p.id===Number(id));

    const makePerson=(name='',position='')=>({id:nextPerson++,name,position});

    const makeDiscussion=(personId=null,description='',notes='')=>({id:nextDiscussion++,personId:personId??attendees[0]?.id??null,description,notes});

    const syncInputs=()=>{

        document.querySelectorAll('#momAttendeesBody tr').forEach(tr=>{const p=attendees.find(x=>x.id===Number(tr.dataset.id));if(!p)return;p.name=tr.querySelector('[data-field="name"]').value;p.position=tr.querySelector('[data-field="position"]').value;});

        document.querySelectorAll('#momDiscussionsBody tr').forEach(tr=>{const d=discussions.find(x=>x.id===Number(tr.dataset.id));if(!d)return;d.personId=Number(tr.querySelector('[data-field="personId"]').value)||null;d.description=tr.querySelector('[data-field="description"]').value;d.notes=tr.querySelector('[data-field="notes"]').value;});

    };

    function renderAttendees(){

        $('momAttendeesBody').innerHTML=attendees.map((p,i)=>`<tr data-id="${p.id}"><td>${i+1}</td><td><input type="text" data-field="name" value="${esc(p.name)}" placeholder="Nama peserta"></td><td><input type="text" data-field="position" value="${esc(p.position)}" placeholder="Position / Jabatan"></td><td style="text-align:center"><button type="button" class="mom-delete" data-remove-person="${p.id}" title="Remove attendee"><i class="fa fa-trash"></i></button></td></tr>`).join('')||'<tr><td colspan="4">Belum ada peserta. Klik Add Person.</td></tr>';

    }

    function renderDiscussions(){

        discussions.forEach(d=>{if(!getPerson(d.personId))d.personId=attendees[0]?.id??null;});

        $('momDiscussionsBody').innerHTML=discussions.map((d,i)=>`<tr data-id="${d.id}"><td>${i+1}</td><td><select data-field="personId"><option value="">Pilih peserta</option>${attendees.map(p=>`<option value="${p.id}" ${d.personId===p.id?'selected':''}>${esc(p.name||'Unnamed')}</option>`).join('')}</select><span class="mom-position-hint">${esc(getPerson(d.personId)?.position||'Position belum diisi')}</span></td><td><textarea data-field="description" rows="3" placeholder="Ketik - di awal baris untuk bullet point">${esc(d.description)}</textarea></td><td><textarea data-field="notes" rows="3" placeholder="Notes / follow up">${esc(d.notes)}</textarea></td><td style="text-align:center"><button type="button" class="mom-delete" data-remove-discussion="${d.id}" title="Remove row"><i class="fa fa-trash"></i></button></td></tr>`).join('')||'<tr><td colspan="5">Belum ada pembahasan. Klik Add Row.</td></tr>';

    }

    function getData(){syncInputs();return {date:$('momDate').value,month:$('momMonth').value,subject:$('momSubject').value,recordedBy:$('momRecordedBy').value,recordedPosition:$('momRecordedPosition').value,attendees:attendees.map(x=>({...x})),discussions:discussions.map(x=>({...x}))};}

    async function save(manual=true){

        clearTimeout(timer);

        if(saving){if(manual)setSave('Menunggu penyimpanan berjalan');return;}

        const d=getData();

        persistLocal();

        if(!navigator.onLine){setNetwork('Offline / no internet');setSave('Tersimpan lokal, akan dikirim saat online');return;}

        if(momId && !clientKey){setSave('client_key record tidak tersedia — autosave dihentikan');return;}

        saving=true;

        const sendingRevision=revision;

        setSave('Menyimpan ke server...');

        const buttons=['momSaveTop','momSaveBottom'].map($);

        buttons.forEach(b=>{b.disabled=true;b.style.opacity='.65';});

        try{

            const response=await fetch(root.dataset.storeUrl,{

                method:'POST',credentials:'same-origin',

                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':root.dataset.csrf},

                body:JSON.stringify({

                    id:momId,client_key:clientKey,meeting_date:d.date||null,start_month:d.month||null,

                    subject:d.subject,attendees:d.attendees,discussions:d.discussions,

                    divisi:d.recordedPosition||null

                })

            });

            const result=await response.json().catch(()=>({}));

            if(!response.ok){

                const errors=result.errors?Object.values(result.errors).flat().join('; '):'';

                throw new Error(errors||result.message||'HTTP '+response.status);

            }

            serverVerified=true;setNetwork('Online — server terhubung');

            if(!momId){

                const oldNewStorageKey=storageKey;

                momId=Number(result.id);

                root.dataset.momId=String(momId);

                $('momRecordId').textContent='MoM #'+momId;

                // Replace the URL without creating another database record.

                if(result.edit_url)history.replaceState({},'',result.edit_url);

                storageKey=recordStorageKey(momId);

                clientKeyStorage=recordClientKeyStorage(momId);

                localStorage.setItem(clientKeyStorage,clientKey);

                localStorage.removeItem(newClientKeyStorage);

                localStorage.removeItem(oldNewStorageKey);

                if(revision!==sendingRevision)persistLocal();

            }

            if(revision===sendingRevision){dirty=false;localStorage.removeItem(storageKey);setSave('Tersimpan di database · '+new Date().toLocaleTimeString('id-ID'));}

            else {dirty=true;persistLocal();setSave('Ada perubahan baru, menyimpan lagi...');}

        }catch(err){

            dirty=true;persistLocal();

            if(!navigator.onLine || err instanceof TypeError){serverVerified=false;setNetwork(navigator.onLine?'Server tidak dapat dihubungi / koneksi lemah':'Offline / no internet');}

            setSave('Tersimpan lokal — '+(err.message||'gagal sinkron'));

            if(manual)alert(err.message||'Belum berhasil menyimpan ke server.');

        }finally{

            saving=false;buttons.forEach(b=>{b.disabled=false;b.style.opacity='';});

            if(dirty && navigator.onLine && revision!==sendingRevision){clearTimeout(timer);timer=setTimeout(()=>save(false),1600);}

        }

    }

    function copyText(){const d=getData();const lines=['MINUTES OF MEETING','PT. NEWWICKER INDONESIA',`Day / Date: ${dateText(d.date)}`,`Start Month: ${d.month}`,`Subject: ${d.subject}`,'','ATTENDEES',...d.attendees.map((p,i)=>`${i+1}. ${p.name} — ${p.position}`),'','SUBJECT / DESCRIPTION / URAIAN',...d.discussions.flatMap((r,i)=>[`${i+1}. ${getPerson(r.personId)?.name||'-'} (${getPerson(r.personId)?.position||'-'})`,...r.description.split('\n').filter(x=>x.trim()).map(x=>`• ${x.trim().replace(/^[•\\-]\s*/,'')}`),`Notes: ${r.notes||'-'}`,'']),`Recorded By: ${d.recordedBy}`,`Position: ${d.recordedPosition||'-'}`];const t=lines.join('\n');if(navigator.clipboard&&window.isSecureContext){navigator.clipboard.writeText(t).then(()=>alert('MoM berhasil disalin.'),()=>fallbackCopy(t));}else fallbackCopy(t);}

    function fallbackCopy(t){const el=document.createElement('textarea');el.value=t;el.style.position='fixed';el.style.opacity='0';document.body.appendChild(el);el.select();const ok=document.execCommand('copy');el.remove();alert(ok?'MoM berhasil disalin.':'Gagal menyalin MoM.');}

    function preview(){const d=getData();$('momPreviewContent').innerHTML=`<h1>MINUTES OF MEETING</h1><div class="mom-company">PT. NEWWICKER INDONESIA</div><table><tr><th style="width:180px">Day / Date</th><td>${esc(dateText(d.date))}</td></tr><tr><th>Start Month</th><td>${esc(d.month)}</td></tr><tr><th>Subject</th><td>${esc(d.subject)}</td></tr></table><h3>ATTENDEES</h3><table><thead><tr><th style="width:50px">No.</th><th>Name</th><th>Position</th></tr></thead><tbody>${d.attendees.map((p,i)=>`<tr><td>${i+1}</td><td>${esc(p.name)}</td><td>${esc(p.position)}</td></tr>`).join('')}</tbody></table><h3>SUBJECT / DESCRIPTION / URAIAN</h3><table><thead><tr><th style="width:40px">No.</th><th style="width:20%">Person / Position</th><th>Description / Uraian</th><th style="width:20%">Notes</th></tr></thead><tbody>${d.discussions.map((r,i)=>`<tr><td>${i+1}</td><td><strong>${esc(getPerson(r.personId)?.name||'-')}</strong><div>${esc(getPerson(r.personId)?.position||'')}</div></td><td><ul>${r.description.split('\n').filter(x=>x.trim()).map(x=>`<li>${esc(x.trim().replace(/^[•\\-]\s*/,''))}</li>`).join('')}</ul></td><td>${esc(r.notes).replace(/\n/g,'<br>')}</td></tr>`).join('')}</tbody></table><h3>RECORDED BY</h3><table><tr><th style="width:180px">Name</th><td>${esc(d.recordedBy)}</td></tr><tr><th>Position / Department</th><td>${esc(d.recordedPosition||'-')}</td></tr></table>`;$('momPreviewOverlay').classList.add('is-open');$('momPreviewOverlay').setAttribute('aria-hidden','false');}

    function closePreview(){$('momPreviewOverlay').classList.remove('is-open');$('momPreviewOverlay').setAttribute('aria-hidden','true');}

    $('momAddPerson').addEventListener('click',()=>{syncInputs();attendees.push(makePerson());renderAttendees();renderDiscussions();$('momAttendeesBody tr:last-child input')?.focus();});

    $('momAddDiscussion').addEventListener('click',()=>{syncInputs();discussions.push(makeDiscussion());renderDiscussions();$('momDiscussionsBody tr:last-child textarea')?.focus();});

    $('momAttendeesBody').addEventListener('input',e=>{const tr=e.target.closest('tr');if(!tr||!e.target.dataset.field)return;const p=getPerson(tr.dataset.id);if(p){p[e.target.dataset.field]=e.target.value;if(e.target.dataset.field==='position')document.querySelectorAll('#momDiscussionsBody tr').forEach(r=>{const d=discussions.find(x=>x.id===Number(r.dataset.id));if(d&&d.personId===p.id)r.querySelector('.mom-position-hint').textContent=p.position||'Position belum diisi';});if(e.target.dataset.field==='name'){document.querySelectorAll('#momDiscussionsBody select[data-field="personId"] option').forEach(o=>{if(Number(o.value)===p.id)o.textContent=p.name||'Unnamed';});}}});

    $('momAttendeesBody').addEventListener('click',e=>{const btn=e.target.closest('[data-remove-person]');if(!btn)return;syncInputs();const id=Number(btn.dataset.removePerson);attendees=attendees.filter(p=>p.id!==id);discussions.forEach(d=>{if(d.personId===id)d.personId=attendees[0]?.id??null;});renderAttendees();renderDiscussions();});

    $('momDiscussionsBody').addEventListener('input',e=>{

        if(e.target.dataset.field==='description'){

            const el=e.target, old=el.value, cursor=el.selectionStart;

            // Only convert a dash followed by whitespace at the start of each line.

            const bulletify=t=>t.replace(/(^|\n)([ \t]*)-[ \t]+/g,'$1$2• ');

            const formatted=bulletify(old);

            if(formatted!==old){el.value=formatted;const pos=bulletify(old.slice(0,cursor)).length;el.setSelectionRange(pos,pos);}

        }

const tr=e.target.closest('tr');if(!tr||!e.target.dataset.field)return;const d=discussions.find(x=>x.id===Number(tr.dataset.id));if(d)d[e.target.dataset.field]=e.target.value;});

    $('momDiscussionsBody').addEventListener('change',e=>{const tr=e.target.closest('tr');if(!tr||!e.target.dataset.field)return;const d=discussions.find(x=>x.id===Number(tr.dataset.id));if(!d)return;if(e.target.dataset.field==='personId'){d.personId=Number(e.target.value)||null;tr.querySelector('.mom-position-hint').textContent=getPerson(d.personId)?.position||'Position belum diisi';}else d[e.target.dataset.field]=e.target.value;});

    $('momDiscussionsBody').addEventListener('click',e=>{const btn=e.target.closest('[data-remove-discussion]');if(!btn)return;syncInputs();discussions=discussions.filter(d=>d.id!==Number(btn.dataset.removeDiscussion));renderDiscussions();});

    ['momSaveTop','momSaveBottom'].forEach(id=>$(id).addEventListener('click',()=>save(true)));

    ['momCopyTop','momCopyBottom'].forEach(id=>$(id).addEventListener('click',copyText));

    $('momPreviewTop').addEventListener('click',preview);$('momClosePreview').addEventListener('click',closePreview);$('momPrint').addEventListener('click',()=>window.print());

    $('momPreviewOverlay').addEventListener('click',e=>{if(e.target===$('momPreviewOverlay'))closePreview();});

    document.addEventListener('keydown',e=>{if(e.key==='Escape')closePreview();});

    function loadData(saved){

        if(!saved)return;

        $('momDate').value=saved.date||'';$('momMonth').value=saved.month||'';

        $('momSubject').value=saved.subject||'';$('momRecordedPosition').value=saved.recordedPosition||'';

        attendees=Array.isArray(saved.attendees)?saved.attendees:[];

        discussions=Array.isArray(saved.discussions)?saved.discussions:[];

        nextPerson=Math.max(0,...attendees.map(x=>Number(x.id)||0))+1;

        nextDiscussion=Math.max(0,...discussions.map(x=>Number(x.id)||0))+1;

    }

    if(momId && !clientKey)setSave('Record lama tidak memiliki client_key. Hubungi admin untuk memperbaiki data.');

    if(initialMom){loadData({date:initialMom.meeting_date?.substring(0,10),month:initialMom.start_month,subject:initialMom.subject,recordedPosition:initialMom.divisi,attendees:initialMom.attendees,discussions:initialMom.discussions});}

    try{

        const cached=JSON.parse(localStorage.getItem(storageKey)||'null');

        if(cached?.data && cached.clientKey === clientKey && Number(cached.id || 0) === Number(momId || 0)){loadData(cached.data);dirty=true;setSave('Draft lokal dipulihkan — menunggu sinkron');}

    }catch(e){}

    // Every user input and structural change schedules autosave, without replacing existing handlers.

    root.addEventListener('input',markDirty);

    root.addEventListener('change',markDirty);

    ['momAddPerson','momAddDiscussion'].forEach(id=>$(id).addEventListener('click',markDirty));

    ['momAttendeesBody','momDiscussionsBody'].forEach(id=>$(id).addEventListener('click',e=>{if(e.target.closest('.mom-delete'))markDirty();}));

    window.addEventListener('beforeunload',()=>{if(dirty)persistLocal();});

    renderAttendees();renderDiscussions();

    verifyServer().then(ok=>{if(ok&&dirty)save(false);});

    setInterval(()=>{if(document.visibilityState==='visible')verifyServer().then(ok=>{if(ok&&dirty&&!saving)save(false);});},30000);

    document.addEventListener('visibilitychange',()=>{if(document.visibilityState==='visible')verifyServer().then(ok=>{if(ok&&dirty&&!saving)save(false);});});

})();

</script>

@endsection
