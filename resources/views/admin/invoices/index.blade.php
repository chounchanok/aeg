@extends('../layout/' . $layout)

@section('subhead')
    <title>ใบแจ้งหนี้ - AEG Admin</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">ใบแจ้งหนี้รายเดือน (Invoices)</h2>
    <div class="text-slate-500 mt-1">
        ใบแจ้งหนี้ที่ระบบออกให้อัตโนมัติทุกวัน (จากสัญญารายเดือน และสรุปยอดลูกค้าวางบิล) — กดดูรายละเอียดเพื่อยืนยันการชำระเงิน (โอนเงิน) หรือดูสถานะการชำระผ่านช่องทางออนไลน์
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 sm:col-span-4 intro-y">
            <div class="box p-5">
                <div class="text-slate-500">รอชำระเงิน</div>
                <div class="text-2xl font-medium mt-1">{{ $summary['pending'] }} ใบ</div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-4 intro-y">
            <div class="box p-5">
                <div class="text-slate-500">เกินกำหนดชำระ</div>
                <div class="text-2xl font-medium mt-1 text-danger">{{ $summary['overdue'] }} ใบ</div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-4 intro-y">
            <div class="box p-5">
                <div class="text-slate-500">ชำระแล้วเดือนนี้</div>
                <div class="text-2xl font-medium mt-1 text-success">{{ $summary['paid_this_month'] }} ใบ</div>
            </div>
        </div>

        <div class="intro-y col-span-12 flex flex-wrap items-center justify-between mt-2">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <select name="type" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">ทุกประเภท</option>
                    <option value="contract" {{ ($filters['type'] ?? '') === 'contract' ? 'selected' : '' }}>สัญญารายเดือน</option>
                    <option value="statement" {{ ($filters['type'] ?? '') === 'statement' ? 'selected' : '' }}>สรุปยอดลูกค้าวางบิล</option>
                </select>
                <select name="status" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">ทุกสถานะ</option>
                    <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>รอชำระเงิน</option>
                    <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>ชำระแล้ว</option>
                    <option value="overdue" {{ ($filters['status'] ?? '') === 'overdue' ? 'selected' : '' }}>เกินกำหนด</option>
                    <option value="cancelled" {{ ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                </select>
                <input type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="form-control w-auto" onchange="this.form.submit()">
                @if (($filters['type'] ?? '') || ($filters['status'] ?? '') || ($filters['month'] ?? ''))
                    <a href="{{ route('admin.invoices') }}" class="text-slate-500 text-sm">ล้างตัวกรอง</a>
                @endif
            </form>

            <button class="btn btn-primary shadow-md" data-tw-toggle="modal" data-tw-target="#generate-now-modal">
                <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i> สร้างใบแจ้งหนี้ตอนนี้
            </button>
        </div>

        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full">
                <thead>
                    <tr>
                        <th>เลขที่ใบแจ้งหนี้</th>
                        <th>ประเภท</th>
                        <th>ลูกค้า</th>
                        <th>งวดเดือน</th>
                        <th class="text-right">ยอดรวม</th>
                        <th class="text-center">ช่องทางจ่าย</th>
                        <th class="text-center">ครบกำหนด</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $inv)
                        @php
                            $isOverdue = $inv->status === 'pending' && \Carbon\Carbon::parse($inv->due_date)->isPast();
                            $statusMap = [
                                'pending' => [$isOverdue ? 'text-danger' : 'text-warning', $isOverdue ? 'เกินกำหนด' : 'รอชำระเงิน'],
                                'paid' => ['text-success', 'ชำระแล้ว'],
                                'overdue' => ['text-danger', 'เกินกำหนด'],
                                'cancelled' => ['text-slate-500', 'ยกเลิก'],
                            ];
                            [$cls, $label] = $statusMap[$inv->status] ?? ['text-slate-500', $inv->status];
                        @endphp
                        <tr class="intro-x">
                            <td class="whitespace-nowrap font-medium">{{ $inv->invoice_number }}</td>
                            <td>{{ $inv->type === 'contract' ? 'สัญญา (' . $inv->contract_number . ')' : 'สรุปยอด' }}</td>
                            <td class="whitespace-nowrap">{{ trim($inv->first_name . ' ' . $inv->last_name) ?: $inv->username }}</td>
                            <td>{{ \Carbon\Carbon::parse($inv->billing_month)->translatedFormat('m/Y') }}</td>
                            <td class="text-right">{{ number_format($inv->total_amount, 2) }}</td>
                            <td class="text-center">{{ $inv->payment_method === 'gateway' ? 'ออนไลน์' : 'โอนเงิน' }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($inv->due_date)->format('d/m/Y') }}</td>
                            <td class="text-center"><span class="{{ $cls }}">{{ $label }}</span></td>
                            <td class="table-report__action w-20">
                                <div class="flex justify-center items-center">
                                    <a href="{{ route('admin.invoices.show', $inv->id) }}" class="flex items-center text-primary">
                                        <i data-lucide="eye" class="w-4 h-4 mr-1"></i> ดู
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-slate-500 py-5">ยังไม่มีใบแจ้งหนี้ในระบบ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Generate Now Modal -->
    <div id="generate-now-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.invoices.generate-now') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">สร้างใบแจ้งหนี้ตอนนี้</h2></div>
                <div class="modal-body">
                    <div class="text-slate-500 text-sm mb-3">
                        ใช้ปุ่มนี้เพื่อสร้างใบแจ้งหนี้ทันทีโดยไม่ต้องรอ cron รายวัน (เช่น กรณียังไม่ได้ตั้ง Task Scheduler บนเครื่อง production หรือต้องการสร้างย้อนหลัง)
                        ระบบจะข้ามรายการที่เคยออกใบแจ้งหนี้ของเดือนนั้นไปแล้วให้อัตโนมัติ ไม่ออกซ้ำ
                    </div>
                    <label class="form-label">เดือนที่ต้องการสร้าง (ว่าง = ให้ระบบเลือกเดือนที่เหมาะสมเองแบบเดียวกับ cron)</label>
                    <input type="month" name="month" class="form-control">
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">สร้างใบแจ้งหนี้</button>
                </div>
            </form>
        </div>
    </div>
@endsection
