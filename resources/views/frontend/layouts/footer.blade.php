<footer>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-4 mb-4 mb-md-0 text-center text-md-start">
                <h5 class="fw-bold mb-3" style="font-size: 1rem;">ดาวน์โหลดแอปพลิเคชัน</h5>
                <div class="d-flex gap-2 justify-content-center justify-content-md-start">
                    <div class="bg-white p-2 rounded d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data=AEG-APP" class="w-100" alt="QR">
                    </div>
                    <div class="d-flex flex-column gap-2">
                        <a href="#"><img src="https://upload.wikimedia.org/wikipedia/commons/3/3c/Download_on_the_App_Store_Badge.svg" height="35" alt="App Store"></a>
                        <a href="#"><img src="https://upload.wikimedia.org/wikipedia/commons/7/78/Google_Play_Store_badge_EN.svg" height="35" alt="Play Store"></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-6 footer-column footer-divider text-center">
                        <h5 class="fw-bold mb-3" style="font-size: 0.95rem;">แพ็กเกจที่ใช้งาน</h5>
                        <a href="{{ route('terms-conditions') }}" class="footer-link">ข้อกำหนดและเงื่อนไข</a>
                    </div>
                    <div class="col-6 footer-column text-center">
                        <h5 class="fw-bold mb-3" style="font-size: 0.95rem;">ช่วยเหลือ</h5>
                        <a href="{{ route('privacy-policy') }}" class="footer-link">นโยบายความเป็นส่วนตัว</a>
                        <a href="{{ route('faq') }}" class="footer-link">คำถามที่พบบ่อย</a>
                    </div>
                </div>
                <div class="social-icons-bar mt-4">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-line"></i></a>
                </div>
            </div>
            <div class="col-md-4 text-center text-md-end mt-4 mt-md-0">
                <img src="{{ asset('assets/image/logo.webp') }}" alt="Logo" height="40" class="mb-3">
                <p class="copyright-text mb-0">© 2024 AEG EASE CLUB. All rights reserved.</p>
            </div>
        </div>
    </div>
</footer>