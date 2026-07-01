@extends('frontend.layouts.main')

@section('title', 'แพ็กเกจของฉัน - AEG EASE CLUB')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Kanit:wght@200;300;400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary-dark: #1a1a2e;
            --primary-red: #c41e3a;
            --primary-navy: #1a2d5e;
            --tab-inactive: #a6abbd;
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
        .package-main {
            padding: 40px 0 100px;
        }

        /* Tabs Design (Match Package.jpg) */
        .tab-nav-container {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
            gap: 15px;
        }

        .tab-btn {
            border: none;
            padding: 12px 0;
            width: 250px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            color: white;
            transition: 0.3s;
            text-align: center;
            text-decoration: none;
        }

        .tab-btn.active {
            background-color: var(--primary-navy);
        }

        .tab-btn.inactive {
            background-color: var(--tab-inactive);
        }

        /* Package Card Styling */
        .package-card {
            background: white;
            border-radius: 35px;
            border: 1px solid #f0f0f0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            padding: 30px;
            margin-bottom: 25px;
            max-width: 850px;
            margin-left: auto;
            margin-right: auto;
            display: flex;
            align-items: flex-start;
            gap: 30px;
            position: relative;
        }

        .package-img-box {
            width: 320px;
            height: 200px;
            border-radius: 15px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .package-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .package-info {
            flex-grow: 1;
        }

        .package-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .package-title {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary-navy);
            margin: 0;
        }

        .status-badge {
            font-weight: 700;
            font-size: 0.85rem;
        }

        .status-active {
            color: #28a745;
        }

        .status-expired {
            color: #999;
        }

        .date-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
        }

        .info-item label {
            display: block;
            font-size: 0.75rem;
            color: #888;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .info-item span {
            display: block;
            font-weight: 700;
            font-size: 0.9rem;
            color: #c41e3a;
        }

        .info-item .val-dark {
            color: #333;
        }

        .pts-line {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.8rem;
            color: #555;
            margin-bottom: 25px;
        }

        .points-pill {
            background: #fff5f6;
            color: var(--primary-red);
            border: 1px solid #ffdadd;
            padding: 2px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        /* Buttons Group */
        .card-btn-group {
            display: flex;
            gap: 10px;
        }

        .btn-navy-small {
            background-color: var(--primary-navy);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 8px 30px;
            font-weight: 600;
            font-size: 0.9rem;
            flex: 1;
            text-align: center;
            text-decoration: none;
        }

        .btn-red-full {
            background-color: var(--primary-red);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 0;
            width: 100%;
            font-weight: 700;
            font-size: 1rem;
            margin-top: 10px;
            text-align: center;
            text-decoration: none;
            display: block;
        }

        .btn-navy-small:hover,
        .btn-red-full:hover {
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

        /* --- Tab Toggle Logic --- */
        .tab-pane {
            display: none;
        }

        .tab-pane.show {
            display: block;
        }

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .tab-btn {
                width: 100%;
            }

            .package-card {
                flex-direction: column;
                padding: 20px;
                border-radius: 25px;
            }

            .package-img-box {
                width: 100%;
                height: 200px;
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

    <!-- Main Content -->
    <main class="package-main">
        <div class="container">

            <!-- Tabs Navigation -->
            <div class="tab-nav-container">
                <button type="button" class="tab-btn active" id="activeTabBtn" onclick="switchTab('active')">แพ็กเกจ/สินค้า</button>
                <button type="button" class="tab-btn inactive" id="historyTabBtn" onclick="switchTab('history')">ประวัติ</button>
            </div>

            <!-- Tab Content: Active Packages -->
            <div id="activeContent" class="tab-pane show">
                @if(isset($activeItems) && $activeItems->count() > 0)
                    @foreach($activeItems as $item)
                        <div class="package-card">
                            <div class="package-img-box">
                                <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}">
                            </div>
                            <div class="package-info">
                                <div class="package-header-flex">
                                    <h2 class="package-title">{{ $item->product_name }}</h2>
                                    <span class="status-badge status-active">ใช้งานปกติ</span>
                                </div>

                                <div class="date-info-grid">
                                    <div class="info-item">
                                        <label>หมายเลข Serial Number / Policy</label>
                                        <span class="val-dark">{{ $item->serial_number }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label>วันที่เริ่มเปิดสิทธิ์ดูแล</label>
                                        <span class="val-dark">{{ $item->created_at->format('d M Y') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label>วันที่สิ้นสุดบริการการดูแล</label>
                                        <span>{{ !empty($item->warranty_expire_date) ? \Carbon\Carbon::parse($item->warranty_expire_date)->format('d M Y') : '-' }}</span>
                                    </div>
                                    @if($item->reference_type === 'product' && $item->total_service_count > 0)
                                    <div class="info-item">
                                        <label>โควต้าบริการคงเหลือ</label>
                                        <div class="points-pill" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;">เหลือ {{ $item->remaining_services }} ครั้ง</div>
                                    </div>
                                    @endif
                                </div>

                                <div class="card-btn-group">

                                    @if($item->reference_type === 'product')
                                        @if($item->total_service_count == 0)
                                            <a href="{{ route('repair-request', $item->id) }}" class="btn-navy-small">แจ้งซ่อมสินค้า</a>
                                            <a href="{{ route('repair-status', $item->id) }}" class="btn-navy-small" style="background-color:#4b5563;">รายละเอียด/ประวัติ</a>
                                        @else
                                            <a href="{{ route('repair-request', $item->id) }}" class="btn-navy-small">แจ้งซ่อมสินค้า</a>
                                            <a href="{{ route('repair-status', $item->id) }}" class="btn-navy-small" style="background-color:#4b5563;">รายละเอียดประวัติบริการ</a>
                                        @endif
                                    @else
                                        <a href="{{ route('repair-status', $item->id) }}" class="btn-navy-small" style="background-color: var(--tab-inactive); width: 100%; flex: none;">ดูรายละเอียดสิทธิ์</a>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5 text-muted">ยังไม่มีรายการสินค้าหรือบริการที่กำลังใช้งาน</div>
                @endif
            </div>

            <!-- Tab Content: History -->
            <div id="historyContent" class="tab-pane">
                @if(isset($historyItems) && $historyItems->count() > 0)
                    @foreach($historyItems as $item)
                        <div class="package-card">
                            <div class="package-img-box">
                                <img src="{{ $item->product ? $item->product->image_url : 'https://via.placeholder.com/600' }}" alt="{{ $item->product_name }}">
                            </div>
                            <div class="package-info">
                                <div class="package-header-flex">
                                    <h2 class="package-title">{{ $item->product_name }}</h2>
                                    <span class="status-badge status-expired">หมดอายุ/เสร็จสิ้น</span>
                                </div>
                                <div class="date-info-grid">
                                    <div class="info-item">
                                        <label>วันที่สั่งซื้อ</label>
                                        <span>{{ $item->created_at->format('d M Y') }}</span>
                                    </div>
                                </div>
                                <div class="card-btn-group">
                                    <a href="#" class="btn-navy-small" style="width: 100%;">รายละเอียด</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-5 text-muted">ไม่มีประวัติการใช้งานแพ็กเกจ</div>
                @endif
            </div>

        </div>
    </main>

    <!-- Footer Section -->
@endsection

@push('scripts')
    <script>
        function switchTab(type) {
            const activeBtn = document.getElementById('activeTabBtn');
            const historyBtn = document.getElementById('historyTabBtn');
            const activeContent = document.getElementById('activeContent');
            const historyContent = document.getElementById('historyContent');

            if (type === 'active') {
                activeBtn.classList.remove('inactive');
                activeBtn.classList.add('active');
                historyBtn.classList.remove('active');
                historyBtn.classList.add('inactive');

                activeContent.classList.add('show');
                historyContent.classList.remove('show');
            } else {
                historyBtn.classList.remove('inactive');
                historyBtn.classList.add('active');
                activeBtn.classList.remove('active');
                activeBtn.classList.add('inactive');

                historyContent.classList.add('show');
                activeContent.classList.remove('show');
            }
        }
    </script>
@endpush
