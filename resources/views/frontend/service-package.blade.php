<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แพ็กเกจดูแลอุปกรณ์ - AEG EASE CLUB</title>
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
            --btn-gradient: linear-gradient(90deg, #4a1c40 0%, #c41e3a 100%);
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #ffffff;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* --- Header Styles (Match index) --- */
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

        /* --- Main Content Section --- */
        .service-main {
            padding: 60px 0 100px;
            min-height: 600px;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 1.6rem;
            text-align: center;
            margin-bottom: 40px;
        }

        /* ปรับความกว้างให้เท่ากับส่วนรายละเอียด (900px) */
        .selection-group {
            max-width: 900px;
            margin: 0 auto 50px;
            padding: 0 15px;
        }

        .selection-label {
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: block;
        }

        .custom-select {
            width: 100%;
            padding: 12px 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 0.95rem;
            color: #888;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'%3E%3Cpath fill='%23666' d='M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 15px;
            cursor: pointer;
        }

        /* --- Dynamic Details Section --- */
        #serviceDetails {
            display: none;
            /* Hidden until selected */
            max-width: 900px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .detail-image-box {
            background: #fff;
            border: 1px solid #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
            width: 100%;
            aspect-ratio: 1.5/1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .detail-image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-info-text {
            padding-left: 30px;
        }

        .info-header {
            font-weight: 700;
            color: var(--primary-red);
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        .info-list {
            list-style: none;
            padding-left: 0;
            font-size: 0.9rem;
            line-height: 1.6;
            color: #333;
            margin-bottom: 25px;
        }

        .price-section {
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .price-info {
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 5px;
        }

        .price-warning {
            font-size: 0.8rem;
            color: var(--primary-red);
            line-height: 1.4;
            margin-bottom: 5px;
        }

        /* --- Action Buttons --- */
        .btn-action-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 60px;
        }

        .btn-custom {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 10px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            min-width: 220px;
            text-decoration: none;
            text-align: center;
            box-shadow: 0 4px 15px rgba(196, 30, 58, 0.2);
            transition: 0.3s;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(196, 30, 58, 0.3);
            opacity: 0.95;
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

            .detail-info-text {
                padding-left: 0;
                margin-top: 30px;
            }

            .btn-action-group {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }

            .btn-custom {
                width: 100%;
                max-width: 300px;
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

    <!-- Main Content Section -->
    <main class="service-main">
        <div class="container">
            <h1 class="page-title">แพ็กเกจดูแลอุปกรณ์</h1>

            <!-- Selection Dropdown (Width matched to 900px) -->
            <div class="selection-group">
                <label class="selection-label">ประเภทอุปกรณ์</label>
                <select class="custom-select" id="serviceSelector" onchange="updateServiceInfo()">
                    <option value="" selected>- เลือกบริการ -</option>
                    <option value="burglary">ระบบสัญญาณกันขโมย</option>
                    <option value="fire">ระบบสัญญาณเตือนอัคคีภัย</option>
                    <option value="cctv">ระบบกล้องวงจรปิด</option>
                </select>
            </div>

            <!-- Detailed Content (Dynamic - 900px) -->
            <div id="serviceDetails">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="detail-image-box">
                            <img src="https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=800&q=80"
                                id="serviceImage" alt="Service Image">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="detail-info-text">
                            <h2 class="info-header">รายละเอียด</h2>
                            <ul class="info-list" id="servicePoints">
                                <!-- Points will be inserted here -->
                            </ul>
                            <div class="price-section">
                                <p class="price-info">ดูแลรักษาเบื้องต้น 1 จุด</p>
                                <p class="price-info">ราคาเริ่มต้นรวมจุดละ <span class="fw-bold">500 บาท</span></p>
                                <p class="price-warning">หมายเหตุ : ราคาอุปกรณ์จัดส่งฟรี ยกเว้นในกรณีที่มีการเปลี่ยน
                                    เช่น เซนเซอร์, แบตเตอรี่, กล้องวงจรปิด ฯลฯ</p>
                                <p class="price-warning">*
                                    บริการนี้ไม่ครอบคลุมรายการอะไหล่อุปกรณ์และวัสดุสิ้นเปลืองที่เป็นตัวเติม</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="btn-action-group">
                <a href="index" class="btn-custom">ย้อนกลับ</a>
                <a href="#" class="btn-custom">เพิ่มลงตะกร้า</a>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
    <footer>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-4 mb-4 mb-md-0 text-center text-md-start">
                    <h5 class="fw-bold mb-3" style="font-size: 1rem;">ดาวน์โหลดแอปพลิเคชัน</h5>
                    <div class="d-flex gap-2 justify-content-center justify-content-md-start">
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

    <!-- Script for Dynamic Selection Logic -->
    <script>
        const serviceData = {
            burglary: {
                img: "https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=800&q=80",
                points: [
                    "1. ตรวจสอบ Motion Sensor, Door Contact, Panic Switch",
                    "2. ทดสอบการทำงานของสัญญาณเตือน",
                    "3. เช็ก Battery Backup / Power Supply",
                    "4. ตรวจสอบแผงควบคุม และการเชื่อมต่อ",
                    "5. แก้ไขระบบที่ไม่ทำงาน / แจ้งเตือนผิดพลาด",
                    "6. ตรวจสอบเซ็นเซอร์เสียงและกล่องควบคุม"
                ]
            },
            fire: {
                img: "https://images.unsplash.com/photo-1582139329536-e7284fece509?auto=format&fit=crop&w=800&q=80",
                points: [
                    "1. ตรวจสอบอุปกรณ์ตรวจจับควันและรังสี",
                    "2. ทดสอบระบบกระดิ่งเตือนภัย",
                    "3. เช็กสถานะสวิตช์แจ้งเหตุแบบมือกด",
                    "4. ตรวจสอบแรงดันไฟระบบสำรอง",
                    "5. ทำความสะอาดสิ่งสกปรกบนหัวเซ็นเซอร์"
                ]
            },
            cctv: {
                img: "https://images.unsplash.com/photo-1557597774-9d273605dfa9?auto=format&fit=crop&w=800&q=80",
                points: [
                    "1. ตรวจสอบความคมชัดของภาพกล้องทุกจุด",
                    "2. เช็กความจุและการบันทึกของ Hard Drive",
                    "3. ทำความสะอาดเลนส์และกระจกหน้ากล้อง",
                    "4. ตรวจสอบระบบจ่ายไฟ POE/Adapter",
                    "5. อัปเดตซอฟต์แวร์เครื่องบันทึก (ถ้ามี)"
                ]
            }
        };

        function updateServiceInfo() {
            const selector = document.getElementById('serviceSelector');
            const detailsDiv = document.getElementById('serviceDetails');
            const imgElement = document.getElementById('serviceImage');
            const pointsList = document.getElementById('servicePoints');

            const selectedValue = selector.value;

            if (selectedValue && serviceData[selectedValue]) {
                const data = serviceData[selectedValue];

                // Update Image
                imgElement.src = data.img;

                // Update Points
                pointsList.innerHTML = '';
                data.points.forEach(point => {
                    const li = document.createElement('li');
                    li.textContent = point;
                    pointsList.appendChild(li);
                });

                // Show Details
                detailsDiv.style.display = 'block';
            } else {
                detailsDiv.style.display = 'none';
            }
        }
    </script>

    <!-- Bootstrap 5.3.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>