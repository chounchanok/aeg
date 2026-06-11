@extends('../layout/side-menu')

@section('subhead')
    <title>ประวัติการส่งแจ้งเตือน - AEG Admin</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">ประวัติการส่งแจ้งเตือน (Notifications)</h2>
    
    @if(session('success'))
        <div class="alert alert-success show mb-2 mt-5" role="alert">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary shadow-md mr-2">
                <i data-lucide="send" class="w-4 h-4 mr-1"></i> ส่งแจ้งเตือนใหม่ (Broadcast)
            </a>
        </div>
        
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full datatable">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">วันที่ส่ง</th>
                        <th class="whitespace-nowrap">ส่งถึง (ผู้รับ)</th>
                        <th class="whitespace-nowrap text-center">ประเภท</th>
                        <th class="whitespace-nowrap">หัวข้อ (Title)</th>
                        <th class="whitespace-nowrap text-center">สถานะการอ่าน</th>
                        <th class="text-center w-32">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($notifications as $item)
                        <tr class="intro-x">
                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="font-medium">{{ $item->username ?? 'ไม่ทราบผู้รับ' }} <br><span class="text-xs text-slate-500">{{ $item->phone }}</span></td>
                            <td class="text-center">
                                @if($item->type == 'promotion') <span class="text-danger font-medium">โปรโมชัน</span>
                                @elseif($item->type == 'privilege') <span class="text-warning font-medium">สิทธิพิเศษ</span>
                                @else <span class="text-primary font-medium">ทั่วไป</span> @endif
                            </td>
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $item->title }}</div>
                                <div class="text-slate-500 text-xs mt-0.5 truncate w-48" title="{{ $item->body }}">{{ $item->body }}</div>
                            </td>
                            <td class="text-center">
                                {!! $item->is_read ? '<span class="text-success"><i data-lucide="check-check" class="w-4 h-4 inline"></i> อ่านแล้ว</span>' : '<span class="text-slate-400">ยังไม่อ่าน</span>' !!}
                            </td>
                            <td class="table-report__action w-32 text-center">
                                <form action="{{ route('admin.notifications.delete', $item->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบแจ้งเตือนนี้? (ผู้รับจะไม่เห็นข้อความนี้อีก)');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="flex items-center text-danger mx-auto">
                                        <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ลบ
                                    </button>
                                </form>
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
        $('.datatable').DataTable({ "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" } });
    });
</script>
@endsection