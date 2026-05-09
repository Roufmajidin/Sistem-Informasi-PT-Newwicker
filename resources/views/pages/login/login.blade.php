<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login</title>

    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>

        body{
            margin:0;
            min-height:100vh;
            background:
                linear-gradient(135deg,#0f172a,#1e293b);

            display:flex;
            justify-content:center;
            align-items:center;

            font-family:Arial, sans-serif;
            padding:20px;
        }

        .login-card{

            width:100%;
            max-width:400px;

            background:#fff;

            border-radius:20px;

            padding:35px 28px;

            box-shadow:
                0 10px 30px rgba(0,0,0,0.25);

            animation:fadeIn .4s ease;
        }

        @keyframes fadeIn{
            from{
                opacity:0;
                transform:translateY(20px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        .logo{
            width:80px;
            height:80px;

            object-fit:contain;

            margin:auto;
            display:block;
            margin-bottom:15px;
        }

        .title{
            text-align:center;
            font-size:28px;
            font-weight:bold;
            margin-bottom:5px;
        }

        .subtitle{
            text-align:center;
            color:#777;
            margin-bottom:30px;
            font-size:14px;
        }

        .form-control{
            height:50px;
            border-radius:12px;
            font-size:15px;
        }

        .btn-login{

            height:50px;

            border-radius:12px;

            font-weight:bold;

            background:#2563eb;
            border:none;
        }

        .btn-login:hover{
            background:#1d4ed8;
        }

        .password-wrapper{
            position:relative;
        }

        .toggle-password{

            position:absolute;

            top:50%;
            right:15px;

            transform:translateY(-50%);

            cursor:pointer;

            color:#888;
        }

        .toggle-password:hover{
            color:#000;
        }

        @media(max-width:576px){

            .login-card{
                padding:28px 20px;
            }

            .title{
                font-size:24px;
            }

        }

    </style>
</head>

<body>

<div class="login-card">

    {{-- LOGO --}}
          <!-- <img src="/images/newwicker-logo.png" height="60"> -->


    <div class="title">
       NewWicker Dashboard
    </div>

    <div class="subtitle">
        Silahkan login untuk melanjutkan
    </div>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login.process') }}">
        @csrf

        {{-- EMAIL --}}
        <div class="mb-3">

            <label class="mb-2">
                Email
            </label>
<input type="hidden"
    name="redirect"
    value="{{ request('redirect') }}">
            <input
                type="email"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                required
                autofocus
            >
        </div>

        {{-- PASSWORD --}}
        <div class="mb-3 password-wrapper">

            <label class="mb-2">
                Password
            </label>

            <input
                type="password"
                name="password"
                id="password"
                class="form-control"
                required
            >

            <i class="fa-solid fa-eye toggle-password"
               id="togglePassword"></i>
        </div>

        {{-- REMEMBER --}}
        <div class="form-check mb-4">

            <input
                type="checkbox"
                name="remember"
                class="form-check-input"
                id="remember"
            >


        </div>

        {{-- BUTTON --}}
        <button type="submit"
                class="btn btn-primary btn-login w-100">

            Login
        </button>

    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

    const togglePassword =
        document.querySelector("#togglePassword");

    const passwordField =
        document.querySelector("#password");

    togglePassword.addEventListener("click", function () {

        const type =
            passwordField.getAttribute("type") === "password"
                ? "text"
                : "password";

        passwordField.setAttribute("type", type);

        this.classList.toggle("fa-eye");
        this.classList.toggle("fa-eye-slash");
    });

</script>

</body>
</html>
