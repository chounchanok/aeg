@extends('frontend.layouts.main')

@section('title', 'รายละเอียดการแจ้งซ่อม - AEG EASE CLUB')

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
        .repair-detail-wrapper {
            padding: 60px 0 100px;
        }

        .repair-card {
            background: white;
            border-radius: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 50px;
            border: 1px solid #eee;
        }

        /* Top Area: Image & Title */
        .repair-hero {
            display: flex;
            gap: 40px;
            margin-bottom: 35px;
        }

        .repair-img-box {
            width: 320px;
            height: 220px;
            border-radius: 18px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .repair-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .repair-title-block {
            flex-grow: 1;
        }

        .repair-main-title {
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--primary-navy);
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .repair-badge {
            background-color: var(--primary-navy);
            color: white;
            display: inline-block;
            padding: 8px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 25px;
        }

        .repair-dates-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .date-label {
            display: block;
            font-size: 0.85rem;
            color: #888;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .date-value {
            font-weight: 700;
            color: var(--primary-red);
            font-size: 1.1rem;
        }

        /* Info Blocks */
        .info-section {
            margin-bottom: 25px;
        }

        .section-label {
            display: block;
            font-weight: 700;
            font-size: 1.2rem;
            color: #000;
            margin-bottom: 8px;
        }

        .info-text-dark {
            font-weight: 500;
            color: #333;
            font-size: 1.05rem;
        }

        .remaining-count {
            font-weight: 700;
            font-size: 1.1rem;
            color: #333;
        }

        /* Scope List (Red Dots) */
        .scope-list-custom {
            list-style: none;
            padding-left: 0;
            margin-bottom: 50px;
        }

        .scope-list-custom li {
            position: relative;
            padding-left: 20px;
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .scope-list-custom li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: var(--primary-red);
            font-weight: 900;
            font-size: 1.2rem;
        }

        /* Footer Action */
        .btn-footer-right {
            text-align: right;
        }

        .btn-navy-back {
            background-color: var(--primary-navy);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 40px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
        }

        .btn-navy-back:hover {
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

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .repair-hero {
                flex-direction: column;
                gap: 30px;
            }

            .repair-img-box {
                width: 100%;
                height: 200px;
            }

            .repair-card {
                padding: 30px 20px;
            }

            .repair-dates-grid {
                gap: 15px;
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

    <!-- Main Repair Details Content -->
    <main class="repair-detail-wrapper">
        <div class="container-950">
            <div class="repair-card">

                <!-- Hero Section: Image and Top Text -->
                <div class="repair-hero">
                    <div class="repair-img-box">
                        <img src="https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=600&q=80"
                            alt="Burglary Alarm System">
                    </div>
                    <div class="repair-title-block">
                        <h1 class="repair-main-title">Burglary Alarm<br>(ระบบสัญญาณกันขโมย)</h1>
                        <div class="repair-badge">ครั้งที่ 2</div>
                        <div class="repair-dates-grid">
                            <div class="date-item">
                                <label class="date-label">วันที่แจ้งซ่อม</label>
                                <span class="date-value">30 มี.ค. 2024</span>
                            </div>
                            <div class="date-item">
                                <label class="date-label">วันที่แก้ไข</label>
                                <span class="date-value">31 มี.ค. 2024</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Sections -->
                <div class="info-section">
                    <span class="section-label">แพ็กเกจดูแลรายเดือน :</span>
                    <p class="info-text-dark">Burglary Alarm (ระบบสัญญาณกันขโมย)</p>
                </div>

                <div class="info-section">
                    <span class="section-label">จำนวนบริการคงเหลือ :</span>
                    <p class="remaining-count">8 ครั้ง</p>
                </div>

                <div class="info-section">
                    <span class="section-label">รายละเอียดปัญหาและการแก้ไข :</span>
                    <p class="info-text-dark">สายสัญญาณชำรุด</p>
                </div>

                <!-- Scope of Service (Bullet list) -->
                <div class="info-section">
                    <span class="section-label">ขอบเขตการบริการ :</span>
                    <ul class="scope-list-custom">
                        <li>ตรวจสอบ Motion Sensor, Door Contact, Panic Switch</li>
                        <li>ทดสอบการทำงานของสัญญาณเตือน</li>
                        <li>เช็ก Battery Backup / Power Supply</li>
                        <li>ตรวจสอบแผงควบคุม และการเชื่อมต่อ</li>
                        <li>แก้ไขระบบที่ไม่ทำงาน / แจ้งเตือนผิดพลาด</li>
                        <li>ตรวจสอบเซ็นเซอร์เสียงและกล่องควบคุม</li>
                    </ul>
                </div>

                <!-- Footer Back Button -->
                <div class="btn-footer-right">
                    <a href="history_detail" class="btn-navy-back">ย้อนกลับ</a>
                </div>

            </div>
        </div>
    </main>
@endsection
