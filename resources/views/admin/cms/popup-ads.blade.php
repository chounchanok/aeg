@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการ Popup Ads - AEG Admin</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">ระบบจัดการ Popup Ads (โฆษณาแบบ Popup)</h2>
    <div class="text-slate-500 mt-1">
        รูปภาพที่เปิดใช้งาน (ลำดับแรกสุด) จะถูกแสดงเป็น popup ให้ลูกค้าเห็นครั้งแรกที่เปิดหน้าแรกของเว็บในแต่ละครั้งที่เข้าชม (session)
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-popup-ad-modal">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่ม Popup Ad ใหม่
            </button>
        </div>

        <div class="intro-y col-span-12 grid grid-cols-12 gap-6 mt-5">
            @forelse($popupAds as $p)
                <div class="intro-y col-span-12 md:col-span-6 xl:col-span-4 box">
                    <div class="p-5">
                        <div class="h-40 2xl:h-56 image-fit rounded-md overflow-hidden before:block before:absolute before:w-full before:h-full before:top-0 before:left-0 before:z-10 before:bg-gradient-to-t before:from-black/90 before:to-black/10">
                            <img alt="Popup Ad" src="{{ $p->image_url }}">
                            <div class="absolute bottom-0 text-white px-5 pb-6 z-10 w-full">
                                <span class="bg-white/20 px-2 py-1 rounded text-xs">ลำดับ: {{ $p->sort_order }}</span>
                                <div class="block font-medium text-base mt-2">{{ $p->title ?? '(ไม่ระบุชื่อ)' }}</div>
                                @if($p->link_url)
                                    <div class="text-slate-300 text-xs mt-0.5 truncate">ลิงก์: {{ $p->link_url }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="text-slate-600 dark:text-slate-500 mt-5 flex justify-between items-center">
                            <div class="flex items-center {{ $p->is_active ? 'text-success' : 'text-danger' }}">
                                <i data-lucide="power" class="w-4 h-4 mr-1"></i> {{ $p->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                            </div>
                            &nbsp;&nbsp;&nbsp;&nbsp;
                            <div class="flex items-center">
                                <button type="button" class="btn-edit-popup-ad text-primary flex items-center mr-3" data-tw-toggle="modal" data-tw-target="#edit-popup-ad-modal" data-popup-ad="{{ json_encode($p) }}">
                                    <i data-lucide="edit" class="w-4 h-4 mr-1"></i> แก้ไข
                                </button>

                                <form action="{{ route('admin.cms.popup-ads.delete', $p->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบ Popup Ad นี้?');">
                                    @csrf
                                    <button type="submit" class="text-danger flex items-center">
                                        <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ลบ
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="intro-y col-span-12 text-center text-slate-500 py-10">
                    ยังไม่มี Popup Ad ในระบบ กดปุ่ม "เพิ่ม Popup Ad ใหม่" เพื่อเริ่มต้น
                </div>
            @endforelse
        </div>
    </div>

    <div id="add-popup-ad-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.cms.popup-ads.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">เพิ่ม Popup Ad ใหม่</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label class="form-label">ชื่อเรียก (สำหรับแอดมินดูอ้างอิงเท่านั้น ไม่แสดงหน้าเว็บ)</label>
                        <input name="title" type="text" class="form-control" placeholder="เช่น โปรโมชันปีใหม่ 2027">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">ไฟล์รูปภาพ Popup <span class="text-danger">*</span></label>
                        <input name="image" type="file" class="form-control" accept="image/*" required>
                    </div>
                    <div class="col-span-12 sm:col-span-8">
                        <label class="form-label">ลิงก์ปลายทางเมื่อกดที่รูป (ถ้ามี)</label>
                        <input name="link_url" type="url" class="form-control" placeholder="https://...">
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">ลำดับการแสดงผล (0 = แสดงก่อน)</label>
                        <input name="sort_order" type="number" class="form-control" value="0">
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
    <div id="edit-popup-ad-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="edit-popup-ad-form" method="POST" enctype="multipart/form-data" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">แก้ไข Popup Ad</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label class="form-label">ชื่อเรียก (สำหรับแอดมินดูอ้างอิงเท่านั้น)</label>
                        <input name="title" id="edit_title" type="text" class="form-control">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">เปลี่ยนไฟล์รูปภาพ Popup</label>
                        <input name="image" type="file" class="form-control" accept="image/*">
                        <div class="mt-2 text-xs text-slate-500">
                            รูปปัจจุบัน: <a id="current_popup_ad_img" href="#" target="_blank" class="text-primary underline">ดูรูปภาพ</a>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-8">
                        <label class="form-label">ลิงก์ปลายทางเมื่อกดที่รูป (ถ้ามี)</label>
                        <input name="link_url" id="edit_link_url" type="url" class="form-control" placeholder="https://...">
                    </div>
                    <div class="col-span-12 sm:col-span-4">
                        <label class="form-label">ลำดับการแสดงผล</label>
                        <input name="sort_order" id="edit_sort_order" type="number" class="form-control">
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
        $('.btn-edit-popup-ad').on('click', function() {
            let popupAd = $(this).data('popup-ad');

            $('#edit-popup-ad-form').attr('action', `{{ url('/admin/cms/popup-ads') }}/${popupAd.id}/update`);

            $('#edit_title').val(popupAd.title);
            $('#edit_link_url').val(popupAd.link_url);
            $('#edit_sort_order').val(popupAd.sort_order);
            $('#edit_is_active').prop('checked', popupAd.is_active == 1);
            $('#current_popup_ad_img').attr('href', popupAd.image_url);
        });
    });
</script>
@endsection
