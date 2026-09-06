import{b as x,r as b,u as p,j as e,H as g}from"./app-DSXKzeVY.js";import{u as h}from"./useTranslation-CvYAzUPq.js";/* empty css            */import"./_commonjsHelpers-DaWZu8wl.js";const u=["bx-plus-medical","bx-heart","bx-clipboard","bx-shield-quarter","bx-pulse","bx-user-circle","bx-band-aid","bx-capsule","bx-first-aid","bx-injection","bx-health","bx-donate-heart"];function y({status:l}){const{t:r,direction:n}=h(),{appUrls:i}=x().props,s=n==="rtl",[o,d]=b.useState(!1),t=p({email:"",password:"",remember:!1}),m=a=>{a.preventDefault(),t.post(i.login,{onFinish:()=>t.reset("password")})};return e.jsxs(e.Fragment,{children:[e.jsx(g,{title:r("global.login")}),e.jsxs("div",{className:"login-shell relative min-h-screen overflow-x-hidden text-slate-800",children:[e.jsx("div",{className:"pointer-events-none fixed inset-0 z-0 overflow-hidden","aria-hidden":"true",children:u.map((a,c)=>e.jsx("i",{className:`bx ${a} login-bg-icon login-bg-icon-${c+1}`},a))}),e.jsx("main",{className:"relative z-10 flex min-h-screen items-center justify-center px-4 py-6",children:e.jsxs("article",{className:"login-card w-full max-w-[400px] rounded-xl border border-slate-200 bg-white px-7 py-8 shadow-[0_1px_3px_rgba(15,23,42,0.06),0_8px_24px_rgba(15,23,42,0.06)]",children:[e.jsxs("div",{className:"mb-7 text-center",children:[e.jsx("div",{className:"mx-auto mb-3.5 inline-flex h-[52px] w-[52px] items-center justify-center rounded-xl bg-teal-600 text-[1.35rem] text-white","aria-hidden":"true",children:e.jsx("i",{className:"bx bx-plus-medical"})}),e.jsx("div",{className:"text-[1.125rem] font-bold tracking-tight text-slate-800",children:r("global.system_name")})]}),e.jsxs("div",{className:"mb-6 text-center",children:[e.jsx("h1",{className:"mb-1.5 text-xl font-semibold text-slate-800",children:r("global.sign_in")}),e.jsx("p",{className:"text-sm leading-relaxed text-slate-500",children:r("global.login")})]}),l?e.jsx("div",{className:"mb-4 rounded-lg border border-teal-200 bg-teal-50 px-3 py-2 text-sm text-teal-800",children:l}):null,e.jsxs("form",{onSubmit:m,noValidate:!0,children:[e.jsxs("div",{className:"relative mb-4",children:[e.jsxs("div",{className:"relative",children:[e.jsx("input",{id:"email",type:"email",name:"email",value:t.data.email,onChange:a=>t.setData("email",a.target.value),required:!0,autoComplete:"email",autoFocus:!0,placeholder:r("global.email"),className:`h-12 w-full rounded-lg border bg-slate-50 text-[0.9375rem] text-slate-800 transition-[border-color,box-shadow,background] placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:outline-none focus:ring-[3px] focus:ring-teal-600/15 ${s?"pe-10 ps-11":"ps-10 pe-3"} ${t.errors.email?"border-red-500/65 shadow-[0_0_0_2px_rgba(220,38,38,0.1)]":"border-slate-300"}`}),e.jsx("i",{className:`bx bx-envelope pointer-events-none absolute top-1/2 -translate-y-1/2 text-[1.05rem] ${t.data.email?"text-teal-600":"text-slate-400"} ${s?"end-3.5":"start-3.5"}`})]}),t.errors.email?e.jsx("span",{className:"mt-1.5 block px-0.5 text-[0.8125rem] text-red-600",children:e.jsx("strong",{children:t.errors.email})}):null]}),e.jsxs("div",{className:"relative mb-4",children:[e.jsxs("div",{className:"relative",children:[e.jsx("input",{id:"password",type:o?"text":"password",name:"password",value:t.data.password,onChange:a=>t.setData("password",a.target.value),required:!0,autoComplete:"current-password",placeholder:r("global.password"),className:`h-12 w-full rounded-lg border bg-slate-50 text-[0.9375rem] text-slate-800 transition-[border-color,box-shadow,background] placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:outline-none focus:ring-[3px] focus:ring-teal-600/15 ${s?"pe-10 ps-11":"ps-10 pe-11"} ${t.errors.password?"border-red-500/65 shadow-[0_0_0_2px_rgba(220,38,38,0.1)]":"border-slate-300"}`}),e.jsx("i",{className:`bx bx-lock-alt pointer-events-none absolute top-1/2 -translate-y-1/2 text-[1.05rem] ${t.data.password?"text-teal-600":"text-slate-400"} ${s?"end-3.5":"start-3.5"}`}),e.jsx("button",{type:"button",className:`absolute top-1/2 inline-flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-teal-600/10 hover:text-teal-600 ${s?"start-2":"end-2"}`,"aria-label":"Toggle password",onClick:()=>d(a=>!a),children:e.jsx("i",{className:`bx ${o?"bx-show":"bx-hide"}`})})]}),t.errors.password?e.jsx("span",{className:"mt-1.5 block px-0.5 text-[0.8125rem] text-red-600",children:e.jsx("strong",{children:t.errors.password})}):null]}),e.jsx("div",{className:"mb-5 mt-1 flex items-center justify-start",children:e.jsxs("label",{htmlFor:"remember-me",className:"inline-flex cursor-pointer select-none items-center gap-2 text-sm text-slate-500",children:[e.jsx("input",{id:"remember-me",type:"checkbox",name:"remember",checked:t.data.remember,onChange:a=>t.setData("remember",a.target.checked),className:"h-4 w-4 cursor-pointer accent-teal-600"}),e.jsx("span",{children:r("global.remember_me")})]})}),e.jsx("button",{type:"submit",disabled:t.processing,className:"relative inline-flex h-[46px] w-full items-center justify-center gap-1.5 rounded-lg border-0 bg-teal-600 text-[0.9375rem] font-semibold text-white transition-[background,transform] hover:bg-teal-700 active:translate-y-px disabled:pointer-events-none disabled:opacity-80",children:t.processing?e.jsx("span",{className:"inline-block h-5 w-5 animate-spin rounded-full border-2 border-white/90 border-t-transparent"}):e.jsxs(e.Fragment,{children:[e.jsx("span",{children:r("global.sign_in")}),e.jsx("i",{className:"bx bx-log-in"})]})})]})]})})]}),e.jsx("style",{children:`
                .login-shell {
                    color: #1e293b;
                    background:
                        radial-gradient(ellipse 80% 50% at 50% 0%, rgba(13, 148, 136, 0.07), transparent 55%),
                        radial-gradient(ellipse 70% 45% at 100% 100%, rgba(100, 116, 139, 0.06), transparent 50%),
                        #eef1f6;
                }
                .login-card {
                    animation: loginFadeUp 420ms ease-out;
                }
                @keyframes loginFadeUp {
                    from { opacity: 0; transform: translateY(12px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .login-bg-icon {
                    position: absolute;
                    color: #0d9488;
                    opacity: 0.11;
                    line-height: 1;
                    animation: loginBgFloat 14s ease-in-out infinite;
                }
                .login-bg-icon:nth-child(odd) { animation-duration: 18s; animation-delay: -2s; }
                .login-bg-icon:nth-child(3n) { color: #64748b; opacity: 0.09; }
                @keyframes loginBgFloat {
                    0%, 100% { transform: translateY(0) rotate(var(--r, 0deg)); }
                    50% { transform: translateY(-10px) rotate(var(--r, 0deg)); }
                }
                .login-bg-icon-1 { --r: -12deg; top: 8%; left: 6%; font-size: clamp(2.5rem, 6vw, 4rem); }
                .login-bg-icon-2 { --r: 8deg; top: 18%; right: 10%; font-size: clamp(2rem, 4.5vw, 3.2rem); animation-delay: -4s; }
                .login-bg-icon-3 { --r: 15deg; top: 42%; left: 3%; font-size: clamp(1.75rem, 4vw, 2.75rem); animation-delay: -1s; }
                .login-bg-icon-4 { --r: -6deg; top: 55%; right: 5%; font-size: clamp(2.25rem, 5vw, 3.5rem); animation-delay: -6s; }
                .login-bg-icon-5 { --r: 22deg; bottom: 28%; left: 12%; font-size: clamp(1.5rem, 3.5vw, 2.5rem); }
                .login-bg-icon-6 { --r: -18deg; bottom: 12%; right: 18%; font-size: clamp(2rem, 4vw, 3rem); animation-delay: -3s; }
                .login-bg-icon-7 { --r: -5deg; top: 65%; left: 22%; font-size: clamp(1.35rem, 3vw, 2rem); animation-delay: -5s; }
                .login-bg-icon-8 { --r: 10deg; top: 12%; left: 38%; font-size: clamp(1.25rem, 2.8vw, 1.85rem); opacity: 0.08; }
                .login-bg-icon-9 { --r: -14deg; bottom: 38%; right: 28%; font-size: clamp(1.6rem, 3.2vw, 2.4rem); animation-delay: -2.5s; }
                .login-bg-icon-10 { --r: 6deg; top: 32%; right: 22%; font-size: clamp(1.4rem, 3vw, 2.1rem); }
                .login-bg-icon-11 { --r: -20deg; bottom: 8%; left: 35%; font-size: clamp(1.8rem, 3.8vw, 2.8rem); animation-delay: -7s; }
                .login-bg-icon-12 { --r: 4deg; top: 48%; right: 38%; font-size: clamp(1.2rem, 2.5vw, 1.75rem); opacity: 0.07; }
                @media (max-width: 640px) {
                    .login-bg-icon-8, .login-bg-icon-12 { display: none; }
                }
            `})]})}export{y as default};
