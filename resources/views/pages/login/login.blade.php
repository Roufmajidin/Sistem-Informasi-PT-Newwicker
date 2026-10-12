
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - NewWicker Dashboard</title>

    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border-radius: 20px;
            padding: 35px 28px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            animation: fadeIn .4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .title {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 30px;
            font-size: 14px;
        }

        .form-control {
            height: 50px;
            border-radius: 12px;
            font-size: 15px;
        }

        .btn-login {
            height: 50px;
            border-radius: 12px;
            font-weight: bold;
            background: #2563eb;
            border: none;
            transition: background .2s, opacity .2s;
        }

        .btn-login:hover:not(:disabled) {
            background: #1d4ed8;
        }

        .btn-login:disabled {
            opacity: .8;
            cursor: not-allowed;
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 45px;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            z-index: 2;
        }

        .toggle-password:hover {
            color: #000;
        }

        /* Loading overlay */
        .login-loading {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: rgba(15, 23, 42, .72);
            backdrop-filter: blur(4px);
        }

        .login-loading.show {
            display: flex;
        }

        .loading-card {
            width: 100%;
            max-width: 320px;
            padding: 30px 24px;
            border-radius: 18px;
            background: #fff;
            text-align: center;
            box-shadow: 0 15px 45px rgba(0, 0, 0, .25);
        }

        .loading-spinner {
            width: 48px;
            height: 48px;
            margin: 0 auto 18px;
            border: 5px solid #dbeafe;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin .8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .loading-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 8px;
        }

        .loading-message {
            color: #64748b;
            font-size: 13px;
            margin: 0;
        }

        @media (max-width: 576px) {
            .login-card {
                padding: 28px 20px;
            }

            .title {
                font-size: 24px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }
        }
    </style>
</head>

<body>

<div class="login-card">

    <div class="title">
        NewWicker Dashboard
    </div>

    <div class="subtitle">
        Silahkan login untuk melanjutkan
    </div>

    {{-- ERROR DARI LARAVEL --}}
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('login.process') }}"
          id="loginForm">

        @csrf

        <input type="hidden"
               name="redirect"
               value="{{ request('redirect', old('redirect')) }}">

        {{-- EMAIL --}}
        <div class="mb-3">
            <label for="email" class="form-label mb-2">
                Email
            </label>

            <input
                type="email"
                name="email"
                id="email"
                class="form-control"
                value="{{ old('email') }}"
                autocomplete="username"
                required
                autofocus
            >
        </div>

        {{-- PASSWORD --}}
        <div class="mb-3 password-wrapper">
            <label for="password" class="form-label mb-2">
                Password
            </label>

            <input
                type="password"
                name="password"
                id="password"
                class="form-control"
                autocomplete="current-password"
                required
            >

            <i class="fa-solid fa-eye toggle-password"
               id="togglePassword"
               role="button"
               tabindex="0"
               aria-label="Tampilkan password"></i>
        </div>

        {{-- REMEMBER --}}
        <div class="form-check mb-4">
            <input
                type="checkbox"
                name="remember"
                value="1"
                class="form-check-input"
                id="remember"
            >

            <label class="form-check-label" for="remember">
                Ingat saya
            </label>
        </div>

        {{-- BUTTON LOGIN --}}
        <button type="submit"
                class="btn btn-primary btn-login w-100"
                id="loginBtn">

            <span id="loginBtnText">
                <i class="fa-solid fa-right-to-bracket me-1"></i>
                Login
            </span>

            <span id="loginBtnLoading" class="d-none">
                <span class="spinner-border spinner-border-sm me-2"
                      aria-hidden="true"></span>
                Memproses...
            </span>
        </button>

    </form>
</div>

{{-- GLOBAL LOADING --}}
<div class="login-loading"
     id="loginLoading"
     role="status"
     aria-live="polite"
     aria-hidden="true">

    <div class="loading-card">
        <div class="loading-spinner"></div>

        <div class="loading-title">
            Sedang Memproses Login
        </div>

        <p class="loading-message" id="loadingMessage">
            Memeriksa akun Anda. Mohon tunggu...
        </p>

        <p class="small text-muted mt-3 mb-0">
            Jangan tutup atau refresh halaman ini.
        </p>
    </div>
</div>

<script>
(function () {
    'use strict';

    const form = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');
    const loginBtnText = document.getElementById('loginBtnText');
    const loginBtnLoading = document.getElementById('loginBtnLoading');
    const loginLoading = document.getElementById('loginLoading');
    const loadingMessage = document.getElementById('loadingMessage');

    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');

    let isSubmitting = false;

    /*
     * TOGGLE PASSWORD
     */
    function togglePasswordVisibility() {
        const isPassword = passwordField.type === 'password';

        passwordField.type = isPassword ? 'text' : 'password';

        togglePassword.classList.toggle('fa-eye', !isPassword);
        togglePassword.classList.toggle('fa-eye-slash', isPassword);

        togglePassword.setAttribute(
            'aria-label',
            isPassword ? 'Sembunyikan password' : 'Tampilkan password'
        );
    }

    togglePassword.addEventListener('click', togglePasswordVisibility);

    togglePassword.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            togglePasswordVisibility();
        }
    });

    /*
     * CEGAH SUBMIT GANDA
     */
    form.addEventListener('submit', function (event) {
        /*
         * Jika sudah pernah submit, hentikan request kedua.
         */
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        /*
         * Jangan tampilkan loading jika validasi HTML gagal.
         * Browser akan menangani validasi required/email.
         */
        if (!form.checkValidity()) {
            return;
        }

        isSubmitting = true;

        /*
         * Tampilkan loading langsung.
         */
        loginLoading.classList.add('show');
        loginLoading.setAttribute('aria-hidden', 'false');

        loadingMessage.textContent =
            'Memeriksa akun Anda. Mohon tunggu...';

        /*
         * Ubah tombol dan cegah klik berulang.
         */
        loginBtn.disabled = true;
        loginBtnText.classList.add('d-none');
        loginBtnLoading.classList.remove('d-none');

        /*
         * Jangan disable input email/password sebelum submit,
         * agar nilainya tetap terkirim ke server.
         *
         * Form tetap dikirim dengan mekanisme normal Laravel.
         */
    });

    /*
     * Jika halaman dipulihkan dari browser back-forward cache,
     * pulihkan tombol agar bisa digunakan kembali.
     */
    window.addEventListener('pageshow', function () {
        isSubmitting = false;

        loginLoading.classList.remove('show');
        loginLoading.setAttribute('aria-hidden', 'true');

        loginBtn.disabled = false;
        loginBtnText.classList.remove('d-none');
        loginBtnLoading.classList.add('d-none');
    });

    /*
     * Jika browser memunculkan kembali halaman dari bfcache,
     * pageshow akan mengatur ulang status loading.
     */
})();
</script>

</body>
</html>
