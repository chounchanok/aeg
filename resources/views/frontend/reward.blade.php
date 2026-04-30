<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดแลกของรางวัล - AEG EASE CLUB</title>
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
            --btn-gradient: linear-gradient(90deg, #4a1c40 0%, #c41e3a 100%);
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);

            /* Gradient defined by user image 1774336746447.jpg */
            /* Direction: Top to Bottom. Red 100% -> Blue 42% -> Fade 0% */
            --custom-hero-gradient: linear-gradient(180deg,
                    rgba(227, 31, 38, 1) 0%,
                    rgba(23, 46, 89, 0.42) 50%,
                    rgba(247, 247, 247, 0) 100%);
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #f4f5f7;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* --- Header Styles (Standard AEG - Original Width) --- */
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

        .points-badge {
            background: white;
            color: var(--primary-red);
            border-radius: 20px;
            padding: 5px 15px;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.9rem;
        }

        /* --- Main Content Layout --- */
        .reward-content-wrapper {
            width: 100%;
            min-height: 100vh;
            background-color: #fff;
        }

        /* Container strictly 950px for content */
        .container-950 {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Hero section with Gradient - No Top Border Radius, flush with menu */
        .hero-gradient-box {
            background: var(--custom-hero-gradient);
            padding: 60px 0 30px;
            width: 100%;
        }

        .hero-inner {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .reward-img-frame {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            max-width: 450px;
        }

        .reward-img-frame img {
            max-width: 100%;
            height: auto;
        }

        .hero-text-side {
            flex: 1;
            color: white;
        }

        .hero-text-side h1 {
            font-weight: 800;
            font-size: 3rem;
            margin-bottom: 5px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .hero-text-side h2 {
            font-weight: 700;
            font-size: 1.6rem;
            margin-bottom: 30px;
            line-height: 1.3;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Redeem Button Box */
        .btn-redeem-pill {
            background: white;
            border-radius: 10px;
            padding: 3px 45px;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-redeem-pill:hover {
            transform: translateY(-5px);
        }

        .btn-redeem-pill .lbl {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 1.1rem;
        }

        .btn-redeem-pill .pts {
            font-weight: 800;
            color: var(--primary-red);
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* --- Details Section (Where Gradient ends) --- */
        .reward-details-area {
            padding: 20px 0 100px;
        }

        .main-product-title {
            font-weight: 700;
            font-size: 2rem;
            color: var(--primary-dark);
            margin-bottom: 30px;
        }

        .specs-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 50px;
        }

        .specs-list li {
            position: relative;
            padding-left: 20px;
            font-size: 1rem;
            color: #444;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .specs-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: var(--primary-red);
            font-weight: 900;
        }

        .section-sub-title {
            font-weight: 700;
            color: var(--primary-red);
            font-size: 1.25rem;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .section-desc-text {
            font-size: 1rem;
            color: #555;
            line-height: 1.8;
            margin-bottom: 40px;
        }

        /* --- QR Modal Style --- */
        .modal-qr-content {
            border-radius: 20px !important;
            border: none;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-qr-blue-top {
            background-color: var(--primary-dark);
            color: white;
            padding: 30px 20px 45px;
            text-align: center;
            position: relative;
        }

        .modal-qr-blue-top h5 {
            font-weight: 700;
            margin-bottom: 30px;
            font-size: 1.6rem;
        }

        .btn-qr-close {
            position: absolute;
            right: 20px;
            top: 20px;
            background: transparent;
            border: none;
            color: white;
            font-size: 1.4rem;
            cursor: pointer;
            opacity: 0.8;
        }

        .qr-frame {
            display: inline-block;
            background: white;
            padding: 15px;
            border-radius: 15px;
        }

        .modal-qr-footer {
            background-color: white;
            padding: 35px 25px;
            text-align: center;
            color: #333;
        }

        /* --- Footer Styles (Original Width) --- */
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

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .hero-inner {
                flex-direction: column;
                text-align: center;
                gap: 40px;
            }

            .hero-text-side {
                padding-left: 0;
            }

            .hero-text-side h1 {
                font-size: 2.2rem;
            }

            .main-product-title {
                font-size: 1.5rem;
                text-align: center;
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

    <!-- Header Section (Original Width) -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container navbar-container">
            <div class="navbar-top-row w-100">
                <div class="nav-icons ms-auto">
                    <a href="#" class="nav-icon-item"><i class="fas fa-headset"></i><span>ติดตามสถานะ</span></a>
                    <a href="#" class="nav-icon-item"><i class="fas fa-bell"></i><span>การแจ้งเตือน</span></a>
                    <a href="#" class="nav-icon-item"><i class="fas fa-user"></i><span>ข้อมูลของฉัน</span></a>
                    <span style="color: rgba(255,255,255,0.5);">|</span>
                    <div class="lang-selector"
                        style="color: white; display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 0.85rem;">
                        <img src="https://flagcdn.com/w20/th.png" alt="TH Flag" width="20">
                        <span>TH</span>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                    </div>
                </div>
            </div>

            <div class="navbar-bottom-row w-100">
                <a class="navbar-brand" href="index">
                    <img src="assets/image/logo.webp" alt="AEG Logo"
                        onerror="this.src='https://via.placeholder.com/150x50?text=AEG+LOGO'">
                </a>

                <div class="search-container mx-auto">
                    <input type="text" class="search-input" placeholder="ค้นหา">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                </div>

                <div class="cart-section" style="display: flex; align-items: center; gap: 15px;">
                    <a href="cart" style="color: white; font-size: 1.5rem;"><i
                            class="fas fa-shopping-cart"></i></a>
                    <div class="points-badge">
                        <i class="fas fa-coins" style="color: #f1c40f;"></i> 200
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Content Wrapper -->
    <main class="reward-content-wrapper">

        <!-- Hero Section with Gradient (Inside 950px Container, Touches Navbar) -->
        <section class="hero-gradient-box">
            <div class="container-950">
                <div class="hero-inner">
                    <div class="reward-img-frame">
                        <img src="https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?auto=format&fit=crop&w=800&q=80"
                            alt="ACONATIC TV">
                    </div>
                    <div class="hero-text-side">
                        <h1>ACONATIC :</h1>
                        <h2>สมาร์ททีวี 43 นิ้ว<br>รุ่น 43HS701AN<br>มูลค่า 7,490 บาท</h2>

                        <button type="button" class="btn-redeem-pill" data-bs-toggle="modal"
                            data-bs-target="#redeemModal">
                            <span class="lbl">รับสิทธิ์</span>
                            <div class="pts">
                                <i class="fas fa-coins" style="color: #edb314;"></i> 200
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Detailed Info (Inside 950px Container, White Background) -->
        <section class="reward-details-area">
            <div class="container-950">
                <!-- Gradient ends 0% opacity here -->
                <h3 class="main-product-title">ACONATIC สมาร์ททีวี 43 นิ้ว รุ่น 43HS701AN ปี 2024</h3>

                <ul class="specs-list">
                    <li>ระบบภาพ: Full HD</li>
                    <li>Resolution(Pixels): 1920 x 1080</li>
                    <li>Features: Google TV</li>
                    <li>HDR: Yes Cinema HDR</li>
                    <li>ช่องต่อ: USB x1, HDMI x2</li>
                    <li>Network: Lan, Wireless, Bluetooth 5.0</li>
                    <li>Contrast Ratio: 5000:1</li>
                    <li>Brightness(CD/M): 220</li>
                    <li>ResponseTime(MS): 6.5</li>
                    <li>มุมมองภาพ(องศา): 178</li>
                    <li>มอก.(Yes/No): Yes 62368เล่ม1-2563</li>
                </ul>

                <h4 class="section-sub-title">รายละเอียดการแลกรางวัล</h4>
                <p class="section-desc-text">*แลกใช้เพียง 18,725 พอยท์ เพื่อแลกรับฟรี ACONATIC สมาร์ททีวี มูลค่า 7,490
                    บาท โดยแต้มจะถูกหักทันทีหลังการยืนยันผ่านแอปพลิเคชัน</p>

                <h4 class="section-sub-title">ระยะเวลาโปรโมชั่น</h4>
                <p class="section-desc-text">เริ่มตั้งแต่วันที่ 29/01/2025 จนถึง 29/01/2026 หรือจนกว่าสินค้าจะหมด</p>

                <h4 class="section-sub-title">เงื่อนไขการใช้สิทธิ์</h4>
                <div class="section-desc-text">
                    <ul class="specs-list">
                        <li>สามารถแลกรับสิทธิ์ได้ตั้งแต่วันที่ 29 ม.ค. 2025 - 29 ม.ค. 2026</li>
                        <li>สิทธิพิเศษนี้ไม่สามารถโอนสิทธิ์, จำหน่าย หรือแลกเปลี่ยนเป็นเงินสดได้ในทุกกรณี</li>
                        <li>บริษัทขอสงวนสิทธิ์ในการยกเลิกหรือคืนคะแนนในทุกกรณีเมื่อยืนยันการแลกคะแนนเสร็จสมบูรณ์แล้ว
                        </li>
                        <li>กรุณาตรวจสอบสภาพสินค้าและความครบถ้วน ณ จุดรับสินค้า</li>
                    </ul>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer Section (Original Width) -->
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
                            <a href="terms" class="footer-link">ข้อกำหนดและเงื่อนไข</a>
                        </div>
                        <div class="col-6 footer-column text-center">
                            <h5 class="fw-bold mb-3" style="font-size: 0.9rem;">คำถามที่พบบ่อย</h5>
                            <a href="faq" class="footer-link">นโยบายความเป็นส่วนตัว</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4"></div>
            </div>
        </div>
    </footer>

    <!-- Redeem Modal -->
    <div class="modal fade" id="redeemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
            <div class="modal-content modal-qr-content">
                <div class="modal-qr-blue-top">
                    <span
                        style="font-size: 0.8rem; opacity: 0.8; text-transform: uppercase;">ดำเนินการต่อบนแอปพลิเคชัน</span>
                    <h5>สแกน Qr Code</h5>
                    <button type="button" class="btn-qr-close" data-bs-dismiss="modal">
                        <i class="fas fa-xmark"></i>
                    </button>
                    <div class="qr-frame">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=170x170&data=AEG-Redeem-Reward"
                            alt="QR" class="w-100">
                    </div>
                </div>
                <div class="modal-qr-footer">
                    สแกนคิวอาร์โค้ดเพื่อดำเนินการแลกรางวัลผ่านแอป
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>