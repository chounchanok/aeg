@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการแบนเนอร์ - AEG Admin</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">ระบบจัดการแบนเนอร์ (Banners)</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-banner-modal">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> อัปโหลดแบนเนอร์ใหม่
            </button>

            <div class="dropdown ml-2">
                <select id="location_filter" class="form-select w-60">
                    <option {{ (!empty($locations) ? $locations == 'main' : request()->location == 'main') ? 'selected' : '' }} value="main">หน้าแรก (Main)</option>
                    <option {{ (!empty($locations) ? $locations == 'ease_club' : request()->location == 'ease_club') ? 'selected' : '' }} value="ease_club">หน้า EASE CLUB</option>
                    <option {{ (!empty($locations) ? $locations == 'service' : request()->location == 'service') ? 'selected' : '' }} value="service">หน้าบริการ/แจ้งซ่อม</option>
                </select>
            </div>
        </div>
        
        <div class="intro-y col-span-12 grid grid-cols-12 gap-6 mt-5">
            @foreach($banners as $b)
                <div class="intro-y col-span-12 md:col-span-6 xl:col-span-4 box">
                    <div class="p-5">
                        <div class="h-40 2xl:h-56 image-fit rounded-md overflow-hidden before:block before:absolute before:w-full before:h-full before:top-0 before:left-0 before:z-10 before:bg-gradient-to-t before:from-black/90 before:to-black/10">
                            <img alt="Banner" src="{{ $b->image_url }}">
                            <div class="absolute bottom-0 text-white px-5 pb-6 z-10 w-full">
                                <span class="bg-white/20 px-2 py-1 rounded text-xs">{{ strtoupper($b->location) }} | ลำดับ: {{ $b->sort_order }}</span>
                                <div class="block font-medium text-base mt-2">{{ $b->title_th }}</div>
                                <div class="text-slate-500 text-xs mt-0.5">{{ $b->title_en }}</div>
                            </div>
                        </div>
                        <div class="text-slate-600 dark:text-slate-500 mt-5 flex justify-between items-center">
                            <div class="flex items-center {{ $b->is_active ? 'text-success' : 'text-danger' }}">
                                <i data-lucide="power" class="w-4 h-4 mr-1"></i> {{ $b->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                            </div>
                            &nbsp;&nbsp;&nbsp;&nbsp; <!-- เพิ่มช่องว่างระหว่างสถานะกับปุ่มจัดการ -->
                            <div class="flex items-center">
                                <button type="button" class="btn-edit-banner text-primary flex items-center mr-3" data-tw-toggle="modal" data-tw-target="#edit-banner-modal" data-banner="{{ json_encode($b) }}">
                                    <i data-lucide="edit" class="w-4 h-4 mr-1"></i> แก้ไข
                                </button>

                                <form action="{{ route('admin.cms.banners.delete', $b->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบแบนเนอร์นี้?');">
                                    @csrf
                                    <button type="submit" class="text-danger flex items-center">
                                        <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ลบ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div id="add-banner-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.cms.banners.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">เพิ่มแบนเนอร์ใหม่</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อแบนเนอร์ (TH) <span class="text-danger">*</span></label>
                        <input name="title_th" type="text" class="form-control" required placeholder="เช่น โปรโมชันสงกรานต์">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อแบนเนอร์ (EN)</label>
                        <input name="title_en" type="text" class="form-control">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">จุดที่ต้องการให้แสดง</label>
                        <select name="location" class="form-select" required>
                            <option value="main">หน้าแรก (Main)</option>
                            <option value="ease_club">หน้า EASE CLUB</option>
                            <option value="service">หน้าบริการ/แจ้งซ่อม</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ลำดับการแสดงผล (0 = บนสุด)</label>
                        <input name="sort_order" type="number" class="form-control" value="0">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">ไฟล์รูปภาพ <span class="text-danger">*</span></label>
                        <input name="image" type="file" class="form-control" accept="image/*" required>
                    </div>
                    <div class="col-span-12 flex items-center mt-3">
                        <input name="is_active" type="checkbox" class="form-check-input border mr-2" id="is_active" checked value="1">
                        <label class="cursor-pointer select-none" for="is_active">เปิดใช้งานทันที</label>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    <div id="edit-banner-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="edit-banner-form" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">แก้ไขแบนเนอร์</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อแบนเนอร์ (TH) <span class="text-danger">*</span></label>
                        <input name="title_th" id="edit_title_th" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อแบนเนอร์ (EN)</label>
                        <input name="title_en" id="edit_title_en" type="text" class="form-control">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">จุดที่ต้องการให้แสดง</label>
                        <select name="location" id="edit_location" class="form-select" required>
                            <option value="main">หน้าแรก (Main)</option>
                            <option value="ease_club">หน้า EASE CLUB</option>
                            <option value="service">หน้าบริการ/แจ้งซ่อม</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ลำดับการแสดงผล</label>
                        <input name="sort_order" id="edit_sort_order" type="number" class="form-control">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">เปลี่ยนไฟล์รูปภาพ (ถ้าไม่เปลี่ยนให้เว้นว่างไว้)</label>
                        <input name="image" type="file" class="form-control" accept="image/*">
                        <div class="mt-2 text-xs text-slate-500">
                            รูปปัจจุบัน: <a id="current_banner_img" href="#" target="_blank" class="text-primary underline">ดูรูปภาพ</a>
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
    @endsection

@section('script')
<script>
    $(document).ready(function() {
        // เมื่อกดปุ่ม "แก้ไข" ให้นำข้อมูลมาใส่ในฟอร์ม Modal
        $('.btn-edit-banner').on('click', function() {
            let banner = $(this).data('banner');
            
            // อัปเดต URL สำหรับ Submit Form
            $('#edit-banner-form').attr('action', `{{ url('/admin/cms/banners') }}/${banner.id}/update`);
            
            // นำข้อมูลไปหยอดตามช่องต่างๆ
            $('#edit_title_th').val(banner.title_th);
            $('#edit_title_en').val(banner.title_en);
            $('#edit_location').val(banner.location);
            $('#edit_sort_order').val(banner.sort_order);
            $('#edit_is_active').prop('checked', banner.is_active == 1);
            $('#current_banner_img').attr('href', banner.image_url);
        });

        $('#location_filter').on('change', function() {
            let location = $(this).val();
            let url = new URL(window.location.href);
            if(location) {
                url.searchParams.set('location', location);
            } else {
                url.searchParams.delete('location');
            }
            window.location.href = url.toString();
        });
    });
</script>
@endsection