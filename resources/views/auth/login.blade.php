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
            --text: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --field-bg: #f8fafc;
            --accent: #0f766e;
            --accent-hover: #0d9488;
            --danger: #dc2626;
            --panel: #ffffff;
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

        .login-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
        }

        .login-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        .login-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                180deg,
                rgba(11, 23, 36, 0.35) 0%,
                rgba(11, 23, 36, 0.45) 100%
            );
        }

        .login-page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--panel);
            border: 1px solid rgba(255, 255, 255, 0.65);
            border-radius: 16px;
            padding: 2rem 1.75rem 1.75rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.22);
            animation: fadeIn 400ms ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand img {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 0.85rem;
            display: block;
            margin-inline: auto;
        }

        .brand h1 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--text);
            line-height: 1.3;
            margin-bottom: 0.25rem;
        }

        .brand p {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .field {
            margin-bottom: 0.9rem;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            {{ $isRtl ? 'right' : 'left' }}: 0.85rem;
            color: #94a3b8;
            font-size: 1.05rem;
            pointer-events: none;
        }

        .form-input {
            width: 100%;
            height: 46px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--field-bg);
            color: var(--text);
            font-size: 0.9375rem;
            padding: 0.7rem 2.5rem;
            transition: border-color 150ms ease, box-shadow 150ms ease, background 150ms ease;
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.12);
        }

        .form-input:focus + .input-icon {
            color: var(--accent);
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            {{ $isRtl ? 'left' : 'right' }}: 0.4rem;
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 8px;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: var(--accent);
            background: rgba(15, 118, 110, 0.08);
        }

        .meta-row {
            margin: 0.15rem 0 1.1rem;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            font-size: 0.875rem;
            cursor: pointer;
            user-select: none;
        }

        .remember input {
            width: 15px;
            height: 15px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            height: 46px;
            border: none;
            border-radius: 10px;
            background: var(--accent);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            position: relative;
            transition: background 150ms ease;
        }

        .btn-login:hover {
            background: var(--accent-hover);
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

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .invalid-feedback {
            display: block;
            color: var(--danger);
            font-size: 0.8rem;
            margin-top: 0.35rem;
        }

        .form-input.is-invalid {
            border-color: rgba(220, 38, 38, 0.55);
        }

        @media (max-width: 420px) {
            .login-card {
                padding: 1.5rem 1.2rem 1.35rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .login-card {
                animation: none;
            }
        }
    </style>
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="login-bg" aria-hidden="true">
        <img src="{{ $bgPath }}" alt="" decoding="async" fetchpriority="high">
    </div>

    <main class="login-page">
        <article class="login-card">
            <header class="brand">
                <img src="{{ $logoPath }}" alt="{{ localize('global.system_name') }}" width="72" height="72">
                <h1>{{ localize('global.system_name') }}</h1>
                <p>{{ localize('global.sign_in') }}</p>
            </header>

            <form method="POST" action="{{ route('login') }}" id="loginForm" novalidate>
                @csrf

                <div class="field">
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
                </button>
            </form>
        </article>
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
    </script>
</body>

</html>
