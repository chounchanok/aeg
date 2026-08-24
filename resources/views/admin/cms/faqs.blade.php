@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการ FAQ - AEG Admin</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">คำถามที่พบบ่อย (FAQ)</h2>
    <div class="text-slate-500 text-sm intro-y mt-1">
        ข้อมูลชุดนี้ใช้ร่วมกับ Chat Bot บนเว็บ/แอป — แก้ไขที่นี่ที่เดียว มีผลทั้งหน้าเว็บ "คำถามที่พบบ่อย" และคำตอบของบอทด้วย
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-faq-modal">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มคำถามใหม่
            </button>
        </div>

        <div class="intro-y col-span-12 box p-5">
            @if($faqs->isEmpty())
                <div class="text-center text-slate-400 py-10">ยังไม่มีคำถาม FAQ กรุณาเพิ่มคำถามใหม่</div>
            @endif

            @foreach($faqs->groupBy('topic_name') as $topicName => $topicFaqs)
                <div class="mb-6">
                    <div class="font-medium text-base text-slate-700 mb-2 flex items-center">
                        <i data-lucide="folder" class="w-4 h-4 mr-2 text-primary"></i> {{ $topicName }}
                    </div>
                    <div class="faq-accordion pl-2">
                        @foreach($topicFaqs->groupBy('service_name') as $serviceName => $serviceFaqs)
                            <div class="text-xs text-slate-400 mb-1 mt-2">บริการ: {{ $serviceName }}</div>
                            @foreach($serviceFaqs as $faq)
                                <div class="border border-slate-200/60 rounded-md p-4 mb-3 flex items-start">
                                    <div class="mr-auto w-full">
                                        <div class="font-medium text-base text-primary flex items-center flex-wrap gap-2">
                                            @if(!$faq->is_active)
                                                <span class="bg-slate-200 text-slate-500 text-xs px-2 py-1 rounded">ปิดใช้งาน</span>
                                            @endif
                                            Q: {{ $faq->question_th }}
                                        </div>
                                        <div class="text-slate-600 mt-2 pl-2 border-l-2 border-slate-200">
                                            A: {!! nl2br(e($faq->answer_th)) !!}
                                        </div>
                                    </div>
                                    <div class="ml-4 flex items-center flex-shrink-0">
                                        <button type="button" class="btn-edit-faq flex items-center mr-3 text-primary" data-tw-toggle="modal" data-tw-target="#edit-faq-modal" data-faq="{{ json_encode($faq) }}">
                                            <i data-lucide="edit" class="w-4 h-4 mr-1"></i> แก้ไข
                                        </button>
                                        <form action="{{ route('admin.cms.faqs.delete', $faq->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบคำถามนี้?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger">ลบ</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- ===================== Modal เพิ่มคำถามใหม่ ===================== -->
    <div id="add-faq-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.cms.faqs.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">เพิ่มคำถาม-คำตอบ (FAQ)</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">หมวดหลัก <span class="text-danger">*</span></label>
                        <select class="form-select faq-topic-select" data-target="#add_service_id" required>
                            <option value="">-- เลือกหมวด --</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->name_th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">บริการย่อย <span class="text-danger">*</span></label>
                        <select name="service_id" id="add_service_id" class="form-select" required>
                            <option value="">-- เลือกหมวดก่อน --</option>
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label font-medium">คำถาม (Question)</label>
                        <input name="question_th" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label font-medium">คำตอบ (Answer)</label>
                        <textarea name="answer_th" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ลำดับการแสดงผล</label>
                        <input name="sort_order" type="number" class="form-control" value="0">
                    </div>
                    <div class="col-span-12 sm:col-span-6 flex items-center mt-6">
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

    <!-- ===================== Modal แก้ไขคำถาม ===================== -->
    <div id="edit-faq-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="edit-faq-form" method="POST" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">แก้ไขคำถาม-คำตอบ (FAQ)</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">หมวดหลัก <span class="text-danger">*</span></label>
                        <select id="edit_topic_id" class="form-select faq-topic-select" data-target="#edit_service_id" required>
                            <option value="">-- เลือกหมวด --</option>
                            @foreach($topics as $topic)
                                <option value="{{ $topic->id }}">{{ $topic->name_th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">บริการย่อย <span class="text-danger">*</span></label>
                        <select name="service_id" id="edit_service_id" class="form-select" required>
                            <option value="">-- เลือกหมวดก่อน --</option>
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label font-medium">คำถาม (Question)</label>
                        <input name="question_th" id="edit_question_th" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label font-medium">คำตอบ (Answer)</label>
                        <textarea name="answer_th" id="edit_answer_th" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ลำดับการแสดงผล</label>
                        <input name="sort_order" id="edit_sort_order" type="number" class="form-control" value="0">
                    </div>
                    <div class="col-span-12 sm:col-span-6 flex items-center mt-6">
                        <input name="is_active" type="checkbox" class="form-check-input border mr-2" id="edit_is_active_faq" value="1">
                        <label class="cursor-pointer select-none" for="edit_is_active_faq">เปิดใช้งาน</label>
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
    // ข้อมูลบริการทั้งหมด (พร้อม topic_id) ไว้ทำ dropdown แบบ cascading (หมวด -> บริการ)
    const faqServices = {!! json_encode($services->map(function($s){ return ['id' => $s->id, 'topic_id' => $s->topic_id, 'name_th' => $s->name_th]; })->values()) !!};

    function populateServiceSelect(selectEl, topicId, selectedServiceId) {
        selectEl.innerHTML = '';
        const filtered = faqServices.filter(s => String(s.topic_id) === String(topicId));
        if (filtered.length === 0) {
            selectEl.innerHTML = '<option value="">-- ไม่มีบริการในหมวดนี้ --</option>';
            return;
        }
        filtered.forEach(s => {
            const opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name_th;
            if (selectedServiceId && String(selectedServiceId) === String(s.id)) opt.selected = true;
            selectEl.appendChild(opt);
        });
    }

    document.querySelectorAll('.faq-topic-select').forEach((el) => {
        el.addEventListener('change', function () {
            const targetSelect = document.querySelector(this.dataset.target);
            populateServiceSelect(targetSelect, this.value, null);
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        // ปุ่มแก้ไข: เติมข้อมูลลงฟอร์ม + ตั้ง action ของฟอร์มให้ตรงกับ id ที่จะแก้
        document.querySelectorAll('.btn-edit-faq').forEach((btn) => {
            btn.addEventListener('click', function () {
                const faq = JSON.parse(this.dataset.faq);

                document.getElementById('edit-faq-form').action = `{{ url('/admin/cms/faqs') }}/${faq.id}/update`;
                document.getElementById('edit_question_th').value = faq.question_th;
                document.getElementById('edit_answer_th').value = faq.answer_th;
                document.getElementById('edit_sort_order').value = faq.sort_order;
                document.getElementById('edit_is_active_faq').checked = (faq.is_active == 1);

                document.getElementById('edit_topic_id').value = faq.topic_id;
                populateServiceSelect(document.getElementById('edit_service_id'), faq.topic_id, faq.service_id);
            });
        });
    });
</script>
@endsection
