<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AEG EASE CLUB</title>
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

        .login-card {
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
            margin-bottom: 20px;
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

        .btn-login-custom {
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

        .btn-login-custom:hover {
            background-color: #a01926;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(189, 30, 45, 0.3);
        }

        .forgot-password {
            text-align: right;
            margin-top: 10px;
            margin-bottom: 25px;
        }

        .forgot-password a {
            color: var(--accent-red);
            text-decoration: none;
            font-size: 0.9rem;
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            font-size: 0.95rem;
            color: #64748b;
        }

        .register-link a {
            color: var(--accent-red);
            text-decoration: none;
            font-weight: 600;
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

        @media (max-width: 576px) {
            .login-card {
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

    <div class="login-card">
        <h1 class="form-title">เข้าสู่ระบบ</h1>
        <p class="form-subtitle">ยินดีต้อนรับกลับมา! กรุณาเข้าสู่ระบบ</p>

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                <strong>เกิดข้อผิดพลาด!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('login.check') }}">
        @csrf
            <div class="mb-3">
                <label class="input-label">เบอร์มือถือ</label>
                <div class="phone-input-group">
                    <div class="country-code-box">
                        <img src="https://flagcdn.com/w20/th.png" width="20" alt="Thailand Flag">
                        <span class="fw-medium">+66</span>
                    </div>
                    <input type="tel" name="phone" class="form-control input-field" value="{{ old('phone') }}" placeholder="08x-xxx-xxxx" required>
                    @error('phone')
                        <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-2">
                <label class="input-label">รหัสผ่าน</label>
                <input type="password" name="password" class="form-control input-field" placeholder="กรอกรหัสผ่านของคุณ" required>
                @error('password')
                    <div class="text-danger mt-1 text-sm">{{ $message }}</div>
                @enderror
            </div>

            <div class="forgot-password">
                <a href="{{ route('forgot-password') }}">ลืมรหัสผ่าน?</a>
            </div>

            <button type="submit" class="btn btn-login-custom">เข้าสู่ระบบ</button>
        </form>

        <div class="register-link">
            ยังไม่มีบัญชี? <a href="{{ route('register') }}">สมัครสมาชิก</a>
        </div>

        <div class="social-divider">
            <span>หรือเข้าสู่ระบบด้วย</span>
        </div>

        <div class="social-icons-group">
            <!-- LINE -->
            <a href="{{ route('social.redirect', 'line') }}" class="social-login-btn text-decoration-none" title="เข้าสู่ระบบด้วย LINE">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/LINE_logo.svg" alt="LINE Icon">
            </a>
            
            <!-- Google -->
            <a href="{{ route('social.redirect', 'google') }}" class="social-login-btn text-decoration-none" title="เข้าสู่ระบบด้วย Google">
                <svg viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z" />
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z" />
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24s.92 7.54 2.56 10.78l7.97-6.19z" />
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z" />
                </svg>
            </a>

            <!-- Facebook -->
            <a href="{{ route('social.redirect', 'facebook') }}" class="social-login-btn text-decoration-none" title="เข้าสู่ระบบด้วย Facebook">
                <svg viewBox="0 0 320 512" fill="#1877F2">
                    <path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z"/>
                </svg>
            </a>

            <!-- WhatsApp (เปลี่ยนจาก Modal ฟอร์ม เป็น Modal QR Code) -->
            <button type="button" data-bs-toggle="modal" data-bs-target="#whatsappQrModal" onclick="initWhatsAppLogin()" class="social-login-btn text-decoration-none" title="เข้าสู่ระบบด้วย WhatsApp">
                <svg viewBox="0 0 448 512" fill="#25D366">
                    <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zM223.9 415.2c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 334.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- 🌟 WhatsApp QR Code Modal -->
    <div class="modal fade" id="whatsappQrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px;">
                <div class="modal-body p-4 text-center">
                    <svg viewBox="0 0 448 512" fill="#25D366" width="60" class="mb-3">
                        <path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157zm..."></path>
                    </svg>
                    <h4 class="fw-bold mb-2">เข้าสู่ระบบด้วย WhatsApp</h4>
                    <p class="text-muted mb-4" style="font-size: 0.95rem;">สแกน QR Code ด้านล่างเพื่อส่งข้อความยืนยัน<br>ระบบจะเข้าสู่ระบบให้อัตโนมัติ</p>

                    <!-- แสดง QR Code -->
                    <div class="mb-4">
                        <img id="wa-qr-img" src="" alt="WhatsApp QR Code" width="200" height="200" class="border p-2 rounded shadow-sm">
                    </div>

                    <!-- ปุ่มสำหรับมือถือ -->
                    <a id="wa-mobile-link" href="#" target="_blank" class="btn btn-login-custom m-0 mb-3 text-decoration-none d-flex align-items-center justify-content-center gap-2">
                        เปิดแอป WhatsApp
                    </a>

                    <div class="spinner-border spinner-border-sm text-success mb-2" role="status"></div>
                    <p class="text-sm text-secondary mb-0">กำลังรอการยืนยัน...</p>
                    <p class="text-xs text-muted mt-1 mb-0" style="font-size: 0.75rem;">(รหัสอ้างอิง: <span id="wa-login-code">---</span>)</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- 🌟 JavaScript ควบคุม WhatsApp Login -->
    <script>
        let waPollingInterval = null;

        function initWhatsAppLogin() {
            // 1. สร้างรหัสสุ่ม 6 หลัก
            const code = Math.floor(100000 + Math.random() * 900000).toString();
            
            // ⚠️ สำคัญ: ใส่เบอร์มือถือ WhatsApp ของบริษัทตรงนี้ (ไม่ต้องมีเครื่องหมาย + นำหน้า)
            const waNumber = '66927590682'; 
            
            // ข้อความที่จะให้ลูกค้าพิมพ์ส่งมา (เช่น Login-123456)
            const text = 'Login-' + code; 
            
            // 2. สร้างลิงก์ wa.me
            const waUrl = `https://wa.me/${waNumber}?text=${text}`;
            
            // 3. อัปเดตข้อมูลบนหน้าจอ
            document.getElementById('wa-login-code').innerText = code;
            document.getElementById('wa-mobile-link').href = waUrl;
            
            // 4. สร้างรูป QR Code (ใช้ API ฟรีของ qrserver)
            document.getElementById('wa-qr-img').src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(waUrl)}`;

            // 5. เริ่ม Polling เช็คสถานะกับ Backend ทุกๆ 3 วินาที
            if(waPollingInterval) clearInterval(waPollingInterval);
            
            waPollingInterval = setInterval(() => {
                checkWaLoginStatus(text);
            }, 3000); 
        }

        function checkWaLoginStatus(loginText) {
            // ยิงไปเช็คที่ Backend ว่ามีข้อความ "Login-XXXXXX" เข้ามาหรือยัง
            fetch('{{ route("whatsapp.check-login") }}', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify({ login_text: loginText })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    // ถ้ายืนยันสำเร็จ ให้หยุดการเช็คและพาไปหน้า Home
                    clearInterval(waPollingInterval);
                    window.location.href = '{{ route("home") }}';
                }
            })
            .catch(err => console.error('Polling error:', err));
        }

        // หยุดการทำงานเมื่อลูกค้ากดปิด Modal
        document.getElementById('whatsappQrModal').addEventListener('hidden.bs.modal', function () {
            if(waPollingInterval) clearInterval(waPollingInterval);
        });
    </script>
</body>

</html>