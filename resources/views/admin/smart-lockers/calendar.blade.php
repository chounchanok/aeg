@extends('../layout/' . $layout)

@section('subhead')
    <title>ปฏิทินการจองตู้เซฟ - AEG Admin</title>
    <style>
        .lk-tl { border-collapse: separate; border-spacing: 0; font-size: 11px; }
        .lk-tl th, .lk-tl td { border-bottom: 1px solid #e2e8f0; border-right: 1px solid #f1f5f9; padding: 0; text-align: center; }
        .lk-tl th.sticky, .lk-tl td.sticky { position: sticky; left: 0; background: #fff; z-index: 2; text-align: left; padding: 6px 10px; min-width: 150px; border-right: 1px solid #e2e8f0; }
        .lk-tl thead th { padding: 4px 0; min-width: 28px; color: #64748b; font-weight: 600; }
        .lk-tl thead th.we { color: #dc2626; }
        .lk-tl td.c { height: 34px; }
        .lk-tl td.c a, .lk-tl td.c span { display: block; height: 34px; }
        .lk-tl td.booked a { background: #fca5a5; }
        .lk-tl td.pending a { background: #fcd34d; }
        .lk-tl td.admin a { background: #a5b4fc; }
        .lk-tl td.block span { background: #cbd5e1; }
        .lk-tl td.today { box-shadow: inset 0 0 0 2px #2563eb; }
        .lk-legend span { display: inline-block; width: 12px; height: 12px; border-radius: 3px; vertical-align: middle; margin-right: 4px; }
    </style>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-wrap items-center mt-10 gap-2">
        <div class="mr-auto">
            <h2 class="text-lg font-medium">ปฏิทินการจองตู้เซฟ (ภาพรวมทุกตู้)</h2>
            <div class="text-slate-500 text-sm mt-1">ชี้ที่ช่องเพื่อดูชื่อลูกค้าและเลขที่ออเดอร์ · คลิกเพื่อเปิดหน้าลูกค้า</div>
        </div>
        <a href="{{ route('admin.smart-lockers.index') }}" class="btn btn-outline-secondary">กลับรายการตู้</a>
    </div>

    <div class="box p-5 mt-5">
        <div class="flex flex-wrap items-center gap-3 mb-4">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.smart-lockers.calendar', ['month' => $monthStart->copy()->subMonth()->format('Y-m')]) }}">&laquo; เดือนก่อน</a>
            <h3 class="font-medium text-base">{{ $monthStart->locale('th')->translatedFormat('F') }} {{ $monthStart->year + 543 }}</h3>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.smart-lockers.calendar', ['month' => $monthStart->copy()->addMonth()->format('Y-m')]) }}">เดือนถัดไป &raquo;</a>
            <div class="lk-legend text-xs ml-auto flex flex-wrap gap-3">
                <div><span style="background:#fca5a5"></span>จองแล้ว</div>
                <div><span style="background:#fcd34d"></span>รอชำระเงิน</div>
                <div><span style="background:#a5b4fc"></span>แอดมินผูกให้ลูกค้า</div>
                <div><span style="background:#cbd5e1"></span>ปิดวัน/ซ่อมบำรุง</div>
                <div><span style="background:#fff;border:1px solid #e2e8f0"></span>ว่าง</div>
            </div>
        </div>

        <div class="overflow-auto">
            <table class="lk-tl w-full">
                <thead>
                    <tr>
                        <th class="sticky">ตู้</th>
                        @foreach($days as $d)
                            <th class="{{ $d->isWeekend() ? 'we' : '' }}">{{ $d->day }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $row)
                        <tr>
                            <td class="sticky">
                                <a class="font-medium text-primary" href="{{ route('admin.smart-lockers.availability', [$row->locker->id, 'month' => $monthStart->format('Y-m')]) }}">{{ $row->locker->locker_number }}</a>
                                <div class="text-slate-500">{{ $row->locker->type }}</div>
                            </td>
                            @foreach($row->cells as $cell)
                                @php
                                    $b = $cell->booking;
                                    $cls = $b ? ($b->source === 'admin_assigned' ? 'admin' : ($b->status === 'pending_payment' ? 'pending' : 'booked')) : (($cell->block || $cell->maintenance) ? 'block' : '');
                                @endphp
                                <td class="c {{ $cls }} {{ $cell->date === now()->toDateString() ? 'today' : '' }}">
                                    @if($b)
                                        <a href="{{ $b->user_id ? route('admin.customers.show', $b->user_id) : '#' }}"
                                           title="{{ $cell->date }} · {{ $b->booking_number }} · {{ $b->customer_name }} ({{ $b->phone ?? '-' }})"></a>
                                    @else
                                        <span title="{{ $cell->date }} · {{ $cell->block ? 'ปิดวัน ' . ($cell->block->note ?? '') : ($cell->maintenance ? 'ซ่อมบำรุง' : 'ว่าง') }}"></span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="box p-5 mt-5 overflow-auto">
        <h3 class="font-medium mb-3">รายการจองที่อยู่ในเดือนนี้</h3>
        <table class="table table-report w-full">
            <thead>
                <tr><th>ตู้</th><th>เลขที่ออเดอร์</th><th>ลูกค้า</th><th>ช่วงเช่า</th><th class="text-center">สถานะ</th></tr>
            </thead>
            <tbody>
                @php $hasAny = false; @endphp
                @foreach($rows as $row)
                    @foreach($row->bookings as $b)
                        @php $hasAny = true; @endphp
                        <tr>
                            <td class="font-medium">{{ $row->locker->locker_number }}</td>
                            <td>{{ $b->booking_number }}</td>
                            <td>
                                @if($b->user_id)
                                    <a class="text-primary" href="{{ route('admin.customers.show', $b->user_id) }}">{{ $b->customer_name }}</a>
                                @else
                                    {{ $b->customer_name }}
                                @endif
                                <div class="text-xs text-slate-500">{{ $b->phone ?? '-' }}</div>
                            </td>
                            <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($b->start_date)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($b->end_date)->subDay()->format('d/m/Y') }}</td>
                            <td class="text-center">
                                {{ $b->source === 'admin_assigned' ? 'แอดมินผูกให้' : ([
                                    'pending_payment' => 'รอชำระเงิน', 'paid' => 'ชำระแล้ว', 'active' => 'ใช้งานอยู่', 'completed' => 'สิ้นสุดสัญญา',
                                ][$b->status] ?? $b->status) }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                @if(!$hasAny)
                    <tr><td colspan="5" class="text-center text-slate-500">ไม่มีการจองในเดือนนี้</td></tr>
                @endif
            </tbody>
        </table>
    </div>
@endsection
