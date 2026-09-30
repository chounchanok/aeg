@extends('../layout/' . $layout)

@section('subhead')
    <title>สัญญา/บริการรายเดือน - AEG Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">สัญญา/บริการรายเดือน (Service Contracts)</h2>
    <div class="text-slate-500 mt-1">
        สร้างสัญญาที่ต้องเรียกเก็บเงินลูกค้าทุกเดือน (เช่น ค่าบำรุงรักษารายเดือน) — ระบบจะออกใบแจ้งหนี้ให้อัตโนมัติทุกเดือนตามวันที่กำหนด
        ดูใบแจ้งหนี้ที่ออกจากสัญญาแต่ละอันได้ที่เมนู "ใบแจ้งหนี้"
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap items-center mt-2">
            <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-modal">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> สร้างสัญญาใหม่
            </button>
        </div>

        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full">
                <thead>
                    <tr>
                        <th>เลขที่สัญญา</th>
                        <th>ลูกค้า</th>
                        <th>ชื่อสัญญา</th>
                        <th class="text-right">ยอดเรียกเก็บ/เดือน</th>
                        <th class="text-center">วันออกบิล</th>
                        <th class="text-center">ช่องทางจ่าย</th>
                        <th class="text-center">ใบแจ้งหนี้ที่ออกแล้ว</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contracts as $contract)
                        <tr class="intro-x">
                            <td class="whitespace-nowrap font-medium">{{ $contract->contract_number }}</td>
                            <td class="whitespace-nowrap">{{ trim($contract->first_name . ' ' . $contract->last_name) ?: $contract->username }}</td>
                            <td>{{ $contract->title }}</td>
                            <td class="text-right">{{ number_format($contract->billing_amount, 2) }}</td>
                            <td class="text-center">ทุกวันที่ {{ $contract->billing_day }}</td>
                            <td class="text-center">{{ $contract->payment_method === 'gateway' ? 'ชำระออนไลน์' : 'โอนเงิน' }}</td>
                            <td class="text-center">{{ $invoiceCounts[$contract->id] ?? 0 }} ใบ</td>
                            <td class="text-center">
                                @php
                                    $statusMap = ['active' => ['text-success', 'ใช้งานอยู่'], 'paused' => ['text-warning', 'พักชั่วคราว'], 'cancelled' => ['text-danger', 'ยกเลิกแล้ว'], 'completed' => ['text-slate-500', 'สิ้นสุดแล้ว']];
                                    [$cls, $label] = $statusMap[$contract->status] ?? ['text-slate-500', $contract->status];
                                @endphp
                                <span class="{{ $cls }}">{{ $label }}</span>
                            </td>
                            <td class="table-report__action w-24">
                                <div class="flex justify-center items-center">
                                    <button type="button" class="flex items-center text-primary btn-edit"
                                        data-tw-toggle="modal" data-tw-target="#edit-modal"
                                        data-contract="{{ json_encode([
                                            'id' => $contract->id,
                                            'contract_number' => $contract->contract_number,
                                            'customer_name' => trim($contract->first_name . ' ' . $contract->last_name) ?: $contract->username,
                                            'title' => $contract->title,
                                            'description' => $contract->description,
                                            'billing_amount' => $contract->billing_amount,
                                            'billing_day' => $contract->billing_day,
                                            'payment_method' => $contract->payment_method,
                                            'end_date' => $contract->end_date,
                                            'status' => $contract->status,
                                        ]) }}">
                                        <i data-lucide="edit" class="w-4 h-4 mr-1"></i> แก้ไข
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-slate-500 py-5">ยังไม่มีสัญญารายเดือน — กด "สร้างสัญญาใหม่" เพื่อเริ่มสร้างสัญญาแรก</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="add-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.service-contracts.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">สร้างสัญญาใหม่</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label class="form-label">ลูกค้า</label>
                        <select name="user_id" class="form-select select2-customers" required style="width: 100%">
                            <option></option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">{{ trim($c->first_name . ' ' . $c->last_name) ?: $c->username }} ({{ $c->username }}){{ $c->tax_id ? ' — เลขผู้เสียภาษี ' . $c->tax_id : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">ชื่อสัญญา</label>
                        <input name="title" type="text" class="form-control" required placeholder="เช่น สัญญาบำรุงรักษารายเดือน - กล้องวงจรปิด">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">รายละเอียด (ถ้ามี)</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">ยอดเรียกเก็บ/เดือน (บาท)</label>
                        <input name="billing_amount" type="number" step="0.01" min="0.01" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">วันออกบิล (1-28)</label>
                        <input name="billing_day" type="number" min="1" max="28" value="1" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">ช่องทางจ่ายเงิน</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="bank_transfer">โอนเงิน (ตรวจสลิปเอง)</option>
                            <option value="gateway">ชำระออนไลน์ (บัตร/พร้อมเพย์)</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">วันเริ่มสัญญา</label>
                        <input name="start_date" type="date" class="form-control" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">วันสิ้นสุดสัญญา (ว่าง = ไม่มีกำหนด)</label>
                        <input name="end_date" type="date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="edit-form" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">แก้ไขสัญญา <span id="edit_contract_number" class="text-slate-500"></span></h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <div class="text-slate-500 text-xs">ลูกค้า: <span id="edit_customer_name" class="font-medium text-slate-700"></span> (แก้ไขลูกค้าของสัญญาที่สร้างแล้วไม่ได้ — ถ้าต้องเปลี่ยนลูกค้า กรุณายกเลิกสัญญานี้แล้วสร้างใหม่)</div>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">ชื่อสัญญา</label>
                        <input name="title" id="edit_title" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">รายละเอียด (ถ้ามี)</label>
                        <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">ยอดเรียกเก็บ/เดือน (บาท)</label>
                        <input name="billing_amount" id="edit_billing_amount" type="number" step="0.01" min="0.01" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">วันออกบิล (1-28)</label>
                        <input name="billing_day" id="edit_billing_day" type="number" min="1" max="28" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">ช่องทางจ่ายเงิน</label>
                        <select name="payment_method" id="edit_payment_method" class="form-select" required>
                            <option value="bank_transfer">โอนเงิน (ตรวจสลิปเอง)</option>
                            <option value="gateway">ชำระออนไลน์ (บัตร/พร้อมเพย์)</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">วันสิ้นสุดสัญญา (ว่าง = ไม่มีกำหนด)</label>
                        <input name="end_date" id="edit_end_date" type="date" class="form-control">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">สถานะ</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="active">ใช้งานอยู่</option>
                            <option value="paused">พักชั่วคราว (หยุดออกบิลชั่วคราว)</option>
                            <option value="cancelled">ยกเลิก</option>
                            <option value="completed">สิ้นสุดสัญญาแล้ว</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        $('.select2-customers').select2({ dropdownParent: $('#add-modal'), placeholder: 'ค้นหาลูกค้าด้วยชื่อ/username' });

        $('.btn-edit').on('click', function () {
            let c = $(this).data('contract');
            $('#edit-form').attr('action', `{{ url('/admin/service-contracts') }}/${c.id}/update`);
            $('#edit_contract_number').text('(' + c.contract_number + ')');
            $('#edit_customer_name').text(c.customer_name);
            $('#edit_title').val(c.title);
            $('#edit_description').val(c.description);
            $('#edit_billing_amount').val(c.billing_amount);
            $('#edit_billing_day').val(c.billing_day);
            $('#edit_payment_method').val(c.payment_method);
            $('#edit_end_date').val(c.end_date);
            $('#edit_status').val(c.status);
        });
    });
</script>
@endsection
