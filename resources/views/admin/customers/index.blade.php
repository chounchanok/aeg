@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการลูกค้า - AEG Admin</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-10">
        <h2 class="text-lg font-medium mr-auto">รายชื่อลูกค้าทั้งหมด (Customers)</h2>
        <a href="{{ route('admin.customers.points-import') }}" class="btn btn-primary shadow-md">
            <i data-lucide="upload" class="w-4 h-4 mr-1"></i> นำเข้าแต้มลูกค้า (Excel)
        </a>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full datatable">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">รหัสอ้างอิง</th>
                        <th class="whitespace-nowrap">ชื่อ-นามสกุล / Username</th>
                        <th class="text-center whitespace-nowrap">เบอร์โทรศัพท์</th>
                        <th class="text-center whitespace-nowrap">วันที่สมัคร</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $c)
                        <tr class="intro-x">
                            <td class="font-medium">CUS-{{ str_pad($c->id, 5, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $c->first_name ? $c->first_name . ' ' . $c->last_name : $c->username }}</div>
                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $c->email }}</div>
                            </td>
                            <td class="text-center">{{ $c->phone ?? '-' }}</td>
                            <td class="text-center">{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y') }}</td>
                            <td class="table-report__action w-56 text-center">
                                <a class="btn btn-sm btn-primary" href="{{ route('admin.customers.show', $c->id) }}">
                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i> ดูรายละเอียด
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
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" }
        });
    });
</script>
@endsection