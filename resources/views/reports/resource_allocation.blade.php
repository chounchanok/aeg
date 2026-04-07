@extends('../layout/' . $layout)

@section('subhead')
    <title>รายงานสรุปการจัดสรรทรัพยากร - Resource Allocation Summary</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายงานสรุปการจัดสรรทรัพยากร (Resource Allocation Summary)</h2>
    <p class="intro-y text-sm text-slate-500 mt-2">วิเคราะห์การใช้ทรัพยากร (Asset, ทีมงาน) ตามประเภทของโครงการ</p>

    <div class="grid grid-cols-12 gap-6 mt-5">

        {{-- BEGIN: Summary Box --}}
        <div class="col-span-12 intro-y box p-5">
            <h3 class="font-medium text-base">ภาพรวมการจัดสรร</h3>
            <p class="text-xs text-slate-500">จำนวนประเภทโครงการที่มีการบันทึก: {{ $summary->count() }} ประเภท</p>
            <p class="text-xs text-slate-500">รวม Asset ที่ใช้ทั้งหมด: {{ $summary->sum('total_assets') }} รายการ</p>
        </div>
        {{-- END: Summary Box --}}

        <!-- BEGIN: Data Table -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">ประเภทโครงการ</th>
                        <th class="whitespace-nowrap text-center">จำนวนโครงการทั้งหมด</th>
                        <th class="whitespace-nowrap text-center">โครงการที่กำลังดำเนินงาน</th>
                        <th class="whitespace-nowrap text-center">รวม Asset ที่ใช้ทั้งหมด</th>
                        <th class="whitespace-nowrap text-center">รวมจำนวนสมาชิกทีม (โดยประมาณ)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($summary as $projectType => $data)
                        <tr class="intro-x">
                            <td class="font-medium whitespace-nowrap">
                                <a href="{{ url('reports/projectDetailByType', ['projectType' => $projectType]) }}" class="text-primary hover:underline">
                                    {{ $projectType ?? 'ไม่ระบุประเภท' }}
                                </a>
                            </td>
                            <td class="text-center whitespace-nowrap">{{ $data['total_projects'] }}</td>
                            <td class="text-center whitespace-nowrap font-medium text-warning">{{ $data['in_progress_count'] }}</td>
                            <td class="text-center whitespace-nowrap font-medium text-primary">{{ $data['total_assets'] }}</td>
                            <td class="text-center whitespace-nowrap">{{ $data['total_team_members'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-10 text-slate-500">ไม่พบข้อมูลโครงการในระบบ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- END: Data Table -->

    </div>
@endsection
