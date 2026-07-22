<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - AEG EASE CLUB</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Kanit -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent-red: #bd1e2d;
            --body-bg: #f1f5f9;
            /* สีเทาอ่อนสะอาดตา */
            --card-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Kanit', sans-serif;
            background-color: var(--body-bg);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .registration-card {
            background: white;
            border-radius: 24px;
            box-shadow: var(--card-shadow);
            width: 100%;
            max-width: 480px;
            padding: 50px 40px;
            border-top: 6px solid var(--accent-red);
            transition: transform 0.3s ease;
        }

        .form-title {
            color: #0f172a;
            font-weight: 600;
            font-size: 2rem;
            margin-bottom: 8px;
            text-align: center;
        }

        .form-subtitle {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 35px;
            text-align: center;
        }

        .phone-input-group {
            display: flex;
            gap: 12px;
            margin-bottom: 25px;
        }

        .country-code-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 0 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 105px;
            height: 56px;
        }

        .phone-input-field {
            border-radius: 12px !important;
            height: 56px;
            border: 1px solid #e2e8f0;
            padding: 0 20px;
            font-size: 1rem;
        }

        .phone-input-field:focus {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 4px rgba(189, 30, 45, 0.1);
        }

        .btn-otp-custom {
            background-color: var(--accent-red);
            color: white;
            border: none;
            border-radius: 12px;
            height: 56px;
            width: 100%;
            font-size: 1.15rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(189, 30, 45, 0.2);
        }

        .btn-otp-custom:hover {
            background-color: #a01926;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(189, 30, 45, 0.3);
        }

        .social-divider {
            display: flex;
            align-items: center;
            margin: 35px 0;
            color: #94a3b8;
            font-size: 0.95rem;
        }

        .social-divider::before,
        .social-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .social-divider span {
            padding: 0 15px;
        }

        .social-icons-group {
            display: flex;
            justify-content: center;
            gap: 30px;
        }

        /* ปุ่มเข้าสู่ระบบด้วยขอบวงกลมครอบ */
        .social-login-btn {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #e2e8f0;
            background: white;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            padding: 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.03);
        }

        .social-login-btn:hover {
            border-color: var(--accent-red);
            transform: scale(1.1);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        }

        .social-login-btn img,
        .social-login-btn svg {
            width: 32px;
            height: 32px;
        }

        /* สำหรับหน้าจอเล็ก */
        @media (max-width: 576px) {
            .registration-card {
                padding: 35px 25px;
                box-shadow: none;
                background: transparent;
                border: none;
            }

            body {
                background-color: white;
            }
        }
    </style>
</head>

<body>

    <div class="registration-card">
        <h1 class="form-title">ลงทะเบียน</h1>
        <p class="form-subtitle">กรอกเบอร์มือถือของคุณเพื่อรับรหัส OTP</p>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                <strong>เกิดข้อผิดพลาด!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}">
        @csrf
            <div class="phone-input-group">
                <div class="country-code-box">
                    <img src="https://flagcdn.com/w20/th.png" width="20" alt="Thailand Flag">
                    <span class="fw-medium">+66</span>
                </div>
                <input type="tel" name="phone" class="form-control phone-input-field" value="{{ old('phone') }}" placeholder="เบอร์มือถือ" required>
            </div>
            @error('phone')
                <div class="text-danger mt-1 text-sm">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn btn-otp-custom">ขอรหัส OTP</button>
        </form>

        <div class="social-divider">
            <span>หรือเข้าสู่ระบบด้วย</span>
        </div>

        <div class="social-icons-group">
            <a href="{{ route('social.redirect', 'line') }}" class="social-login-btn text-decoration-none" title="เข้าสู่ระบบด้วย LINE">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/LINE_logo.svg" alt="LINE Icon">
            </a>
            
            <a href="{{ route('social.redirect', 'google') }}" class="social-login-btn text-decoration-none" title="เข้าสู่ระบบด้วย Google">
                <svg viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24s.92 7.54 2.56 10.78l7.97-6.19z" />
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                </svg>
            </a>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>