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
            --ink: #0f1c2e;
            --text: #122033;
            --text-muted: #5b6b7c;
            --glass: rgba(255, 252, 248, 0.88);
            --glass-border: rgba(255, 255, 255, 0.55);
            --field-border: rgba(18, 48, 72, 0.16);
            --field-bg: rgba(255, 255, 255, 0.72);
            --accent: #0f766e;
            --accent-deep: #0b5f59;
            --accent-soft: rgba(15, 118, 110, 0.14);
            --danger: #c2410c;
            --warm-glow: rgba(212, 163, 84, 0.28);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            color: var(--text);
            background: #0b1724;
            overflow-x: hidden;
        }

        .login-scene {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .login-scene__image {
            position: absolute;
            inset: -2%;
            width: 104%;
            height: 104%;
            object-fit: cover;
            object-position: center center;
            transform: scale(1.04);
            animation: sceneDrift 28s ease-in-out infinite alternate;
            filter: saturate(1.05) contrast(1.02);
        }

        .login-scene__veil {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(120deg, rgba(8, 22, 36, 0.72) 0%, rgba(8, 22, 36, 0.42) 42%, rgba(12, 40, 48, 0.55) 100%),
                radial-gradient(ellipse 70% 55% at 70% 20%, var(--warm-glow), transparent 60%),
                radial-gradient(ellipse 80% 70% at 50% 100%, rgba(8, 22, 36, 0.55), transparent 55%);
        }

        .login-scene__grain {
            position: absolute;
            inset: 0;
            opacity: 0.08;
            pointer-events: none;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='2' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
            background-size: 180px 180px;
            mix-blend-mode: soft-light;
        }

        @keyframes sceneDrift {
            from { transform: scale(1.04) translate3d(0, 0, 0); }
            to { transform: scale(1.08) translate3d(-1.2%, -0.6%, 0); }
        }

        .login-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: clamp(1.25rem, 4vw, 2.5rem) 1rem;
        }

        .login-shell {
            width: min(100%, 440px);
            animation: riseIn 700ms cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes riseIn {
            from {
                opacity: 0;
                transform: translateY(22px) scale(0.985);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .brand-hero {
            text-align: center;
            margin-bottom: 1.35rem;
            color: #f7f4ef;
            text-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }

        .brand-logo {
            width: clamp(72px, 16vw, 92px);
            height: clamp(72px, 16vw, 92px);
            margin: 0 auto 0.9rem;
            border-radius: 50%;
            object-fit: cover;
            background: rgba(255, 255, 255, 0.08);
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.28),
                0 12px 36px rgba(0, 0, 0, 0.35);
            animation: logoGlow 4.5s ease-in-out infinite;
        }

        @keyframes logoGlow {
            0%, 100% { box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.28), 0 12px 36px rgba(0, 0, 0, 0.35); }
            50% { box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.4), 0 14px 42px rgba(0, 0, 0, 0.4), 0 0 28px rgba(212, 163, 84, 0.25); }
        }

        .brand-name {
            font-size: clamp(1.55rem, 3.6vw, 2rem);
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin-bottom: 0.4rem;
        }

        .brand-tagline {
            font-size: 0.95rem;
            color: rgba(247, 244, 239, 0.82);
            line-height: 1.5;
        }

        .login-panel {
            background: var(--glass);
            border: 1px solid var(--glass-border);
            border-radius: 22px;
            backdrop-filter: blur(18px) saturate(1.15);
            -webkit-backdrop-filter: blur(18px) saturate(1.15);
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.55) inset,
                0 24px 60px rgba(4, 14, 24, 0.28);
            padding: clamp(1.4rem, 3.5vw, 1.85rem);
        }

        .form-head {
            margin-bottom: 1.15rem;
        }

        .form-head h1 {
            font-size: 1.2rem;
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
            color: #7d8b99;
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
            color: #8a97a5;
        }

        .form-input:focus {
            outline: none;
            border-color: rgba(15, 118, 110, 0.55);
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
            color: #7d8b99;
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
            background:
                linear-gradient(135deg, #129287 0%, var(--accent) 55%, var(--accent-deep) 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
            position: relative;
            box-shadow: 0 10px 24px rgba(15, 118, 110, 0.28);
        }

        .btn-login:hover {
            filter: brightness(1.05);
            box-shadow: 0 14px 28px rgba(15, 118, 110, 0.34);
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

        @media (max-width: 480px) {
            .login-panel {
                border-radius: 18px;
                padding: 1.25rem 1.1rem 1.2rem;
            }

            .brand-name {
                font-size: 1.4rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .login-scene__image,
            .brand-logo,
            .login-shell {
                animation: none;
            }
        }
    </style>
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="login-scene" aria-hidden="true">
        <img
            class="login-scene__image"
            src="{{ $bgPath }}"
            alt=""
            decoding="async"
            fetchpriority="high">
        <div class="login-scene__veil"></div>
        <div class="login-scene__grain"></div>
    </div>

    <main class="login-page">
        <div class="login-shell">
            <header class="brand-hero">
                <img
                    class="brand-logo"
                    src="{{ $logoPath }}"
                    alt="{{ localize('global.system_name') }}"
                    width="92"
                    height="92">
                <div class="brand-name">{{ localize('global.system_name') }}</div>
                <p class="brand-tagline">{{ localize('global.login') }}</p>
            </header>

            <article class="login-panel">
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
            </article>
        </div>
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
