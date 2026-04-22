@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการแจ้งซ่อม - AEG Admin</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
    <style>
        .truncate-text {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายการแจ้งซ่อมทั้งหมด (Service Requests)</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <div class="hidden md:block text-slate-500">แสดงรายการแจ้งซ่อมเรียงตามวันเวลาที่แจ้งล่าสุด</div>
        </div>

        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full datatable">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">เลขที่ใบงาน</th>
                        <th class="whitespace-nowrap">ข้อมูลลูกค้า</th>
                        <th class="whitespace-nowrap">สินค้า / ปัญหา</th>
                        <th class="text-center whitespace-nowrap">วันที่นัดหมาย</th>
                        <th class="text-center whitespace-nowrap">สถานะ</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $req)
                        <tr class="intro-x">
                            <td class="w-40">
                                <div class="font-medium whitespace-nowrap">{{ $req->ticket_number }}</div>
                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ \Carbon\Carbon::parse($req->created_at)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $req->username }}</div>
                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $req->phone ?? 'ไม่มีเบอร์โทร' }}</div>
                            </td>
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $req->product_name }}</div>
                                <div class="text-slate-500 text-xs mt-0.5 truncate-text" title="{{ $req->problem_description }}">
                                    {{ $req->problem_description }}
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="font-medium">{{ \Carbon\Carbon::parse($req->preferred_date)->format('d/m/Y') }}</div>
                                <div class="text-slate-500 text-xs">{{ $req->time_slot }}</div>
                            </td>
                            <td class="text-center">
                                @php
                                    // จัดการสีและข้อความของสถานะ
                                    $statusColors = [
                                        'pending' => 'text-pending',       // สีเหลือง/ส้ม
                                        'assigned' => 'text-primary',      // สีฟ้า
                                        'in_progress' => 'text-warning',   // สีเหลือง
                                        'completed' => 'text-success',     // สีเขียว
                                        'cancelled' => 'text-danger'       // สีแดง
                                    ];
                                    $statusLabels = [
                                        'pending' => 'รอดำเนินการ',
                                        'assigned' => 'จ่ายงานช่างแล้ว',
                                        'in_progress' => 'กำลังดำเนินการ',
                                        'completed' => 'เสร็จสิ้น',
                                        'cancelled' => 'ยกเลิก'
                                    ];
                                    $color = $statusColors[$req->status] ?? 'text-slate-500';
                                    $label = $statusLabels[$req->status] ?? $req->status;
                                @endphp
                                <div class="flex items-center justify-center {{ $color }}">
                                    <i data-lucide="check-circle" class="w-4 h-4 mr-1"></i> {{ $label }}
                                </div>
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3 text-primary" href="{{ route('admin.service-requests.show', $req->id) }}">
                                        <i data-lucide="eye" class="w-4 h-4 mr-1"></i> รายละเอียด
                                    </a>
                                </div>
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
            "language": {
                "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" // แปลงเมนู DataTables เป็นภาษาไทย
            },
            "order": [[ 0, "desc" ]], // เรียงจากคอลัมน์แรก (วันที่) จากใหม่ไปเก่า
            "pageLength": 10 // แสดงหน้าละ 10 รายการ
        });
    });
</script>
@endsection