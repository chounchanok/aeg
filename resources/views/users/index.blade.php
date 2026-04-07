@extends('../layout/' . $layout)

@section('subhead')
    <title>รายการผู้ใช้ทั้งหมด - AEG</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายการผู้ใช้ทั้งหมด</h2>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <a href="{{ route('users.create') }}" class="btn btn-primary shadow-md mr-2">เพิ่มผู้ใช้ใหม่</a>
        </div>

        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-form datatable -mt-2" style="background-color: white;">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">ชื่อ-นามสกุล</th>
                        <th class="whitespace-nowrap">อีเมล</th>
                        <th class="text-center whitespace-nowrap">เบอร์โทร</th>
                        <th class="text-center whitespace-nowrap">Role</th>
                        <th class="text-center whitespace-nowrap">สถานะ</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($users as $user)
                        <tr class="intro-x">
                            <td>
                                <a href="" class="font-medium whitespace-nowrap">{{ $user->first_name }} {{ $user->last_name }}</a>
                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $user->user_id }}</div>
                            </td>
                            <td class="whitespace-nowrap">{{ $user->email }}</td>
                            <td class="text-center w-40 whitespace-nowrap">{{ $user->phone_number ?? '-' }}</td>
                            <td class="text-center w-40 whitespace-nowrap">
                                <span class="badge {{ $user->role->role_name == 'Admin' ? 'text-primary' : 'text-slate-500' }}">
                                    {{ $user->role->role_name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="w-40">
                                @php
                                    $status_class = $user->is_active ? 'text-success' : 'text-danger';
                                    $status_text = $user->is_active ? 'Active' : 'Inactive';
                                @endphp
                                <div class="flex items-center justify-center {{ $status_class }}">
                                    <i data-lucide="check-square" class="w-4 h-4 mr-2"></i> {{ $status_text }}
                                </div>
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3" href="{{ route('users.edit', $user->user_id) }}">
                                        <i data-lucide="check-square" class="w-4 h-4 mr-1"></i> แก้ไข
                                    </a>

                                    {{-- ปุ่มลบ (Soft Delete) --}}
                                    <form action="{{ route('users.destroy', $user->user_id) }}" method="POST" onsubmit="return confirm('คุณต้องการลบผู้ใช้นี้หรือไม่?');">
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
        </div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-1.12.4.js" integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU=" crossorigin="anonymous"></script>
<script src="{{ asset('dist/js/datatables.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        new DataTable('.datatable');
    });
</script>
@endsection
