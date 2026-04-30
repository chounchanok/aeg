@extends('../layout/' . $layout)

@section('subhead')
    <title>ประวัติคำสั่งซื้อ - AEG Admin</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">ประวัติคำสั่งซื้อทั้งหมด (Orders)</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full datatable">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">เลขที่ออเดอร์</th>
                        <th class="whitespace-nowrap">ลูกค้า</th>
                        <th class="text-center whitespace-nowrap">วันที่สั่งซื้อ</th>
                        <th class="text-right whitespace-nowrap">ยอดสุทธิ (บาท)</th>
                        <th class="text-center whitespace-nowrap">สถานะ</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr class="intro-x">
                            <td class="font-medium whitespace-nowrap">
                                {{ $order->order_number }}
                                @if($order->payment_gateway)
                                    <div class="text-slate-500 text-xs mt-0.5">ชำระผ่าน: {{ strtoupper($order->payment_gateway) }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $order->first_name ? $order->first_name . ' ' . $order->last_name : $order->username }}</div>
                            </td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="text-right font-medium text-primary">{{ number_format($order->total_amount, 2) }}</td>
                            <td class="text-center">
                                @php
                                    $statusColors = [
                                        'pending_payment' => 'text-warning bg-warning/20',
                                        'paid' => 'text-primary bg-primary/20',
                                        'processing' => 'text-pending bg-pending/20',
                                        'completed' => 'text-success bg-success/20',
                                        'cancelled' => 'text-danger bg-danger/20'
                                    ];
                                    $statusLabels = [
                                        'pending_payment' => 'รอชำระเงิน',
                                        'paid' => 'ชำระเงินแล้ว',
                                        'processing' => 'กำลังดำเนินการ',
                                        'completed' => 'เสร็จสิ้น',
                                        'cancelled' => 'ยกเลิก'
                                    ];
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusColors[$order->status] }}">
                                    {{ $statusLabels[$order->status] }}
                                </span>
                            </td>
                            <td class="table-report__action w-56 text-center">
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.orders.show', $order->id) }}">
                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i> รายละเอียด
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ asset('dist/js/datatables.min.js') }}"></script>
<script>
    $(document).ready(function(){
        $('.datatable').DataTable({
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" },
            "order": [[ 2, "desc" ]] // เรียงวันที่ใหม่ล่าสุดขึ้นก่อน
        });
    });
</script>
@endsection