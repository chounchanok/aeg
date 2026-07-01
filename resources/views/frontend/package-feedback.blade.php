@extends('frontend.layouts.main')

@section('title', 'ให้คะแนนและรีวิว - AEG EASE CLUB')

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
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
            --gray-banner: #EBEDF4;
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #f4f5f7;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* --- Header & Navigation --- */
        .navbar-main-header {
            background-image: url('assets/image/header-bk.webp');
            background-size: cover;
            background-position: center;
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
            transition: opacity 0.3s;
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
            color: white;
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
            padding: 8px 20px;
            border: none;
            width: 100%;
            font-size: 0.95rem;
        }

        .search-btn {
            position: absolute;
            right: 15px;
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

        /* --- Content Layout --- */
        .content-container-950 {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .reward-banner-gray {
            background-color: var(--gray-banner);
            text-align: center;
            padding: 10px 0;
            font-size: 0.85rem;
            font-weight: 600;
            color: #c41e3a;
        }

        .feedback-wrapper {
            padding: 50px 0 100px;
        }

        .feedback-card {
            background: white;
            border-radius: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 50px;
            border: 1px solid #eee;
        }

        .feedback-grid {
            display: flex;
            gap: 40px;
        }

        .col-left {
            flex: 0 0 350px;
        }

        .col-right {
            flex: 1;
        }

        .pkg-img-box {
            width: 100%;
            height: 220px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .pkg-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .rating-group {
            margin-bottom: 25px;
        }

        .rating-label {
            display: block;
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--primary-navy);
            margin-bottom: 12px;
            line-height: 1.4;
        }

        .star-rating {
            display: flex;
            gap: 10px;
        }

        .star-rating i {
            font-size: 1.8rem;
            color: #ddd;
            cursor: pointer;
            transition: 0.2s;
        }

        .star-rating i.selected {
            color: #f1c40f;
        }

        .pkg-title-main {
            font-weight: 700;
            font-size: 1.8rem;
            color: var(--primary-navy);
            margin-bottom: 5px;
            line-height: 1.3;
        }

        .pkg-subtitle {
            color: #888;
            font-size: 0.95rem;
            font-weight: 500;
            display: block;
            margin-bottom: 30px;
        }

        .custom-textarea {
            background-color: #ebedf4;
            border: none;
            border-radius: 12px;
            padding: 20px;
            width: 100%;
            min-height: 220px;
            font-size: 0.95rem;
            color: #333;
            resize: none;
        }

        .action-btns-row {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-navy-pill {
            background-color: var(--primary-navy);
            color: white !important;
            border: none;
            border-radius: 10px;
            padding: 10px 45px;
            font-weight: 600;
            font-size: 1.1rem;
            min-width: 160px;
            text-decoration: none;
            text-align: center;
            transition: 0.3s;
        }

        /* --- Footer --- */
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

        @media (max-width: 991px) {
            .navbar-top-row {
                display: none;
            }

            .feedback-grid {
                flex-direction: column;
                gap: 30px;
            }

            .col-left {
                width: 100%;
            }

            .action-btns-row {
                flex-direction: column-reverse;
            }

            .footer-divider {
                border-right: none !important;
            }

            /* Hamburger Menu Icon: White SVG */
            .navbar-toggler {
                border-color: rgba(255, 255, 255, 0.7) !important;
            }

            .navbar-toggler-icon {
                background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255, 255, 255, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E") !important;
            }
        }
    </style>
@endpush

@section('content')

    <div class="reward-banner-gray">
        <div class="container">
            <i class="fas fa-coins" style="color: #edb314;"></i> ให้คะแนนและเขียนรีวิวเพื่อรับ 1 EASE Coins
        </div>
    </div>

    <main class="feedback-wrapper">
        <div class="content-container-950">
            <div class="feedback-card">
                <div class="feedback-grid">
                    <div class="col-left">
                        <div class="pkg-img-box">
                            <img src="{{ $item->product ? $item->product->image_url : 'https://via.placeholder.com/600' }}" alt="{{ $item->product_name }}">
                        </div>
                        <div class="rating-group">
                            <span class="rating-label">ให้คะแนนคุณภาพงานติดตั้งและบริการหลังการขาย</span>
                            <div class="star-rating" data-id="install">
                                <i class="fas fa-star selected" data-value="1"></i>
                                <i class="fas fa-star selected" data-value="2"></i>
                                <i class="fas fa-star selected" data-value="3"></i>
                                <i class="fas fa-star selected" data-value="4"></i>
                                <i class="fas fa-star" data-value="5"></i>
                            </div>
                        </div>
                        <div class="rating-group">
                            <span class="rating-label">ให้คะแนนคุณภาพการให้คำแนะนำและข้อมูลจากฝ่ายขาย</span>
                            <div class="star-rating" data-id="sales">
                                <i class="fas fa-star selected" data-value="1"></i>
                                <i class="fas fa-star selected" data-value="2"></i>
                                <i class="fas fa-star selected" data-value="3"></i>
                                <i class="fas fa-star selected" data-value="4"></i>
                                <i class="fas fa-star" data-value="5"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-right">
                        <h1 class="pkg-title-main">{{ $item->product_name }}</h1>
                        <span class="pkg-subtitle">แพ็กเกจการดูแลอุปกรณ์ (Order: {{ $item->order->order_number }})</span>

                        <form action="{{ url('/packages/feedback/' . $item->id) }}" method="POST">
                            @csrf

                            <input type="hidden" name="install_rating" id="install_rating_input" value="4">
                            <input type="hidden" name="sales_rating" id="sales_rating_input" value="4">

                            <div class="review-block">
                                <span class="review-label font-weight-bold d-block mb-2">เขียนรีวิว</span>
                                <textarea class="custom-textarea" name="review_text" placeholder="เขียนรีวิวหรือคำแนะนำเพื่อปรับปรุงบริการ"></textarea>
                            </div>

                            <div class="action-btns-row">
                                <a href="{{ route('packages.mine') }}" class="btn-navy-pill bg-secondary text-dark border-0">ย้อนกลับ</a>
                                <button type="submit" class="btn-navy-pill">ยืนยัน</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>



    <!-- Footer Section -->
@endsection
@push('scripts')

    <!-- Bootstrap 5.3.3 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const ratings = document.querySelectorAll('.star-rating');
        ratings.forEach(container => {
            const type = container.getAttribute('data-id'); // 'install' หรือ 'sales'
            const hiddenInput = document.getElementById(type + '_rating_input'); // หา Input ซ่อน

            const stars = container.querySelectorAll('i');
            stars.forEach(star => {
                star.onclick = function () {
                    const val = parseInt(this.getAttribute('data-value'));
                    hiddenInput.value = val; // 🌟 เก็บค่าลง Form

                    stars.forEach(s => {
                        const sVal = parseInt(s.getAttribute('data-value'));
                        if (sVal <= val) {
                            s.classList.add('selected');
                        } else {
                            s.classList.remove('selected');
                        }
                    });
                };
            });
        });

        function confirmReview() {
            window.location.href = 'packages';
        }
    </script>
@endpush
