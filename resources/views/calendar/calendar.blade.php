@extends('../layout/' . $layout)

@section('subhead')
    <title>ปฏิทินตารางงาน - Build Smooth</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">ปฏิทินตารางงาน</h2>
    </div>
    <div class="grid grid-cols-12 gap-5 mt-5">
        <!-- BEGIN: ปฏิทินตารางงาน Content -->
        <div class="col-span-12 xl:col-span-12 2xl:col-span-12">
            <div class="box p-5">
                <div class="full-calendar" id="calendar"></div>
            </div>
        </div>
        <!-- END: ปฏิทินตารางงาน Content -->
    </div>

@endsection

@section('scripts')
@endsection

<script src="{{ asset('dist/js/jquery-3.7.1.min.js') }}?v={{ time() }}"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js?v={{ time() }}'></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'listWeek',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        },

        events: function (fetchInfo, successCallback, failureCallback) {
            let month = fetchInfo.start.getMonth() + 1;
            let year = fetchInfo.start.getFullYear();

            fetch(`/get_calendar_events?month=${month}&year=${year}`)
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => {
                    console.error('Error fetching events:', error);
                    failureCallback(error);
                });
        },

        dayHeaderFormat: { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' },

        // ✅ ใช้ SweetAlert2 และแก้ไขการดึงข้อมูลจาก extendedProps
        eventClick: function(info) {
            Swal.fire({
                title: 'ข้อมูลการนัดหมาย',
                html: `
                    <p><strong>ลูกค้า:</strong> ${info.event.extendedProps.customer || "ไม่ระบุ"}</p>
                    <p><strong>เวลา:</strong> ${info.event.extendedProps.timestart} ถึง ${info.event.extendedProps.timeend}</p>
                    <p><strong>ช่าง:</strong> ${info.event.extendedProps.employee || "ไม่ระบุ"}</p>
                    <p><strong>สถานะ:</strong> ${info.event.extendedProps.status || "ไม่ระบุ"}</p>
                `,
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'ปิด',
                cancelButtonText: 'ดูรายละเอียดเพิ่มเติม',
                buttonsStyling: false, 
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-primary'
                }
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    window.open(`book_form_edit/${info.event.id}`, '_blank');  // เปิดในแท็บใหม่
                }
            });
        }
    });

    calendar.render();
});




</script>
