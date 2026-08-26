@extends('frontend.layouts.main') @section('title', 'บัญชีของฉัน - AEG EASE CLUB') @section('styles')

@endsection

@section('content')

    <!-- Sub Header Bar -->
    <div class="breadcrumb-bar">
        <div class="container">
            <span>บัญชีของฉัน</span>
        </div>
    </div>

    <!-- Main Content Body -->
    <main class="main-content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10">

                    <!-- แจ้งเตือนเมื่ออัปเดตข้อมูลสำเร็จหรือผิดพลาด -->
                    @if(session('success'))
                        <div class="alert alert-success mt-3">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Profile Card -->
                    <div class="card-custom mt-3">
                        <div class="row align-items-center">
                            <div class="col-lg-5 mb-4 mb-lg-0">
                                
                                <!-- 🌟 เขียน PHP เช็คเงื่อนไข Level เพื่อดึงรูปภาพ -->
                                @php
                                    $memberLevel = strtolower($profile->level ?? 'standard');
                                    $bgImage = '';

                                    if ($memberLevel == 'advance') {
                                        $bgImage = asset('assets/image/card-advance.webp');
                                    } elseif ($memberLevel == 'platinum') {
                                        $bgImage = asset('assets/image/card-platinum.webp');
                                    } elseif ($memberLevel == 'beyond') {
                                        $bgImage = asset('assets/image/card-beyond.webp');
                                    } else {
                                        $bgImage = 'none'; // ถ้าเป็น Standard ให้ใช้สี Gradient เดิม
                                    }
                                @endphp

                                <!-- 🌟 ใส่ภาพ Background ลงไปใน Style -->
                                <div class="member-card-visual" style="{{ $bgImage !== 'none' ? "background-image: url('{$bgImage}'); background-size: cover; background-position: center;" : '' }}">
                                    
                                    <!-- 🌟 ถ้าไม่มีรูป (เป็น Standard) ถึงจะโชว์ตัวหนังสือ HTML -->
                                    @if($bgImage === 'none')
                                        <div class="d-flex justify-content-between">
                                            <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                                <span style="color:#0a1931; font-weight: 800; font-size: 8px;">AEG</span>
                                            </div>
                                            <div class="card-label-top">{{ $profile->level ?? 'Standard Member' }}</div>
                                        </div>
                                        <div class="card-brand-main">
                                            <h2>EASE</h2>
                                            <span>CLUB</span>
                                        </div>
                                        <div class="card-footer-label">MEMBERSHIP</div>
                                        <div class="position-absolute bottom-0 end-0 p-3">
                                            <svg width="40" height="40" viewBox="0 0 40 40" style="opacity: 0.3;">
                                                <rect width="40" height="40" rx="5" fill="white" />
                                            </svg>
                                        </div>
                                    @endif
                                    
                                </div>
                            </div>
                            
                            <!-- โค้ดข้อมูลส่วนตัวฝั่งขวา (เหมือนเดิม) -->
                            <div class="col-lg-7">
                                <h3 class="section-title">ข้อมูลส่วนตัว</h3>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="info-label">ชื่อ</div>
                                        <div class="info-value">คุณ {{ $profile->first_name }} {{ $profile->last_name }}</div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="info-label">เบอร์โทรศัพท์มือถือ</div>
                                        <div class="info-value">{{ $profile->phone ?? '-' }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">อีเมล</div>
                                        <div class="info-value">{{ $user->email }}</div>
                                    </div>
                                    <div class="col-6">
                                        <div class="info-label">บริษัท</div>
                                        <div class="info-value">{{ $profile->company ?? '-' }}</div>
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#profileModal">แก้ไขข้อมูลส่วนตัว</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ข้อมูลเพิ่มเติม -->
                    <div class="card-custom mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="section-title">ข้อมูลเพิ่มเติม</h3>
                            <span id="saveIndicator" class="badge bg-success" style="opacity: 0; transition: opacity 0.5s;">
                                <i class="fas fa-check-circle me-1"></i> บันทึกแล้ว
                            </span>
                        </div>
                        
                        <div class="row mb-3">
                            <!-- 1. กล่องเลือกประเภทธุรกิจ -->
                            <div class="col-12 mb-3">
                                <label for="company_type" class="fw-bold mb-1">ประเภทธุรกิจ</label>
                                <select class="form-select" id="company_type" name="company_type">
                                    <option value="">-- เลือกประเภทธุรกิจ --</option>
                                    <option value="ร้านทอง" {{ ($profile->company_type ?? '') == 'ร้านทอง' ? 'selected' : '' }}>ร้านทอง</option>
                                    <option value="ร้านเพชรพลอยและอัญมณี" {{ ($profile->company_type ?? '') == 'ร้านเพชรพลอยและอัญมณี' ? 'selected' : '' }}>ร้านเพชรพลอยและอัญมณี</option>
                                    <option value="โรงรับจำนำ" {{ ($profile->company_type ?? '') == 'โรงรับจำนำ' ? 'selected' : '' }}>โรงรับจำนำ</option>
                                    <option value="โรงงาน/คลังสินค้า" {{ ($profile->company_type ?? '') == 'โรงงาน/คลังสินค้า' ? 'selected' : '' }}>โรงงาน/คลังสินค้า</option>
                                    <option value="สำนักงาน" {{ ($profile->company_type ?? '') == 'สำนักงาน' ? 'selected' : '' }}>สำนักงาน</option>
                                    <option value="อาคารและสิ่งปลูกสร้าง" {{ ($profile->company_type ?? '') == 'อาคารและสิ่งปลูกสร้าง' ? 'selected' : '' }}>อาคารและสิ่งปลูกสร้าง</option>
                                    <option value="บ้าน" {{ ($profile->company_type ?? '') == 'บ้าน' ? 'selected' : '' }}>บ้าน</option>
                                    <option value="อื่นๆ" {{ ($profile->company_type ?? '') == 'อื่นๆ' ? 'selected' : '' }}>อื่น ๆ</option>
                                </select>
                            </div>

                            <!-- 2. กล่องเลือกบริการที่สนใจ (แบบ Dropdown Checkbox) -->
                            <div class="col-12">
                                <label class="fw-bold mb-1">บริการที่สนใจ</label>
                                
                                @php
                                    $rawJson = $profile->service_interesting ?? '[]';
                                    $savedServices = json_decode($rawJson, true);
                                    if (!is_array($savedServices)) $savedServices = [];

                                    $allServices = [
                                        'ระบบสัญญาณกันขโมย', 'ระบบควบคุมการเข้า-ออก', 'ระบบสัญญาณเตือนอัคคีภัย', 
                                        'ระบบกล้องวงจรปิด', 'ประกันภัยอัญมณี ทองและทรัพย์สินมูลค่าสูง', 
                                        'ประกันวินาศภัยสิ่งปลูกสร้าง', 'ประกันวินาศภัยเพื่อการขนส่งสินค้ามูลค่าสูง', 
                                        'AEG Gold Cap-Lock', 'ตู้นิรภัยให้เช่า', 'ขนส่งสินค้ามูลค่าสูง'
                                    ];
                                @endphp

                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary w-100 text-start d-flex justify-content-between align-items-center" type="button" id="serviceDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="background: white;">
                                        <span id="serviceBtnText" class="text-truncate">-- เลือกบริการที่สนใจ --</span>
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <ul class="dropdown-menu w-100 p-2 shadow-sm" aria-labelledby="serviceDropdownBtn" style="max-height: 250px; overflow-y: auto;">
                                        @foreach($allServices as $index => $serviceName)
                                        <li>
                                            <!-- 🌟 เปลี่ยนมาใช้ d-flex align-items-center เพื่อบังคับให้อยู่บรรทัดเดียวกัน -->
                                            <div class="p-2 rounded d-flex align-items-center hover-bg-light" style="cursor: pointer;">
                                                
                                                <!-- 🌟 เอา w-100 ออก แล้วใส่ flex-grow-1 เพื่อให้ข้อความใช้พื้นที่ที่เหลือพอดี -->
                                                <label class="form-check-label ms-2 mb-0 flex-grow-1" for="chkService{{ $index }}" style="cursor: pointer; user-select: none;">
                                                    {{ $serviceName }}
                                                </label>
                                                
                                                <!-- 🌟 เพิ่ม flex-shrink-0 ป้องกันไม่ให้ checkbox หดตัวเบี้ยว -->
                                                <input class="form-check-input service-checkbox m-0 flex-shrink-0" type="checkbox" value="{{ $serviceName }}" id="chkService{{ $index }}" {{ in_array($serviceName, $savedServices) ? 'checked' : '' }} style="cursor: pointer;">
                                                
                                            </div>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Address Card -->
                    <div class="card-custom mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="section-title mb-0">รายการที่อยู่ของฉัน</h3>
                            <button class="btn btn-navy btn-sm" data-bs-toggle="modal" data-bs-target="#addAddressModal">+ เพิ่มที่อยู่ใหม่</button>
                        </div>

                        @forelse($addresses as $address)
                            <div class="address-item border rounded p-3 mb-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark" style="font-size: 1.1rem;">
                                        <i class="fas fa-map-marker-alt text-danger me-2"></i>{{ $address->title }}
                                    </div>
                                    <div class="mt-2 text-muted" style="font-size: 0.9rem;">
                                        <strong>ชื่อผู้ติดต่อ:</strong> {{ $address->contact_name }} ({{ $address->contact_phone }})<br>
                                        {{ $address->address_line }} ต.{{ $address->subdistrict }} อ.{{ $address->district }} จ.{{ $address->province }} {{ $address->zipcode }}
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-column flex-sm-row">
                                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#editAddressModal{{ $address->id }}">แก้ไข</button>

                                    <form action="{{ route('my-account.address.delete', $address->id) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบที่อยู่นี้?');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">ลบ</button>
                                    </form>
                                </div>
                            </div>

                            <div class="modal fade" id="editAddressModal{{ $address->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold">แก้ไขที่อยู่ ({{ $address->title }})</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('my-account.address.update', $address->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <div class="row g-3">
                                                    <div class="col-md-12">
                                                        <label class="form-label">ชื่อสถานที่ (เช่น บ้าน, ที่ทำงาน)</label>
                                                        <input type="text" class="form-control" name="title" value="{{ $address->title }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">ชื่อผู้ติดต่อ</label>
                                                        <input type="text" class="form-control" name="contact_name" value="{{ $address->contact_name }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">เบอร์โทรศัพท์</label>
                                                        <input type="text" class="form-control" name="contact_phone" value="{{ $address->contact_phone }}" required>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <label class="form-label">ที่อยู่ (บ้านเลขที่, หมู่, ซอย, ถนน)</label>
                                                        <input type="text" class="form-control" name="address_line" value="{{ $address->address_line }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">จังหวัด</label>
                                                        <input type="text" class="form-control" name="province" value="{{ $address->province }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">เขต/อำเภอ</label>
                                                        <input type="text" class="form-control" name="district" value="{{ $address->district }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">แขวง/ตำบล</label>
                                                        <input type="text" class="form-control" name="subdistrict" value="{{ $address->subdistrict }}" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">รหัสไปรษณีย์</label>
                                                        <input type="text" class="form-control" name="zipcode" value="{{ $address->zipcode }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                                <button type="submit" class="btn btn-navy">บันทึกการแก้ไข</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-5 border rounded bg-light">
                                <i class="fas fa-map-marked-alt fa-3x mb-3 text-secondary"></i>
                                <h5>ยังไม่มีข้อมูลที่อยู่</h5>
                                <p>เพิ่มที่อยู่เพื่อให้การสั่งซื้อและการรับบริการสะดวกยิ่งขึ้น</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </main>

    <!-- (ใส่ Footer เดิมของคุณตรงนี้) -->

    <!-- Modal: แก้ไขข้อมูลส่วนตัว (แก้ไขจาก Form เดิมเพื่อใช้บันทึกข้อมูล Profile) -->
    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขข้อมูลส่วนตัว</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- กำหนด Form Action ไปที่ Route my-account.update -->
                <form action="{{ route('my-account.update') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="first_name">ชื่อ</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $profile->first_name) }}" required>
                            </div>
                            <div class="col-6">
                                <label for="last_name">นามสกุล</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $profile->last_name) }}" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label for="phone">เบอร์โทรศัพท์มือถือ</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $profile->phone) }}" required>
                            </div>
                            <div class="col-6">
                                <label for="company">บริษัท</label>
                                <input type="text" class="form-control" id="company" name="company" value="{{ old('company', $profile->company) }}" autocomplete="off">
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3">เปลี่ยนรหัสผ่าน (เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</h6>
                        <div class="mb-3">
                            <label for="password">รหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-modal-confirm">บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">เพิ่มที่อยู่ใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('my-account.address.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">ชื่อสถานที่ (เช่น บ้าน, ที่ทำงาน)</label>
                                <input type="text" class="form-control" name="title" placeholder="บ้าน" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ชื่อผู้ติดต่อ</label>
                                <input type="text" class="form-control" name="contact_name" placeholder="ชื่อ-นามสกุล" value="{{ $profile->first_name }} {{ $profile->last_name }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" name="contact_phone" placeholder="08X-XXX-XXXX" value="{{ $profile->phone }}" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">ที่อยู่ (บ้านเลขที่, หมู่, ซอย, ถนน)</label>
                                <input type="text" class="form-control" name="address_line" placeholder="เลขที่..." required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">จังหวัด</label>
                                <input type="text" class="form-control" name="province" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เขต/อำเภอ</label>
                                <input type="text" class="form-control" name="district" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">แขวง/ตำบล</label>
                                <input type="text" class="form-control" name="subdistrict" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">รหัสไปรษณีย์</label>
                                <input type="text" class="form-control" name="zipcode" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-navy">บันทึกที่อยู่</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const companyTypeSelect = document.getElementById('company_type');
        const serviceCheckboxes = document.querySelectorAll('.service-checkbox');
        const serviceBtnText = document.getElementById('serviceBtnText');
        const saveIndicator = document.getElementById('saveIndicator');

        // ฟังก์ชันอัปเดตข้อความบนปุ่ม Dropdown
        function updateDropdownText() {
            let checkedItems = Array.from(serviceCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            if (checkedItems.length > 0) {
                // ถ้าเลือกเกิน 2 อัน ให้แสดงจำนวนแทน เพื่อไม่ให้ข้อความล้น
                serviceBtnText.innerText = checkedItems.length > 2 
                    ? `เลือกแล้ว ${checkedItems.length} บริการ` 
                    : checkedItems.join(', ');
                serviceBtnText.classList.add('text-dark', 'fw-bold');
            } else {
                serviceBtnText.innerText = '-- เลือกบริการที่สนใจ --';
                serviceBtnText.classList.remove('text-dark', 'fw-bold');
            }
        }

        // ฟังก์ชันส่ง AJAX ไปบันทึกข้อมูล
        function autoSaveAdditionalInfo() {
            let companyType = companyTypeSelect.value;
            let checkedServices = Array.from(serviceCheckboxes).filter(cb => cb.checked).map(cb => cb.value);

            // แสดงคำว่า "กำลังบันทึก..." (ถ้าต้องการ)

            fetch("{{ route('my-account.update-additional') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    company_type: companyType,
                    service_interesting: checkedServices
                })
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    // โชว์ป้ายเขียว "บันทึกแล้ว" 2 วินาทีแล้วจางหายไป
                    saveIndicator.style.opacity = "1";
                    setTimeout(() => {
                        saveIndicator.style.opacity = "0";
                    }, 2000);
                }
            })
            .catch(error => console.error("Error saving data:", error));
        }

        // ตั้งค่าเมื่อโหลดหน้าแรกให้ข้อความปุ่มตรงกับที่ติ๊กไว้
        updateDropdownText();

        // ดักจับ Event OnChange ของประเภทธุรกิจ
        companyTypeSelect.addEventListener('change', autoSaveAdditionalInfo);

        // ดักจับ Event OnChange ของ Checkbox บริการ
        serviceCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                updateDropdownText(); // เปลี่ยนตัวหนังสือ
                autoSaveAdditionalInfo(); // เซฟลง DB
            });
        });
    });
</script>
@endpush