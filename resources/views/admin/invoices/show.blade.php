@extends('../layout/' . $layout)

@section('subhead')
    <title>ใบแจ้งหนี้ {{ $invoice->invoice_number }} - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="flex items-center mt-10">
        <h2 class="text-lg font-medium mr-auto">ใบแจ้งหนี้ #{{ $invoice->invoice_number }}</h2>
        <a href="{{ route('admin.invoices') }}" class="text-primary flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> กลับไปหน้ารายการ
        </a>
    </div>

    @php
        $isOverdue = $invoice->status === 'pending' && \Carbon\Carbon::parse($invoice->due_date)->isPast();
        $statusMap = [
            'pending' => [$isOverdue ? 'text-danger' : 'text-warning', $isOverdue ? 'เกินกำหนดชำระ' : 'รอชำระเงิน'],
            'paid' => ['text-success', 'ชำระแล้ว'],
            'overdue' => ['text-danger', 'เกินกำหนด'],
            'cancelled' => ['text-slate-500', 'ยกเลิก'],
        ];
        [$cls, $label] = $statusMap[$invoice->status] ?? ['text-slate-500', $invoice->status];
    @endphp

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 lg:col-span-8">
            <div class="intro-y box p-5">
                <div class="flex items-center justify-between border-b border-slate-200/60 pb-3 mb-3">
                    <div>
                        <div class="font-medium text-base">{{ $invoice->type === 'contract' ? 'ใบแจ้งหนี้จากสัญญารายเดือน' : 'ใบแจ้งหนี้สรุปยอดรายเดือน' }}</div>
                        @if ($invoice->type === 'contract')
                            <div class="text-slate-500 text-sm">สัญญา: {{ $invoice->contract_number }} — {{ $invoice->contract_title }}</div>
                        @endif
                    </div>
                    <span class="{{ $cls }} font-medium">{{ $label }}</span>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm mb-4">
                    <div><span class="text-slate-500">งวดเดือน:</span> {{ \Carbon\Carbon::parse($invoice->billing_month)->translatedFormat('m/Y') }}</div>
                    <div><span class="text-slate-500">วันที่ออก:</span> {{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</div>
                    <div><span class="text-slate-500">ครบกำหนดชำระ:</span> {{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</div>
                    <div><span class="text-slate-500">ช่องทางจ่าย:</span> {{ $invoice->payment_method === 'gateway' ? 'ชำระออนไลน์' : 'โอนเงิน' }}</div>
                    @if ($invoice->status === 'paid')
                        <div><span class="text-slate-500">ชำระเมื่อ:</span> {{ \Carbon\Carbon::parse($invoice->paid_at)->format('d/m/Y H:i') }}</div>
                        <div><span class="text-slate-500">อ้างอิงการชำระ:</span> {{ $invoice->gateway_transaction_id ?: '-' }}</div>
                    @endif
                </div>

                <div class="font-medium mb-2">รายการ</div>
                <table class="table table-report w-full mb-4">
                    <thead>
                        <tr>
                            <th>รายการ</th>
                            <th class="text-right">จำนวนเงิน</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-right">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex justify-end">
                    <div class="w-64 text-sm">
                        <div class="flex justify-between py-1"><span class="text-slate-500">ยอดก่อน VAT</span><span>{{ number_format($invoice->subtotal, 2) }}</span></div>
                        <div class="flex justify-between py-1"><span class="text-slate-500">VAT 7%</span><span>{{ number_format($invoice->vat_amount, 2) }}</span></div>
                        <div class="flex justify-between py-2 border-t border-slate-200/60 font-medium text-base"><span>ยอดรวมสุทธิ</span><span>{{ number_format($invoice->total_amount, 2) }} บาท</span></div>
                    </div>
                </div>

                @if ($invoice->payment_slip_url)
                    <div class="mt-4 border-t border-slate-200/60 pt-3">
                        <div class="font-medium mb-2">สลิปโอนเงินที่ลูกค้าแนบมา</div>
                        <a href="{{ $invoice->payment_slip_url }}" target="_blank">
                            <img src="{{ $invoice->payment_slip_url }}" class="max-w-xs rounded border border-slate-200/60">
                        </a>
                    </div>
                @endif

                @if ($invoice->admin_note)
                    <div class="mt-4 border-t border-slate-200/60 pt-3">
                        <div class="font-medium mb-1">บันทึกจากแอดมิน</div>
                        <div class="text-slate-500 text-sm">{{ $invoice->admin_note }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4">
            <div class="intro-y box p-5">
                <div class="font-medium mb-2">ข้อมูลลูกค้า (ณ วันที่ออกใบแจ้งหนี้)</div>
                <div class="text-sm space-y-1">
                    <div>{{ $invoice->customer_name_snapshot }} ({{ $invoice->username }})</div>
                    @if ($invoice->customer_tax_id_snapshot)
                        <div class="text-slate-500">เลขผู้เสียภาษี: {{ $invoice->customer_tax_id_snapshot }}</div>
                    @endif
                    @if ($invoice->customer_branch_snapshot)
                        <div class="text-slate-500">สาขา: {{ $invoice->customer_branch_snapshot }}</div>
                    @endif
                    @if ($invoice->customer_address_snapshot)
                        <div class="text-slate-500">{{ $invoice->customer_address_snapshot }}</div>
                    @endif
                    @if ($invoice->phone)
                        <div class="text-slate-500">โทร: {{ $invoice->phone }}</div>
                    @endif
                </div>
            </div>

            @if ($invoice->status === 'pending')
                <div class="intro-y box p-5 mt-5">
                    <div class="font-medium mb-3">ยืนยันการชำระเงิน</div>
                    <form action="{{ route('admin.invoices.mark-paid', $invoice->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">เลขอ้างอิงการชำระ (ถ้ามี)</label>
                            <input type="text" name="transaction_ref" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">บันทึกเพิ่มเติม (ถ้ามี)</label>
                            <textarea name="admin_note" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-full" onclick="return confirm('ยืนยันว่าได้รับเงินสำหรับใบแจ้งหนี้นี้แล้ว?')">
                            <i data-lucide="check" class="w-4 h-4 mr-1"></i> ยืนยันว่าชำระแล้ว
                        </button>
                    </form>
                    <form action="{{ route('admin.invoices.cancel', $invoice->id) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-full text-danger" onclick="return confirm('ยืนยันยกเลิกใบแจ้งหนี้นี้?')">
                            ยกเลิกใบแจ้งหนี้นี้
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
