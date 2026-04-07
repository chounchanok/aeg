@extends('../layout/base')

@section('body')
    <body class="py-5">
        @yield('content')
        @include('../layout/components/dark-mode-switcher')
        @include('../layout/components/main-color-switcher')

        <!-- BEGIN: JS Assets-->
        <script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
        <script src="{{ asset('dist/js/jquery-3.7.1.min.js') }}?v={{ time() }}"></script>
        <script src="{{ mix('dist/js/app.js') }}?v={{ time() }}"></script>
        <script src="{{ asset('dist/js/datatables.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <!-- Lightbox2 JS -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
        <!-- END: JS Assets-->

        <!-- นำเข้า FullCalendar -->
        @yield('script')
    </body>
@endsection
