@extends('../layout/' . $layout)

@section('subhead')
    <title>รายละเอียดการนำเข้าแต้ม - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">รายละเอียดการนำเข้าแต้ม #{{ $batch->id }}</h2>
        <a href="{{ route('admin.customers.points-import') }}" class="btn btn-outline-secondary w-24">กลับ</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show flex items-center mb-2 mt-5" role="alert">
            <i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> {{ session('success') }}
            <button type="button" class="btn-close text-white" data-tw-dismiss="alert" aria-label="Close"> <i data-lucide="x" class="w-4 h-4"></i> </button>
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-6 sm:col-span-3">
            <div class="box p-4 text-center">
                <div class="text-slate-500 text-xs">ไฟล์</div>
                <div class="font-medium mt-1 text-sm truncate">{{ $batch->original_filename ?? '-' }}</div>
            </div>
        </div>
        <div class="intro-y col-span-6 sm:col-span-3">
            <div class="box p-4 text-center">
                <div class="text-slate-500 text-xs">ทั้งหมด</div>
                <div class="text-xl font-medium mt-1">{{ $batch->total_rows }}</div>
            </div>
        </div>
        <div class="intro-y col-span-6 sm:col-span-3">
            <div class="box p-4 text-center">
                <div class="text-slate-500 text-xs">สำเร็จ</div>
                <div class="text-xl font-medium mt-1 text-success">{{ $batch->success_count }}</div>
            </div>
        </div>
        <div class="intro-y col-span-6 sm:col-span-3">
            <div class="box p-4 text-center">
                <div class="text-slate-500 text-xs">ไม่สำเร็จ</div>
                <div class="text-xl font-medium mt-1 text-danger">{{ $batch->fail_count }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="box p-5">
                <div class="font-medium text-base border-b border-slate-200/60 pb-5 mb-3 text-success">รายการที่สำเร็จ</div>
                <div class="overflow-x-auto" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-report -mt-2 w-full">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">ลูกค้า</th>
                                <th class="whitespace-nowrap">เบอร์โทร</th>
                                <th class="text-right whitespace-nowrap">แต้มที่เพิ่ม</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($successRows as $row)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.customers.show', $row->user_id) }}">
                                            {{ $row->first_name ? $row->first_name . ' ' . $row->last_name : '#' . $row->user_id }}
                                        </a>
                                    </td>
                                    <td>{{ $row->phone }}</td>
                                    <td class="text-right text-success font-medium">+{{ number_format($row->amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-slate-500 py-5">ไม่มีรายการที่สำเร็จ</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="box p-5">
                <div class="font-medium text-base border-b border-slate-200/60 pb-5 mb-3 text-danger">รายการที่ไม่สำเร็จ</div>
                <div class="overflow-x-auto" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-report -mt-2 w-full">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">แถวที่</th>
                                <th class="whitespace-nowrap">เบอร์โทร (ตามไฟล์)</th>
                                <th class="whitespace-nowrap">สาเหตุ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($failDetails as $fail)
                                <tr>
                                    <td>{{ $fail['row'] ?? '-' }}</td>
                                    <td>{{ $fail['phone'] ?? '-' }}</td>
                                    <td class="text-danger">{{ $fail['reason'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-slate-500 py-5">ไม่มีรายการที่ผิดพลาด</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
