@extends('../layout/side-menu')

@section('subhead')
    <title>แชทกับ {{ $customer->username }} - AEG Admin</title>
    <style>
        .chat-container { 
            height: calc(100vh - 320px); /* ลดความสูงลงเพื่อไม่ให้ชนขอบล่าง */
            min-height: 400px; 
            display: flex; 
            flex-direction: column; 
            background: #fff; 
            border-radius: 8px; 
            box-shadow: 0px 3px 20px #0000000b; 
            margin-bottom: 80px; /* 🌟 เพิ่มระยะห่างด้านล่าง ดันกล่องแชทหนีปุ่ม Dark Mode */
        }
        .chat-messages { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 15px; background-color: #f8fafc; }
        
        .msg-bubble { max-width: 70%; padding: 12px 18px; border-radius: 18px; font-size: 0.95rem; line-height: 1.5; position: relative; }
        .msg-time { font-size: 0.75rem; margin-top: 5px; opacity: 0.8; }
        
        /* ฝั่งลูกค้า (ซ้าย) */
        .msg-row-customer { display: flex; align-items: flex-end; gap: 10px; align-self: flex-start; }
        .msg-bubble-customer { background-color: #e2e8f0; color: #334155; border-bottom-left-radius: 4px; }
        
        /* ฝั่งแอดมิน (ขวา) */
        .msg-row-admin { display: flex; align-items: flex-end; gap: 10px; align-self: flex-end; flex-direction: row-reverse; }
        .msg-bubble-admin { background-color: #1e3a8a; color: #ffffff; border-bottom-right-radius: 4px; }
        
        .avatar-circle { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.8rem; flex-shrink: 0; }
        .avatar-customer { background-color: #cbd5e1; color: #475569; }
        .avatar-admin { background-color: #e0e7ff; color: #1e3a8a; border: 2px solid #1e3a8a; }

        .chat-input-area { padding: 15px 20px; background: #fff; border-top: 1px solid #e2e8f0; display: flex; gap: 10px; align-items: center; border-radius: 0 0 8px 8px; }
        .chat-input { flex: 1; border: 1px solid #cbd5e1; border-radius: 20px; padding: 10px 18px; outline: none; transition: 0.2s; }
        .chat-input:focus { border-color: #1e3a8a; box-shadow: 0 0 0 2px #1e3a8a20; }
        .btn-send { background-color: #1e3a8a; color: white; border: none; padding: 10px 25px; border-radius: 20px; font-weight: 500; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: 0.2s; }
        .btn-send:hover { background-color: #172554; }
    </style>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8 mb-5">
        <h2 class="text-lg font-medium mr-auto">
            แชทกับ: {{ $customer->username }} ({{ $customer->phone }}) <br>
            <span class="text-sm text-slate-500 font-normal">หัวข้อ: {{ $topic }}</span>
        </h2>
        <a href="{{ route('admin.support-chats.index') }}" class="btn btn-outline-secondary w-24">ย้อนกลับ</a>
    </div>

    <div class="intro-y chat-container">
        <div class="chat-messages" id="chatBox">
            @foreach($messages as $msg)
                @if($msg->sender_type == 'customer')
                    <div class="msg-row-customer">
                        <div class="avatar-circle avatar-customer">{{ strtoupper(substr($customer->username, 0, 2)) }}</div>
                        <div class="msg-bubble msg-bubble-customer">
                            <div>{{ $msg->message }}</div>
                            <div class="msg-time text-left">{{ \Carbon\Carbon::parse($msg->created_at)->format('H:i น.') }}</div>
                        </div>
                    </div>
                @else
                    <div class="msg-row-admin">
                        <div class="avatar-circle avatar-admin">AEG</div>
                        <div class="msg-bubble msg-bubble-admin">
                            <div>{{ $msg->message }}</div>
                            <div class="msg-time text-right text-white/70">{{ \Carbon\Carbon::parse($msg->created_at)->format('H:i น.') }}</div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <div class="chat-input-area">
            <input type="hidden" id="topicVal" value="{{ $topic }}">
            <input type="text" id="adminChatInput" class="chat-input" placeholder="พิมพ์ข้อความตอบกลับที่นี่..." onkeypress="handleEnter(event)">
            <button onclick="sendAdminMessage()" class="btn-send">
                <span>ส่งข้อความ</span>
            </button>
        </div>
    </div>
@endsection

@section('script')
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    
    <script>
        const customerId = {{ $customer->id }};
        const customerName = "{{ strtoupper(substr($customer->username, 0, 2)) }}";
        const topic = document.getElementById('topicVal').value;
        const chatBox = document.getElementById('chatBox');
        const chatInput = document.getElementById('adminChatInput');

        // ฟังก์ชันเลื่อนแชทลงล่างสุด
        function scrollToBottom() { 
            chatBox.scrollTop = chatBox.scrollHeight; 
        }
        scrollToBottom(); // เลื่อนลงทันทีตอนโหลดหน้า

        // 🌟 ตั้งค่า Laravel Echo (Pusher)
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env("PUSHER_APP_KEY") }}',
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
            forceTLS: true
        });

        // 🌟 ดักฟัง Event ข้อความเข้าจากลูกค้าคนนี้
        window.Echo.channel('support-chat.' + customerId)
            .listen('.message.sent', (e) => {
                const msg = e.messageData;
                
                // ถ้าลูกค้าเป็นคนพิมพ์ ให้โชว์ฝั่งซ้าย
                if(msg.sender_type === 'customer') {
                    const time = new Date(msg.created_at).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'}) + ' น.';
                    const html = `
                        <div class="msg-row-customer">
                            <div class="avatar-circle avatar-customer">${customerName}</div>
                            <div class="msg-bubble msg-bubble-customer">
                                <div>${msg.message}</div>
                                <div class="msg-time text-left">${time}</div>
                            </div>
                        </div>`;
                    chatBox.insertAdjacentHTML('beforeend', html);
                    scrollToBottom();
                }
            });

        // กดปุ่ม Enter ส่งข้อความ
        function handleEnter(e) {
            if (e.key === 'Enter') {
                sendAdminMessage();
            }
        }

        // ฟังก์ชันส่งข้อความ
        function sendAdminMessage() {
            const text = chatInput.value.trim();
            if(!text) return;
            
            chatInput.value = ''; // เคลียร์ช่องพิมพ์

            // 1. วาดข้อความแอดมินลงหน้าจอฝั่งขวาทันที (UX)
            const timeNow = new Date().toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'}) + ' น.';
            const html = `
                <div class="msg-row-admin">
                    <div class="avatar-circle avatar-admin">AEG</div>
                    <div class="msg-bubble msg-bubble-admin">
                        <div>${text}</div>
                        <div class="msg-time text-right text-white/70">${timeNow}</div>
                    </div>
                </div>`;
            chatBox.insertAdjacentHTML('beforeend', html);
            scrollToBottom();

            // 2. ยิง API บันทึกลงฐานข้อมูลและส่ง Pusher แบบ AJAX
            fetch(`{{ url('admin/support-chats') }}/${customerId}/reply`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ topic: topic, message: text })
            }).catch(err => console.error("Send Error: ", err));
        }
    </script>
@endsection