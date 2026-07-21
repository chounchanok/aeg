@extends('frontend.layouts.main')

@section('title', 'แพ็กเกจดูแลอุปกรณ์ - AEG EASE CLUB')

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
        }

        body {
            font-family: 'Poppins', 'Kanit', sans-serif !important;
            background-color: #ffffff;
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

        /* --- Main Content Section --- */
        .service-main {
            padding: 60px 0 100px;
            min-height: 600px;
        }

        .page-title {
            color: var(--primary-dark);
            font-weight: 700;
            font-size: 1.6rem;
            text-align: center;
            margin-bottom: 40px;
        }

        /* ปรับความกว้างให้เท่ากับส่วนรายละเอียด (900px) */
        .selection-group {
            max-width: 900px;
            margin: 0 auto 50px;
            padding: 0 15px;
        }

        .selection-label {
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 15px;
            font-size: 1.1rem;
            display: block;
        }

        .custom-select {
            width: 100%;
            padding: 12px 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            font-size: 0.95rem;
            color: #888;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 448 512'%3E%3Cpath fill='%23666' d='M201.4 342.6c12.5 12.5 32.8 12.5 45.3 0l160-160c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L224 274.7 86.6 137.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l160 160z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            background-size: 15px;
            cursor: pointer;
        }

        /* --- Dynamic Details Section --- */
        #serviceDetails {
            display: none;
            /* Hidden until selected */
            max-width: 900px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .detail-image-box {
            background: #fff;
            border: 1px solid #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
            width: 100%;
            aspect-ratio: 1.5/1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .detail-image-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-info-text {
            padding-left: 30px;
        }

        .info-header {
            font-weight: 700;
            color: var(--primary-red);
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        .info-list {
            list-style: none;
            padding-left: 0;
            font-size: 0.9rem;
            line-height: 1.6;
            color: #333;
            margin-bottom: 25px;
        }

        .price-section {
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .price-info {
            font-size: 0.9rem;
            color: #333;
            margin-bottom: 5px;
        }

        .price-warning {
            font-size: 0.8rem;
            color: var(--primary-red);
            line-height: 1.4;
            margin-bottom: 5px;
        }

        /* --- Action Buttons --- */
        .btn-action-group {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 60px;
        }

        .btn-custom {
            background: var(--btn-gradient);
            color: white !important;
            border: none;
            border-radius: 50px;
            padding: 10px 40px;
            font-size: 1.1rem;
            font-weight: 600;
            min-width: 220px;
            text-decoration: none;
            text-align: center;
            box-shadow: 0 4px 15px rgba(196, 30, 58, 0.2);
            transition: 0.3s;
        }

        .btn-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(196, 30, 58, 0.3);
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

            .detail-info-text {
                padding-left: 0;
                margin-top: 30px;
            }

            .btn-action-group {
                flex-direction: column;
                align-items: center;
                gap: 15px;
            }

            .btn-custom {
                width: 100%;
                max-width: 300px;
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
    <main class="service-main">
        <div class="container">
            <h1 class="page-title">แพ็กเกจดูแลอุปกรณ์</h1>

            <!-- Selection Dropdown (Width matched to 900px) -->
            <div class="selection-group">
                <label class="selection-label">ประเภทอุปกรณ์</label>
                <select class="custom-select" id="serviceSelector" onchange="updateServiceInfo()">
                    <option value="" selected>- เลือกบริการ -</option>
                    @if(!empty($products))
                        @foreach($products as $product)
                            <option value="service-{{ $product->id }}">{{ $product->name_th }}</option>
                        @endforeach
                    @endif
                </select>
            </div>

            @if(!empty($products))
                <form action="{{ route('cart.add') }}" method="post" id="addToCartForm">
                    @csrf
                    @foreach($products as $product)
                        <div class="service-detail-block" style="display: none;" data-service="service-{{ $product->id }}">
                            <div class="row align-items-center">
                                <div class="col-lg-6">
                                    <div class="detail-image-box">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name_th }}"
                                        onerror="this.src='https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=600&q=80'">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="detail-info-text">
                                        <h2 class="info-header">รายละเอียด</h2>
                                        <ul class="info-list">
                                            @foreach(explode("\n", $product->description_th) as $point)
                                                <li>{{ trim($point) }}</li>
                                            @endforeach
                                        </ul>
                                        <div class="price-section">
                                            <p class="price-info">ดูแลรักษาเบื้องต้น 1 จุด</p>
                                            <p class="price-info">ราคาเริ่มต้นรวมจุดละ <span class="fw-bold text-danger">{{ number_format($product->price) }} บาท / เดือน</span></p>
                                            <p class="price-warning">หมายเหตุ : ราคาอุปกรณ์จัดส่งฟรี ยกเว้นในกรณีที่มีการเปลี่ยน เช่น เซนเซอร์, แบตเตอรี่, กล้องวงจรปิด ฯลฯ</p>
                                            <p class="price-warning">* บริการนี้ไม่ครอบคลุมรายการอะไหล่อุปกรณ์และวัสดุสิ้นเปลืองที่เป็นตัวเติม</p>
                                        </div>

                                        <!-- 🌟 ส่วนที่เพิ่มใหม่: เลือกจำนวนและระยะเวลา -->
                                        <div class="row mt-4">
                                            <div class="col-sm-6 mb-3">
                                                <label class="form-label fw-bold" style="color: var(--primary-dark); font-size: 0.9rem;">จำนวน (จุด/เครื่อง)</label>
                                                <input type="number" name="quantity" class="form-control form-control-custom quantity-input" value="1" min="1" disabled style="margin-bottom: 0;">
                                            </div>
                                            <div class="col-sm-6 mb-3">
                                                <label class="form-label fw-bold" style="color: var(--primary-dark); font-size: 0.9rem;">ระยะเวลารับบริการ</label>
                                                <select name="duration_months" class="form-select form-control-custom duration-input" disabled style="margin-bottom: 0;">
                                                    <option value="1">1 เดือน</option>
                                                    <option value="3">3 เดือน</option>
                                                    <option value="6">6 เดือน</option>
                                                    <option value="12">1 ปี (12 เดือน)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <!-- 🌟 สิ้นสุดส่วนที่เพิ่มใหม่ -->

                                    </div>
                                </div>
                            </div>

                            <div class="btn-action-group">
                                <input type="hidden" name="product_id" value="{{ $product->id }}" class="product-id-input" disabled>
                                <!-- 🌟 ลบ <input type="hidden" name="quantity" value="1"> ตัวเก่าทิ้งไปเลยครับ เพราะเราใช้ตัวใหม่ข้างบนแล้ว -->
                                <button type="button" onclick="history.back();" class="btn-custom border-0">ย้อนกลับ</button>
                                <button type="submit" class="btn-custom border-0">เพิ่มลงตะกร้า</button>
                            </div>
                        </div>
                    @endforeach
                </form>
            @endif
        </div>
    </main>

@endsection
@push('scripts')
    <script>
        function updateServiceInfo() {
            const selectedService = document.getElementById('serviceSelector').value;
            const serviceBlocks = document.querySelectorAll('.service-detail-block');

            serviceBlocks.forEach(function(block) {
                const serviceId = block.getAttribute('data-service');
                const productInput = block.querySelector('.product-id-input');
                const quantityInput = block.querySelector('.quantity-input'); // 🌟 ดึงช่องจำนวน
                const durationInput = block.querySelector('.duration-input'); // 🌟 ดึงช่องระยะเวลา

                if (serviceId === selectedService && selectedService !== "") {
                    block.style.display = 'block';
                    if (productInput) productInput.disabled = false;
                    if (quantityInput) quantityInput.disabled = false; // 🌟 เปิดให้แก้ไขได้
                    if (durationInput) durationInput.disabled = false; // 🌟 เปิดให้แก้ไขได้
                } else {
                    block.style.display = 'none';
                    if (productInput) productInput.disabled = true;
                    if (quantityInput) quantityInput.disabled = true; // 🌟 ปิดไว้กันส่งค่าซ้ำซ้อน
                    if (durationInput) durationInput.disabled = true; // 🌟 ปิดไว้
                }
            });
        }
    </script>
@endpush