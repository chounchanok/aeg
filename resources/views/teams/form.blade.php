@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ isset($team) ? 'แก้ไขทีม' : 'สร้างทีมใหม่' }} - AEG</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ isset($team) ? 'แก้ไขข้อมูลทีม' : 'สร้างทีมใหม่' }}
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12">
            <!-- BEGIN: Form Layout -->
            <form action="{{ isset($team) ? route('teams.update', $team->team_id) : route('teams.store') }}" method="POST">
                @csrf
                @if (isset($team))
                    @method('PUT')
                @endif

                <div class="intro-y box p-5">
                    <div>
                        <label for="team_name" class="form-label">ชื่อทีม</label>
                        <input id="team_name" name="team_name" type="text" class="form-control w-full"
                            placeholder="เช่น ทีม Alpha (ก่อสร้าง)" value="{{ old('team_name', $team->team_name ?? '') }}" required>
                    </div>

                    <div class="mt-3">
                        <label for="team_lead_id" class="form-label">หัวหน้าทีม</label>
                        <select id="team_lead_id" name="team_lead_id" class="form-select w-full">
                            <option value="">-- ไม่กำหนด --</option>
                            @php
                                $currentLead = old('team_lead_id', $team->team_lead_id ?? '');
                            @endphp
                            @foreach ($teamLeads as $lead)
                                <option value="{{ $lead->user_id }}" {{ $currentLead == $lead->user_id ? 'selected' : '' }}>
                                    {{ $lead->first_name }} {{ $lead->last_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-3">
                        <label for="description" class="form-label">รายละเอียด</label>
                        <textarea id="description" name="description" class="form-control w-full"
                            placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับทีม">{{ old('description', $team->description ?? '') }}</textarea>
                    </div>

                    <div class="text-right mt-5">
                        <a href="{{ route('teams.index') }}" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary w-24">{{ isset($team) ? 'อัปเดต' : 'บันทึก' }}</button>
                    </div>
                </div>
            </form>
            <!-- END: Form Layout -->
        </div>
    </div>
@endsection
