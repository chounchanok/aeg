@extends('frontend.layouts.main')

@section('title', 'แจ้งซ่อม - AEG EASE CLUB')

@push('styles')
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
@endpush

@section('content')

    <main class="repair-wrapper">
        <div class="container-950">
            <form action="{{ route('repair-request.submit', $item->id) }}" method="POST" class="repair-card">
                @csrf

                @if(session('error'))
                    <div class="alert alert-danger mb-4">{{ session('error') }}</div>
                @endif

                <div class="repair-hero">
                    <div class="repair-img-box">
                        <img src="{{ $item->product ? $item->product->image_url : 'https://via.placeholder.com/600' }}" alt="Product Image">
                    </div>
                    <div class="repair-title-info">
                        <h1>{{ $item->product_name ?? 'อุปกรณ์ของคุณ' }}</h1>
                        <div class="package-info-box">
                            <span class="info-label" style="font-size: 1.2rem; margin-bottom: 15px;">ข้อมูลแพ็กเกจ</span>
                            <div class="mb-2">
                                <span class="sub-label">แพ็กเกจที่ต้องการแจ้งซ่อม :</span>
                                <span class="val-text">{{ $item->product_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <span class="info-label">รายละเอียดปัญหา <span class="text-danger">*</span></span>
                    <textarea name="problem_description" class="custom-textarea" required
                        placeholder="• ระบุอาการเสีย หรือข้อมูลเพิ่มเติมที่ต้องการให้ช่างทราบ"></textarea>
                </div>

                <div class="form-section">
                    <span class="info-label">ที่อยู่รับบริการ :</span>
                    <div class="address-box">
                        <div class="address-text">
                            <strong class="val-text">{{ $profile->first_name ?? $user->username }}</strong><br>
                            เบอร์โทรติดต่อ: {{ $user->phone }}
                            </div>
                    </div>
                </div>

                <div class="row cal-time-flex">
                    <div class="col-lg-6">
                        <div class="form-section mb-lg-0">
                            <span class="info-label">เลือกวันที่ใช้บริการ <span class="text-danger">*</span></span>
                            <input type="hidden" name="preferred_date" id="hidden_preferred_date" required>

                            <div class="calendar-container">
                                <div class="calendar-header">
                                    <i class="fas fa-chevron-left cursor-pointer" id="prevMonth"></i>
                                    <span id="monthDisplay"></span>
                                    <i class="fas fa-chevron-right cursor-pointer" id="nextMonth"></i>
                                </div>
                                <div class="calendar-grid" id="calendarGrid">
                                    <div class="cal-day-label">Mon</div>
                                    <div class="cal-day-label">Tue</div>
                                    <div class="cal-day-label">Wed</div>
                                    <div class="cal-day-label">Thu</div>
                                    <div class="cal-day-label">Fri</div>
                                    <div class="cal-day-label">Sat</div>
                                    <div class="cal-day-label">Sun</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-section mb-0">
                            <span class="info-label">เวลาที่ใช้บริการ <span class="text-danger">*</span></span>
                            <div class="time-select-container">
                                <select name="preferred_time" class="custom-dropdown" required>
                                    <option value="" selected disabled>• เลือกช่วงเวลา</option>
                                    <option value="ช่วงเช้า (09:00 - 12:00 น.)">ช่วงเช้า (09:00 - 12:00 น.)</option>
                                    <option value="ช่วงบ่าย (13:00 - 17:00 น.)">ช่วงบ่าย (13:00 - 17:00 น.)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="repair-footer-actions">
                    <a href="{{ route('packages.mine') }}" class="btn-navy-action" style="background: transparent; color: var(--primary-navy); border: 2px solid var(--primary-navy);">ย้อนกลับ</a>
                    <button type="submit" class="btn-navy-action">บันทึกและส่งคำขอ</button>
                </div>
            </form>
        </div>
    </main>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Calendar Logic ที่ปรับปรุงให้ส่งค่าเข้า Form
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

        let currentDate = new Date();
        let currentYear = currentDate.getFullYear();
        let currentMonth = currentDate.getMonth();
        let selectedDate = currentDate.getDate(); // ให้ Default เป็นวันนี้

        const monthDisplay = document.getElementById('monthDisplay');
        const calendarGrid = document.getElementById('calendarGrid');
        const prevBtn = document.getElementById('prevMonth');
        const nextBtn = document.getElementById('nextMonth');
        const hiddenDateInput = document.getElementById('hidden_preferred_date');

        function updateHiddenDate(date, month, year) {
            // สร้าง Format: YYYY-MM-DD
            const formattedMonth = String(month + 1).padStart(2, '0');
            const formattedDate = String(date).padStart(2, '0');
            hiddenDateInput.value = `${year}-${formattedMonth}-${formattedDate}`;
        }

        function renderCalendar(month, year) {
            monthDisplay.textContent = `${monthNames[month]} ${year}`;

            // เคลียร์วันที่เก่าทิ้ง แต่เก็บ Label (Mon-Sun) ไว้
            const labels = Array.from(calendarGrid.querySelectorAll('.cal-day-label'));
            calendarGrid.innerHTML = '';
            labels.forEach(label => calendarGrid.appendChild(label));

            const firstDay = new Date(year, month, 1).getDay();
            let startOffset = firstDay === 0 ? 6 : firstDay - 1;

            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const daysInPrevMonth = new Date(year, month, 0).getDate();

            // 1. วันของเดือนก่อนหน้า (จางๆ)
            for (let i = startOffset; i > 0; i--) {
                const dateDiv = document.createElement('div');
                dateDiv.className = 'cal-date fade';
                dateDiv.textContent = daysInPrevMonth - i + 1;
                calendarGrid.appendChild(dateDiv);
            }

            // 2. วันในเดือนนี้
            for (let i = 1; i <= daysInMonth; i++) {
                const dateDiv = document.createElement('div');
                dateDiv.className = 'cal-date';

                // เช็คไฮไลท์
                if (i === selectedDate && currentMonth === month && currentYear === year) {
                    dateDiv.classList.add('active');
                    updateHiddenDate(i, month, year); // ตั้งค่าเริ่มต้นให้ Input
                }

                dateDiv.textContent = i;

                // กดเลือกวัน
                dateDiv.onclick = function () {
                    document.querySelectorAll('.cal-date').forEach(d => d.classList.remove('active'));
                    this.classList.add('active');
                    selectedDate = i;
                    currentMonth = month;
                    currentYear = year;
                    updateHiddenDate(i, month, year); // อัปเดตค่าให้ Form
                };

                calendarGrid.appendChild(dateDiv);
            }

            // 3. วันของเดือนถัดไป (จางๆ)
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
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            renderCalendar(currentMonth, currentYear);
        };

        nextBtn.onclick = () => {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            renderCalendar(currentMonth, currentYear);
        };

        // Render ครั้งแรก
        renderCalendar(currentMonth, currentYear);
    </script>
@endpush
