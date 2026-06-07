import{_ as I,c as w,o as v,a as p,t as L,k as E,n as x,f as B,l as F,F as T,r as M,e as _,h as j}from"./_plugin-vue_export-helper-beOoQdwu.js";const q={name:"SvgTooth",props:{toothNumber:{type:Number,required:!0},toothData:{type:Object,default:null},toothType:{type:String,default:"incisor"},isSelected:{type:Boolean,default:!1}},computed:{condition(){return this.toothData?.tooth_condition||"no_data"},conditionClass(){return`tooth-${this.condition}`},conditionColor(){return{healthy:"#008000",cavity:"#ffc107",filling:"#17a2b8",crown:"#6f42c1",bridge:"#6610f2",missing:"#6c757d",extraction:"#dc3545",impacted:"#fd7e14",root_canal:"#20c997",implant:"#0d6efd",decay:"#e83e8c",fractured:"#ff6b6b",no_data:"#008000"}[this.condition]||"#008000"},toothStyle(){return{backgroundColor:this.conditionColor,borderColor:this.isSelected?"#0066ff":"#000000"}},tooltipText(){return this.toothData?`FDI ${this.toothNumber}: ${this.toothData.tooth_condition}`:`FDI ${this.toothNumber}: No data`}},methods:{handleClick(){this.$emit("click",this.toothNumber)}}},R=["title"],P={class:"tooth-number-label"},z={class:"tooth-number-below"};function H(e,l,i,s,d,a){return v(),w("div",{class:x(["tooth-wrapper",{"has-data":i.toothData,clickable:!0,selected:i.isSelected}]),onClick:l[0]||(l[0]=(...c)=>a.handleClick&&a.handleClick(...c)),title:a.tooltipText},[p("div",{class:x(["tooth-oval",a.conditionClass]),style:E(a.toothStyle)},[p("span",P,L(i.toothNumber),1)],6),p("div",z,L(i.toothNumber),1)],10,R)}const O=I(q,[["render",H],["__scopeId","data-v-9399888a"]]),A={name:"DentalChart",components:{SvgTooth:O},props:{teethData:{type:Object,default:()=>({})},dentistRegistrationId:{type:Number,required:!0}},data(){return{selectedTooth:null}},methods:{getToothData(e){return this.teethData[e]||null},getToothType(e){return[16,17,18,26,27,28,36,37,38,46,47,48].includes(e)?"molar":[14,15,24,25,34,35,44,45].includes(e)?"premolar":[13,23,33,43].includes(e)?"canine":"incisor"},handleToothClick(e){console.log("Tooth clicked:",e),this.selectedTooth=e,this.$emit("tooth-clicked",e);const l=this.getToothData(e),i=l?l.id:null;if(console.log("Tooth data:",l,"Chart ID:",i),console.log("openToothModal available:",typeof window.openToothModal),typeof window.openToothModal=="function")try{window.openToothModal(e,i)}catch(s){console.error("Error calling openToothModal:",s),alert("Error opening modal: "+s.message)}else console.warn("openToothModal not available, retrying..."),setTimeout(()=>{typeof window.openToothModal=="function"?window.openToothModal(e,i):(console.error("openToothModal function still not available after retry"),alert("Unable to open modal. Please refresh the page."))},200)},localize(e){return window.localize?window.localize(e):e}}},U={class:"dental-chart-container"},Y={class:"jaw-section upper-jaw mb-4"},V={class:"quadrant-row d-flex justify-content-center align-items-center mb-2"},G={class:"quadrant-row d-flex justify-content-center align-items-center mb-2"},W={class:"jaw-section lower-jaw"},K={class:"quadrant-row d-flex justify-content-center align-items-center mb-2"},X={class:"quadrant-row d-flex justify-content-center align-items-center mb-2"};function J(e,l,i,s,d,a){const c=B("svg-tooth");return v(),w("div",U,[l[2]||(l[2]=p("div",{class:"chart-header text-center mb-4"},[p("h5",{class:"mb-1"},"دندان‌ها"),p("p",{class:"text-muted mb-0 small"},"دندان‌های دائمی")],-1)),p("div",Y,[l[0]||(l[0]=p("div",{class:"jaw-label text-center mb-3"},[p("strong",null,"جوف فوقانی")],-1)),p("div",V,[(v(),w(T,null,M([18,17,16,15,14,13,12,11],t=>_(c,{key:"upper-right-"+t,"tooth-number":t,"tooth-data":a.getToothData(t),"tooth-type":a.getToothType(t),"is-selected":d.selectedTooth===t,onClick:n=>a.handleToothClick(t)},null,8,["tooth-number","tooth-data","tooth-type","is-selected","onClick"])),64))]),p("div",G,[(v(),w(T,null,M([21,22,23,24,25,26,27,28],t=>_(c,{key:"upper-left-"+t,"tooth-number":t,"tooth-data":a.getToothData(t),"tooth-type":a.getToothType(t),"is-selected":d.selectedTooth===t,onClick:n=>a.handleToothClick(t)},null,8,["tooth-number","tooth-data","tooth-type","is-selected","onClick"])),64))])]),l[3]||(l[3]=p("div",{class:"jaw-spacer my-4"},null,-1)),p("div",W,[l[1]||(l[1]=p("div",{class:"jaw-label text-center mb-3"},[p("strong",null,"جوف تحتانی")],-1)),p("div",K,[(v(),w(T,null,M([48,47,46,45,44,43,42,41],t=>_(c,{key:"lower-left-"+t,"tooth-number":t,"tooth-data":a.getToothData(t),"tooth-type":a.getToothType(t),"is-selected":d.selectedTooth===t,onClick:n=>a.handleToothClick(t)},null,8,["tooth-number","tooth-data","tooth-type","is-selected","onClick"])),64))]),p("div",X,[(v(),w(T,null,M([31,32,33,34,35,36,37,38],t=>_(c,{key:"lower-right-"+t,"tooth-number":t,"tooth-data":a.getToothData(t),"tooth-type":a.getToothType(t),"is-selected":d.selectedTooth===t,onClick:n=>a.handleToothClick(t)},null,8,["tooth-number","tooth-data","tooth-type","is-selected","onClick"])),64))])]),l[4]||(l[4]=F('<div class="chart-legend mt-4 pt-3 border-top" data-v-e76448d6><h6 class="mb-2 text-center" data-v-e76448d6>راهنما:</h6><div class="d-flex flex-wrap justify-content-center gap-2" data-v-e76448d6><span class="badge" style="background-color:#008000;color:white;" data-v-e76448d6> سالم </span><span class="badge" style="background-color:#ffc107;color:#000;" data-v-e76448d6> پوسیدگی </span><span class="badge" style="background-color:#17a2b8;color:white;" data-v-e76448d6> پرکردگی </span><span class="badge" style="background-color:#6f42c1;color:white;" data-v-e76448d6> پوش </span><span class="badge" style="background-color:#6c757d;color:white;" data-v-e76448d6> فاقد دندان </span><span class="badge" style="background-color:#dc3545;color:white;" data-v-e76448d6> کشیده شده </span></div></div>',1))])}const Q=I(A,[["render",J],["__scopeId","data-v-e76448d6"]]);function k(e=document){if(!e)return;e.querySelectorAll("[data-implant-fields]").forEach(i=>{const d=(i.closest("form")||e.querySelector("form")||e).querySelector('select[name="tooth_condition"]');if(!d)return;const a=()=>{const c=d.value==="implant";i.style.display=c?"":"none",i.querySelectorAll("input, select, textarea").forEach(t=>{t.disabled=!c})};d.addEventListener("change",a),a()})}function D(){const e=document.getElementById("toothModal");if(e)try{if(typeof window.bootstrap<"u"&&window.bootstrap.Modal){const l=window.bootstrap.Modal.getInstance(e);l?l.hide():new window.bootstrap.Modal(e).hide()}else if(typeof window.$<"u"&&window.$.fn.modal)window.$(e).modal("hide");else{e.style.display="none",e.classList.remove("show"),document.body.classList.remove("modal-open");const l=document.getElementById("toothModalBackdrop");l&&l.remove()}}catch(l){console.error("Error closing modal:",l),e.style.display="none",e.classList.remove("show"),document.body.classList.remove("modal-open");const i=document.getElementById("toothModalBackdrop");i&&i.remove()}}function y(e,l,i){const s=new FormData(e),d=e.dataset.dentistRegistrationId||document.getElementById("dental-chart-vue-container")?.dataset.dentistRegistrationId||e.querySelector('input[name="dentist_registration_id"]')?.value||"";let a;if(i&&l)a=`/dental-charts/update/${l}`;else if(d)a=`/dental-charts/store/${d}`;else if(e.action&&e.action!==window.location.href&&!e.action.includes("dentist-registrations/show")&&e.action.includes("dental-charts"))a=e.action;else{console.error("Cannot determine submission URL",{isEdit:i,chartId:l,dentistRegistrationId:d,formAction:e.action,currentUrl:window.location.href}),alert("Error: Cannot determine submission URL. Please refresh the page.");return}if(a.startsWith("http://")||a.startsWith("https://"))try{a=new URL(a).pathname}catch{console.error("Invalid URL format:",a)}else a.startsWith("/")||(a="/"+a);console.log("Submitting to URL:",a,{isEdit:i,chartId:l,dentistRegistrationId:d});const c=document.querySelector('meta[name="csrf-token"]')?.content||"",t=e.querySelector('button[type="submit"]'),n=t.innerHTML;t.disabled=!0,t.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>'+(window.localize?window.localize("global.saving"):"Saving..."),s.has("_token")||s.append("_token",c),i&&!s.has("_method")&&s.append("_method","PUT"),fetch(a,{method:"POST",headers:{"X-CSRF-TOKEN":c,"X-Requested-With":"XMLHttpRequest",Accept:"application/json"},body:s}).then(o=>{const m=o.headers.get("content-type");return m&&m.includes("application/json")?o.json():o.text().then(r=>{if(o.redirected||o.url!==a)return{success:!0,redirect:!0,url:o.url};const u=new DOMParser().parseFromString(r,"text/html").querySelectorAll(".error, .invalid-feedback, .alert-danger");return u.length>0?{success:!1,message:Array.from(u).map(g=>g.textContent.trim()).join(", ")}:{success:!0}})}).then(o=>{if(o&&o.success!==void 0)if(o.success)o.message&&console.log("Success:",o.message),D(),window.location.reload?setTimeout(()=>{window.location.reload()},500):window.location.reload();else{const m=o.message||o.error||(window.localize?window.localize("global.save_failed"):"Save failed");alert(m),t.disabled=!1,t.innerHTML=n}else if(o&&o.errors){const m=Object.values(o.errors).flat().join(`
`);alert(m||(window.localize?window.localize("global.validation_failed"):"Validation failed")),t.disabled=!1,t.innerHTML=n}else D(),setTimeout(()=>window.location.reload(),500)}).catch(o=>{console.error("Error submitting form:",o),alert(window.localize?window.localize("global.save_failed"):"Save failed: "+o.message),t.disabled=!1,t.innerHTML=n})}function C(e,l){console.log("openToothModal called with:",e,l);const i=document.getElementById("toothModalBody"),s=document.getElementById("toothModal");if(!s){console.error("Tooth modal element not found. Creating modal..."),Z(),setTimeout(()=>C(e,l),100);return}let d;if(typeof window.bootstrap<"u"&&window.bootstrap.Modal)try{let n=window.bootstrap.Modal.getInstance(s);n||(n=new window.bootstrap.Modal(s,{backdrop:!0,keyboard:!0,focus:!0})),d=n}catch(n){console.log("Bootstrap Modal error, using jQuery fallback:",n),typeof window.$<"u"&&window.$.fn.modal?d={show:()=>window.$(s).modal("show"),hide:()=>window.$(s).modal("hide")}:d={show:()=>{s.style.display="block",s.classList.add("show"),document.body.classList.add("modal-open");const o=document.createElement("div");o.className="modal-backdrop fade show",o.id="toothModalBackdrop",o.onclick=()=>d.hide(),document.body.appendChild(o)},hide:()=>{s.style.display="none",s.classList.remove("show"),document.body.classList.remove("modal-open");const o=document.getElementById("toothModalBackdrop");o&&o.remove()}}}else typeof window.$<"u"&&window.$.fn.modal?d={show:()=>window.$(s).modal("show"),hide:()=>window.$(s).modal("hide")}:(console.warn("Bootstrap not available, using manual modal"),d={show:()=>{s.style.display="block",s.classList.add("show"),document.body.classList.add("modal-open");const n=document.createElement("div");n.className="modal-backdrop fade show",n.id="toothModalBackdrop",n.onclick=()=>d.hide(),document.body.appendChild(n)},hide:()=>{s.style.display="none",s.classList.remove("show"),document.body.classList.remove("modal-open");const n=document.getElementById("toothModalBackdrop");n&&n.remove()}});const a=document.getElementById("modalToothNumber");a&&(a.textContent=e);const c=document.getElementById("dental-chart-vue-container")?.dataset.dentistRegistrationId||"",t=document.querySelector('meta[name="csrf-token"]')?.content||"";l?(i.innerHTML=`
            <div class="text-center mb-3">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `,fetch(`/dental-charts/edit/${l}`).then(n=>n.text()).then(n=>{const r=new DOMParser().parseFromString(n,"text/html").querySelector("form");if(r){r.setAttribute("action",`/dental-charts/update/${l}`),r.setAttribute("method","POST"),r.dataset.dentistRegistrationId=c,r.dataset.chartId=l;let h=r.querySelector('input[name="_method"]');h?h.value="PUT":(h=document.createElement("input"),h.type="hidden",h.name="_method",h.value="PUT",r.appendChild(h));let f=r.querySelector('input[name="_token"]');f?f.value=t:(f=document.createElement("input"),f.type="hidden",f.name="_token",f.value=t,r.appendChild(f)),setTimeout(()=>{if(typeof window.$<"u"&&window.$.fn.persianDatepicker){const u=r.querySelector('input.datepicker_dari:not([name="chart_date"])');if(u&&!u.dataset.persianDatepickerInitialized){const b=u.value;$(u).persianDatepicker({formatDate:"YYYY-MM-DD",calendar:{persian:{locale:"en",showHint:!0,leapYearMode:"algorithmic"}},checkDate:function(g){return!0},onSelect:function(){const g=$(this).val();g&&(u.value=g)}}),b&&$(u).val(b),u.dataset.persianDatepickerInitialized="true"}}},150),k(r),r.addEventListener("submit",function(u){u.preventDefault(),u.stopPropagation();const b=typeof y=="function"?y:window.submitToothForm;b&&b(r,l,!0)}),i.innerHTML="",i.appendChild(r)}else S(i,e,c,t,l)}).catch(n=>{console.error("Error loading chart:",n),S(i,e,c,t,l)})):S(i,e,c,t,null);try{if(d&&typeof d.show=="function")d.show(),console.log("Modal shown successfully");else{console.error("Modal show method not available"),s.style.display="block",s.classList.add("show"),document.body.classList.add("modal-open");const n=document.createElement("div");n.className="modal-backdrop fade show",n.id="toothModalBackdrop",n.onclick=()=>{s.style.display="none",s.classList.remove("show"),document.body.classList.remove("modal-open"),n.remove()},document.body.appendChild(n)}}catch(n){if(console.error("Error showing modal:",n),s){s.style.display="block",s.classList.add("show"),document.body.classList.add("modal-open");const o=document.createElement("div");o.className="modal-backdrop fade show",o.id="toothModalBackdrop",o.onclick=()=>{s.style.display="none",s.classList.remove("show"),document.body.classList.remove("modal-open"),o.remove()},document.body.appendChild(o)}}}window.openToothModal=C;window.submitToothForm=y;window.closeModal=D;window.initImplantFields=k;window.addEventListener("load",function(){window.openToothModal||(window.openToothModal=C),window.submitToothForm||(window.submitToothForm=y),window.closeModal||(window.closeModal=D),window.initImplantFields||(window.initImplantFields=k)});document.addEventListener("DOMContentLoaded",function(){k(document);const e=document.getElementById("dental-chart-vue-container");if(e){const l=parseInt(e.dataset.dentistRegistrationId||"0"),i=e.dataset.teethData||"{}";let s={};try{const c=i.replace(/&quot;/g,'"').replace(/&#039;/g,"'").replace(/&amp;/g,"&");s=JSON.parse(c)}catch(c){console.error("Error parsing teeth data:",c),console.error("Raw data:",i),console.error("Data length:",i.length);try{const t=i.replace(/&quot;/g,'"');s=JSON.parse(t)}catch(t){console.error("Second parse attempt failed:",t),s={}}}j(Q,{teethData:s||{},dentistRegistrationId:l}).mount("#dental-chart-vue-container")}});function S(e,l,i,s,d){const a=d!==null,c=a?`/dental-charts/update/${d}`:`/dental-charts/store/${i}`;new Date().toLocaleDateString("fa-IR"),e.innerHTML=`
        <form id="toothForm" action="${c}" method="POST">
            <input type="hidden" name="_token" value="${s}">
            ${a?'<input type="hidden" name="_method" value="PUT">':""}
            <input type="hidden" name="tooth_number" value="${l}">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">وضعیت دندان <span class="text-danger">*</span></label>
                    <select name="tooth_condition" class="form-select" required>
                        <option value="healthy">Healthy</option>
                        <option value="cavity">Cavity</option>
                        <option value="filling">Filling</option>
                        <option value="crown">Crown</option>
                        <option value="bridge">Bridge</option>
                        <option value="root_canal">Root Canal</option>
                        <option value="implant">Implant</option>
                        <option value="decay">Decay</option>
                        <option value="fractured">Fractured</option>
                        <option value="extraction">Extraction</option>
                        <option value="missing">Missing</option>
                        <option value="impacted">Impacted</option>
                    </select>
                </div>
                
                <!-- Implant-only fields -->
                <div class="col-12" data-implant-fields style="display:none;">
                    <div class="border rounded p-3 mb-3 bg-body-secondary">
                        <h6 class="mb-3">جزئیات ایمپلنت</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">سیستم/برند ایمپلنت</label>
                                <input type="text" name="implant_system_brand" class="form-control" placeholder="مثال: Straumann">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">قطر (میلی‌متر)</label>
                                <input type="number" step="0.01" min="0" name="implant_diameter" class="form-control" placeholder="مثال: 4.1">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">طول (میلی‌متر)</label>
                                <input type="number" step="0.01" min="0" name="implant_length" class="form-control" placeholder="مثال: 10">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">وضعیت ایمپلنت</label>
                                <select name="implant_status" class="form-select">
                                    <option value="">Select</option>
                                    <option value="planned">Planned</option>
                                    <option value="placed">Placed</option>
                                    <option value="failed">Failed</option>
                                    <option value="removed">Removed</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">یادداشت‌های ایمپلنت</label>
                                <textarea name="implant_notes" class="form-control" rows="3" placeholder="یادداشت‌های مرتبط با ایمپلنت را وارد کنید"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">سلامت بیره دندان</label>
                    <select name="gum_health" class="form-select">
                        <option value="">Select</option>
                        <option value="healthy">Healthy</option>
                        <option value="gingivitis">Gingivitis</option>
                        <option value="periodontitis">Periodontitis</option>
                        <option value="recession">Recession</option>
                        <option value="bleeding">Bleeding</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">امتیاز بهداشت دهان</label>
                    <input type="number" step="0.1" min="0" max="10" name="oral_hygiene_score" class="form-control" placeholder="0-10">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">عمق ریشه (mm)</label>
                    <input type="number" step="0.01" min="0" max="20" name="pocket_depth" class="form-control" placeholder="0-20">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">خونریزی</label>
                    <select name="bleeding" class="form-select">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">لقی دندان</label>
                    <select name="mobility" class="form-select">
                        <option value="">Select</option>
                        <option value="none">None</option>
                        <option value="grade1">Grade 1</option>
                        <option value="grade2">Grade 2</option>
                        <option value="grade3">Grade 3</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">سابقه درمان</label>
                    <textarea name="treatment_history" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">یادداشت ها</label>
                    <textarea name="notes" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                <button type="submit" class="btn btn-primary">ذخیره</button>
            </div>
        </form>
    `,setTimeout(()=>{const n=document.getElementById("toothForm");if(n){if(!n.action||n.action.includes("dentist-registrations/show")){const m=a?`/dental-charts/update/${d}`:`/dental-charts/store/${i}`;n.setAttribute("action",m),n.action=m}const o=n.cloneNode(!0);if(o.setAttribute("action",n.action),o.action=n.action,n.parentNode.replaceChild(o,n),o.dataset.dentistRegistrationId=i,o.dataset.chartId=d||"",k(o),typeof window.$<"u"&&window.$.fn.persianDatepicker){const m=o.querySelector('input.datepicker_dari:not([name="chart_date"])');if(m&&!m.dataset.persianDatepickerInitialized&&($(m).persianDatepicker({formatDate:"YYYY-MM-DD",calendar:{persian:{locale:"en",showHint:!0,leapYearMode:"algorithmic"}},checkDate:function(r){return!0},onSelect:function(){const r=$(this).val();r&&(m.value=r)}}),m.dataset.persianDatepickerInitialized="true",!a&&!m.value)){const r=new Date;r.getFullYear(),String(r.getMonth()+1).padStart(2,"0"),String(r.getDate()).padStart(2,"0")}}else console.warn("Persian datepicker library not loaded");o.addEventListener("submit",function(m){m.preventDefault(),m.stopPropagation();const r=typeof y=="function"?y:typeof window.submitToothForm=="function"?window.submitToothForm:null;r?r(o,d,a):(console.error("submitToothForm function not available"),alert("Form submission error. Please refresh the page."))})}},150)}function Z(){if(document.getElementById("toothModal"))return;const e=`
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
    `;document.body.insertAdjacentHTML("beforeend",e)}
