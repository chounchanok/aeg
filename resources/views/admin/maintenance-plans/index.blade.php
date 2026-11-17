@extends('../layout/' . $layout)

@section('subhead')
    <title>แผนบำรุงรักษา - AEG Admin</title>
    <style>
        /* prefix mp- (ห้ามขึ้นต้นด้วย ad- เพราะตัวบล็อกโฆษณาจะซ่อน) */
        .mp-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; }
        .mp-card { background: #fff; border-radius: 10px; padding: 14px 16px; box-shadow: 0 3px 15px #0000000b; }
        .mp-card .lbl { font-size: 12px; color: #64748b; }
        .mp-card .val { font-size: 24px; font-weight: 600; color: #1e3a8a; }
        .mp-alert { padding: 12px 16px; border-radius: 8px; margin-top: 16px; }
        .mp-alert.ok { background: #dcfce7; color: #166534; }
        .mp-alert.err { background: #fee2e2; color: #991b1b; }
        .mp-alert.info { background: #eff6ff; color: #1e40af; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .mp-table th, .mp-table td { white-space: nowrap; vertical-align: top; }
        .mp-pill { display: inline-block; font-size: 11px; padding: 2px 8px; border-radius: 999px; background: #f1f5f9; color: #475569; }
        .mp-pill.on { background: #dcfce7; color: #166534; }
        .mp-pill.warn { background: #fef3c7; color: #92400e; }
        .mp-pill.late { background: #fee2e2; color: #991b1b; }
        .mp-modal { position: fixed; inset: 0; background: rgba(15, 23, 42, .55); display: none; align-items: center; justify-content: center; z-index: 9999; padding: 16px; }
        .mp-modal.show { display: flex; }
        .mp-modal-box { background: #fff; border-radius: 12px; padding: 20px; width: 100%; max-width: 620px; max-height: 92vh; overflow-y: auto; }
        .mp-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media (max-width: 640px) { .mp-grid2 { grid-template-columns: 1fr; } }
        .mp-day { border-left: 3px solid #1e3a8a; padding: 6px 12px; margin-bottom: 10px; }
        .mp-day .d { font-weight: 600; font-size: 13px; }
        .mp-day .it { font-size: 13px; color: #334155; }
        .mp-filter { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
    </style>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8" style="gap:8px; flex-wrap:wrap;">
        <h2 class="text-lg font-medium mr-auto">แผนบำรุงรักษา</h2>
        <form action="{{ route('admin.maintenance-plans.run-due') }}" method="POST" onsubmit="return confirm('เปิดใบงานให้ทุกแผนที่ถึงช่วงเปิดล่วงหน้าตอนนี้เลย?');">
            @csrf
            <button type="submit" class="btn btn-outline-primary">เปิดใบงานที่ถึงรอบตอนนี้</button>
        </form>
    </div>

    @if(session('success'))<div class="mp-alert ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mp-alert err">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="mp-alert err"><ul style="margin:0;padding-left:18px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    @if($withoutPlan > 0)
        <div class="mp-alert info">
            <span>มีแพ็กเกจของลูกค้า <b>{{ number_format($withoutPlan) }}</b> รายการที่ซื้อก่อนมีระบบนี้ แต่บริการต้นทางตั้งแผนบำรุงรักษาไว้แล้ว</span>
            <form action="{{ route('admin.maintenance-plans.apply-defaults') }}" method="POST" onsubmit="return confirm('คัดลอกแผนจากบริการต้นทางให้ทุกรายการ? (รอบแรกจะเป็นรอบถัดไปที่ยังไม่ถึง)');">
                @csrf <button type="submit" class="btn btn-primary btn-sm">ใช้แผนจากบริการให้ทั้งหมด</button>
            </form>
        </div>
    @endif

    <div class="mp-cards mt-5">
        <div class="mp-card"><div class="lbl">แผนทั้งหมด</div><div class="val">{{ number_format($stats['plans']) }}</div></div>
        <div class="mp-card"><div class="lbl">เปิดใบงานอัตโนมัติ</div><div class="val">{{ number_format($stats['auto']) }}</div></div>
        <div class="mp-card"><div class="lbl">ครบรอบใน 30 วัน</div><div class="val">{{ number_format($stats['due30']) }}</div></div>
        <div class="mp-card"><div class="lbl">ใบงานตามรอบที่ยังไม่ปิด</div><div class="val">{{ number_format($stats['open']) }}</div>
            <div class="lbl">รอมอบหมายช่าง {{ number_format($stats['unassigned']) }}</div></div>
    </div>

    <form method="GET" class="box p-5 mt-5 mp-filter">
        <div>
            <label class="form-label text-xs">ค้นหา</label>
            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="ชื่อลูกค้า / เบอร์ / แพ็กเกจ / S/N">
        </div>
        <div>
            <label class="form-label text-xs">แสดง</label>
            <select name="filter" class="form-select">
                @foreach(['all' => 'ทั้งหมด', 'due30' => 'ครบรอบใน 30 วัน', 'overdue' => 'เลยกำหนด (ยังไม่มีใบงาน)', 'open' => 'มีใบงานค้าง', 'off' => 'ปิดอัตโนมัติ'] as $k => $l)
                    <option value="{{ $k }}" {{ $filter === $k ? 'selected' : '' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        @if(request('customer_id'))<input type="hidden" name="customer_id" value="{{ request('customer_id') }}">@endif
        <button type="submit" class="btn btn-primary">ค้นหา</button>
        @if(request('customer_id') || $q || $filter !== 'all')<a href="{{ route('admin.maintenance-plans.index') }}" class="btn btn-outline-secondary">ล้างตัวกรอง</a>@endif
    </form>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 xl:col-span-9">
            <div class="box p-5">
                <div class="overflow-x-auto">
                    <table class="table mp-table">
                        <thead>
                            <tr>
                                <th>ลูกค้า</th>
                                <th>แพ็กเกจ / บริการ</th>
                                <th>ความถี่</th>
                                <th>รอบถัดไป</th>
                                <th>โควต้า</th>
                                <th>ช่างประจำ</th>
                                <th>ใบงานตามรอบ</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($plans as $pl)
                                @php
                                    $late = $pl->next_service_date && $pl->next_service_date < now()->toDateString() && !$pl->open_request_id;
                                    $expired = $pl->warranty_expire_date && $pl->warranty_expire_date < now()->toDateString();
                                @endphp
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.customers.show', $pl->customer_id) }}" class="font-medium">{{ trim(($pl->first_name ?? '') . ' ' . ($pl->last_name ?? '')) ?: $pl->username }}</a>
                                        <div class="text-xs text-slate-500">{{ $pl->phone }}</div>
                                    </td>
                                    <td>
                                        <div style="white-space:normal; max-width:260px;">{{ $pl->product_name }}</div>
                                        <div class="text-xs text-slate-500">S/N {{ $pl->serial_number ?: '-' }} · หมดสัญญา {{ $pl->warranty_expire_date ? \Carbon\Carbon::parse($pl->warranty_expire_date)->format('d/m/Y') : '-' }}</div>
                                    </td>
                                    <td>
                                        ทุก {{ $pl->visit_interval_months }} เดือน
                                        <div>
                                            @if(!$pl->auto_schedule)<span class="mp-pill">ปิดอัตโนมัติ</span>
                                            @elseif($expired)<span class="mp-pill late">สัญญาหมดแล้ว</span>
                                            @else<span class="mp-pill on">อัตโนมัติ</span>@endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($pl->next_service_date)
                                            <span class="{{ $late ? 'text-danger font-medium' : '' }}">{{ \Carbon\Carbon::parse($pl->next_service_date)->format('d/m/Y') }}</span>
                                            @if($late)<div><span class="mp-pill late">เลยกำหนด</span></div>@endif
                                        @else - @endif
                                        @if($pl->preferred_time_slot)<div class="text-xs text-slate-500">{{ $pl->preferred_time_slot }}</div>@endif
                                    </td>
                                    <td>{{ $pl->remaining === null ? 'ไม่จำกัด' : $pl->remaining . ' / ' . $pl->total_service_count }}</td>
                                    <td>{{ $pl->technician_name ?: '—' }}</td>
                                    <td>
                                        @if($pl->open_request_id)
                                            <a href="{{ route('admin.service-requests.show', $pl->open_request_id) }}" class="text-primary">{{ $pl->open_ticket }}</a>
                                            <div class="text-xs text-slate-500">นัด {{ $pl->open_date ? \Carbon\Carbon::parse($pl->open_date)->format('d/m/Y') : '-' }}
                                                @if($pl->open_status === 'pending')<span class="mp-pill warn">รอมอบหมายช่าง</span>@endif</div>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="display:flex; gap:6px;">
                                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="mpOpen('plan{{ $pl->id }}')">แก้ไขแผน</button>
                                            <form action="{{ route('admin.maintenance-plans.run', $pl->id) }}" method="POST" onsubmit="return confirm('เปิดใบงานรอบนี้ ({{ $pl->next_service_date ? \Carbon\Carbon::parse($pl->next_service_date)->format('d/m/Y') : '-' }}) ตอนนี้เลย?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary" {{ $pl->block_reason ? 'disabled' : '' }} title="{{ $pl->block_reason ?: 'เปิดใบงานรอบนี้ทันที' }}">เปิดใบงาน</button>
                                            </form>
                                        </div>
                                        @if($pl->block_reason && !$pl->open_request_id)<div class="text-xs text-slate-400 mt-1">{{ $pl->block_reason }}</div>@endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-slate-500 py-6">
                                    ยังไม่มีแผนบำรุงรักษา — ตั้งค่าแผนตั้งต้นได้ที่ "สินค้าและบริการ → แพ็กเกจ & บริการ" แล้วแผนจะถูกสร้างให้ลูกค้าเมื่อซื้อหรือแอดมินเพิ่มแพ็กเกจให้
                                </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-3">
            <div class="box p-5">
                <div class="font-medium text-base mb-3">รอบที่ใกล้ถึง (60 วัน)</div>
                @forelse($upcoming as $date => $items)
                    <div class="mp-day">
                        <div class="d">{{ \Carbon\Carbon::parse($date)->locale('th')->translatedFormat('D j M Y') }}</div>
                        @foreach($items as $it)
                            <div class="it">{{ trim(($it->first_name ?? '') . ' ' . ($it->last_name ?? '')) ?: $it->username }} — {{ \Illuminate\Support\Str::limit($it->product_name, 28) }}
                                @if($it->technician_name)<span class="text-slate-500">({{ $it->technician_name }})</span>@endif</div>
                        @endforeach
                    </div>
                @empty
                    <div class="text-slate-500 text-sm">ไม่มีรอบใน 60 วันข้างหน้า</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ===== Modal แก้ไขแผนของแต่ละแพ็กเกจ ===== --}}
    @foreach($plans as $pl)
        <div id="plan{{ $pl->id }}" class="mp-modal" onclick="if(event.target===this)mpClose(this.id)">
            <div class="mp-modal-box">
                <div class="font-medium text-base">แผนบำรุงรักษา: {{ $pl->product_name }}</div>
                <div class="text-xs text-slate-500 mb-4">{{ trim(($pl->first_name ?? '') . ' ' . ($pl->last_name ?? '')) ?: $pl->username }} · S/N {{ $pl->serial_number ?: '-' }}</div>
                <form action="{{ route('admin.maintenance-plans.update', $pl->id) }}" method="POST">
                    @csrf
                    <div class="mp-grid2">
                        <div>
                            <label class="form-label">เข้าบริการทุกกี่เดือน <span style="color:#dc2626">*</span></label>
                            <input type="number" name="visit_interval_months" min="1" max="60" value="{{ $pl->visit_interval_months }}" class="form-control" required>
                        </div>
                        <div>
                            <label class="form-label">วันครบรอบถัดไป</label>
                            <input type="date" name="next_service_date" value="{{ $pl->next_service_date }}" class="form-control">
                        </div>
                        <div>
                            <label class="form-label">วันที่สะดวกของเดือน (1-28)</label>
                            <input type="number" name="preferred_day" min="1" max="28" value="{{ $pl->preferred_day }}" class="form-control" placeholder="เว้นว่าง = ตามวันครบรอบ">
                        </div>
                        <div>
                            <label class="form-label">ช่วงเวลาที่สะดวก</label>
                            <select name="preferred_time_slot" class="form-select">
                                <option value="">ไม่ระบุ</option>
                                @foreach(\App\Services\MaintenancePlanService::TIME_SLOTS as $slot)
                                    <option value="{{ $slot }}" {{ $pl->preferred_time_slot === $slot ? 'selected' : '' }}>{{ $slot }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">ช่างประจำ</label>
                            <select name="maintenance_technician_id" class="form-select">
                                <option value="">— ให้แอดมินมอบหมายทุกรอบ —</option>
                                @foreach($technicians as $t)
                                    <option value="{{ $t->id }}" {{ (int) $pl->maintenance_technician_id === (int) $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">ที่อยู่เข้าบริการ</label>
                            <select name="maintenance_address_id" class="form-select">
                                <option value="">— ที่อยู่หลักของลูกค้า —</option>
                                @foreach($addresses[$pl->customer_id] ?? [] as $a)
                                    <option value="{{ $a->id }}" {{ (int) $pl->maintenance_address_id === (int) $a->id ? 'selected' : '' }}>{{ $a->title }} — {{ \Illuminate\Support\Str::limit($a->address_line, 40) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">โควต้าเข้าบริการทั้งหมด</label>
                            <input type="number" name="total_service_count" min="0" value="{{ $pl->total_service_count }}" class="form-control">
                            <div class="text-xs text-slate-500">ใช้ไปแล้ว {{ (int) $pl->used_service_count }} ครั้ง · 0 = ไม่จำกัด</div>
                        </div>
                        <div>
                            <label class="form-label">วันหมดอายุสัญญา</label>
                            <input type="date" name="warranty_expire_date" value="{{ $pl->warranty_expire_date ? \Carbon\Carbon::parse($pl->warranty_expire_date)->toDateString() : '' }}" class="form-control">
                        </div>
                        <div>
                            <label class="form-label">เปิดใบงานล่วงหน้า (วัน)</label>
                            <input type="number" name="ticket_lead_days" min="0" max="90" value="{{ $pl->ticket_lead_days ?? 10 }}" class="form-control">
                        </div>
                    </div>
                    <label class="form-label mt-3">หมายเหตุถึงช่าง (แนบในใบงานทุกรอบ)</label>
                    <input type="text" name="maintenance_note" maxlength="500" value="{{ $pl->maintenance_note }}" class="form-control" placeholder="เช่น ติดต่อ รปภ. ก่อนเข้า / ตรวจกล้อง 8 ตัว">
                    <div class="flex items-center mt-3">
                        <input type="checkbox" name="auto_schedule" value="1" id="auto{{ $pl->id }}" class="form-check-input border mr-2" {{ $pl->auto_schedule ? 'checked' : '' }}>
                        <label for="auto{{ $pl->id }}">เปิดใบงานตามรอบให้อัตโนมัติ</label>
                    </div>
                    <div class="flex justify-end gap-2 mt-4">
                        <button type="button" class="btn btn-outline-secondary" onclick="mpClose('plan{{ $pl->id }}')">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary">บันทึกแผน</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection

@section('script')
    <script>
        function mpOpen(id) { document.getElementById(id).classList.add('show'); }
        function mpClose(id) { document.getElementById(id).classList.remove('show'); }
    </script>
@endsection
