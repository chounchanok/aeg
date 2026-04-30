@extends('../layout/' . $layout)

@section('subhead')
    <title>รายละเอียดออเดอร์ - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            รายละเอียดคำสั่งซื้อ: <span class="text-primary">{{ $order->order_number }}</span>
        </h2>
        <a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary w-24">กลับ</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show flex items-center mb-2 mt-5" role="alert">
            <i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> {{ session('success') }}
            <button type="button" class="btn-close text-white" data-tw-dismiss="alert" aria-label="Close"> <i data-lucide="x" class="w-4 h-4"></i> </button>
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 lg:col-span-4">
            <div class="box p-5 intro-y">
                <div class="flex items-center border-b border-slate-200/60 pb-5 mb-5">
                    <div class="font-medium text-base truncate">ข้อมูลลูกค้า (Customer)</div>
                </div>
                <div class="flex items-center">
                    <i data-lucide="user" class="w-4 h-4 text-slate-500 mr-2"></i>
                    {{ $order->first_name ? $order->first_name . ' ' . $order->last_name : $order->username }}
                </div>
                <div class="flex items-center mt-3">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-500 mr-2"></i>
                    {{ $order->email }}
                </div>
                <div class="flex items-center mt-3">
                    <i data-lucide="phone" class="w-4 h-4 text-slate-500 mr-2"></i>
                    {{ $order->phone ?? '-' }}
                </div>
            </div>

            <div class="box p-5 intro-y mt-5">
                <div class="flex items-center border-b border-slate-200/60 pb-5 mb-5">
                    <div class="font-medium text-base truncate">ที่อยู่สำหรับบริการ/จัดส่ง</div>
                </div>
                @if($address)
                    <div class="font-medium">{{ $address->contact_name }} ({{ $address->contact_phone }})</div>
                    <div class="text-slate-500 mt-1">
                        {{ $address->address_line }} <br>
                        อ.{{ $address->district }} ต.{{ $address->subdistrict }} <br>
                        จ.{{ $address->province }} {{ $address->zipcode }}
                    </div>
                @else
                    <div class="text-slate-500 text-center">ไม่มีข้อมูลที่อยู่สำหรับออเดอร์นี้</div>
                @endif
            </div>
            
            <div class="box p-5 intro-y mt-5">
                <div class="flex items-center border-b border-slate-200/60 pb-5 mb-5">
                    <div class="font-medium text-base truncate">อัปเดตสถานะคำสั่งซื้อ</div>
                </div>
                <form action="{{ route('admin.orders.status', $order->id) }}" method="POST">
                    @csrf
                    <select name="status" class="form-select mb-4">
                        <option value="pending_payment" {{ $order->status == 'pending_payment' ? 'selected' : '' }}>รอชำระเงิน</option>
                        <option value="paid" {{ $order->status == 'paid' ? 'selected' : '' }}>ชำระเงินแล้ว</option>
                        <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>กำลังดำเนินการ/เตรียมของ</option>
                        <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>เสร็จสิ้น</option>
                        <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>ยกเลิก</option>
                    </select>
                    <button type="submit" class="btn btn-primary w-full">บันทึกสถานะ</button>
                </form>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-8">
            <div class="box p-5 intro-y">
                <div class="flex items-center border-b border-slate-200/60 pb-5 mb-5">
                    <div class="font-medium text-base truncate">รายการสินค้า (Order Items)</div>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th class="border-b-2 dark:border-darkmode-400 whitespace-nowrap">ชื่อสินค้า/บริการ</th>
                                <th class="border-b-2 dark:border-darkmode-400 text-right whitespace-nowrap">ราคาต่อหน่วย</th>
                                <th class="border-b-2 dark:border-darkmode-400 text-center whitespace-nowrap">จำนวน</th>
                                <th class="border-b-2 dark:border-darkmode-400 text-right whitespace-nowrap">รวม (บาท)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orderItems as $item)
                                <tr>
                                    <td class="border-b dark:border-darkmode-400">
                                        <div class="font-medium whitespace-nowrap">{{ $item->product_name }}</div>
                                    </td>
                                    <td class="text-right border-b dark:border-darkmode-400 w-32">{{ number_format($item->price, 2) }}</td>
                                    <td class="text-center border-b dark:border-darkmode-400 w-32">{{ $item->quantity }}</td>
                                    <td class="text-right border-b dark:border-darkmode-400 w-32 font-medium">{{ number_format($item->price * $item->quantity, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end mt-4">
                    <div class="w-full sm:w-1/2 lg:w-1/3" style="padding-left: 20px; padding-right: 20px;">
                        <div class="flex items-center mb-2">
                            <span class="text-slate-500 mr-auto">ยอดรวม (Subtotal)</span>
                            <span class="font-medium">{{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex items-center mb-2">
                            <span class="text-slate-500 mr-auto">ส่วนลด (Discount)</span>
                            <span class="font-medium text-danger">- {{ number_format($order->discount, 2) }}</span>
                        </div>
                        <div class="flex items-center pt-4 border-t border-slate-200/60 dark:border-darkmode-400">
                            <span class="text-base font-medium mr-auto">ยอดสุทธิ (Total)</span>
                            <span class="text-xl font-medium text-primary">{{ number_format($order->total_amount, 2) }} ฿</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection