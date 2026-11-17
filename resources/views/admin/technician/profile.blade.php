@extends('../layout/' . $layout)

@section('subhead')
    <title>โปรไฟล์ & KPI - AEG Admin</title>
    <style>

        /* CSS เฉพาะหน้าช่าง (prefix tj- — ห้ามใช้ ad- เพราะตัวบล็อกโฆษณาจะซ่อน) */
        .tj-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
        .tj-card { background: #fff; border-radius: 10px; padding: 14px 16px; box-shadow: 0 3px 15px #0000000b; }
        .tj-card .lbl { font-size: 12px; color: #64748b; }
        .tj-card .val { font-size: 24px; font-weight: 600; color: #1e3a8a; line-height: 1.3; }
        .tj-badge { display: inline-block; font-size: 12px; padding: 3px 10px; border-radius: 999px; font-weight: 500; white-space: nowrap; }
        .tj-s-assigned { background: #fef3c7; color: #92400e; }
        .tj-s-accepted, .tj-s-traveling, .tj-s-arrived { background: #dbeafe; color: #1e40af; }
        .tj-s-in_progress { background: #e0e7ff; color: #3730a3; }
        .tj-s-completed { background: #dcfce7; color: #166534; }
        .tj-s-cancelled, .tj-s-pending { background: #f1f5f9; color: #475569; }
        .tj-alert { padding: 12px 16px; border-radius: 8px; margin-top: 20px; }
        .tj-alert.ok { background: #dcfce7; color: #166534; }
        .tj-alert.err { background: #fee2e2; color: #991b1b; }

        .tj-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media (max-width: 1024px) { .tj-grid { grid-template-columns: 1fr; } }
        .tj-box { background: #fff; border-radius: 12px; padding: 18px; box-shadow: 0 3px 15px #0000000b; }
        .tj-box h3 { font-weight: 600; font-size: 15px; margin: 0 0 12px; }
        .tj-bar { height: 10px; border-radius: 999px; background: #e2e8f0; overflow: hidden; }
        .tj-bar > div { height: 100%; border-radius: 999px; }
        .tj-field { margin-bottom: 14px; }
    </style>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">โปรไฟล์ & KPI</h2>
    </div>

    @if(session('success'))<div class="tj-alert ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="tj-alert err">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="tj-alert err"><ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <div class="tj-cards mt-5">
        <div class="tj-card"><div class="lbl">คะแนนเฉลี่ย</div><div class="val">⭐ {{ number_format($kpi['summary']['average_rating'], 1) }}</div></div>
        <div class="tj-card"><div class="lbl">ปิดงานตรงเวลา</div><div class="val">{{ $kpi['summary']['on_time_percentage'] }}%</div></div>
        <div class="tj-card"><div class="lbl">งานที่ปิดแล้ว</div><div class="val">{{ number_format($kpi['summary']['total_completed']) }}</div></div>
    </div>

    <div class="tj-grid mt-5">
        <div class="tj-box">
            <h3>สถิติการทำงาน</h3>
            @foreach($kpi['kpis'] as $k)
                @php $color = ['green' => '#16a34a', 'yellow' => '#f59e0b', 'red' => '#dc2626'][$k['color_theme']] ?? '#1e3a8a'; @endphp
                <div style="margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; font-size:14px; margin-bottom:6px;">
                        <span>{{ $k['title'] }}</span><span style="color:{{ $color }}; font-weight:600;">{{ $k['status_text'] }} · {{ round($k['percentage']) }}%</span>
                    </div>
                    <div class="tj-bar"><div style="width: {{ min(100, max(0, $k['percentage'])) }}%; background: {{ $color }};"></div></div>
                </div>
            @endforeach
        </div>

        <div class="tj-box">
            <h3>ข้อมูลส่วนตัว</h3>
            <form action="{{ route('admin.tech.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div style="display:flex; align-items:center; gap:14px; margin-bottom:14px;">
                    <img src="{{ $profile['profile_image_url'] ?: asset('dist/images/profile-1.jpg') }}" alt="" style="width:64px; height:64px; border-radius:50%; object-fit:cover;">
                    <input type="file" name="profile_image" accept="image/jpeg,image/png,image/webp" class="form-control">
                </div>
                <div class="tj-field">
                    <label class="form-label">ชื่อ-นามสกุล <span style="color:#dc2626">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name', $profile['full_name']) }}" class="form-control" required maxlength="255">
                </div>
                <div class="tj-field">
                    <label class="form-label">เบอร์โทรศัพท์ (ใช้เข้าสู่ระบบ) <span style="color:#dc2626">*</span></label>
                    <input type="tel" name="phone" value="{{ old('phone', $profile['phone']) }}" class="form-control" required maxlength="20">
                </div>
                <div class="tj-field">
                    <label class="form-label">ที่อยู่</label>
                    <textarea name="address" rows="3" class="form-control">{{ old('address', $profile['address']) }}</textarea>
                </div>
                <div class="tj-field">
                    <label class="form-label">อีเมล</label>
                    <input type="text" value="{{ $profile['email'] }}" class="form-control" disabled>
                </div>
                <button type="submit" class="btn btn-primary w-full">บันทึก</button>
            </form>
        </div>
    </div>
@endsection
