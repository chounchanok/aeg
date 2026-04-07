@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ isset($asset) ? 'แก้ไขสินทรัพย์' : 'เพิ่มสินทรัพย์ใหม่' }} - AEG</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ isset($asset) ? 'แก้ไขข้อมูลสินทรัพย์' : 'เพิ่มสินทรัพย์ใหม่' }}
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12">
            <form action="{{ isset($asset) ? route('assets.update', $asset->asset_id) : route('assets.store') }}"
                  method="POST"
                  enctype="multipart/form-data">
                @csrf
                @if (isset($asset))
                    @method('PUT')
                @endif

                <div class="intro-y box p-5">
                    <div class="grid grid-cols-12 gap-x-5">
                        <div class="col-span-12 xl:col-span-4">
                            <label for="asset_name" class="form-label">ชื่อสินทรัพย์ <span class="text-danger">*</span></label>
                            <input id="asset_name" name="asset_name" type="text" class="form-control w-full"
                                   placeholder="ชื่อสินทรัพย์" value="{{ old('asset_name', $asset->asset_name ?? '') }}" required>
                        </div>
                        <div class="col-span-12 xl:col-span-4 mt-3 xl:mt-0">
                            <label for="asset_code" class="form-label">รหัสสินทรัพย์</label>
                            <input id="asset_code" name="asset_code" type="text" class="form-control w-full"
                                   placeholder="BK-CAT-001" value="{{ old('asset_code', $asset->asset_code ?? '') }}">
                        </div>
                        <div class="col-span-12 xl:col-span-4 mt-3 xl:mt-0">
                            <label for="asset_type_id" class="form-label">หมวดหมู่สินทรัพย์ <span class="text-danger">*</span></label>
                            <select id="asset_type_id" name="asset_type_id" class="form-select w-full" required>
                                <option value="">-- เลือกหมวดหมู่ --</option>
                                @foreach ($assetTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('asset_type_id', $asset->asset_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-x-5 mt-3">
                        <div class="col-span-12 xl:col-span-4">
                            <label for="type" class="form-label">ประเภท <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-select w-full" required>
                                <option value="Asset" {{ old('type', $asset->type ?? '') == 'Asset' ? 'selected' : '' }}>Asset (สินทรัพย์)</option>
                                <option value="Service" {{ old('type', $asset->type ?? '') == 'Service' ? 'selected' : '' }}>Service (งานบริการ)</option>
                            </select>
                        </div>
                        <div class="col-span-12 xl:col-span-4 mt-3 xl:mt-0">
                            <label for="status" class="form-label">สถานะ <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-select w-full" required>
                                @foreach (['Available', 'In Use', 'Under Maintenance', 'Retired'] as $status)
                                    <option value="{{ $status }}" {{ old('status', $asset->status ?? 'Available') == $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-12 xl:col-span-4 mt-3 xl:mt-0">
                            <label for="expenses" class="form-label">ค่าใช้จ่าย/มูลค่า (บาท)</label>
                            <input id="expenses" name="expenses" type="number" step="0.01" class="form-control w-full"
                                   value="{{ old('expenses', $asset->expenses ?? '0') }}">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="description" class="form-label">รายละเอียด</label>
                        <textarea id="description" name="description" class="form-control w-full" rows="3">{{ old('description', $asset->description ?? '') }}</textarea>
                    </div>

                    <div class="grid grid-cols-12 gap-x-5 mt-3">
                        <div class="col-span-12 xl:col-span-4">
                            <label for="start_date" class="form-label">วันที่เริ่มใช้งาน</label>
                            <input id="start_date" name="start_date" type="date" class="form-control w-full"
                                   value="{{ old('start_date', isset($asset->start_date) ? $asset->start_date->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-span-12 xl:col-span-4 mt-3 xl:mt-0">
                            <label for="end_date" class="form-label">วันที่สิ้นสุดการใช้งาน</label>
                            <input id="end_date" name="end_date" type="date" class="form-control w-full"
                                   value="{{ old('end_date', isset($asset->end_date) ? $asset->end_date->format('Y-m-d') : '') }}">
                        </div>
                        <div class="col-span-12 xl:col-span-4 mt-3 xl:mt-0">
                            <label for="house_id" class="form-label">กำหนดให้บ้าน (House)</label>
                            <select id="house_id" name="house_id" class="form-select w-full">
                                <option value="">-- ไม่ระบุ --</option>
                                @foreach ($houses as $house)
                                    <option value="{{ $house->house_id }}" {{ old('house_id', $asset->house_id ?? '') == $house->house_id ? 'selected' : '' }}>
                                        {{ $house->house_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-x-5 mt-3">
                        <div class="col-span-12 xl:col-span-6">
                            <label for="installer_name" class="form-label">ชื่อผู้ติดตั้ง/บริษัท</label>
                            <input id="installer_name" name="installer_name" type="text" class="form-control w-full"
                                   value="{{ old('installer_name', $asset->installer_name ?? '') }}">
                        </div>
                        <div class="col-span-12 xl:col-span-6 mt-3 xl:mt-0">
                            <label for="contact_number" class="form-label">เบอร์ติดต่อ</label>
                            <input id="contact_number" name="contact_number" type="text" class="form-control w-full"
                                   value="{{ old('contact_number', $asset->contact_number ?? '') }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-x-5 mt-3">
                        <div class="col-span-12 xl:col-span-6">
                            <label for="assigned_user" class="form-label">ผู้ดูแล (Assigned Users)</label>
                            <select id="assigned_user" name="assigned_user[]" class="form-control select2-multiple w-full" multiple="multiple">
                                <option value="">-- ไม่ระบุ --</option
                                @foreach($customerUsers as $user)
                                    <option value="{{ $user->user_id }}"
                                        {{ in_array($user->user_id, (array)old('assigned_user', $asset->assigned_user ?? [])) ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-help text-xs mt-1">* กด Ctrl ค้างไว้เพื่อเลือกหลายคน</div>
                        </div>

                        <div class="col-span-12 xl:col-span-6 mt-3 xl:mt-0">
                            <label for="team_members" class="form-label">ทีมผู้รับผิดชอบ (Team Members)</label>
                            <select id="team_members" name="team_members[]" class="form-control select2-multiple w-full" multiple="multiple">
                                <option value="">-- ไม่ระบุ --</option
                                @foreach($teamUsers as $user)
                                    <option value="{{ $user->user_id }}"
                                        {{ in_array($user->user_id, (array)old('team_members', $asset->team_members ?? [])) ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-3">
                        <div>
                            <label for="images" class="form-label">แนบไฟล์รูปภาพ (JPG/PNG)</label>
                            <input id="images" name="images[]" type="file" class="form-control" multiple accept="image/*">
                        </div>
                        <div>
                            <label for="documents" class="form-label">แนบไฟล์เอกสาร (PDF)</label>
                            <input id="documents" name="documents[]" type="file" class="form-control" multiple accept=".pdf">
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="document_detail" class="form-label">คำอธิบายรูปภาพ/เอกสาร</label>
                        <textarea id="document_detail" name="document_detail" class="form-control" placeholder="ระบุรายละเอียดไฟล์">{{ old('document_detail', $asset->document_detail ?? '') }}</textarea>
                    </div>

                    <div class="text-right mt-5">
                        <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary w-24">{{ isset($asset) ? 'อัปเดต' : 'บันทึก' }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@once
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                $('.select2-multiple').select2({
                    placeholder: "เลือกรายชื่อ",
                    allowClear: true
                });
            });
        </script>

        {{-- Alpine JS ของเดิม --}}
        <script src="//unpkg.com/alpinejs" defer></script>
    @endpush
@endonce
