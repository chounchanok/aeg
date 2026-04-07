<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งรหัสผ่านใหม่ | Reset Password</title>
    <!-- โหลด Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* กำหนดฟอนต์ Inter และการปรับแต่งพื้นฐาน */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fb;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white p-8 shadow-2xl rounded-xl">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2 text-center">
            ตั้งรหัสผ่านใหม่
        </h1>
        <p class="text-center text-sm text-gray-500 mb-6">
            กรุณากรอกอีเมลและรหัสผ่านใหม่ที่คุณต้องการ
        </p>

        <!-- ฟอร์มหลักสำหรับรีเซ็ตรหัสผ่าน -->
        <form id="resetPasswordForm" class="space-y-6">

            <!-- Hidden Token Field -->
            <input type="hidden" id="token" name="token">

            <!-- สถานะการแจ้งเตือน (ซ่อนไว้จนกว่าจะมีการตอบกลับ) -->
            <div id="messageBox" class="p-3 rounded-lg text-sm hidden" role="alert"></div>

            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">อีเมล</label>
                <div class="mt-1">
                    <input
                        id="email"
                        name="email"
                        type="email"
                        required
                        placeholder="your@email.com"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out"
                    >
                </div>
            </div>

            <!-- New Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">รหัสผ่านใหม่</label>
                <div class="mt-1">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        minlength="8"
                        placeholder="รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out"
                    >
                </div>
            </div>

            <!-- Confirm New Password Field -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">ยืนยันรหัสผ่านใหม่</label>
                <div class="mt-1">
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        required
                        minlength="8"
                        placeholder="ยืนยันรหัสผ่านอีกครั้ง"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition duration-150 ease-in-out"
                    >
                </div>
            </div>

            <!-- Submit Button -->
            <div>
                <button
                    type="submit"
                    id="submitButton"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-50"
                >
                    รีเซ็ตรหัสผ่าน
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('resetPasswordForm');
            const tokenInput = document.getElementById('token');
            const messageBox = document.getElementById('messageBox');
            const submitButton = document.getElementById('submitButton');

            // URL ของ API Endpoint สำหรับ Reset Password
            // *** สำคัญ: หาก API ของคุณอยู่คนละโดเมนกับหน้านี้ โปรดเปลี่ยน URL ด้านล่างให้ถูกต้อง ***
            const API_URL = 'https://AEG.champagne.orangeworkshop.info/api/reset-password';

            /**
             * ฟังก์ชันสำหรับดึงค่า query parameter จาก URL
             * @param {string} name ชื่อของ parameter ที่ต้องการ
             * @returns {string | null} ค่าของ parameter หรือ null ถ้าหาไม่พบ
             */
            function getUrlParameter(name) {
                name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
                const regex = new RegExp('[\\?&]' + name + '=([^&#]*)');
                const results = regex.exec(location.search);
                return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
            }

            /**
             * แสดงข้อความแจ้งเตือน
             * @param {string} message ข้อความที่ต้องการแสดง
             * @param {boolean} isSuccess เป็นข้อความสำเร็จหรือไม่
             */
            function showMessage(message, isSuccess = false) {
                messageBox.textContent = message;
                messageBox.classList.remove('hidden', 'bg-red-100', 'text-red-700', 'bg-green-100', 'text-green-700');
                if (isSuccess) {
                    messageBox.classList.add('bg-green-100', 'text-green-700');
                } else {
                    messageBox.classList.add('bg-red-100', 'text-red-700');
                }
            }

            // 1. ดึง Token จาก URL เมื่อหน้าเว็บโหลด
            const token = getUrlParameter('token');
            if (!token) {
                showMessage("Token สำหรับรีเซ็ตรหัสผ่านไม่ถูกต้อง กรุณาขอลิงก์ใหม่", false);
                submitButton.disabled = true;
            } else {
                tokenInput.value = token;
            }

            // 2. จัดการการ Submit Form
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                messageBox.classList.add('hidden');
                submitButton.disabled = true;
                submitButton.textContent = 'กำลังดำเนินการ...';

                const data = {
                    email: document.getElementById('email').value,
                    token: tokenInput.value,
                    password: document.getElementById('password').value,
                    password_confirmation: document.getElementById('password_confirmation').value,
                };

                try {
                    const response = await fetch(API_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify(data),
                    });

                    const result = await response.json();

                    if (response.ok) {
                        showMessage(result.message || 'รีเซ็ตรหัสผ่านสำเร็จแล้ว! คุณสามารถเข้าสู่ระบบด้วยรหัสผ่านใหม่ได้ทันที', true);
                        // ล้างฟอร์มหลังจากสำเร็จ
                        form.reset();
                    } else {
                        // ดึง Error จาก Laravel Validation
                        let errorMessage = result.message || 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน';
                        if (result.errors) {
                            errorMessage += '\n' + Object.values(result.errors).map(e => e.join(', ')).join('\n');
                        }
                        showMessage(errorMessage, false);
                    }
                } catch (error) {
                    console.error('Error during fetch:', error);
                    showMessage('ไม่สามารถติดต่อเซิร์ฟเวอร์ได้ ลองใหม่อีกครั้งภายหลัง', false);
                } finally {
                    submitButton.disabled = false;
                    submitButton.textContent = 'รีเซ็ตรหัสผ่าน';
                }
            });
        });
    </script>
</body>
</html>
