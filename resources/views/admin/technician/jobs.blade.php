@extends('../layout/' . $layout)

@section('subhead')
    <title>งานของฉัน - AEG Admin</title>
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

        .tj-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
        .tj-tab { padding: 8px 16px; border-radius: 999px; background: #fff; color: #475569; text-decoration: none; box-shadow: 0 2px 8px #0000000b; font-weight: 500; }
        .tj-tab.active { background: #1e3a8a; color: #fff; }
        .tj-tab .n { display: inline-block; min-width: 22px; padding: 0 6px; margin-left: 6px; border-radius: 999px; background: #e2e8f0; color: #334155; font-size: 12px; text-align: center; }
        .tj-tab.active .n { background: #fff; color: #1e3a8a; }
        .tj-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 14px; }
        .tj-job { background: #fff; border-radius: 12px; padding: 16px; box-shadow: 0 3px 15px #0000000b; display: flex; flex-direction: column; gap: 8px; }
        .tj-job .top { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .tj-job .ticket { font-weight: 600; color: #0f172a; }
        .tj-job .row { font-size: 13px; color: #475569; }
        .tj-job .problem { font-size: 13px; color: #334155; background: #f8fafc; border-radius: 8px; padding: 8px 10px; }
        .tj-job .actions { display: flex; gap: 8px; margin-top: 4px; }
        .tj-empty { background: #fff; border-radius: 12px; padding: 40px; text-align: center; color: #94a3b8; }
        .tj-filter { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
    </style>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">งานของฉัน</h2>
    </div>

    @if(session('success'))<div class="tj-alert ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="tj-alert err">{{ session('error') }}</div>@endif
    @if($errors->any())
        <div class="tj-alert err"><ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    {{-- สรุป KPI เหมือนหน้าแรกของแอปช่าง --}}
    <div class="tj-cards mt-5">
        <div class="tj-card"><div class="lbl">คะแนนเฉลี่ยจากลูกค้า</div><div class="val">⭐ {{ number_format($kpi['summary']['average_rating'], 1) }}</div></div>
        <div class="tj-card"><div class="lbl">ปิดงานตรงเวลา</div><div class="val">{{ $kpi['summary']['on_time_percentage'] }}%</div></div>
        <div class="tj-card"><div class="lbl">งานที่ปิดแล้วทั้งหมด</div><div class="val">{{ number_format($kpi['summary']['total_completed']) }}</div></div>
        <div class="tj-card"><div class="lbl">งานที่ต้องทำ</div><div class="val">{{ count($tasks['new_tasks']) + count($tasks['ongoing_tasks']) }}</div></div>
    </div>

    <form method="GET" class="box p-5 mt-5 tj-filter">
        <div>
            <label class="form-label text-xs">วันที่นัดหมาย</label>
            <input type="date" name="date" value="{{ $date }}" class="form-control">
        </div>
        <input type="hidden" name="tab" value="{{ $tab }}">
        <button type="submit" class="btn btn-primary">ค้นหา</button>
        <a href="{{ route('admin.tech.jobs', ['date' => now()->toDateString(), 'tab' => $tab]) }}" class="btn btn-outline-secondary">วันนี้</a>
        @if($date)<a href="{{ route('admin.tech.jobs', ['tab' => $tab]) }}" class="btn btn-outline-secondary">ทุกวัน</a>@endif
    </form>

    @php
        $tabs = [
            'new' => ['งานใหม่', $tasks['new_tasks']],
            'ongoing' => ['กำลังดำเนินการ', $tasks['ongoing_tasks']],
            'history' => ['ประวัติงาน', $tasks['history_tasks']],
        ];
        $list = $tabs[$tab][1];
    @endphp
    <div class="tj-tabs mt-5">
        @foreach($tabs as $key => [$title, $items])
            <a href="{{ route('admin.tech.jobs', ['tab' => $key, 'date' => $date]) }}" class="tj-tab {{ $tab === $key ? 'active' : '' }}">{{ $title }}<span class="n">{{ count($items) }}</span></a>
        @endforeach
    </div>

    <div class="mt-4">
        @if(count($list) === 0)
            <div class="tj-empty">ไม่มีงานในแท็บนี้{{ $date ? ' ในวันที่เลือก' : '' }}</div>
        @else
            <div class="tj-list">
                @foreach($list as $t)
                    <div class="tj-job">
                        <div class="top">
                            <span class="ticket">{{ $t->ticket_number ?? ('#' . $t->id) }}</span>
                            <span class="tj-badge tj-s-{{ $t->status }}">{{ $labels[$t->status] ?? $t->status }}</span>
                        </div>
                        <div class="row">📅 {{ $t->preferred_date ? \Carbon\Carbon::parse($t->preferred_date)->format('d/m/Y') : '-' }} @if($t->time_slot) · {{ $t->time_slot }} @endif</div>
                        <div class="row">🔧 {{ $t->product_name ?? 'บริการทั่วไป' }}</div>
                        <div class="row">📍 {{ trim(($t->district ?? '') . ' ' . ($t->province ?? '')) ?: '-' }}</div>
                        @if($t->problem_description)<div class="problem">{{ \Illuminate\Support\Str::limit($t->problem_description, 120) }}</div>@endif
                        <div class="actions">
                            <a href="{{ route('admin.tech.show', $t->id) }}" class="btn btn-primary btn-sm">ดูรายละเอียด</a>
                            @if($t->latitude && $t->longitude)
                                <a href="https://www.google.com/maps/dir/?api=1&destination={{ $t->latitude }},{{ $t->longitude }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">นำทาง</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
