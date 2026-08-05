<footer>
        <div class="container">
            <div class="row g-4 align-items-start">
                <div class="col-md-4 text-center text-md-start">
                    <h6 class="fw-bold mb-3">ดาวน์โหลดแอปพลิเคชัน</h6>
                    <div class="d-flex justify-content-center justify-content-md-start gap-3">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=AEG-APP"
                            class="bg-white p-1 rounded" alt="QR" width="80">
                        <div class="d-flex flex-column gap-2">
                            <a href="#"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg"
                                    height="28"></a>
                            <a href="#"><img
                                    src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg"
                                    height="28"></a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 text-center">
                    <div class="row">
                        <div class="col-6 footer-column footer-divider">
                            <a href="{{ route('packages.mine') }}" class="d-block text-white-50 text-decoration-none mb-2">แพ็กเกจที่ใช้งาน</a>
                            <a href="{{ route('terms-conditions') }}" class="d-block text-white-50 text-decoration-none mb-2">ข้อกำหนดและเงื่อนไข</a>
                        </div>
                        <div class="col-6 footer-column">
                            <a href="{{ route('faq') }}" class="d-block text-white-50 text-decoration-none mb-2">ช่วยเหลือ</a>
                            <a href="{{ route('privacy-policy') }}" class="d-block text-white-50 text-decoration-none mb-2">นโยบายความเป็นส่วนตัว</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 text-center text-md-end">
                    <img src="{{ asset('assets/image/logo.webp') }}" alt="Logo" height="40">
                </div>
            </div>

            <div class="row mt-5 align-items-center">
                <div class="col-md-4 d-none d-md-block"></div>
                <div class="col-md-4 text-center">
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-line"></i></a>
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0">
                    <p class="copyright-text mb-0">© 2024 AEG EASE CLUB. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating Chat -->
    @if(!Auth::user())
    <div class="floating-chat" style="position: fixed; bottom: 40px; right: 40px; z-index: 1000;">
        <div class="chat-circle"
            style="width: 60px; height: 60px; background: white; border-radius: 50%; box-shadow: 0 5px 25px rgba(0,0,0,0.15); display: flex; align-items: center; justify-content: center; border: 1px solid #f0f0f0;">
            <i class="fas fa-comment-dots text-danger fs-3"></i>
        </div>
    </div>
    @endif
