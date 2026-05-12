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
                                <div class="member-card-visual">
                                    <div class="d-flex justify-content-between">
                                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                                            <span style="color:#0a1931; font-weight: 800; font-size: 8px;">AEG</span>
                                        </div>
                                        <div class="card-label-top">{{ Auth::user()->tier ?? 'Standard Member' }}</div>
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
                                </div>
                            </div>
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

                    <!-- My Address Card (คงไว้ตามเดิม แต่ใส่ข้อมูลจำลองไปก่อน) -->
                    <div class="card-custom mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="section-title mb-0">รายการที่อยู่ของฉัน</h3>
                            <button class="btn btn-add-address" data-bs-toggle="modal" data-bs-target="#addressModal">เพิ่มที่อยู่ใหม่</button>
                        </div>
                        <div class="address-item">
                            <div>
                                <div class="address-name">AEG CNX Branch</div>
                                <div class="address-details">
                                    {{ $user->address ?? 'เลขที่ 135 ถ.มหิดล ต.หายยา อ.เมืองเชียงใหม่ จ.เชียงใหม่ 50100' }}
                                </div>
                            </div>
                            <div>
                                <a class="edit-link" data-bs-toggle="modal" data-bs-target="#addressModal">แก้ไข</a>
                            </div>
                        </div>
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

@endsection

@section('scripts')
@endsection