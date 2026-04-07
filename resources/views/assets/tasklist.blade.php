@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการสินทรัพย์ - AEG</title>
    <link rel="stylesheet" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <div class="intro-y box p-5 mt-5">
        <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
            <div class="font-medium text-base truncate">ประวัติการแจ้งซ่อม/บำรุงรักษา</div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-report">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">วันที่</th>
                        <th class="whitespace-nowrap">บ้าน/อาคาร</th>
                        <th class="whitespace-nowrap">ชื่อสินทรัพย์</th>
                        <th class="whitespace-nowrap">ปัญหาที่แจ้ง</th>
                        <th class="text-center whitespace-nowrap">ผู้ดำเนินการ</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports->sortByDesc('created_at') as $report)
                    <tr class="intro-x">
                        <td class="whitespace-nowrap">{{ $report->created_at->addYears(543)->format('d/m/Y') }}</td>
                        <?php
                            $house = DB::Table('houses')->where('house_id', $report->asset->house_id)->first();
                        ?>
                        <td class="whitespace-nowrap">{{ $house ? $house->house_name : 'ไม่ระบุบ้าน/อาคาร' }}</td>
                        <td class="whitespace-nowrap">{{ $report->asset->asset_name }}</td>
                        <td>{{ Str::limit($report->problem, 80) }}</td>
                        <td class="text-center">{{ $report->user_task }}</td>
                        <td class="table-report__action w-56">
                            <div class="flex justify-center items-center">
                                <a class="flex items-center mr-3 text-primary" href="{{ route('report.asset.pdf', $report->report_id) }}">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-1"></i> PDF
                                </a>
                                @if(in_array(Auth::user()->role_id, [1,2,5]) || $report->created_by == Auth::user()->user_id)
                                <button class="flex items-center text-danger" onclick="deleteAssetReport('{{ $report->report_id }}')">
                                    <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ลบ
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-1.12.4.js" integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU=" crossorigin="anonymous"></script>
<script src="{{ asset('dist/js/datatables.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        // ถ้าใช้ Filter จาก Server-side แล้ว DataTables Client-side อาจจะไม่จำเป็นต้องใช้ Search ของมันซ้อนทับกัน
        // แต่ถ้าข้อมูลไม่เยอะ ใช้ DataTables ช่วย sort ก็สะดวกดีครับ
        new DataTable('.datatable', {
            "ordering": false, // ปิด ordering ถ้ารู้สึกว่ามันตีกับ query ที่เรา order มาจาก controller
            "info": false,
            "lengthChange": false,
            "searching": false // ปิด search ของ datatable เพราะเราอาจจะทำ search เองข้างบน (ถ้าต้องการเปิด ให้เปลี่ยนเป็น true)
        });
    });

    function deleteAssetReport(report_id) {
        if (confirm('คุณแน่ใจหรือไม่ว่าต้องการลบรายงานนี้? การกระทำนี้ไม่สามารถย้อนกลับได้')) {

            $.ajax({
                url: "/reports/asset/delete/" + report_id,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}" // ส่ง CSRF Token ไปด้วย
                },
                success: function(response) {
                    // ลบแถวออกจาก Table ทันทีโดยไม่ต้อง Reload (UX จะดีกว่า)
                    $('button[onclick="deleteAssetReport(\'' + report_id + '\')"]').closest('tr').fadeOut(500, function() {
                        $(this).remove();
                        alert('ลบข้อมูลสำเร็จ');
                    });
                },
                error: function(xhr) {
                    if (xhr.status === 403) {
                        alert('คุณไม่มีสิทธิ์ลบรายงานนี้ (เฉพาะเจ้าของหรือผู้ดูแลระบบ)');
                    } else {
                        alert('ไม่สามารถลบข้อมูลได้ กรุณาลองใหม่');
                    }
                }
            });
        }
    }
</script>
@endsection
