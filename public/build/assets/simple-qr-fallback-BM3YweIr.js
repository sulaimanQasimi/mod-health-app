class c{constructor(){this.size=80,this.margin=2}generateSimpleQR(t,r){const n=document.getElementById(r);if(!n)return;const e=this.createPattern(t),o=document.createElement("div");o.style.cssText=`
            width: ${this.size}px;
            height: ${this.size}px;
            background: white;
            border: 1px solid #ccc;
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            grid-template-rows: repeat(8, 1fr);
            gap: 1px;
            padding: 2px;
        `;for(let s=0;s<64;s++){const l=document.createElement("div");l.style.cssText=`
                background: ${e[s]?"#000":"#fff"};
                border-radius: 1px;
            `,o.appendChild(l)}const a=document.createElement("div");a.style.cssText=`
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            font-size: 8px;
            text-align: center;
            color: #666;
            word-break: break-all;
        `,a.textContent=t;const i=document.createElement("div");i.style.cssText="position: relative; display: inline-block;",i.appendChild(o),i.appendChild(a),n.innerHTML="",n.appendChild(i)}createPattern(t){const r=[];let n=0;for(let e=0;e<t.length;e++)n=(n<<5)-n+t.charCodeAt(e)&4294967295;for(let e=0;e<64;e++)r.push(n>>e&!0);return r}}window.SimpleQRGenerator=new c;document.addEventListener("DOMContentLoaded",function(){document.querySelectorAll("[data-simple-qr]").forEach(t=>{const r=t.getAttribute("data-simple-qr");r&&window.SimpleQRGenerator.generateSimpleQR(r,t.id)})});
