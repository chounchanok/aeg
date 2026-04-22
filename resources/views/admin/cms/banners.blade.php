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
        </div>
        
        <div class="intro-y col-span-12 grid grid-cols-12 gap-6 mt-5">
            @foreach($banners as $b)
                <div class="intro-y col-span-12 md:col-span-6 xl:col-span-4 box">
                    <div class="p-5">
                        <div class="h-40 2xl:h-56 image-fit rounded-md overflow-hidden before:block before:absolute before:w-full before:h-full before:top-0 before:left-0 before:z-10 before:bg-gradient-to-t before:from-black/90 before:to-black/10">
                            <img alt="Banner" src="{{ $b->image_url }}">
                            <div class="absolute bottom-0 text-white px-5 pb-6 z-10 w-full">
                                <span class="bg-white/20 px-2 py-1 rounded text-xs">{{ strtoupper($b->location) }}</span>
                                <a href="" class="block font-medium text-base mt-2">{{ $b->title_th ?? $b->title_en }}</a>
                            </div>
                        </div>
                        <div class="text-slate-600 dark:text-slate-500 mt-5 flex justify-between items-center">
                            <div class="flex items-center {{ $b->is_active ? 'text-success' : 'text-danger' }}">
                                <i data-lucide="power" class="w-4 h-4 mr-1"></i> {{ $b->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                            </div>
                            <form action="{{ route('admin.cms.banners.delete', $b->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบแบนเนอร์นี้?');">
                                @csrf
                                <button type="submit" class="text-danger flex items-center">
                                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ลบ
                                </button>
                            </form>
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
                        <label class="form-label">ชื่อแบนเนอร์ (ภาษาไทย)</label>
                        <input name="title_th" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อแบนเนอร์ (English)</label>
                        <input name="title_en" type="text" class="form-control">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">จุดที่ต้องการให้แสดง</label>
                        <select name="location" class="form-select" required>
                            <option value="main">หน้าแรก (Main)</option>
                            <option value="ease_club">หน้า EASE CLUB</option>
                            <option value="service">หน้าบริการ/แจ้งซ่อม</option>
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">ไฟล์รูปภาพ</label>
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
@endsection