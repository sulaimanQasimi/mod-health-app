<!DOCTYPE html>
<html lang="{{ session()->get('language', 'en') }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ localize('global.login') }}</title>
    <meta name="description" content="{{ localize('global.system_name') }}" />
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
            --text: #14233a;
            --text-soft: #334155;
            --text-muted: #64748b;
            --border: rgba(20, 35, 58, 0.12);
            --field-bg: rgba(248, 250, 252, 0.92);
            --accent: #0f766e;
            --accent-deep: #0b5f59;
            --accent-soft: rgba(15, 118, 110, 0.12);
            --danger: #b91c1c;
            --panel: rgba(255, 255, 255, 0.88);
            --panel-solid: #ffffff;
            --line: rgba(255, 255, 255, 0.28);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            color: var(--text);
            background: #08111c;
            overflow-x: hidden;
        }

        .login-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .login-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transform: scale(1.04);
            animation: bgDrift 18s ease-in-out infinite alternate;
        }

        .login-bg::before,
        .login-bg::after {
            content: '';
            position: absolute;
            inset: 0;
        }

        .login-bg::before {
            background:
                linear-gradient(115deg, rgba(8, 17, 28, 0.72) 0%, rgba(8, 17, 28, 0.28) 48%, rgba(11, 50, 48, 0.45) 100%);
        }

        .login-bg::after {
            background:
                radial-gradient(ellipse 70% 55% at 18% 30%, rgba(15, 118, 110, 0.22), transparent 60%),
                radial-gradient(ellipse 50% 40% at 85% 75%, rgba(20, 35, 58, 0.35), transparent 55%);
            pointer-events: none;
        }

        .login-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(320px, 460px);
            align-items: stretch;
            gap: 0;
            padding: 0;
        }

        .login-hero {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: clamp(1.75rem, 4vw, 3.5rem);
            color: #f8fafc;
        }

        .hero-top {
            display: inline-flex;
            align-items: center;
            gap: 0.85rem;
            opacity: 0;
            animation: riseIn 700ms ease-out 80ms forwards;
        }

        .hero-top img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            filter: drop-shadow(0 8px 18px rgba(0, 0, 0, 0.28));
        }

        .hero-top span {
            font-size: 0.95rem;
            letter-spacing: 0.02em;
            color: rgba(248, 250, 252, 0.86);
        }

        .hero-copy {
            max-width: 34rem;
            margin-top: auto;
            margin-bottom: auto;
            opacity: 0;
            animation: riseIn 800ms ease-out 180ms forwards;
        }

        .hero-copy h1 {
            font-size: clamp(2rem, 4.2vw, 3.35rem);
            line-height: 1.15;
            font-weight: 700;
            margin-bottom: 0.85rem;
            text-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
        }

        .hero-copy p {
            font-size: clamp(1rem, 1.5vw, 1.15rem);
            line-height: 1.7;
            color: rgba(248, 250, 252, 0.82);
            max-width: 28rem;
        }

        .hero-foot {
            font-size: 0.8rem;
            color: rgba(248, 250, 252, 0.55);
            opacity: 0;
            animation: riseIn 700ms ease-out 320ms forwards;
        }

        .login-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1.25rem, 3vw, 2.5rem);
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            border-{{ $isRtl ? 'right' : 'left' }}: 1px solid var(--line);
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--panel);
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 18px;
            padding: 2rem 1.75rem 1.65rem;
            box-shadow: 0 18px 40px rgba(8, 17, 28, 0.22);
            opacity: 0;
            animation: cardIn 650ms ease-out 120ms forwards;
        }

        .brand {
            text-align: center;
            margin-bottom: 1.6rem;
        }

        .brand-mark {
            width: 78px;
            height: 78px;
            margin: 0 auto 0.95rem;
            border-radius: 18px;
            background: linear-gradient(160deg, #ffffff 0%, #eef7f6 100%);
            border: 1px solid rgba(15, 118, 110, 0.14);
            display: grid;
            place-items: center;
            overflow: hidden;
        }

        .brand-mark img {
            width: 62px;
            height: 62px;
            object-fit: contain;
        }

        .brand h2 {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1.3;
            margin-bottom: 0.3rem;
        }

        .brand p {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .field {
            margin-bottom: 0.95rem;
            opacity: 0;
            animation: riseIn 550ms ease-out forwards;
        }

        .field:nth-child(1) { animation-delay: 260ms; }
        .field:nth-child(2) { animation-delay: 340ms; }

        .field-label {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-soft);
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            {{ $isRtl ? 'right' : 'left' }}: 0.9rem;
            color: #94a3b8;
            font-size: 1.05rem;
            pointer-events: none;
            transition: color 160ms ease;
        }

        .form-input {
            width: 100%;
            height: 48px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: var(--field-bg);
            color: var(--text);
            font-size: 0.95rem;
            padding: 0.7rem 2.65rem;
            transition: border-color 160ms ease, box-shadow 160ms ease, background 160ms ease, transform 160ms ease;
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .form-input:hover {
            border-color: rgba(15, 118, 110, 0.28);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            background: var(--panel-solid);
            box-shadow: 0 0 0 3px var(--accent-soft);
        }

        .form-input:focus ~ .input-icon {
            color: var(--accent);
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            {{ $isRtl ? 'left' : 'right' }}: 0.4rem;
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 9px;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 150ms ease, background 150ms ease;
        }

        .password-toggle:hover {
            color: var(--accent);
            background: var(--accent-soft);
        }

        .meta-row {
            margin: 0.1rem 0 1.2rem;
            opacity: 0;
            animation: riseIn 550ms ease-out 420ms forwards;
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
            height: 48px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-deep) 100%);
            color: #fff;
            font-size: 0.98rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            position: relative;
            transition: transform 160ms ease, filter 160ms ease;
            opacity: 0;
            animation: riseIn 550ms ease-out 500ms forwards;
        }

        .btn-login:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login.loading {
            pointer-events: none;
            color: transparent;
        }

        .btn-login.loading::after {
            content: '';
            width: 18px;
            height: 18px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.9);
            border-top-color: transparent;
            position: absolute;
            animation: spin 650ms linear infinite;
        }

        .invalid-feedback {
            display: block;
            color: var(--danger);
            font-size: 0.8rem;
            margin-top: 0.35rem;
        }

        .form-input.is-invalid {
            border-color: rgba(185, 28, 28, 0.55);
        }

        .card-foot {
            margin-top: 1.25rem;
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted);
            opacity: 0;
            animation: riseIn 550ms ease-out 580ms forwards;
        }

        @keyframes bgDrift {
            from { transform: scale(1.04) translate3d(0, 0, 0); }
            to { transform: scale(1.1) translate3d(-1.2%, -0.8%, 0); }
        }

        @keyframes riseIn {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes cardIn {
            from {
                opacity: 0;
                transform: translateY(18px) scale(0.985);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 920px) {
            .login-page {
                grid-template-columns: 1fr;
                align-content: center;
                padding: 1.25rem 1rem 1.5rem;
            }

            .login-hero {
                padding: 0.5rem 0.25rem 1.25rem;
                text-align: center;
                align-items: center;
            }

            .hero-top {
                justify-content: center;
            }

            .hero-copy {
                margin: 1rem 0 0.5rem;
            }

            .hero-copy h1 {
                font-size: clamp(1.65rem, 7vw, 2.2rem);
            }

            .hero-copy p,
            .hero-foot {
                display: none;
            }

            .login-panel {
                background: transparent;
                border: 0;
                backdrop-filter: none;
                padding: 0;
            }

            .login-card {
                background: var(--panel-solid);
            }

            .brand-mark {
                display: none;
            }
        }

        @media (max-width: 420px) {
            .login-card {
                padding: 1.45rem 1.15rem 1.25rem;
                border-radius: 14px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .login-bg img,
            .login-card,
            .hero-top,
            .hero-copy,
            .hero-foot,
            .field,
            .meta-row,
            .btn-login,
            .card-foot {
                animation: none !important;
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="login-bg" aria-hidden="true">
        <img src="{{ $bgPath }}" alt="" decoding="async" fetchpriority="high">
    </div>

    <main class="login-page">
        <section class="login-hero" aria-label="{{ localize('global.system_name') }}">
            <div class="hero-top">
                <img src="{{ $logoPath }}" alt="" width="56" height="56">
                <span>{{ localize('global.system_name') }}</span>
            </div>

            <div class="hero-copy">
                <h1>{{ localize('global.system_name') }}</h1>
                <p>{{ localize('global.sign_in') }}</p>
            </div>

            <div class="hero-foot">
                &copy; {{ date('Y') }} {{ localize('global.system_name') }}
            </div>
        </section>

        <section class="login-panel">
            <article class="login-card">
                <header class="brand">
                    <div class="brand-mark">
                        <img src="{{ $logoPath }}" alt="{{ localize('global.system_name') }}" width="62" height="62">
                    </div>
                    <h2>{{ localize('global.sign_in') }}</h2>
                    <p>{{ localize('global.system_name') }}</p>
                </header>

                <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                    @csrf

                    <div class="field">
                        <label class="field-label" for="email">{{ localize('global.email') }}</label>
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
                        <label class="field-label" for="password">{{ localize('global.password') }}</label>
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
                        <i class='bx bx-{{ $isRtl ? 'left' : 'right' }}-arrow-alt'></i>
                    </button>
                </form>

                <p class="card-foot">&copy; {{ date('Y') }} {{ localize('global.system_name') }}</p>
            </article>
        </section>
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
            this.querySelector('.btn-login').classList.add('loading');
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>

</html>
