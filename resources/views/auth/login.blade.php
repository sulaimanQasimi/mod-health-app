<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <title>{{ localize('global.login') }}</title>
    <meta name="description" content="Modern Health App Login" />
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}" />

    @php
        $isRtl = session()->get('language') !== 'en';
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
            --page-bg: #eef1f6;
            --card-bg: #ffffff;
            --card-border: #e2e8f0;
            --text: #1e293b;
            --text-muted: #64748b;
            --field-border: #cbd5e1;
            --field-bg: #f8fafc;
            --accent: #0d9488;
            --accent-hover: #0f766e;
            --danger: #dc2626;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            color: var(--text);
            background:
                radial-gradient(ellipse 80% 50% at 50% 0%, rgba(13, 148, 136, 0.07), transparent 55%),
                radial-gradient(ellipse 70% 45% at 100% 100%, rgba(100, 116, 139, 0.06), transparent 50%),
                var(--page-bg);
            overflow-x: hidden;
        }

        .login-bg-icons {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        .login-bg-icons .bg-icon {
            position: absolute;
            color: var(--accent);
            opacity: 0.11;
            line-height: 1;
            animation: bgFloat 14s ease-in-out infinite;
        }

        .login-bg-icons .bg-icon:nth-child(odd) {
            animation-duration: 18s;
            animation-delay: -2s;
        }

        .login-bg-icons .bg-icon:nth-child(3n) {
            color: #64748b;
            opacity: 0.09;
        }

        @keyframes bgFloat {
            0%, 100% { transform: translateY(0) rotate(var(--r, 0deg)); }
            50% { transform: translateY(-10px) rotate(var(--r, 0deg)); }
        }

        .bg-icon.i1 { --r: -12deg; top: 8%; left: 6%; font-size: clamp(2.5rem, 6vw, 4rem); }
        .bg-icon.i2 { --r: 8deg; top: 18%; right: 10%; font-size: clamp(2rem, 4.5vw, 3.2rem); animation-delay: -4s; }
        .bg-icon.i3 { --r: 15deg; top: 42%; left: 3%; font-size: clamp(1.75rem, 4vw, 2.75rem); animation-delay: -1s; }
        .bg-icon.i4 { --r: -6deg; top: 55%; right: 5%; font-size: clamp(2.25rem, 5vw, 3.5rem); animation-delay: -6s; }
        .bg-icon.i5 { --r: 22deg; bottom: 28%; left: 12%; font-size: clamp(1.5rem, 3.5vw, 2.5rem); }
        .bg-icon.i6 { --r: -18deg; bottom: 12%; right: 18%; font-size: clamp(2rem, 4vw, 3rem); animation-delay: -3s; }
        .bg-icon.i7 { --r: -5deg; top: 65%; left: 22%; font-size: clamp(1.35rem, 3vw, 2rem); animation-delay: -5s; }
        .bg-icon.i8 { --r: 10deg; top: 12%; left: 38%; font-size: clamp(1.25rem, 2.8vw, 1.85rem); opacity: 0.08; }
        .bg-icon.i9 { --r: -14deg; bottom: 38%; right: 28%; font-size: clamp(1.6rem, 3.2vw, 2.4rem); animation-delay: -2.5s; }
        .bg-icon.i10 { --r: 6deg; top: 32%; right: 22%; font-size: clamp(1.4rem, 3vw, 2.1rem); }
        .bg-icon.i11 { --r: -20deg; bottom: 8%; left: 35%; font-size: clamp(1.8rem, 3.8vw, 2.8rem); animation-delay: -7s; }
        .bg-icon.i12 { --r: 4deg; top: 48%; right: 38%; font-size: clamp(1.2rem, 2.5vw, 1.75rem); opacity: 0.07; }

        @media (max-width: 640px) {
            .bg-icon.i8,
            .bg-icon.i12 {
                display: none;
            }
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
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06), 0 8px 24px rgba(15, 23, 42, 0.06);
            padding: 2rem 1.75rem 1.75rem;
            animation: fadeUp 420ms ease-out;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-block {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-mark {
            width: 52px;
            height: 52px;
            margin: 0 auto 0.85rem;
            border-radius: 12px;
            background: var(--accent);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
        }

        .brand-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .form-head {
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .form-head h1 {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 0.35rem;
        }

        .form-head p {
            font-size: 0.875rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .field {
            margin-bottom: 1rem;
            position: relative;
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
            transition: color 160ms ease;
        }

        .form-input {
            width: 100%;
            height: 48px;
            border-radius: 8px;
            border: 1px solid var(--field-border);
            background: var(--field-bg);
            color: var(--text);
            font-size: 0.9375rem;
            padding: 0.75rem 2.6rem;
            transition: border-color 160ms ease, box-shadow 160ms ease, background 160ms ease;
        }

        .form-input::placeholder {
            color: #94a3b8;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
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
            {{ $isRtl ? 'left' : 'right' }}: 0.5rem;
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 160ms ease, color 160ms ease;
        }

        .password-toggle:hover {
            background: rgba(13, 148, 136, 0.08);
            color: var(--accent);
        }

        .meta-row {
            margin: 0.25rem 0 1.25rem;
            display: flex;
            align-items: center;
            justify-content: flex-start;
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
            width: 16px;
            height: 16px;
            accent-color: var(--accent);
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            border: none;
            border-radius: 8px;
            height: 46px;
            font-size: 0.9375rem;
            font-weight: 600;
            cursor: pointer;
            color: #fff;
            background: var(--accent);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            transition: background 160ms ease, transform 160ms ease;
            position: relative;
        }

        .btn-login:hover {
            background: var(--accent-hover);
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
            border-color: rgba(220, 38, 38, 0.65);
            box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.1);
        }

        @media (max-width: 400px) {
            .login-card {
                padding: 1.5rem 1.25rem 1.35rem;
            }
        }
    </style>
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="login-bg-icons" aria-hidden="true">
        <i class='bx bx-plus-medical bg-icon i1'></i>
        <i class='bx bx-heart bg-icon i2'></i>
        <i class='bx bx-clipboard bg-icon i3'></i>
        <i class='bx bx-shield-quarter bg-icon i4'></i>
        <i class='bx bx-pulse bg-icon i5'></i>
        <i class='bx bx-user-circle bg-icon i6'></i>
        <i class='bx bx-band-aid bg-icon i7'></i>
        <i class='bx bx-capsule bg-icon i8'></i>
        <i class='bx bx-first-aid bg-icon i9'></i>
        <i class='bx bx-injection bg-icon i10'></i>
        <i class='bx bx-health bg-icon i11'></i>
        <i class='bx bx-donate-heart bg-icon i12'></i>
    </div>

    <main class="login-page">
        <article class="login-card">
            <div class="brand-block">
                <div class="brand-mark" aria-hidden="true">
                    <i class='bx bx-plus-medical'></i>
                </div>
                <div class="brand-name">{{ localize('global.system_name') }}</div>
            </div>

            <div class="form-head">
                <h1>{{ localize('global.sign_in') }}</h1>
                <p>{{ localize('global.login') }}</p>
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
