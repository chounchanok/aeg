@extends('frontend.layouts.main')

@section('title', __('แพ็กเกจบริการตู้เซฟนิรภัย') . ' - AEG EASE CLUB')

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

        /* --- Header Styles (Match index.html) --- */
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

        /* --- Main Content Area --- */
        .page-wrapper {
            padding: 50px 0 100px;
        }

        .safe-detail-card {
            background: white;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            max-width: 900px;
            margin: 0 auto;
            border: none;
        }

        .banner-image {
            width: 100%;
            height: auto;
            display: block;
        }

        .card-body-content {
            padding: 40px 60px;
        }

        .section-header {
            text-align: center;
            margin-bottom: 40px;
            position: relative;
        }

        .section-header h1 {
            color: #1a2d5e;
            font-weight: 700;
            font-size: 1.8rem;
            display: inline-block;
            padding-bottom: 10px;
        }

        .section-header::after {
            content: "";
            display: block;
            width: 300px;
            height: 1px;
            background-color: #ddd;
            margin: 0 auto;
        }

        .content-group {
            margin-bottom: 35px;
        }

        .content-group h2 {
            font-weight: 700;
            font-size: 1.15rem;
            color: #1a2d5e;
            margin-bottom: 12px;
        }

        .detail-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .detail-list li {
            font-size: 1.05rem;
            color: #444;
            margin-bottom: 5px;
            line-height: 1.6;
        }

        .btn-consult-wrapper {
            text-align: center;
            margin-top: 50px;
        }

        .btn-gradient-pill {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 12px 55px;
            font-size: 1.2rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 5px 20px rgba(196, 30, 58, 0.3);
            transition: 0.3s;
        }

        .btn-gradient-pill:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(196, 30, 58, 0.4);
            opacity: 0.95;
        }

        /* Floating Chat Icon */
        .floating-chat {
            position: fixed;
            bottom: 40px;
            right: 40px;
            z-index: 1000;
        }

        .chat-circle {
            width: 60px;
            height: 60px;
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

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .card-body-content {
                padding: 30px 25px;
            }

            .section-header h1 {
                font-size: 1.4rem;
            }

            .section-header::after {
                width: 200px;
            }

            .content-group h2 {
                font-size: 1rem;
            }

            .detail-list li {
                font-size: 0.9rem;
            }

            .btn-gradient-pill {
                width: 100%;
                max-width: 320px;
                font-size: 1.1rem;
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

    <!-- Main Content Section -->
    <main class="page-wrapper">
        <div class="container">
            <div class="safe-detail-card">
                <!-- Header Banner Image (Matching Safe.jpg) -->
                <img src="assets/image/safe-banner.webp" alt="{{ __('แพ็กเกจบริการตู้เซฟนิรภัย') }}" class="banner-image"
                    onerror="this.src='https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?auto=format&fit=crop&w=1200&q=80'">

                <div class="card-body-content">
                    <div class="section-header">
                        <h1>{{ __('แพ็กเกจบริการตู้เซฟนิรภัย') }}</h1>
                    </div>

                    <!-- Content Group 1: กันไฟ -->
                    <div class="content-group">
                        <h2>{{ __('ตู้เซฟนิรภัยกันไฟ') }}</h2>
                        <ul class="detail-list">
                            <li>{{ __('ป้องกันเอกสารและทรัพย์สินจากความร้อนและเปลวไฟ') }}</li>
                            <li>{{ __('โครงสร้างวัสดุทนไฟ ป้องกันความเสียหายจากอัคคีภัย') }}</li>
                            <li>{{ __('เสริมความปลอดภัยด้วยระบบล็อกหลากหลายรูปแบบ') }}</li>
                        </ul>
                    </div>

                    <!-- Content Group 2: กันโจรกรรม -->
                    <div class="content-group">
                        <h2>{{ __('ตู้เซฟนิรภัยกันการเจาะโจรกรรม') }}</h2>
                        <ul class="detail-list">
                            <li>{{ __('โครงสร้างเหล็กหนา แข็งแรง ทนต่อการงัดแงะและการเจาะทำลาย') }}</li>
                            <li>{{ __('ระบบล็อกหลายชั้น เพิ่มความปลอดภัยต่อเหตุการณ์ไม่คาดฝัน') }}</li>
                            <li>{{ __('สามารถติดตั้งยึดกับพื้นหรือผนังเพื่อป้องกันการเคลื่อนย้าย') }}</li>
                        </ul>
                    </div>

                    <!-- Action Button Link to Contact -->
                    <div class="btn-consult-wrapper">
                        <a href="{{ route('safe-contact', 5) }}" class="btn-gradient-pill">{{ __('ปรึกษาผู้เชี่ยวชาญ') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

@endsection
