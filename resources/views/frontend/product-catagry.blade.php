@extends('frontend.layouts.main')

@section('title', 'ระบบรักษาความปลอดภัย - AEG EASE CLUB')

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
            --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
            --btn-gradient: linear-gradient(90deg, #4a1c40 0%, #c41e3a 100%);
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #ffffff;
            color: #333;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* --- Header Styles (from index.html) --- */
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
            text-decoration: none;
        }

        /* --- Category Navigation Tabs (Enlarged with Images) --- */
        .category-nav {
            padding: 50px 0;
            background: white;
            border-bottom: 1px solid #f0f0f0;
        }

        .nav-tabs-custom {
            display: flex;
            justify-content: center;
            gap: 20px;
            border: none;
            overflow-x: auto;
            flex-wrap: nowrap;
            padding-bottom: 15px;
            scrollbar-width: none;
        }

        .nav-tabs-custom::-webkit-scrollbar {
            display: none;
        }

        .nav-tabs-custom .nav-link {
            border: none !important;
            padding: 0;
            background: none !important;
        }

        .category-box {
            background: #ffffff;
            border: 1px solid #f0f0f0;
            border-radius: 20px;
            padding: 25px 15px;
            width: 220px;
            height: 190px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 0.3s ease;
        }

        .category-icon {
            width: 70px;
            height: 70px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .category-icon img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            filter: grayscale(100%);
            opacity: 0.4;
            transition: all 0.3s ease;
        }

        .category-text-en {
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 4px;
            color: #bbb;
        }

        .category-text-th {
            font-weight: 400;
            font-size: 0.85rem;
            color: #ccc;
        }

        /* Active State */
        .nav-link.active .category-box {
            border: 1px solid #eee;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            background-color: #fff;
            transform: translateY(-5px);
        }

        .nav-link.active .category-icon img {
            filter: grayscale(0%);
            opacity: 1;
        }

        .nav-link.active .category-text-en {
            color: #111;
        }

        .nav-link.active .category-text-th {
            color: #444;
        }

        /* --- Product Grid Styles --- */
        .product-section {
            padding: 60px 0 100px;
            background-color: #fff;
        }

        .product-item-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 60px;
        }

        .product-image-frame {
            background: white;
            border: 1px solid #f2f2f2;
            border-radius: 25px;
            padding: 30px;
            width: 100%;
            max-width: 380px;
            aspect-ratio: 1 / 0.8;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        .product-image-frame:hover {
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.07);
            border-color: #e8e8e8;
        }

        .product-image-frame img {
            max-width: 95%;
            max-height: 95%;
            object-fit: contain;
        }

        .product-title-en {
            font-weight: 700;
            font-size: 1.15rem;
            margin-bottom: 4px;
            color: #1a2d5e;
            white-space: nowrap;
        }

        .product-title-th {
            font-weight: 500;
            font-size: 1rem;
            color: #1a2d5e;
            margin-bottom: 20px;
        }

        .btn-action-sales {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 10px 50px;
            font-size: 1.25rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(196, 30, 58, 0.25);
            transition: all 0.3s ease;
        }

        .btn-action-sales:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(196, 30, 58, 0.35);
            opacity: 0.95;
        }

        /* Floating Chat */
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

        @media (max-width: 1200px) {
            .category-box {
                width: 180px;
                height: 160px;
            }

            .product-image-frame {
                max-width: 320px;
            }
        }

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .footer-column {
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                margin-bottom: 25px;
                padding-bottom: 25px;
            }

            .nav-tabs-custom {
                justify-content: flex-start;
                padding-left: 15px;
            }

            .product-image-frame {
                max-width: 100%;
                padding: 20px;
            }

            .btn-action-sales {
                font-size: 1.1rem;
                padding: 8px 35px;
            }

            .category-box {
                width: 150px;
                height: 150px;
            }

            .category-icon {
                width: 60px;
                height: 60px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="category-nav">
        <div class="container">
            <div class="nav nav-tabs nav-tabs-custom flex-nowrap overflow-auto" id="securityTabs" role="tablist" style="border-bottom: none; -webkit-overflow-scrolling: touch;">

                <a href="{{ url('products/' . ($currentGroup ?? 'equipment')) }}" class="nav-link {{ is_null($currentCategoryId ?? null) ? 'active' : '' }}" style="text-decoration: none; border: none;">
                    <div class="category-box">
                        <div class="category-icon">
                            <img src="{{ asset('assets/image/cat1.webp') }}" alt="All Products">
                        </div>
                        <div class="category-text-en">All Products</div>
                        <div class="category-text-th">ทั้งหมด</div>
                    </div>
                </a>

                @if(isset($categories))
                    @foreach($categories as $cat)
                        @php
                            $catNameTh = $cat->title_th ?? $cat->title_en ?? 'หมวดหมู่';
                            $catNameEn = $cat->title_en ?? $cat->title_th ?? 'Category';
                            $icon = $cat->image_url ? asset($cat->image_url) : asset('assets/image/cat1.webp');
                        @endphp

                        <a href="{{ url('products/'.$cat->group.'/'.$cat->id) }}" class="nav-link {{ ($currentCategoryId ?? null) == $cat->id ? 'active' : '' }}" style="text-decoration: none; border: none;">
                            <div class="category-box">
                                <div class="category-icon">
                                    <img src="{{ $icon }}" alt="{{ $catNameEn }}">
                                </div>
                                <div class="category-text-en">{{ $catNameEn }}</div>
                                <div class="category-text-th">{{ $catNameTh }}</div>
                            </div>
                        </a>
                    @endforeach
                @endif

            </div>
        </div>
    </div>

    <main class="product-section">
        <div class="container">
            <div class="tab-content">
                <div class="tab-pane fade show active" role="tabpanel">
                    <div class="row g-3 g-lg-4 justify-content-center">

                        @forelse($products as $product)
                            <div class="col-6 col-md-4">
                                <div class="product-item-wrapper">
                                    <a href="{{ route('product-detail', $product->id) }}" class="text-decoration-none w-100 d-flex justify-content-center">
                                        <div class="product-image-frame">
                                            <img src="{{ $product->image_url ?? asset('assets/image/product-1.webp') }}" alt="{{ $product->name }}">
                                        </div>
                                    </a>
                                    <div class="product-title-en">{{ $product->name }}</div>
                                    <div class="product-title-th">฿{{ number_format($product->price, 2) }}</div>

                                    <a href="{{ route('product-detail', $product->id) }}" class="btn-action-sales">ดูรายละเอียด</a>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center text-muted py-5">
                                <i class="fas fa-box-open fa-3x mb-3" style="opacity: 0.5;"></i>
                                <h5>ไม่พบข้อมูลสินค้าในหมวดหมู่นี้</h5>
                            </div>
                        @endforelse

                    </div>

                    <div class="d-flex justify-content-center mt-5">
                        {{ $products->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </div>
    </main>

    <div class="position-fixed" style="bottom: 40px; right: 40px; z-index: 1000;">
        <div class="bg-white rounded-circle shadow d-flex align-items-center justify-content-center border" style="width: 65px; height: 65px;">
            <i class="fas fa-comment-dots text-danger fs-3"></i>
        </div>
    </div>
@endsection
