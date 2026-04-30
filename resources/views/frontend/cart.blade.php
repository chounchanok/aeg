<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า - AEG EASE CLUB</title>
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
            --btn-gradient: linear-gradient(90deg, #4a1c40 0%, #c41e3a 100%);
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #f8f9fa;
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

        /* --- Custom Radio Point Design --- */
        .custom-radio {
            cursor: pointer;
            width: 18px;
            height: 18px;
            background-color: #ddd;
            border-radius: 50%;
            display: inline-block;
            position: relative;
            transition: 0.3s;
        }

        .radio-active {
            background-color: var(--primary-red) !important;
        }

        /* --- Main Cart Content --- */
        .cart-main {
            padding: 60px 0 100px;
            min-height: 70vh;
        }

        .cart-header {
            display: flex;
            align-items: center;
            gap: 15px;
            max-width: 800px;
            margin: 0 auto 30px;
            padding: 0 15px;
            cursor: pointer;
        }

        .select-all-text {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--primary-dark);
        }

        /* Cart Item Cards */
        .cart-item-card {
            background: white;
            border: 1px solid #eee;
            border-radius: 20px;
            max-width: 800px;
            margin: 0 auto 20px;
            padding: 25px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
            transition: 0.3s;
            cursor: pointer;
        }

        .cart-item-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06);
        }

        .item-info-group {
            display: flex;
            align-items: center;
            gap: 25px;
            flex-grow: 1;
        }

        .item-image-box {
            width: 180px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            background: #fcfcfc;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #f0f0f0;
        }

        .item-image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            text-align: left;
        }

        .item-title {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }

        .item-subtitle {
            font-size: 0.95rem;
            color: #777;
            margin-bottom: 0;
        }

        /* Action Buttons */
        .cart-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 60px;
        }

        .btn-cart {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 12px 45px;
            font-size: 1.1rem;
            font-weight: 600;
            min-width: 220px;
            text-decoration: none;
            text-align: center;
            box-shadow: 0 4px 15px rgba(196, 30, 58, 0.2);
            transition: 0.3s;
        }

        .btn-cart:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(196, 30, 58, 0.3);
            opacity: 0.95;
        }

        /* --- QR Code Modal Styles (Exact match to QR Code.png) --- */
        .modal-qr-content {
            border-radius: 20px !important;
            border: none;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
        }

        /* ส่วนหัวและส่วนแสดง QR เป็นสีน้ำเงินกรมท่าเดียวกัน */
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

        .btn-qr-close:hover {
            opacity: 1;
            transform: scale(1.1);
        }

        .qr-image-container {
            display: inline-block;
            background: white;
            padding: 12px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .qr-image-container img {
            width: 150px;
            height: 150px;
            display: block;
        }

        /* ส่วนท้ายเป็นสีขาว */
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
            letter-spacing: 0.2px;
        }

        /* Floating Icon */
        .floating-icon {
            position: fixed;
            bottom: 40px;
            right: 40px;
            z-index: 1000;
        }

        .chat-btn {
            width: 60px;
            height: 60px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.12);
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

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .item-info-group {
                gap: 15px;
            }

            .item-image-box {
                width: 120px;
                height: 90px;
            }

            .item-title {
                font-size: 1.1rem;
            }

            .cart-actions {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }

            .btn-cart {
                width: 100%;
                max-width: 320px;
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
                <a class="navbar-brand" href="index">
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

    <!-- Main Cart Content -->
    <main class="cart-main">
        <div class="container">

            <!-- Select All Header -->
            <div class="cart-header" onclick="toggleAll()">
                <span class="custom-radio radio-active" id="allSelector"></span>
                <span class="select-all-text">เลือกทั้งหมด</span>
            </div>

            <!-- Cart Item 1 -->
            <div class="cart-item-card" onclick="toggleItem(1)">
                <div class="item-info-group">
                    <div class="item-image-box">
                        <img src="assets/image/service-1.webp" alt="Burglary Package"
                            onerror="this.src='https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=300&q=80'">
                    </div>
                    <div class="item-details">
                        <h3 class="item-title">แพ็กเกจดูแลอุปกรณ์</h3>
                        <p class="item-subtitle">Burglary Alarm (ระบบสัญญาณกันขโมย)</p>
                    </div>
                </div>
                <div class="custom-radio radio-active item-radio" id="itemRadio-1"></div>
            </div>

            <!-- Cart Item 2 -->
            <div class="cart-item-card" onclick="toggleItem(2)">
                <div class="item-info-group">
                    <div class="item-image-box">
                        <img src="assets/image/service-2.webp" alt="CCTV Package"
                            onerror="this.src='https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=300&q=80'">
                    </div>
                    <div class="item-details">
                        <h3 class="item-title">แพ็กเกจดูแลอุปกรณ์</h3>
                        <p class="item-subtitle">CCTV (ระบบกล้องวงจรปิด)</p>
                    </div>
                </div>
                <div class="custom-radio radio-active item-radio" id="itemRadio-2"></div>
            </div>

            <!-- Action Buttons -->
            <div class="cart-actions">
                <a href="service_package" class="btn-cart">ย้อนกลับ</a>
                <button class="btn-cart" data-bs-toggle="modal" data-bs-target="#qrModal">ดำเนินการต่อ</button>
            </div>

        </div>
    </main>

    <!-- QR Code Modal (Match QR Code.png 100%) -->
    <div class="modal fade" id="qrModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
            <div class="modal-content modal-qr-content">
                <!-- ส่วนสีน้ำเงินกรมท่า รวมทั้งหัวข้อและ QR Code -->
                <div class="modal-qr-blue-section">
                    <h5 class="modal-qr-title">สแกน Qr Code</h5>
                    <button type="button" class="btn-qr-close" data-bs-dismiss="modal">
                        <i class="fas fa-xmark"></i>
                    </button>
                    <div class="qr-image-container">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=AEG-Checkout-Proceed"
                            alt="QR Code">
                    </div>
                </div>
                <!-- ส่วนสีขาวด้านล่าง -->
                <div class="qr-footer-info">
                    <p class="qr-instruction">สแกนคิวอาร์โค้ดเพื่อดำเนินการต่อผ่านแอปพลิเคชัน</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Chat Icon -->
    <div class="floating-icon">
        <div class="chat-btn">
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

    <script>
        function toggleItem(id) {
            const radio = document.getElementById('itemRadio-' + id);
            radio.classList.toggle('radio-active');
            updateSelectAllState();
        }

        function toggleAll() {
            const allRadio = document.getElementById('allSelector');
            const itemRadios = document.querySelectorAll('.item-radio');

            const isCurrentlyActive = allRadio.classList.contains('radio-active');

            if (isCurrentlyActive) {
                allRadio.classList.remove('radio-active');
                itemRadios.forEach(r => r.classList.remove('radio-active'));
            } else {
                allRadio.classList.add('radio-active');
                itemRadios.forEach(r => r.classList.add('radio-active'));
            }
        }

        function updateSelectAllState() {
            const allRadio = document.getElementById('allSelector');
            const itemRadios = document.querySelectorAll('.item-radio');
            const activeRadios = document.querySelectorAll('.item-radio.radio-active');

            if (activeRadios.length === itemRadios.length) {
                allRadio.classList.add('radio-active');
            } else {
                allRadio.classList.remove('radio-active');
            }
        }
    </script>
</body>

</html>