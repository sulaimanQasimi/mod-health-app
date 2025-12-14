import{_ as k,c as p,o as b,a as i,t as w,k as M,n as z,f as C,F as g,r as y,e as f,h as $}from"./_plugin-vue_export-helper-CvOMsnYn.js";const L={name:"SvgTooth",props:{toothNumber:{type:Number,required:!0},toothData:{type:Object,default:null},toothType:{type:String,default:"incisor"},isSelected:{type:Boolean,default:!1}},computed:{condition(){return this.toothData?.tooth_condition||"no_data"},conditionClass(){return`tooth-${this.condition}`},conditionColor(){return{healthy:"#008000",cavity:"#ffc107",filling:"#17a2b8",crown:"#6f42c1",bridge:"#6610f2",missing:"#6c757d",extraction:"#dc3545",impacted:"#fd7e14",root_canal:"#20c997",implant:"#0d6efd",decay:"#e83e8c",fractured:"#ff6b6b",no_data:"#008000"}[this.condition]||"#008000"},toothStyle(){return{backgroundColor:this.conditionColor,borderColor:this.isSelected?"#0066ff":"#000000"}},tooltipText(){return this.toothData?`Tooth ${this.toothNumber}: ${this.toothData.tooth_condition}`:`Tooth ${this.toothNumber}: No data`}},methods:{handleClick(){this.$emit("click",this.toothNumber)}}},x=["title"],D={class:"tooth-number-label"},E={class:"tooth-number-below"};function B(o,a,d,l,s,e){return b(),p("div",{class:z(["tooth-wrapper",{"has-data":d.toothData,clickable:!0,selected:d.isSelected}]),onClick:a[0]||(a[0]=(...c)=>e.handleClick&&e.handleClick(...c)),title:e.tooltipText},[i("div",{class:z(["tooth-oval",e.conditionClass]),style:M(e.toothStyle)},[i("span",D,w(d.toothNumber),1)],6),i("div",E,w(d.toothNumber),1)],10,x)}const S=k(L,[["render",B],["__scopeId","data-v-a3850227"]]),I={name:"DentalChart",components:{SvgTooth:S},props:{teethData:{type:Object,default:()=>({})},dentistRegistrationId:{type:Number,required:!0}},data(){return{selectedTooth:null}},methods:{getToothData(o){return this.teethData[o]||null},getToothType(o){return[16,17,18,26,27,28,36,37,38,46,47,48].includes(o)?"molar":[14,15,24,25,34,35,44,45].includes(o)?"premolar":[13,23,33,43].includes(o)?"canine":"incisor"},handleToothClick(o){console.log("Tooth clicked:",o),this.selectedTooth=o,this.$emit("tooth-clicked",o);const a=this.getToothData(o),d=a?a.id:null;if(console.log("Tooth data:",a,"Chart ID:",d),console.log("openToothModal available:",typeof window.openToothModal),typeof window.openToothModal=="function")try{window.openToothModal(o,d)}catch(l){console.error("Error calling openToothModal:",l),alert("Error opening modal: "+l.message)}else console.warn("openToothModal not available, retrying..."),setTimeout(()=>{typeof window.openToothModal=="function"?window.openToothModal(o,d):(console.error("openToothModal function still not available after retry"),alert("Unable to open modal. Please refresh the page."))},200)},localize(o){return window.localize?window.localize(o):o}}},j={class:"dental-chart-container"},H={class:"chart-header text-center mb-4"},q={class:"mb-1"},R={class:"text-muted mb-0 small"},F={class:"jaw-section upper-jaw mb-4"},O={class:"jaw-label text-center mb-3"},P={class:"quadrant-row d-flex justify-content-center align-items-center mb-2"},G={class:"quadrant-row d-flex justify-content-center align-items-center mb-2"},K={class:"jaw-section lower-jaw"},U={class:"jaw-label text-center mb-3"},X={class:"quadrant-row d-flex justify-content-center align-items-center mb-2"},A={class:"quadrant-row d-flex justify-content-center align-items-center mb-2"},V={class:"chart-legend mt-4 pt-3 border-top"},J={class:"mb-2 text-center"},Q={class:"d-flex flex-wrap justify-content-center gap-2"},W={class:"badge",style:{"background-color":"#008000",color:"white"}},Y={class:"badge",style:{"background-color":"#ffc107",color:"#000"}},Z={class:"badge",style:{"background-color":"#17a2b8",color:"white"}},N={class:"badge",style:{"background-color":"#6f42c1",color:"white"}},oo={class:"badge",style:{"background-color":"#6c757d",color:"white"}},eo={class:"badge",style:{"background-color":"#dc3545",color:"white"}};function to(o,a,d,l,s,e){const c=C("svg-tooth");return b(),p("div",j,[i("div",H,[i("h5",q,w(e.localize("global.human_dentition")),1),i("p",R,w(e.localize("global.permanent_teeth")),1)]),i("div",F,[i("div",O,[i("strong",null,w(e.localize("global.upper_jaw")),1)]),i("div",P,[(b(),p(g,null,y([18,17,16,15,14,13,12,11],t=>f(c,{key:"upper-right-"+t,"tooth-number":t,"tooth-data":e.getToothData(t),"tooth-type":e.getToothType(t),"is-selected":s.selectedTooth===t,onClick:n=>e.handleToothClick(t)},null,8,["tooth-number","tooth-data","tooth-type","is-selected","onClick"])),64))]),i("div",G,[(b(),p(g,null,y([21,22,23,24,25,26,27,28],t=>f(c,{key:"upper-left-"+t,"tooth-number":t,"tooth-data":e.getToothData(t),"tooth-type":e.getToothType(t),"is-selected":s.selectedTooth===t,onClick:n=>e.handleToothClick(t)},null,8,["tooth-number","tooth-data","tooth-type","is-selected","onClick"])),64))])]),a[0]||(a[0]=i("div",{class:"jaw-spacer my-4"},null,-1)),i("div",K,[i("div",U,[i("strong",null,w(e.localize("global.lower_jaw")),1)]),i("div",X,[(b(),p(g,null,y([48,47,46,45,44,43,42,41],t=>f(c,{key:"lower-left-"+t,"tooth-number":t,"tooth-data":e.getToothData(t),"tooth-type":e.getToothType(t),"is-selected":s.selectedTooth===t,onClick:n=>e.handleToothClick(t)},null,8,["tooth-number","tooth-data","tooth-type","is-selected","onClick"])),64))]),i("div",A,[(b(),p(g,null,y([31,32,33,34,35,36,37,38],t=>f(c,{key:"lower-right-"+t,"tooth-number":t,"tooth-data":e.getToothData(t),"tooth-type":e.getToothType(t),"is-selected":s.selectedTooth===t,onClick:n=>e.handleToothClick(t)},null,8,["tooth-number","tooth-data","tooth-type","is-selected","onClick"])),64))])]),i("div",V,[i("h6",J,w(e.localize("global.legend"))+":",1),i("div",Q,[i("span",W,w(e.localize("global.healthy")),1),i("span",Y,w(e.localize("global.cavity")),1),i("span",Z,w(e.localize("global.filling")),1),i("span",N,w(e.localize("global.crown")),1),i("span",oo,w(e.localize("global.missing")),1),i("span",eo,w(e.localize("global.extraction")),1)])])])}const lo=k(I,[["render",to],["__scopeId","data-v-da2860df"]]);function _(o,a){console.log("openToothModal called with:",o,a);const d=document.getElementById("toothModalBody"),l=document.getElementById("toothModal");if(!l){console.error("Tooth modal element not found. Creating modal..."),no(),setTimeout(()=>_(o,a),100);return}let s;if(typeof window.bootstrap<"u"&&window.bootstrap.Modal)try{let n=window.bootstrap.Modal.getInstance(l);n||(n=new window.bootstrap.Modal(l,{backdrop:!0,keyboard:!0,focus:!0})),s=n}catch(n){console.log("Bootstrap Modal error, using jQuery fallback:",n),typeof window.$<"u"&&window.$.fn.modal?s={show:()=>window.$(l).modal("show"),hide:()=>window.$(l).modal("hide")}:s={show:()=>{l.style.display="block",l.classList.add("show"),document.body.classList.add("modal-open");const r=document.createElement("div");r.className="modal-backdrop fade show",r.id="toothModalBackdrop",r.onclick=()=>s.hide(),document.body.appendChild(r)},hide:()=>{l.style.display="none",l.classList.remove("show"),document.body.classList.remove("modal-open");const r=document.getElementById("toothModalBackdrop");r&&r.remove()}}}else typeof window.$<"u"&&window.$.fn.modal?s={show:()=>window.$(l).modal("show"),hide:()=>window.$(l).modal("hide")}:(console.warn("Bootstrap not available, using manual modal"),s={show:()=>{l.style.display="block",l.classList.add("show"),document.body.classList.add("modal-open");const n=document.createElement("div");n.className="modal-backdrop fade show",n.id="toothModalBackdrop",n.onclick=()=>s.hide(),document.body.appendChild(n)},hide:()=>{l.style.display="none",l.classList.remove("show"),document.body.classList.remove("modal-open");const n=document.getElementById("toothModalBackdrop");n&&n.remove()}});const e=document.getElementById("modalToothNumber");e&&(e.textContent=o);const c=document.getElementById("dental-chart-vue-container")?.dataset.dentistRegistrationId||"",t=document.querySelector('meta[name="csrf-token"]')?.content||"";a?(d.innerHTML=`
            <div class="text-center mb-3">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `,fetch(`/dental-charts/edit/${a}`).then(n=>n.text()).then(n=>{const h=new DOMParser().parseFromString(n,"text/html").querySelector("form");if(h){h.action=`/dental-charts/update/${a}`,h.method="POST";const u=document.createElement("input");if(u.type="hidden",u.name="_method",u.value="PUT",h.appendChild(u),!h.querySelector('input[name="_token"]')){const m=document.createElement("input");m.type="hidden",m.name="_token",m.value=t,h.appendChild(m)}h.onsubmit=function(m){m.preventDefault(),ao(h,a)},d.innerHTML="",d.appendChild(h)}else v(d,o,c,t,a)}).catch(n=>{console.error("Error loading chart:",n),v(d,o,c,t,a)})):v(d,o,c,t,null);try{if(s&&typeof s.show=="function")s.show(),console.log("Modal shown successfully");else{console.error("Modal show method not available"),l.style.display="block",l.classList.add("show"),document.body.classList.add("modal-open");const n=document.createElement("div");n.className="modal-backdrop fade show",n.id="toothModalBackdrop",n.onclick=()=>{l.style.display="none",l.classList.remove("show"),document.body.classList.remove("modal-open"),n.remove()},document.body.appendChild(n)}}catch(n){if(console.error("Error showing modal:",n),l){l.style.display="block",l.classList.add("show"),document.body.classList.add("modal-open");const r=document.createElement("div");r.className="modal-backdrop fade show",r.id="toothModalBackdrop",r.onclick=()=>{l.style.display="none",l.classList.remove("show"),document.body.classList.remove("modal-open"),r.remove()},document.body.appendChild(r)}}}window.openToothModal=_;window.addEventListener("load",function(){window.openToothModal||(window.openToothModal=_)});document.addEventListener("DOMContentLoaded",function(){const o=document.getElementById("dental-chart-vue-container");if(o){const a=parseInt(o.dataset.dentistRegistrationId||"0"),d=o.dataset.teethData||"{}";let l={};try{l=JSON.parse(d)}catch(c){console.error("Error parsing teeth data:",c)}$(lo,{teethData:l||{},dentistRegistrationId:a}).mount("#dental-chart-vue-container")}});function v(o,a,d,l,s){const e=s!==null;o.innerHTML=`
        <form id="toothForm" onsubmit="event.preventDefault(); submitToothForm(this, ${s||"null"}, ${e});">
            <input type="hidden" name="_token" value="${l}">
            ${e?'<input type="hidden" name="_method" value="PUT">':""}
            <input type="hidden" name="tooth_number" value="${a}">
            <input type="hidden" name="chart_date" value="${new Date().toISOString().split("T")[0]}">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">${window.localize?window.localize("global.tooth_condition"):"Tooth Condition"} <span class="text-danger">*</span></label>
                    <select name="tooth_condition" class="form-select" required>
                        <option value="healthy">${window.localize?window.localize("global.healthy"):"Healthy"}</option>
                        <option value="cavity">${window.localize?window.localize("global.cavity"):"Cavity"}</option>
                        <option value="filling">${window.localize?window.localize("global.filling"):"Filling"}</option>
                        <option value="crown">${window.localize?window.localize("global.crown"):"Crown"}</option>
                        <option value="bridge">${window.localize?window.localize("global.bridge"):"Bridge"}</option>
                        <option value="root_canal">${window.localize?window.localize("global.root_canal"):"Root Canal"}</option>
                        <option value="implant">${window.localize?window.localize("global.implant"):"Implant"}</option>
                        <option value="decay">${window.localize?window.localize("global.decay"):"Decay"}</option>
                        <option value="fractured">${window.localize?window.localize("global.fractured"):"Fractured"}</option>
                        <option value="extraction">${window.localize?window.localize("global.extraction"):"Extraction"}</option>
                        <option value="missing">${window.localize?window.localize("global.missing"):"Missing"}</option>
                        <option value="impacted">${window.localize?window.localize("global.impacted"):"Impacted"}</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">${window.localize?window.localize("global.gum_health"):"Gum Health"}</label>
                    <select name="gum_health" class="form-select">
                        <option value="">${window.localize?window.localize("global.select"):"Select"}</option>
                        <option value="healthy">${window.localize?window.localize("global.healthy"):"Healthy"}</option>
                        <option value="gingivitis">${window.localize?window.localize("global.gingivitis"):"Gingivitis"}</option>
                        <option value="periodontitis">${window.localize?window.localize("global.periodontitis"):"Periodontitis"}</option>
                        <option value="recession">${window.localize?window.localize("global.recession"):"Recession"}</option>
                        <option value="bleeding">${window.localize?window.localize("global.bleeding"):"Bleeding"}</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">${window.localize?window.localize("global.oral_hygiene_score"):"Oral Hygiene Score"}</label>
                    <input type="number" step="0.1" min="0" max="10" name="oral_hygiene_score" class="form-control" placeholder="0-10">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">${window.localize?window.localize("global.pocket_depth"):"Pocket Depth"} (mm)</label>
                    <input type="number" step="0.01" min="0" max="20" name="pocket_depth" class="form-control" placeholder="0-20">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">${window.localize?window.localize("global.bleeding"):"Bleeding"}</label>
                    <select name="bleeding" class="form-select">
                        <option value="0">${window.localize?window.localize("global.no"):"No"}</option>
                        <option value="1">${window.localize?window.localize("global.yes"):"Yes"}</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">${window.localize?window.localize("global.mobility"):"Mobility"}</label>
                    <select name="mobility" class="form-select">
                        <option value="">${window.localize?window.localize("global.select"):"Select"}</option>
                        <option value="none">${window.localize?window.localize("global.none"):"None"}</option>
                        <option value="grade1">${window.localize?window.localize("global.grade1"):"Grade 1"}</option>
                        <option value="grade2">${window.localize?window.localize("global.grade2"):"Grade 2"}</option>
                        <option value="grade3">${window.localize?window.localize("global.grade3"):"Grade 3"}</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">${window.localize?window.localize("global.treatment_history"):"Treatment History"}</label>
                    <textarea name="treatment_history" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">${window.localize?window.localize("global.notes"):"Notes"}</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${window.localize?window.localize("global.cancel"):"Cancel"}</button>
                <button type="submit" class="btn btn-primary">${window.localize?window.localize("global.save"):"Save"}</button>
            </div>
        </form>
    `}function ao(o,a,d){const l=new FormData(o),s=`/dental-charts/update/${a}`,e=o.querySelector('button[type="submit"]'),c=e.innerHTML;e.disabled=!0,e.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>'+(window.localize?window.localize("global.saving"):"Saving..."),fetch(s,{method:"POST",headers:{"X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content,"X-Requested-With":"XMLHttpRequest"},body:l}).then(t=>{if(t.redirected)window.location.href=t.url;else return t.json().catch(()=>{window.location.reload()})}).then(t=>{t&&t.success!==void 0?t.success?(T(),window.location.reload()):(alert(t.message||(window.localize?window.localize("global.save_failed"):"Save failed")),e.disabled=!1,e.innerHTML=c):(T(),window.location.reload())}).catch(t=>{console.error("Error:",t),alert(window.localize?window.localize("global.save_failed"):"Save failed"),e.disabled=!1,e.innerHTML=c})}function T(){const o=document.getElementById("toothModal");if(o)try{if(typeof window.bootstrap<"u"&&window.bootstrap.Modal){const a=window.bootstrap.Modal.getInstance(o);a?a.hide():new window.bootstrap.Modal(o).hide()}else if(typeof window.$<"u"&&window.$.fn.modal)window.$(o).modal("hide");else{o.style.display="none",o.classList.remove("show"),document.body.classList.remove("modal-open");const a=document.getElementById("toothModalBackdrop");a&&a.remove()}}catch(a){console.error("Error closing modal:",a),o.style.display="none",o.classList.remove("show"),document.body.classList.remove("modal-open");const d=document.getElementById("toothModalBackdrop");d&&d.remove()}}function no(){if(document.getElementById("toothModal"))return;const o=`
        <div class="modal fade" id="toothModal" tabindex="-1" aria-labelledby="toothModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="toothModalLabel">${window.localize?window.localize("global.tooth"):"Tooth"} <span id="modalToothNumber"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="toothModalBody">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    `;document.body.insertAdjacentHTML("beforeend",o)}
