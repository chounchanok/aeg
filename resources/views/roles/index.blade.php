@extends('../layout/' . $layout)

@section('subhead')
    <title>รายการ Role ทั้งหมด - AEG</title>
    {{-- ตรวจสอบว่ามีการใช้ Datatables หรือไม่ ถ้าใช่ ให้เพิ่ม CSS/JS ที่จำเป็น --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายการ Role / สิทธิ์การใช้งาน ทั้งหมด</h2>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <a href="{{ route('roles.create') }}" class="btn btn-primary shadow-md mr-2">เพิ่ม Role ใหม่</a>
        </div>

        <!-- BEGIN: Data List -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-form datatable -mt-2" style="background-color: white;">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Role ID</th>
                        <th class="whitespace-nowrap">ชื่อ Role</th>
                        <th class="whitespace-nowrap">คำอธิบาย</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($roles as $role)
                        <tr class="intro-x">
                            <td class="w-20">{{ $role->role_id }}</td>
                            <td>
                                <a href="" class="font-medium whitespace-nowrap">{{ $role->role_name }}</a>
                            </td>
                            <td class="text-slate-500 text-xs">{{ $role->description ?? '-' }}</td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3" href="{{ route('roles.edit', $role->role_id) }}">
                                        <i data-lucide="check-square" class="w-4 h-4 mr-1"></i> แก้ไข
                                    </a>

                                    {{-- ปุ่มลบ --}}
                                    <form action="{{ route('roles.destroy', $role->role_id) }}" method="POST" onsubmit="return confirm('คำเตือน: การลบ Role อาจส่งผลต่อ User ที่ใช้งาน Role นี้ คุณแน่ใจหรือไม่?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center text-danger">
                                            <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ลบ
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- END: Data List -->
    </div>
@endsection

@section('script')
{{-- ตรวจสอบว่ามีการใช้ Datatables หรือไม่ ถ้าใช่ ให้เพิ่ม JS ที่จำเป็น --}}
<script src="https://code.jquery.com/jquery-1.12.4.js" integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU=" crossorigin="anonymous"></script>
<script src="{{ asset('dist/js/datatables.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        new DataTable('.datatable');
    });
</script>
@endsection
