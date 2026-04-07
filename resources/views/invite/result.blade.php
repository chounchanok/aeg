<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ผลการเข้าร่วม - AEG</title>
    <link rel="stylesheet" href="{{ asset('dist/css/app.css') }}" />
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-slate-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg text-center">
        @if($status === 'success')
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="check" class="w-10 h-10 text-green-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2">สำเร็จ!</h2>
            <p class="text-slate-600 mb-8">{{ $message }}</p>
        @else
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i data-lucide="x" class="w-10 h-10 text-red-600"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 mb-2">เกิดข้อผิดพลาด</h2>
            <p class="text-slate-600 mb-8">{{ $message }}</p>
        @endif

        <p class="text-xs text-slate-400 mt-6">คุณสามารถปิดหน้านี้ แล้วกลับไปใช้งานที่แอปพลิเคชันได้เลย</p>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
