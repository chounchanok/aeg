<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บัญชีของฉัน - AEG EASE CLUB</title>
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
            --card-bg: #EBEDF4;
            --privilege-bg: #9899A2;
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
            --navy-dark: #0e1b3d;
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #f4f5f7;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* --- New Header Styles from index --- */
        .navbar {
            background-image: url('assets/image/header-bk.webp');
            background-size: cover;
            background-position: center;
            background-color: var(--primary-dark);
            /* Fallback */
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

        .nav-icon-item:hover {
            opacity: 0.8;
            color: white;
        }

        .lang-selector {
            color: white;
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
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
            background-color: white;
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

        /* Breadcrumb Bar */
        .breadcrumb-bar {
            background-color: #fcfcfc;
            border-bottom: 1px solid #e0e0e0;
            padding: 10px 0;
            font-size: 0.85rem;
            color: #999;
        }

        /* Main Content Layout */
        .main-content {
            padding: 3rem 0;
        }

        .card-custom {
            background: white;
            border-radius: 30px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            padding: 40px;
            margin-bottom: 30px;
        }

        /* Membership Card Visualization */
        .member-card-visual {
            width: 100%;
            aspect-ratio: 1.6 / 1;
            background: linear-gradient(135deg, #1c4d8c 0%, #0a1931 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(10, 25, 49, 0.3);
        }

        .card-label-top {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            text-align: right;
        }

        .card-brand-main {
            margin-top: 15px;
        }

        .card-brand-main h2 {
            font-style: italic;
            font-weight: 900;
            margin: 0;
            font-size: 2.2rem;
            line-height: 1;
        }

        .card-brand-main span {
            color: #4dc3ff;
            font-size: 1.2rem;
            letter-spacing: 4px;
        }

        .card-footer-label {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 0.6rem;
            letter-spacing: 1px;
            opacity: 0.6;
        }

        /* Info Styles */
        .section-title {
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 25px;
            color: var(--primary-dark);
        }

        .info-label {
            font-size: 0.8rem;
            color: #888;
            margin-bottom: 2px;
            font-weight: 500;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
        }

        .btn-navy {
            background-color: var(--primary-dark);
            color: white;
            border-radius: 10px;
            padding: 10px 25px;
            font-size: 0.9rem;
            font-weight: 500;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-navy:hover {
            background-color: var(--primary-red);
            transform: translateY(-2px);
            color: white;
        }

        /* Address Section */
        .address-item {
            border-top: 1px solid #f0f0f0;
            padding: 25px 0;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .address-name {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .address-details {
            font-size: 0.9rem;
            color: #666;
            max-width: 80%;
        }

        .edit-link {
            color: #888;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
        }

        .edit-link:hover {
            color: var(--primary-red);
        }

        .btn-add-address {
            background-color: var(--primary-dark);
            color: white;
            padding: 6px 16px;
            border-radius: 6px;
            font-size: 0.8rem;
            text-decoration: none;
            border: none;
        }

        .btn-add-address:hover {
            background-color: var(--primary-red);
            color: white;
        }

        /* --- New Footer Styles from index --- */
        footer {
            background: var(--ease-gradient);
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .footer-link:hover {
            color: white;
            text-decoration: underline;
        }

        .footer-column {
            border-right: 1px solid rgba(255, 255, 255, 0.3);
            padding-right: 20px;
            padding-left: 20px;
        }

        .footer-column:first-child {
            padding-left: 0;
        }

        .footer-column:last-child {
            border-right: none;
        }

        .social-icons-footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }

        .social-icons-footer a {
            color: white;
            margin: 0 15px;
            font-size: 1.2rem;
            text-decoration: none;
        }

        /* Custom Modal Styles (From account address image) */
        .modal-content {
            border-radius: 25px;
            border: none;
            padding: 20px;
        }

        .modal-header {
            border-bottom: none;
            padding-bottom: 0;
        }

        .modal-title {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 1.5rem;
        }

        .modal-body label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .modal-body .form-control,
        .modal-body .form-select {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            font-size: 0.9rem;
            background-color: #fff;
        }

        .modal-body .form-control:focus,
        .modal-body .form-select:focus {
            box-shadow: none;
            border-color: var(--primary-dark);
        }

        .modal-footer {
            border-top: none;
            justify-content: center;
            gap: 20px;
            padding-top: 0;
            padding-bottom: 20px;
        }

        .btn-modal-confirm {
            background-color: var(--primary-dark);
            color: white;
            border-radius: 10px;
            padding: 8px 30px;
            font-weight: 600;
            border: none;
        }

        .btn-modal-cancel {
            background: none;
            border: none;
            color: #333;
            font-weight: 600;
            font-size: 0.9rem;
        }

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .search-container {
                max-width: 100%;
                margin: 15px 0;
            }

            .footer-column {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.3);
                padding-bottom: 20px;
                margin-bottom: 20px;
            }

            .card-custom {
                padding: 25px;
            }
        }
    </style>
</head>

<body>

    <!-- Updated Header from index -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container navbar-container">
            <!-- Top Row -->
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

            <!-- Bottom Row -->
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
                    <a href="#" class="cart-icon"><i class="fas fa-shopping-cart"></i></a>
                    <div class="points-badge">
                        <i class="fas fa-coins" style="color: #f1c40f;"></i> 200
                    </div>
                </div>

                <button class="navbar-toggler d-lg-none ms-3" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Sub Header Bar -->
    <div class="breadcrumb-bar">
        <div class="container">
            <span>บัญชีของฉัน</span>
        </div>
    </div>

    <!-- Main Content Body -->
    <main class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10">

                    <!-- Profile Card -->
                    <div class="card-custom">
                        <div class="row align-items-center">
                            <div class="col-lg-5 mb-4 mb-lg-0">
                                <!-- Visual Membership Card -->
                                <div class="member-card-visual">
                                    <div class="d-flex justify-content-between">
                                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 30px; height: 30px;">
                                            <span style="color:#0a1931; font-weight: 800; font-size: 8px;">AEG</span>
                                        </div>
                                        <div class="card-label-top">Advance Member</div>
                                    </div>
                                    <div class="card-brand-main">
                                        <h2>EASE</h2>
                                        <span>CLUB</span>
                                    </div>
                                    <div class="card-footer-label">MEMBERSHIP</div>
                                    <div class="position-absolute bottom-0 end-0 p-3">
                                        <svg width="40" height="40" viewBox="0 0 40 40" style="opacity: 0.3;">
                                            <rect width="40" height="40" rx="5" fill="white" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <h3 class="section-title">ข้อมูลส่วนตัว</h3>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="info-label">ชื่อ</div>
                                        <div class="info-value">คุณ noppawat</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-label">เบอร์โทรศัพท์มือถือ</div>
                                        <div class="info-value">061-519-5974</div>
                                    </div>
                                    <div class="col-12">
                                        <div class="info-label">อีเมล</div>
                                        <div class="info-value">noppawat.b@aeginc.co</div>
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button class="btn btn-navy" data-bs-toggle="modal"
                                        data-bs-target="#addressModal">แก้ไขข้อมูลส่วนตัว</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Address Card -->
                    <div class="card-custom">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="section-title mb-0">รายการที่อยู่ของฉัน</h3>
                            <button class="btn btn-add-address" data-bs-toggle="modal"
                                data-bs-target="#addressModal">เพิ่มที่อยู่ใหม่</button>
                        </div>

                        <div class="address-item">
                            <div>
                                <div class="address-name">AEG CNX Branch</div>
                                <div class="address-details">
                                    เลขที่ 135 ถ.มหิดล ต.หายยา อ.เมืองเชียงใหม่ จ.เชียงใหม่ 50100
                                </div>
                            </div>
                            <div>
                                <a class="edit-link" data-bs-toggle="modal" data-bs-target="#addressModal">แก้ไข</a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- Updated Footer from index -->
    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="fw-bold mb-3" style="font-size: 1rem;">ดาวน์โหลดแอปพลิเคชัน</h5>
                    <div class="d-flex gap-2 align-items-start">
                        <!-- Placeholder QR -->
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
                        <div class="col-6 footer-column">
                            <h5 class="fw-bold mb-3 text-center" style="font-size: 0.95rem;">แพ็กเกจที่ใช้งาน</h5>
                            <a href="#" class="text-center footer-link">ข้อกำหนดและเงื่อนไข</a>
                        </div>
                        <div class="col-6 footer-column">
                            <h5 class="fw-bold mb-3 text-center" style="font-size: 0.95rem;">คำถามที่พบบ่อย</h5>
                            <a href="#" class="text-center footer-link">นโยบายความเป็นส่วนตัว</a>
                        </div>
                    </div>
                    <div class="social-icons-footer">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-line"></i></a>
                    </div>
                </div>
                <div class="col-md-4">
                    <!-- Right column space (can be used for additional logos) -->
                </div>
            </div>
        </div>
    </footer>

    <!-- Modal: Address (As requested previously) -->
    <div class="modal fade" id="addressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">ที่อยู่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="modal-name">ชื่อ</label>
                                <input type="text" class="form-control" id="modal-name">
                            </div>
                            <div class="col-6">
                                <label for="modal-phone">เบอร์โทรศัพท์มือถือ</label>
                                <input type="text" class="form-control" id="modal-phone">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>จังหวัด, เขต/อำเภอ, แขวง/ตำบล, รหัสไปรษณีย์</label>
                            <select class="form-select">
                                <option selected disabled></option>
                                <option value="1">กรุงเทพมหานคร, เขตปทุมวัน, แขวงลุมพินี, 10330</option>
                                <option value="2">เชียงใหม่, อ.เมืองเชียงใหม่, ต.หายยา, 50100</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="modal-detail">บ้านเลขที่, ซอย, หมู่บ้าน, ถนน</label>
                            <textarea class="form-control" id="modal-detail" rows="2"
                                placeholder="กรุณากรอกรายละเอียด"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-modal-confirm">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>