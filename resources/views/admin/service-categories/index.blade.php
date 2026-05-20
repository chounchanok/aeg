@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการหมวดหมู่บริการ - AEG Admin</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">หมวดหมู่บริการ (Service Categories)</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-category-modal">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มหมวดหมู่ใหม่
            </button>
        </div>
        
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full datatable">
                <thead>
                    <tr>
                        <th class="w-16 text-center">ลำดับ</th>
                        <th class="w-20 text-center">ไอคอน</th>
                        <th>ชื่อหมวดหมู่ (TH / EN)</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $cat)
                        <tr class="intro-x">
                            <td class="text-center font-medium">{{ $cat->sort_order }}</td>
                            <td>
                                <div class="w-12 h-12 image-fit zoom-in mx-auto">
                                    <img alt="Icon" class="rounded-md border p-1" src="{{ $cat->image_url ?? asset('dist/images/preview-1.jpg') }}">
                                </div>
                            </td>
                            <td>
                                <div class="font-medium text-base whitespace-nowrap">{{ $cat->title_th }}</div>
                                <div class="text-slate-500 text-sm whitespace-nowrap mt-0.5">{{ $cat->title_en ?? '-' }}</div>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center {{ $cat->is_active ? 'text-success' : 'text-danger' }}">
                                    <i data-lucide="power" class="w-4 h-4 mr-1"></i> {{ $cat->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                </div>
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <button type="button" class="btn-edit-category flex items-center mr-3 text-primary" data-tw-toggle="modal" data-tw-target="#edit-category-modal" data-category="{{ json_encode($cat) }}">
                                        <i data-lucide="edit" class="w-4 h-4 mr-1"></i> แก้ไข
                                    </button>
                                    <form action="{{ route('admin.service-categories.delete', $cat->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบหมวดหมู่นี้?');">
                                        @csrf
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

    <!-- BEGIN: Modal เพิ่มหมวดหมู่ -->
    <div id="add-category-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.service-categories.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">เพิ่มหมวดหมู่บริการ</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อหมวดหมู่ (TH) <span class="text-danger">*</span></label>
                        <input name="title_th" type="text" class="form-control" required placeholder="เช่น เครื่องปรับอากาศ">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อหมวดหมู่ (EN)</label>
                        <input name="title_en" type="text" class="form-control" placeholder="เช่น Air Conditioner">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ลำดับการแสดงผล</label>
                        <input name="sort_order" type="number" class="form-control" value="0">
                        <div class="text-xs text-slate-500 mt-1">ค่าน้อยสุดจะแสดงก่อน</div>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ไอคอนหมวดหมู่</label>
                        <input name="image" type="file" class="form-control" accept="image/*">
                    </div>
                    <div class="col-span-12 flex items-center mt-3">
                        <input name="is_active" type="checkbox" class="form-check-input border mr-2" id="is_active" checked value="1">
                        <label class="cursor-pointer select-none" for="is_active">เปิดใช้งานให้แอปพลิเคชันเห็นทันที</label>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Modal -->

    <!-- BEGIN: Modal แก้ไขหมวดหมู่ -->
    <div id="edit-category-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="edit-category-form" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">แก้ไขหมวดหมู่บริการ</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label class="form-label">กลุ่มของหมวดหมู่นี้ (Group Type)</label>
                        <select name="group" class="form-select" required>
                            <option value="equipment">สินค้า / อุปกรณ์ทั่วไป (Equipment)</option>
                            <option value="package">แพ็กเกจดูแลรักษารายปี (Package)</option>
                            <option value="service">บริการทั่วไป / เรียกช่างหน้างาน (Service)</option>
                        </select>
                        <small class="text-slate-500 block mt-1">ช่วยแยกประเภทให้ระบบรู้ว่าหมวดหมู่นี้เป็นของกลุ่มสินค้าตัวไหน</small>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อหมวดหมู่ (TH) <span class="text-danger">*</span></label>
                        <input name="title_th" id="edit_title_th" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อหมวดหมู่ (EN)</label>
                        <input name="title_en" id="edit_title_en" type="text" class="form-control">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ลำดับการแสดงผล</label>
                        <input name="sort_order" id="edit_sort_order" type="number" class="form-control">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">เปลี่ยนไอคอน (ถ้าไม่เปลี่ยนให้เว้นว่าง)</label>
                        <input name="image" type="file" class="form-control" accept="image/*">
                        <div class="mt-2 text-xs text-slate-500">
                            ไอคอนปัจจุบัน: <a id="current_icon_img" href="#" target="_blank" class="text-primary underline">ดูรูปภาพ</a>
                        </div>
                    </div>
                    <div class="col-span-12 flex items-center mt-3">
                        <input name="is_active" type="checkbox" class="form-check-input border mr-2" id="edit_is_active" value="1">
                        <label class="cursor-pointer select-none" for="edit_is_active">เปิดใช้งาน</label>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Modal -->
@endsection

@section('script')
<script src="{{ asset('dist/js/datatables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.datatable').DataTable({
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" },
            "order": [[ 0, "asc" ]] // เรียงลำดับจากคอลัมน์แรก (Sort Order)
        });

        // ดึงข้อมูลใส่ฟอร์มตอนกดแก้ไข
        $('.btn-edit-category').on('click', function() {
            let category = $(this).data('category');
            
            // เปลี่ยน URL ฟอร์ม
            $('#edit-category-form').attr('action', `{{ url('/admin/service-categories') }}/${category.id}/update`);
            
            // ใส่ข้อมูลลงช่อง
            $('#edit_title_th').val(category.title_th);
            $('#edit_title_en').val(category.title_en);
            $('#edit_sort_order').val(category.sort_order);
            $('#edit_is_active').prop('checked', category.is_active == 1);
            $('#current_icon_img').attr('href', category.image_url);
        });
    });
</script>
@endsection