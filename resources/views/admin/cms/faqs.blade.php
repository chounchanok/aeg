@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการ FAQ - AEG Admin</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">คำถามที่พบบ่อย (FAQ)</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-faq-modal">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มคำถามใหม่
            </button>
        </div>
        
        <div class="intro-y col-span-12 box p-5">
            <div class="faq-accordion">
                @foreach($faqs as $faq)
                    <div class="border border-slate-200/60 rounded-md p-4 mb-3 flex items-start">
                        <div class="mr-auto w-full">
                            <div class="font-medium text-base text-primary flex items-center">
                                <span class="bg-slate-100 text-slate-500 text-xs px-2 py-1 rounded mr-2">{{ $faq->category }}</span>
                                Q: {{ $faq->question_th ?? $faq->question_en }}
                            </div>
                            <div class="text-slate-600 mt-2 pl-2 border-l-2 border-slate-200">
                                A: {!! nl2br(e($faq->answer_th ?? $faq->answer_en)) !!}
                            </div>
                        </div>
                        <div class="ml-4 flex items-center">
                            <form action="{{ route('admin.cms.faqs.delete', $faq->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบคำถามนี้?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">ลบ</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div id="add-faq-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.cms.faqs.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">เพิ่มคำถาม-คำตอบ (FAQ)</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">หมวดหมู่</label>
                        <input name="category" type="text" class="form-control" placeholder="เช่น การรับประกัน, ทั่วไป">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ลำดับการแสดงผล</label>
                        <input name="sort_order" type="number" class="form-control" value="0">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label font-medium">คำถาม (Question - TH)</label>
                        <input name="question_th" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label font-medium text-slate-500">คำถาม (Question - EN)</label>
                        <input name="question_en" type="text" class="form-control">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label font-medium">คำตอบ (Answer - TH)</label>
                        <textarea name="answer_th" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label font-medium text-slate-500">คำตอบ (Answer - EN)</label>
                        <textarea name="answer_en" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-span-12 flex items-center mt-2">
                        <input name="is_active" type="checkbox" class="form-check-input border mr-2" id="is_active_faq" checked value="1">
                        <label class="cursor-pointer select-none" for="is_active_faq">เปิดใช้งานทันที</label>
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