@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการสินค้าและบริการ - AEG Admin</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายการสินค้าและบริการ (Master Products)</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            {{-- 🌟 แยกหน้าจอสินค้า (อุปกรณ์) / แพ็กเกจ & บริการ --}}
            <div style="display:flex; gap:8px; flex-wrap:wrap; margin-right:auto;">
                @foreach(['all' => 'ทั้งหมด', 'equipment' => 'สินค้า (อุปกรณ์)', 'service' => 'แพ็กเกจ & บริการ'] as $g => $label)
                    <a href="{{ route('admin.products', $g === 'all' ? [] : ['group' => $g]) }}"
                       class="btn {{ ($group ?? 'all') === $g ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $label }}</a>
                @endforeach
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('admin.products.create', ['group' => 'equipment']) }}" class="btn btn-primary shadow-md">
                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มสินค้า
                </a>
                <a href="{{ route('admin.products.create', ['group' => 'service']) }}" class="btn btn-primary shadow-md">
                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มแพ็กเกจ/บริการ
                </a>
            </div>
        </div>
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full datatable">
                <thead>
                    <tr>
                        <th class="w-16">รูปภาพ</th>
                        <th>ชื่อสินค้า/บริการ</th>
                        <th class="text-center">ประเภท</th>
                        <th class="text-right">ราคาขาย</th>
                        <th class="text-center">ได้รับแต้ม</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $p)
                        <tr class="intro-x">
                            <td>
                                <div class="w-10 h-10 image-fit zoom-in">
                                    <img alt="Product" class="rounded-md border" src="{{ $p->image_url ?? asset('dist/images/preview-1.jpg') }}">
                                </div>
                            </td>
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $p->name_th }}</div>
                                <div class="text-slate-500 text-xs mt-0.5">{{ $p->name_en }}</div>
                                @if($p->compare_at_price > $p->price)
                                    <div class="text-slate-500 text-xs mt-0.5 line-through">ราคาเต็ม {{ number_format($p->compare_at_price, 2) }} ฿</div>
                                @endif
                            </td>
                            <td class="text-center">
                                @if(($p->category_group ?? null) === 'service') <span class="text-info">บริการ</span>
                                @elseif(($p->category_group ?? null) === 'package') <span class="text-warning">แพ็กเกจ</span>
                                @else <span class="text-success">อุปกรณ์</span> @endif
                                @if($p->category_title)<div class="text-xs text-slate-500">{{ $p->category_title }}</div>@endif
                                @if(!empty($p->visit_interval_months))
                                    <div class="text-xs text-primary">ดูแลทุก {{ $p->visit_interval_months }} เดือน{{ $p->auto_schedule ? ' · อัตโนมัติ' : '' }}</div>
                                @endif
                            </td>
                            <td class="text-right font-medium">{{ number_format($p->price, 2) }}</td>
                            <td class="text-center text-primary">{{ number_format($p->point_earn) }} Pt</td>
                            <td class="text-center">
                                <div class="flex items-center justify-center {{ $p->is_active ? 'text-success' : 'text-danger' }}">
                                    <i data-lucide="{{ $p->is_active ? 'check-square' : 'x-square' }}" class="w-4 h-4 mr-1"></i>
                                    {{ $p->is_active ? 'เปิดขาย' : 'ปิดการขาย' }}
                                </div>
                            </td>
                            <td class="table-report__action w-56 text-center">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.products.edit', $p->id) }}">
                                    <i data-lucide="edit" class="w-4 h-4 mr-1"></i> แก้ไข
                                </a>
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
        $('.datatable').DataTable({
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" }
        });
    });
</script>
@endsection