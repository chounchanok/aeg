@extends('../layout/' . $layout)

@section('subhead')
    <title>รายงานสถานะโครงการ - Project Status Report</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายงานสถานะและความคืบหน้าของโครงการ (Project Status Report)</h2>
    <p class="intro-y text-sm text-slate-500 mt-2">ติดตามความคืบหน้า, ระยะเวลา, และการใช้ทรัพยากรของแต่ละโครงการ</p>

    <div class="grid grid-cols-12 gap-6 mt-5">

        <!-- BEGIN: Data Table -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">รหัส/ชื่อโครงการ</th>
                        <th class="whitespace-nowrap text-center">ประเภท</th>
                        <th class="whitespace-nowrap text-center">ความคืบหน้า (%)</th>
                        <th class="whitespace-nowrap text-center">เริ่มต้น</th>
                        <th class="whitespace-nowrap text-center">สิ้นสุดที่คาดการณ์</th>
                        <th class="whitespace-nowrap text-center">สถานะเวลา</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($projects as $project)
                        @php
                            // กำหนดสี Progress Bar
                            $progressColor = $project->progress >= 100 ? 'bg-success' : ($project->progress > 50 ? 'bg-warning' : 'bg-primary');

                            // ตรวจสอบสถานะเวลา
                            $timeStatus = 'ปกติ';
                            $timeStatusColor = 'text-success';

                            if ($project->end_date) {
                                if ($project->end_date->isPast() && $project->progress < 100) {
                                    $timeStatus = 'ล่าช้า';
                                    $timeStatusColor = 'text-danger';
                                } elseif ($project->end_date->isFuture() && $project->end_date->diffInDays(now()) <= 30 && $project->progress < 100) {
                                    $timeStatus = 'ใกล้กำหนด';
                                    $timeStatusColor = 'text-warning';
                                } elseif ($project->progress >= 100) {
                                    $timeStatus = 'สำเร็จ';
                                    $timeStatusColor = 'text-success';
                                }
                            } else {
                                $timeStatus = 'ไม่ระบุ';
                                $timeStatusColor = 'text-slate-500';
                            }
                        @endphp
                        <tr class="intro-x">
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $project->project_name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $project->project_code }}</div>
                            </td>
                            <td class="text-center whitespace-nowrap">{{ $project->project_type ?? '-' }}</td>
                            <td class="w-40">
                                <div class="flex items-center">
                                    <div class="w-full h-1 bg-slate-200 mr-3">
                                        <div class="h-1 {{ $progressColor }}" style="width: {{ $project->progress > 100 ? 100 : $project->progress }}%"></div>
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $project->progress }}%</div>
                                </div>
                            </td>
                            <td class="text-center whitespace-nowrap">{{ $project->start_date?->format('Y-m-d') ?? '-' }}</td>
                            <td class="text-center whitespace-nowrap">{{ $project->end_date?->format('Y-m-d') ?? '-' }}</td>
                            <td class="text-center whitespace-nowrap">
                                <div class="{{ $timeStatusColor }} font-medium">
                                    {{ $timeStatus }}
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($projects->isEmpty())
                <div class="text-center py-10 text-slate-500">ไม่พบข้อมูลโครงการในระบบ</div>
            @endif
        </div>
        <!-- END: Data Table -->

    </div>
@endsection
