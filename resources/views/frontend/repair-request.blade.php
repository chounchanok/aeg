<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งซ่อม - AEG EASE CLUB</title>
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

        /* Container strictly 950px for content */
        .container-950 {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 15px;
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
            max-width: 450px;
        }

        .search-input {
            border-radius: 25px;
            padding: 10px 50px 10px 20px;
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

        /* --- Main Content --- */
        .repair-wrapper {
            padding: 60px 0 100px;
        }

        .repair-card {
            background: white;
            border-radius: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 50px;
            border: 1px solid #eee;
        }

        /* Product Hero */
        .repair-hero {
            display: flex;
            gap: 40px;
            margin-bottom: 40px;
        }

        .repair-img-box {
            width: 300px;
            height: 200px;
            border-radius: 15px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .repair-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .repair-title-info h1 {
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--primary-navy);
            margin-bottom: 25px;
        }

        .info-label {
            display: block;
            font-size: 1.1rem;
            font-weight: 700;
            color: #000;
            margin-bottom: 10px;
        }

        .sub-label {
            display: block;
            font-size: 0.85rem;
            color: #888;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .val-text {
            font-weight: 500;
            color: #333;
            font-size: 0.95rem;
        }

        /* Form Controls */
        .form-section {
            margin-bottom: 35px;
        }

        .custom-textarea {
            background-color: #ebedf4;
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
            width: 100%;
            min-height: 120px;
            font-size: 0.9rem;
            color: #555;
            resize: none;
        }

        .custom-textarea:focus {
            outline: none;
            background-color: #e2e4ed;
        }

        /* Address Section */
        .address-box {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .address-text {
            color: #555;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .btn-outline-navy {
            border: 1.5px solid var(--primary-navy);
            color: var(--primary-navy);
            background: transparent;
            padding: 5px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: 0.3s;
        }

        .btn-outline-navy:hover {
            background-color: var(--primary-navy);
            color: white;
        }

        /* Calendar UI */
        .calendar-container {
            max-width: 350px;
            background: #fff;
            user-select: none;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-weight: 700;
            font-size: 1rem;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            text-align: center;
            gap: 5px;
        }

        .cal-day-label {
            font-size: 0.7rem;
            font-weight: 700;
            color: #000;
            padding: 10px 0;
        }

        .cal-date {
            font-size: 0.85rem;
            padding: 10px 0;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            border: 1px solid transparent;
            transition: 0.2s;
        }

        .cal-date.active {
            border: 2px solid var(--primary-navy);
            background-color: #f8f9fa;
            font-weight: 700;
        }

        .cal-date.fade {
            color: #ccc;
            cursor: default;
        }

        .cal-date:hover:not(.fade) {
            background-color: #f0f0f0;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        /* Time Selection */
        .time-select-container {
            flex-grow: 1;
        }

        .custom-dropdown {
            background-color: #ebedf4;
            border: none;
            border-radius: 10px;
            padding: 12px 15px;
            width: 100%;
            font-size: 0.9rem;
            color: #888;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'%3E%3Cpath fill='%23888' d='M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 14px;
        }

        /* Action Buttons */
        .repair-footer-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 50px;
        }

        .btn-navy-action {
            background-color: var(--primary-navy);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 40px;
            font-weight: 600;
            font-size: 1rem;
            min-width: 150px;
            text-decoration: none;
            text-align: center;
        }

        .btn-navy-action:hover {
            opacity: 0.95;
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

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .repair-hero {
                flex-direction: column;
                gap: 25px;
            }

            .repair-img-box {
                width: 100%;
                height: 200px;
            }

            .repair-card {
                padding: 30px 20px;
            }

            .cal-time-flex {
                flex-direction: column;
                gap: 30px;
            }

            .calendar-container {
                max-width: 100%;
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

    <!-- Repair Form Main Content -->
    <main class="repair-wrapper">
        <div class="container-950">
            <div class="repair-card">

                <!-- Package Summary Section -->
                <div class="repair-hero">
                    <div class="repair-img-box">
                        <img src="https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=600&q=80"
                            alt="Burglary Alarm">
                    </div>
                    <div class="repair-title-info">
                        <h1>Burglary Alarm<br>(ระบบสัญญาณกันขโมย)</h1>
                        <div class="package-info-box">
                            <span class="info-label"
                                style="font-size: 1.2rem; margin-bottom: 15px;">ข้อมูลแพ็กเกจ</span>
                            <div class="mb-2">
                                <span class="sub-label">แพ็กเกจดูแลรายเดือน :</span>
                                <span class="val-text">Burglary Alarm (ระบบสัญญาณกันขโมย)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Problem Details -->
                <div class="form-section">
                    <span class="info-label">รายละเอียดปัญหา</span>
                    <textarea class="custom-textarea"
                        placeholder="• สอบถามรายละเอียดเพิ่มเติม / Request More Information"></textarea>
                </div>

                <!-- Service Address -->
                <div class="form-section">
                    <span class="info-label">ที่อยู่รับบริการ :</span>
                    <div class="address-box">
                        <div class="address-text">
                            <strong class="val-text">AEG CNX Branch</strong><br>
                            เลขที่ 135 ถ.มหิดล ต.หายยา อ.เมืองเชียงใหม่ จ.เชียงใหม่ 50100
                        </div>
                        <button class="btn-outline-navy">แก้ไขที่อยู่</button>
                    </div>
                </div>

                <!-- Date & Time Selection -->
                <div class="row cal-time-flex">
                    <div class="col-lg-6">
                        <div class="form-section mb-lg-0">
                            <span class="info-label">เลือกวันที่ใช้บริการ :</span>
                            <div class="calendar-container">
                                <div class="calendar-header">
                                    <i class="fas fa-chevron-left cursor-pointer" id="prevMonth"></i>
                                    <span id="monthDisplay">January 2026</span>
                                    <i class="fas fa-chevron-right cursor-pointer" id="nextMonth"></i>
                                </div>
                                <div class="calendar-grid" id="calendarGrid">
                                    <!-- Header Row (Days) -->
                                    <div class="cal-day-label">Mon</div>
                                    <div class="cal-day-label">Tue</div>
                                    <div class="cal-day-label">Wed</div>
                                    <div class="cal-day-label">Thu</div>
                                    <div class="cal-day-label">Fri</div>
                                    <div class="cal-day-label">Sat</div>
                                    <div class="cal-day-label">Sun</div>
                                    <!-- Dates will be injected here by JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-section mb-0">
                            <span class="info-label">เวลาที่ใช้บริการ :</span>
                            <div class="time-select-container">
                                <select class="custom-dropdown">
                                    <option value="" selected disabled>• ช่วงเวลาที่สะดวกให้ติดต่อกลับ</option>
                                    <option value="9-12">ช่วงเช้า (09:00 - 12:00 น.)</option>
                                    <option value="13-17">ช่วงบ่าย (13:00 - 17:00 น.)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="repair-footer-actions">
                    <a href="package" class="btn-navy-action">ย้อนกลับ</a>
                    <button class="btn-navy-action">บันทึก</button>
                </div>

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
                            <h5 class="fw-bold mb-3" style="font-size: 0.95rem;">แพ็กเกจที่ใช้งาน</h5>
                            <a href="package" class="footer-link">ข้อกำหนดและเงื่อนไข</a>
                        </div>
                        <div class="col-6 footer-column text-center">
                            <h5 class="fw-bold mb-3" style="font-size: 0.95rem;">คำถามที่พบบ่อย</h5>
                            <a href="faq" class="footer-link">นโยบายความเป็นส่วนตัว</a>
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
        // Calendar Logic
        const monthNames = ["January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ];

        let currentYear = 2026;
        let currentMonth = 0; // January
        let selectedDate = 10; // Default active date from image

        const monthDisplay = document.getElementById('monthDisplay');
        const calendarGrid = document.getElementById('calendarGrid');
        const prevBtn = document.getElementById('prevMonth');
        const nextBtn = document.getElementById('nextMonth');

        function renderCalendar(month, year) {
            monthDisplay.textContent = `${monthNames[month]} ${year}`;

            // Clear existing dates (keep labels)
            const labels = Array.from(calendarGrid.querySelectorAll('.cal-day-label'));
            calendarGrid.innerHTML = '';
            labels.forEach(label => calendarGrid.appendChild(label));

            // Logic to get first day of month and total days
            const firstDay = new Date(year, month, 1).getDay();
            // Adjust for Mon-Sun grid (standard JS Day is Sun=0, Mon=1...)
            // We want Mon=0, Tue=1, ... Sun=6
            let startOffset = firstDay === 0 ? 6 : firstDay - 1;

            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();

            // 1. Prev Month Faded Dates
            for (let i = startOffset; i > 0; i--) {
                const dateDiv = document.createElement('div');
                dateDiv.className = 'cal-date fade';
                dateDiv.textContent = daysInPrevMonth - i + 1;
                calendarGrid.appendChild(dateDiv);
            }

            // 2. Current Month Dates
            for (let i = 1; i <= daysInMonth; i++) {
                const dateDiv = document.createElement('div');
                dateDiv.className = 'cal-date';
                if (i === selectedDate && currentMonth === month && currentYear === year) {
                    dateDiv.classList.add('active');
                }
                dateDiv.textContent = i;

                dateDiv.onclick = function () {
                    // Remove active from all
                    document.querySelectorAll('.cal-date').forEach(d => d.classList.remove('active'));
                    this.classList.add('active');
                    selectedDate = i;
                };

                calendarGrid.appendChild(dateDiv);
            }

            // 3. Next Month Faded Dates (fill to 42 cells for 6 rows)
            const remaining = 42 - (startOffset + daysInMonth);
            for (let i = 1; i <= remaining; i++) {
                const dateDiv = document.createElement('div');
                dateDiv.className = 'cal-date fade';
                dateDiv.textContent = i;
                calendarGrid.appendChild(dateDiv);
            }
        }

        prevBtn.onclick = () => {
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCalendar(currentMonth, currentYear);
        };

        nextBtn.onclick = () => {
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCalendar(currentMonth, currentYear);
        };

        // Initial render
        renderCalendar(currentMonth, currentYear);
    </script>
</body>

</html>