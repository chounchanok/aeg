<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('รายละเอียดประวัติ') }} - AEG EASE CLUB</title>
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
            --primary-navy: #1a2d5e;
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #f4f5f7;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Container strictly 950px for content */
        .container-950 {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* --- Header Styles (Match Canvas style) --- */
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
            padding-bottom: 0px !important;
            border-bottom: 0px solid rgba(255, 255, 255, 0.2) !important;
            margin-bottom: 0px !important;
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
            max-width: 450px;
        }

        .search-input {
            border-radius: 25px;
            padding: 8px 50px 8px 20px;
            border: none;
            width: 100%;
            font-size: 0.95rem;
        }

        .search-btn {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            color: #666;
            border: none;
            font-size: 1.1rem;
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
            font-size: 0.85rem;
        }

        /* --- Detail Content --- */
        .detail-wrapper {
            padding: 60px 0 100px;
        }

        .detail-card {
            background: white;
            border-radius: 35px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 50px;
            border: 1px solid #eee;
        }

        /* Top Hero Part */
        .detail-hero {
            display: flex;
            gap: 35px;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #f2f2f2;
        }

        .detail-img-box {
            width: 320px;
            height: 220px;
            border-radius: 15px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .detail-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-summary {
            flex-grow: 1;
        }

        .detail-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .detail-title {
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--primary-navy);
            margin: 0;
        }

        .status-tag {
            font-weight: 700;
            font-size: 0.85rem;
            color: #999;
        }

        .info-block {
            margin-bottom: 20px;
        }

        .info-label {
            display: block;
            font-size: 0.8rem;
            color: #888;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 700;
            color: var(--primary-navy);
            font-size: 1rem;
        }

        .date-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .date-item span {
            color: var(--primary-red);
            font-weight: 700;
        }

        /* Bottom Details Part */
        .detail-body-section {
            padding-top: 10px;
        }

        .body-title {
            font-weight: 700;
            font-size: 1.2rem;
            color: #000;
            margin-bottom: 15px;
            display: block;
        }

        .history-btn-group {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
        }

        .btn-history-round {
            background-color: var(--primary-navy);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 10px 25px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-history-round:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            color: white;
        }

        .scope-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 50px;
        }

        .scope-list li {
            position: relative;
            padding-left: 20px;
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .scope-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: var(--primary-red);
            font-weight: 900;
            font-size: 1.2rem;
        }

        /* Action Footer */
        .detail-footer-btn {
            text-align: right;
        }

        .btn-back-navy {
            background-color: var(--primary-navy);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 40px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
        }

        .btn-back-navy:hover {
            opacity: 0.95;
            color: white;
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

            .detail-hero {
                flex-direction: column;
                gap: 25px;
            }

            .detail-img-box {
                width: 100%;
                height: 200px;
            }

            .detail-card {
                padding: 30px 20px;
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

    <!-- Main Detail Content -->
    <main class="detail-wrapper">
        <div class="container-950">
            <div class="detail-card">

                <!-- Hero Top Section -->
                <div class="detail-hero">
                    <div class="detail-img-box">
                        <img src="https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=600&q=80"
                            alt="Burglary Alarm">
                    </div>
                    <div class="detail-summary">
                        <div class="detail-header-flex">
                            <h1 class="detail-title">Burglary Alarm<br>{{ __('(ระบบสัญญาณกันขโมย)') }}</h1>
                            <span class="status-tag">{{ __('หมดอายุ') }}</span>
                        </div>
                        <div class="info-block">
                            <label class="info-label">{{ __('แพ็กเกจดูแลรายเดือน') }} :</label>
                            <div class="info-value">Burglary Alarm {{ __('(ระบบสัญญาณกันขโมย)') }}</div>
                        </div>
                        <div class="info-block">
                            <label class="info-label">{{ __('ระยะเวลาการดูแล') }} :</label>
                            <div class="date-grid">
                                <div class="date-item">
                                    <label class="info-label">{{ __('เริ่มต้น') }}</label>
                                    <span>01 {{ __('ม.ค.') }} 2024</span>
                                </div>
                                <div class="date-item">
                                    <label class="info-label">{{ __('สิ้นสุด') }}</label>
                                    <span>31 {{ __('มี.ค.') }} 2024</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Body Section -->
                <div class="detail-body-section">
                    <span class="body-title">{{ __('จำนวนบริการคงเหลือ') }} :</span>
                    <p class="fw-bold mb-4" style="font-size: 1.1rem;">8 {{ __('ครั้ง') }}</p>

                    <span class="body-title">{{ __('ประวัติการแจ้งซ่อม') }} :</span>
                    <div class="history-btn-group">
                        <a href="#" class="btn-history-round">{{ __('ครั้งที่ 1') }}</a>
                        <a href="#" class="btn-history-round">{{ __('ครั้งที่ 2') }}</a>
                    </div>

                    <span class="body-title">{{ __('ขอบเขตการบริการ') }} :</span>
                    <ul class="scope-list">
                        <li>{{ __('ตรวจสอบ Motion Sensor, Door Contact, Panic Switch') }}</li>
                        <li>{{ __('ทดสอบการทำงานของสัญญาณเตือน') }}</li>
                        <li>{{ __('เช็ก Battery Backup / Power Supply') }}</li>
                        <li>{{ __('ตรวจสอบแผงควบคุม และการเชื่อมต่อ') }}</li>
                        <li>{{ __('แก้ไขระบบที่ไม่ทำงาน / แจ้งเตือนผิดพลาด') }}</li>
                        <li>{{ __('ตรวจสอบเซ็นเซอร์เสียงและกล่องควบคุม') }}</li>
                    </ul>

                    <div class="detail-footer-btn">
                        <a href="package" class="btn-back-navy">{{ __('ย้อนกลับ') }}</a>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="fw-bold mb-3" style="font-size: 1rem;">{{ __('ดาวน์โหลดแอปพลิเคชัน') }}</h5>
                    <div class="d-flex gap-2">
                        <div class="bg-white p-2 rounded"
                            style="width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">
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
                            <h5 class="fw-bold mb-3" style="font-size: 0.95rem;">{{ __('แพ็กเกจที่ใช้งาน') }}</h5>
                            <a href="package" class="footer-link">{{ __('ข้อกำหนดและเงื่อนไข') }}</a>
                        </div>
                        <div class="col-6 footer-column text-center">
                            <h5 class="fw-bold mb-3" style="font-size: 0.95rem;">{{ __('คำถามที่พบบ่อย') }}</h5>
                            <a href="faq" class="footer-link">{{ __('นโยบายความเป็นส่วนตัว') }}</a>
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
