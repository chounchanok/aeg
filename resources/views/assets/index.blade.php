@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการสินทรัพย์ - AEG</title>
    <link rel="stylesheet" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายการสินทรัพย์ทั้งหมด</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2 justify-between">

            <a href="{{ route('assets.create') }}" class="btn btn-primary shadow-md mr-2">เพิ่มสินทรัพย์ใหม่</a>

            <div class="w-full sm:w-auto mt-3 sm:mt-0 sm:ml-auto md:ml-0">
                <form action="{{ route('assets.index') }}" method="GET" class="flex gap-2">
                    <div class="relative text-slate-500">
                        <select name="house_id" class="form-select w-56 box pr-10" onchange="this.form.submit()">
                            <option value="">-- บ้านทุกหลัง --</option>
                            @foreach ($houses as $house)
                                <option value="{{ $house->house_id }}" {{ request('house_id') == $house->house_id ? 'selected' : '' }}>
                                    {{ $house->house_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- (Optional) ปุ่มรีเซ็ต Filter --}}
                    @if(request('project_id'))
                        <a href="{{ route('assets.index') }}" class="btn btn-outline-secondary">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-form datatable -mt-2" style="background-color: white;">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">ชื่อสินทรัพย์</th>
                        <th class="whitespace-nowrap">รหัส</th>
                        <th class="text-center whitespace-nowrap">สถานะ</th>
                        <th class="text-center whitespace-nowrap">บ้าน</th>
                        <th class="text-center whitespace-nowrap">ผู้ดูแล</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets as $asset)
                        <tr class="intro-x">
                            <td>
                                <a href="{{ route('assets.edit', $asset->asset_id) }}" class="font-medium whitespace-nowrap">{{ $asset->asset_name }}</a>
                            </td>
                            <td>{{ $asset->asset_code ?? '-' }}</td>
                            <td class="w-40 text-center">
                                {{-- เพิ่มสีให้สถานะดูง่ายขึ้น --}}
                                <div class="flex items-center justify-center {{ $asset->status == 'available' ? 'text-success' : 'text-danger' }}">
                                    {{ $asset->status }}
                                </div>
                            </td>
                            <td class="text-center">{{ $asset->house->house_name ?? '-' }}</td>
                            <td class="text-center">
                                @if($asset->assigned_users_list->isNotEmpty())
                                    {{ $asset->assigned_users_list->map(function($user){
                                        return $user->first_name . ' ' . $user->last_name;
                                    })->implode(', ') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3" href="{{ route('report.asset.create', $asset->asset_id) }}">
                                        <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มรายงานความคืบหน้า
                                    </a>
                                    <a class="flex items-center mr-3" href="{{ route('assets.edit', $asset->asset_id) }}">
                                        <i data-lucide="check-square" class="w-4 h-4 mr-1"></i> แก้ไข
                                    </a>
                                    <a class="flex items-center text-danger" href="javascript:;" data-tw-toggle="modal"
                                        data-tw-target="#delete-confirmation-modal-{{ $asset->asset_id }}">
                                        <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ลบ
                                    </a>
                                </div>
                            </td>
                        </tr>

                        <div id="delete-confirmation-modal-{{ $asset->asset_id }}" class="modal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-body p-0">
                                        <div class="p-5 text-center">
                                            <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                                            <div class="text-3xl mt-5">คุณแน่ใจหรือไม่?</div>
                                            <div class="text-slate-500 mt-2">คุณต้องการลบสินทรัพย์ "{{ $asset->asset_name }}" ใช่หรือไม่?</div>
                                        </div>
                                        <div class="px-5 pb-8 text-center">
                                            <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                                            <form action="{{ route('assets.destroy', $asset->asset_id) }}" method="POST" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger w-24">ลบ</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr class="intro-x">
                            <td colspan="6" class="text-center p-5">ไม่พบข้อมูลสินทรัพย์ในระบบ</td>
                        </tr>
                    @endforelse
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
        // ถ้าใช้ Filter จาก Server-side แล้ว DataTables Client-side อาจจะไม่จำเป็นต้องใช้ Search ของมันซ้อนทับกัน
        // แต่ถ้าข้อมูลไม่เยอะ ใช้ DataTables ช่วย sort ก็สะดวกดีครับ
        new DataTable('.datatable', {
            "ordering": false, // ปิด ordering ถ้ารู้สึกว่ามันตีกับ query ที่เรา order มาจาก controller
            "info": false,
            "lengthChange": false,
            "searching": false // ปิด search ของ datatable เพราะเราอาจจะทำ search เองข้างบน (ถ้าต้องการเปิด ให้เปลี่ยนเป็น true)
        });
    });
</script>
@endsection
