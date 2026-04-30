<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดชุดระบบสัญญาณกันขโมย - AEG EASE CLUB</title>
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
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
            --btn-gradient: linear-gradient(90deg, #4a1c40 0%, #c41e3a 100%);
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #ffffff;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* --- Header Styles --- */
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

        .cart-section {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .cart-icon {
            font-size: 1.5rem;
            color: white;
            text-decoration: none;
        }

        /* --- Category Navigation Tabs (Responsive 5 Items) --- */
        .category-nav {
            padding: 40px 0;
            background: white;
            border-bottom: 1px solid #f0f0f0;
        }

        .nav-tabs-custom {
            display: flex;
            justify-content: center;
            gap: 10px;
            /* Reduced gap to fit all items */
            border: none;
            flex-wrap: nowrap;
            /* Enforce single row */
            padding-bottom: 15px;
            width: 100%;
        }

        .nav-tabs-custom .nav-link {
            border: none !important;
            padding: 0;
            background: none !important;
            flex: 1;
            /* Force all tabs to take equal width */
            min-width: 0;
            /* Allow shrinking below content size */
            max-width: 220px;
            /* Cap size on large screens */
        }

        .category-box {
            background: #ffffff;
            border: 1px solid #f0f0f0;
            border-radius: 15px;
            padding: 20px 5px;
            width: 100%;
            height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 0.3s ease;
        }

        .category-icon {
            width: 60px;
            height: 60px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .category-icon img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            filter: grayscale(100%);
            opacity: 0.4;
        }

        .category-text-en {
            font-weight: 700;
            font-size: 0.85rem;
            margin-bottom: 2px;
            color: #bbb;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 100%;
        }

        .category-text-th {
            font-weight: 400;
            font-size: 0.75rem;
            color: #ccc;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            width: 100%;
        }

        .nav-link.active .category-box {
            border: 1px solid #eee;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            background-color: #fff;
            transform: translateY(-5px);
        }

        .nav-link.active .category-icon img {
            filter: grayscale(0%);
            opacity: 1;
        }

        .nav-link.active .category-text-en {
            color: #111;
        }

        .nav-link.active .category-text-th {
            color: #444;
        }

        /* --- Product Detail Section --- */
        .detail-section {
            padding: 60px 0 100px;
            background-color: #fff;
        }

        .detail-image-box {
            background: white;
            border: 1px solid #f2f2f2;
            border-radius: 25px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            aspect-ratio: 1 / 0.85;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }

        .detail-image-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .detail-content {
            padding-left: 30px;
        }

        .detail-title {
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .detail-subtitle {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-red);
            margin-bottom: 10px;
        }

        .detail-desc {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .detail-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .detail-list li {
            font-size: 0.95rem;
            color: #333;
            margin-bottom: 8px;
            display: flex;
            gap: 10px;
        }

        /* Buttons Section - Centered */
        .btn-container-centered {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 50px;
            width: 100%;
        }

        .btn-gradient {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 12px 0;
            width: 220px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            box-shadow: 0 4px 15px rgba(196, 30, 58, 0.2);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(196, 30, 58, 0.3);
            opacity: 0.95;
        }

        /* Footer Styles */
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

            .detail-content {
                padding-left: 0;
                margin-top: 30px;
                text-align: center;
            }

            .detail-list {
                display: inline-block;
                text-align: left;
            }

            .btn-container-centered {
                flex-direction: column;
                align-items: center;
                gap: 15px;
                margin-top: 40px;
            }

            .btn-gradient {
                width: 100%;
                max-width: 300px;
            }

            /* Responsive Categories - Keep all 5 visible */
            .category-box {
                height: 120px;
                padding: 10px 2px;
                border-radius: 10px;
            }

            .category-icon {
                width: 35px;
                height: 35px;
                margin-bottom: 5px;
            }

            .category-text-en {
                font-size: 0.6rem;
            }

            .category-text-th {
                font-size: 0.55rem;
            }

            .nav-tabs-custom {
                gap: 5px;
            }
        }

        @media (max-width: 480px) {
            .category-box {
                height: 100px;
            }

            .category-text-en {
                font-size: 0.5rem;
            }

            .category-text-th {
                display: none;
            }

            /* Hide Thai text on very small screens to maintain clarity */
        }
    </style>
</head>

<body>

    <!-- Header Section -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container navbar-container">
            <div class="navbar-top-row w-100">
                <div class="nav-icons ms-auto">
                    <a href="#" class="nav-icon-item"><i class="fas fa-bell"></i><span>การแจ้งเตือน</span></a>
                    <a href="#" class="nav-icon-item"><i class="fas fa-user"></i><span>เข้าสู่ระบบ</span></a>
                    <span style="color: rgba(255,255,255,0.5);">|</span>
                    <div class="lang-selector">
                        <img src="https://flagcdn.com/w20/th.png" alt="TH Flag" width="20">
                        <span>TH</span>
                        <i class="fas fa-chevron-down" style="font-size: 0.7rem;"></i>
                    </div>
                </div>
            </div>

            <div class="navbar-bottom-row w-100">
                <a class="navbar-brand" href="#">
                    <img src="assets/image/logo.webp" alt="AEG Logo"
                        onerror="this.src='https://via.placeholder.com/150x50?text=AEG+LOGO'">
                </a>

                <div class="search-container mx-auto">
                    <input type="text" class="search-input" placeholder="ค้นหา">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                </div>

                <div class="cart-section">
                    <a href="#" class="cart-icon" style="color: white; text-decoration: none;"><i
                            class="fas fa-shopping-cart"></i></a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Tabbed Category Navigation -->
    <div class="category-nav">
        <div class="container">
            <div class="nav nav-tabs nav-tabs-custom" id="securityTabs" role="tablist">
                <button class="nav-link active" type="button">
                    <div class="category-box">
                        <div class="category-icon">
                            <img src="assets/image/cat1.webp" alt="Burglary Alarm"
                                onerror="this.src='https://via.placeholder.com/70x70?text=Burglary'">
                        </div>
                        <div class="category-text-en">Burglary Alarm</div>
                        <div class="category-text-th">ระบบสัญญาณกันขโมย</div>
                    </div>
                </button>
                <button class="nav-link" type="button">
                    <div class="category-box">
                        <div class="category-icon">
                            <img src="assets/image/cat2.webp" alt="Fire Alarm"
                                onerror="this.src='https://via.placeholder.com/70x70?text=Fire'">
                        </div>
                        <div class="category-text-en">Fire Alarm</div>
                        <div class="category-text-th">ระบบสัญญาณเตือนอัคคีภัย</div>
                    </div>
                </button>
                <button class="nav-link" type="button">
                    <div class="category-box">
                        <div class="category-icon">
                            <img src="assets/image/cat3.webp" alt="Electronic Lock"
                                onerror="this.src='https://via.placeholder.com/70x70?text=Lock'">
                        </div>
                        <div class="category-text-en">Electronic Lock</div>
                        <div class="category-text-th">ระบบล็อกอิเล็กทรอนิกส์</div>
                    </div>
                </button>
                <button class="nav-link" type="button">
                    <div class="category-box">
                        <div class="category-icon">
                            <img src="assets/image/cat4.webp" alt="CCTV"
                                onerror="this.src='https://via.placeholder.com/70x70?text=CCTV'">
                        </div>
                        <div class="category-text-en">CCTV</div>
                        <div class="category-text-th">ระบบกล้องวงจรปิด</div>
                    </div>
                </button>
                <button class="nav-link" type="button">
                    <div class="category-box">
                        <div class="category-icon">
                            <img src="assets/image/cat5.webp" alt="Gold Cap Lock"
                                onerror="this.src='https://via.placeholder.com/70x70?text=Gold+Cap'">
                        </div>
                        <div class="category-text-en">AEG Gold Cap-Lock</div>
                        <div class="category-text-th">โกลด์แคป-ล็อก</div>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Product Detail Section -->
    <main class="detail-section">
        <div class="container">
            <div class="row align-items-center">
                <!-- Product Image Column -->
                <div class="col-lg-6 d-flex justify-content-center">
                    <div class="detail-image-box">
                        <img src="assets/image/product-1.webp" alt="ชุดระบบสัญญาณกันขโมย">
                    </div>
                </div>
                <!-- Product Information Column -->
                <div class="col-lg-6">
                    <div class="detail-content">
                        <h1 class="detail-title">ชุดระบบสัญญาณกันขโมย</h1>
                        <h2 class="detail-subtitle">รายละเอียด</h2>
                        <p class="detail-desc">ชุดอุปกรณ์ตรวจจับและแจ้งเตือนความเคลื่อนไหวในพื้นที่ที่กำหนด</p>

                        <p class="fw-bold mb-2 small" style="color: #666;">ประกอบด้วย :</p>
                        <ul class="detail-list">
                            <li>1. DT Detector</li>
                            <li>2. Control Panel</li>
                            <li>3. LCD Keyboard on/off</li>
                            <li>4. Bill Box with Siren</li>
                            <li>5. Door Contact-Flush/Heavy</li>
                            <li>6. Hold Up Switch</li>
                            <li>7. PSTN to 4G Converter</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Centered Action Buttons Row -->
            <div class="row">
                <div class="col-12">
                    <div class="btn-container-centered">
                        <a href="burglary_alarm" class="btn-gradient">ย้อนกลับ</a>
                        <a href="#" class="btn-gradient">ติดต่อฝ่ายขาย</a>
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
                    <h5 class="fw-bold mb-3" style="font-size: 1rem;">ดาวน์โหลดแอปพลิเคชัน</h5>
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
                        <div class="col-6 footer-column">
                            <h5 class="fw-bold mb-3 text-center" style="font-size: 0.9rem;">แพ็กเกจที่ใช้งาน</h5>
                            <a href="#" class="text-center footer-link">ข้อกำหนดและเงื่อนไข</a>
                        </div>
                        <div class="col-6 footer-column">
                            <h5 class="fw-bold mb-3 text-center" style="font-size: 0.9rem;">คำถามที่พบบ่อย</h5>
                            <a href="#" class="text-center footer-link">นโยบายความเป็นส่วนตัว</a>
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