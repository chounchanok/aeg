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
                                    <div class="col-12">
                                        <div class="info-label">อีเมล</div>
                                        <div class="info-value">{{ $user->email }}</div>
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button class="btn btn-navy" data-bs-toggle="modal" data-bs-target="#profileModal">แก้ไขข้อมูลส่วนตัว</button>
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
                        </div>

                        <hr>
                        <h6 class="mb-3">เปลี่ยนรหัสผ่าน (เว้นว่างไว้หากไม่ต้องการเปลี่ยน)</h6>
                        <div class="mb-3">
                            <label for="password">รหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmation">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
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
                                <input type="text" class="form-control" name="contact_name" placeholder="ชื่อ-นามสกุล" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">เบอร์โทรศัพท์</label>
                                <input type="text" class="form-control" name="contact_phone" placeholder="08X-XXX-XXXX" required>
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
