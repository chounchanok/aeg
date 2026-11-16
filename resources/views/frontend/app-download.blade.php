<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('ดาวน์โหลดแอป AEG EASE CLUB') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background: #f1f5f9; margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .card { background: #fff; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,.08); max-width: 440px; width: 100%; padding: 40px 32px; text-align: center; border-top: 6px solid #bd1e2d; }
        h1 { font-size: 1.6rem; color: #0f172a; margin: 0 0 8px; }
        p { color: #64748b; margin: 0 0 28px; }
        .btn { display: block; text-decoration: none; padding: 16px; border-radius: 14px; font-size: 1.1rem; font-weight: 500; margin-bottom: 12px; color: #fff; }
        .ios { background: #0f172a; }
        .android { background: #1a7f37; }
        .muted { font-size: .85rem; color: #94a3b8; margin-top: 18px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>AEG EASE CLUB</h1>
        <p>{{ __('ดาวน์โหลดแอปได้ทั้ง iPhone และ Android') }}</p>
        <a class="btn ios" href="{{ url('/d/' . $code . '/ios') }}">App Store (iPhone / iPad)</a>
        <a class="btn android" href="{{ url('/d/' . $code . '/android') }}">Google Play (Android)</a>
        <div class="muted">{{ __('หรือสแกน QR Code ด้วยกล้องมือถือ ระบบจะพาไปสโตร์ที่ถูกต้องให้อัตโนมัติ') }}</div>
    </div>
</body>
</html>
