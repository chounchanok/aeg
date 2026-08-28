@extends('../layout/' . $layout)

@section('subhead')
    <title>รายละเอียดใบแจ้งซ่อม - AEG Admin</title>
    <!-- เพิ่ม CSS ของ Lightbox สำหรับกดดูรูปภาพ -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            ใบแจ้งซ่อมหมายเลข: <span class="text-primary">{{ $request->ticket_number }}</span>
        </h2>
        <a href="{{ route('admin.service-requests') }}" class="btn btn-outline-secondary w-24 mr-1">กลับหน้าหลัก</a>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-8">
            <div class="box p-5">
                <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5">
                    <div class="font-medium text-base">รายละเอียดและข้อมูลลูกค้า</div>
                </div>
                <div class="grid grid-cols-2 gap-y-5 gap-x-4 mt-5">
                    <div>
                        <div class="text-slate-500">ชื่อลูกค้า</div>
                        <div class="mt-1 font-medium">{{ $request->first_name ?? $request->username }} ({{ $request->phone }})</div>
                    </div>
                    <div>
                        <div class="text-slate-500">วันและเวลาที่ลูกค้านัดหมาย (ปัจจุบัน)</div>
                        <div class="mt-1 font-medium text-pending">{{ \Carbon\Carbon::parse($request->preferred_date)->format('d/m/Y') }} | {{ $request->time_slot }}</div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-slate-500">สินค้า/แพ็กเกจที่เกิดปัญหา</div>
                        <div class="mt-1 font-medium">{{ $request->product_name }} (S/N: {{ $request->serial_number ?? '-' }})</div>
                    </div>
                    <div class="col-span-2">
                        <div class="text-slate-500">ที่อยู่สำหรับเข้าบริการ</div>
                        <div class="mt-1">{{ $display_address }}</div>
                    </div>
                    <div class="col-span-2 border-t border-slate-200/60 pt-5 mt-2">
                        <div class="text-slate-500">รายละเอียดอาการเสีย</div>
                        <div class="mt-1 p-4 bg-slate-50 rounded-md text-slate-700">
                            {{ $request->problem_description }}
                        </div>
                    </div>
                </div>

                @if(count($images) > 0)
                <div class="mt-5 border-t border-slate-200/60 pt-5">
                    <div class="text-slate-500 mb-3">รูปภาพประกอบจากลูกค้า</div>
                    <div class="grid grid-cols-4 gap-4">
                        @foreach($images as $img)
                        <!-- 🌟 เปิดใช้งาน Lightbox2 เมื่อกดที่รูปภาพ -->
                        <a href="{{ $img->image_url }}" data-lightbox="service-gallery" data-title="รูปภาพประกอบแจ้งซ่อม" class="w-24 h-24 image-fit zoom-in rounded-md overflow-hidden block">
                            <img alt="รูปภาพแจ้งซ่อม" src="{{ $img->image_url }}">
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- 🌟 ฟอร์มแก้ไขข้อมูล/สถานะ -->
            <div class="box p-5 mt-5">
                <div class="font-medium text-base mb-4">อัปเดตสถานะงาน / วันเวลานัดหมาย / จ่ายงานให้ช่าง</div>
                <form action="#" method="POST" class="grid grid-cols-12 gap-4">
                    @csrf

                    <!-- แถวที่ 1: สถานะ และ ช่าง -->
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">เลือกสถานะ</label>
                        <select class="form-select" id="status-select" name="status">
                            <option value="pending" {{ $request->status == 'pending' ? 'selected' : '' }}>รอดำเนินการ (Pending)</option>
                            <option value="assigned" {{ $request->status == 'assigned' ? 'selected' : '' }}>จ่ายงานแล้ว (Assigned)</option>
                            <option value="in_progress" {{ $request->status == 'in_progress' ? 'selected' : '' }}>กำลังดำเนินการ (In Progress)</option>
                            <option value="completed" {{ $request->status == 'completed' ? 'selected' : '' }}>เสร็จสิ้น (Completed)</option>
                        </select>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">เลือกช่างผู้รับผิดชอบ</label>
                        <select class="form-select" id="technician-select" name="technician_id">
                            <option value="">-- ยังไม่ระบุช่าง --</option>
                            @foreach($technicians as $tech)
                                <option value="{{ $tech->id }}" {{ $request->technician_id == $tech->id ? 'selected' : '' }}>
                                    {{ $tech->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 🌟 แถวที่ 2: วันที่ และ เวลานัดหมาย -->
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">แก้ไขวันที่นัดหมาย</label>
                        <input type="date" id="preferred-date-input" class="form-control" value="{{ \Carbon\Carbon::parse($request->preferred_date)->format('Y-m-d') }}">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">แก้ไขเวลานัดหมาย</label>
                        <select class="form-select" id="time-slot-input" name="time_slot">
                            <option value="">-- ยังไม่ได้ระบุช่วงเวลานัดหมาย --</option>
                            <option value="09:00-12:00" {{ $request->time_slot == '09:00-12:00' ? 'selected' : '' }}>09:00 - 12:00</option>
                            <option value="13:00-18:00" {{ $request->time_slot == '13:00-18:00' ? 'selected' : '' }}>13:00 - 18:00</option>
                        </select>
                    </div>

                    <div class="col-span-12 mt-2">
                        <button type="button" id="btn-update-status" class="btn btn-primary w-24">บันทึก</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4">
            <div class="box p-5">
                <div class="font-medium text-base flex items-center border-b border-slate-200/60 pb-3">
                    <i data-lucide="message-circle" class="w-4 h-4 mr-2"></i> แชทพูดคุยกับลูกค้า
                </div>
                <div class="overflow-y-auto mt-4 px-2" id="chat-container" style="max-height: 400px;">
                    @forelse($chats as $chat)
                        <div class="chat__box__text-box flex items-end {{ $chat->sender_type == 'admin' ? 'justify-end mb-4' : 'float-left mb-4 w-full' }}">
                            <div class="{{ $chat->sender_type == 'admin' ? 'bg-primary text-white px-4 py-3 rounded-l-md rounded-t-md' : 'bg-slate-100 text-slate-700 px-4 py-3 rounded-r-md rounded-t-md' }}">
                                {{ $chat->message }}
                                <div class="mt-1 text-xs {{ $chat->sender_type == 'admin' ? 'text-primary-light' : 'text-slate-500' }}">
                                    {{ \Carbon\Carbon::parse($chat->created_at)->format('H:i') }}
                                </div>
                            </div>
                        </div>
                        <div class="clear-both"></div>
                    @empty
                        <div class="text-center text-slate-500 mt-5">ยังไม่มีข้อความแชท</div>
                    @endforelse
                </div>
                <div class="pt-4 border-t border-slate-200/60 mt-4 flex">
                    <input type="text" id="chat-input" class="form-control w-full mr-2" placeholder="พิมพ์ข้อความตอบกลับ...">
                    <button id="btn-send-chat" class="btn btn-primary"><i data-lucide="send" class="w-4 h-4"></i></button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<!-- เพิ่ม JS ของ Lightbox สำหรับการทำงานของปุ่มกดดูรูปภาพ -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const requestId = {{ $request->id }};
    const updateStatusUrl = `{{ url('/admin/service-requests') }}/${requestId}/status`;
    const sendChatUrl = `{{ url('/admin/service-requests') }}/${requestId}/chat`;

    $(document).ready(function() {
        const chatContainer = $('#chat-container');
        if(chatContainer.length) {
            chatContainer.scrollTop(chatContainer[0].scrollHeight);
        }

        // ==========================================
        // 1. ระบบบันทึกข้อมูล (แนบวันที่และเวลาไปพร้อมกัน)
        // ==========================================
        $('#btn-update-status').click(function() {
            let status = $('#status-select').val();
            let technician_id = $('#technician-select').val();
            let preferred_date = $('#preferred-date-input').val(); // 🌟 รับค่าวันที่
            let time_slot = $('#time-slot-input').val();           // 🌟 รับค่าเวลา

            let btn = $(this);
            btn.prop('disabled', true).html('กำลังบันทึก...');

            axios.post(updateStatusUrl, {
                status: status,
                technician_id: technician_id,
                preferred_date: preferred_date,
                time_slot: time_slot
            })
            .then(res => {
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: res.data.message || 'บันทึกข้อมูลเรียบร้อยแล้ว',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            })
            .catch(err => {
                Swal.fire('ข้อผิดพลาด', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
            })
            .finally(() => {
                btn.prop('disabled', false).html('บันทึก');
            });
        });

        // ==========================================
        // 2. ระบบส่งข้อความแชท
        // ==========================================
        function sendChatMessage() {
            let message = $('#chat-input').val().trim();
            if (!message) return;

            let btn = $('#btn-send-chat');
            let input = $('#chat-input');

            btn.prop('disabled', true);
            input.prop('disabled', true);

            axios.post(sendChatUrl, { message: message })
                .then(res => {
                    let newChatHtml = `
                        <div class="chat__box__text-box flex items-end justify-end mb-4">
                            <div class="bg-primary text-white px-4 py-3 rounded-l-md rounded-t-md">
                                ${res.data.chat.message}
                                <div class="mt-1 text-xs text-primary-light">${res.data.chat.time}</div>
                            </div>
                        </div>
                        <div class="clear-both"></div>
                    `;

                    chatContainer.append(newChatHtml);
                    chatContainer.scrollTop(chatContainer[0].scrollHeight);
                    input.val('');
                })
                .catch(err => {
                    Swal.fire('ข้อผิดพลาด', 'ส่งข้อความไม่สำเร็จ', 'error');
                })
                .finally(() => {
                    btn.prop('disabled', false);
                    input.prop('disabled', false).focus();
                });
        }

        $('#btn-send-chat').click(function() {
            sendChatMessage();
        });

        $('#chat-input').keypress(function(e) {
            if (e.which == 13) {
                sendChatMessage();
            }
        });
    });
</script>
@endsection
