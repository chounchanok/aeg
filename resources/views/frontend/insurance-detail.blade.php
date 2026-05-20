
@extends('frontend.layouts.main')

@section('title', 'รายละเอียดประกันภัย - AEG EASE CLUB')

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

        /* --- Insurance Detail Main --- */
        .detail-wrapper {
            padding: 50px 0 100px;
        }

        .insurance-detail-card {
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

        .detail-body {
            padding: 40px 60px;
        }

        .detail-header-title {
            text-align: center;
            color: #1a2d5e;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 30px;
            line-height: 1.4;
        }

        .detail-intro {
            font-weight: 600;
            font-size: 1.05rem;
            color: #1a2d5e;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .detail-description {
            font-size: 1rem;
            color: #555;
            margin-bottom: 25px;
            line-height: 1.7;
        }

        .coverage-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 40px;
        }

        .coverage-list li {
            position: relative;
            padding-left: 25px;
            margin-bottom: 12px;
            font-size: 1rem;
            color: #444;
            line-height: 1.6;
        }

        .coverage-list li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: #666;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .btn-consult {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 12px 50px;
            font-size: 1.25rem;
            font-weight: 600;
            text-decoration: none;
            display: block;
            margin: 0 auto;
            width: fit-content;
            box-shadow: 0 5px 20px rgba(196, 30, 58, 0.3);
            transition: 0.3s;
        }

        .btn-consult:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(196, 30, 58, 0.4);
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

            .detail-body {
                padding: 30px 25px;
            }

            .detail-header-title {
                font-size: 1.4rem;
            }

            .detail-intro {
                font-size: 0.95rem;
            }

            .coverage-list li {
                font-size: 0.9rem;
            }

            .btn-consult {
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

    <main class="detail-wrapper">
        <div class="container">
            <div class="insurance-detail-card">
                <img src="{{ $insurance->image_url ?? 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=1200&q=80' }}" 
                    alt="{{ $insurance->title_th }}" class="banner-image">

                <div class="detail-body">
                    <h1 class="detail-header-title">
                        {{ $insurance->title_th }}
                    </h1>

                    <div class="detail-description mb-5">
                        {!! $insurance->description_th !!}
                    </div>

                    <a href="{{ route('insurance-contact', $insurance->id) }}" class="btn-consult">ปรึกษาผู้เชี่ยวชาญ</a>
                </div>
            </div>
        </div>
    </main>

@endsection
