@extends('../layout/' . $layout)

@section('subhead')
    <title>ปฏิทินตู้ {{ $locker->locker_number }} - AEG Admin</title>
    <style>
        .lk-cal { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 4px; }
        .lk-cal-head { font-size: 12px; font-weight: 600; text-align: center; color: #64748b; padding: 4px 0; }
        .lk-day { min-height: 92px; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 6px; font-size: 11px; background: #fff; overflow: hidden; }
        .lk-day.out { opacity: .4; }
        .lk-day.free { background: #f0fdf4; }
        .lk-day.booked { background: #fef2f2; }
        .lk-day.blocked { background: #f1f5f9; }
        .lk-day.today { outline: 2px solid #2563eb; }
        .lk-day .num { font-weight: 600; font-size: 12px; }
        .lk-chip { display: block; margin-top: 2px; padding: 1px 4px; border-radius: 4px; background: #fee2e2; color: #991b1b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .lk-chip.pending { background: #fef3c7; color: #92400e; }
        .lk-chip.admin { background: #e0e7ff; color: #3730a3; }
        .lk-chip.block { background: #e2e8f0; color: #334155; }
    </style>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-wrap items-center mt-10 gap-2">
        <div class="mr-auto">
            <h2 class="text-lg font-medium">ปฏิทินวันว่าง/ไม่ว่าง · ตู้ {{ $locker->locker_number }}</h2>
            <div class="text-slate-500 text-sm mt-1">{{ $locker->title_th }} · คลิกที่รายการจองเพื่อดูข้อมูลลูกค้า</div>
        </div>
        <a href="{{ route('admin.smart-lockers.calendar', ['month' => $monthStart->format('Y-m')]) }}" class="btn btn-outline-primary">
            <i data-lucide="calendar-range" class="w-4 h-4 mr-1"></i> ปฏิทินรวมทุกตู้
        </a>
        <a href="{{ route('admin.smart-lockers.index') }}" class="btn btn-outline-secondary">กลับรายการตู้</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success show mt-5">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger show mt-5">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 xl:col-span-3 box p-5 h-fit">
            <h3 class="font-medium mb-4">กำหนดช่วงวัน (ปิด/เปิดเอง)</h3>
            <form method="POST" action="{{ route('admin.smart-lockers.availability.update', $locker->id) }}">
                @csrf
                <label class="form-label">ตั้งแต่วันที่</label>
                <input type="date" name="start_date" class="form-control mb-3" min="{{ now()->toDateString() }}" value="{{ old('start_date') }}" required>
                <label class="form-label">ถึงวันที่ (รวมวันนี้)</label>
                <input type="date" name="end_date" class="form-control mb-3" min="{{ now()->toDateString() }}" value="{{ old('end_date') }}" required>
                <label class="form-label">สถานะ</label>
                <select name="availability" class="form-select mb-3" required>
                    <option value="unavailable" @selected(old('availability', 'unavailable') === 'unavailable')>ไม่ว่าง</option>
                    <option value="available" @selected(old('availability') === 'available')>ว่าง (ลบวันที่ปิดเอง)</option>
                </select>
                <label class="form-label">หมายเหตุ</label>
                <input type="text" name="note" class="form-control mb-4" maxlength="255" value="{{ old('note') }}" placeholder="เช่น ปิดปรับปรุง">
                <button type="submit" class="btn btn-primary w-full">บันทึกช่วงวัน</button>
            </form>
            <p class="text-xs text-slate-500 mt-4">วันที่มีการจองจากออเดอร์จะแสดงว่าไม่ว่างอัตโนมัติ และไม่สามารถเปิดทับวันที่จองได้</p>

            <div class="mt-5 text-xs space-y-1">
                <div><span class="lk-chip" style="display:inline-block">LCK-…</span> จองแล้ว (ชำระแล้ว/ใช้งานอยู่)</div>
                <div><span class="lk-chip pending" style="display:inline-block">LCK-…</span> รอชำระเงิน</div>
                <div><span class="lk-chip admin" style="display:inline-block">ADMIN-…</span> แอดมินผูกให้ลูกค้า</div>
                <div><span class="lk-chip block" style="display:inline-block">ปิดวัน</span> ปิดโดยผู้ดูแล</div>
            </div>
        </div>

        <div class="col-span-12 xl:col-span-9 box p-5 overflow-auto">
            <div class="flex items-center mb-4">
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.smart-lockers.availability', [$locker->id, 'month' => $monthStart->copy()->subMonth()->format('Y-m')]) }}">&laquo; เดือนก่อน</a>
                <h3 class="font-medium mx-auto text-base">{{ $monthStart->locale('th')->translatedFormat('F') }} {{ $monthStart->year + 543 }}</h3>
                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.smart-lockers.availability', [$locker->id, 'month' => $monthStart->copy()->addMonth()->format('Y-m')]) }}">เดือนถัดไป &raquo;</a>
            </div>

            <div class="lk-cal" style="min-width: 640px;">
                @foreach(['อา.', 'จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.'] as $dow)
                    <div class="lk-cal-head">{{ $dow }}</div>
                @endforeach

                @foreach($weeks as $week)
                    @foreach($week as $day)
                        @php
                            $cls = $day->bookings->isNotEmpty() ? 'booked' : (($day->block || $day->maintenance) ? 'blocked' : 'free');
                        @endphp
                        <div class="lk-day {{ $cls }} {{ $day->in_month ? '' : 'out' }} {{ $day->is_today ? 'today' : '' }}">
                            <div class="flex justify-between">
                                <span class="num">{{ $day->day }}</span>
                                <span class="{{ $day->is_available ? 'text-success' : 'text-danger' }}">{{ $day->is_available ? 'ว่าง' : 'ไม่ว่าง' }}</span>
                            </div>
                            @foreach($day->bookings as $b)
                                <a href="{{ $b->user_id ? route('admin.customers.show', $b->user_id) : '#' }}"
                                   class="lk-chip {{ $b->source === 'admin_assigned' ? 'admin' : ($b->status === 'pending_payment' ? 'pending' : '') }}"
                                   title="{{ $b->booking_number }} · {{ $b->customer_name }} ({{ $b->phone ?? '-' }}) · {{ $b->start_date }} ถึง {{ \Carbon\Carbon::parse($b->end_date)->subDay()->toDateString() }}">
                                    {{ $b->booking_number }}<br>{{ $b->customer_name }}
                                </a>
                            @endforeach
                            @if($day->block)
                                <span class="lk-chip block" title="{{ $day->block->note }}">ปิดวัน{{ $day->block->note ? ': ' . $day->block->note : '' }}</span>
                            @elseif($day->maintenance)
                                <span class="lk-chip block">ซ่อมบำรุง</span>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>

            <h3 class="font-medium mt-8 mb-3">รายการจองในเดือนนี้</h3>
            <table class="table table-report w-full">
                <thead>
                    <tr>
                        <th>เลขที่ออเดอร์</th>
                        <th>ลูกค้า</th>
                        <th>ช่วงเช่า</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-right">ยอดเงิน</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($monthBookings as $b)
                        <tr>
                            <td class="font-medium">
                                {{ $b->booking_number }}
                                @if($b->renewal_of_booking_id)
                                    <div class="text-xs text-slate-500">ต่ออายุจากรายการ #{{ $b->renewal_of_booking_id }}</div>
                                @endif
                            </td>
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
                                @php
                                    $statusLabel = [
                                        'pending_payment' => 'รอชำระเงิน', 'paid' => 'ชำระแล้ว', 'active' => 'ใช้งานอยู่', 'completed' => 'สิ้นสุดสัญญา',
                                    ][$b->status] ?? $b->status;
                                @endphp
                                {{ $b->source === 'admin_assigned' ? 'แอดมินผูกให้' : $statusLabel }}
                            </td>
                            <td class="text-right">{{ $b->total_amount !== null ? '฿' . number_format($b->total_amount, 2) : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-500">ไม่มีการจองในเดือนนี้</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
