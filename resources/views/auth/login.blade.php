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
            --bg-1: #07122b;
            --bg-2: #0d1f40;
            --bg-3: #0f766e;
            --accent: #10b981;
            --accent-strong: #059669;
            --text-main: #e5ecff;
            --text-soft: #b6c4e5;
            --card-bg: rgba(255, 255, 255, 0.88);
            --card-border: rgba(255, 255, 255, 0.42);
            --field-bg: rgba(255, 255, 255, 0.85);
            --field-border: #cfd9ec;
            --danger: #dc2626;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            color: var(--text-main);
            background:
                radial-gradient(circle at 10% 15%, rgba(16, 185, 129, 0.18), transparent 32%),
                radial-gradient(circle at 88% 82%, rgba(59, 130, 246, 0.22), transparent 28%),
                linear-gradient(125deg, var(--bg-1) 0%, var(--bg-2) 52%, var(--bg-3) 100%);
            overflow-x: hidden;
        }

        .scene-overlay {
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
            mask-image: radial-gradient(circle at center, black 30%, transparent 95%);
            opacity: 0.35;
        }

        .login-shell {
            position: relative;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.1fr 1fr;
            padding: clamp(1rem, 1.3vw, 1.6rem);
            gap: clamp(1rem, 1.8vw, 2rem);
        }

        .hero-panel {
            position: relative;
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: linear-gradient(155deg, rgba(6, 20, 48, 0.68), rgba(15, 118, 110, 0.26));
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.28);
            overflow: hidden;
            padding: clamp(2rem, 3vw, 3rem);
            display: flex;
            align-items: center;
        }

        .hero-panel::before,
        .hero-panel::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            filter: blur(3px);
        }

        .hero-panel::before {
            width: 420px;
            height: 420px;
            top: -170px;
            {{ $isRtl ? 'right' : 'left' }}: -120px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.35), rgba(56, 189, 248, 0));
            animation: driftA 12s infinite alternate ease-in-out;
        }

        .hero-panel::after {
            width: 360px;
            height: 360px;
            bottom: -160px;
            {{ $isRtl ? 'left' : 'right' }}: -95px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.35), rgba(16, 185, 129, 0));
            animation: driftB 10s infinite alternate ease-in-out;
        }

        @keyframes driftA {
            from { transform: translateY(0) scale(1); }
            to { transform: translateY(16px) scale(1.08); }
        }

        @keyframes driftB {
            from { transform: translateY(0) scale(1); }
            to { transform: translateY(-20px) scale(1.1); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 560px;
            animation: intro 650ms ease-out;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid rgba(226, 232, 240, 0.25);
            background: rgba(255, 255, 255, 0.08);
            border-radius: 999px;
            padding: 0.5rem 0.95rem;
            font-size: 0.82rem;
            color: #d9e7ff;
            margin-bottom: 1.25rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .hero-title {
            font-size: clamp(1.8rem, 3vw, 3rem);
            line-height: 1.17;
            font-weight: 800;
            margin-bottom: 0.95rem;
            text-wrap: balance;
        }

        .hero-subtitle {
            color: var(--text-soft);
            font-size: clamp(0.95rem, 1.3vw, 1.1rem);
            line-height: 1.72;
            max-width: 48ch;
        }

        .hero-stats {
            margin-top: 1.8rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .hero-chip {
            padding: 0.65rem 0.9rem;
            border-radius: 14px;
            background: rgba(15, 23, 42, 0.35);
            border: 1px solid rgba(226, 232, 240, 0.22);
            color: #d8e5ff;
            font-size: 0.87rem;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
        }

        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .form-card {
            width: 100%;
            max-width: 470px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 28px;
            box-shadow: 0 28px 60px rgba(2, 10, 32, 0.24);
            backdrop-filter: blur(20px);
            padding: clamp(1.4rem, 2.3vw, 2.5rem);
            color: #12213f;
            animation: intro 550ms ease-out;
        }

        @keyframes intro {
            from {
                opacity: 0;
                transform: translateY(26px) scale(0.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .brand-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .brand-mark {
            width: 45px;
            height: 45px;
            border-radius: 13px;
            background: linear-gradient(145deg, #0f766e, #10b981);
            color: #fff;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.32);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .brand-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f1d3e;
        }

        .form-head h1 {
            font-size: clamp(1.6rem, 2vw, 2rem);
            color: #0c1b3c;
            margin-bottom: 0.35rem;
        }

        .form-head p {
            color: #506287;
            margin-bottom: 1.35rem;
            font-size: 0.93rem;
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
            {{ $isRtl ? 'right' : 'left' }}: 0.95rem;
            color: #6680af;
            font-size: 1.1rem;
            pointer-events: none;
            transition: color 180ms ease;
        }

        .form-input {
            width: 100%;
            height: 54px;
            border-radius: 14px;
            border: 1px solid var(--field-border);
            background: var(--field-bg);
            color: #0f1f43;
            font-size: 0.95rem;
            padding: 0.95rem 2.7rem;
            transition: border-color 200ms ease, box-shadow 200ms ease, background 200ms ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #21a8a0;
            box-shadow: 0 0 0 4px rgba(33, 168, 160, 0.16);
            background: #fff;
        }

        .form-input:focus + .input-icon,
        .field.has-value .input-icon {
            color: #0f766e;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            {{ $isRtl ? 'left' : 'right' }}: 0.65rem;
            width: 34px;
            height: 34px;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: #6680af;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: background 180ms ease, color 180ms ease;
        }

        .password-toggle:hover {
            background: rgba(15, 118, 110, 0.1);
            color: #0f766e;
        }

        .meta-row {
            margin: 0.5rem 0 1.3rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #44597f;
            font-size: 0.9rem;
            cursor: pointer;
            user-select: none;
        }

        .remember input {
            width: 18px;
            height: 18px;
            accent-color: #0f766e;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            border: none;
            border-radius: 14px;
            height: 52px;
            font-size: 0.98rem;
            font-weight: 700;
            cursor: pointer;
            color: #fff;
            background: linear-gradient(130deg, var(--accent-strong), var(--accent));
            box-shadow: 0 12px 28px rgba(5, 150, 105, 0.34);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.55rem;
            transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
            position: relative;
        }

        .btn-login:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 16px 32px rgba(5, 150, 105, 0.4);
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
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.95);
            border-top-color: transparent;
            position: absolute;
            animation: spin 700ms linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .invalid-feedback {
            display: block;
            color: var(--danger);
            font-size: 0.82rem;
            margin-top: 0.45rem;
            padding-inline: 0.2rem;
        }

        .form-input.is-invalid {
            border-color: rgba(220, 38, 38, 0.68);
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.12);
        }

        .secure-row {
            margin-top: 0.9rem;
            font-size: 0.8rem;
            color: #61759e;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            justify-content: center;
        }

        @media (max-width: 1080px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .hero-panel {
                min-height: 230px;
                order: 2;
            }

            .form-panel {
                order: 1;
            }
        }

        @media (max-width: 640px) {
            .login-shell {
                padding: 0.7rem;
                gap: 0.85rem;
            }

            .hero-panel {
                min-height: 200px;
                border-radius: 24px;
            }

            .hero-title {
                font-size: 1.55rem;
            }

            .hero-subtitle {
                font-size: 0.9rem;
            }

            .form-card {
                border-radius: 22px;
                padding: 1.15rem;
            }
        }
    </style>
</head>

<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
    <div class="scene-overlay" aria-hidden="true"></div>

    <main class="login-shell">
        <section class="hero-panel">
            <div class="hero-content">
                <div class="hero-badge">
                    <i class='bx bx-shield-quarter'></i>
                    <span>{{ localize('global.system_name') }}</span>
                </div>
                <h2 class="hero-title">{{ localize('global.system_name') }}</h2>
                {{-- <p class="hero-subtitle">
                    Streamlined access for care teams with secure sign-in and fast workflows across departments.
                </p>

                <div class="hero-stats">
                    <span class="hero-chip"><i class='bx bx-lock-alt'></i> Encrypted Access</span>
                    <span class="hero-chip"><i class='bx bx-pulse'></i> Clinical Workflow</span>
                    <span class="hero-chip"><i class='bx bx-time-five'></i> Real-Time Updates</span> --}}
                {{-- </div> --}}
            </div>
        </section>

        <section class="form-panel">
            <article class="form-card">
                <div class="brand-row">
                    <span class="brand-mark"><i class='bx bx-plus-medical'></i></span>
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
                        <i class='bx bx-right-arrow-alt'></i>
                    </button>
                </form>
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
