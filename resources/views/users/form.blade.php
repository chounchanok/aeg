@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ isset($user) ? 'แก้ไขข้อมูลผู้ใช้: ' . $user->first_name : 'สร้างผู้ใช้ใหม่' }} - AEG</title>
@endsection
{{-- ถ้าคุณใช้ Select2 สำหรับ Role dropdown, อย่าลืมเพิ่ม CSS --}}
{{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ isset($user) ? 'แก้ไขข้อมูลผู้ใช้' : 'สร้างผู้ใช้ใหม่' }}
        </h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-8">
            <form
                action="{{ isset($user) ? route('users.update', $user->user_id) : route('users.store') }}"
                method="POST"
            >
                @csrf
                @if (isset($user))
                    @method('PUT')
                @endif

                <div class="intro-y box p-5">

                    {{-- แสดงข้อผิดพลาดจากการ Validate --}}
                    @if ($errors->any())
                        <div class="alert alert-danger show mb-4">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- ชื่อจริง --}}
                        <div>
                            <label for="first_name" class="form-label">ชื่อจริง <span class="text-danger">*</span></label>
                            <input id="first_name" name="first_name" type="text" class="form-control" placeholder="ชื่อจริง"
                                value="{{ old('first_name', $user->first_name ?? '') }}" required>
                        </div>
                        {{-- นามสกุล --}}
                        <div>
                            <label for="last_name" class="form-label">นามสกุล <span class="text-danger">*</span></label>
                            <input id="last_name" name="last_name" type="text" class="form-control" placeholder="นามสกุล"
                                value="{{ old('last_name', $user->last_name ?? '') }}" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="email" class="form-label">อีเมล <span class="text-danger">*</span></label>
                        <input id="email" name="email" type="email" class="form-control" placeholder="name@example.com"
                            value="{{ old('email', $user->email ?? '') }}" required>
                    </div>

                    <div class="mt-3">
                        <label for="phone_number" class="form-label">เบอร์โทร</label>
                        <input id="phone_number" name="phone_number" type="text" class="form-control" placeholder="08XXXXXXXX"
                            value="{{ old('phone_number', $user->phone_number ?? '') }}">
                    </div>

                    <div class="mt-3">
                        <label for="role_id" class="form-label">Role / สิทธิ์การใช้งาน <span class="text-danger">*</span></label>
                        <select id="role_id" name="role_id" class="form-select" required>
                            <option value="">-- เลือก Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->role_id }}"
                                    {{ old('role_id', $user->role_id ?? '') == $role->role_id ? 'selected' : '' }}>
                                    {{ $role->role_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                        {{-- รหัสผ่าน --}}
                        <div>
                            <label for="password" class="form-label">รหัสผ่าน @if(!isset($user))<span class="text-danger">*</span>@else (ปล่อยว่างหากไม่ต้องการเปลี่ยน) @endif</label>
                            <input id="password" name="password" type="password" class="form-control" placeholder="รหัสผ่าน (ขั้นต่ำ 8 ตัวอักษร)"
                                @if(!isset($user)) required @endif>
                        </div>
                        {{-- ยืนยันรหัสผ่าน --}}
                        <div>
                            <label for="password_confirmation" class="form-label">ยืนยันรหัสผ่าน @if(!isset($user))<span class="text-danger">*</span>@endif</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" placeholder="ยืนยันรหัสผ่าน"
                                @if(!isset($user)) required @endif>
                        </div>
                    </div>

                    {{-- สถานะ Active --}}
                    <div class="form-check mt-3">
                        <input id="is_active" name="is_active" class="form-check-input" type="checkbox" value="1"
                            {{ old('is_active', $user->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">สถานะ Active (ผู้ใช้สามารถเข้าสู่ระบบได้)</label>
                    </div>

                    <div class="text-right mt-5">
                        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary w-24">{{ isset($user) ? 'อัปเดต' : 'บันทึก' }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
{{-- ถ้าคุณใช้ Select2 สำหรับ Role dropdown, อย่าลืมเพิ่ม JS --}}
{{-- <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.form-select').select2();
    });
</script> --}}
@endsection
