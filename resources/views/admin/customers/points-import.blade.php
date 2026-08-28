@extends('../layout/' . $layout)

@section('subhead')
    <title>นำเข้าแต้มลูกค้า - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">นำเข้าแต้มลูกค้าจากไฟล์ Excel</h2>
        <a href="{{ route('admin.customers') }}" class="btn btn-outline-secondary w-24">กลับ</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show flex items-center mb-2 mt-5" role="alert">
            <i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> {{ session('success') }}
            <button type="button" class="btn-close text-white" data-tw-dismiss="alert" aria-label="Close"> <i data-lucide="x" class="w-4 h-4"></i> </button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible show flex items-center mb-2 mt-5" role="alert">
            <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> {{ session('error') }}
            <button type="button" class="btn-close text-white" data-tw-dismiss="alert" aria-label="Close"> <i data-lucide="x" class="w-4 h-4"></i> </button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger show mb-2 mt-5">
            <ul class="mb-0">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-5">
            <div class="box p-5">
                <div class="font-medium text-base border-b border-slate-200/60 pb-5 mb-3">อัปโหลดไฟล์</div>

                <div class="text-slate-500 text-sm mb-4">
                    ไฟล์ต้องมี 2 คอลัมน์ โดย<b>แถวแรกเป็นหัวตาราง</b> (จะถูกข้ามอัตโนมัติ):<br>
                    คอลัมน์ที่ 1 = เบอร์โทรศัพท์ลูกค้า<br>
                    คอลัมน์ที่ 2 = จำนวนแต้มที่จะบวกเพิ่ม<br><br>
                    รองรับไฟล์ .xlsx, .xls, .csv ขนาดไม่เกิน 5MB
                </div>

                <form action="{{ route('admin.customers.points-import.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <i data-lucide="upload" class="w-4 h-4 mr-1"></i> อัปโหลดและนำเข้าแต้ม
                    </button>
                </form>

                <div class="text-warning text-xs mt-4">
                    <i data-lucide="alert-triangle" class="w-3 h-3 inline mr-1"></i>
                    การนำเข้าจะบวกแต้มเข้ากระเป๋าลูกค้าจริงทันทีเมื่อกดอัปโหลด แนะนำให้ตรวจสอบไฟล์ให้ถูกต้องก่อนกดทุกครั้ง (ยกเลิก/ย้อนกลับทีหลังต้องปรับแต้มคืนเองเป็นรายคน)
                </div>
            </div>
        </div>

        <div class="intro-y col-span-12 lg:col-span-7">
            <div class="box p-5">
                <div class="font-medium text-base border-b border-slate-200/60 pb-5 mb-3">ประวัติการนำเข้าที่ผ่านมา</div>
                <div class="overflow-x-auto">
                    <table class="table table-report -mt-2 w-full">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">วันที่นำเข้า</th>
                                <th class="whitespace-nowrap">ชื่อไฟล์</th>
                                <th class="text-center whitespace-nowrap">ทั้งหมด</th>
                                <th class="text-center whitespace-nowrap">สำเร็จ</th>
                                <th class="text-center whitespace-nowrap">ไม่สำเร็จ</th>
                                <th class="text-center whitespace-nowrap">รายละเอียด</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batches as $batch)
                                <tr>
                                    <td class="whitespace-nowrap text-xs">{{ \Carbon\Carbon::parse($batch->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="text-xs">{{ $batch->original_filename ?? '-' }}</td>
                                    <td class="text-center">{{ $batch->total_rows }}</td>
                                    <td class="text-center text-success font-medium">{{ $batch->success_count }}</td>
                                    <td class="text-center {{ $batch->fail_count > 0 ? 'text-danger font-medium' : '' }}">{{ $batch->fail_count }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.customers.points-import.show', $batch->id) }}" class="btn btn-sm btn-outline-primary">ดูรายละเอียด</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-slate-500 py-5">ยังไม่เคยนำเข้าแต้ม</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
