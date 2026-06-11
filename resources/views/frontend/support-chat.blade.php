@extends('frontend.layouts.main')

@section('title', 'ติดต่อสอบถาม - AEG')

@push('styles')
    <style>
        .chat-container { max-width: 800px; margin: 40px auto; background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        .chat-header { background: var(--primary-navy, #1a2d5e); color: #fff; padding: 20px; font-weight: bold; }
        .chat-box { height: 500px; overflow-y: auto; padding: 20px; background: #f4f5f7; display: flex; flex-direction: column; gap: 15px; }
        .chat-msg { max-width: 75%; padding: 12px 18px; border-radius: 15px; font-size: 0.95rem; }
        .msg-customer { background: var(--primary-navy, #1a2d5e); color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
        .msg-admin { background: #e2e8f0; color: #333; align-self: flex-start; border-bottom-left-radius: 4px; }
        .chat-input-area { padding: 15px 20px; background: #fff; border-top: 1px solid #eee; display: flex; gap: 10px; }
        .chat-input { flex: 1; padding: 10px 15px; border: 1px solid #ddd; border-radius: 20px; outline: none; }
        .btn-send { background: var(--primary-red, #c41e3a); color: #fff; border: none; padding: 10px 25px; border-radius: 20px; cursor: pointer; }
        .time-text { font-size: 0.7rem; margin-top: 5px; opacity: 0.7; }
    </style>
@endpush

@section('content')
    <div class="container">
        <div class="chat-container">
            <div class="chat-header">ปรึกษาผู้เชี่ยวชาญ (หัวข้อ: {{ $topic }})</div>
            
            <div class="chat-box" id="chatBox">
                @foreach($messages as $msg)
                    <div class="chat-msg {{ $msg->sender_type == 'customer' ? 'msg-customer' : 'msg-admin' }}">
                        {{ $msg->message }}
                        <div class="time-text {{ $msg->sender_type == 'customer' ? 'text-end' : 'text-start' }}">
                            {{ \Carbon\Carbon::parse($msg->created_at)->format('H:i') }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="chat-input-area">
                <input type="text" id="chatInput" class="chat-input" placeholder="พิมพ์ข้อความของคุณที่นี่..." onkeypress="handleEnter(event)">
                <button onclick="sendMessage()" class="btn-send"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- ดึง Pusher และ Laravel Echo จาก CDN (ไม่ต้อง Compile npm) -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

    <script>
        const userId = {{ $user->id }};
        const topic = "{{ $topic }}";
        const chatBox = document.getElementById('chatBox');
        const chatInput = document.getElementById('chatInput');

        // เลื่อนลงล่างสุดอัตโนมัติ
        function scrollToBottom() {
            chatBox.scrollTop = chatBox.scrollHeight;
        }
        scrollToBottom();

        // 🌟 ตั้งค่า Laravel Echo
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env("PUSHER_APP_KEY") }}',
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
            forceTLS: true
        });

        // 🌟 ดักฟัง Event จาก Pusher
        window.Echo.channel('support-chat.' + userId)
            .listen('.message.sent', (e) => {
                const msg = e.messageData;
                // ถ้าแอดมินเป็นคนพิมพ์ (ฝั่งนี้ลูกค้าดูอยู่ เลยโชว์ฝั่งซ้าย)
                if(msg.sender_type === 'admin') {
                    const time = new Date(msg.created_at).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'});
                    const html = `
                        <div class="chat-msg msg-admin">
                            ${msg.message}
                            <div class="time-text text-start">${time}</div>
                        </div>`;
                    chatBox.insertAdjacentHTML('beforeend', html);
                    scrollToBottom();
                }
            });

        function handleEnter(e) { if(e.key === 'Enter') sendMessage(); }

        // ส่งข้อความไป API ด้วย Fetch
        function sendMessage() {
            const text = chatInput.value.trim();
            if (!text) return;
            
            chatInput.value = ''; // เคลียร์ช่องพิมพ์

            // วาดฝั่งตัวเอง (ลูกค้า) ลงจอก่อนเลยเพื่อความรวดเร็ว
            const timeNow = new Date().toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'});
            chatBox.insertAdjacentHTML('beforeend', `
                <div class="chat-msg msg-customer">
                    ${text}
                    <div class="time-text text-end">${timeNow}</div>
                </div>`);
            scrollToBottom();

            // ยิง API แบบเงียบๆ (AJAX)
            fetch("{{ route('support-chat.send') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ topic: topic, message: text })
            });
        }
    </script>
@endpush