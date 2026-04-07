@extends('../layout/' . $layout)

@section('subhead')
    <title>รายงานการใช้สินทรัพย์ - Asset Utilization Report</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายงานการใช้สินทรัพย์ (Asset Utilization Report)</h2>
    <p class="intro-y text-sm text-slate-500 mt-2">ภาพรวมสถานะสินทรัพย์และบ้านที่กำลังใช้งาน</p>

    <div class="grid grid-cols-12 gap-6 mt-5">

        {{-- BEGIN: Report Filters / Summary Box (ตัวอย่าง) --}}
        <div class="col-span-12 intro-y box p-5">
            <div class="flex flex-wrap items-center">
                <div class="mr-auto">
                    <h3 class="font-medium text-base">สรุปสถานะโดยรวม</h3>
                    <p class="text-xs text-slate-500">สินทรัพย์ทั้งหมด: {{ $assets->count() }} รายการ</p>
                </div>
                {{-- ตัวอย่างการกรองสถานะ (อาจใช้ JS หรือ Livewire ในงานจริง) --}}
                <div class="flex items-center">
                    <label class="mr-2 text-slate-500 text-sm">กรองสถานะ:</label>
                    <select class="form-select form-select-sm w-36">
                        <option value="">ทั้งหมด</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">{{ $status }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        {{-- END: Report Filters / Summary Box --}}

        <!-- BEGIN: Data Table -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-report -mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Asset ID / Code</th>
                        <th class="whitespace-nowrap">ชื่อสินทรัพย์</th>
                        <th class="whitespace-nowrap text-center">สถานะ</th>
                        <th class="whitespace-nowrap">บ้านที่ใช้งาน</th>
                        <th class="whitespace-nowrap">ผู้รับผิดชอบ</th>
                        <th class="whitespace-nowrap text-center">ระยะเวลาใช้งาน (วัน)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $asset)
                        <tr class="intro-x">
                            <td class="w-20">
                                <p class="font-medium whitespace-nowrap">{{ $asset->asset_code ?? $asset->asset_id }}</p>
                            </td>
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $asset->asset_name }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ Str::limit($asset->description, 50) }}</div>
                            </td>
                            <td class="text-center">
                                @php
                                    // กำหนดสีตามสถานะ
                                    $statusClass = [
                                        'ใช้งาน' => 'text-success',
                                        'ว่าง' => 'text-pending',
                                        'ซ่อมบำรุง' => 'text-warning',
                                        'ชำรุด' => 'text-danger',
                                    ][$asset->status ?? 'ว่าง'] ?? 'text-slate-500';
                                @endphp
                                <div class="{{ $statusClass }} whitespace-nowrap">{{ $asset->status ?? 'ไม่ระบุ' }}</div>
                            </td>
                            <td>
                                @if ($asset->house)
                                    <div class="font-medium whitespace-nowrap">{{ $asset->house->house_name }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">Progress: {{ $asset->house->progress }}%</div>
                                @else
                                    <span class="text-slate-400">- ไม่ได้ผูกกับบ้าน -</span>
                                @endif
                            </td>
                            <td>
                                @if($asset->assigned_users_list->isNotEmpty())
                                    {{ $asset->assigned_users_list->map(function($user){
                                        return $user->first_name . ' ' . $user->last_name;
                                    })->implode(', ') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($asset->start_date && $asset->end_date)
                                    @php
                                        $diff = $asset->start_date->diffInDays($asset->end_date);
                                    @endphp
                                    {{ $diff }} วัน
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($assets->isEmpty())
                <div class="text-center py-10 text-slate-500">ไม่พบข้อมูลสินทรัพย์ในระบบ</div>
            @endif
        </div>
        <!-- END: Data Table -->

    </div>
@endsection
