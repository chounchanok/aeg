@extends('../layout/' . $layout)

@section('subhead')
    <title>รายการโครงการทั้งหมด - AEG</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <div class="intro-y box p-5 mt-5">
        <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
            <div class="font-medium text-base truncate">ประวัติรายงานความคืบหน้าโครงการ</div>
        </div>
        <div class="overflow-x-auto">
            <table class="table table-report">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">วันที่รายงาน</th>
                        <th class="whitespace-nowrap">โครงการ</th>
                        <th class="whitespace-nowrap">รายละเอียด</th>
                        <th class="text-center whitespace-nowrap">ความคืบหน้า</th>
                        <th class="text-center whitespace-nowrap">ผู้รายงาน</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports->sortByDesc('created_at') as $report)
                    <tr class="intro-x">
                        <td class="whitespace-nowrap">{{ $report->created_at->addYears(543)->format('d/m/Y H:i') }}</td>
                        <td class="whitespace-nowrap">{{ $report->project->project_name }}</td>
                        <td class="w-2/5">
                            <div class="text-slate-500 text-xs">{{ Str::limit($report->detail, 100) }}</div>
                        </td>
                        <td class="text-center">{{ $report->progress }}%</td>
                        <td class="text-center">{{ $report->user_task }}</td>
                        <td class="table-report__action w-56">
                            <div class="flex justify-center items-center">
                                <a class="flex items-center mr-3 text-primary" href="{{ route('report.project.pdf', $report->report_id) }}">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-1"></i> PDF
                                </a>
                                @if(in_array(Auth::user()->role_id, [1,2,5]) || $report->created_by == Auth::user()->user_id)
                                <button class="flex items-center text-danger" onclick="deleteReport('{{ $report->report_id }}')">
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
<script src="{{ asset('dist/js/datatables.min.js') }}"></script>
<script>
    $(document).ready(function(){
        $('.table').DataTable({
            paging: false,
            info: false
        });
    });

    function deleteReport(id) {
        if(confirm('ยืนยันการลบรายงานนี้?')) {
            $.ajax({
                url: "/reports/project/delete/" + id, // ตาม function deleteProjectReport
                type: 'DELETE',
                data: { _token: "{{ csrf_token() }}" },
                success: function(res) {
                    location.reload();
                }
            });
        }
    }
</script>
@endsection
