@extends('../layout/' . $layout)

@section('subhead')
    <title>เกณฑ์ระดับสมาชิก - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">เกณฑ์ระดับสมาชิก (Tier)</h2>
        <a href="{{ route('admin.customers') }}" class="btn btn-outline-secondary w-24">กลับ</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success show flex items-center mb-2 mt-5" role="alert">
            <i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger show mb-2 mt-5"><ul class="mb-0">@foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach</ul></div>
    @endif

    <div class="intro-y box p-5 mt-5">
        <div class="text-slate-500 text-sm mb-4">
            ลูกค้าจะอยู่ระดับที่<b>สูงสุด</b>ที่ถึงเกณฑ์ข้อใดข้อหนึ่ง (นับย้อนหลัง 12 เดือน):<br>
            • <b>แต้มสะสม</b> = แต้มที่ได้รับเข้ามา (การแลกของรางวัลไม่ทำให้ระดับตก)<br>
            • <b>ยอดซื้อ</b> = ออเดอร์ที่ชำระแล้ว + ใบแจ้งหนี้รายเดือนที่ชำระแล้ว + การจองตู้เซฟที่ชำระแล้ว — เว้นว่างถ้าไม่ใช้ยอดซื้อกับระดับนั้น<br>
            ขึ้นระดับทันทีเมื่อถึงเกณฑ์ และสถานะอยู่ได้ 1 ปีนับจากวันที่เปลี่ยน ครบ 1 ปีระบบจะประเมินใหม่ (ถึงเกณฑ์ = ต่ออายุ, ไม่ถึง = ปรับลด)
        </div>

        <form action="{{ route('admin.customers.tier-settings.update') }}" method="POST">
            @csrf
            <div class="overflow-x-auto">
                <table class="table table-report">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">ระดับ</th>
                            <th class="whitespace-nowrap">แต้มสะสมขั้นต่ำ (12 เดือน)</th>
                            <th class="whitespace-nowrap">หรือ ยอดซื้อขั้นต่ำ (บาท / 12 เดือน)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tiers as $i => $tier)
                            <tr>
                                <td class="font-medium">{{ $tier->name }} @if($i === 0)<span class="text-xs text-slate-500">(ระดับเริ่มต้น)</span>@endif</td>
                                <td><input type="number" min="0" step="1" name="tiers[{{ $tier->id }}][min_points]" value="{{ $tier->min_points }}" class="form-control w-48"></td>
                                <td><input type="number" min="0" step="0.01" name="tiers[{{ $tier->id }}][min_spending]" value="{{ $tier->min_spending !== null && (float) $tier->min_spending > 0 ? $tier->min_spending : '' }}" class="form-control w-48" placeholder="ไม่ใช้ยอดซื้อ"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="btn btn-primary mt-4">บันทึกเกณฑ์</button>
        </form>
    </div>
@endsection
