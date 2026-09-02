@extends('frontend.layouts.main')

@section('title', __('รายละเอียดแลกของรางวัล - AEG EASE CLUB'))

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
            --btn-gradient: linear-gradient(90deg, #4a1c40 0%, #c41e3a 100%);
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);

            /* Gradient defined by user image 1774336746447.jpg */
            /* Direction: Top to Bottom. Red 100% -> Blue 42% -> Fade 0% */
            --custom-hero-gradient: linear-gradient(180deg,
                    rgba(227, 31, 38, 1) 0%,
                    rgba(23, 46, 89, 0.42) 50%,
                    rgba(247, 247, 247, 0) 100%);
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #f4f5f7;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* --- Header Styles (Standard AEG - Original Width) --- */
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

        /* --- Main Content Layout --- */
        .reward-content-wrapper {
            width: 100%;
            min-height: 100vh;
            background-color: #fff;
        }

        /* Container strictly 950px for content */
        .container-950 {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Hero section with Gradient - No Top Border Radius, flush with menu */
        .hero-gradient-box {
            background: var(--custom-hero-gradient);
            padding: 60px 0 30px;
            width: 100%;
        }

        .hero-inner {
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .reward-img-frame {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            max-width: 450px;
        }

        .reward-img-frame img {
            max-width: 100%;
            height: auto;
        }

        .hero-text-side {
            flex: 1;
            color: white;
        }

        .hero-text-side h1 {
            font-weight: 800;
            font-size: 3rem;
            margin-bottom: 5px;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .hero-text-side h2 {
            font-weight: 700;
            font-size: 1.6rem;
            margin-bottom: 30px;
            line-height: 1.3;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Redeem Button Box */
        .btn-redeem-pill {
            background: white;
            border-radius: 10px;
            padding: 3px 45px;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            border: none;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-redeem-pill:hover {
            transform: translateY(-5px);
        }

        .btn-redeem-pill .lbl {
            font-weight: 700;
            color: var(--primary-dark);
            font-size: 1.1rem;
        }

        .btn-redeem-pill .pts {
            font-weight: 800;
            color: var(--primary-red);
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* --- Details Section (Where Gradient ends) --- */
        .reward-details-area {
            padding: 20px 0 100px;
        }

        .main-product-title {
            font-weight: 700;
            font-size: 2rem;
            color: var(--primary-dark);
            margin-bottom: 30px;
        }

        .specs-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 50px;
        }

        .specs-list li {
            position: relative;
            padding-left: 20px;
            font-size: 1rem;
            color: #444;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .specs-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: var(--primary-red);
            font-weight: 900;
        }

        .section-sub-title {
            font-weight: 700;
            color: var(--primary-red);
            font-size: 1.25rem;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .section-desc-text {
            font-size: 1rem;
            color: #555;
            line-height: 1.8;
            margin-bottom: 40px;
        }

        /* --- QR Modal Style --- */
        .modal-qr-content {
            border-radius: 20px !important;
            border: none;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-qr-blue-top {
            background-color: var(--primary-dark);
            color: white;
            padding: 30px 20px 45px;
            text-align: center;
            position: relative;
        }

        .modal-qr-blue-top h5 {
            font-weight: 700;
            margin-bottom: 30px;
            font-size: 1.6rem;
        }

        .btn-qr-close {
            position: absolute;
            right: 20px;
            top: 20px;
            background: transparent;
            border: none;
            color: white;
            font-size: 1.4rem;
            cursor: pointer;
            opacity: 0.8;
        }

        .qr-frame {
            display: inline-block;
            background: white;
            padding: 15px;
            border-radius: 15px;
        }

        .modal-qr-footer {
            background-color: white;
            padding: 35px 25px;
            text-align: center;
            color: #333;
        }

        /* --- Footer Styles (Original Width) --- */
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

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .hero-inner {
                flex-direction: column;
                text-align: center;
                gap: 40px;
            }

            .hero-text-side {
                padding-left: 0;
            }

            .hero-text-side h1 {
                font-size: 2.2rem;
            }

            .main-product-title {
                font-size: 1.5rem;
                text-align: center;
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
    <!-- Content Wrapper -->
    <main class="reward-content-wrapper">

        @if(isset($rewards) && $rewards->count() > 0)
            <!-- Hero Section with Gradient -->
            <section class="hero-gradient-box mt-4">
                <div class="container-950">
                    <div class="hero-inner">
                        <div class="reward-img-frame">
                            <img src="{{ $rewards->image_url }}" alt="{{ $rewards->title }}" onerror="this.src='https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?auto=format&fit=crop&w=800&q=80'">
                        </div>
                        <div class="hero-text-side">
                            <h1>{{ $rewards->title_th }}</h1>
                            <h2>{!! nl2br(e($rewards->description_th)) !!}</h2>

                            <button type="button" class="btn-redeem-pill" data-bs-toggle="modal" data-bs-target="#redeemModal">
                                <span class="lbl">{{ __('รับสิทธิ์') }}</span>
                                <div class="pts">
                                    <i class="fas fa-coins" style="color: #edb314;"></i> {{ number_format($rewards->points_required) }}
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Detailed Info -->
            <section class="reward-details-area mb-5">
                <div class="container-950">
                    <h3 class="main-product-title">{{ $rewards->title_th }}</h3>

                    <h4 class="section-sub-title">{{ __('รายละเอียดการแลกรางวัล') }}</h4>
                    <p class="section-desc-text">{{ __('*แลกใช้เพียง') }} {{ number_format($rewards->points_required) }} {{ __('พอยท์ โดยแต้มจะถูกหักทันทีหลังการยืนยันผ่านแอปพลิเคชัน') }}</p>

                    <h4 class="section-sub-title mt-4">{{ __('เงื่อนไขการใช้สิทธิ์') }}</h4>
                    <div class="section-desc-text">
                        <ul class="specs-list">
                            <li>{{ __('สิทธิพิเศษนี้ไม่สามารถโอนสิทธิ์, จำหน่าย หรือแลกเปลี่ยนเป็นเงินสดได้ในทุกกรณี') }}</li>
                            <li>{{ __('บริษัทขอสงวนสิทธิ์ในการยกเลิกหรือคืนคะแนนในทุกกรณีเมื่อยืนยันการแลกคะแนนเสร็จสมบูรณ์แล้ว') }}</li>
                            <li>{{ __('กรุณาตรวจสอบสภาพสินค้าและความครบถ้วน ณ จุดรับสินค้า') }}</li>
                        </ul>
                    </div>
                    <hr class="mt-5 mb-5">
                </div>
            </section>
        @else
            <div class="container text-center mt-5 mb-5">
                <h3 class="text-muted">{{ __('ยังไม่มีสิทธิพิเศษในขณะนี้') }}</h3>
            </div>
        @endif

    </main>

    <!-- (ใส่ Footer เดิมของคุณตรงนี้) -->

    <!-- Redeem Modal -->
    <div class="modal fade" id="redeemModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
            <div class="modal-content modal-qr-content">
                <div class="modal-qr-blue-top">
                    <span style="font-size: 0.8rem; opacity: 0.8; text-transform: uppercase;">{{ __('ดำเนินการต่อบนแอปพลิเคชัน') }}</span>
                    <h5>{{ __('สแกน Qr Code') }}</h5>
                    <button type="button" class="btn-qr-close" data-bs-dismiss="modal">
                        <i class="fas fa-xmark"></i>
                    </button>
                    <div class="qr-frame">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=170x170&data={{ urlencode(route('app.download')) }}" alt="QR" class="w-100">
                    </div>
                </div>
                <div class="modal-qr-footer">
                    {{ __('สแกนคิวอาร์โค้ดเพื่อดำเนินการแลกรางวัลผ่านแอป') }}
                </div>
            </div>
        </div>
    </div>
@endsection
