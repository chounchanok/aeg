@extends('frontend.layouts.main')

@section('title', 'หน้าหลัก - AEG EASE CLUB')

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
                        <a href="{{ route('packages', ['type' => 'equipment']) }}" class="btn btn-service mt-2">ดูเพิ่มเติม</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="service-card"><img src="{{ asset('assets/image/g2.webp') }}" alt="แพ็กเกจบริการ">
                    <div class="service-card-body">
                        <h5 class="fw-bold">แพ็กเกจบริการ</h5>
                        <a href="{{ route('packages', ['type' => 'service']) }}" class="btn btn-service mt-2">ดูเพิ่มเติม</a>
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
                                <a href="{{ route('services', ['category' => $category->id]) }}" class="btn btn-sm btn-outline-danger mt-2">ดูบริการ</a>
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
                                    <h5 class="fw-bold text-dark">{{ $privilege->title ?? 'ไม่มีชื่อ' }}</h5>
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
