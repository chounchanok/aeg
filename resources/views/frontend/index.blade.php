@extends('frontend.layouts.main')

@section('title', 'หน้าหลัก - AEG EASE CLUB')

@push('styles')
<style>
    :root {
        --primary-dark: #1a1a2e;
        --primary-red: #c41e3a;
        --primary-purple: #4a1c40;
        --card-bg: #EBEDF4;
        --privilege-bg: #9899A2;
        --ease-gradient: linear-gradient(135deg, #1a1a2e 0%, #c41e3a 100%);
    }

    body {
        font-family: 'Poppins', 'Kanit', sans-serif !important;
        background-color: #ffffff;
        color: #333;
    }

    /* 1. Header & Navbar */
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
        transition: opacity 0.3s;
        cursor: pointer;
    }

    /* Dropdown Styles */
    .header-dropdown .btn-dropdown {
        color: white;
        background: transparent;
        border: none;
        font-size: 0.85rem;
        padding: 0;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .header-dropdown .dropdown-menu {
        min-width: 160px;
        background: var(--primary-dark);
        border: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: 10px !important;
        z-index: 99 !important;
        /* z-index 99 */
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
        padding: 8px 50px 8px 20px;
        border: none;
        width: 100%;
        font-size: 0.95rem;
    }

    .search-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        color: #666;
        border: none;
        font-size: 1.1rem;
    }

    .cart-section {
        display: flex;
        align-items: center;
        gap: 15px;
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

    /* Hamburger Menu Icon: White SVG */
    .navbar-toggler {
        border: 1px solid rgba(255, 255, 255, 0.7) !important;
        padding: 4px 8px;
    }

    .navbar-toggler-icon {
        background-image: url("data:image/svg+xml;charset=utf8,%3Csvg viewBox='0 0 30 30' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath stroke='rgba(255, 255, 255, 1)' stroke-width='2' stroke-linecap='round' stroke-miterlimit='10' d='M4 7h22M4 15h22M4 23h22'/%3E%3C/svg%3E") !important;
    }

    /* 2. Main Navigation */
    .main-navigation-bar {
        background-color: #fff;
        border-bottom: 1px solid #eee;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        z-index: 90;
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

    /* 3. Hero Carousel */
    .carousel-item img {
        height: 400px;
        object-fit: cover;
        border-radius: 15px;
    }

    /* 4. Service Cards */
    .service-card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease;
        height: 100%;
        background: var(--card-bg);
    }

    .service-card:hover {
        transform: translateY(-5px);
    }

    .service-card img {
        height: 370px;
        object-fit: cover;
        width: 100%;
    }

    .service-card-body {
        padding: 20px;
        text-align: center;
    }

    .btn-service {
        background: var(--ease-gradient);
        color: white;
        border: none;
        border-radius: 20px;
        padding: 8px 25px;
        font-size: 0.9rem;
    }

    /* 5. Age Services */
    .age-service-box {
        background-color: #EBEDF4 !important;
        border-radius: 15px;
        padding: 20px;
        display: flex;
        align-items: center;
    }

    .age-icon {
        width: 60px;
        height: 60px;
        object-fit: contain;
        margin-right: 15px;
    }

    /* 6. Recommended Services */
    .rec-card {
        border-radius: 15px;
        overflow: hidden;
        position: relative;
        height: 556px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        background: var(--card-bg);
        transition: transform 0.3s ease;
    }

    .rec-card:hover {
        transform: scale(1.02);
    }

    .rec-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .rec-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
        padding: 20px;
        color: white;
        text-align: center;
    }

    /* 7. Special Privileges */
    .privilege-section {
        background: var(--privilege-bg);
    }

    .privilege-card {
        background: var(--card-bg);
        border-radius: 15px;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        transition: transform 0.3s ease;
    }

    .privilege-card:hover {
        transform: translateY(-5px);
    }

    .privilege-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    /* 8. Footer */
    footer {
        background: var(--ease-gradient);
        color: white;
        padding: 40px 0 20px;
        margin-top: 50px;
    }

    .footer-column {
        padding: 0 20px;
    }

    /* 2px divider for Footer */
    .footer-divider {
        border-right: 2px solid rgba(255, 255, 255, 0.3) !important;
    }

    .social-icons {
        display: flex;
        gap: 20px;
        justify-content: center;
    }

    .social-icons a {
        color: white;
        font-size: 1.3rem;
        transition: color 0.3s;
    }

    .social-icons a:hover {
        color: #f1c40f;
    }

    .copyright-text {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.7);
    }

    @media (max-width: 991px) {
        .navbar-top-row {
            display: none;
        }

        .carousel-item img {
            height: 250px;
        }

        .footer-column {
            text-align: center !important;
            margin-bottom: 20px;
        }

        .footer-divider {
            border-right: none !important;
        }

        .copyright-text {
            text-align: center;
            margin-top: 15px;
        }

        .rec-card {
            height: 400px;
        }
    }
</style>
@endpush

@section('content')

    <!-- Slider (Dynamic Banners) -->
    <div class="container mt-4">
        <div id="heroCarousel" class="carousel slide shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel">
            <div class="carousel-inner">
                @if(isset($banners) && $banners->count() > 0)
                    @foreach($banners as $index => $banner)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ $banner->image_url }}" class="d-block w-100" alt="{{ $banner->title ?? 'Banner' }}">
                        </div>
                    @endforeach
                @else
                    <!-- รูป Default หากยังไม่มีข้อมูลแบนเนอร์ -->
                    <div class="carousel-item active">
                        <img src="{{ asset('assets/image/slider.webp') }}" class="d-block w-100" alt="Default Banner">
                    </div>
                @endif
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>

    <!-- Category Grid -->
    <div class="container mt-5">
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="service-card"><img src="{{ asset('assets/image/g1.webp') }}" alt="สินค้าพร้อมติดตั้ง">
                    <div class="service-card-body">
                        <h5 class="fw-bold">สินค้าพร้อมติดตั้ง</h5>
                        <a href="{{ route('products', ['group' => 'equipment']) }}" class="btn btn-service mt-2">ดูเพิ่มเติม</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-card"><img src="{{ asset('assets/image/g2.webp') }}" alt="แพ็กเกจบริการ">
                    <div class="service-card-body">
                        <h5 class="fw-bold">แพ็กเกจบริการ</h5>
                        <a href="{{ route('products', ['group' => 'package']) }}" class="btn btn-service mt-2">ดูเพิ่มเติม</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-card"><img src="{{ asset('assets/image/g3.webp') }}" alt="ประกัน">
                    <div class="service-card-body">
                        <h5 class="fw-bold">ประกัน</h5>
                        <a href="{{ route('insurance') }}" class="btn btn-service mt-2">ดูเพิ่มเติม</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-card"><img src="{{ asset('assets/image/g4.webp') }}" alt="ตู้เซฟนิรภัย">
                    <div class="service-card-body">
                        <h5 class="fw-bold">ตู้เซฟนิรภัย</h5>
                        <a href="{{ route('lockers') }}" class="btn btn-service mt-2">ดูเพิ่มเติม</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Service Categories (Dynamic - ลูปข้อมูลจาก $categories) -->
    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4">หมวดหมู่บริการของเรา</h3>
        <div class="row g-3">
            @if(isset($categories) && $categories->count() > 0)
                @foreach($categories as $category)
                    <div class="col-lg-6">
                        <div class="age-service-box shadow-sm">
                            <img src="{{ $category->image_url ?? asset('assets/image/logo2.webp') }}" class="age-icon" alt="{{ $category->name }}">
                            <div class="flex-grow-1">
                                <h5 class="fw-bold mb-1">{{ $category->name }}</h5>
                                <p class="text-muted small mb-0">{{ $category->description ?? 'รายละเอียดหมวดหมู่' }}</p>
                            </div>
                            <div class="ms-3 text-end border-start ps-3">
                                <a href="{{ route('products', ['group' => $category->group, 'category' => $category->id]) }}" class="btn btn-sm btn-outline-danger mt-2">ดูบริการ</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <!-- ข้อมูลตัวอย่างหากยังไม่มีหมวดหมู่ในฐานข้อมูล -->
                <div class="col-lg-6">
                    <div class="age-service-box shadow-sm">
                        <img src="{{ asset('assets/image/logo2.webp') }}" class="age-icon" alt="Logo">
                        <div class="flex-grow-1">
                            <h5 class="fw-bold mb-1">ยังไม่มีหมวดหมู่บริการ</h5>
                            <p class="text-muted small mb-0">โปรดเพิ่มข้อมูลในระบบหลังบ้าน</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Recommended Services Section -->
    <div class="container mt-5">
        <h3 class="text-center fw-bold mb-4">บริการแนะนำ</h3>
        <div class="row g-4 mt-3">
            @if(isset($recommendedServices) && $recommendedServices->count() > 0)
                @foreach($recommendedServices as $service)
                <div class="col-6 col-md-3">
                    <a href="{{ route('product-detail', $service->id) }}" class="text-decoration-none d-block">
                        <div class="rec-card shadow-sm">
                            <img src="{{ $service->image_url ?? asset('assets/image/img-zo1.webp') }}" alt="{{ $service->name ?? 'Service' }}">
                            <div class="rec-overlay">
                                <h5 class="fw-bold text-white">{{ $service->name ?? 'ไม่มีชื่อ' }}</h5>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach
            @else
                <div class="col-12 text-center text-muted">
                    <p>ยังไม่มีบริการแนะนำในขณะนี้</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Special Privileges Section -->
    <div class="container mt-5 mb-5">
        <div class="privilege-section p-4 rounded-4 shadow">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold mb-0 text-white">สิทธิพิเศษแนะนำ</h2>
                <a href="{{ route('rewards') }}" class="text-white fw-bold text-decoration-none">ดูทั้งหมด <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div class="row g-4">
                @if(isset($recommendedPrivileges) && $recommendedPrivileges->count() > 0)
                    @foreach($recommendedPrivileges as $privilege)
                    <div class="col-md-4">
                        <a href="{{ route('rewards') }}" class="text-decoration-none d-block h-100">
                            <div class="privilege-card shadow-sm">
                                <img src="{{ $privilege->image_url ?? asset('assets/image/rew3.webp') }}" alt="{{ $privilege->title ?? 'Privilege' }}">
                                <div class="service-card-body">
                                    <h5 class="fw-bold text-dark">{{ $privilege->title_th ?? 'ไม่มีชื่อ' }}</h5>
                                    <div class="text-danger fw-bold mt-2">
                                        ใช้ {{ $privilege->points_required ?? 0 }} คะแนน
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                    @endforeach
                @else
                    <div class="col-12 text-center text-white">
                        <p>ยังไม่มีสิทธิพิเศษแนะนำในขณะนี้</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
 