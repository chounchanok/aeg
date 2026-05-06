<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>คำถามที่พบบ่อย - AEG EASE CLUB</title>
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
            --btn-gradient: linear-gradient(90deg, #1a2d5e 0%, #c41e3a 100%);
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

        .cart-icon {
            font-size: 1.5rem;
            color: white;
        }

        /* --- FAQ Main Content --- */
        .faq-wrapper {
            padding: 60px 0 100px;
        }

        .faq-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .faq-header h1 {
            color: #1a2d5e;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .faq-header h2 {
            color: #1a2d5e;
            font-weight: 700;
            font-size: 1.4rem;
        }

        /* FAQ Accordion Styling */
        .faq-container {
            max-width: 850px;
            margin: 0 auto;
        }

        .accordion-item {
            border: 1px solid #e0e0e0;
            border-radius: 15px !important;
            margin-bottom: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .accordion-button {
            padding: 20px 25px;
            font-weight: 600;
            color: #1a2d5e;
            font-size: 1.05rem;
            background-color: white;
        }

        .accordion-button:not(.collapsed) {
            background-color: white;
            color: var(--primary-red);
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(0, 0, 0, 0.125);
        }

        .accordion-body {
            padding: 20px 25px 30px;
            color: #666;
            font-size: 0.95rem;
            line-height: 1.8;
            border-top: 1px solid #f0f0f0;
        }

        /* --- Contact CTA Section --- */
        .contact-cta {
            text-align: center;
            margin-top: 100px;
        }

        .cta-text {
            font-weight: 600;
            font-size: 1.2rem;
            color: #333;
            margin-bottom: 25px;
        }

        .btn-cta-contact {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 15px 0;
            width: 100%;
            max-width: 600px;
            font-size: 1.4rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 8px 25px rgba(26, 45, 94, 0.2);
            transition: 0.3s;
        }

        .btn-cta-contact:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(196, 30, 58, 0.3);
            opacity: 0.95;
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

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .faq-header h1 {
                font-size: 1.5rem;
            }

            .faq-header h2 {
                font-size: 1.2rem;
            }

            .accordion-button {
                font-size: 0.95rem;
                padding: 15px 20px;
            }

            .btn-cta-contact {
                font-size: 1.1rem;
                padding: 12px 0;
                max-width: 90%;
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
    @include('frontend.header')

    <!-- FAQ Main Content -->
    <main class="faq-wrapper">
        <div class="container">
            <div class="faq-header">
                <h1>Frequently Asked Questions</h1>
                <h2>คำถามที่พบบ่อย</h2>
            </div>

            <div class="faq-container">
                <div class="accordion" id="faqAccordion">

                    <!-- หมวด 1 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq1">
                                ระบบรักษาความปลอดภัย (Security System)
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                ข้อมูลเกี่ยวกับระบบสัญญาณกันขโมย การทำงานของเซ็นเซอร์ และการแจ้งเตือนผ่านแอปพลิเคชัน
                            </div>
                        </div>
                    </div>

                    <!-- หมวด 2 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq2">
                                ประกันภัย (Insurance)
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                รายละเอียดความคุ้มครอง ขั้นตอนการเคลม และประเภทของทรัพย์สินที่สามารถทำประกันได้
                            </div>
                        </div>
                    </div>

                    <!-- หมวด 3 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq3">
                                ตู้เซฟนิรภัย (Safety Deposit Locker)
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                ขั้นตอนการเช่าตู้เซฟ ระบบความปลอดภัยในห้องนิรภัย และเวลาที่สามารถเข้าใช้งานได้
                            </div>
                        </div>
                    </div>

                    <!-- หมวด 4 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq4">
                                บริการช่าง (Technician Service)
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                การนัดหมายบริการตรวจเช็กอุปกรณ์ การซ่อมบำรุง และค่าบริการเบื้องต้น
                            </div>
                        </div>
                    </div>

                    <!-- หมวด 5 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq5">
                                สิทธิพิเศษสมาชิก (EASE CLUB)
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                วิธีการสะสมแต้ม การแลกสิทธิประโยชน์ และระดับของสมาชิก Advance Member
                            </div>
                        </div>
                    </div>

                    <!-- หมวด 6 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#faq6">
                                การใช้งานแอปพลิเคชัน (Application)
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                การดาวน์โหลดแอป การตั้งค่ารหัสผ่าน และการดูสถานะแบบ Real-time ผ่านมือถือ
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Contact CTA (Match FAQ.jpg) -->
            <div class="contact-cta">
                <p class="cta-text">ยังแก้ปัญหาไม่ได้ใช่ไหม? ส่งข้อความหาเราได้เลย</p>
                <a href="insurance-contact" class="btn-cta-contact">ส่งข้อความหาเรา</a>
            </div>
        </div>
    </main>

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
</body>

</html>
