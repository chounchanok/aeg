<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AEG Smart Locker - บริการตู้เซฟนิรภัย</title>
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

            /* Table Colors from Locker package ease clib 1.png */
            --table-header-bg: #0d5b5b;
            /* Dark Teal */
            --table-subhead-bg: #f2e8b6;
            /* Pale Gold */
            --table-row-label-bg: #0d5b5b;
            --table-cell-bg: #0d5b5b;
            --table-border: #ffffff;
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

        /* --- Main Locker Detail Content --- */
        .detail-wrapper {
            padding: 50px 0 100px;
        }

        .locker-detail-card {
            background: white;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            max-width: 1100px;
            margin: 0 auto;
            border: none;
        }

        .locker-banner {
            width: 100%;
            height: auto;
            display: block;
        }

        .detail-body {
            padding: 45px 50px;
        }

        .locker-main-title {
            text-align: center;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 5px;
        }

        .locker-sub-title {
            text-align: center;
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 35px;
        }

        .locker-intro-text {
            font-size: 1rem;
            color: #444;
            line-height: 1.7;
            text-align: center;
            max-width: 900px;
            margin: 0 auto 40px;
        }

        /* --- Updated Price Table Styles (Match Locker package ease clib 1.png) --- */
        .table-responsive-custom {
            margin-bottom: 40px;
            border-radius: 20px;
            overflow-x: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }

        .price-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 800px;
            /* Ensures scroll on mobile */
        }

        .price-table thead .main-header-row th {
            background-color: var(--table-header-bg);
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .price-table thead .sub-header-row th {
            background-color: var(--table-subhead-bg);
            color: #000;
            padding: 12px;
            text-align: center;
            font-weight: 600;
            border: 1px solid var(--table-border);
            font-size: 0.95rem;
        }

        /* Diagonal Line Top Left Cell */
        .header-corner {
            position: relative;
            background: linear-gradient(to top right, var(--table-subhead-bg) 49.5%, var(--table-border) 49.5%, var(--table-border) 50.5%, var(--table-header-bg) 50.5%) !important;
            padding: 0 !important;
            min-width: 150px;
        }

        .header-corner .label-time {
            position: absolute;
            top: 5px;
            right: 10px;
            color: white;
            font-size: 0.8rem;
        }

        .header-corner .label-size {
            position: absolute;
            bottom: 5px;
            left: 10px;
            color: #000;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .price-table tbody tr td {
            background-color: var(--table-cell-bg);
            color: white;
            padding: 18px 10px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            font-weight: 500;
        }

        .price-table .row-label-cell {
            background-color: var(--table-row-label-bg) !important;
            text-align: center !important;
            font-weight: 700 !important;
            font-size: 1.1rem !important;
        }

        .row-label-cell span {
            display: block;
            font-weight: 400;
            font-size: 0.75rem;
            color: #edb314;
            /* Gold accent for dimensions */
        }

        .price-promo {
            color: #ff3b3b;
            font-weight: 700;
            display: block;
        }

        .price-old {
            text-decoration: line-through;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.85rem;
            display: block;
        }

        /* Additional Info & Terms */
        .terms-section {
            font-size: 0.9rem;
            color: #666;
            line-height: 1.8;
            margin-bottom: 50px;
        }

        .promo-highlight {
            color: var(--primary-red);
            font-weight: 600;
        }

        /* Action Button */
        .btn-booking {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 12px 65px;
            font-size: 1.25rem;
            font-weight: 600;
            text-decoration: none;
            display: block;
            margin: 0 auto;
            width: fit-content;
            box-shadow: 0 5px 20px rgba(196, 30, 58, 0.3);
            transition: 0.3s;
        }

        .btn-booking:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(196, 30, 58, 0.4);
            opacity: 0.95;
        }

        /* --- Success/QR Code Modal --- */
        .modal-qr-content {
            border-radius: 20px !important;
            border: none;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
        }

        .modal-qr-blue-section {
            background-color: var(--primary-dark);
            color: white;
            padding: 25px 20px 40px;
            text-align: center;
            position: relative;
        }

        .modal-qr-title {
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 1.5rem;
            letter-spacing: 0.5px;
        }

        .btn-qr-close {
            position: absolute;
            right: 20px;
            top: 20px;
            background: transparent;
            border: none;
            color: white;
            font-size: 1.2rem;
            line-height: 1;
            cursor: pointer;
            transition: 0.2s;
            opacity: 0.8;
            z-index: 10;
        }

        .qr-image-container {
            display: inline-block;
            background: white;
            padding: 12px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .qr-footer-info {
            background-color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .qr-instruction {
            font-size: 0.95rem;
            color: #333;
            margin: 0;
            font-weight: 400;
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

            .detail-body {
                padding: 30px 20px;
            }

            .locker-main-title {
                font-size: 1.5rem;
            }

            .locker-sub-title {
                font-size: 1.2rem;
            }

            .btn-booking {
                width: 100%;
                max-width: 300px;
                font-size: 1.1rem;
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
                    <div class="points-badge">
                        <i class="fas fa-coins" style="color: #f1c40f;"></i> 200
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Detail Content -->
    <main class="detail-wrapper">
        <div class="container">
            <div class="locker-detail-card">
                <!-- Banner Image -->
                <img src="assets/image/locker-banner-full.webp" alt="AEG Smart Locker Banner" class="locker-banner"
                    onerror="this.src='https://images.unsplash.com/photo-1558223124-789a74288005?auto=format&fit=crop&w=1200&q=80'">

                <div class="detail-body">
                    <h1 class="locker-main-title">AEG Smart Locker</h1>
                    <h2 class="locker-sub-title">บริการให้เช่าตู้เซฟนิรภัย ในห้องนิรภัย</h2>

                    <p class="locker-intro-text">
                        AEG Smart Locker
                        คือบริการเช่าตู้เซฟนิรภัยภายในห้องนิรภัยมาตรฐานสากลที่มีความเป็นส่วนตัวและมีความปลอดภัยสูง
                        ด้วยระบบรักษาความปลอดภัยขั้นสูงที่ออกแบบมาเพื่อเก็บรักษาทรัพย์สินมูลค่าสูงโดยเฉพาะ
                        มาพร้อมระบบความปลอดภัยขั้นสูง ถึง 4 ขั้น ทั้งระบบควบคุมการเข้า —
                        ออกรวมถึงระบบยืนยันตัวตนทางชีวภาพ (Biometrics) 2 ขั้นเต็มรูปแบบ และให้ความเป็นส่วนตัวสูงสุด
                        ภายใต้แนวคิด "Full Privacy, Maximum Security"
                    </p>

                    <!-- Price Table Section (Responsive with custom colors) -->
                    <div class="table-responsive-custom">
                        <table class="price-table">
                            <thead>
                                <tr class="main-header-row">
                                    <th colspan="7">ค่าธรรมเนียมการเช่า (บาท)</th>
                                </tr>
                                <tr class="sub-header-row">
                                    <th class="header-corner">
                                        <span class="label-time">ระยะเวลา</span>
                                        <span class="label-size">ขนาด</span>
                                    </th>
                                    <th>3 เดือน</th>
                                    <th>6 เดือน</th>
                                    <th>9 เดือน</th>
                                    <th>1 ปี</th>
                                    <th>3 ปี</th>
                                    <th>6 ปี</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="row-label-cell">Prime <span>ขนาดเล็ก (H:5", L:12", W:18")</span></td>
                                    <td>4,200.-</td>
                                    <td>8,050.-</td>
                                    <td>11,550.-</td>
                                    <td>14,000.-</td>
                                    <td>
                                        <span class="price-promo">39,900.-</span>
                                        <span class="price-old">42,000.-</span>
                                    </td>
                                    <td>
                                        <span class="price-promo">75,600.-</span>
                                        <span class="price-old">84,000.-</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="row-label-cell">Privilege <span>ขนาดใหญ่ (H:10", L:12", W:18")</span>
                                    </td>
                                    <td>4,800.-</td>
                                    <td>9,200.-</td>
                                    <td>13,200.-</td>
                                    <td>16,000.-</td>
                                    <td>
                                        <span class="price-promo">45,600.-</span>
                                        <span class="price-old">48,000.-</span>
                                    </td>
                                    <td>
                                        <span class="price-promo">86,400.-</span>
                                        <span class="price-old">96,000.-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Additional Terms -->
                    <div class="terms-section text-center">
                        <p>โปรดนำบัตรประชาชนตัวจริง หรือพาสปอร์ต
                            ตัวจริงมาในวันที่ลงทะเบียนที่สาขา<br>ลูกค้าสามารถเข้ารับบริการได้ภายใน 30 วัน
                            หลังจากชำระค่าบริการ</p>
                        <p class="promo-highlight">สัญญา 3 ปี ได้รับส่วนลด 5% / สัญญา 6 ปี ได้รับส่วนลด 10%</p>
                        <p>* ครั้งแรกของการทำสัญญาต้องชำระค่ามัดจำ<br>เป็นจำนวน 5,000 บาท
                            (ได้รับคืนเต็มจำนวนเมื่อสิ้นสุดสัญญา)<br>* ราคาข้างต้นยังไม่รวมภาษีมูลค่าเพิ่ม 7%</p>
                    </div>

                    <!-- Book Button -->
                    <button type="button" class="btn-booking" data-bs-toggle="modal"
                        data-bs-target="#qrModal">จองเลย</button>
                </div>
            </div>
        </div>
    </main>

    <!-- QR Code Modal (Match Locker2-2.jpg 100%) -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content modal-qr-content">
                <div class="modal-qr-blue-section">
                    <h5 class="modal-qr-title">สแกน Qr Code</h5>
                    <button type="button" class="btn-qr-close" data-bs-dismiss="modal">
                        <i class="fas fa-xmark"></i>
                    </button>
                    <div class="qr-image-container">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=AEG-Smart-Locker-Booking"
                            alt="Booking QR Code">
                    </div>
                </div>
                <div class="qr-footer-info">
                    <p class="qr-instruction">สแกนคิวอาร์โค้ดเพื่อดำเนินการต่อผ่านแอปพลิเคชัน</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Chat -->
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
</body>

</html>