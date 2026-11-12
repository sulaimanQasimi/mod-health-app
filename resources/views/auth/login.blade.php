<!DOCTYPE html>
<html lang="{{ session()->get('language', 'en') }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ localize('global.login') }}</title>
    <meta name="description" content="Modern Health App Login" />
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    @php
        $isRtl = session()->get('language') !== 'en';
        $logoPath = asset('images/logos/لوگوی جدید وزارت دفاع ملی.png');
        $bgPath = asset('images/loginbackground.png');
    @endphp

    @if (session()->get('language') == 'en')
        <style type="text/css">
            @font-face {
                font-family: "eng_font";
                src: url({{ asset('assets/fonts/eng.ttf') }});
            }

            body,
            body *,
            .label {
                font-family: eng_font, 'Segoe UI', sans-serif;
            }
        </style>
    @else
        <style type="text/css">
            @font-face {
                font-family: "persian_font";
                src: url({{ asset('assets/fonts/mod_font.ttf') }});
            }

            body,
            body *,
            .label {
                font-family: persian_font, 'Segoe UI', sans-serif;
            }
        </style>
    @endif

    <style>
        :root {
            --ink: #132536;
            --text: #1a3044;
            --text-muted: #5f7386;
            --field-border: rgba(30, 58, 76, 0.18);
            --field-bg: rgba(255, 255, 255, 0.78);
            --accent: #1a6b72;
            --accent-deep: #14555b;
            --accent-soft: rgba(26, 107, 114, 0.14);
            --danger: #c2410c;
            /* Hospital image palette: sky → stone → dusk */
            --panel-light: #eef4f8;
            --panel-mid: #d5e2ec;
            --panel-dark: #9eb4c6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            color: var(--text);
            background: #0d1b28;
            overflow-x: hidden;
        }

        .login-layout {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.35fr minmax(340px, 440px);
            direction: ltr; /* keep hospital left, form right in all languages */
        }

        .login-visual {
            position: relative;
            overflow: hidden;
            min-height: 100vh;
        }

        .login-visual__image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            transform: scale(1.03);
            animation: sceneDrift 28s ease-in-out infinite alternate;
            filter: saturate(1.06) contrast(1.03);
        }

        .login-visual__veil {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(8, 22, 36, 0.18) 0%, rgba(8, 22, 36, 0.08) 55%, rgba(13, 40, 52, 0.35) 100%),
                linear-gradient(180deg, rgba(8, 22, 36, 0.12) 0%, transparent 30%, rgba(8, 22, 36, 0.28) 100%);
            pointer-events: none;
        }

        @keyframes sceneDrift {
            from { transform: scale(1.03) translate3d(0, 0, 0); }
            to { transform: scale(1.07) translate3d(-1%, -0.5%, 0); }
        }

        .login-aside {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: clamp(1.5rem, 4vh, 2.75rem) clamp(1.25rem, 3vw, 2.25rem);
            background:
                linear-gradient(
                    165deg,
                    var(--panel-light) 0%,
                    var(--panel-mid) 48%,
                    var(--panel-dark) 100%
                );
            box-shadow: -18px 0 40px rgba(6, 18, 28, 0.22);
            animation: panelIn 650ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        .login-aside::before {
            content: '';
            position: absolute;
            inset: 0;
            pointer-events: none;
            background:
                radial-gradient(ellipse 90% 55% at 50% -10%, rgba(255, 255, 255, 0.55), transparent 60%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.22), transparent 28%);
        }

        @keyframes panelIn {
            from {
                opacity: 0;
                transform: translateX(18px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .login-aside__inner {
            position: relative;
            width: 100%;
            max-width: 360px;
            margin-inline: auto;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
        }

        .brand-top {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-logo {
            width: 88px;
            height: 88px;
            margin: 0 auto 0.85rem;
            border-radius: 50%;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.35);
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.55),
                0 10px 28px rgba(18, 48, 72, 0.18);
            animation: logoGlow 4.8s ease-in-out infinite;
        }

        @keyframes logoGlow {
            0%, 100% {
                box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.55), 0 10px 28px rgba(18, 48, 72, 0.18);
            }
            50% {
                box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.7), 0 12px 32px rgba(18, 48, 72, 0.22), 0 0 24px rgba(158, 180, 198, 0.45);
            }
        }

        .brand-name {
            font-size: clamp(1.35rem, 2.4vw, 1.65rem);
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -0.02em;
            line-height: 1.25;
            margin-bottom: 0.3rem;
        }

        .brand-tagline {
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.45;
        }

        .form-head {
            margin-bottom: 1.1rem;
        }

        .form-head h1 {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--ink);
        }

        .field {
            margin-bottom: 0.95rem;
            position: relative;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            {{ $isRtl ? 'right' : 'left' }}: 0.9rem;
            color: #7a8d9d;
            font-size: 1.08rem;
            pointer-events: none;
            transition: color 180ms ease;
        }

        .form-input {
            width: 100%;
            height: 50px;
            border-radius: 12px;
            border: 1px solid var(--field-border);
            background: var(--field-bg);
            color: var(--text);
            font-size: 0.95rem;
            padding: 0.75rem 2.7rem;
            transition: border-color 180ms ease, box-shadow 180ms ease, background 180ms ease;
        }

        .form-input::placeholder {
            color: #8799a8;
        }

        .form-input:focus {
            outline: none;
            border-color: rgba(26, 107, 114, 0.55);
            box-shadow: 0 0 0 4px var(--accent-soft);
            background: #fff;
        }

        .form-input:focus + .input-icon,
        .field.has-value .input-icon {
            color: var(--accent);
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            {{ $isRtl ? 'left' : 'right' }}: 0.45rem;
            width: 38px;
            height: 38px;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: #7a8d9d;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 160ms ease, color 160ms ease;
        }

        .password-toggle:hover {
            background: var(--accent-soft);
            color: var(--accent);
        }

        .meta-row {
            margin: 0.15rem 0 1.15rem;
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: var(--text-muted);
            font-size: 0.875rem;
            cursor: pointer;
            user-select: none;
        }

        .remember input {
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            border: none;
            border-radius: 12px;
            height: 50px;
            font-size: 0.98rem;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(135deg, #2a858d 0%, var(--accent) 55%, var(--accent-deep) 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
            position: relative;
            box-shadow: 0 10px 22px rgba(20, 85, 91, 0.28);
        }

        .btn-login:hover {
            filter: brightness(1.05);
            box-shadow: 0 14px 26px rgba(20, 85, 91, 0.34);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        .btn-login.loading {
            pointer-events: none;
            color: transparent;
        }

        .btn-login.loading::after {
            content: '';
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.9);
            border-top-color: transparent;
            position: absolute;
            animation: spin 650ms linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .invalid-feedback {
            display: block;
            color: var(--danger);
            font-size: 0.8125rem;
            margin-top: 0.4rem;
            padding-inline: 0.15rem;
        }

        .form-input.is-invalid {
            border-color: rgba(194, 65, 12, 0.55);
            box-shadow: 0 0 0 3px rgba(194, 65, 12, 0.12);
        }

        @media (max-width: 900px) {
            .login-layout {
                grid-template-columns: 1fr;
            }

            .login-visual {
                min-height: 34vh;
                max-height: 38vh;
            }

            .login-aside {
                min-height: auto;
                flex: 1;
                box-shadow: none;
                animation-name: panelInUp;
            }

            @keyframes panelInUp {
                from {
                    opacity: 0;
                    transform: translateY(16px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .login-visual__image,
            .brand-logo,
            .login-aside {
                animation: none;
            }
        }
    </style>
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <main class="login-layout">
        <section class="login-visual" aria-hidden="true">
            <img
                class="login-visual__image"
                src="{{ $bgPath }}"
                alt=""
                decoding="async"
                fetchpriority="high">
            <div class="login-visual__veil"></div>
        </section>

        <aside class="login-aside">
            <div class="login-aside__inner">
                <header class="brand-top">
                    <img
                        class="brand-logo"
                        src="{{ $logoPath }}"
                        alt="{{ localize('global.system_name') }}"
                        width="88"
                        height="88">
                    <div class="brand-name">{{ localize('global.system_name') }}</div>
                    <p class="brand-tagline">{{ localize('global.login') }}</p>
                </header>

                <div class="form-head">
                    <h1>{{ localize('global.sign_in') }}</h1>
                </div>

                <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                    @csrf

                    <div class="field {{ old('email') ? 'has-value' : '' }}">
                        <div class="input-wrap">
                            <input
                                id="email"
                                type="email"
                                class="form-input @error('email') is-invalid @enderror"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                autofocus
                                placeholder="{{ localize('global.email') }}">
                            <i class='bx bx-envelope input-icon'></i>
                        </div>
                        @if ($errors->has('email'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="field">
                        <div class="input-wrap">
                            <input
                                id="password"
                                type="password"
                                class="form-input @error('password') is-invalid @enderror"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="{{ localize('global.password') }}">
                            <i class='bx bx-lock-alt input-icon'></i>
                            <button type="button" class="password-toggle" id="passwordToggle" aria-label="Toggle password">
                                <i class='bx bx-hide' id="toggleIcon"></i>
                            </button>
                        </div>
                        @if ($errors->has('password'))
                            <span class="invalid-feedback">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>

                    <div class="meta-row">
                        <label class="remember" for="remember-me">
                            <input type="checkbox" id="remember-me" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>{{ localize('global.remember_me') }}</span>
                        </label>
                    </div>

                    <button type="submit" class="btn-login">
                        <span>{{ localize('global.sign_in') }}</span>
                        <i class='bx bx-log-in'></i>
                    </button>
                </form>
            </div>
        </aside>
    </main>

    <script>
        const loginForm = document.getElementById('loginForm');
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        const toggleIcon = document.getElementById('toggleIcon');

        passwordToggle.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleIcon.classList.toggle('bx-hide', !isPassword);
            toggleIcon.classList.toggle('bx-show', isPassword);
        });

        loginForm.addEventListener('submit', function () {
            const submitButton = this.querySelector('.btn-login');
            submitButton.classList.add('loading');
        });

        document.querySelectorAll('.form-input').forEach(function (input) {
            input.addEventListener('input', function () {
                this.closest('.field').classList.toggle('has-value', this.value.trim().length > 0);
            });
        });
    </script>
</body>

</html>
