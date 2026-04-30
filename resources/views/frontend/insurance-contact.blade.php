<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ติดต่อผู้เชี่ยวชาญ - AEG EASE CLUB</title>
    <!-- Google Fonts: Poppins (Main) & Kanit (Thai support) -->
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Kanit:wght@200;300;400;500&display=swap"
        rel="stylesheet">
    <!-- Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1a1a2e;
            --primary-red: #c41e3a;
            --primary-purple: #4a1c40;
            --btn-gradient: linear-gradient(90deg, #4a1c40 0%, #c41e3a 100%);
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #f8f9fa;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* --- Header Styles (Match index) --- */
        .navbar {
            background-image: url('assets/image/header-bk.webp');
            background-size: cover;
            background-position: center;
            background-color: var(--primary-dark);
            padding: 10px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-container {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        .navbar-top-row {
            display: flex;
            justify-content: flex-end;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 10px;
        }

        .nav-icons {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .nav-icon-item {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
        }

        .navbar-bottom-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand img {
            height: 50px;
        }

        .search-container {
            position: relative;
            width: 100%;
            max-width: 500px;
        }

        .search-input {
            border-radius: 25px;
            padding: 10px 50px 10px 20px;
            border: none;
            width: 100%;
            font-size: 1rem;
        }

        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            color: #666;
            border: none;
            font-size: 1.2rem;
        }

        /* --- Contact Form Section --- */
        .contact-wrapper {
            padding: 60px 0 100px;
        }

        .contact-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 60px;
            border: none;
        }

        .contact-title {
            text-align: center;
            color: #1a2d5e;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 40px;
        }

        .form-label-custom {
            font-weight: 500;
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 8px;
        }

        .form-control-custom {
            background-color: #f1f3f8;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 0.95rem;
            color: #333;
            margin-bottom: 20px;
        }

        .form-control-custom:focus {
            background-color: #ebedf4;
            box-shadow: none;
            border: 1px solid #ddd;
        }

        .form-control-custom::placeholder {
            color: #aaa;
            font-size: 0.85rem;
        }

        .form-select-custom {
            background-color: #f1f3f8;
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            font-size: 0.95rem;
            color: #aaa;
            margin-bottom: 20px;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'%3E%3Cpath fill='%23666' d='M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 15px;
        }

        .textarea-custom {
            height: 120px;
            resize: none;
        }

        /* Buttons Section */
        .btn-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }

        .btn-gradient {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 12px 0;
            width: 200px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            box-shadow: 0 4px 15px rgba(196, 30, 58, 0.2);
            transition: 0.3s;
        }

        .btn-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(196, 30, 58, 0.3);
            opacity: 0.95;
        }

        /* --- Success Modal Styles (Match Insurance-Contact 2.jpg) --- */
        .modal-success-content {
            border-radius: 30px !important;
            border: none;
            padding: 40px 30px;
            text-align: center;
        }

        .success-text-en {
            font-weight: 600;
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 5px;
        }

        .success-text-th {
            font-weight: 400;
            font-size: 1rem;
            color: #555;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn-back-home {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 10px 45px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(196, 30, 58, 0.2);
        }

        /* --- Footer Styles --- */
        footer {
            background: var(--ease-gradient);
            color: white;
            padding: 60px 0 30px;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: block;
            margin-bottom: 12px;
            font-size: 0.9rem;
        }

        .footer-column {
            border-right: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0 25px;
        }

        .social-icons-bar {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .social-icons-bar a {
            color: white;
            margin: 0 15px;
            font-size: 1.3rem;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .navbar-top-row {
                display: none;
            }

            .contact-card {
                padding: 30px 25px;
            }

            .contact-title {
                font-size: 1.5rem;
            }

            .btn-container {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }

            .btn-gradient {
                width: 100%;
                max-width: 280px;
            }

            .footer-column {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                margin-bottom: 25px;
                padding-bottom: 25px;
            }
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container navbar-container">
            <!-- Row 1 -->
            <div class="navbar-top-row w-100">
                <div class="nav-icons ms-auto">
                    <a href="#" class="nav-icon-item"><i class="fas fa-headset"></i><span>ติดตามสถานะ</span></a>
                    <a href="#" class="nav-icon-item"><i class="fas fa-bell"></i><span>การแจ้งเตือน</span></a>
                    <a href="#" class="nav-icon-item"><i class="fas fa-user"></i><span>ข้อมูลของฉัน</span></a>
                    <span style="color: rgba(255,255,255,0.5);">|</span>
                    <div class="lang-selector">
                        <img src="https://flagcdn.com/w20/th.png" alt="TH Flag" width="20">
                        <span>TH</span>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                    </div>
                </div>
            </div>

            <!-- Row 2 -->
            <div class="navbar-bottom-row w-100">
                <a class="navbar-brand" href="index">
                    <img src="assets/image/logo.webp" alt="AEG Logo"
                        onerror="this.src='https://via.placeholder.com/150x50?text=AEG+LOGO'">
                </a>

                <div class="search-container mx-auto">
                    <input type="text" class="search-input" placeholder="ค้นหา">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                </div>

                <div class="cart-section">
                    <a href="cart" class="cart-icon" style="color: white; text-decoration: none;"><i
                            class="fas fa-shopping-cart"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contact Form Main Content -->
    <main class="contact-wrapper">
        <div class="container">
            <div class="contact-card">
                <h1 class="contact-title">Contact AEG Specialist</h1>

                <form id="contactForm">
                    <div class="row">
                        <div class="col-12">
                            <input type="text" class="form-control form-control-custom" placeholder="• ชื่อผู้ติดต่อ"
                                required>
                        </div>
                        <div class="col-md-6">
                            <input type="tel" class="form-control form-control-custom"
                                placeholder="• เบอร์โทรศัพท์ Telephone Number" required>
                        </div>
                        <div class="col-md-6">
                            <input type="email" class="form-control form-control-custom" placeholder="• อีเมล / Email"
                                required>
                        </div>
                        <div class="col-12">
                            <select class="form-select form-control-custom form-select-custom" required>
                                <option value="" selected disabled>• ช่วงเวลาที่สะดวกให้ติดต่อกลับ</option>
                                <option value="morning">เช้า (09:00 - 12:00)</option>
                                <option value="afternoon">บ่าย (13:00 - 17:00)</option>
                                <option value="anytime">สะดวกตลอดเวลา</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <textarea class="form-control form-control-custom textarea-custom"
                                placeholder="• สอบถามรายละเอียดเพิ่มเติม / Request More Information"></textarea>
                        </div>
                    </div>

                    <div class="btn-container">
                        <a href="detail-Insurance" class="btn-gradient">ย้อนกลับ</a>
                        <button type="submit" class="btn-gradient">ส่งข้อความถึงเรา</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Success Modal (Match Insurance-Contact 2.jpg) -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-success-content">
                <div class="modal-body p-0">
                    <p class="success-text-en">Your request has been submitted</p>
                    <p class="success-text-en">We will contact you as soon as possible.</p>
                    <p class="success-text-th">ระบบได้รับคำขอของคุณแล้ว ทางบริษัทของเราจะติดต่อกลับโดยเร็วที่สุด</p>
                    <a href="index" class="btn-back-home">กลับสู่หน้าหลัก</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Chat Icon -->
    <div class="floating-chat" style="position: fixed; bottom: 40px; right: 40px; z-index: 1000;">
        <div class="chat-circle"
            style="width: 60px; height: 60px; background: white; border-radius: 50%; box-shadow: 0 5px 25px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; border: 1px solid #f0f0f0;">
            <i class="fas fa-comment-dots text-danger fs-3"></i>
        </div>
    </div>

    <!-- Footer Section -->
    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 mb-4 mb-md-0 text-center text-md-start">
                    <h5 class="fw-bold mb-3" style="font-size: 1rem;">ดาวน์โหลดแอปพลิเคชัน</h5>
                    <div class="d-flex gap-2 justify-content-center justify-content-md-start">
                        <div class="bg-white p-2 rounded d-flex align-items-center justify-content-center"
                            style="width: 80px; height: 80px;">
                            <i class="fas fa-qrcode fa-3x text-dark"></i>
                        </div>
                        <div class="d-flex flex-column gap-2">
                            <a href="#"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg"
                                    height="35" alt="App Store"></a>
                            <a href="#"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                                    height="35" alt="Play Store"></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="row">
                        <div class="col-6 footer-column text-center">
                            <h5 class="fw-bold mb-3" style="font-size: 0.9rem;">แพ็กเกจที่ใช้งาน</h5>
                            <a href="#" class="footer-link">ข้อกำหนดและเงื่อนไข</a>
                        </div>
                        <div class="col-6 footer-column text-center">
                            <h5 class="fw-bold mb-3" style="font-size: 0.9rem;">คำถามที่พบบ่อย</h5>
                            <a href="#" class="footer-link">นโยบายความเป็นส่วนตัว</a>
                        </div>
                    </div>
                    <div class="social-icons-bar">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-line"></i></a>
                    </div>
                </div>
                <div class="col-md-4"></div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Handle form submission to show success modal
        document.getElementById('contactForm').addEventListener('submit', function (e) {
            e.preventDefault();
            // Show the Bootstrap modal
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        });
    </script>
</body>

</html>