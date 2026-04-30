@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการสินค้าและบริการ - AEG Admin</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายการสินค้าและบริการ (Master Products)</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary shadow-md mr-2">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มสินค้าใหม่
            </a>
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
                                @if($p->type == 'service') <span class="text-info">บริการ</span>
                                @elseif($p->type == 'package') <span class="text-warning">แพ็กเกจ</span>
                                @else <span class="text-success">อุปกรณ์</span> @endif
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