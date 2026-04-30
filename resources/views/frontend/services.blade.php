<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สิทธิพิเศษสำหรับสมาชิก EASE CLUB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-dark: #1a1a2e;
            --primary-red: #c41e3a;
            --primary-purple: #4a1c40;
            --brand-gradient: linear-gradient(90deg, #c41e3a, #4a1c40);
            --text-blue: #1a365d;
        }

        body {
            font-family: 'Poppins', sans-serif !important;
            background-color: #f8f9fa;
            color: #333;
        }

        /* --- Navbar --- */
        .navbar {
            background-image: url('assets/image/header-bk.webp');
            background-size: cover;
            padding: 10px 0;
        }

        .navbar-brand img {
            height: 50px;
        }

        .brand-text {
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            text-decoration: none;
        }

        .brand-text .ease {
            color: #f1c40f;
        }

        .search-input {
            border-radius: 25px;
            border: none;
            padding: 10px 20px;
            width: 100%;
            max-width: 400px;
        }

        .points-badge {
            background: white;
            color: var(--primary-red);
            border-radius: 20px;
            padding: 5px 15px;
            font-weight: bold;
        }

        /* --- Hero Slider --- */
        .carousel-item img {
            height: 400px;
            object-fit: cover;
            border-radius: 20px;
        }

        /* --- Page Headers --- */
        .gradient-title {
            background: var(--brand-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 700;
        }

        /* --- Unified Box with Specific Opacity Gradient --- */
        .privilege-unified-box {
            /* ไล่สี: แดง 15% (rgba 0.15) -> น้ำเงิน 10% (rgba 0.10) -> หายไป 0% */
            background: linear-gradient(180deg,
                    rgba(244, 22, 41, 0.15) 0%,
                    /* สีแดงทึบ 15% */
                    rgba(23, 46, 89, 0.10) 50%,
                    /* สีน้ำเงินทึบ 10% */
                    rgba(248, 249, 250, 0) 100%
                    /* จางหายไป 0% */
                );
            border-radius: 30px;
            padding: 60px 20px;
            margin-top: 30px;
        }

        /* --- Tabs System --- */
        .privilege-nav {
            justify-content: center;
            border: none;
            gap: 15px;
            overflow-x: auto;
            flex-wrap: nowrap;
            padding-bottom: 15px;
        }

        .privilege-nav .nav-link {
            border: none !important;
            background: transparent !important;
            color: #555;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            width: 120px;
            transition: 0.3s;
        }

        .nav-icon-wrapper {
            width: 85px;
            height: 85px;
            border-radius: 50%;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            transition: 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .nav-icon-wrapper img {
            width: 100%;
            height: 145%;
            object-fit: cover;
        }

        .icon-active {
            display: none;
        }

        .nav-link.active .nav-icon-wrapper {
            border: none !important;
            box-shadow: 0 8px 20px rgba(196, 30, 58, 0.2);
        }

        .nav-link.active .icon-default {
            display: none;
        }

        .nav-link.active .icon-active {
            display: block;
        }

        .nav-link.active span {
            color: var(--primary-red);
            font-weight: 700;
        }

        /* --- Reward Card --- */
        .reward-card {
            background: #ffffff;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: none;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .reward-img-container img {
            width: 100%;
            display: block;
        }

        .reward-body {
            padding: 25px 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .reward-title {
            font-weight: 800;
            color: var(--text-blue);
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .reward-desc {
            font-size: 1.05rem;
            color: #4a5568;
            line-height: 1.4;
            margin-bottom: 25px;
        }

        .reward-points-wrapper {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            margin-bottom: 10px;
        }

        .coin-icon {
            width: 30px;
            height: 30px;
        }

        .points-text {
            color: #b1272c;
            font-weight: 800;
            font-size: 1.6rem;
        }

        .dashed-line {
            border-top: 2px dashed #e2e8f0;
            margin: 5px 0 15px 0;
            width: 100%;
        }

        .reward-expiry-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .clock-icon {
            width: 22px;
        }

        .expiry-text {
            font-weight: 600;
            color: #2d3748;
            font-size: 0.9rem;
        }

        .expiry-date {
            font-weight: 700;
            color: var(--text-blue);
        }

        footer {
            background: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .carousel-item img {
                height: 200px;
            }

            .privilege-nav .nav-link {
                width: 100px;
            }

            .nav-icon-wrapper {
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <div class="row w-100 align-items-center">
                <div class="col-6 col-lg-3">
                    <a class="navbar-brand brand-text" href="#">
                        <img src="assets/image/logo.webp" alt="Logo" class="me-2">
                        <span class="ease">EASE</span> CLUB
                    </a>
                </div>
                <div class="col-lg-6 d-none d-lg-block text-center">
                    <input type="text" class="search-input shadow-sm" placeholder="ค้นหา">
                </div>
                <div class="col-6 col-lg-3 text-end">
                    <div class="points-badge d-inline-flex align-items-center">
                        <i class="fas fa-coins me-2" style="color: #f1c40f;"></i> 200
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div id="heroCarousel" class="carousel slide shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="assets/image/slider.webp" class="d-block w-100" alt="Banner 1">
                </div>
                <div class="carousel-item">
                    <img src="assets/image/slider.webp" class="d-block w-100" alt="Banner 2">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
            </button>
        </div>

        <div class="privilege-unified-box">
            <div class="text-center mb-5">
                <h1 class="gradient-title">สิทธิพิเศษสำหรับสมาชิก EASE CLUB</h1>
                <p class="text-muted">สะสมพอยท์เพื่อแลกของรางวัล และดีลพิเศษมากมาย</p>
            </div>

            <div class="privilege-tabs-container mb-5">
                <ul class="nav nav-pills privilege-nav" id="pills-tab" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#cat-aeg">
                            <div class="nav-icon-wrapper">
                                <img src="assets/image/w-discount.webp" class="icon-default">
                                <img src="assets/image/gd-discount.webp" class="icon-active">
                            </div>
                            <span>ส่วนลด AEG</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cat-food">
                            <div class="nav-icon-wrapper">
                                <img src="assets/image/w-bev.webp" class="icon-default">
                                <img src="assets/image/gd-bev.webp" class="icon-active">
                            </div>
                            <span>อาหารและเครื่องดื่ม</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cat-shopping">
                            <div class="nav-icon-wrapper">
                                <img src="assets/image/w-shop.webp" class="icon-default">
                                <img src="assets/image/gd-shop.webp" class="icon-active">
                            </div>
                            <span>ช้อปปิ้ง</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cat-travel">
                            <div class="nav-icon-wrapper">
                                <img src="assets/image/w-vaca.webp" class="icon-default">
                                <img src="assets/image/gd-vaca.webp" class="icon-active">
                            </div>
                            <span>ท่องเที่ยว</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cat-health">
                            <div class="nav-icon-wrapper">
                                <img src="assets/image/w-health.webp" class="icon-default">
                                <img src="assets/image/gd-health.webp" class="icon-active">
                            </div>
                            <span>สุขภาพ</span>
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cat-others">
                            <div class="nav-icon-wrapper">
                                <img src="assets/image/w-others.webp" class="icon-default">
                                <img src="assets/image/gd-others.webp" class="icon-active">
                            </div>
                            <span>อื่น ๆ</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="cat-aeg">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <div class="reward-card">
                                <div class="reward-img-container"><img src="assets/image/asd.webp"></div>
                                <div class="reward-body">
                                    <h3 class="reward-title">ANGLO EAST GROUP</h3>
                                    <p class="reward-desc">ส่วนลดมูลค่า 1,000 บาท สำหรับการติดตั้งและค่าบริการของ AEG
                                    </p>
                                    <div class="reward-points-wrapper"><img src="assets/image/objects.webp"
                                            class="coin-icon"><span class="points-text">4,000</span></div>
                                    <div class="dashed-line"></div>
                                    <div class="reward-expiry-wrapper">
                                        <img src="assets/image/clock-icon.webp" class="clock-icon">
                                        <span class="expiry-text">วันหมดอายุ : <span
                                                class="expiry-date">31/10/2025</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <div class="reward-card">
                                <div class="reward-img-container"><img src="assets/image/asd.webp"></div>
                                <div class="reward-body">
                                    <h3 class="reward-title">ANGLO EAST GROUP</h3>
                                    <p class="reward-desc">ส่วนลดมูลค่า 1,000 บาท สำหรับการติดตั้งและค่าบริการของ AEG
                                    </p>
                                    <div class="reward-points-wrapper"><img src="assets/image/objects.webp"
                                            class="coin-icon"><span class="points-text">4,000</span></div>
                                    <div class="dashed-line"></div>
                                    <div class="reward-expiry-wrapper">
                                        <img src="assets/image/clock-icon.webp" class="clock-icon">
                                        <span class="expiry-text">วันหมดอายุ : <span
                                                class="expiry-date">31/10/2025</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="cat-food">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <div class="reward-card">
                                <div class="reward-img-container"><img src="assets/image/asd.webp"></div>
                                <div class="reward-body">
                                    <h3 class="reward-title">Starbucks Coffee</h3>
                                    <p class="reward-desc">คูปองเงินสดมูลค่า 100 บาท สำหรับซื้อเครื่องดื่มและอาหาร</p>
                                    <div class="reward-points-wrapper"><img src="assets/image/objects.webp"
                                            class="coin-icon"><span class="points-text">1,500</span></div>
                                    <div class="dashed-line"></div>
                                    <div class="reward-expiry-wrapper">
                                        <img src="assets/image/clock-icon.webp" class="clock-icon">
                                        <span class="expiry-text">วันหมดอายุ : <span
                                                class="expiry-date">31/12/2026</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="cat-shopping">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <div class="reward-card">
                                <div class="reward-img-container"><img src="assets/image/asd.webp"></div>
                                <div class="reward-body">
                                    <h3 class="reward-title">Central Department</h3>
                                    <p class="reward-desc">ส่วนลดเพิ่ม 10% เมื่อช้อปครบ 2,000 บาทขึ้นไป</p>
                                    <div class="reward-points-wrapper"><img src="assets/image/objects.webp"
                                            class="coin-icon"><span class="points-text">2,500</span></div>
                                    <div class="dashed-line"></div>
                                    <div class="reward-expiry-wrapper">
                                        <img src="assets/image/clock-icon.webp" class="clock-icon">
                                        <span class="expiry-text">วันหมดอายุ : <span
                                                class="expiry-date">15/05/2026</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="cat-travel">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <div class="reward-card">
                                <div class="reward-img-container"><img src="assets/image/asd.webp"></div>
                                <div class="reward-body">
                                    <h3 class="reward-title">ATLANTIS MANOR</h3>
                                    <p class="reward-desc">ฟรี เข้าพักโรงแรม ATLANTIS MANOR บางแสน ROOM TYPE A มูลค่า
                                        4,500 บาท</p>
                                    <div class="reward-points-wrapper"><img src="assets/image/objects.webp"
                                            class="coin-icon"><span class="points-text">11,250</span></div>
                                    <div class="dashed-line"></div>
                                    <div class="reward-expiry-wrapper">
                                        <img src="assets/image/clock-icon.webp" class="clock-icon">
                                        <span class="expiry-text">วันหมดอายุ : <span
                                                class="expiry-date">31/10/2025</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="cat-health">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <div class="reward-card">
                                <div class="reward-img-container"><img src="assets/image/asd.webp"></div>
                                <div class="reward-body">
                                    <h3 class="reward-title">BDMS Health Care</h3>
                                    <p class="reward-desc">แพ็กเกจตรวจสุขภาพเบื้องต้น 12 รายการ สำหรับสมาชิก EASE</p>
                                    <div class="reward-points-wrapper"><img src="assets/image/objects.webp"
                                            class="coin-icon"><span class="points-text">5,000</span></div>
                                    <div class="dashed-line"></div>
                                    <div class="reward-expiry-wrapper">
                                        <img src="assets/image/clock-icon.webp" class="clock-icon">
                                        <span class="expiry-text">วันหมดอายุ : <span
                                                class="expiry-date">30/09/2026</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="cat-others">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg-4">
                            <div class="reward-card">
                                <div class="reward-img-container"><img src="assets/image/asd.webp"></div>
                                <div class="reward-body">
                                    <h3 class="reward-title">Major Cineplex</h3>
                                    <p class="reward-desc">แลกบัตรชมภาพยนตร์ที่นั่งปกติ 1 ที่นั่ง (ทุกเรื่อง ทุกรอบ)</p>
                                    <div class="reward-points-wrapper"><img src="assets/image/objects.webp"
                                            class="coin-icon"><span class="points-text">1,200</span></div>
                                    <div class="dashed-line"></div>
                                    <div class="reward-expiry-wrapper">
                                        <img src="assets/image/clock-icon.webp" class="clock-icon">
                                        <span class="expiry-text">วันหมดอายุ : <span
                                                class="expiry-date">31/12/2026</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <div class="row mb-3">
                <div class="col-12">
                    <a href="#" class="footer-link mx-2">ข้อกำหนดและเงื่อนไข</a> |
                    <a href="#" class="footer-link mx-2">นโยบายความเป็นส่วนตัว</a>
                </div>
            </div>
            <div class="social-icons">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-line"></i></a>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>