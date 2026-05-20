@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการตู้เซฟนิรภัย - AEG Admin</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">ระบบจัดการตู้เซฟนิรภัย (Smart Lockers)</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap items-center mt-2">
            <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-modal">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มตู้เซฟใหม่
            </button>
        </div>
        
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full">
                <thead>
                    <tr>
                        <th class="w-20">รูปภาพ</th>
                        <th>เลขตู้ (Locker No.)</th>
                        <th>ประเภท</th>
                        <th>ราคา/เดือน</th>
                        <th class="text-center">สถานะการเช่า</th>
                        <th class="text-center">แสดงผล</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lockers as $locker)
                        <tr class="intro-x">
                            <td>
                                <div class="w-12 h-12 image-fit zoom-in mx-auto">
                                    <img alt="Locker" class="rounded-md border p-1" src="{{ $locker->image_url ?? asset('dist/images/preview-1.jpg') }}">
                                </div>
                            </td>
                            <td class="font-medium whitespace-nowrap">{{ $locker->locker_number }}</td>
                            <td>
                                <span class="px-2 py-1 rounded-full text-xs {{ $locker->type == 'PRIME' ? 'bg-blue-100 text-blue-600' : 'bg-amber-100 text-amber-600' }}">{{ $locker->type }}</span>
                            </td>
                            <td>฿{{ number_format($locker->price, 2) }}</td>
                            <td class="text-center">
                                @if($locker->status == 'available')
                                    <span class="text-success"><i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i> ว่าง</span>
                                @elseif($locker->status == 'rented')
                                    <span class="text-warning"><i data-lucide="lock" class="w-4 h-4 inline mr-1"></i> ถูกเช่า</span>
                                @else
                                    <span class="text-danger"><i data-lucide="tool" class="w-4 h-4 inline mr-1"></i> ซ่อมบำรุง</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center {{ $locker->is_active ? 'text-success' : 'text-danger' }}">
                                    <i data-lucide="power" class="w-4 h-4 mr-1"></i> {{ $locker->is_active ? 'เปิด' : 'ปิด' }}
                                </div>
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <button class="flex items-center mr-3 text-primary btn-edit" data-tw-toggle="modal" data-tw-target="#edit-modal" data-locker="{{ json_encode($locker) }}">
                                        <i data-lucide="edit" class="w-4 h-4 mr-1"></i> แก้ไข
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div id="add-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.smart-lockers.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">เพิ่มตู้เซฟนิรภัย</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">เลขตู้ (Locker No.)</label>
                        <input name="locker_number" type="text" class="form-control" required placeholder="เช่น PR-001">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ประเภทตู้</label>
                        <select name="type" class="form-select" required>
                            <option value="PRIME">PRIME</option>
                            <option value="PRIVILEGE">PRIVILEGE</option>
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">ชื่อตู้เซฟ (TH)</label>
                        <input name="title_th" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ราคาเช่า / เดือน</label>
                        <input name="price" type="number" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">สถานะการเช่า</label>
                        <select name="status" class="form-select">
                            <option value="available">ว่าง (Available)</option>
                            <option value="rented">ถูกเช่าแล้ว (Rented)</option>
                            <option value="maintenance">ซ่อมบำรุง (Maintenance)</option>
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">รูปภาพตู้เซฟ</label>
                        <input name="image" type="file" class="form-control" accept="image/*">
                    </div>
                    <div class="col-span-12 flex items-center mt-3">
                        <input name="is_active" type="checkbox" class="form-check-input border mr-2" checked value="1">
                        <label>เปิดแสดงผลในแอป</label>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <div id="edit-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="edit-form" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">แก้ไขตู้เซฟ</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">เลขตู้</label>
                        <input name="locker_number" id="edit_locker_number" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ประเภทตู้</label>
                        <select name="type" id="edit_type" class="form-select" required>
                            <option value="PRIME">PRIME</option>
                            <option value="PRIVILEGE">PRIVILEGE</option>
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">ชื่อตู้เซฟ (TH)</label>
                        <input name="title_th" id="edit_title_th" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ราคาเช่า / เดือน</label>
                        <input name="price" id="edit_price" type="number" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">สถานะการเช่า</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="available">ว่าง (Available)</option>
                            <option value="rented">ถูกเช่าแล้ว (Rented)</option>
                            <option value="maintenance">ซ่อมบำรุง (Maintenance)</option>
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">เปลี่ยนรูปภาพ (เว้นว่างถ้าไม่เปลี่ยน)</label>
                        <input name="image" type="file" class="form-control" accept="image/*">
                    </div>
                    <div class="col-span-12 flex items-center mt-3">
                        <input name="is_active" id="edit_is_active" type="checkbox" class="form-check-input border mr-2" value="1">
                        <label>เปิดแสดงผลในแอป</label>
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
    $('.btn-edit').on('click', function() {
        let locker = $(this).data('locker');
        $('#edit-form').attr('action', `{{ url('/admin/smart-lockers') }}/${locker.id}/update`);
        $('#edit_locker_number').val(locker.locker_number);
        $('#edit_type').val(locker.type);
        $('#edit_title_th').val(locker.title_th);
        $('#edit_price').val(locker.price);
        $('#edit_status').val(locker.status);
        $('#edit_is_active').prop('checked', locker.is_active == 1);
    });
</script>
@endsection