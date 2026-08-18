<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กำลังเชื่อมต่อกับระบบชำระเงิน...</title>
    <style>
        body {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            height: 100vh; margin: 0; font-family: 'Kanit', sans-serif; background-color: #f8f9fa;
        }
        .loader {
            border: 5px solid #f3f3f3; border-top: 5px solid #1a2d5e; border-radius: 50%;
            width: 50px; height: 50px; animation: spin 1s linear infinite; margin-bottom: 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .text { color: #1a2d5e; font-weight: 500; font-size: 1.2rem; }
    </style>
</head>
<body>
    <div class="loader"></div>
    <div class="text">กำลังเชื่อมต่อกับระบบชำระเงิน Bangkok Bank...</div>

    <!-- ฟอร์มสำหรับส่งข้อมูลไป BBL (ถูกซ่อนไว้) -->
    <form id="bblForm" method="post" action="https://ipay.bangkokbank.com/b2c/eng/payment/payForm.jsp" style="display: none;">
        <input type="hidden" name="merchantId" value="{{ $merchantId }}">
        <input type="hidden" name="amount" value="{{ $amount }}">
        <input type="hidden" name="orderRef" value="{{ $orderRef }}">
        <input type="hidden" name="currCode" value="{{ $currCode }}">
        <input type="hidden" name="successUrl" value="{{ $successUrl }}">
        <input type="hidden" name="failUrl" value="{{ $failUrl }}">
        <input type="hidden" name="cancelUrl" value="{{ $cancelUrl }}">
        <input type="hidden" name="payType" value="{{ $payType }}">
        <input type="hidden" name="payMethod" value="{{ $payMethod }}">
        <input type="hidden" name="lang" value="E">
        <input type="hidden" name="secureHash" value="{{ $secureHash }}">
    </form>

    <script>
        // สั่งให้ฟอร์มกดยืนยันตัวเองทันทีที่หน้าเว็บโหลดเสร็จ
        window.onload = function() {
            document.getElementById('bblForm').submit();
        };
    </script>
</body>
</html>