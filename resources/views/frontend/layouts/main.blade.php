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
</body>
</html>