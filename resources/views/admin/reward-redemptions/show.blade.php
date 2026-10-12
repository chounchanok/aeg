@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $code->code }} - คูปอง/ของรางวัล - AEG Admin</title>
    <style>
        .rr-timeline { position: relative; padding-left: 4px; }
        .rr-timeline-item { position: relative; padding: 0 0 18px 26px; }
        .rr-timeline-item:last-child { padding-bottom: 0; }
        /* เส้นแนวตั้งเชื่อมระหว่างจุด */
        .rr-timeline-item::before { content: ""; position: absolute; left: 5px; top: 16px; bottom: -2px; width: 2px; background: #e2e8f0; }
        .rr-timeline-item:last-child::before { display: none; }
        .rr-timeline-dot, .rr-legend-dot { display: inline-block; width: 12px; height: 12px; border-radius: 9999px; }
        .rr-timeline-dot { position: absolute; left: 0; top: 4px; box-shadow: 0 0 0 3px #fff; }
        .rr-legend-dot { vertical-align: -1px; margin-right: 4px; }
        .is-customer { background: #f59e0b; }
        .is-admin { background: #1e40af; }
        /* ฟอร์มหลายคอลัมน์ (คลาส sm:col-span-* ไม่มีใน CSS ที่ build ไว้) */
        .rr-form-row { display: grid; gap: 12px; grid-template-columns: 1fr; }
        .rr-span-all { grid-column: 1 / -1; }
        @media (min-width: 768px) {
            .rr-form-row { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .rr-form-row-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
@endsection

@php
    $statusLabels = \App\Services\RewardService::STATUS_LABELS;
    $customerName = trim(($code->first_name ?? '') . ' ' . ($code->last_name ?? '')) ?: $code->username;
    $productSteps = ['active', 'shipping_confirm', 'processing', 'shipping', 'delivered'];
    $currentStep = array_search(($code->status === 'active' && \App\Services\RewardService::hasShippingInfo($code)) ? 'shipping_confirm' : $code->status, $productSteps, true);
@endphp

@section('subcontent')
    <div class="intro-y flex flex-wrap items-center mt-10 gap-2">
        <div class="mr-auto">
            <h2 class="text-lg font-medium">{{ $code->reward_title }}</h2>
            <div class="text-slate-500 text-sm mt-1">
                {{ \App\Services\RewardService::TYPE_LABELS[$type] ?? $type }} · โค้ดอ้างอิง <span class="font-mono">{{ $code->code }}</span>
                · แลกเมื่อ {{ \Carbon\Carbon::parse($code->created_at)->format('d/m/Y H:i') }}
            </div>
        </div>
        <a href="{{ route('admin.reward-redemptions.index') }}" class="btn btn-outline-secondary">กลับรายการ</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success show mt-5">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger show mt-5">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger show mt-5">{{ $errors->first() }}</div>
    @endif

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 lg:col-span-7 space-y-5">
            <div class="box p-5">
                <div class="flex items-center">
                    <div class="w-16 h-16 image-fit mr-4 flex-none">
                        <img class="rounded-md border" src="{{ $code->image_url ?? asset('dist/images/preview-1.jpg') }}" alt="">
                    </div>
                    <div class="flex-1">
                        <div class="text-slate-500 text-xs">ลูกค้า</div>
                        <a href="{{ route('admin.customers.show', $code->user_id) }}" class="font-medium text-primary">{{ $customerName }}</a>
                        <div class="text-slate-500 text-xs">{{ $code->phone ?? '-' }} · {{ $code->email ?? '' }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-slate-500 text-xs">สถานะปัจจุบัน</div>
                        <div class="font-medium text-base">{{ $statusLabels[$code->status] ?? $code->status }}</div>
                    </div>
                </div>

                @if($type === 'product')
                    <div class="flex mt-6 text-xs">
                        @foreach($productSteps as $i => $step)
                            <div class="flex-1 text-center">
                                <div class="mx-auto w-7 h-7 rounded-full flex items-center justify-center {{ $currentStep !== false && $i <= $currentStep ? 'bg-primary text-white' : 'bg-slate-200 text-slate-500' }}">{{ $i + 1 }}</div>
                                <div class="mt-1 {{ $currentStep === $i ? 'font-medium' : 'text-slate-500' }}">{{ $statusLabels[$step] }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($type === 'product')
                {{-- ===== สินค้า: ตรวจที่อยู่ → ยืนยันการจัดส่ง → ดำเนินการ → จัดส่ง → สำเร็จ ===== --}}
                <div class="box p-5">
                    <h3 class="font-medium mb-3">ที่อยู่จัดส่งที่ลูกค้าแจ้ง</h3>
                    <div class="bg-slate-50 rounded-md p-4 text-sm leading-relaxed">
                        <div><span class="text-slate-500">ผู้รับ:</span> <span class="font-medium">{{ $code->customer_name ?? ($address->contact_name ?? '-') }}</span></div>
                        <div><span class="text-slate-500">เบอร์โทร:</span> {{ $code->customer_phone ?? ($address->contact_phone ?? '-') }}</div>
                        <div class="mt-2"><span class="text-slate-500">ที่อยู่:</span>
                            @if($address)
                                {{ $address->address_line }} ต.{{ $address->subdistrict }} อ.{{ $address->district }} จ.{{ $address->province }} {{ $address->zipcode }}
                                @if(!empty($address->deleted_at)) <span class="text-xs text-warning">(ลูกค้าลบที่อยู่นี้ออกจากสมุดที่อยู่แล้ว)</span> @endif
                            @else
                                {{ $code->address_text ?: '— ลูกค้ายังไม่ได้ระบุที่อยู่ —' }}
                            @endif
                        </div>
                        @if($code->requested_at)
                            <div class="text-xs text-slate-500 mt-2">ลูกค้ายืนยันที่อยู่จัดส่งเมื่อ {{ \Carbon\Carbon::parse($code->requested_at)->format('d/m/Y H:i') }}</div>
                        @endif
                        @if($code->tracking_number)
                            <div class="mt-2"><span class="text-slate-500">ขนส่ง/Tracking:</span> {{ $code->shipping_carrier }} {{ $code->tracking_number }}</div>
                        @endif
                    </div>

                    @if(in_array($code->status, ['active', 'shipping_confirm', 'processing'], true) && auth()->user()->can('customers.manage'))
                        <details class="mt-3">
                            <summary class="text-primary text-sm cursor-pointer">แก้ไขที่อยู่จัดส่ง (กรณีลูกค้าแจ้งเปลี่ยน)</summary>
                            <form method="POST" action="{{ route('admin.reward-redemptions.address', $code->id) }}" class="rr-form-row rr-form-row-2 mt-3">
                                @csrf
                                <input type="text" name="customer_name" class="form-control w-full" placeholder="ชื่อผู้รับ" value="{{ $code->customer_name ?? ($address->contact_name ?? '') }}" required>
                                <input type="text" name="customer_phone" class="form-control w-full" placeholder="เบอร์โทร" value="{{ $code->customer_phone ?? ($address->contact_phone ?? '') }}" required>
                                <textarea name="address_text" class="form-control w-full rr-span-all" rows="2" placeholder="ที่อยู่เต็ม" required>{{ $code->address_text ?? ($address ? $address->address_line . ' ต.' . $address->subdistrict . ' อ.' . $address->district . ' จ.' . $address->province . ' ' . $address->zipcode : '') }}</textarea>
                                <div class="rr-span-all text-right"><button class="btn btn-outline-primary btn-sm">บันทึกที่อยู่</button></div>
                            </form>
                        </details>
                    @endif

                    <div class="border-t mt-5 pt-5">
                        @if(empty($nextStatuses) && $code->status === 'active')
                            <div class="alert alert-secondary show">ลูกค้ายังไม่ได้ระบุที่อยู่จัดส่ง — เมื่อลูกค้าส่งที่อยู่ในแอป สถานะจะเป็น "ยืนยันการจัดส่ง" และจัดส่งได้ทันที (หรือกรอกที่อยู่ให้ลูกค้าที่ "แก้ไขที่อยู่จัดส่ง" ด้านบน)</div>
                        @elseif(!empty($nextStatuses) && !auth()->user()->can('customers.manage'))
                            <div class="text-sm text-slate-500">ต้องมีสิทธิ์ customers.manage จึงจะเปลี่ยนสถานะได้</div>
                        @elseif(!empty($nextStatuses))
                            {{-- 🌟 ลูกค้าระบุที่อยู่แล้ว = ยืนยันการจัดส่ง → แอดมินเลือกสถานะถัดไปได้เลย (ข้ามขั้นได้) --}}
                            <form method="POST" action="{{ route('admin.reward-redemptions.status', $code->id) }}">
                                @csrf
                                <div class="rr-form-row">
                                    <div>
                                        <label class="form-label">เปลี่ยนสถานะเป็น</label>
                                        <select name="status" id="rr-next-status" class="form-select w-full" required>
                                            @foreach($nextStatuses as $st)
                                                <option value="{{ $st }}">{{ $statusLabels[$st] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label">บริษัทขนส่ง</label>
                                        <input type="text" name="shipping_carrier" class="form-control w-full" placeholder="เช่น Kerry, Flash" value="{{ $code->shipping_carrier }}">
                                    </div>
                                    <div>
                                        <label class="form-label">เลขพัสดุ (Tracking) <span class="text-xs text-slate-500">— บังคับเมื่อเลือก "กำลังจัดส่ง"</span></label>
                                        <input type="text" name="tracking_number" class="form-control w-full" placeholder="เลขพัสดุ" value="{{ $code->tracking_number }}">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label class="form-label">หมายเหตุ (ไม่บังคับ)</label>
                                    <input type="text" name="note" class="form-control w-full" placeholder="เช่น แพ็กของแล้ว รอขนส่งมารับ">
                                </div>
                                <div class="text-right mt-4">
                                    <button class="btn btn-primary" onclick="return confirm('ยืนยันเปลี่ยนสถานะเป็น &quot;' + document.getElementById('rr-next-status').selectedOptions[0].text + '&quot;? ลูกค้าจะได้รับแจ้งเตือน');">
                                        <i data-lucide="truck" class="w-4 h-4 mr-1"></i> บันทึกสถานะ
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="text-sm text-slate-500">รายการนี้ดำเนินการเสร็จสิ้นแล้ว</div>
                        @endif
                    </div>
                </div>
            @else
                {{-- ===== วอยเชอร์ / ส่วนลด: กรอกรหัสส่งกลับไปแสดงในแอป ===== --}}
                <div class="box p-5">
                    <h3 class="font-medium mb-1">ส่งคูปองให้ลูกค้า</h3>
                    <p class="text-xs text-slate-500 mb-4">
                        รหัสที่กรอกจะแสดงในแอปของลูกค้าทันทีและมีแจ้งเตือนไปหาลูกค้า — สถานะยังเป็น "ยังไม่ได้ใช้" จนกว่าลูกค้าจะกดใช้งานเอง
                        @if($type === 'discount')
                            <br>ส่วนลดในแอป: ถ้าไม่กรอก ลูกค้าใช้โค้ด <span class="font-mono">{{ $code->code }}</span> ตอนชำระเงินได้อยู่แล้ว (มูลค่า ฿{{ number_format($code->discount_amount, 2) }})
                        @endif
                    </p>
                    @if($code->status === 'active' && !auth()->user()->can('customers.manage'))
                        <div class="text-sm text-slate-500">รหัสปัจจุบัน: <span class="font-mono">{{ $code->voucher_code ?: '-' }}</span> · ต้องมีสิทธิ์ customers.manage จึงจะส่งรหัสได้</div>
                    @elseif($code->status === 'active')
                        <form method="POST" action="{{ route('admin.reward-redemptions.send-code', $code->id) }}">
                            @csrf
                            <label class="form-label">รหัสคูปอง / วอยเชอร์ <span class="text-danger">*</span></label>
                            <input type="text" name="voucher_code" class="form-control font-mono" value="{{ old('voucher_code', $code->voucher_code) }}" required maxlength="255" placeholder="เช่น STARBUCKS-ABC123">
                            <label class="form-label mt-3">วิธีใช้ / หมายเหตุที่จะแสดงบนแอป</label>
                            <textarea name="voucher_note" class="form-control" rows="3" placeholder="เช่น ใช้ได้ถึง 31/12/2026 แสดงรหัสที่หน้าร้าน">{{ old('voucher_note', $code->voucher_note) }}</textarea>
                            <div class="text-right mt-4">
                                <button class="btn btn-primary"><i data-lucide="send" class="w-4 h-4 mr-1"></i> {{ $code->voucher_code ? 'แก้ไขและส่งรหัสอีกครั้ง' : 'ส่งคูปอง' }}</button>
                            </div>
                        </form>
                    @else
                        <div class="bg-slate-50 rounded-md p-4 text-sm">
                            รหัส: <span class="font-mono font-medium">{{ $code->voucher_code ?: $code->code }}</span>
                            · ลูกค้าใช้แล้วเมื่อ {{ $code->used_at ? \Carbon\Carbon::parse($code->used_at)->format('d/m/Y H:i') : '-' }}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="col-span-12 lg:col-span-5">
            <div class="box p-5">
                <h3 class="font-medium mb-4">ประวัติสถานะ</h3>
                {{-- 🌟 timeline: ใช้ CSS ของหน้านี้เอง (คลาส Tailwind อย่าง -left-1.5 / pl-5 ไม่มีใน CSS ที่ build ไว้ ทำให้จุดทับข้อความ) --}}
                <div class="rr-timeline">
                    @forelse($logs as $log)
                        <div class="rr-timeline-item">
                            <span class="rr-timeline-dot {{ $log->actor === 'customer' ? 'is-customer' : 'is-admin' }}"></span>
                            <div class="text-sm font-medium">{{ $statusLabels[$log->status] ?? $log->status }}</div>
                            @if($log->note)<div class="text-xs text-slate-600 mt-0.5">{{ $log->note }}</div>@endif
                            <div class="text-xs text-slate-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') }}
                                · {{ $log->actor === 'customer' ? 'ลูกค้า' : ($log->actor === 'system' ? 'ระบบ' : ($log->changed_by_name ?? 'แอดมิน')) }}
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-slate-500">ยังไม่มีประวัติ (รายการที่แลกก่อนเปิดใช้ระบบนี้)</div>
                    @endforelse
                </div>
                @if($logs->isNotEmpty())
                    <div class="text-xs text-slate-500 mt-4">
                        <span class="rr-legend-dot is-customer"></span> ลูกค้า
                        <span class="rr-legend-dot is-admin" style="margin-left:12px"></span> แอดมิน/ระบบ
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection