import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { useTranslation } from '../../hooks/useTranslation';
import { SharedPageProps } from '../../types';

interface LoginProps {
    status?: string | null;
}

const BG_ICONS = [
    'bx-plus-medical',
    'bx-heart',
    'bx-clipboard',
    'bx-shield-quarter',
    'bx-pulse',
    'bx-user-circle',
    'bx-band-aid',
    'bx-capsule',
    'bx-first-aid',
    'bx-injection',
    'bx-health',
    'bx-donate-heart',
] as const;

export default function Login({ status }: LoginProps) {
    const { t, direction } = useTranslation();
    const { urls } = usePage<SharedPageProps>().props;
    const isRtl = direction === 'rtl';
    const [showPassword, setShowPassword] = useState(false);

    const form = useForm({
        email: '',
        password: '',
        remember: false as boolean,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(urls.login, {
            onFinish: () => form.reset('password'),
        });
    };

    return (
        <>
            <Head title={t('global.login')} />

            <div className="login-shell relative min-h-screen overflow-x-hidden text-slate-800">
                <div className="pointer-events-none fixed inset-0 z-0 overflow-hidden" aria-hidden="true">
                    {BG_ICONS.map((icon, index) => (
                        <i
                            key={icon}
                            className={`bx ${icon} login-bg-icon login-bg-icon-${index + 1}`}
                        />
                    ))}
                </div>

                <main className="relative z-10 flex min-h-screen items-center justify-center px-4 py-6">
                    <article className="login-card w-full max-w-[400px] rounded-xl border border-slate-200 bg-white px-7 py-8 shadow-[0_1px_3px_rgba(15,23,42,0.06),0_8px_24px_rgba(15,23,42,0.06)]">
                        <div className="mb-7 text-center">
                            <div
                                className="mx-auto mb-3.5 inline-flex h-[52px] w-[52px] items-center justify-center rounded-xl bg-teal-600 text-[1.35rem] text-white"
                                aria-hidden="true"
                            >
                                <i className="bx bx-plus-medical" />
                            </div>
                            <div className="text-[1.125rem] font-bold tracking-tight text-slate-800">
                                {t('global.system_name')}
                            </div>
                        </div>

                        <div className="mb-6 text-center">
                            <h1 className="mb-1.5 text-xl font-semibold text-slate-800">
                                {t('global.sign_in')}
                            </h1>
                            <p className="text-sm leading-relaxed text-slate-500">{t('global.login')}</p>
                        </div>

                        {status ? (
                            <div className="mb-4 rounded-lg border border-teal-200 bg-teal-50 px-3 py-2 text-sm text-teal-800">
                                {status}
                            </div>
                        ) : null}

                        <form onSubmit={submit} noValidate>
                            <div className="relative mb-4">
                                <div className="relative">
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value={form.data.email}
                                        onChange={(e) => form.setData('email', e.target.value)}
                                        required
                                        autoComplete="email"
                                        autoFocus
                                        placeholder={t('global.email')}
                                        className={`h-12 w-full rounded-lg border bg-slate-50 text-[0.9375rem] text-slate-800 transition-[border-color,box-shadow,background] placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:outline-none focus:ring-[3px] focus:ring-teal-600/15 ${
                                            isRtl ? 'pe-10 ps-11' : 'ps-10 pe-3'
                                        } ${
                                            form.errors.email
                                                ? 'border-red-500/65 shadow-[0_0_0_2px_rgba(220,38,38,0.1)]'
                                                : 'border-slate-300'
                                        }`}
                                    />
                                    <i
                                        className={`bx bx-envelope pointer-events-none absolute top-1/2 -translate-y-1/2 text-[1.05rem] ${
                                            form.data.email ? 'text-teal-600' : 'text-slate-400'
                                        } ${isRtl ? 'end-3.5' : 'start-3.5'}`}
                                    />
                                </div>
                                {form.errors.email ? (
                                    <span className="mt-1.5 block px-0.5 text-[0.8125rem] text-red-600">
                                        <strong>{form.errors.email}</strong>
                                    </span>
                                ) : null}
                            </div>

                            <div className="relative mb-4">
                                <div className="relative">
                                    <input
                                        id="password"
                                        type={showPassword ? 'text' : 'password'}
                                        name="password"
                                        value={form.data.password}
                                        onChange={(e) => form.setData('password', e.target.value)}
                                        required
                                        autoComplete="current-password"
                                        placeholder={t('global.password')}
                                        className={`h-12 w-full rounded-lg border bg-slate-50 text-[0.9375rem] text-slate-800 transition-[border-color,box-shadow,background] placeholder:text-slate-400 focus:border-teal-600 focus:bg-white focus:outline-none focus:ring-[3px] focus:ring-teal-600/15 ${
                                            isRtl ? 'pe-10 ps-11' : 'ps-10 pe-11'
                                        } ${
                                            form.errors.password
                                                ? 'border-red-500/65 shadow-[0_0_0_2px_rgba(220,38,38,0.1)]'
                                                : 'border-slate-300'
                                        }`}
                                    />
                                    <i
                                        className={`bx bx-lock-alt pointer-events-none absolute top-1/2 -translate-y-1/2 text-[1.05rem] ${
                                            form.data.password ? 'text-teal-600' : 'text-slate-400'
                                        } ${isRtl ? 'end-3.5' : 'start-3.5'}`}
                                    />
                                    <button
                                        type="button"
                                        className={`absolute top-1/2 inline-flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition-colors hover:bg-teal-600/10 hover:text-teal-600 ${
                                            isRtl ? 'start-2' : 'end-2'
                                        }`}
                                        aria-label="Toggle password"
                                        onClick={() => setShowPassword((value) => !value)}
                                    >
                                        <i className={`bx ${showPassword ? 'bx-show' : 'bx-hide'}`} />
                                    </button>
                                </div>
                                {form.errors.password ? (
                                    <span className="mt-1.5 block px-0.5 text-[0.8125rem] text-red-600">
                                        <strong>{form.errors.password}</strong>
                                    </span>
                                ) : null}
                            </div>

                            <div className="mb-5 mt-1 flex items-center justify-start">
                                <label
                                    htmlFor="remember-me"
                                    className="inline-flex cursor-pointer select-none items-center gap-2 text-sm text-slate-500"
                                >
                                    <input
                                        id="remember-me"
                                        type="checkbox"
                                        name="remember"
                                        checked={form.data.remember}
                                        onChange={(e) => form.setData('remember', e.target.checked)}
                                        className="h-4 w-4 cursor-pointer accent-teal-600"
                                    />
                                    <span>{t('global.remember_me')}</span>
                                </label>
                            </div>

                            <button
                                type="submit"
                                disabled={form.processing}
                                className="relative inline-flex h-[46px] w-full items-center justify-center gap-1.5 rounded-lg border-0 bg-teal-600 text-[0.9375rem] font-semibold text-white transition-[background,transform] hover:bg-teal-700 active:translate-y-px disabled:pointer-events-none disabled:opacity-80"
                            >
                                {form.processing ? (
                                    <span className="inline-block h-5 w-5 animate-spin rounded-full border-2 border-white/90 border-t-transparent" />
                                ) : (
                                    <>
                                        <span>{t('global.sign_in')}</span>
                                        <i className="bx bx-log-in" />
                                    </>
                                )}
                            </button>
                        </form>
                    </article>
                </main>
            </div>

            <style>{`
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
            `}</style>
        </>
    );
}
