<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AEG EASE CLUB')</title>
    
    <!-- Bootstrap & Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Kanit:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Main CSS ที่เรารวมไว้ -->
    <link rel="stylesheet" href="{{ asset('dist/css/main.css') }}">
    
    <!-- สำหรับหน้าไหนที่มี CSS พิเศษ ก็ให้พ่นลงตรงนี้ -->
    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
    <!-- เรียกใช้ Header -->
    @include('frontend.layouts.header')
    
    <!-- เนื้อหาหลักของแต่ละหน้าจะมาแทรกตรงนี้ -->
    @yield('content')
    
    <!-- เรียกใช้ Footer -->
    @include('frontend.layouts.footer')

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- สำหรับหน้าไหนที่มี JS พิเศษ ก็ให้พ่นลงตรงนี้ -->
    @stack('scripts')

    @auth
        @if(Auth::user()->role === 'customer')
            <style>
                .floating-chat-btn { position: fixed; bottom: 30px; right: 30px; background-color: var(--primary-red, #c41e3a); color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 9999; transition: transform 0.3s; }
                .floating-chat-btn:hover { transform: scale(1.1); }
                
                .chat-widget-window { position: fixed; bottom: 100px; right: 30px; width: 350px; height: 500px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); display: none; flex-direction: column; z-index: 9998; overflow: hidden; border: 1px solid #eee; }
                .chat-widget-window.active { display: flex; animation: slideUp 0.3s ease; }
                
                .cw-header { background: var(--primary-navy, #1a2d5e); color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; font-weight: 600; }
                .cw-close { cursor: pointer; font-size: 1.2rem; }
                
                .cw-body { flex: 1; padding: 15px; overflow-y: auto; background: #f4f5f7; display: flex; flex-direction: column; gap: 10px; }
                .cw-msg { max-width: 80%; padding: 10px 15px; border-radius: 15px; font-size: 0.9rem; line-height: 1.4; }
                .cw-msg.customer { background: var(--primary-navy, #1a2d5e); color: white; align-self: flex-end; border-bottom-right-radius: 2px; }
                .cw-msg.admin { background: #e2e8f0; color: #333; align-self: flex-start; border-bottom-left-radius: 2px; }
                .cw-time { font-size: 0.7rem; margin-top: 4px; opacity: 0.7; }
                
                .cw-footer { padding: 10px 15px; border-top: 1px solid #eee; background: white; display: flex; gap: 10px; }
                .cw-input { flex: 1; border: 1px solid #ddd; border-radius: 20px; padding: 8px 15px; outline: none; font-size: 0.9rem; }
                .cw-send { background: var(--primary-red, #c41e3a); color: white; border: none; border-radius: 50%; width: 38px; height: 38px; cursor: pointer; display: flex; justify-content: center; align-items: center;}
                
                @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
            </style>

            <div class="floating-chat-btn" onclick="toggleChatWidget()">
                <i class="fas fa-comments"></i>
            </div>

            <div class="chat-widget-window" id="chatWidgetWindow">
                <div class="cw-header">
                    <span><i class="fas fa-headset mr-2"></i> ปรึกษาผู้เชี่ยวชาญ</span>
                    <i class="fas fa-times cw-close" onclick="toggleChatWidget()"></i>
                </div>
                <div class="cw-body" id="cwBody">
                    <div class="text-center text-muted" style="font-size: 0.8rem; margin-top: 50%;">กำลังโหลดข้อมูล...</div>
                </div>
                <div class="cw-footer">
                    <input type="text" id="cwInput" class="cw-input" placeholder="พิมพ์ข้อความ..." onkeypress="handleCwEnter(event)">
                    <button onclick="sendCwMessage()" class="cw-send"><i class="fas fa-paper-plane"></i></button>
                </div>
            </div>

            <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>

            <script>
                let isChatLoaded = false;
                const cwBody = document.getElementById('cwBody');
                const cwInput = document.getElementById('cwInput');
                const chatWidget = document.getElementById('chatWidgetWindow');
                const currentUserId = {{ Auth::id() }};
                const chatTopic = 'general';

                // 🌟 เช็คตอนโหลดหน้าเว็บว่าก่อนหน้านี้เปิดแชทค้างไว้ไหม (แก้ปัญหา F5 แล้วแชทหาย)
                window.onload = function() {
                    if(localStorage.getItem('chat_widget_open') === 'true') {
                        chatWidget.classList.add('active');
                        loadChatHistory();
                        initPusher();
                        isChatLoaded = true;
                    }
                };

                function toggleChatWidget() {
                    chatWidget.classList.toggle('active');
                    
                    // 🌟 บันทึกสถานะการเปิด/ปิด ลงในความจำของเบราว์เซอร์
                    if(chatWidget.classList.contains('active')) {
                        localStorage.setItem('chat_widget_open', 'true');
                        if(!isChatLoaded) {
                            loadChatHistory();
                            initPusher();
                            isChatLoaded = true;
                        }
                    } else {
                        localStorage.setItem('chat_widget_open', 'false');
                    }
                }

                function scrollToBottom() {
                    cwBody.scrollTop = cwBody.scrollHeight;
                }

                function loadChatHistory() {
                    fetch(`{{ route('support-chat.history') }}?topic=${chatTopic}`)
                        .then(res => {
                            if(!res.ok) throw new Error('Network response was not ok');
                            return res.json();
                        })
                        .then(data => {
                            cwBody.innerHTML = ''; // ล้างข้อความโหลด
                            if(data.data.length === 0) {
                                cwBody.innerHTML = '<div class="text-center text-muted mt-3" style="font-size:0.8rem;">เริ่มการสนทนากับเจ้าหน้าที่ได้เลยครับ</div>';
                            } else {
                                data.data.forEach(msg => appendMessage(msg));
                            }
                            scrollToBottom();
                        })
                        .catch(err => {
                            console.error('Error fetching chat history:', err);
                            cwBody.innerHTML = '<div class="text-center text-danger mt-3" style="font-size:0.8rem;">โหลดข้อมูลล้มเหลว กรุณาลองใหม่อีกครั้ง</div>';
                        });
                }

                function appendMessage(msg) {
                    // เช็คว่าเวลามาถูกต้องไหม ป้องกัน Error
                    let timeStr = '';
                    if(msg.created_at) {
                        const dateObj = new Date(msg.created_at);
                        timeStr = dateObj.toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'});
                    }
                    
                    const alignTime = msg.sender_type === 'customer' ? 'text-end' : 'text-start';
                    const msgClass = msg.sender_type === 'customer' ? 'customer' : 'admin';
                    
                    const html = `
                        <div class="cw-msg ${msgClass}">
                            ${msg.message}
                            <div class="cw-time ${alignTime}">${timeStr}</div>
                        </div>`;
                    cwBody.insertAdjacentHTML('beforeend', html);
                }

                function sendCwMessage() {
                    const text = cwInput.value.trim();
                    if(!text) return;
                    
                    cwInput.value = ''; // เคลียร์ช่อง
                    
                    // วาดลงจอก่อนเพื่อให้ลูกค้ารู้สึกว่าระบบไว
                    appendMessage({ message: text, sender_type: 'customer', created_at: new Date() });
                    scrollToBottom();

                    // ยิง API หลังบ้าน
                    fetch("{{ route('support-chat.send') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ topic: chatTopic, message: text })
                    }).catch(err => console.error('Send error:', err));
                }

                function handleCwEnter(e) {
                    if (e.key === 'Enter') sendCwMessage();
                }

                function initPusher() {
                    window.Echo = new Echo({
                        broadcaster: 'pusher',
                        key: '{{ env("PUSHER_APP_KEY") }}',
                        cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
                        forceTLS: true
                    });

                    window.Echo.channel('support-chat.' + currentUserId)
                        .listen('.message.sent', (e) => {
                            const msg = e.messageData;
                            if(msg.sender_type === 'admin') {
                                appendMessage(msg);
                                scrollToBottom();
                                
                                const widget = document.getElementById('chatWidgetWindow');
                                if(!widget.classList.contains('active')) {
                                    widget.classList.add('active');
                                    localStorage.setItem('chat_widget_open', 'true'); // อัปเดตสถานะถ้าแชทเด้งเปิดเอง
                                }
                            }
                        });
                }
            </script>
        @endif
    @endauth
</body>
</html>