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
                        @php
                            $groupLabels = ['equipment' => 'สินค้า (อุปกรณ์)', 'package' => 'แพ็กเกจ', 'service' => 'บริการ'];
                            $presetGroup = $presetGroup ?? null;
                        @endphp
                        <select name="type" id="categorySelect" class="form-select">
                            @if(!empty($categories))
                                @foreach(collect($categories)->groupBy('group') as $g => $cats)
                                    <optgroup label="{{ $groupLabels[$g] ?? $g }}">
                                        @foreach($cats as $cat)
                                            @php
                                                $sel = isset($product) ? $product->type == $cat->id
                                                    : ($presetGroup && (($presetGroup === 'equipment') === ($cat->group === 'equipment')) && !isset($presetPicked));
                                                if ($sel && !isset($product)) $presetPicked = true;
                                            @endphp
                                            <option value="{{ $cat->id }}" data-group="{{ $cat->group }}" {{ $sel ? 'selected' : '' }}>
                                                {{ $cat->title_th }} ({{ $groupLabels[$cat->group] ?? ucfirst($cat->group) }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            @endif
                        </select>
                        <div class="text-xs text-slate-500 mt-1">หมวดกลุ่ม "สินค้า (อุปกรณ์)" จะแสดงช่องข้อมูลสินค้า — กลุ่ม "แพ็กเกจ / บริการ" จะแสดงช่องแผนบำรุงรักษา</div>
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
                            @include('admin.partials.image-hint', ['key' => 'product'])
                        </div>
                    </div>

                    <div class="mt-4 border-t border-slate-200/60 pt-4" id="equipmentFields">
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


                    {{-- 🌟 แผนบำรุงรักษา (แสดงเมื่อเลือกหมวดกลุ่มแพ็กเกจ/บริการ) — ค่าตั้งต้นของบริการตัวนี้
                         ตอนลูกค้าซื้อ / แอดมินเพิ่มให้ลูกค้า ระบบจะคัดลอกไปเป็นแผนของลูกค้ารายนั้น (ปรับรายคนได้ที่เมนูแผนบำรุงรักษา) --}}
                    <div class="mt-4 border-t border-slate-200/60 pt-4" id="maintenanceFields">
                        <label class="form-label font-medium mb-1">แผนบำรุงรักษา (ค่าตั้งต้นของบริการนี้)</label>
                        <div class="text-xs text-slate-500 mb-3">เว้น "เข้าบริการทุกกี่เดือน" ว่างไว้ ถ้าบริการนี้ไม่มีการเข้าดูแลตามรอบ</div>
                        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(190px, 1fr)); gap:14px;">
                            <div>
                                <label class="form-label">ระยะสัญญา (เดือน)</label>
                                <input name="contract_months" type="number" min="1" max="120" class="form-control" value="{{ old('contract_months', $product->contract_months ?? '') }}" placeholder="เช่น 12">
                            </div>
                            <div>
                                <label class="form-label">จำนวนครั้งเข้าบริการ / สัญญา</label>
                                <input name="service_visits" type="number" min="0" class="form-control" value="{{ old('service_visits', $product->service_visits ?? '') }}" placeholder="เช่น 4 (0 = ไม่จำกัด)">
                            </div>
                            <div>
                                <label class="form-label">เข้าบริการทุกกี่เดือน</label>
                                <input name="visit_interval_months" type="number" min="1" max="60" class="form-control" value="{{ old('visit_interval_months', $product->visit_interval_months ?? '') }}" placeholder="1 = รายเดือน, 3 = รายไตรมาส">
                            </div>
                            <div>
                                <label class="form-label">เปิดใบงานล่วงหน้า (วัน)</label>
                                <input name="ticket_lead_days" type="number" min="0" max="90" class="form-control" value="{{ old('ticket_lead_days', $product->ticket_lead_days ?? 10) }}">
                            </div>
                        </div>
                        <div class="flex items-center mt-3">
                            <input type="hidden" name="auto_schedule" value="0">
                            <input name="auto_schedule" id="auto_schedule" type="checkbox" value="1" class="form-check-input border mr-2" {{ old('auto_schedule', $product->auto_schedule ?? false) ? 'checked' : '' }}>
                            <label for="auto_schedule">เปิดใบงานบำรุงรักษาตามรอบให้อัตโนมัติ (แอดมินมอบหมายช่างต่อ)</label>
                        </div>
                        <div id="planPreview" class="text-xs text-primary mt-2"></div>
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

@section('script')
<script>
    // 🌟 แสดงช่องตามกลุ่มของหมวดหมู่: สินค้า (อุปกรณ์) ↔ แพ็กเกจ/บริการ
    (function () {
        const sel = document.getElementById('categorySelect');
        const eq = document.getElementById('equipmentFields');
        const mt = document.getElementById('maintenanceFields');
        const preview = document.getElementById('planPreview');
        function groupOf() { const o = sel && sel.options[sel.selectedIndex]; return o ? o.dataset.group : 'equipment'; }
        function toggle() {
            const isEquipment = groupOf() === 'equipment';
            if (eq) eq.style.display = isEquipment ? '' : 'none';
            if (mt) mt.style.display = isEquipment ? 'none' : '';
            updatePreview();
        }
        function val(n) { const el = document.querySelector('[name="' + n + '"]'); return el && el.value ? parseInt(el.value, 10) : null; }
        function updatePreview() {
            if (!preview) return;
            const months = val('contract_months'), visits = val('service_visits'), every = val('visit_interval_months');
            if (!every) { preview.textContent = 'บริการนี้ไม่มีการเข้าดูแลตามรอบ'; return; }
            let t = 'เข้าดูแลทุก ' + every + ' เดือน';
            if (months) t += ' ตลอดสัญญา ' + months + ' เดือน (≈ ' + Math.floor(months / every) + ' รอบ)';
            if (visits) t += ' · โควต้า ' + visits + ' ครั้ง';
            if (months && visits && Math.floor(months / every) < visits) t += ' ⚠️ จำนวนรอบในสัญญาน้อยกว่าโควต้า';
            preview.textContent = t;
        }
        if (sel) sel.addEventListener('change', toggle);
        ['contract_months', 'service_visits', 'visit_interval_months'].forEach(n => {
            const el = document.querySelector('[name="' + n + '"]'); if (el) el.addEventListener('input', updatePreview);
        });
        toggle();
    })();
</script>
@endsection
