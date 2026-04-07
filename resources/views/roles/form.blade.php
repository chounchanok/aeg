@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ isset($role) ? 'แก้ไข Role: ' . $role->role_name : 'สร้าง Role ใหม่' }} - AEG</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ isset($role) ? 'แก้ไขข้อมูล Role' : 'สร้าง Role / สิทธิ์การใช้งาน ใหม่' }}
        </h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-6">
            <form
                action="{{ isset($role) ? route('roles.update', $role->role_id) : route('roles.store') }}"
                method="POST"
            >
                @csrf
                @if (isset($role))
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

                    {{-- ชื่อ Role --}}
                    <div>
                        <label for="role_name" class="form-label">ชื่อ Role <span class="text-danger">*</span></label>
                        <input id="role_name" name="role_name" type="text" class="form-control" placeholder="เช่น Admin, Manager, Guest"
                            value="{{ old('role_name', $role->role_name ?? '') }}" required>
                        <div class="form-text mt-1">ชื่อนี้จะต้องไม่ซ้ำกัน</div>
                    </div>

                    {{-- คำอธิบาย --}}
                    <div class="mt-3">
                        <label for="description" class="form-label">คำอธิบาย</label>
                        <textarea id="description" name="description" class="form-control" rows="3" placeholder="คำอธิบายสิทธิ์ของ Role นี้">{{ old('description', $role->description ?? '') }}</textarea>
                    </div>

                    <div class="text-right mt-5">
                        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary w-24">{{ isset($role) ? 'อัปเดต' : 'บันทึก' }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
@endsection
