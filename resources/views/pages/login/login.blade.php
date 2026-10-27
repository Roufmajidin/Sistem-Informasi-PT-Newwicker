<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Login | NewWicker Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root { --navy:#15243b; --blue:#315d8b; --muted:#728096; --line:#dfe5ed; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; font-family:Inter,system-ui,-apple-system,"Segoe UI",sans-serif; background:#f3f6fa; color:var(--navy); }
        .page { min-height:100vh; display:grid; grid-template-columns:minmax(0,1fr) minmax(420px, 510px); }
        .intro { background:linear-gradient(145deg,#15243b 0%,#203d5d 100%); color:#fff; padding:clamp(32px,6vw,90px); display:flex; flex-direction:column; justify-content:space-between; }
        .brand { display:flex; align-items:center; gap:12px; font-size:19px; font-weight:750; letter-spacing:.01em; }
        .brand-icon { width:38px; height:38px; display:grid; place-items:center; border:1px solid #ffffff55; border-radius:11px; color:#c8e1fa; }
        .intro-content { max-width:540px; padding:60px 0; }
        .eyebrow { color:#9dc1e6; font-size:12px; font-weight:700; letter-spacing:.18em; text-transform:uppercase; margin-bottom:20px; }
        .intro h1 { font-size:clamp(34px,4.2vw,57px); font-weight:700; line-height:1.16; letter-spacing:-.045em; margin:0 0 22px; }
        .intro p { max-width:440px; color:#c4d1df; font-size:15px; line-height:1.85; margin:0; }
        .intro-footer { color:#a7b9cc; font-size:12px; }
        .form-side { display:flex; align-items:center; justify-content:center; padding:48px 36px; background:#fff; }
        .form-inner { width:100%; max-width:365px; }
        .mobile-brand { display:none; }
        .form-inner h2 { font-size:28px; letter-spacing:-.035em; font-weight:750; margin-bottom:9px; }
        .form-subtitle { color:var(--muted); font-size:14px; margin-bottom:34px; }
        .form-label { font-size:13px; font-weight:650; margin-bottom:9px; }
        .form-control { height:49px; border:1px solid var(--line); border-radius:9px; padding:0 14px; font-size:14px; box-shadow:none!important; }
        .form-control:focus { border-color:#608ab4; outline:3px solid #dce9f6; }
        .field { margin-bottom:22px; }
        .password-wrap { position:relative; }
        .password-wrap .form-control { padding-right:48px; }
        .toggle-password { position:absolute; top:50%; right:10px; transform:translateY(-50%); border:0; background:transparent; color:#78869a; padding:10px; cursor:pointer; }
        .toggle-password:hover { color:var(--navy); }
        .form-check { margin:0 0 27px; }
        .form-check-input { border-color:#b6c2d0; cursor:pointer; }
        .form-check-input:checked { background-color:var(--blue); border-color:var(--blue); }
        .form-check-label { color:#526174; font-size:13px; cursor:pointer; }
        .btn-login { height:49px; background:var(--navy); border:1px solid var(--navy); border-radius:9px; font-size:14px; font-weight:650; color:#fff; transition:background .2s; }
        .btn-login:hover:not(:disabled) { background:#294766; border-color:#294766; color:#fff; }
        .btn-login:disabled { opacity:.75; }
        .form-footer { text-align:center; color:#9aa5b3; font-size:12px; margin-top:34px; }
        .alert { font-size:13px; border-radius:9px; }
        .login-loading { position:fixed; inset:0; z-index:99999; display:none; align-items:center; justify-content:center; background:rgba(15,27,44,.68); padding:20px; }
        .login-loading.show { display:flex; }
        .loading-card { width:100%; max-width:320px; border-radius:14px; background:#fff; padding:32px 26px; text-align:center; box-shadow:0 15px 45px #0002; }
        .loading-spinner { width:38px; height:38px; border:4px solid #e5edf5; border-top-color:var(--blue); border-radius:50%; margin:0 auto 18px; animation:spin .8s linear infinite; }
        .loading-title { font-size:16px; font-weight:700; margin-bottom:8px; }
        .loading-message { font-size:13px; color:var(--muted); margin:0; }
        @keyframes spin { to { transform:rotate(360deg); } }
        @media(max-width:850px) { .page { grid-template-columns:1fr; } .intro { display:none; } .form-side { min-height:100vh; padding:35px 24px; } .mobile-brand { display:flex; align-items:center; gap:10px; margin-bottom:58px; font-size:18px; font-weight:750; } .mobile-brand .brand-icon { border-color:#dbe5ef; color:var(--blue); } }
        @media(prefers-reduced-motion:reduce) { *,*::before,*::after { animation-duration:.01ms!important; transition-duration:.01ms!important; } }

        .brand-logo { width:230px; height:115px; max-width:100%; object-fit:contain; object-position:center; display:block; }
        .mobile-brand .brand-logo { width:230px; height:115px; }
        /* Centered classic login layout with refreshed corporate styling */
        body { background:linear-gradient(135deg,#101c30 0%,#1b3553 52%,#284967 100%); }
        .page { display:flex; justify-content:center; align-items:center; min-height:100vh; padding:24px; }
        .form-side { width:100%; max-width:440px; min-height:0; padding:38px 36px; background:#fff; border:1px solid rgba(255,255,255,.6); border-radius:16px; box-shadow:0 22px 65px rgba(4,14,29,.28); }
        .form-inner { max-width:none; }
        .mobile-brand { justify-content:center; align-items:center; margin:0 0 18px; font-size:18px; }
        .form-inner h2,.form-subtitle { text-align:center; }
        .form-inner h2 { font-size:26px; }
        .form-subtitle { margin-bottom:30px; }
        .form-control { border-radius:10px; }
        .btn-login { border-radius:10px; }
        .form-footer { margin-top:30px; }
        @media(max-width:850px) { .page { padding:20px; } .form-side { min-height:0; padding:32px 24px; } .mobile-brand { margin-bottom:24px; } }
        @media(max-width:420px) { .form-side { padding:30px 21px; } }
    </style>
</head>
<body>
<div class="page">
    <aside class="intro" style="display:none">
        {{-- <div class="brand"><img src="{{ asset('assets/images/logo.png') }}" alt="Logo NewWicker" class="brand-logo"> NewWicker</div> --}}
        <div class="intro-content">
            <div class="eyebrow">Internal Management System</div>
            <h1>Satu platform.<br>Kerja lebih terarah.</h1>
            <p>Akses dashboard internal NewWicker untuk mendukung koordinasi dan aktivitas operasional perusahaan.</p>
        </div>
        <div class="intro-footer">ONE TEAM, ONE STANDARD.</div>
    </aside>
    <main class="form-side">
        <div class="form-inner">
            {{-- <div class="mobile-brand" style="display:flex"><img src="{{ asset('assets/images/logo.png') }}" alt="Logo NewWicker" class="brand-logo"></div> --}}
            <h2>Selamat datang</h2>
            <p class="form-subtitle">Masuk ke akun Anda untuk melanjutkan.</p>
            @if ($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('login.process') }}" id="loginForm">
                @csrf
                <input type="hidden" name="redirect" value="{{ request('redirect', old('redirect')) }}">
                <div class="field">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="your@gmail.com" value="{{ old('email') }}" autocomplete="username" required autofocus>
                </div>
                <div class="field">
                    <label class="form-label" for="password">Password</label>
                    <div class="password-wrap">
                        <input type="password" name="password" id="password" class="form-control" placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Tampilkan password" aria-pressed="false"><i class="fa-solid fa-eye" id="passwordIcon"></i></button>
                    </div>
                </div>
                <div class="form-check">
                    <input type="checkbox" name="remember" value="1" class="form-check-input" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>
                <button type="submit" class="btn btn-login w-100" id="loginBtn">
                    <span id="loginBtnText">Masuk ke Dashboard <i class="fa-solid fa-arrow-right ms-2"></i></span>
                    <span id="loginBtnLoading" class="d-none"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Memproses...</span>
                </button>
            </form>
            <div class="form-footer">© {{ date('Y') }} PT. NewWicker Indonesia</div>
        </div>
    </main>
</div>
<div class="login-loading" id="loginLoading" role="status" aria-live="polite" aria-hidden="true">
    <div class="loading-card">
        <div class="loading-spinner"></div>
        <div class="loading-title">Sedang Memproses Login</div>
        <p class="loading-message" id="loadingMessage">Memeriksa akun Anda. Mohon tunggu...</p>
        <p class="small text-muted mt-3 mb-0">Jangan tutup atau refresh halaman ini.</p>
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
    const togglePassword = document.getElementById('togglePassword');
    const passwordField = document.getElementById('password');
    const passwordIcon = document.getElementById('passwordIcon');
    let isSubmitting = false;
    togglePassword.addEventListener('click', function () {
        const visible = passwordField.type === 'password';
        passwordField.type = visible ? 'text' : 'password';
        passwordIcon.classList.toggle('fa-eye', !visible);
        passwordIcon.classList.toggle('fa-eye-slash', visible);
        togglePassword.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
        togglePassword.setAttribute('aria-pressed', String(visible));
    });
    form.addEventListener('submit', function (event) {
        if (isSubmitting) { event.preventDefault(); return; }
        if (!form.checkValidity()) return;
        isSubmitting = true;
        loginLoading.classList.add('show');
        loginLoading.setAttribute('aria-hidden', 'false');
        loginBtn.disabled = true;
        loginBtnText.classList.add('d-none');
        loginBtnLoading.classList.remove('d-none');
    });
    window.addEventListener('pageshow', function () {
        isSubmitting = false;
        loginLoading.classList.remove('show');
        loginLoading.setAttribute('aria-hidden', 'true');
        loginBtn.disabled = false;
        loginBtnText.classList.remove('d-none');
        loginBtnLoading.classList.add('d-none');
    });
})();
</script>
</body>
</html>
