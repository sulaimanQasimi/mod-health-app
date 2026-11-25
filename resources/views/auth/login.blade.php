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
            --line: #d9e0ea;
            --field: #f4f7fb;
            --gold: #b0893c;
            --navy: #1a3358;
            --navy-hover: #142845;
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
        }

        .scene img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 40%;
        }

        .scene::after {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(10, 20, 35, 0.18) 0%, rgba(10, 20, 35, 0.08) 35%, rgba(10, 20, 35, 0.55) 100%),
                radial-gradient(ellipse at center, transparent 35%, rgba(8, 16, 28, 0.35) 100%);
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
            max-width: 420px;
            animation: enter 500ms ease-out;
        }

        .card {
            background: var(--white);
            border-radius: 4px;
            border-top: 3px solid var(--gold);
            padding: 2.35rem 2.1rem 1.85rem;
            box-shadow: 0 16px 40px rgba(8, 16, 28, 0.28);
        }

        .brand {
            text-align: center;
            margin-bottom: 1.85rem;
        }

        .brand img {
            width: 88px;
            height: 88px;
            object-fit: contain;
            display: block;
            margin: 0 auto 1rem;
        }

        .brand h1 {
            font-size: 1.35rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1.35;
            margin-bottom: 0.35rem;
            letter-spacing: 0.01em;
        }

        .brand .rule {
            width: 42px;
            height: 2px;
            background: var(--gold);
            margin: 0.75rem auto 0.7rem;
        }

        .brand p {
            font-size: 0.9rem;
            color: var(--muted);
        }

        .field {
            margin-bottom: 1rem;
        }

        .field label {
            display: block;
            margin-bottom: 0.4rem;
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
        }

        .control input {
            width: 100%;
            height: 46px;
            border: 1px solid var(--line);
            border-radius: 3px;
            background: var(--field);
            color: var(--ink);
            font-size: 0.94rem;
            padding: 0.65rem 2.6rem;
            transition: border-color 150ms ease, background 150ms ease;
        }

        .control input::placeholder {
            color: #9aabc0;
        }

        .control input:focus {
            outline: none;
            border-color: var(--navy);
            background: var(--white);
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
            border-radius: 3px;
        }

        .toggle:hover {
            color: var(--navy);
            background: rgba(26, 51, 88, 0.06);
        }

        .row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 0.15rem 0 1.25rem;
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
            height: 46px;
            border: 0;
            border-radius: 3px;
            background: var(--navy);
            color: var(--white);
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            cursor: pointer;
            position: relative;
            transition: background 150ms ease;
        }

        .submit:hover {
            background: var(--navy-hover);
        }

        .submit.loading {
            pointer-events: none;
            color: transparent;
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

        .footer {
            margin-top: 1.15rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.78rem;
            text-shadow: 0 1px 8px rgba(0, 0, 0, 0.35);
        }

        @keyframes enter {
            from {
                opacity: 0;
                transform: translateY(12px);
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
                padding: 1.75rem 1.25rem 1.4rem;
            }

            .brand img {
                width: 76px;
                height: 76px;
            }

            .brand h1 {
                font-size: 1.2rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .shell {
                animation: none;
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
                    <img src="{{ $logoPath }}" alt="{{ localize('global.system_name') }}" width="88" height="88">
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

                    <button type="submit" class="submit">{{ localize('global.sign_in') }}</button>
                </form>
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
