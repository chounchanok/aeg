<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ให้คะแนนและรีวิว - AEG EASE CLUB</title>
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
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
            --gray-banner: #EBEDF4;
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

        /* --- Content Layout --- */
        .content-container-950 {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .reward-banner-gray {
            background-color: var(--gray-banner);
            text-align: center;
            padding: 10px 0;
            font-size: 0.85rem;
            font-weight: 600;
            color: #c41e3a;
        }

        .feedback-wrapper {
            padding: 50px 0 100px;
        }

        .feedback-card {
            background: white;
            border-radius: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 50px;
            border: 1px solid #eee;
        }

        .feedback-grid {
            display: flex;
            gap: 40px;
        }

        .col-left {
            flex: 0 0 350px;
        }

        .col-right {
            flex: 1;
        }

        .pkg-img-box {
            width: 100%;
            height: 220px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .pkg-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .rating-group {
            margin-bottom: 25px;
        }

        .rating-label {
            display: block;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--primary-navy);
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .star-rating {
            display: flex;
            gap: 10px;
        }

        .star-rating i {
            font-size: 1.8rem;
            color: #ddd;
            cursor: pointer;
            transition: 0.2s;
        }

        .star-rating i.selected {
            color: #f1c40f;
        }

        .pkg-title-main {
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary-navy);
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .pkg-subtitle {
            color: #888;
            font-size: 0.95rem;
            font-weight: 500;
            display: block;
            margin-bottom: 30px;
        }

        .custom-textarea {
            background-color: #ebedf4;
            border: none;
            border-radius: 12px;
            padding: 20px;
            width: 100%;
            min-height: 220px;
            font-size: 0.95rem;
            color: #333;
            resize: none;
        }

        .action-btns-row {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-navy-pill {
            background-color: var(--primary-navy);
            color: white !important;
            border: none;
            border-radius: 10px;
            padding: 10px 45px;
            font-weight: 600;
            font-size: 1.1rem;
            min-width: 160px;
            text-decoration: none;
            text-align: center;
            transition: 0.3s;
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

        @media (max-width: 991px) {
            .navbar-top-row {
                display: none;
            }

            .feedback-grid {
                flex-direction: column;
                gap: 30px;
            }

            .col-left {
                width: 100%;
            }

            .action-btns-row {
                flex-direction: column-reverse;
            }

            .footer-divider {
                border-right: none !important;
            }

            /* Hamburger Menu Icon: White SVG */
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

    <div class="reward-banner-gray">
        <div class="container">
            <i class="fas fa-coins" style="color: #edb314;"></i> ให้คะแนนและเขียนรีวิวเพื่อรับ 1 EASE Coins
        </div>
    </div>

    <main class="feedback-wrapper">
        <div class="content-container-950">
            <div class="feedback-card">
                <div class="feedback-grid">
                    <div class="col-left">
                        <div class="pkg-img-box">
                            <img src="https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=600&q=80"
                                alt="Burglary Alarm">
                        </div>
                        <div class="rating-group">
                            <span class="rating-label">ให้คะแนนคุณภาพงานติดตั้งและบริการหลังการขาย</span>
                            <div class="star-rating" data-id="install">
                                <i class="fas fa-star selected" data-value="1"></i>
                                <i class="fas fa-star selected" data-value="2"></i>
                                <i class="fas fa-star selected" data-value="3"></i>
                                <i class="fas fa-star selected" data-value="4"></i>
                                <i class="fas fa-star" data-value="5"></i>
                            </div>
                        </div>
                        <div class="rating-group">
                            <span class="rating-label">ให้คะแนนคุณภาพการให้คำแนะนำและข้อมูลจากฝ่ายขาย</span>
                            <div class="star-rating" data-id="sales">
                                <i class="fas fa-star selected" data-value="1"></i>
                                <i class="fas fa-star selected" data-value="2"></i>
                                <i class="fas fa-star selected" data-value="3"></i>
                                <i class="fas fa-star selected" data-value="4"></i>
                                <i class="fas fa-star" data-value="5"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-right">
                        <h1 class="pkg-title-main">Burglary Alarm<br>(ระบบสัญญาณกันขโมย)</h1>
                        <span class="pkg-subtitle">แพ็กเกจการดูแลอุปกรณ์</span>
                        <div class="review-block">
                            <span class="review-label">เขียนรีวิว</span>
                            <textarea class="custom-textarea"
                                placeholder="เขียนรีวิวหรือคำแนะนำเพื่อปรับปรุงบริการ"></textarea>
                        </div>
                        <div class="action-btns-row">
                            <a href="packages" class="btn-navy-pill">ย้อนกลับ</a>
                            <button class="btn-navy-pill" onclick="confirmReview()">ยืนยัน</button>
                        </div>
                    </div>
                </div>
            </div>
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

    <script>
        const ratings = document.querySelectorAll('.star-rating');
        ratings.forEach(container => {
            const stars = container.querySelectorAll('i');
            stars.forEach(star => {
                star.onclick = function () {
                    const val = parseInt(this.getAttribute('data-value'));
                    stars.forEach(s => {
                        const sVal = parseInt(s.getAttribute('data-value'));
                        if (sVal <= val) {
                            s.classList.add('selected');
                        } else {
                            s.classList.remove('selected');
                        }
                    });
                };
            });
        });

        function confirmReview() {
            window.location.href = 'packages';
        }
    </script>
</body>

</html>