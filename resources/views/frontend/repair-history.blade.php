@extends('frontend.layouts.main')

@section('title', 'ประวัติการแจ้งซ่อม - AEG EASE CLUB')

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
            --primary-navy: #1a2d5e;
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

        /* Container strictly 950px for content */
        .container-950 {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 15px;
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
        .repair-detail-wrapper {
            padding: 60px 0 100px;
        }

        .repair-card {
            background: white;
            border-radius: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 50px;
            border: 1px solid #eee;
        }

        /* Top Area: Image & Title */
        .repair-hero {
            display: flex;
            gap: 40px;
            margin-bottom: 35px;
        }

        .repair-img-box {
            width: 320px;
            height: 220px;
            border-radius: 18px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .repair-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .repair-title-block {
            flex-grow: 1;
        }

        .repair-main-title {
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--primary-navy);
            margin-bottom: 20px;
            line-height: 1.3;
        }

        .repair-badge {
            background-color: var(--primary-navy);
            color: white;
            display: inline-block;
            padding: 8px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 25px;
        }

        .repair-dates-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .date-label {
            display: block;
            font-size: 0.85rem;
            color: #888;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .date-value {
            font-weight: 700;
            color: var(--primary-red);
            font-size: 1.1rem;
        }

        /* Info Blocks */
        .info-section {
            margin-bottom: 25px;
        }

        .section-label {
            display: block;
            font-weight: 700;
            font-size: 1.2rem;
            color: #000;
            margin-bottom: 8px;
        }

        .info-text-dark {
            font-weight: 500;
            color: #333;
            font-size: 1.05rem;
        }

        .remaining-count {
            font-weight: 700;
            font-size: 1.1rem;
            color: #333;
        }

        /* Scope List (Red Dots) */
        .scope-list-custom {
            list-style: none;
            padding-left: 0;
            margin-bottom: 50px;
        }

        .scope-list-custom li {
            position: relative;
            padding-left: 20px;
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 8px;
            line-height: 1.6;
        }

        .scope-list-custom li::before {
            content: "•";
            position: absolute;
            left: 0;
            color: var(--primary-red);
            font-weight: 900;
            font-size: 1.2rem;
        }

        /* Footer Action */
        .btn-footer-right {
            text-align: right;
        }

        .btn-navy-back {
            background-color: var(--primary-navy);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 40px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
            transition: 0.3s;
        }

        .btn-navy-back:hover {
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

        @media (max-width: 992px) {
            .navbar-top-row {
                display: none;
            }

            .repair-hero {
                flex-direction: column;
                gap: 30px;
            }

            .repair-img-box {
                width: 100%;
                height: 200px;
            }

            .repair-card {
                padding: 30px 20px;
            }

            .repair-dates-grid {
                gap: 15px;
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

    <!-- Main Repair Details Content -->
    <main class="repair-detail-wrapper">
        <div class="container-950">
            <h2 class="fw-bold mb-4" style="color: var(--primary-navy);">ประวัติการแจ้งซ่อมของคุณ</h2>

            @if(isset($requests) && $requests->count() > 0)
                @foreach($requests as $req)
                <div class="repair-card mb-4" style="padding: 30px;">
                    <div class="repair-hero mb-0">
                        <div class="repair-img-box" style="width: 200px; height: 140px;">
                            <img src="{{ $req->image_url ?? 'https://via.placeholder.com/400' }}" alt="Package Image">
                        </div>
                        <div class="repair-title-block">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="fw-bold text-dark mb-1">{{ $req->product_name }}</h4>
                                    <p class="text-muted small mb-2">Ticket: {{ $req->ticket_number }}</p>
                                </div>
                                <div>
                                    @if($req->status == 'completed')
                                        <span class="badge bg-success px-3 py-2">เสร็จสิ้น</span>
                                    @elseif($req->status == 'cancelled')
                                        <span class="badge bg-secondary px-3 py-2">ยกเลิก</span>
                                    @else
                                        <span class="badge bg-warning text-dark px-3 py-2">กำลังดำเนินการ</span>
                                    @endif
                                </div>
                            </div>

                            <p class="mb-2 text-truncate" style="max-width: 400px; font-size: 0.9rem;">
                                <strong>ปัญหา:</strong> {{ $req->problem_description }}
                            </p>

                            <div class="d-flex justify-content-between align-items-end mt-3">
                                <span class="text-muted" style="font-size: 0.8rem;">
                                    วันที่แจ้ง: {{ \Carbon\Carbon::parse($req->created_at)->format('d M Y') }}
                                </span>
                                <a href="{{ route('repair-status', $req->id) }}" class="btn-navy-back" style="padding: 8px 25px;">ดูสถานะ</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <p class="text-muted">คุณยังไม่มีประวัติการแจ้งซ่อม</p>
                </div>
            @endif
        </div>
    </main>
@endsection
