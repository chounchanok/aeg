<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบรักษาความปลอดภัย - AEG EASE CLUB</title>
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

        /* --- Header Styles (from index) --- */
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
            text-decoration: none;
        }

        /* --- Category Navigation Tabs (Enlarged with Images) --- */
        .category-nav {
            padding: 50px 0;
            background: white;
            border-bottom: 1px solid #f0f0f0;
        }

        .nav-tabs-custom {
            display: flex;
            justify-content: center;
            gap: 20px;
            border: none;
            overflow-x: auto;
            flex-wrap: nowrap;
            padding-bottom: 15px;
            scrollbar-width: none;
        }

        .nav-tabs-custom::-webkit-scrollbar {
            display: none;
        }

        .nav-tabs-custom .nav-link {
            border: none !important;
            padding: 0;
            background: none !important;
        }

        .category-box {
            background: #ffffff;
            border: 1px solid #f0f0f0;
            border-radius: 20px;
            padding: 25px 15px;
            width: 220px;
            height: 190px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 0.3s ease;
        }

        .category-icon {
            width: 70px;
            height: 70px;
            margin-bottom: 15px;
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
            transition: all 0.3s ease;
        }

        .category-text-en {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 4px;
            color: #bbb;
        }

        .category-text-th {
            font-weight: 400;
            font-size: 0.85rem;
            color: #ccc;
        }

        /* Active State */
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

        /* --- Product Grid Styles --- */
        .product-section {
            padding: 60px 0 100px;
            background-color: #fff;
        }

        .product-item-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 60px;
        }

        .product-image-frame {
            background: white;
            border: 1px solid #f2f2f2;
            border-radius: 25px;
            padding: 30px;
            width: 100%;
            max-width: 380px;
            aspect-ratio: 1 / 0.8;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .product-image-frame:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.07);
            border-color: #e8e8e8;
        }

        .product-image-frame img {
            max-width: 95%;
            max-height: 95%;
            object-fit: contain;
        }

        .product-title-en {
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 4px;
            color: #1a2d5e;
            white-space: nowrap;
        }

        .product-title-th {
            font-weight: 500;
            font-size: 1rem;
            color: #1a2d5e;
            margin-bottom: 20px;
        }

        .btn-action-sales {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 10px 50px;
            font-size: 1.25rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(196, 30, 58, 0.25);
            transition: all 0.3s ease;
        }

        .btn-action-sales:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(196, 30, 58, 0.35);
            opacity: 0.95;
        }

        /* Floating Chat */
        .floating-chat {
            position: fixed;
            bottom: 40px;
            right: 40px;
            z-index: 1000;
        }

        .chat-circle {
            width: 65px;
            height: 65px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #f0f0f0;
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

        @media (max-width: 1200px) {
            .category-box {
                width: 180px;
                height: 160px;
            }

            .product-image-frame {
                max-width: 320px;
            }
        }

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .footer-column {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                margin-bottom: 25px;
                padding-bottom: 25px;
            }

            .nav-tabs-custom {
                justify-content: flex-start;
                padding-left: 15px;
            }

            .product-image-frame {
                max-width: 100%;
                padding: 20px;
            }

            .btn-action-sales {
                font-size: 1.1rem;
                padding: 8px 35px;
            }

            .category-box {
                width: 150px;
                height: 150px;
            }

            .category-icon {
                width: 60px;
                height: 60px;
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

                <button class="navbar-toggler d-lg-none ms-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Tabbed Category Navigation (Enlarged with Image Icons) -->
    <div class="category-nav">
        <div class="container">
            <div class="nav nav-tabs nav-tabs-custom" id="securityTabs" role="tablist">
                <!-- Tab 1: Burglary -->
                <button class="nav-link active" id="tab-burglary" data-bs-toggle="tab" data-bs-target="#pane-burglary"
                    type="button" role="tab">
                    <div class="category-box">
                        <div class="category-icon">
                            <img src="assets/image/cat1.webp" alt="Burglary Alarm"
                                onerror="this.src='https://via.placeholder.com/70x70?text=Burglary'">
                        </div>
                        <div class="category-text-en">Burglary Alarm</div>
                        <div class="category-text-th">ระบบสัญญาณกันขโมย</div>
                    </div>
                </button>
                <!-- Tab 2: Fire -->
                <button class="nav-link" id="tab-fire" data-bs-toggle="tab" data-bs-target="#pane-fire" type="button"
                    role="tab">
                    <div class="category-box">
                        <div class="category-icon">
                            <img src="assets/image/cat2.webp" alt="Fire Alarm"
                                onerror="this.src='https://via.placeholder.com/70x70?text=Fire'">
                        </div>
                        <div class="category-text-en">Fire Alarm</div>
                        <div class="category-text-th">ระบบสัญญาณเตือนอัคคีภัย</div>
                    </div>
                </button>
                <!-- Tab 3: Lock -->
                <button class="nav-link" id="tab-lock" data-bs-toggle="tab" data-bs-target="#pane-lock" type="button"
                    role="tab">
                    <div class="category-box">
                        <div class="category-icon">
                            <img src="assets/image/cat3.webp" alt="Electronic Lock"
                                onerror="this.src='https://via.placeholder.com/70x70?text=Lock'">
                        </div>
                        <div class="category-text-en">Electronic Lock</div>
                        <div class="category-text-th">ระบบล็อกอิเล็กทรอนิกส์</div>
                    </div>
                </button>
                <!-- Tab 4: CCTV -->
                <button class="nav-link" id="tab-cctv" data-bs-toggle="tab" data-bs-target="#pane-cctv" type="button"
                    role="tab">
                    <div class="category-box">
                        <div class="category-icon">
                            <img src="assets/image/cat4.webp" alt="CCTV"
                                onerror="this.src='https://via.placeholder.com/70x70?text=CCTV'">
                        </div>
                        <div class="category-text-en">CCTV</div>
                        <div class="category-text-th">ระบบกล้องวงจรปิด</div>
                    </div>
                </button>
                <!-- Tab 5: Gold Cap -->
                <button class="nav-link" id="tab-goldcap" data-bs-toggle="tab" data-bs-target="#pane-goldcap"
                    type="button" role="tab">
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

    <!-- Product Grid Sections -->
    <main class="product-section">
        <div class="container">
            <div class="tab-content" id="securityTabsContent">

                <!-- Pane 1: Burglary -->
                <div class="tab-pane fade show active" id="pane-burglary" role="tabpanel">
                    <div class="row g-3 g-lg-4 justify-content-center">
                        <div class="col-6 col-md-4">
                            <div class="product-item-wrapper">
                                <div class="product-image-frame">
                                    <img src="assets/image/product-1.webp" alt="AEG Surveillance">
                                </div>
                                <div class="product-title-en">AEG Surveillance</div>
                                <div class="product-title-th">ชุดระบบสัญญาณกันขโมย</div>
                                <a href="#" class="btn-action-sales">ติดต่อฝ่ายขาย</a>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="product-item-wrapper">
                                <div class="product-image-frame">
                                    <img src="assets/image/product-2.webp" alt="Motion Sensor">
                                </div>
                                <div class="product-title-en">Motion Sensor - Wireless</div>
                                <div class="product-title-th">อุปกรณ์ตรวจจับความเคลื่อนไหว</div>
                                <a href="#" class="btn-action-sales">ติดต่อฝ่ายขาย</a>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="product-item-wrapper">
                                <div class="product-image-frame">
                                    <img src="assets/image/product-3.webp" alt="Hold Up">
                                </div>
                                <div class="product-title-en">Hold Up - Switch</div>
                                <div class="product-title-th">ปุ่มกดสัญญาณฉุกเฉิน</div>
                                <a href="#" class="btn-action-sales">ติดต่อฝ่ายขาย</a>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="product-item-wrapper">
                                <div class="product-image-frame">
                                    <img src="assets/image/product-3.webp" alt="Seismic Sensor">
                                </div>
                                <div class="product-title-en">Seismic Sensor</div>
                                <div class="product-title-th">อุปกรณ์ตรวจจับแรงสั่นสะเทือน</div>
                                <a href="#" class="btn-action-sales">ติดต่อฝ่ายขาย</a>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="product-item-wrapper">
                                <div class="product-image-frame">
                                    <img src="assets/image/product-2.webp" alt="Vibration Sensor">
                                </div>
                                <div class="product-title-en">Vibration Sensor</div>
                                <div class="product-title-th">อุปกรณ์ตรวจจับแรงสั่นสะเทือนแบบหนัก</div>
                                <a href="#" class="btn-action-sales">ติดต่อฝ่ายขาย</a>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="product-item-wrapper">
                                <div class="product-image-frame">
                                    <img src="assets/image/product-1.webp" alt="Door Contact">
                                </div>
                                <div class="product-title-en">Door Contact - Flush</div>
                                <div class="product-title-th">อุปกรณ์ตรวจจับการเปิด-ปิดประตู</div>
                                <a href="#" class="btn-action-sales">ติดต่อฝ่ายขาย</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Other panes (Placeholder logic for Structure) -->
                <div class="tab-pane fade" id="pane-fire" role="tabpanel">
                    <div class="row g-3 g-lg-4 justify-content-center">
                        <div class="col-6 col-md-4">
                            <div class="product-item-wrapper">
                                <div class="product-image-frame">
                                    <img src="assets/image/product-1.webp" alt="Smoke Detector">
                                </div>
                                <div class="product-title-en">Smoke Detector</div>
                                <div class="product-title-th">อุปกรณ์ตรวจจับควันไฟ</div>
                                <a href="#" class="btn-action-sales">ติดต่อฝ่ายขาย</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane-lock" role="tabpanel">
                    <div class="row g-3 g-lg-4 justify-content-center">
                        <div class="col-6 col-md-4">
                            <div class="product-item-wrapper">
                                <div class="product-image-frame">
                                    <img src="assets/image/product-1.webp" alt="Digital Lock">
                                </div>
                                <div class="product-title-en">Digital Door Lock</div>
                                <div class="product-title-th">ระบบล็อกประตูดิจิทัล</div>
                                <a href="#" class="btn-action-sales">ติดต่อฝ่ายขาย</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane-cctv" role="tabpanel">
                    <div class="row g-3 g-lg-4 justify-content-center">
                        <div class="col-6 col-md-4">
                            <div class="product-item-wrapper">
                                <div class="product-image-frame">
                                    <img src="assets/image/product-1.webp" alt="CCTV">
                                </div>
                                <div class="product-title-en">IP Camera 4K</div>
                                <div class="product-title-th">กล้องวงจรปิดความละเอียดสูง</div>
                                <a href="#" class="btn-action-sales">ติดต่อฝ่ายขาย</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="pane-goldcap" role="tabpanel">
                    <div class="row g-3 g-lg-4 justify-content-center">
                        <div class="col-6 col-md-4">
                            <div class="product-item-wrapper">
                                <div class="product-image-frame">
                                    <img src="assets/image/product-1.webp" alt="Gold Cap">
                                </div>
                                <div class="product-title-en">AEG Gold Cap-Lock</div>
                                <div class="product-title-th">ชุดโกลด์แคป-ล็อก พรีเมียม</div>
                                <a href="#" class="btn-action-sales">ติดต่อฝ่ายขาย</a>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Floating Chat -->
    <div class="floating-chat">
        <div class="chat-circle">
            <i class="fas fa-comment-dots text-danger fs-3"></i>
        </div>
    </div>

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