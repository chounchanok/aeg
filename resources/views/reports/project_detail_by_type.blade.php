@extends('../layout/' . $layout)

@section('subhead')
    <title>รายละเอียดโครงการประเภท: {{ $projectType }}</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายละเอียดโครงการ (Drill-down)</h2>
    <p class="intro-y text-sm text-slate-500 mt-2">รายการโครงการทั้งหมดภายใต้ประเภท: <span class="font-bold text-primary">{{ $projectType }}</span> (ทั้งหมด {{ $projects->count() }} โครงการ)</p>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12">
            <a href="{{ url('reports/resourceAllocationSummary') }}" class="btn btn-outline-secondary w-24">
                <i class="w-4 h-4 mr-2" data-lucide="corner-down-left"></i> กลับ
            </a>
        </div>

        <!-- BEGIN: Data Table -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">รหัส/ชื่อโครงการ</th>
                        <th class="whitespace-nowrap text-center">ความคืบหน้า (%)</th>
                        <th class="whitespace-nowrap text-center">Asset ที่ใช้ (รายการ)</th>
                        <th class="whitespace-nowrap text-center">สมาชิกทีมงาน</th>
                        <th class="whitespace-nowrap text-center">วันสิ้นสุด</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        @php
                            // กำหนดสี Progress Bar
                            $progressColor = $project->progress >= 100 ? 'bg-success' : ($project->progress > 50 ? 'bg-warning' : 'bg-primary');
                        @endphp
                        <tr class="intro-x">
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $project->project_name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $project->project_code }}</div>
                            </td>
                            <td class="w-40">
                                <div class="flex items-center justify-center">
                                    <div class="w-full h-1 bg-slate-200 mr-3">
                                        <div class="h-1 {{ $progressColor }}" style="width: {{ $project->progress > 100 ? 100 : $project->progress }}%"></div>
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $project->progress }}%</div>
                                </div>
                            </td>
                            <td class="text-center whitespace-nowrap font-medium">{{ $project->assets_count }}</td>
                            <td class="text-center whitespace-nowrap">
                                @if (is_array($project->team_members) && count($project->team_members) > 0)
                                    {{ count($project->team_members) }} คน
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center whitespace-nowrap">
                                @if ($project->end_date && $project->end_date->isPast() && $project->progress < 100)
                                    <span class="text-danger">{{ $project->end_date->format('Y-m-d') }} (ล่าช้า)</span>
                                @else
                                    {{ $project->end_date?->format('Y-m-d') ?? '-' }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-slate-500">ไม่พบโครงการในประเภท "{{ $projectType }}"</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- END: Data Table -->

    </div>
@endsection
