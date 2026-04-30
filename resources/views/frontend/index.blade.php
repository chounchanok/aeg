<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AEG EASE CLUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Kanit:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-dark: #1a1a2e;
            --primary-red: #c41e3a;
            --primary-purple: #4a1c40;
            --card-bg: #EBEDF4;
            --privilege-bg: #9899A2;
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #ffffff;
            color: #333;
        }

        /* 1. Header & Navbar */
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
            cursor: pointer;
        }

        /* Dropdown Styles */
        .header-dropdown .btn-dropdown {
            color: white;
            background: transparent;
            border: none;
            font-size: 0.85rem;
            padding: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .header-dropdown .dropdown-menu {
            min-width: 160px;
            background: var(--primary-dark);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 10px !important;
            z-index: 99 !important;
            /* z-index 99 */
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
            padding: 8px 50px 8px 20px;
            border: none;
            width: 100%;
            font-size: 0.95rem;
        }

        .search-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            color: #666;
            border: none;
            font-size: 1.1rem;
        }

        .cart-section {
            display: flex;
            align-items: center;
            gap: 15px;
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

        /* Hamburger Menu Icon: White SVG */
        .navbar-toggler {
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
            padding: 4px 8px;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255, 255, 255, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E") !important;
        }

        /* 2. Main Navigation */
        .main-navigation-bar {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            z-index: 90;
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

        /* 3. Hero Carousel */
        .carousel-item img {
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
        }

        /* 4. Service Cards */
        .service-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
            height: 100%;
            background: var(--card-bg);
        }

        .service-card:hover {
            transform: translateY(-5px);
        }

        .service-card img {
            height: 370px;
            object-fit: cover;
            width: 100%;
        }

        .service-card-body {
            padding: 20px;
            text-align: center;
        }

        .btn-service {
            background: var(--ease-gradient);
            color: white;
            border: none;
            border-radius: 20px;
            padding: 8px 25px;
            font-size: 0.9rem;
        }

        /* 5. Age Services */
        .age-service-box {
            background-color: #EBEDF4 !important;
            border-radius: 15px;
            padding: 20px;
            display: flex;
            align-items: center;
        }

        .age-icon {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-right: 15px;
        }

        /* 6. Recommended Services */
        .rec-card {
            border-radius: 15px;
            overflow: hidden;
            position: relative;
            height: 556px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background: var(--card-bg);
            transition: transform 0.3s ease;
        }

        .rec-card:hover {
            transform: scale(1.02);
        }

        .rec-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .rec-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            padding: 20px;
            color: white;
            text-align: center;
        }

        /* 7. Special Privileges */
        .privilege-section {
            background: var(--privilege-bg);
        }

        .privilege-card {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .privilege-card:hover {
            transform: translateY(-5px);
        }

        .privilege-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        /* 8. Footer */
        footer {
            background: var(--ease-gradient);
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }

        .footer-column {
            padding: 0 20px;
        }

        /* 2px divider for Footer */
        .footer-divider {
            border-right: 2px solid rgba(255, 255, 255, 0.3) !important;
        }

        .social-icons {
            display: flex;
            gap: 20px;
            justify-content: center;
        }

        .social-icons a {
            color: white;
            font-size: 1.3rem;
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

            .carousel-item img {
                height: 250px;
            }

            .footer-column {
                text-align: center !important;
                margin-bottom: 20px;
            }

            .footer-divider {
                border-right: none !important;
            }

            .copyright-text {
                text-align: center;
                margin-top: 15px;
            }

            .rec-card {
                height: 400px;
            }
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <header class="navbar-main-header">
        <div class="container navbar-container">
            <!-- Top Row -->
            <div class="navbar-top-row w-100">
                <div class="nav-icons ms-auto">
                    <a href="#" class="nav-icon-item"><i class="fas fa-headset"></i><span>ติดตามสถานะ</span></a>
                    <a href="#" class="nav-icon-item"><i class="fas fa-bell"></i><span>การแจ้งเตือน</span></a>

                    <!-- My Account Dropdown -->
                    <div class="dropdown header-dropdown">
                        <button class="btn-dropdown dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fas fa-user"></i><span>ข้อมูลของฉัน</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-id-card-alt me-2"></i>
                                    ข้อมูลของฉัน</a></li>
                            <li>
                                <hr class="dropdown-divider border-secondary">
                            </li>
                            <li><a class="dropdown-item text-warning" href="#"><i class="fas fa-sign-out-alt me-2"></i>
                                    ออกจากระบบ</a></li>
                        </ul>
                    </div>

                    <span style="color: rgba(255,255,255,0.5);">|</span>

                    <!-- Language Dropdown -->
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

            <!-- Bottom Row -->
            <div class="navbar-bottom-row w-100 mt-2">
                <a class="navbar-brand" href="#"><img src="assets/image/logo.webp" alt="AEG Logo"></a>
                <div class="search-container mx-lg-4 flex-grow-1 d-none d-md-block">
                    <input type="text" class="search-input" placeholder="ค้นหาบริการหรือสินค้า...">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                </div>
                <div class="cart-section">
                    <a href="#" class="cart-icon"><i class="fas fa-shopping-cart"></i></a>
                    <div class="points-badge shadow-sm"><i class="fas fa-coins" style="color: #f1c40f;"></i> 200</div>
                    <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse"
                        data-bs-target="#mainMenuCollapse">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Navigation Bar -->
    <nav class="main-navigation-bar sticky-top">
        <div class="container">
            <div class="collapse navbar-collapse d-lg-block" id="mainMenuCollapse">
                <ul class="navbar-nav d-flex flex-column flex-lg-row justify-content-center text-center">
                    <li class="nav-item"><a class="nav-link nav-link-custom active" href="#">หน้าหลัก</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="#">สินค้าพร้อมติดตั้ง</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="#">แพ็กเกจบริการ</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="#">บริการแนะนำ</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Slider -->
    <div class="container mt-4">
        <div id="heroCarousel" class="carousel slide shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active"><img src="assets/image/slider.webp" class="d-block w-100"
                        alt="Banner 1"></div>
                <div class="carousel-item"><img src="assets/image/slider.webp" class="d-block w-100" alt="Banner 2">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel"
                data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel"
                data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
        </div>
    </div>

    <!-- Category Grid -->
    <div class="container mt-5">
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="service-card"><img src="assets/image/g1.webp" alt="G1">
                    <div class="service-card-body">
                        <h5 class="fw-bold">สินค้าพร้อมติดตั้ง</h5><button
                            class="btn btn-service mt-2">ดูเพิ่มเติม</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-card"><img src="assets/image/g2.webp" alt="G2">
                    <div class="service-card-body">
                        <h5 class="fw-bold">แพ็กเกจบริการ</h5><button class="btn btn-service mt-2">ดูเพิ่มเติม</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-card"><img src="assets/image/g3.webp" alt="G3">
                    <div class="service-card-body">
                        <h5 class="fw-bold">ประกัน</h5><button class="btn btn-service mt-2">ดูเพิ่มเติม</button>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-card"><img src="assets/image/g4.webp" alt="G4">
                    <div class="service-card-body">
                        <h5 class="fw-bold">ตู้เซฟนิรภัย</h5><button class="btn btn-service mt-2">ดูเพิ่มเติม</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Age Services -->
    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4">บริการที่ใกล้เคียงหมวดอายุ</h3>
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="age-service-box shadow-sm">
                    <img src="assets/image/logo2.webp" class="age-icon" alt="Logo">
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">ประกันทรัพย์สินมูลค่าสูง</h5>
                        <p class="text-muted small mb-0">กรมธรรม์ชดเชยผลประโยชน์จากการโจรกรรม</p>
                    </div>
                    <div class="ms-3 text-end border-start ps-3">
                        <div class="small text-muted">เริ่ม: <span class="text-danger fw-bold">xx/xx/xxxx</span></div>
                        <div class="small text-muted">จบ: <span class="text-danger fw-bold">xx/xx/xxxx</span></div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="age-service-box shadow-sm">
                    <img src="assets/image/logo2.webp" class="age-icon" alt="Logo">
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1">AEG Smart Locker</h5>
                        <p class="text-muted small mb-0">ตู้ล็อกเกอร์ให้เช่า ขนาดเล็ก (Prime)</p>
                    </div>
                    <div class="ms-3 text-end border-start ps-3">
                        <div class="small text-muted">เริ่ม: <span class="text-danger fw-bold">xx/xx/xxxx</span></div>
                        <div class="small text-muted">จบ: <span class="text-danger fw-bold">xx/xx/xxxx</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommended Services Section (Clickable) -->
    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4">บริการแนะนำ</h3>
        <div class="row g-4 mt-3">
            <div class="col-6 col-md-3">
                <a href="product" class="text-decoration-none d-block">
                    <div class="rec-card shadow-sm">
                        <img src="assets/image/img-zo1.webp" alt="Signal System">
                        <div class="rec-overlay">
                            <h5 class="fw-bold text-white">ระบบสัญญาณกันขโมย</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="product" class="text-decoration-none d-block">
                    <div class="rec-card shadow-sm">
                        <img src="assets/image/img-zo2.webp" alt="Smoke Alarm">
                        <div class="rec-overlay">
                            <h5 class="fw-bold text-white">ระบบสัญญาณเตือนอัคคีภัย</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="product" class="text-decoration-none d-block">
                    <div class="rec-card shadow-sm">
                        <img src="assets/image/img-zo3.webp" alt="Gold Cap Lock">
                        <div class="rec-overlay">
                            <h5 class="fw-bold text-white">AEG GOLD CAP-LOCK</h5>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="product" class="text-decoration-none d-block">
                    <div class="rec-card shadow-sm">
                        <img src="assets/image/img-zo4.webp" alt="Access Control">
                        <div class="rec-overlay">
                            <h5 class="fw-bold text-white">ระบบควบคุมการเข้า - ออก</h5>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Special Privileges Section (Clickable) -->
    <div class="container mt-5 mb-5">
        <div class="privilege-section p-4 rounded-4 shadow">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0 text-white">สิทธิพิเศษแนะนำ</h2>
                <a href="#" class="text-white fw-bold text-decoration-none">ดูทั้งหมด <i
                        class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <a href="product" class="text-decoration-none d-block h-100">
                        <div class="privilege-card shadow-sm">
                            <img src="assets/image/rew3.webp" alt="Diffuser">
                            <div class="service-card-body">
                                <h5 class="fw-bold text-dark">Muji Aroma Duffuser L</h5>
                                <div class="text-danger fw-bold mt-2">มูลค่า 3,490 บาท</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="product" class="text-decoration-none d-block h-100">
                        <div class="privilege-card shadow-sm">
                            <img src="assets/image/rew2.webp" alt="Robot Vacuum">
                            <div class="service-card-body">
                                <h5 class="fw-bold text-dark">TEFAL X-Plorer Series 70</h5>
                                <div class="text-danger fw-bold mt-2">มูลค่า 14,990 บาท</div>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="product" class="text-decoration-none d-block h-100">
                        <div class="privilege-card shadow-sm">
                            <img src="assets/image/rew1.webp" alt="Jewelry">
                            <div class="service-card-body">
                                <h5 class="fw-bold text-dark">ANGLO EAST GROUP</h5>
                                <div class="text-danger fw-bold mt-2">ส่วนลด 1,000 บาท</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
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
                            <a href="#"
                                class="d-block text-white-50 text-decoration-none small mb-2">ข้อกำหนดและเงื่อนไข</a>
                        </div>
                        <div class="col-6 footer-column">
                            <h6 class="fw-bold mb-3">ช่วยเหลือ</h6>
                            <a href="#"
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>