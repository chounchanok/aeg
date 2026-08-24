{{--
    chatbot-widget.blade.php

    UI + JS ของแชทบอทเมนูปุ่มกด (โหมดที่ 1) และแชทกับเจ้าหน้าที่ (โหมดที่ 2)
    ใช้ร่วมกันทั้งหน้า /support-chat (เต็มหน้า) และ floating widget มุมขวาล่างในทุกหน้าเว็บ

    ตัวแปรที่รับเข้ามา:
    - $botUserId : int|null  -> id ของผู้ใช้ที่ล็อกอินอยู่ (null = guest ยังไม่ได้ล็อกอิน)
--}}
@php
    $botUserId = $botUserId ?? null;
@endphp

<style>
    .chat-container { background: #fff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; position: relative; display: flex; flex-direction: column; height: 100%; }
    .chat-header-bar { background: var(--primary-navy, #1a2d5e); color: #fff; padding: 15px 20px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; }
    .chat-header { font-weight: bold; }
    .header-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .btn-agent { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.4); color: #fff; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 0.85rem; white-space: nowrap; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
    .btn-agent:hover { background: rgba(255,255,255,0.25); color: #fff; }
    .btn-end { background: rgba(196,30,58,0.85); border-color: rgba(196,30,58,0.9); }
    .btn-end:hover { background: rgba(196,30,58,1); }
    .chat-box { flex: 1; min-height: 0; overflow-y: auto; padding: 20px; background: #f4f5f7; display: flex; flex-direction: column; gap: 15px; }
    .chat-msg { max-width: 85%; padding: 12px 18px; border-radius: 15px; font-size: 0.95rem; line-height: 1.6; }
    .msg-customer { background: var(--primary-navy, #1a2d5e); color: #fff; align-self: flex-end; border-bottom-right-radius: 4px; }
    .msg-admin { background: #e2e8f0; color: #333; align-self: flex-start; border-bottom-left-radius: 4px; }
    .msg-bot { background: #ffffff; border: 1px solid #d7dce3; color: #222; align-self: flex-start; border-bottom-left-radius: 4px; }
    .chat-input-area { padding: 15px 20px; background: #fff; border-top: 1px solid #eee; display: flex; gap: 10px; }
    .chat-input { flex: 1; padding: 10px 15px; border: 1px solid #ddd; border-radius: 20px; outline: none; min-width: 0; }
    .btn-send { background: var(--primary-red, #c41e3a); color: #fff; border: none; padding: 10px 25px; border-radius: 20px; cursor: pointer; flex-shrink: 0; }
    .time-text { font-size: 0.7rem; margin-top: 5px; opacity: 0.7; }
    .back-to-bot { display: block; text-align: center; font-size: 0.8rem; padding: 8px; color: #666; cursor: pointer; background: #fff; border-top: 1px solid #eee; }

    .bot-buttons { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
    .bot-btn { background: var(--primary-navy, #1a2d5e); color: #fff; border: none; padding: 8px 16px; border-radius: 18px; font-size: 0.85rem; cursor: pointer; }
    .bot-btn:hover { opacity: 0.9; }
    .bot-btn-outline { background: #fff; color: var(--primary-navy, #1a2d5e); border: 1px solid #c7cbd4; }
    .bot-subhead { font-weight: 700; color: var(--primary-navy, #1a2d5e); margin-top: 12px; margin-bottom: 4px; font-size: 0.9rem; }
    .bot-form { display: flex; flex-direction: column; gap: 8px; margin-top: 12px; }
    .bot-input { padding: 9px 12px; border: 1px solid #ddd; border-radius: 10px; font-size: 0.9rem; font-family: inherit; }
    .bot-textarea { min-height: 70px; resize: vertical; }

    .rating-modal { position: absolute; inset: 0; background: rgba(15,20,35,0.55); display: none; align-items: center; justify-content: center; z-index: 50; border-radius: 20px; }
    .rating-modal-box { background: #fff; border-radius: 16px; padding: 30px 25px; width: 88%; max-width: 340px; text-align: center; }
    .rating-modal-title { font-weight: 700; color: var(--primary-navy, #1a2d5e); font-size: 1.1rem; margin-bottom: 6px; }
    .rating-modal-sub { font-size: 0.85rem; color: #777; margin-bottom: 16px; }
    .rating-stars { font-size: 1.8rem; color: #f5b301; margin-bottom: 14px; display: flex; justify-content: center; gap: 8px; }
    .rating-stars i { cursor: pointer; }
    .rating-comment { width: 100%; min-height: 60px; border: 1px solid #ddd; border-radius: 10px; padding: 8px 10px; font-family: inherit; font-size: 0.85rem; resize: vertical; margin-bottom: 14px; }
    .rating-submit-btn { width: 100%; }
    .rating-submit-btn:disabled { opacity: 0.5; cursor: not-allowed; }
</style>

<div class="chat-container">
    <div class="chat-header-bar">
        <div class="chat-header" id="panelTitle">ถามบอทตอบอัตโนมัติ</div>
        <div class="header-actions">
            @if($botUserId)
                <button type="button" class="btn-agent" id="toggleAgentBtn" onclick="switchToAgent()">
                    <i class="fas fa-headset"></i> คุยกับเจ้าหน้าที่
                </button>
            @else
                <a href="{{ route('login') }}" class="btn-agent" id="toggleAgentBtn">
                    <i class="fas fa-headset"></i> เข้าสู่ระบบเพื่อคุยกับเจ้าหน้าที่
                </a>
            @endif
            <button type="button" class="btn-agent btn-end" onclick="openRatingModal()">
                <i class="fas fa-circle-xmark"></i> จบการสนทนา
            </button>
        </div>
    </div>

    {{-- ===================== โหมดที่ 1: แชทบอทเมนูปุ่มกด ===================== --}}
    <div id="botPanel" style="display:flex; flex-direction:column; flex:1; min-height:0;">
        <div class="chat-box" id="botChatBox"></div>
        <div class="chat-input-area">
            <input type="text" id="botChatInput" class="chat-input" placeholder="พิมพ์คำถามของคุณที่นี่..." onkeypress="handleBotEnter(event)">
            <button onclick="submitBotText()" class="btn-send"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>

    {{-- ===================== โหมดที่ 2: คุยกับเจ้าหน้าที่ (เฉพาะผู้ที่ล็อกอินแล้ว) ===================== --}}
    @if($botUserId)
        <div id="agentPanel" style="display:none; flex-direction:column; flex:1; min-height:0;">
            <div class="chat-box" id="chatBox">
                <div class="text-center text-muted" style="font-size:0.8rem;">กำลังโหลดข้อมูล...</div>
            </div>
            <div class="chat-input-area">
                <input type="text" id="chatInput" class="chat-input" placeholder="พิมพ์ข้อความของคุณที่นี่..." onkeypress="handleEnter(event)">
                <button onclick="sendMessage()" class="btn-send"><i class="fas fa-paper-plane"></i></button>
            </div>
            <div class="back-to-bot" onclick="switchToBot()">← กลับไปถามบอทอัตโนมัติ</div>
        </div>
    @endif

    {{-- ===================== Modal ให้คะแนน (บังคับก่อนปิดการสนทนา) ===================== --}}
    <div id="ratingModal" class="rating-modal">
        <div class="rating-modal-box">
            <div class="rating-modal-title">ให้คะแนนความพึงพอใจ</div>
            <div class="rating-modal-sub">กรุณาให้คะแนนการให้บริการของเราก่อนปิดการสนทนา</div>
            <div class="rating-stars" id="ratingStars">
                <i class="far fa-star" data-value="1" onclick="setRating(1)"></i>
                <i class="far fa-star" data-value="2" onclick="setRating(2)"></i>
                <i class="far fa-star" data-value="3" onclick="setRating(3)"></i>
                <i class="far fa-star" data-value="4" onclick="setRating(4)"></i>
                <i class="far fa-star" data-value="5" onclick="setRating(5)"></i>
            </div>
            <textarea id="ratingComment" class="rating-comment" placeholder="ความคิดเห็นเพิ่มเติม (ถ้ามี)"></textarea>
            <button type="button" class="btn-send rating-submit-btn" id="ratingSubmitBtn" disabled onclick="submitRatingAndClose()">ส่งคะแนนและปิดแชท</button>
        </div>
    </div>
</div>

@if($botUserId)
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
@endif

<script>
    const botUserId = {{ $botUserId ? $botUserId : 'null' }};
    const chatTopic = 'general';
    const csrfToken = "{{ csrf_token() }}";
    const chatBox = document.getElementById('chatBox');
    const chatInput = document.getElementById('chatInput');
    const botChatBox = document.getElementById('botChatBox');
    const botChatInput = document.getElementById('botChatInput');
    const loginUrl = "{{ route('login') }}";

    // ปลายทางของ Chat Bot เมนูปุ่มกด (public - ใช้ได้แม้ยังไม่ล็อกอิน)
    const botUrls = {
        topics: "{{ route('support-chat.bot.topics') }}",
        topicsBase: "{{ url('/support-chat/bot/topics') }}",
        servicesBase: "{{ url('/support-chat/bot/services') }}",
        search: "{{ route('support-chat.bot.search') }}",
        leads: "{{ route('support-chat.bot.leads') }}",
        rating: "{{ route('support-chat.bot.rating') }}",
        packagesMine: "{{ route('packages.mine') }}",
        routeMap: {
            lockers: "{{ route('lockers') }}",
            services: "{{ route('services') }}",
        },
    };

    let lastUnansweredQuestion = null;
    let agentHistoryLoaded = false;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.innerText = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function scrollToBottom() { if (chatBox) chatBox.scrollTop = chatBox.scrollHeight; }
    function scrollToBottomBot() { botChatBox.scrollTop = botChatBox.scrollHeight; }

    // ===================== แชทกับเจ้าหน้าที่ (เฉพาะสมาชิกที่ล็อกอิน) =====================
    if (botUserId) {
        window.Echo = new Echo({
            broadcaster: 'pusher',
            key: '{{ env("PUSHER_APP_KEY") }}',
            cluster: '{{ env("PUSHER_APP_CLUSTER") }}',
            forceTLS: true
        });

        window.Echo.channel('support-chat.' + botUserId)
            .listen('.message.sent', (e) => {
                const msg = e.messageData;
                if (msg.sender_type === 'admin') {
                    appendAgentMessage(msg);
                    scrollToBottom();
                }
            });
    }

    function appendAgentMessage(msg) {
        let timeStr = '';
        if (msg.created_at) {
            timeStr = new Date(msg.created_at).toLocaleTimeString('th-TH', {hour: '2-digit', minute:'2-digit'});
        }
        const align = msg.sender_type === 'customer' ? 'text-end' : 'text-start';
        const cls = msg.sender_type === 'customer' ? 'msg-customer' : 'msg-admin';
        chatBox.insertAdjacentHTML('beforeend', `
            <div class="chat-msg ${cls}">
                ${escapeHtml(msg.message)}
                <div class="time-text ${align}">${timeStr}</div>
            </div>`);
    }

    function loadAgentHistory() {
        fetch(`{{ route('support-chat.history') }}?topic=${chatTopic}`)
            .then((res) => res.json())
            .then((json) => {
                chatBox.innerHTML = '';
                const list = json.data || [];
                if (list.length === 0) {
                    chatBox.innerHTML = '<div class="text-center text-muted" style="font-size:0.8rem;">เริ่มการสนทนากับเจ้าหน้าที่ได้เลยครับ</div>';
                } else {
                    list.forEach((m) => appendAgentMessage(m));
                }
                scrollToBottom();
            })
            .catch(() => {
                chatBox.innerHTML = '<div class="text-center text-danger" style="font-size:0.8rem;">โหลดข้อมูลล้มเหลว กรุณาลองใหม่อีกครั้ง</div>';
            });
    }

    function handleEnter(e) { if (e.key === 'Enter') sendMessage(); }

    function sendMessage() {
        const text = chatInput.value.trim();
        if (!text) return;
        chatInput.value = '';

        appendAgentMessage({ message: text, sender_type: 'customer', created_at: new Date() });
        scrollToBottom();

        fetch("{{ route('support-chat.send') }}", {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ topic: chatTopic, message: text })
        }).catch(() => {});
    }

    // ===================== สลับโหมด บอท <-> เจ้าหน้าที่ =====================

    function switchToAgent() {
        if (!botUserId) { window.location.href = loginUrl; return; }
        document.getElementById('botPanel').style.display = 'none';
        const agentPanel = document.getElementById('agentPanel');
        agentPanel.style.display = 'flex';
        document.getElementById('panelTitle').textContent = 'ปรึกษาผู้เชี่ยวชาญ';
        document.getElementById('toggleAgentBtn').style.display = 'none';

        if (!agentHistoryLoaded) {
            loadAgentHistory();
            agentHistoryLoaded = true;
        }

        if (lastUnansweredQuestion) {
            chatInput.value = lastUnansweredQuestion;
            sendMessage();
            lastUnansweredQuestion = null;
        }
        scrollToBottom();
    }

    function switchToBot() {
        if (document.getElementById('agentPanel')) {
            document.getElementById('agentPanel').style.display = 'none';
        }
        document.getElementById('botPanel').style.display = 'flex';
        document.getElementById('panelTitle').textContent = 'ถามบอทตอบอัตโนมัติ';
        const toggleBtn = document.getElementById('toggleAgentBtn');
        if (toggleBtn) toggleBtn.style.display = 'inline-flex';
    }

    // ===================== Chat Bot เมนูปุ่มกด =====================

    let botTopicsCache = [];
    let botServicesCache = {};
    let botServiceDetailCache = {};
    let currentTopicId = null;
    let currentServiceId = null;

    async function apiGet(url) {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        return res.json();
    }
    async function apiPost(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(body)
        });
        return res.json();
    }

    function renderBotHtml(html) {
        const div = document.createElement('div');
        div.className = 'chat-msg msg-bot';
        div.innerHTML = html;
        botChatBox.appendChild(div);
        scrollToBottomBot();
        return div;
    }
    function renderUserHtml(html) {
        const div = document.createElement('div');
        div.className = 'chat-msg msg-customer';
        div.innerHTML = html;
        botChatBox.appendChild(div);
        scrollToBottomBot();
        return div;
    }

    function findTopic(id) { return botTopicsCache.find(t => t.id === id); }
    function findService(topicId, id) { return (botServicesCache[topicId] || []).find(s => s.id === id); }

    async function initBot() {
        renderBotHtml('สวัสดีค่ะ/ครับ AEG ยินดีต้อนรับ<br>หากท่านมีข้อสงสัยหรือสนใจอุปกรณ์ใดสามารถเลือกหัวข้อที่ต้องการ หรือพิมพ์สอบถามได้ที่ช่องทางนี้เลยค่ะ');
        await goMainMenu();
    }

    async function goMainMenu() {
        currentTopicId = null;
        currentServiceId = null;
        if (botTopicsCache.length === 0) {
            const json = await apiGet(botUrls.topics);
            botTopicsCache = json.data || [];
        }
        renderMainMenu();
    }

    function renderMainMenu() {
        const btns = botTopicsCache.map(t => `<button type="button" class="bot-btn" onclick="selectTopic(${t.id})">${escapeHtml(t.name_th)}</button>`).join('');
        renderBotHtml(`เลือกหัวข้อที่ต้องการได้เลยครับ<div class="bot-buttons">${btns}</div>`);
    }

    async function selectTopic(topicId) {
        const t = findTopic(topicId);
        renderUserHtml(escapeHtml(t ? t.name_th : ''));
        currentTopicId = topicId;
        currentServiceId = null;

        if (!botServicesCache[topicId]) {
            const json = await apiGet(botUrls.topicsBase + '/' + topicId + '/services');
            botServicesCache[topicId] = json.data || [];
        }
        renderTopicMenu(topicId);
    }

    function renderTopicMenu(topicId) {
        const t = findTopic(topicId);
        const services = botServicesCache[topicId] || [];
        const btns = services.map(s => `<button type="button" class="bot-btn" onclick="selectService(${s.id})">${escapeHtml(s.name_th)}</button>`).join('');
        const backBtn = `<button type="button" class="bot-btn bot-btn-outline" onclick="goMainMenu()">← กลับเมนูหลัก</button>`;
        renderBotHtml(`หัวข้อ "${escapeHtml(t ? t.name_th : '')}" มีบริการดังนี้ครับ เลือกได้เลย<div class="bot-buttons">${btns}${backBtn}</div>`);
    }

    async function selectService(serviceId) {
        const s = findService(currentTopicId, serviceId);
        renderUserHtml(escapeHtml(s ? s.name_th : ''));
        currentServiceId = serviceId;

        if (!botServiceDetailCache[serviceId]) {
            const json = await apiGet(botUrls.servicesBase + '/' + serviceId);
            botServiceDetailCache[serviceId] = json.data;
        }
        renderServiceActions();
    }

    function goServiceMenu() { renderTopicMenu(currentTopicId); }
    function goServiceActions() { renderServiceActions(); }

    function renderServiceActions() {
        const detail = botServiceDetailCache[currentServiceId];
        if (!detail) return;
        let btns = `<button type="button" class="bot-btn" onclick="showServiceInfo()">ข้อมูลบริการ</button>`;
        if (detail.faqs && detail.faqs.length) {
            btns += `<button type="button" class="bot-btn" onclick="showFaqList()">แก้ปัญหาเบื้องต้น (FAQ)</button>`;
        }
        if (detail.has_technician_contact) {
            btns += `<button type="button" class="bot-btn" onclick="showTechnicianContact()">ติดต่อช่าง</button>`;
        }
        if (detail.has_claim) {
            btns += `<button type="button" class="bot-btn" onclick="showLeadForm('claim')">แจ้งเคลม</button>`;
        }
        if (detail.has_purchase_interest) {
            btns += `<button type="button" class="bot-btn" onclick="handlePurchaseInterest()">สนใจซื้อบริการ</button>`;
        }
        btns += `<button type="button" class="bot-btn bot-btn-outline" onclick="goServiceMenu()">← กลับ</button>`;
        renderBotHtml(`เลือกสิ่งที่ต้องการได้เลยครับ<div class="bot-buttons">${btns}</div>`);
    }

    function showServiceInfo() {
        renderUserHtml('ข้อมูลบริการ');
        const detail = botServiceDetailCache[currentServiceId];
        let html = (detail.info_th ? escapeHtml(detail.info_th).replace(/\n/g, '<br>') : 'ยังไม่มีข้อมูลบริการนี้');
        if (detail.extra_info) {
            Object.keys(detail.extra_info).forEach((key) => {
                html += `<div class="bot-subhead">${escapeHtml(key)}</div>`;
                html += `<div>${escapeHtml(detail.extra_info[key]).replace(/\n/g, '<br>')}</div>`;
            });
        }
        html += `<div class="bot-buttons"><button type="button" class="bot-btn bot-btn-outline" onclick="goServiceActions()">← กลับ</button></div>`;
        renderBotHtml(html);
    }

    function showFaqList() {
        renderUserHtml('แก้ปัญหาเบื้องต้น (FAQ)');
        const detail = botServiceDetailCache[currentServiceId];
        const btns = (detail.faqs || []).map((f, idx) => `<button type="button" class="bot-btn" onclick="showFaqAnswer(${idx})">${escapeHtml(f.question_th)}</button>`).join('');
        const backBtn = `<button type="button" class="bot-btn bot-btn-outline" onclick="goServiceActions()">← กลับ</button>`;
        renderBotHtml(`เลือกคำถามที่ตรงกับปัญหาของท่านได้เลยครับ<div class="bot-buttons">${btns}${backBtn}</div>`);
    }

    function showFaqAnswer(idx) {
        const detail = botServiceDetailCache[currentServiceId];
        const faq = (detail.faqs || [])[idx];
        if (!faq) return;
        renderUserHtml(escapeHtml(faq.question_th));
        const answerHtml = escapeHtml(faq.answer_th).replace(/\n/g, '<br>');
        let btns = `<button type="button" class="bot-btn bot-btn-outline" onclick="showFaqList()">← กลับรายการคำถาม</button>`;
        if (detail.has_technician_contact) {
            btns += `<button type="button" class="bot-btn" onclick="showTechnicianContact()">ยังไม่หาย ติดต่อช่าง</button>`;
        }
        renderBotHtml(`${answerHtml}<div class="bot-buttons">${btns}</div>`);
    }

    function showTechnicianContact() {
        renderUserHtml('ติดต่อช่าง');
        const btns = `
            <button type="button" class="bot-btn" onclick="switchToAgent()">ติดต่อเจ้าหน้าที่</button>
            <button type="button" class="bot-btn" onclick="goToRepairRequest()">แจ้งซ่อม</button>
            <button type="button" class="bot-btn bot-btn-outline" onclick="goServiceActions()">← กลับ</button>`;
        renderBotHtml(`เลือกได้เลยครับ ต้องการคุยกับเจ้าหน้าที่ทันที หรือไปที่หน้าแจ้งซ่อมเพื่อเลือกอุปกรณ์/แพ็กเกจที่ต้องการแจ้งซ่อม<div class="bot-buttons">${btns}</div>`);
    }

    function goToRepairRequest() {
        window.location.href = botUserId ? botUrls.packagesMine : loginUrl;
    }

    function handlePurchaseInterest() {
        const detail = botServiceDetailCache[currentServiceId];
        if (detail.purchase_link_route && botUrls.routeMap[detail.purchase_link_route]) {
            renderUserHtml('สนใจซื้อบริการ');
            renderBotHtml('กำลังพาไปยังหน้าบริการให้เลยครับ...');
            window.location.href = botUrls.routeMap[detail.purchase_link_route];
            return;
        }
        showLeadForm('purchase');
    }

    function showLeadForm(type) {
        renderUserHtml(type === 'claim' ? 'แจ้งเคลม' : 'สนใจซื้อบริการ');
        const formHtml = `
            <div class="bot-form">
                <input type="text" id="leadName" class="bot-input" placeholder="ชื่อ-นามสกุล">
                <input type="text" id="leadPhone" class="bot-input" placeholder="เบอร์โทรศัพท์">
                <input type="email" id="leadEmail" class="bot-input" placeholder="อีเมล (ถ้ามี)">
                <textarea id="leadMessage" class="bot-input bot-textarea" placeholder="${type === 'claim' ? 'รายละเอียดที่ต้องการแจ้งเคลม' : 'รายละเอียดเพิ่มเติม (ถ้ามี)'}"></textarea>
                <div class="bot-buttons">
                    <button type="button" class="bot-btn" onclick="submitLeadForm('${type}')">ส่งข้อมูล</button>
                    <button type="button" class="bot-btn bot-btn-outline" onclick="goServiceActions()">← ยกเลิก</button>
                </div>
            </div>`;
        renderBotHtml(`กรอกข้อมูลเพื่อให้ผู้เชี่ยวชาญติดต่อกลับได้เลยครับ${formHtml}`);
    }

    async function submitLeadForm(type) {
        const name = document.getElementById('leadName').value.trim();
        const phone = document.getElementById('leadPhone').value.trim();
        const email = document.getElementById('leadEmail').value.trim();
        const message = document.getElementById('leadMessage').value.trim();
        if (!name || !phone) {
            alert('กรุณากรอกชื่อและเบอร์โทรศัพท์');
            return;
        }
        const s = findService(currentTopicId, currentServiceId);
        const t = findTopic(currentTopicId);
        const json = await apiPost(botUrls.leads, {
            type: type,
            name: name,
            phone: phone,
            email: email || null,
            message: message || null,
            topic_key: t ? t.key : null,
            service_name: s ? s.name_th : null,
        });
        const data = json.data || {};
        if (data.require_membership) {
            renderBotHtml(`ขออภัยครับ ต้องเข้าสู่ระบบก่อนจึงจะใช้งานส่วนนี้ได้<div class="bot-buttons"><a href="${loginUrl}" class="bot-btn">เข้าสู่ระบบ</a></div>`);
            return;
        }
        renderBotHtml((json.message || 'ขอบคุณครับ ทีมงานจะติดต่อกลับโดยเร็วที่สุด') + `<div class="bot-buttons"><button type="button" class="bot-btn bot-btn-outline" onclick="goServiceActions()">← กลับ</button></div>`);
    }

    // ===================== พิมพ์คำถามอิสระ (จับ Keyword) =====================

    function handleBotEnter(e) { if (e.key === 'Enter') submitBotText(); }

    async function submitBotText() {
        const text = botChatInput.value.trim();
        if (!text) return;
        botChatInput.value = '';
        renderUserHtml(escapeHtml(text));
        const typingEl = renderBotHtml('<i>กำลังค้นคำตอบ...</i>');

        let json;
        try {
            json = await apiPost(botUrls.search, { message: text, topic_id: currentTopicId });
        } catch (e) {
            typingEl.remove();
            renderBotHtml('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง');
            return;
        }
        typingEl.remove();

        const data = json.data || {};
        if (data.require_membership) {
            renderBotHtml(`ขออภัยครับ ต้องเข้าสู่ระบบก่อนจึงจะถามคำถามเพิ่มเติมได้<div class="bot-buttons"><a href="${loginUrl}" class="bot-btn">เข้าสู่ระบบ</a></div>`);
            return;
        }

        if (data.matched) {
            renderBotHtml(escapeHtml(data.answer).replace(/\n/g, '<br>'));
        } else if (botUserId) {
            lastUnansweredQuestion = text;
            renderBotHtml(escapeHtml(data.escalation_message || 'ทางเราได้รับเรื่องแล้ว กำลังส่งต่อให้เจ้าหน้าที่ครับ'));
            setTimeout(() => { if (lastUnansweredQuestion) switchToAgent(); }, 1200);
        } else {
            renderBotHtml(`ขออภัยครับ บอทยังไม่พบคำตอบที่ตรงกับคำถามนี้ กรุณาเข้าสู่ระบบเพื่อคุยกับเจ้าหน้าที่ต่อได้เลยครับ<div class="bot-buttons"><a href="${loginUrl}" class="bot-btn">เข้าสู่ระบบ</a></div>`);
        }
    }

    // ===================== ให้คะแนน / จบการสนทนา =====================

    let selectedRating = 0;

    function openRatingModal() {
        document.getElementById('ratingModal').style.display = 'flex';
    }
    function closeRatingModalOnly() {
        document.getElementById('ratingModal').style.display = 'none';
    }

    function setRating(value) {
        selectedRating = value;
        document.querySelectorAll('#ratingStars i').forEach((el) => {
            const v = parseInt(el.dataset.value, 10);
            el.classList.toggle('fas', v <= value);
            el.classList.toggle('far', v > value);
        });
        document.getElementById('ratingSubmitBtn').disabled = false;
    }

    async function submitRatingAndClose() {
        if (!selectedRating) return;
        const comment = document.getElementById('ratingComment').value.trim();
        try {
            await apiPost(botUrls.rating, { rating: selectedRating, comment: comment || null });
        } catch (e) { /* เงียบไว้ ไม่ให้ปิดการสนทนาไม่ได้เพราะ network error */ }
        closeRatingModalOnly();
        resetBotConversation();
    }

    function resetBotConversation() {
        selectedRating = 0;
        document.getElementById('ratingComment').value = '';
        document.querySelectorAll('#ratingStars i').forEach((el) => { el.classList.remove('fas'); el.classList.add('far'); });
        document.getElementById('ratingSubmitBtn').disabled = true;
        botChatBox.innerHTML = '';
        lastUnansweredQuestion = null;
        switchToBot();
        initBot();
    }

    // เริ่มการสนทนาบอทตอนโหลด widget นี้
    initBot();
</script>
