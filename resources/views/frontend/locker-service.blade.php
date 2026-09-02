@extends('frontend.layouts.main')

@section('title', __('บริการตู้ล็อกเกอร์') . ' - AEG EASE CLUB')

@push('styles')
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

        /* --- Main Locker Content --- */
        .locker-section {
            padding: 60px 0 100px;
        }

        .locker-card {
            background: white;
            border-radius: 40px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 45px;
            transition: transform 0.3s ease;
            max-width: 950px;
            margin-left: auto;
            margin-right: auto;
        }

        .locker-card:hover {
            transform: translateY(-8px);
        }

        .locker-image-box {
            width: 100%;
            height: 420px;
            overflow: hidden;
        }

        .locker-image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .locker-content {
            padding: 35px 20px;
            text-align: center;
        }

        .locker-title {
            font-weight: 700;
            font-size: 1.8rem;
            color: #1a2d5e;
            margin-bottom: 25px;
        }

        .btn-view-details {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 12px 55px;
            font-size: 1.15rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(196, 30, 58, 0.25);
            transition: 0.3s;
        }

        .btn-view-details:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 20px rgba(196, 30, 58, 0.35);
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
            width: 65px;
            height: 65px;
            background: white;
            border-radius: 50%;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.12);
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
            font-size: 1.4rem;
            text-decoration: none;
        }

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .locker-image-box {
                height: 280px;
            }

            .locker-title {
                font-size: 1.4rem;
            }

            .btn-view-details {
                font-size: 1rem;
                padding: 10px 40px;
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

    <!-- Main Locker Services Content -->
    <main class="locker-section">
        <div class="container">

            <!-- Service Card 1: AEG Smart Locker -->
            <div class="locker-card">
                <div class="locker-image-box">
                    <!-- Placeholder representing the image in Locker.jpg (Man with yellow locker) -->
                    <img src="https://aeg.champagne.orangeworkshop.info/storage/products/fb90e825349d8bc5474a4af6f2b574fae7e4420c.jpg"
                        alt="AEG Smart Locker">
                </div>
                <div class="locker-content">
                    <h2 class="locker-title">AEG Smart Locker</h2>
                    <a href="{{ route('locker-detail') }}" class="btn-view-details">{{ __('ดูรายละเอียด') }}</a>
                </div>
            </div>

            <!-- Service Card 2: บริการตู้เซฟนิรภัย -->
            <div class="locker-card">
                <div class="locker-image-box">
                    <!-- Placeholder representing the safes in Locker.jpg -->
                    <img src="https://images.unsplash.com/photo-1606760227091-3dd870d97f1d?auto=format&fit=crop&w=1200&q=80"
                        alt="{{ __('บริการตู้เซฟนิรภัย') }}">
                </div>
                <div class="locker-content">
                    <h2 class="locker-title">{{ __('บริการตู้เซฟนิรภัย') }}</h2>
                    <a href="{{ route('safe-detail') }}" class="btn-view-details">{{ __('ดูรายละเอียด') }}</a>
                </div>
            </div>

        </div>
    </main>

@endsection
