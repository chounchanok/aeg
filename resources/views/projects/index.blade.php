@extends('../layout/' . $layout)

@section('subhead')
    <title>รายการโครงการทั้งหมด - AEG</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายการโครงการทั้งหมด</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <a href="{{ route('projects.create') }}" class="btn btn-primary shadow-md mr-2">เพิ่มโครงการใหม่</a>
        </div>

        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">รหัสโครงการ</th>
                        <th class="whitespace-nowrap">ชื่อโครงการ</th>
                        <th class="text-center whitespace-nowrap">ความคืบหน้า</th>
                        <th class="text-center whitespace-nowrap">วันที่เริ่ม</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        <tr class="intro-x">
                            <td class="w-40 font-medium">{{ $project->project_code }}</td>
                            <td>
                                <a href="{{ route('projects.edit', $project->project_id) }}" class="font-medium whitespace-nowrap">{{ $project->project_name }}</a>
                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ Str::limit($project->description, 50) }}</div>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center">
                                    <div class="w-full bg-slate-200 rounded-full h-1.5 dark:bg-darkmode-400 mr-2">
                                        <div class="bg-primary h-1.5 rounded-full" style="width: {{ $project->progress }}%"></div>
                                    </div>
                                    {{ $project->progress }}%
                                </div>
                            </td>
                            <td class="text-center">{{ $project->start_date ? $project->start_date->format('d/m/Y') : '-' }}</td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3 text-primary" href="{{ route('projects.edit', $project->project_id) }}">
                                        <i data-lucide="edit" class="w-4 h-4 mr-1"></i> แก้ไข
                                    </a>
                                    <form action="{{ route('projects.destroy', $project->project_id) }}" method="POST" onsubmit="return confirm('คุณแน่ใจหรือไม่ว่าต้องการลบโครงการนี้?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center text-danger">
                                            <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ลบ
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-5">
                {{ $projects->links() }}
            </div>
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
</script>
@endsection
