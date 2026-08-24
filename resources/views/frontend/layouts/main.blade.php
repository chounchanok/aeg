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

    @php
        // แสดง floating chat widget ให้ทั้ง guest (ยังไม่ล็อกอิน) และสมาชิกที่ role เป็น customer
        // ซ่อนสำหรับ staff/admin และซ่อนในหน้า /support-chat เอง (มี UI แชทเต็มหน้าอยู่แล้ว - กันไม่ให้ id ซ้ำกันในหน้าเดียว)
        $showChatWidget = !request()->routeIs('support-chat')
            && (!Auth::check() || (Auth::user()->role ?? null) === 'customer');
    @endphp
    @if($showChatWidget)
        <style>
            .floating-chat-btn { position: fixed; bottom: 30px; right: 30px; background-color: var(--primary-red, #c41e3a); color: white; width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 24px; cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 9999; transition: transform 0.3s; }
            .floating-chat-btn:hover { transform: scale(1.1); }

            .chat-widget-window { position: fixed; bottom: 100px; right: 30px; width: 360px; height: 520px; background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.15); display: none; flex-direction: column; z-index: 9998; overflow: hidden; border: 1px solid #eee; }
            .chat-widget-window.active { display: flex; animation: slideUp 0.3s ease; }

            @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        </style>

        <div class="floating-chat-btn" onclick="toggleChatWidget()">
            <i class="fas fa-comments"></i>
        </div>

        <div class="chat-widget-window" id="chatWidgetWindow">
            @include('frontend.partials.chatbot-widget', ['botUserId' => Auth::id()])
        </div>

        <script>
            const chatWidget = document.getElementById('chatWidgetWindow');

            // 🌟 เช็คตอนโหลดหน้าเว็บว่าก่อนหน้านี้เปิดแชทค้างไว้ไหม (แก้ปัญหา F5 แล้วแชทหาย)
            window.addEventListener('DOMContentLoaded', function() {
                if (localStorage.getItem('chat_widget_open') === 'true') {
                    chatWidget.classList.add('active');
                }
            });

            function toggleChatWidget() {
                chatWidget.classList.toggle('active');
                localStorage.setItem('chat_widget_open', chatWidget.classList.contains('active') ? 'true' : 'false');
            }
        </script>
    @endif
</body>
</html>