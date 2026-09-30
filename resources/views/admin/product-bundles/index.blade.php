@extends('../layout/' . $layout)

@section('subhead')
    <title>สินค้าจับกลุ่ม (Bundle) - AEG Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">สินค้าจับกลุ่ม (Bundle)</h2>
    <div class="text-slate-500 mt-1">
        เลือกสินค้าตั้งแต่ 2 ชิ้นขึ้นไปมารวมเป็นชุด แล้วตั้งราคาชุดใหม่เอง — ระบบจะคำนวณส่วนต่างจากราคาปกติให้อัตโนมัติ
        และหน้าตะกร้าฝั่งแอปจะแนะนำลูกค้าให้ซื้อสินค้าที่ขาดเพิ่มเพื่อรับราคาชุดนี้
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap items-center mt-2">
            <button class="btn btn-primary shadow-md mr-2" data-tw-toggle="modal" data-tw-target="#add-modal">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มชุดสินค้าใหม่
            </button>
        </div>

        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full">
                <thead>
                    <tr>
                        <th>ชื่อชุด</th>
                        <th>สินค้าที่รวมอยู่ในชุด</th>
                        <th class="text-right">ราคาปกติรวม</th>
                        <th class="text-right">ราคาชุด</th>
                        <th class="text-right">ประหยัด</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bundles as $bundle)
                        <tr class="intro-x">
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $bundle->name_th }}</div>
                                <div class="text-slate-500 text-xs mt-0.5">{{ $bundle->name_en }}</div>
                            </td>
                            <td>
                                @foreach ($bundle->items as $item)
                                    <span class="px-2 py-1 rounded-full text-xs bg-slate-100 text-slate-600 inline-block mb-1 mr-1">{{ $item->name_th }}</span>
                                @endforeach
                            </td>
                            <td class="text-right text-slate-500 line-through">{{ number_format($bundle->original_total, 2) }}</td>
                            <td class="text-right font-medium">{{ number_format($bundle->bundle_price, 2) }}</td>
                            <td class="text-right text-success font-medium">-{{ number_format($bundle->savings, 2) }}</td>
                            <td class="text-center">
                                <div class="flex items-center justify-center {{ $bundle->is_active ? 'text-success' : 'text-danger' }}">
                                    <i data-lucide="{{ $bundle->is_active ? 'check-square' : 'x-square' }}" class="w-4 h-4 mr-1"></i>
                                    {{ $bundle->is_active ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}
                                </div>
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <button type="button" class="flex items-center mr-3 text-primary btn-edit"
                                        data-tw-toggle="modal" data-tw-target="#edit-modal"
                                        data-bundle="{{ json_encode([
                                            'id' => $bundle->id,
                                            'name_th' => $bundle->name_th,
                                            'name_en' => $bundle->name_en,
                                            'bundle_price' => $bundle->bundle_price,
                                            'sort_order' => $bundle->sort_order,
                                            'is_active' => $bundle->is_active,
                                            'product_ids' => $bundle->items->pluck('product_id'),
                                        ]) }}">
                                        <i data-lucide="edit" class="w-4 h-4 mr-1"></i> แก้ไข
                                    </button>
                                    <form action="{{ route('admin.product-bundles.destroy', $bundle->id) }}" method="POST" class="btn-delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="flex items-center text-danger">
                                            <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ลบ
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-slate-500 py-5">ยังไม่มีสินค้าจับกลุ่ม — กด "เพิ่มชุดสินค้าใหม่" เพื่อเริ่มสร้างชุดแรก</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Modal -->
    <div id="add-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.product-bundles.store') }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">เพิ่มชุดสินค้าใหม่</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อชุด (TH)</label>
                        <input name="name_th" type="text" class="form-control" required placeholder="เช่น ชุดกล้องวงจรปิด + กล่องบันทึก">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อชุด (EN)</label>
                        <input name="name_en" type="text" class="form-control">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">สินค้าในชุด (เลือกอย่างน้อย 2 รายการ)</label>
                        <select name="product_ids[]" class="form-select select2-products" multiple required style="width: 100%">
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name_th }} ({{ number_format($p->price, 2) }} ฿)</option>
                            @endforeach
                        </select>
                        <div class="text-slate-500 text-xs mt-1">ราคาปกติรวมของสินค้าที่เลือก: <span class="add-original-total font-medium">0.00</span> ฿</div>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ราคาชุด (บาท)</label>
                        <input name="bundle_price" type="number" step="0.01" min="0" class="form-control add-bundle-price" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ลำดับการแสดงผล</label>
                        <input name="sort_order" type="number" class="form-control" value="0">
                    </div>
                    <div class="col-span-12 flex items-center mt-3">
                        <input name="is_active" type="checkbox" class="form-check-input border mr-2" checked value="1">
                        <label>เปิดใช้งานทันที</label>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="edit-form" method="POST" class="modal-content">
                @csrf
                @method('PUT')
                <div class="modal-header"><h2 class="font-medium text-base mr-auto">แก้ไขชุดสินค้า</h2></div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อชุด (TH)</label>
                        <input name="name_th" id="edit_name_th" type="text" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ชื่อชุด (EN)</label>
                        <input name="name_en" id="edit_name_en" type="text" class="form-control">
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">สินค้าในชุด (เลือกอย่างน้อย 2 รายการ)</label>
                        <select name="product_ids[]" id="edit_product_ids" class="form-select select2-products-edit" multiple required style="width: 100%">
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name_th }} ({{ number_format($p->price, 2) }} ฿)</option>
                            @endforeach
                        </select>
                        <div class="text-slate-500 text-xs mt-1">ราคาปกติรวมของสินค้าที่เลือก: <span class="edit-original-total font-medium">0.00</span> ฿</div>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ราคาชุด (บาท)</label>
                        <input name="bundle_price" id="edit_bundle_price" type="number" step="0.01" min="0" class="form-control edit-bundle-price" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">ลำดับการแสดงผล</label>
                        <input name="sort_order" id="edit_sort_order" type="number" class="form-control">
                    </div>
                    <div class="col-span-12 flex items-center mt-3">
                        <input name="is_active" id="edit_is_active" type="checkbox" class="form-check-input border mr-2" value="1">
                        <label>เปิดใช้งาน</label>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script>
    function calcOriginalTotal(selectEl) {
        let total = 0;
        $(selectEl).find('option:selected').each(function () {
            total += parseFloat($(this).data('price')) || 0;
        });
        return total;
    }

    $(document).ready(function () {
        $('.select2-products').select2({ dropdownParent: $('#add-modal'), placeholder: 'เลือกสินค้าเข้าชุด' });
        $('.select2-products-edit').select2({ dropdownParent: $('#edit-modal'), placeholder: 'เลือกสินค้าเข้าชุด' });

        $('.select2-products').on('change', function () {
            $('.add-original-total').text(calcOriginalTotal(this).toLocaleString('en-US', { minimumFractionDigits: 2 }));
        });
        $('.select2-products-edit').on('change', function () {
            $('.edit-original-total').text(calcOriginalTotal(this).toLocaleString('en-US', { minimumFractionDigits: 2 }));
        });

        // 🌟 ลบชุดสินค้า — ยืนยันก่อนลบเพราะกระทบราคาที่ลูกค้าเห็นในแอปทันที
        $('.btn-delete-form').on('submit', function (e) {
            if (!confirm('ยืนยันลบชุดสินค้านี้? ลูกค้าจะไม่เห็นราคาชุดนี้อีกต่อไป')) {
                e.preventDefault();
            }
        });

        $('.btn-edit').on('click', function () {
            let bundle = $(this).data('bundle');
            // 🌟 แก้บั๊ก: route นี้ประกาศด้วย Route::resource() ซึ่ง URI ของ PUT ไม่มี /update ต่อท้าย
            // (ต่างจาก smart-lockers ที่ใช้ Route::post('.../update') ตรงๆ) ของเดิมยิง .../update ทำให้ 404 ตอนกดบันทึกแก้ไข
            $('#edit-form').attr('action', `{{ url('/admin/product-bundles') }}/${bundle.id}`);
            $('#edit_name_th').val(bundle.name_th);
            $('#edit_name_en').val(bundle.name_en);
            $('#edit_bundle_price').val(bundle.bundle_price);
            $('#edit_sort_order').val(bundle.sort_order);
            $('#edit_is_active').prop('checked', bundle.is_active == 1);
            $('#edit_product_ids').val(bundle.product_ids.map(String)).trigger('change');
        });
    });
</script>
@endsection
