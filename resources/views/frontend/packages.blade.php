<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แพ็กเกจของฉัน - AEG EASE CLUB</title>
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
            --tab-inactive: #a6abbd;
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

        /* --- Header Styles (Standard AEG) --- */
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

        /* --- Main Content Section --- */
        .package-main {
            padding: 40px 0 100px;
        }

        /* Tabs Design (Match Package.jpg) */
        .tab-nav-container {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
            gap: 15px;
        }

        .tab-btn {
            border: none;
            padding: 12px 0;
            width: 250px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            transition: 0.3s;
            text-align: center;
            text-decoration: none;
        }

        .tab-btn.active {
            background-color: var(--primary-navy);
        }

        .tab-btn.inactive {
            background-color: var(--tab-inactive);
        }

        /* Package Card Styling */
        .package-card {
            background: white;
            border-radius: 35px;
            border: 1px solid #f0f0f0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            padding: 30px;
            margin-bottom: 25px;
            max-width: 850px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            align-items: flex-start;
            gap: 30px;
            position: relative;
        }

        .package-img-box {
            width: 320px;
            height: 200px;
            border-radius: 15px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .package-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .package-info {
            flex-grow: 1;
        }

        .package-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .package-title {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-navy);
            margin: 0;
        }

        .status-badge {
            font-weight: 700;
            font-size: 0.85rem;
        }

        .status-active {
            color: #28a745;
        }

        .status-expired {
            color: #999;
        }

        .date-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-item label {
            display: block;
            font-size: 0.75rem;
            color: #888;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .info-item span {
            display: block;
            font-weight: 700;
            font-size: 0.9rem;
            color: #c41e3a;
        }

        .info-item .val-dark {
            color: #333;
        }

        .pts-line {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.8rem;
            color: #555;
            margin-bottom: 25px;
        }

        .points-pill {
            background: #fff5f6;
            color: var(--primary-red);
            border: 1px solid #ffdadd;
            padding: 2px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        /* Buttons Group */
        .card-btn-group {
            display: flex;
            gap: 10px;
        }

        .btn-navy-small {
            background-color: var(--primary-navy);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 30px;
            font-weight: 600;
            font-size: 0.9rem;
            flex: 1;
            text-align: center;
            text-decoration: none;
        }

        .btn-red-full {
            background-color: var(--primary-red);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 0;
            width: 100%;
            font-weight: 700;
            font-size: 1rem;
            margin-top: 10px;
            text-align: center;
            text-decoration: none;
            display: block;
        }

        .btn-navy-small:hover,
        .btn-red-full:hover {
            opacity: 0.9;
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

        /* --- Tab Toggle Logic --- */
        .tab-pane {
            display: none;
        }

        .tab-pane.show {
            display: block;
        }

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .tab-btn {
                width: 100%;
            }

            .package-card {
                flex-direction: column;
                padding: 20px;
                border-radius: 25px;
            }

            .package-img-box {
                width: 100%;
                height: 200px;
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
                    <a href="index" class="nav-icon-item"><i class="fas fa-user"></i><span>ข้อมูลของฉัน</span></a>
                    <span style="color: rgba(255,255,255,0.5);">|</span>
                    <div class="lang-selector"
                        style="color: white; display: flex; align-items: center; gap: 5px; cursor: pointer; font-size: 0.85rem;">
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

                <div class="cart-section" style="display: flex; align-items: center; gap: 15px;">
                    <a href="cart" style="color: white; font-size: 1.5rem;"><i
                            class="fas fa-shopping-cart"></i></a>
                    <div class="points-badge">
                        <i class="fas fa-coins" style="color: #f1c40f;"></i> 200
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="package-main">
        <div class="container">

            <!-- Tabs Navigation -->
            <div class="tab-nav-container">
                <button class="tab-btn active" id="activeTabBtn" onclick="switchTab('active')">แพ็กเกจที่ใช้งาน</button>
                <button class="tab-btn inactive" id="historyTabBtn"
                    onclick="switchTab('history')">ประวัติการใช้งาน</button>
            </div>

            <!-- Tab Content: Active Packages -->
            <div id="activeContent" class="tab-pane show">
                <!-- Card 1: Burglary -->
                <div class="package-card">
                    <div class="package-img-box">
                        <img src="https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=600&q=80"
                            alt="Burglary Alarm">
                    </div>
                    <div class="package-info">
                        <div class="package-header-flex">
                            <h2 class="package-title">Burglary Alarm (ระบบสัญญาณกันขโมย)</h2>
                            <span class="status-badge status-active">ใช้งาน</span>
                        </div>
                        <div class="date-info-grid">
                            <div class="info-item">
                                <label>วันที่เริ่มใช้บริการ</label>
                                <span>01 ม.ค. 2025</span>
                            </div>
                            <div class="info-item">
                                <label>ระยะเวลาคุ้มครอง :</label>
                                <span class="val-dark">3 เดือน</span>
                            </div>
                            <div class="info-item">
                                <label>วันที่สิ้นสุดบริการ</label>
                                <span>31 มี.ค. 2025</span>
                            </div>
                            <div class="info-item">
                                <label>ได้รับพอยท์</label>
                                <div class="points-pill">ให้คะแนนพอยท์รับ 1 EASE Coins</div>
                            </div>
                        </div>
                        <div class="card-btn-group">
                            <a href="repair-request" class="btn-navy-small">แจ้งซ่อม</a>
                            <a href="#" class="btn-navy-small">รายละเอียด</a>
                        </div>
                        <a href="#" class="btn-red-full">สถานะแจ้งซ่อม</a>
                    </div>
                </div>

                <!-- Card 2: CCTV -->
                <div class="package-card">
                    <div class="package-img-box">
                        <img src="https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=600&q=80"
                            alt="CCTV Networks">
                    </div>
                    <div class="package-info">
                        <div class="package-header-flex">
                            <h2 class="package-title">CCTV Networks (ระบบกล้องวงจรปิด)</h2>
                            <span class="status-badge status-active">ใช้งาน</span>
                        </div>
                        <div class="date-info-grid">
                            <div class="info-item">
                                <label>วันที่เริ่มใช้บริการ</label>
                                <span>01 ม.ค. 2025</span>
                            </div>
                            <div class="info-item">
                                <label>ระยะเวลาคุ้มครอง :</label>
                                <span class="val-dark">3 เดือน</span>
                            </div>
                            <div class="info-item">
                                <label>วันที่สิ้นสุดบริการ</label>
                                <span>31 มี.ค. 2025</span>
                            </div>
                            <div class="info-item">
                                <label>ได้รับพอยท์</label>
                                <div class="points-pill">ให้คะแนนพอยท์รับ 1 EASE Coins</div>
                            </div>
                        </div>
                        <div class="card-btn-group">
                            <a href="#" class="btn-navy-small">แจ้งซ่อม</a>
                            <a href="#" class="btn-navy-small">รายละเอียด</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: History -->
            <div id="historyContent" class="tab-pane">
                <!-- Expired Card 1 -->
                <div class="package-card">
                    <div class="package-img-box">
                        <img src="https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=600&q=80"
                            alt="Burglary Alarm">
                    </div>
                    <div class="package-info">
                        <div class="package-header-flex">
                            <h2 class="package-title">Burglary Alarm (ระบบสัญญาณกันขโมย)</h2>
                            <span class="status-badge status-expired">หมดอายุ</span>
                        </div>
                        <div class="date-info-grid">
                            <div class="info-item">
                                <label>วันที่เริ่มใช้บริการ</label>
                                <span>01 ม.ค. 2024</span>
                            </div>
                            <div class="info-item">
                                <label>ระยะเวลาคุ้มครอง :</label>
                                <span class="val-dark">3 เดือน</span>
                            </div>
                            <div class="info-item">
                                <label>วันที่สิ้นสุดบริการ</label>
                                <span>31 มี.ค. 2024</span>
                            </div>
                        </div>
                        <div class="card-btn-group">
                            <a href="#" class="btn-navy-small" style="width: 100%;">รายละเอียด</a>
                        </div>
                    </div>
                </div>

                <!-- Expired Card 2 -->
                <div class="package-card">
                    <div class="package-img-box">
                        <img src="https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=600&q=80"
                            alt="CCTV Networks">
                    </div>
                    <div class="package-info">
                        <div class="package-header-flex">
                            <h2 class="package-title">CCTV Networks (ระบบกล้องวงจรปิด)</h2>
                            <span class="status-badge status-expired">หมดอายุ</span>
                        </div>
                        <div class="date-info-grid">
                            <div class="info-item">
                                <label>วันที่เริ่มใช้บริการ</label>
                                <span>01 ม.ค. 2024</span>
                            </div>
                            <div class="info-item">
                                <label>ระยะเวลาคุ้มครอง :</label>
                                <span class="val-dark">3 เดือน</span>
                            </div>
                            <div class="info-item">
                                <label>วันที่สิ้นสุดบริการ</label>
                                <span>31 มี.ค. 2024</span>
                            </div>
                        </div>
                        <div class="card-btn-group">
                            <a href="#" class="btn-navy-small" style="width: 100%;">รายละเอียด</a>
                        </div>
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
                            <h5 class="fw-bold mb-3 text-center" style="font-size: 0.95rem;">แพ็กเกจที่ใช้งาน</h5>
                            <a href="package" class="text-center footer-link">ข้อกำหนดและเงื่อนไข</a>
                        </div>
                        <div class="col-6 footer-column">
                            <h5 class="fw-bold mb-3 text-center" style="font-size: 0.95rem;">คำถามที่พบบ่อย</h5>
                            <a href="faq" class="text-center footer-link">นโยบายความเป็นส่วนตัว</a>
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

    <!-- Floating Chat -->
    <div class="floating-chat" style="position: fixed; bottom: 40px; right: 40px; z-index: 1000;">
        <div class="chat-circle"
            style="width: 60px; height: 60px; background: white; border-radius: 50%; box-shadow: 0 5px 25px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; border: 1px solid #f0f0f0;">
            <i class="fas fa-comment-dots text-danger fs-3"></i>
        </div>
    </div>

    <!-- Bootstrap 5.3.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function switchTab(type) {
            const activeBtn = document.getElementById('activeTabBtn');
            const historyBtn = document.getElementById('historyTabBtn');
            const activeContent = document.getElementById('activeContent');
            const historyContent = document.getElementById('historyContent');

            if (type === 'active') {
                activeBtn.classList.remove('inactive');
                activeBtn.classList.add('active');
                historyBtn.classList.remove('active');
                historyBtn.classList.add('inactive');
                activeContent.classList.add('show');
                historyContent.classList.remove('show');
            } else {
                historyBtn.classList.remove('inactive');
                historyBtn.classList.add('active');
                activeBtn.classList.remove('active');
                activeBtn.classList.add('inactive');
                historyContent.classList.add('show');
                activeContent.classList.remove('show');
            }
        }
    </script>
</body>

</html>