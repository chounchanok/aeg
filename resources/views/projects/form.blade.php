@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ isset($project) ? 'แก้ไขโครงการ' : 'สร้างโครงการใหม่' }} - AEG</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ isset($project) ? 'แก้ไขโครงการ: ' . $project->project_name : 'กรอกรายละเอียดโครงการใหม่' }}
        </h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12">
            <form action="{{ isset($project) ? route('projects.update', $project->project_id) : route('projects.store') }}"
                  method="POST" enctype="multipart/form-data"
                  x-data="{
                      projectType: '{{ old('project_type', $project->project_type ?? 'โครงการ') }}',
                      progress: {{ old('progress', $project->progress ?? 0) }}
                  }">
                @csrf
                @if (isset($project)) @method('PUT') @endif

                <div class="intro-y box p-5">
                    {{-- ประเภทโครงการ --}}
                    <div class="mb-4">
                        <label class="form-label font-bold">ประเภทโครงการ</label>
                        <div class="flex flex-col sm:flex-row mt-2">
                            @foreach(['โครงการ', 'บ้าน', 'อื่นๆ'] as $type)
                                <div class="form-check mr-4">
                                    <input id="type-{{ $type }}" class="form-check-input" type="radio" name="project_type" value="{{ $type }}" x-model="projectType">
                                    <label class="form-check-label" for="type-{{ $type }}">{{ $type === 'อื่นๆ' ? 'อื่นๆ (โปรดระบุ)' : 'งาน'.$type }}</label>
                                </div>
                            @endforeach
                        </div>
                        <div x-show="projectType === 'อื่นๆ'" class="mt-2">
                            <input name="project_type_other" type="text" class="form-control" placeholder="ระบุประเภท..." value="{{ old('project_type_other', $project->project_type_other ?? '') }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="form-label">รหัสโครงการ</label>
                            <input name="project_code" type="text" class="form-control" placeholder="SR-E001" value="{{ old('project_code', $project->project_code ?? '') }}">
                        </div>
                        <div>
                            <label class="form-label">รหัสอ้างอิง</label>
                            <input name="reference_code" type="text" class="form-control" value="{{ old('reference_code', $project->reference_code ?? '') }}">
                        </div>
                        <div>
                            <label class="form-label">รหัสใบ PO</label>
                            <input name="po_number" type="text" class="form-control" value="{{ old('po_number', $project->po_number ?? '') }}">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">ชื่อโครงการ / ชื่อบ้าน</label>
                        <input name="project_name" type="text" class="form-control" required value="{{ old('project_name', $project->project_name ?? '') }}">
                    </div>

                    <div class="mt-4">
                        <label class="form-label">สถานที่ตั้ง (ที่อยู่)</label>
                        <textarea name="location_address" class="form-control" rows="2">{{ old('location_address', $project->location_address ?? '') }}</textarea>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">Google Map Link</label>
                        <input name="location_map_link" type="url" class="form-control" placeholder="https://goo.gl/maps/..." value="{{ old('location_map_link', $project->location_map_link ?? '') }}">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        {{-- ทีมงาน (Multiple) --}}
                        <div>
                            <label class="form-label">ทีมงานผู้รับผิดชอบ</label>
                            <select name="team_members[]" class="form-control select2" multiple>
                                @foreach($teamUsers as $user)
                                    <option value="{{ $user->user_id }}"
                                        {{ in_array($user->user_id, old('team_members', $project->team_members ?? [])) ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- ลูกค้า (Multiple) --}}
                        <div>
                            <label class="form-label">ผู้ติดต่อฝั่งลูกค้า</label>
                            <select name="customer_contacts[]" class="form-control select2" multiple>
                                @foreach($customerUsers as $user)
                                    <option value="{{ $user->user_id }}"
                                        {{ in_array($user->user_id, old('customer_contacts', $project->customer_contacts ?? [])) ? 'selected' : '' }}>
                                        {{ $user->first_name }} {{ $user->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                        <div>
                            <label class="form-label">วันที่เริ่มงาน</label>
                            <input name="start_date" type="date" class="form-control" value="{{ old('start_date', isset($project) && $project->start_date ? $project->start_date->format('Y-m-d') : '') }}">
                        </div>
                        <div>
                            <label class="form-label">วันที่สิ้นสุดงาน</label>
                            <input name="end_date" type="date" class="form-control" value="{{ old('end_date', isset($project) && $project->end_date ? $project->end_date->format('Y-m-d') : '') }}">
                        </div>
                        <div>
                            <label class="form-label">ความคืบหน้า (<span x-text="progress"></span>%)</label>
                            <input type="range" name="progress" class="form-range w-full" min="0" max="100" x-model="progress">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">รายละเอียดงาน</label>
                        <textarea name="description" class="form-control" rows="4">{{ old('description', $project->description ?? '') }}</textarea>
                    </div>

                    <div class="form-check mt-4">
                        <input id="is_subscribed" name="is_subscribed" class="form-check-input" type="checkbox" value="1" {{ old('is_subscribed', $project->is_subscribed ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_subscribed">ติดตามความเคลื่อนไหว (Subscribed)</label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="form-label">อัปโหลดรูปภาพโครงการ</label>
                            <input name="images[]" type="file" class="form-control" multiple accept="image/*">
                        </div>
                        <div>
                            <label class="form-label">อัปโหลดเอกสาร (PDF)</label>
                            <input name="documents[]" type="file" class="form-control" multiple accept=".pdf">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label">คำอธิบายรูปภาพ</label>
                        <input name="image_description" type="text" class="form-control" value="{{ old('image_description', $project->image_description ?? '') }}">
                    </div>

                    <div class="text-right mt-8">
                        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary w-32">{{ isset($project) ? 'อัปเดตโครงการ' : 'บันทึกโครงการ' }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="//unpkg.com/alpinejs" defer></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "คลิกเพื่อเลือก...",
            allowClear: true
        });
    });
</script>
@endsection
