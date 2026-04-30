<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สถานะการแจ้งซ่อม - AEG EASE CLUB</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Kanit:wght@200;300;400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary-dark: #1a1a2e;
            --primary-red: #c41e3a;
            --primary-navy: #1a2d5e;
            --status-yellow: #F7E5A1;
            --status-green: #28a745;
            --card-bg: #EBEDF4;
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

        /* --- Header & Navigation --- */
        .navbar-main-header {
            background-image: url('assets/image/header-bk.webp');
            background-size: cover;
            background-position: center;
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
            transition: opacity 0.3s;
        }

        .header-dropdown .btn-dropdown {
            color: white;
            background: transparent;
            border: none;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .header-dropdown .dropdown-menu {
            background: var(--primary-dark);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 10px !important;
            z-index: 1050;
        }

        .header-dropdown .dropdown-item {
            color: white;
            font-size: 0.85rem;
            padding: 8px 15px;
        }

        .header-dropdown .dropdown-item:hover {
            background: var(--primary-red);
            color: white;
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
            padding: 8px 20px;
            border: none;
            width: 100%;
            font-size: 0.95rem;
        }

        .search-btn {
            position: absolute;
            right: 15px;
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
            font-size: 0.9rem;
        }

        .main-navigation-bar {
            background: #fff;
            border-bottom: 1px solid #eee;
            z-index: 1000;
        }

        .nav-link-custom {
            font-weight: 500;
            color: var(--primary-dark);
            padding: 15px 20px !important;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }

        .nav-link-custom:hover,
        .nav-link-custom.active {
            color: var(--primary-red);
            border-bottom: 3px solid var(--primary-red);
        }

        /* --- Main Status Content --- */
        .content-container-950 {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .status-wrapper {
            padding: 60px 0 100px;
        }

        .status-card {
            background: white;
            border-radius: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 45px 50px;
            border: 1px solid #eee;
        }

        .status-hero {
            display: flex;
            gap: 35px;
            margin-bottom: 25px;
        }

        .pkg-img-box {
            width: 320px;
            height: 200px;
            border-radius: 18px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .pkg-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .pkg-info-side {
            flex-grow: 1;
        }

        .pkg-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .pkg-main-title {
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--primary-navy);
            margin: 0;
            line-height: 1.2;
        }

        .tag-red-label {
            color: var(--primary-red);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .job-details-text {
            font-size: 0.75rem;
            color: #555;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .badge-repair-round {
            background-color: var(--primary-navy);
            color: white;
            font-size: 0.75rem;
            padding: 4px 15px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-block;
        }

        .dates-summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 15px;
        }

        .summary-label {
            display: block;
            font-size: 0.75rem;
            color: #888;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .summary-value {
            font-weight: 700;
            color: var(--primary-red);
            font-size: 0.95rem;
        }

        /* --- Stepper Component --- */
        .stepper-container {
            position: relative;
            padding: 40px 0;
            margin-bottom: 40px;
            text-align: center;
        }

        .current-status-tag {
            background-color: var(--status-yellow);
            color: #d12e3e;
            display: inline-block;
            padding: 6px 40px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .stepper-line-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 650px;
            margin: 0 auto;
            position: relative;
        }

        .stepper-line-wrapper::before {
            content: "";
            position: absolute;
            top: 25px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: #ddd;
            z-index: 1;
        }

        .stepper-line-wrapper::after {
            content: "";
            position: absolute;
            top: 25px;
            left: 0;
            width: 66%;
            height: 3px;
            background-color: var(--status-green);
            z-index: 2;
        }

        .step-item {
            position: relative;
            z-index: 3;
            width: 120px;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            background-color: #ddd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            color: white;
            font-size: 1.2rem;
            border: 5px solid white;
        }

        .step-item.completed .step-circle,
        .step-item.active .step-circle {
            background-color: var(--status-green);
        }

        .step-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #333;
        }

        .info-block {
            margin-bottom: 25px;
        }

        .info-block-label {
            display: block;
            font-weight: 700;
            font-size: 1.15rem;
            color: #000;
            margin-bottom: 8px;
        }

        .info-block-value {
            font-weight: 500;
            color: #333;
            font-size: 1rem;
        }

        .scope-list-red {
            list-style: none;
            padding-left: 0;
            margin-bottom: 40px;
        }

        .scope-list-red li {
            position: relative;
            padding-left: 20px;
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .scope-list-red li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: var(--primary-red);
            font-weight: 900;
            font-size: 1.2rem;
        }

        .card-footer-right {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn-navy-back {
            background-color: var(--primary-navy);
            color: white !important;
            border-radius: 8px;
            padding: 10px 45px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-navy-back:hover {
            opacity: 0.95;
        }

        /* --- Footer --- */
        footer {
            background: var(--ease-gradient);
            color: white;
            padding: 40px 0 20px;
        }

        .footer-divider {
            border-right: 2px solid rgba(255, 255, 255, 0.3) !important;
        }

        .social-icons a {
            color: white;
            font-size: 1.3rem;
            margin: 0 10px;
            transition: color 0.3s;
        }

        .social-icons a:hover {
            color: #f1c40f;
        }

        .copyright-text {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 991px) {
            .navbar-top-row {
                display: none;
            }

            .status-hero {
                flex-direction: column;
                gap: 25px;
            }

            .pkg-img-box {
                width: 100%;
                height: 200px;
            }

            .status-card {
                padding: 30px 20px;
                border-radius: 30px;
            }

            .stepper-line-wrapper {
                max-width: 100%;
            }

            .step-item {
                width: 25%;
            }

            .footer-divider {
                border-right: none !important;
            }

            /* --- บังคับไอคอนแฮมเบอร์เกอร์สีขาว --- */
            .navbar-toggler {
                border-color: rgba(255, 255, 255, 0.7) !important;
            }

            .navbar-toggler-icon {
                background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255, 255, 255, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E") !important;
            }
        }
    </style>
</head>

<body>

    <header class="navbar-main-header">
        <div class="container navbar-container">
            <div class="navbar-top-row w-100">
                <div class="nav-icons ms-auto">
                    <a href="#" class="nav-icon-item"><i class="fas fa-headset"></i><span>ติดตามสถานะ</span></a>
                    <a href="#" class="nav-icon-item"><i class="fas fa-bell"></i><span>การแจ้งเตือน</span></a>

                    <div class="dropdown header-dropdown">
                        <button class="btn-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-user"></i><span>ข้อมูลของฉัน</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i
                                        class="fas fa-id-card-alt me-2"></i>ข้อมูลของฉัน</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider border-secondary">
                            </li>
                            <li><a class="dropdown-item text-warning" href="#"><i
                                        class="fas fa-sign-out-alt me-2"></i>ออกจากระบบ</a></li>
                        </ul>
                    </div>

                    <span style="color: rgba(255,255,255,0.5);">|</span>

                    <div class="dropdown header-dropdown">
                        <button class="btn-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <img src="https://flagcdn.com/w20/th.png" alt="TH" width="20">
                            <span>TH</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="#"><img
                                        src="https://flagcdn.com/w20/th.png" width="18"> Thai (TH)</a></li>
                            <li><a class="dropdown-item d-flex align-items-center gap-2" href="#"><img
                                        src="https://flagcdn.com/w20/gb.png" width="18"> English (EN)</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="navbar-bottom-row w-100 mt-2">
                <a class="navbar-brand" href="index"><img src="assets/image/logo.webp" alt="AEG Logo"></a>
                <div class="search-container mx-lg-4 flex-grow-1 d-none d-md-block">
                    <input type="text" class="search-input" placeholder="ค้นหาบริการหรือสินค้า...">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                </div>
                <div class="cart-section">
                    <a href="#" class="cart-icon" style="color: white; margin-right: 15px; font-size: 1.5rem;"><i
                            class="fas fa-shopping-cart"></i></a>
                    <div class="points-badge shadow-sm"><i class="fas fa-coins" style="color: #f1c40f;"></i> 200</div>
                    <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse"
                        data-bs-target="#mainMenuCollapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <nav class="main-navigation-bar sticky-top">
        <div class="container">
            <div class="collapse navbar-collapse d-lg-block" id="mainMenuCollapse">
                <ul class="navbar-nav d-flex flex-column flex-lg-row justify-content-center text-center">
                    <li class="nav-item"><a class="nav-link nav-link-custom active" href="index">หน้าหลัก</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="#">สินค้าพร้อมติดตั้ง</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="packages">แพ็กเกจบริการ</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="#">บริการแนะนำ</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="status-wrapper">
        <div class="content-container-950">
            <article class="status-card">
                <div class="status-hero">
                    <div class="pkg-img-box">
                        <img src="https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=600&q=80"
                            alt="Burglary Alarm">
                    </div>
                    <div class="pkg-info-side">
                        <div class="pkg-header-flex">
                            <h1 class="pkg-main-title">Burglary Alarm<br>(ระบบสัญญาณกันขโมย)</h1>
                            <span class="tag-red-label">แจ้งซ่อม</span>
                        </div>
                        <div class="job-details-text">
                            JOB 20250130-001<br>
                            ตรวจเช็คเสียงสัญญาณเตือน<br>
                            คุณ ณพวัฒน์ บุญยืน 099 999 9999
                        </div>
                        <div class="badge-repair-round">ครั้งที่ 3</div>

                        <div class="dates-summary-grid">
                            <div class="date-col">
                                <span class="summary-label">วันที่แจ้ง</span>
                                <span class="summary-value">30 มี.ค. 2025</span>
                            </div>
                            <div class="date-col">
                                <span class="summary-label">วันนัดหมาย</span>
                                <span class="summary-value">31 มี.ค. 2025</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="stepper-container">
                    <div class="current-status-tag">กำลังดำเนินการ</div>
                    <div class="stepper-line-wrapper">
                        <div class="step-item completed">
                            <div class="step-circle"><i class="fas fa-check"></i></div>
                            <div class="step-label">รับเรื่องแล้ว</div>
                        </div>
                        <div class="step-item completed">
                            <div class="step-circle"><i class="fas fa-check"></i></div>
                            <div class="step-label">นัดหมาย</div>
                        </div>
                        <div class="step-item active">
                            <div class="step-circle"><i class="fas fa-check"></i></div>
                            <div class="step-label">กำลังดำเนินการ</div>
                        </div>
                        <div class="step-item">
                            <div class="step-circle"><i class="fas fa-check"></i></div>
                            <div class="step-label">เสร็จสิ้น</div>
                        </div>
                    </div>
                </div>

                <div class="info-block">
                    <span class="info-block-label">แพ็กเกจดูแลรายเดือน :</span>
                    <p class="info-block-value">Burglary Alarm (ระบบสัญญาณกันขโมย)</p>
                </div>
                <div class="info-block">
                    <span class="info-block-label">จำนวนบริการคงเหลือ :</span>
                    <p class="info-block-value fw-bold">7 ครั้ง</p>
                </div>
                <div class="info-block">
                    <span class="info-block-label">รายละเอียดปัญหาและการแก้ไข :</span>
                    <p class="info-block-value">สายสัญญาณชำรุด</p>
                </div>
                <div class="info-block">
                    <span class="info-block-label">ขอบเขตการบริการ :</span>
                    <ul class="scope-list-red">
                        <li>ตรวจสอบ Motion Sensor, Door Contact, Panic Switch</li>
                        <li>ทดสอบการทำงานของสัญญาณเตือน</li>
                        <li>เช็ก Battery Backup / Power Supply</li>
                        <li>ตรวจสอบแผงควบคุม และการเชื่อมต่อ</li>
                        <li>แก้ไขระบบที่ไม่ทำงาน / แจ้งเตือนผิดพลาด</li>
                        <li>ตรวจสอบเซ็นเซอร์เสียงและกล่องควบคุม</li>
                    </ul>
                </div>

                <div class="card-footer-right">
                    <a href="packages" class="btn-navy-back">ย้อนกลับ</a>
                </div>
            </article>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-md-4 text-center text-md-start">
                    <h6 class="fw-bold mb-3">ดาวน์โหลดแอปพลิเคชัน</h6>
                    <div class="d-flex justify-content-center justify-content-md-start gap-3">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=AEG-APP"
                            class="bg-white p-1 rounded" alt="QR" width="80">
                        <div class="d-flex flex-column gap-2">
                            <a href="#"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg"
                                    height="28"></a>
                            <a href="#"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                                    height="28"></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 text-center">
                    <div class="row">
                        <div class="col-6 footer-column footer-divider">
                            <h6 class="fw-bold mb-3">แพ็กเกจที่ใช้งาน</h6>
                            <a href="packages"
                                class="d-block text-white-50 text-decoration-none small mb-2">ข้อกำหนดและเงื่อนไข</a>
                        </div>
                        <div class="col-6 footer-column">
                            <h6 class="fw-bold mb-3">ช่วยเหลือ</h6>
                            <a href="privacy-policy"
                                class="d-block text-white-50 text-decoration-none small mb-2">นโยบายความเป็นส่วนตัว</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 text-center text-md-end">
                    <img src="assets/image/logo.webp" alt="Logo" height="40">
                </div>
            </div>

            <div class="row mt-5 align-items-center">
                <div class="col-md-4 d-none d-md-block"></div>
                <div class="col-md-4 text-center">
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-line"></i></a>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0">
                    <p class="copyright-text mb-0">© 2024 AEG EASE CLUB. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>