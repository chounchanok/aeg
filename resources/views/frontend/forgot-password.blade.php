<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - AEG EASE CLUB</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Kanit -->
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent-red: #bd1e2d;
            --body-bg: #f1f5f9;
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

        .forgot-card {
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

        .input-label {
            font-size: 0.9rem;
            color: #475569;
            margin-bottom: 8px;
            font-weight: 500;
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

        .input-field {
            border-radius: 12px !important;
            height: 56px;
            border: 1px solid #e2e8f0;
            padding: 0 20px;
            font-size: 1rem;
        }

        .input-field:focus {
            border-color: var(--accent-red);
            box-shadow: 0 0 0 4px rgba(189, 30, 45, 0.1);
        }

        .btn-submit-custom {
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
            margin-top: 10px;
        }

        .btn-submit-custom:hover {
            background-color: #a01926;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(189, 30, 45, 0.3);
        }

        .back-to-login {
            text-align: center;
            margin-top: 30px;
            font-size: 0.95rem;
            color: #64748b;
        }

        .back-to-login a {
            color: var(--accent-red);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        @media (max-width: 576px) {
            .forgot-card {
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

    <div class="forgot-card">
        <h1 class="form-title">ลืมรหัสผ่าน?</h1>
        <p class="form-subtitle">ไม่ต้องกังวล! ระบุเบอร์มือถือของคุณเพื่อรับรหัสยืนยันในการตั้งรหัสผ่านใหม่</p>

        <form>
            <div class="mb-4">
                <label class="input-label">เบอร์มือถือ</label>
                <div class="phone-input-group">
                    <div class="country-code-box">
                        <img src="https://flagcdn.com/w20/th.png" width="20" alt="Thailand Flag">
                        <span class="fw-medium">+66</span>
                    </div>
                    <input type="tel" class="form-control input-field" placeholder="08x-xxx-xxxx" required>
                </div>
            </div>

            <button type="submit" class="btn btn-submit-custom">ส่งรหัสยืนยัน</button>
        </form>

        <div class="back-to-login">
            <a href="login.html">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                    class="bi bi-arrow-left" viewBox="0 0 16 16">
                    <path fill-rule="evenodd"
                        d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z" />
                </svg>
                กลับไปหน้าเข้าสู่ระบบ
            </a>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>