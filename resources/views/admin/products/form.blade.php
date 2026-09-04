@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ isset($product) ? 'แก้ไขสินค้า' : 'เพิ่มสินค้าใหม่' }} - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ isset($product) ? 'แก้ไขข้อมูลสินค้า/บริการ' : 'เพิ่มสินค้า/บริการใหม่' }}</h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-8">
            <form action="{{ isset($product) ? route('admin.products.update', $product->id) : route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($product)) @method('PUT') @endif

                <div class="intro-y box p-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-medium">ชื่อสินค้า/บริการ (ภาษาไทย) <span class="text-danger">*</span></label>
                            <input name="name_th" type="text" class="form-control" value="{{ $product->name_th ?? '' }}" required>
                        </div>
                        <div>
                            <label class="form-label font-medium">ชื่อสินค้า/บริการ (English)</label>
                            <input name="name_en" type="text" class="form-control" value="{{ $product->name_en ?? '' }}">
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="form-label font-medium">หมวดสินค้า (Category) <span class="text-danger">*</span></label>
                        <select name="type" class="form-select">
                            @if(!empty($categories))
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ (isset($product) && $product->type == $cat->id) ? 'selected' : '' }}>
                                        {{ $cat->title_th }} ({{ ucfirst($cat->group) }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="col-span-12 mt-3 flex items-center">
                        <input name="is_contact_only" type="checkbox" class="form-check-input border mr-2" value="1" {{ (isset($product) && $product->is_contact_only) ? 'checked' : '' }}>
                        <label class="font-medium text-danger">ตั้งค่าเป็นบริการที่ต้องติดต่อฝ่ายขายเท่านั้น (ไม่สามารถกดซื้อผ่านแอปได้)</label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="form-label font-medium">ราคาขายจริง (Price) <span class="text-danger">*</span></label>
                            <input name="price" type="number" step="0.01" class="form-control" value="{{ $product->price ?? '' }}" required placeholder="0.00">
                        </div>
                        <div>
                            <label class="form-label font-medium">ราคาเต็ม (Compare at price)</label>
                            <input name="compare_at_price" type="number" step="0.01" class="form-control" value="{{ $product->compare_at_price ?? '' }}" placeholder="สำหรับโชว์ป้ายลดราคา (เว้นว่างได้)">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="form-label font-medium">แต้มที่จะได้รับ (Point Earn)</label>
                            <input name="point_earn" type="number" class="form-control" value="{{ $product->point_earn ?? 0 }}">
                        </div>
                        <div>
                            <label class="form-label font-medium">รูปภาพสินค้า (สามารถเลือกได้มากกว่า 1 รูป)</label>
                            <input name="images[]" type="file" class="form-control" accept="image/*" multiple>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-slate-200/60 pt-4">
                        <label class="form-label font-medium text-slate-500 mb-2">ข้อมูลเพิ่มเติมสำหรับหน้ารายละเอียดสินค้า (ช่วยลูกค้าตัดสินใจซื้อ)</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">ยี่ห้อ (Brand)</label>
                                <input name="brand" type="text" class="form-control" value="{{ $product->brand ?? '' }}">
                            </div>
                            <div>
                                <label class="form-label">รุ่น (Model)</label>
                                <input name="model" type="text" class="form-control" value="{{ $product->model ?? '' }}">
                            </div>
                            <div>
                                <label class="form-label">จำนวนคงเหลือ (เว้นว่าง = ไม่จำกัด/ไม่นับสต็อก)</label>
                                <input name="stock_quantity" type="number" min="0" class="form-control" value="{{ $product->stock_quantity ?? '' }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div>
                                <label class="form-label">ระยะเวลารับประกัน (เดือน)</label>
                                <input name="warranty_months" type="number" min="0" class="form-control" value="{{ $product->warranty_months ?? '' }}">
                            </div>
                            <div>
                                <label class="form-label">ค่าจัดส่ง (บาท)</label>
                                <input name="shipping_fee" type="number" step="0.01" min="0" class="form-control" value="{{ $product->shipping_fee ?? '' }}">
                            </div>
                            <div>
                                <label class="form-label">ค่าติดตั้ง (บาท)</label>
                                <input name="install_fee" type="number" step="0.01" min="0" class="form-control" value="{{ $product->install_fee ?? '' }}">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="form-label">เงื่อนไขการคืนหรือเปลี่ยนสินค้า</label>
                                <textarea name="return_policy_th" class="form-control" rows="3">{{ $product->return_policy_th ?? '' }}</textarea>
                            </div>
                            <div>
                                <label class="form-label">อุปกรณ์ที่ใช้งานร่วมกันได้</label>
                                <textarea name="compatible_with" class="form-control" rows="3" placeholder="เช่น ใช้ร่วมกับกล่องควบคุมรุ่น SP55/SP68">{{ $product->compatible_with ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>

                    @if(isset($product) && isset($productImages) && count($productImages) > 0)
                    <div class="mt-4 border-t border-slate-200/60 pt-4">
                        <label class="form-label font-medium text-slate-500 mb-2">รูปภาพปัจจุบันในระบบ</label>
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                            @foreach($productImages as $img)
                            <div class="relative border rounded p-1 bg-slate-50">
                                <div class="w-full h-20 image-fit">
                                    <img alt="Product Gallery" class="rounded" src="{{ $img->image_url }}">
                                </div>
                                <div class="text-center text-xs text-slate-400 mt-1">รูปที่ {{ $img->sort_order + 1 }}</div>
                            </div>
                            @endforeach
                        </div>
                        <div class="text-slate-400 text-xs mt-2">* หมายเหตุ: หากเลือกอัปโหลดรูปภาพใหม่ ระบบจะทำการล้างรูปภาพชุดเดิมทั้งหมดออกอัตโนมัติ</div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="form-label font-medium">รายละเอียดเพิ่มเติม (ภาษาไทย)</label>
                            <textarea name="description_th" class="form-control" rows="4">{{ $product->description_th ?? '' }}</textarea>
                        </div>
                        <div>
                            <label class="form-label font-medium">รายละเอียดเพิ่มเติม (English)</label>
                            <textarea name="description_en" class="form-control" rows="4">{{ $product->description_en ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="form-check mt-5">
                        <input name="is_active" class="form-check-input" type="checkbox" id="active-checkbox" {{ (!isset($product) || $product->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="active-checkbox">เปิดขายสินค้า/บริการนี้</label>
                    </div>

                    <div class="text-right mt-6 border-t border-slate-200/60 pt-5">
                        <a href="{{ route('admin.products') }}" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
