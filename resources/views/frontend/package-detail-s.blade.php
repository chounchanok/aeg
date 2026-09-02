
@extends('frontend.layouts.main')

@section('title', __('รายละเอียดแพ็กเกจ') . ' - AEG EASE CLUB')

@push('styles')
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
            --tab-inactive: #9899A2;
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

        /* --- New Header & Navigation Styles --- */
        .navbar-main-header {
            background-image: url('assets/image/header-bk.webp');
            background-size: cover;
            background-position: center;
            padding: 10px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-top-row {
            display: flex;
            justify-content: flex-end;
            padding-bottom: 0px !important;
            border-bottom: 0px solid rgba(255, 255, 255, 0.2) !important;
            margin-bottom: 0px !important;
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
        }

        .navbar-bottom-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .navbar-brand img {
            height: 50px;
        }

        .search-input {
            border-radius: 25px;
            padding: 8px 20px;
            border: none;
            width: 100%;
            max-width: 500px;
            font-size: 0.95rem;
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

        /* Hamburger Menu Icon: White SVG */
        .navbar-toggler {
            border: 1px solid rgba(255, 255, 255, 0.7) !important;
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255, 255, 255, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E") !important;
        }

        /* --- Existing Content Layout --- */
        .content-container-950 {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .detail-main-wrapper {
            padding: 60px 0 100px;
        }

        .detail-card {
            background: white;
            border-radius: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 40px 50px;
            border: 1px solid #eee;
        }

        .detail-top-hero {
            display: flex;
            gap: 40px;
            margin-bottom: 35px;
            padding-bottom: 30px;
            border-bottom: 1px solid #f2f2f2;
        }

        .detail-img-box {
            width: 320px;
            height: 220px;
            border-radius: 18px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .detail-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-info-side {
            flex-grow: 1;
        }

        .detail-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .detail-main-title {
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--primary-navy);
            margin: 0;
            line-height: 1.3;
        }

        .detail-status-tag {
            font-weight: 700;
            font-size: 0.85rem;
            color: #28a745;
        }

        .info-label-small {
            display: block;
            font-size: 0.8rem;
            color: #888;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .info-value-navy {
            font-weight: 700;
            color: var(--primary-navy);
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .date-grid-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .val-red-date {
            color: var(--primary-red);
            font-weight: 700;
        }

        .internal-tab-nav {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .itab-btn {
            background: none;
            border: none;
            padding: 8px 20px;
            font-weight: 700;
            color: var(--tab-inactive);
            font-size: 0.95rem;
            position: relative;
            cursor: pointer;
            transition: 0.3s;
        }

        .itab-btn.active {
            color: var(--primary-navy);
        }

        .itab-btn.active::after {
            content: "";
            position: absolute;
            bottom: -12px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary-navy);
        }

        .itab-panel {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .itab-panel.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-label-bold {
            display: block;
            font-weight: 700;
            font-size: 1.2rem;
            color: #000;
            margin-bottom: 15px;
        }

        .remaining-text {
            font-weight: 700;
            color: #333;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .repair-history-btns {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
        }

        .btn-history-pill {
            background-color: var(--primary-navy);
            color: white !important;
            border-radius: 10px;
            padding: 8px 30px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
            display: inline-block;
        }

        .btn-history-pill:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .service-scope-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 30px;
        }

        .service-scope-list li {
            position: relative;
            padding-left: 20px;
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .service-scope-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: var(--primary-red);
            font-weight: 900;
            font-size: 1.3rem;
        }

        .detail-footer-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 50px;
        }

        .btn-back-main {
            background-color: var(--primary-navy);
            color: white !important;
            border-radius: 8px;
            padding: 10px 45px;
            font-weight: 600;
            display: inline-block;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-back-main:hover {
            opacity: 0.95;
        }

        /* --- New Footer Styles --- */
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

        .copyright-text {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
        }

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .detail-top-hero {
                flex-direction: column;
                gap: 25px;
                padding-bottom: 20px;
            }

            .detail-img-box {
                width: 100%;
                height: 200px;
            }

            .detail-card {
                padding: 30px 20px;
                border-radius: 30px;
            }

            .internal-tab-nav {
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 15px;
            }

            .footer-divider {
                border-right: none !important;
            }
        }
    </style>
@endpush

@section('content')

    <nav class="main-navigation-bar sticky-top">
        <div class="container">
            <div class="collapse navbar-collapse d-lg-block" id="mainMenuCollapse">
                <ul class="navbar-nav d-flex flex-column flex-lg-row justify-content-center text-center">
                    <li class="nav-item"><a class="nav-link nav-link-custom active" href="index">{{ __('หน้าหลัก') }}</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="#">{{ __('สินค้าพร้อมติดตั้ง') }}</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="packages">{{ __('แพ็กเกจบริการ') }}</a></li>
                    <li class="nav-item"><a class="nav-link nav-link-custom" href="#">{{ __('บริการแนะนำ') }}</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="detail-main-wrapper">
        <div class="content-container-950">
            <div class="detail-card">
                <div class="detail-top-hero">
                    <div class="detail-img-box">
                        <img src="https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=600&q=80"
                            alt="Burglary Alarm">
                    </div>
                    <div class="detail-info-side">
                        <div class="detail-header-flex">
                            <h1 class="detail-main-title">Burglary Alarm<br>{{ __('(ระบบสัญญาณกันขโมย)') }}</h1>
                            <span class="detail-status-tag">{{ __('ใช้งาน') }}</span>
                        </div>
                        <label class="info-label-small">{{ __('แพ็กเกจดูแลรายเดือน') }} :</label>
                        <div class="info-value-navy">Burglary Alarm {{ __('(ระบบสัญญาณกันขโมย)') }}</div>

                        <label class="info-label-small">{{ __('ระยะเวลาการดูแล') }} :</label>
                        <div class="date-grid-info">
                            <div>
                                <label class="info-label-small">{{ __('เริ่มต้น') }}</label>
                                <span class="val-red-date">01 {{ __('ม.ค.') }} 2025</span>
                            </div>
                            <div>
                                <label class="info-label-small">{{ __('สิ้นสุด') }}</label>
                                <span class="val-red-date">31 {{ __('มี.ค.') }} 2025</span>
                            </div>
                        </div>
                    </div>
                </div>

                <nav class="internal-tab-nav">
                    <button class="itab-btn active" onclick="switchInternalTab(event, 'tabInfo')">{{ __('รายละเอียด') }}</button>
                    <button class="itab-btn" onclick="switchInternalTab(event, 'tabScope')">{{ __('ขอบเขตการบริการ') }}</button>
                    <button class="itab-btn" onclick="switchInternalTab(event, 'tabHistory')">{{ __('ประวัติการซ่อม') }}</button>
                </nav>

                <div class="itab-panels-container">
                    <div id="tabInfo" class="itab-panel active">
                        <span class="section-label-bold">{{ __('ข้อมูลแพ็กเกจ') }} :</span>
                        <div class="mb-4">
                            <label class="info-label-small">{{ __('จำนวนบริการคงเหลือ') }} :</label>
                            <p class="remaining-text">8 {{ __('ครั้ง') }}</p>
                        </div>
                        <p class="text-muted small">
                            {{ __('บริการดูแลรักษาอุปกรณ์เบื้องต้นเพื่อประสิทธิภาพสูงสุดในการทำงานของระบบรักษาความปลอดภัยของคุณ') }}
                        </p>
                    </div>

                    <div id="tabScope" class="itab-panel">
                        <span class="section-label-bold">{{ __('ขอบเขตการบริการ') }} :</span>
                        <ul class="service-scope-list">
                            <li>{{ __('ตรวจสอบ Motion Sensor, Door Contact, Panic Switch') }}</li>
                            <li>{{ __('ทดสอบการทำงานของสัญญาณเตือน') }}</li>
                            <li>{{ __('เช็ก Battery Backup / Power Supply') }}</li>
                            <li>{{ __('ตรวจสอบแผงควบคุม และการเชื่อมต่อ') }}</li>
                            <li>{{ __('แก้ไขระบบที่ไม่ทำงาน / แจ้งเตือนผิดพลาด') }}</li>
                            <li>{{ __('ตรวจสอบเซ็นเซอร์เสียงและกล่องควบคุม') }}</li>
                        </ul>
                    </div>

                    <div id="tabHistory" class="itab-panel">
                        <span class="section-label-bold">{{ __('ประวัติการแจ้งซ่อม') }} :</span>
                        <div class="repair-history-btns">
                            <a href="history_repair_entry" class="btn-history-pill">{{ __('ครั้งที่ 1') }}</a>
                            <a href="history_repair_entry" class="btn-history-pill">{{ __('ครั้งที่ 2') }}</a>
                        </div>
                        <p class="mt-4 text-muted small">* {{ __('ท่านสามารถกดเพื่อดูรายละเอียดปัญหาและการแก้ไขในแต่ละครั้งได้') }}
                        </p>
                    </div>
                </div>

                <div class="detail-footer-actions">
                    <a href="packages" class="btn-back-main">{{ __('ย้อนกลับ') }}</a>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer Section -->
@endsection
