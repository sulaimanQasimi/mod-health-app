<!DOCTYPE html>
<html lang="{{ session()->get('language', 'en') }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ localize('global.login') }} — {{ localize('global.system_name') }}</title>
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
            body * {
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
            body * {
                font-family: persian_font, 'Segoe UI', sans-serif;
            }
        </style>
    @endif

    <style>
        :root {
            --ink: #152238;
            --ink-soft: #3d4f66;
            --muted: #6b7c90;
            --line: #d5deea;
            --field: #f3f6fa;
            --gold: #b0893c;
            --gold-soft: #d4b56a;
            --navy: #1a3358;
            --navy-hover: #122844;
            --danger: #b42318;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            color: var(--ink);
            background: #0d1a2b;
        }

        .scene {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .scene img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 42%;
            transform: scale(1.02);
            animation: sceneDrift 22s ease-in-out infinite alternate;
        }

        .scene::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(12, 24, 40, 0.22) 0%, rgba(12, 24, 40, 0.08) 38%, rgba(10, 20, 34, 0.62) 100%);
        }

        .scene::after {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 50% 45%, transparent 28%, rgba(8, 16, 28, 0.42) 100%);
            pointer-events: none;
        }

        .page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
        }

        .shell {
            width: 100%;
            max-width: 440px;
            animation: enter 560ms ease-out;
        }

        .card {
            position: relative;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, #ffffff 100%);
            border-radius: 6px;
            padding: 2.45rem 2.2rem 1.95rem;
            box-shadow:
                0 1px 0 rgba(255, 255, 255, 0.65) inset,
                0 18px 48px rgba(8, 16, 28, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.55);
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, var(--navy) 0%, var(--gold) 50%, var(--navy) 100%);
        }

        .card::after {
            content: '';
            position: absolute;
            inset: 10px;
            border: 1px solid rgba(176, 137, 60, 0.18);
            border-radius: 3px;
            pointer-events: none;
        }

        .brand {
            position: relative;
            z-index: 1;
            text-align: center;
            margin-bottom: 1.85rem;
        }

        .logo-wrap {
            width: 100px;
            height: 100px;
            margin: 0 auto 1.05rem;
            border-radius: 50%;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at 35% 30%, #ffffff 0%, #f4f7fb 70%);
            border: 1px solid rgba(176, 137, 60, 0.35);
            box-shadow:
                0 0 0 4px rgba(176, 137, 60, 0.08),
                0 10px 24px rgba(21, 34, 56, 0.08);
        }

        .logo-wrap img {
            width: 74px;
            height: 74px;
            object-fit: contain;
            display: block;
        }

        .brand h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.35;
            letter-spacing: 0.01em;
        }

        .brand .rule {
            width: 48px;
            height: 2px;
            margin: 0.8rem auto 0.75rem;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        .brand p {
            font-size: 0.9rem;
            color: var(--muted);
        }

        form {
            position: relative;
            z-index: 1;
        }

        .field {
            margin-bottom: 1.05rem;
        }

        .field label {
            display: block;
            margin-bottom: 0.42rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--ink-soft);
            letter-spacing: 0.02em;
        }

        .control {
            position: relative;
        }

        .control i.leading {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            {{ $isRtl ? 'right' : 'left' }}: 0.9rem;
            color: #8a9bb0;
            font-size: 1.05rem;
            pointer-events: none;
            transition: color 150ms ease;
        }

        .control input {
            width: 100%;
            height: 48px;
            border: 1px solid var(--line);
            border-radius: 4px;
            background: var(--field);
            color: var(--ink);
            font-size: 0.94rem;
            padding: 0.65rem 2.65rem;
            transition: border-color 150ms ease, background 150ms ease, box-shadow 150ms ease;
        }

        .control input::placeholder {
            color: #9aabc0;
        }

        .control input:hover {
            border-color: #c3cedc;
        }

        .control input:focus {
            outline: none;
            border-color: var(--navy);
            background: var(--white);
            box-shadow: 0 0 0 3px rgba(26, 51, 88, 0.1);
        }

        .control input:focus ~ i.leading {
            color: var(--navy);
        }

        .toggle {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            {{ $isRtl ? 'left' : 'right' }}: 0.35rem;
            width: 34px;
            height: 34px;
            border: 0;
            background: transparent;
            color: #8a9bb0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: color 150ms ease, background 150ms ease;
        }

        .toggle:hover {
            color: var(--navy);
            background: rgba(26, 51, 88, 0.06);
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0.2rem 0 1.35rem;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.86rem;
            color: var(--muted);
            cursor: pointer;
            user-select: none;
        }

        .remember input {
            width: 15px;
            height: 15px;
            accent-color: var(--navy);
            cursor: pointer;
        }

        .submit {
            width: 100%;
            height: 48px;
            border: 0;
            border-radius: 4px;
            background: linear-gradient(180deg, #21406c 0%, var(--navy) 100%);
            color: var(--white);
            font-size: 0.96rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            cursor: pointer;
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            transition: background 150ms ease, transform 150ms ease;
        }

        .submit:hover {
            background: linear-gradient(180deg, #1b365c 0%, var(--navy-hover) 100%);
            transform: translateY(-1px);
        }

        .submit:active {
            transform: translateY(0);
        }

        .submit i {
            font-size: 1.05rem;
            opacity: 0.9;
        }

        .submit.loading {
            pointer-events: none;
            color: transparent;
        }

        .submit.loading i {
            opacity: 0;
        }

        .submit.loading::after {
            content: '';
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.85);
            border-top-color: transparent;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            margin: -9px 0 0 -9px;
            animation: spin 0.65s linear infinite;
        }

        .invalid-feedback {
            display: block;
            margin-top: 0.35rem;
            color: var(--danger);
            font-size: 0.78rem;
        }

        .control input.is-invalid {
            border-color: rgba(180, 35, 24, 0.55);
        }

        .secure-note {
            position: relative;
            z-index: 1;
            margin-top: 1.15rem;
            padding-top: 1rem;
            border-top: 1px solid #e8eef5;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            color: var(--muted);
            font-size: 0.78rem;
        }

        .secure-note i {
            color: var(--gold);
            font-size: 0.95rem;
        }

        .footer {
            margin-top: 1.2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.82);
            font-size: 0.78rem;
            text-shadow: 0 1px 10px rgba(0, 0, 0, 0.4);
        }

        @keyframes sceneDrift {
            from { transform: scale(1.02) translate3d(0, 0, 0); }
            to { transform: scale(1.06) translate3d(-0.8%, -0.5%, 0); }
        }

        @keyframes enter {
            from {
                opacity: 0;
                transform: translateY(14px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .card {
                padding: 1.85rem 1.3rem 1.5rem;
            }

            .card::after {
                inset: 8px;
            }

            .logo-wrap {
                width: 88px;
                height: 88px;
            }

            .logo-wrap img {
                width: 64px;
                height: 64px;
            }

            .brand h1 {
                font-size: 1.22rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .shell,
            .scene img {
                animation: none;
            }

            .scene img {
                transform: none;
            }
        }
    </style>
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="scene" aria-hidden="true">
        <img src="{{ $bgPath }}" alt="" decoding="async" fetchpriority="high">
    </div>

    <main class="page">
        <div class="shell">
            <section class="card">
                <header class="brand">
                    <div class="logo-wrap">
                        <img src="{{ $logoPath }}" alt="{{ localize('global.system_name') }}" width="74" height="74">
                    </div>
                    <h1>{{ localize('global.system_name') }}</h1>
                    <div class="rule" aria-hidden="true"></div>
                    <p>{{ localize('global.sign_in') }}</p>
                </header>

                <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                    @csrf

                    <div class="field">
                        <label for="email">{{ localize('global.email') }}</label>
                        <div class="control">
                            <input
                                id="email"
                                type="email"
                                class="@error('email') is-invalid @enderror"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                autofocus
                                placeholder="{{ localize('global.email') }}">
                            <i class='bx bx-envelope leading'></i>
                        </div>
                        @error('email')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">{{ localize('global.password') }}</label>
                        <div class="control">
                            <input
                                id="password"
                                type="password"
                                class="@error('password') is-invalid @enderror"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="{{ localize('global.password') }}">
                            <i class='bx bx-lock-alt leading'></i>
                            <button type="button" class="toggle" id="passwordToggle" aria-label="Toggle password">
                                <i class='bx bx-hide' id="toggleIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="row">
                        <label class="remember" for="remember-me">
                            <input type="checkbox" id="remember-me" name="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>{{ localize('global.remember_me') }}</span>
                        </label>
                    </div>

                    <button type="submit" class="submit">
                        <span>{{ localize('global.sign_in') }}</span>
                        <i class='bx bx-{{ $isRtl ? 'left' : 'right' }}-arrow-alt'></i>
                    </button>
                </form>

                <div class="secure-note">
                    <i class='bx bx-shield-quarter'></i>
                    <span>{{ localize('global.system_name') }}</span>
                </div>
            </section>

            <p class="footer">&copy; {{ date('Y') }} {{ localize('global.system_name') }}</p>
        </div>
    </main>

    <script>
        const form = document.getElementById('loginForm');
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.getElementById('passwordToggle');
        const toggleIcon = document.getElementById('toggleIcon');

        passwordToggle.addEventListener('click', function () {
            const show = passwordInput.type === 'password';
            passwordInput.type = show ? 'text' : 'password';
            toggleIcon.classList.toggle('bx-hide', !show);
            toggleIcon.classList.toggle('bx-show', show);
        });

        form.addEventListener('submit', function () {
            this.querySelector('.submit').classList.add('loading');
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>

</html>
