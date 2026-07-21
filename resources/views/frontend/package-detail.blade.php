@extends('frontend.layouts.main')

@section('title', 'รายละเอียดแพ็กเกจ - AEG EASE CLUB')

@push('styles')
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Kanit:wght@200;300;400;500&display=swap" rel="stylesheet">
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

        .container-950 {
            max-width: 950px;
            margin: 0 auto;
            padding: 0 15px;
        }

        .detail-main-wrapper {
            padding: 60px 0 100px;
        }

        .detail-card {
            background: white;
            border-radius: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 50px;
            border: 1px solid #eee;
        }

        .detail-top-hero {
            display: flex;
            gap: 40px;
            margin-bottom: 45px;
            padding-bottom: 30px;
            border-bottom: 1px solid #f2f2f2;
        }

        .detail-img-box {
            width: 320px;
            height: 220px;
            border-radius: 18px;
            overflow: hidden;
            flex-shrink: 0;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .detail-img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-info-side {
            flex-grow: 1;
        }

        .detail-header-flex {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }

        .detail-main-title {
            font-weight: 700;
            font-size: 1.6rem;
            color: var(--primary-navy);
            margin: 0;
        }

        .detail-status-tag {
            font-weight: 700;
            font-size: 0.85rem;
            color: #28a745;
        }

        .detail-status-tag.expired {
            color: #999;
        }

        .info-label {
            display: block;
            font-size: 0.8rem;
            color: #888;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .info-value-navy {
            font-weight: 700;
            color: var(--primary-navy);
            font-size: 1rem;
            margin-bottom: 15px;
        }

        .date-columns {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .date-val-red {
            color: var(--primary-red);
            font-weight: 700;
        }

        .section-block {
            margin-bottom: 30px;
        }

        .section-label-bold {
            display: block;
            font-weight: 700;
            font-size: 1.15rem;
            color: #000;
            margin-bottom: 12px;
        }

        .remaining-text {
            font-weight: 600;
            color: #333;
            font-size: 1.1rem;
        }

        .repair-history-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 10px;
        }

        .btn-history-pill {
            background-color: var(--primary-navy);
            color: white !important;
            border-radius: 10px;
            padding: 8px 30px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
            cursor: pointer;
            border: none;
        }

        .btn-history-pill:hover {
            opacity: 0.9;
        }

        .service-scope-content {
            font-size: 0.95rem;
            color: #555;
            margin-bottom: 50px;
            line-height: 1.8;
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }

        .detail-footer {
            text-align: right;
            margin-top: 30px;
            border-top: 1px solid #f2f2f2;
            padding-top: 25px;
        }

        .btn-back-detail {
            background-color: var(--primary-navy);
            color: white !important;
            border-radius: 8px;
            padding: 10px 40px;
            font-weight: 600;
            display: inline-block;
            text-decoration: none;
        }

        /* Styles สำหรับ Timeline ฝั่งดีไซน์ช่าง */
        .tech-timeline {
            list-style: none;
            padding: 0;
            position: relative;
        }
        .tech-timeline::before {
            content: '';
            position: absolute;
            top: 0; bottom: 0; left: 9px;
            width: 2px; background: #e2e8f0;
        }
        .tech-timeline li {
            position: relative;
            padding-left: 30px;
            margin-bottom: 15px;
            font-size: 0.85rem;
        }
        .tech-timeline li::before {
            content: '';
            position: absolute;
            left: 3px; top: 4px;
            width: 14px; height: 14px;
            border-radius: 50%; background: #c41e3a;
            border: 2px solid #fff;
        }

        @media (max-width: 991px) {
            .detail-top-hero {
                flex-direction: column;
                gap: 25px;
                padding-bottom: 20px;
            }
            .detail-img-box {
                width: 100%;
                height: 200px;
            }
            .detail-card {
                padding: 30px 20px;
                border-radius: 30px;
            }
        }
    </style>
@endpush

@section('content')

    <main class="detail-main-wrapper">
        <div class="container-950">
            <div class="detail-card">

                <div class="detail-top-hero">
                    <div class="detail-img-box">
                        <img src="{{ $package->image_url ?? asset('assets/image/logo2.webp') }}" alt="{{ $package->product_name }}">
                    </div>
                    <div class="detail-info-side">
                        <div class="detail-header-flex">
                            <h1 class="detail-main-title">{{ $package->product_name }}</h1>

                            @php
                                $isExpired = !empty($package->warranty_expire_date) && \Carbon\Carbon::parse($package->warranty_expire_date)->isPast();
                            @endphp

                            <span class="detail-status-tag {{ ($package->status !== 'active' || $isExpired) ? 'expired' : '' }}">
                                {{ ($package->status === 'active' && !$isExpired) ? 'ใช้งานปกติ' : 'หมดอายุ / ไม่ได้ใช้งาน' }}
                            </span>
                        </div>

                        <label class="info-label">หมายเลข Serial Number / Policy :</label>
                        <div class="info-value-navy">{{ $package->serial_number ?? 'ไม่ระบุ' }}</div>

                        <label class="info-label">ระยะเวลาการคุ้มครอง / การดูแล :</label>
                        <div class="date-columns">
                            <div>
                                <label class="info-label">เริ่มต้น</label>
                                <span class="date-val-red">{{ \Carbon\Carbon::parse($package->created_at)->translatedFormat('d M Y') }}</span>
                            </div>
                            <div>
                                <label class="info-label">สิ้นสุด</label>
                                <span class="date-val-red">{{ !empty($package->warranty_expire_date) ? \Carbon\Carbon::parse($package->warranty_expire_date)->translatedFormat('d M Y') : 'ไม่ระบุวันสิ้นสุด' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-body-area">

                    @if($package->reference_type === 'product' && $package->total_service_count > 0)
                    <div class="section-block">
                        <span class="section-label-bold">จำนวนบริการคงเหลือ :</span>
                        <p class="remaining-text">{{ max(0, $package->total_service_count - $package->used_service_count) }} ครั้ง (จากทั้งหมด {{ $package->total_service_count }} ครั้ง)</p>
                    </div>
                    @endif

                    <div class="section-block">
                        <span class="section-label-bold">ประวัติการแจ้งซ่อม / เรียกใช้บริการ :</span>
                        @if($repairs->count() > 0)
                            <div class="repair-history-btns">
                                @foreach($repairs as $index => $repair)
                                    <button type="button" class="btn-history-pill" onclick="openCompletionModal({{ $repair->id }})">
                                        ครั้งที่ {{ $repairs->count() - $index }}
                                        <small style="opacity: 0.8; margin-left: 5px;">({{ \Carbon\Carbon::parse($repair->created_at)->format('d/m/Y') }})</small>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">ยังไม่มีประวัติการแจ้งซ่อม</p>
                        @endif
                    </div>

                    <div class="section-block">
                        <span class="section-label-bold">รายละเอียด / ขอบเขตการบริการ :</span>
                        <div class="service-scope-content">
                            {!! nl2br(e($description)) !!}
                            @if(!empty($coverage))
                                <hr class="my-3">
                                <strong>¼ข้อมูลความคุ้มครองเพิ่มเติม:</strong><br>
                                {!! nl2br(e($coverage)) !!}
                            @endif
                        </div>
                    </div>

                    <div class="detail-footer">
                        <a href="{{ route('packages.mine') }}" class="btn-back-detail"><i class="fas fa-arrow-left me-2"></i> ย้อนกลับ</a>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <div class="modal fade" id="completionModal" static="true" tabindex="-1" aria-hidden="true" style="z-index: 99999;">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border-radius: 20px; overflow: hidden; border: none;">
                <div class="modal-header text-white" style="background-color: var(--primary-navy);">
                    <h5 class="modal-title fw-bold" id="modalTitle">รายละเอียดรายงานการซ่อม</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4" style="background-color: #f8fafc;">
                    <div id="modalLoading" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">กำลังดึงข้อมูลรายงานการซ่อม...</p>
                    </div>

                    <div id="modalContent" class="d-none">
                        <div class="row g-4 mb-4">
                            <div class="col-md-5">
                                <h6 class="fw-bold text-dark mb-3"><i class="fas fa-history me-2 text-danger"></i>บันทึกเวลาทำงาน (Timeline)</h6>
                                <ul class="tech-timeline" id="logTimeline">
                                    </ul>
                            </div>
                            <div class="col-md-7">
                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-comment-alt me-2 text-primary"></i>หมายเหตุการแก้ไขจากช่าง</h6>
                                <div class="p-3 bg-white border rounded mb-3" id="techNote" style="font-size: 0.9rem; min-height: 80px;"></div>

                                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-signature me-2 text-success"></i>ลายเซ็นยืนยันจากลูกค้า</h6>
                                <div class="text-center bg-white border rounded p-2" style="max-width: 250px;">
                                    <img id="custSignature" src="" alt="Customer Signature" style="max-height: 80px; width: auto;">
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark mb-2 border-t pt-3"><i class="fas fa-images me-2 text-warning"></i>รูปภาพ/หลักฐานประกอบจากหน้างาน</h6>
                        <div class="row mt-2">
                            <div class="col-6">
                                <span class="d-block small fw-bold text-muted mb-2">📸 ภาพก่อนการซ่อม (Before)</span>
                                <div class="d-flex flex-wrap gap-2" id="galleryBefore"></div>
                            </div>
                            <div class="col-6">
                                <span class="d-block small fw-bold text-muted mb-2">✅ ภาพหลังการซ่อม (After)</span>
                                <div class="d-flex flex-wrap gap-2" id="galleryAfter"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // ฟังก์ชัน AJAX เรียกเปิดข้อมูล Modal รายงานการซ่อม
        function openCompletionModal(repairId) {
            // 1. สั่งเปิด Modal โครงขึ้นมา และเซ็ตโหมดโหลดข้อมูล
            const modalEl = document.getElementById('completionModal');
            const bsModal = new bootstrap.Modal(modalEl);

            document.getElementById('modalLoading').classList.remove('d-none');
            document.getElementById('modalContent').classList.add('d-none');
            bsModal.show();

            // 2. ดึงค่าผ่าน Fetch API
            fetch(`/my-packages/repair-completion/${repairId}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    const data = res.data;

                    // เซ็ตชื่อหัวข้อบิล
                    document.getElementById('modalTitle').innerText = `รายละเอียดรายงานของใบงาน: ${data.ticket_number}`;

                    // เซ็ตหมายเหตุช่าง และลายเซ็น
                    document.getElementById('techNote').innerText = data.technician_note;
                    document.getElementById('custSignature').src = data.customer_signature ? data.customer_signature : 'https://via.placeholder.com/150?text=No+Signature';

                    // วาดชุดประวัติเวลา (Timeline)
                    let timelineHtml = `
                        <li><span class="text-muted">จ่ายงาน:</span> <strong class="text-dark">${data.timestamps.assigned}</strong></li>
                        <li><span class="text-muted">รับงาน:</span> <strong class="text-dark">${data.timestamps.accepted}</strong></li>
                        <li><span class="text-muted">เริ่มเดินทาง:</span> <strong class="text-dark">${data.timestamps.traveling}</strong></li>
                        <li><span class="text-muted">ถึงหน้างาน:</span> <strong class="text-dark">${data.timestamps.arrived}</strong></li>
                        <li><span class="text-muted">เริ่มลงมือซ่อม:</span> <strong class="text-dark">${data.timestamps.started}</strong></li>
                        <li><span class="text-muted">ส่งมอบงาน:</span> <strong class="text-success">${data.timestamps.completed}</strong></li>
                    `;
                    document.getElementById('logTimeline').innerHTML = timelineHtml;

                    // วาดรูปแกลเลอรี่ก่อนซ่อม
                    let beforeHtml = '';
                    if(data.before_media.length > 0) {
                        data.before_media.forEach(url => {
                            beforeHtml += `<a href="${url}" target="_blank" class="d-block border rounded overflow-hidden" style="width:75px; height:75px;"><img src="${url}" style="width:100%; height:100%; object-fit:cover;"></a>`;
                        });
                    } else { beforeHtml = '<span class="text-muted small">ไม่มีรูปภาพ</span>'; }
                    document.getElementById('galleryBefore').innerHTML = beforeHtml;

                    // วาดรูปแกลเลอรี่หลังซ่อม
                    let afterHtml = '';
                    if(data.after_media.length > 0) {
                        data.after_media.forEach(url => {
                            afterHtml += `<a href="${url}" target="_blank" class="d-block border rounded overflow-hidden" style="width:75px; height:75px;"><img src="${url}" style="width:100%; height:100%; object-fit:cover;"></a>`;
                        });
                    } else { afterHtml = '<span class="text-muted small">ไม่มีรูปภาพ</span>'; }
                    document.getElementById('galleryAfter').innerHTML = afterHtml;

                    // ปิดโหมดโหลด โชว์คอนเทนต์จริง
                    document.getElementById('modalLoading').classAdd = 'd-none'; // ซ่อนโหลด
                    document.getElementById('modalLoading').classList.add('d-none');
                    document.getElementById('modalContent').classList.remove('d-none');
                } else {
                    alert('ไม่สามารถดึงข้อมูลรายงานได้');
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อระบบหลังบ้าน');
            });
        }
    </script>
@endpush
