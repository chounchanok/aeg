@extends('../layout/' . $layout)

@section('subhead')
    <title>รายละเอียดใบแจ้งซ่อม - AEG Admin</title>
    <!-- เพิ่ม CSS ของ Lightbox สำหรับกดดูรูปภาพ -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />
    <style>
        /* ประวัติ & หลักฐานการซ่อม (prefix sr- ) */
        .sr-steps { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 10px; }
        .sr-step { border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 10px; font-size: 12px; }
        .sr-step .k { color: #64748b; }
        .sr-step .v { font-weight: 600; color: #0f172a; margin-top: 2px; }
        .sr-step.empty .v { color: #cbd5e1; font-weight: 400; }
        .sr-timeline { list-style: none; margin: 0; padding: 0; }
        .sr-timeline li { position: relative; padding: 0 0 14px 22px; font-size: 13px; }
        .sr-timeline li::before { content: ''; position: absolute; left: 5px; top: 5px; width: 9px; height: 9px; border-radius: 50%; background: #1e3a8a; }
        .sr-timeline li::after { content: ''; position: absolute; left: 9px; top: 16px; bottom: 0; width: 1px; background: #cbd5e1; }
        .sr-timeline li:last-child::after { display: none; }
        .sr-timeline .t { color: #94a3b8; font-size: 12px; }
        .sr-imgs { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 6px; }
        .sr-imgs img, .sr-imgs video { width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }
        .sr-kv { display: grid; grid-template-columns: 150px 1fr; gap: 6px 12px; font-size: 14px; }
        .sr-kv .k { color: #64748b; }
        @media (max-width: 640px) { .sr-kv { grid-template-columns: 1fr; } }
    </style>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            ใบแจ้งซ่อมหมายเลข: <span class="text-primary">{{ $request->ticket_number }}</span>
            @if(($request->source ?? 'customer') === 'scheduled')
                <span style="display:inline-block; font-size:12px; padding:3px 10px; border-radius:999px; background:#e0e7ff; color:#3730a3; vertical-align:middle; margin-left:6px;">🔧 บำรุงรักษาตามรอบ</span>
            @endif
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

            {{-- ===== 🌟 ประวัติการทำงานของช่าง ===== --}}
            <div class="box p-5 mt-5">
                <div class="font-medium text-base flex items-center border-b border-slate-200/60 pb-3 mb-4">
                    <i data-lucide="history" class="w-4 h-4 mr-2"></i> ประวัติการทำงานของช่าง
                    @if($technicianName)<span class="ml-auto text-sm text-slate-500 font-normal">ช่าง: {{ $technicianName }}</span>@endif
                </div>

                <div class="sr-steps">
                    @foreach(['accepted_at' => 'รับงาน', 'traveling_at' => 'เริ่มเดินทาง', 'arrived_at' => 'ถึงหน้างาน', 'started_at' => 'เริ่มซ่อม', 'completed_at' => 'ปิดงาน'] as $col => $label)
                        <div class="sr-step {{ empty($request->$col) ? 'empty' : '' }}">
                            <div class="k">{{ $label }}</div>
                            <div class="v">{{ !empty($request->$col) ? \Carbon\Carbon::parse($request->$col)->format('d/m/Y H:i') : '—' }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-5">
                    @if($tracking->isNotEmpty())
                        <ul class="sr-timeline">
                            @foreach($tracking as $log)
                                <li>
                                    <div>{{ $log->status }}</div>
                                    @if($log->description && $log->description !== $log->status)<div class="text-slate-500">{{ $log->description }}</div>@endif
                                    <div class="t">{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}</div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-slate-500 text-sm">ยังไม่มีประวัติการอัปเดตสถานะ</div>
                    @endif
                </div>
            </div>

            {{-- ===== 🌟 หลักฐานการซ่อม (ตอนช่างปิดงาน) ===== --}}
            <div class="box p-5 mt-5">
                <div class="font-medium text-base flex items-center border-b border-slate-200/60 pb-3 mb-4">
                    <i data-lucide="camera" class="w-4 h-4 mr-2"></i> หลักฐานการซ่อม
                </div>
                @if($completion)
                    <div class="sr-kv">
                        <div class="k">คะแนนจากลูกค้า</div>
                        <div><span style="color:#f59e0b">{{ str_repeat('★', (int) $completion->rating) }}</span><span style="color:#cbd5e1">{{ str_repeat('★', max(0, 5 - (int) $completion->rating)) }}</span> ({{ (int) $completion->rating }}/5)</div>
                        <div class="k">บันทึกจากช่าง</div>
                        <div style="white-space:pre-line">{{ $completion->technician_note ?: '-' }}</div>
                        <div class="k">ตำแหน่งตอนปิดงาน</div>
                        <div>
                            @if($completion->latitude && $completion->longitude)
                                <a href="https://www.google.com/maps?q={{ $completion->latitude }},{{ $completion->longitude }}" target="_blank" rel="noopener" class="text-primary">{{ $completion->latitude }}, {{ $completion->longitude }} (เปิดแผนที่)</a>
                            @else - @endif
                        </div>
                        <div class="k">ส่งงานเมื่อ</div>
                        <div>{{ \Carbon\Carbon::parse($completion->created_at)->format('d/m/Y H:i') }}</div>
                    </div>

                    @foreach(['before_images' => 'รูป/วิดีโอ ก่อนซ่อม', 'after_images' => 'รูป/วิดีโอ หลังซ่อม'] as $key => $title)
                        <div class="mt-5 font-medium text-sm">{{ $title }}</div>
                        @if(!empty($completion->$key))
                            <div class="sr-imgs">
                                @foreach($completion->$key as $url)
                                    @if(preg_match('/\.(mp4|mov)$/i', $url))
                                        <video src="{{ $url }}" controls preload="metadata"></video>
                                    @else
                                        <a href="{{ $url }}" data-lightbox="repair-{{ $key }}"><img src="{{ $url }}" alt="{{ $title }}"></a>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="text-slate-500 text-sm mt-1">ไม่มีไฟล์แนบ</div>
                        @endif
                    @endforeach

                    <div class="mt-5 font-medium text-sm">ลายเซ็นลูกค้า</div>
                    @if($completion->customer_signature_path)
                        <a href="{{ $completion->customer_signature_path }}" data-lightbox="repair-signature">
                            <img src="{{ $completion->customer_signature_path }}" alt="ลายเซ็นลูกค้า" style="max-width:300px; border:1px solid #e2e8f0; border-radius:8px; margin-top:6px; background:#fff;">
                        </a>
                    @else
                        <div class="text-slate-500 text-sm mt-1">-</div>
                    @endif
                @else
                    <div class="text-slate-500 text-sm">ยังไม่มีหลักฐาน — จะแสดงเมื่อช่างกดปิดงานจากแอปหรือเว็บ (รูปก่อน/หลังซ่อม, ลายเซ็นลูกค้า, คะแนน, ตำแหน่ง)</div>
                @endif
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4">
            <div class="box p-5">
                <div class="font-medium text-base flex items-center border-b border-slate-200/60 pb-3 mb-4">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> ใบเสนอราคา / ใบเสร็จรับเงิน
                </div>

                <div class="mb-3">
                    <div class="text-slate-500 text-xs mb-1">ใบเสนอราคา</div>
                    @if($request->quotation_url)
                        <a href="{{ $request->quotation_url }}" target="_blank" class="text-primary underline flex items-center">
                            <i data-lucide="file-text" class="w-4 h-4 mr-1"></i> เปิดดู/ดาวน์โหลด
                        </a>
                    @else
                        <span class="text-slate-400">ยังไม่ได้อัปโหลด</span>
                    @endif
                </div>
                <div class="mb-4">
                    <div class="text-slate-500 text-xs mb-1">ใบเสร็จรับเงิน</div>
                    @if($request->receipt_url)
                        <a href="{{ $request->receipt_url }}" target="_blank" class="text-primary underline flex items-center">
                            <i data-lucide="file-text" class="w-4 h-4 mr-1"></i> เปิดดู/ดาวน์โหลด
                        </a>
                    @else
                        <span class="text-slate-400">ยังไม่ได้อัปโหลด</span>
                    @endif
                </div>

                <form action="{{ route('admin.service-requests.documents', $request->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">อัปโหลด/แทนที่ใบเสนอราคา</label>
                        <input type="file" name="quotation" class="form-control" accept=".pdf,image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">อัปโหลด/แทนที่ใบเสร็จรับเงิน</label>
                        <input type="file" name="receipt" class="form-control" accept=".pdf,image/*">
                    </div>
                    <button type="submit" class="btn btn-primary w-full">บันทึกเอกสาร</button>
                </form>
            </div>

            <div class="box p-5 mt-5">
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
