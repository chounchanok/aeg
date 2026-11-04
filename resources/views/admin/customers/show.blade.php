@extends('../layout/' . $layout)

@section('subhead')
    <title>รายละเอียดลูกค้า - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">รายละเอียดลูกค้า</h2>
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

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-4">
            <div class="box p-5 text-center">
                <div class="w-24 h-24 image-fit mx-auto zoom-in">
                    <img class="rounded-full shadow-md" src="{{ $customer->profile_image_url ?? asset('dist/images/profile-1.jpg') }}">
                </div>
                <div class="mt-4 font-medium text-lg">{{ $customer->first_name ?? $customer->username }}</div>
                <div class="text-slate-500 mt-1">{{ $customer->email }}</div>
                <div class="mt-5 border-t border-slate-200/60 pt-5 text-left">
                    <div class="mb-2"><i data-lucide="phone" class="w-4 h-4 inline mr-2 text-slate-500"></i> {{ $customer->phone ?? 'ไม่ระบุ' }}</div>
                    <div class="mb-2"><i data-lucide="map-pin" class="w-4 h-4 inline mr-2 text-slate-500"></i> {{ $customer->address ?? 'ยังไม่มีข้อมูลที่อยู่' }}</div>
                    <div class="mb-2"><i data-lucide="receipt" class="w-4 h-4 inline mr-2 text-slate-500"></i> เลขผู้เสียภาษี: {{ $customer->tax_id ?? 'ไม่ระบุ' }}</div>
                    <div class="mb-2"><i data-lucide="building-2" class="w-4 h-4 inline mr-2 text-slate-500"></i> สาขา: {{ $customer->branch ?? 'ไม่ระบุ' }}</div>
                </div>
                <div class="mt-5 border-t border-slate-200/60 pt-5 text-left">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-slate-500 text-xs">แต้มสะสมปัจจุบัน</span>
                        @if($wallet && $wallet->tier_name)
                            <span class="bg-slate-100 text-slate-500 text-xs px-2 py-1 rounded">{{ $wallet->tier_name }}</span>
                        @endif
                    </div>
                    <div class="text-2xl font-medium text-primary">{{ number_format($wallet->current_points ?? 0) }} <span class="text-sm text-slate-500 font-normal">แต้ม</span></div>
                </div>
            </div>

            {{-- 🌟 ระดับสมาชิก (Advance / Platinum / Beyond) + เพิ่ม/หักแต้มรายคน --}}
            <div class="box p-5 mt-5">
                <div class="flex items-center border-b border-slate-200/60 pb-3 mb-3">
                    <div class="font-medium text-base mr-auto">ระดับสมาชิก & แต้ม</div>
                    @can('customers.manage')
                        <a href="{{ route('admin.customers.tier-settings') }}" class="text-xs text-primary">ตั้งค่าเกณฑ์</a>
                    @endcan
                </div>

                @if($tierStatus)
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-medium text-primary">{{ $tierStatus['tier_name'] ?? '-' }}</span>
                        <span class="text-xs text-slate-500">
                            ใช้ได้ถึง {{ $tierStatus['tier_expires_at'] ? \Carbon\Carbon::parse($tierStatus['tier_expires_at'])->format('d/m/Y') : '-' }}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mt-3 text-sm">
                        <div class="bg-slate-50 rounded p-2">
                            <div class="text-slate-500 text-xs">แต้มสะสม 12 เดือน</div>
                            <div class="font-medium">{{ number_format($tierStatus['points_12m']) }}</div>
                        </div>
                        <div class="bg-slate-50 rounded p-2">
                            <div class="text-slate-500 text-xs">ยอดซื้อ 12 เดือน</div>
                            <div class="font-medium">฿{{ number_format($tierStatus['spending_12m'], 2) }}</div>
                        </div>
                    </div>
                    @if($tierStatus['next_tier'])
                        <div class="text-xs text-slate-500 mt-2">
                            ขึ้น {{ $tierStatus['next_tier']['tier_name'] }}:
                            @if($tierStatus['next_tier']['points_needed'] !== null) อีก {{ number_format($tierStatus['next_tier']['points_needed']) }} แต้ม @endif
                            @if($tierStatus['next_tier']['spending_needed'] !== null) หรือซื้อเพิ่มอีก ฿{{ number_format($tierStatus['next_tier']['spending_needed'], 2) }} @endif
                        </div>
                    @else
                        <div class="text-xs text-success mt-2">อยู่ระดับสูงสุดแล้ว</div>
                    @endif
                @else
                    <div class="text-slate-500 text-sm">ยังไม่มีกระเป๋าแต้ม (จะสร้างให้อัตโนมัติเมื่อเพิ่มแต้มครั้งแรก)</div>
                @endif

                @can('customers.manage')
                    <form action="{{ route('admin.customers.points.adjust', $customer->id) }}" method="POST" class="mt-4 border-t border-slate-200/60 pt-4"
                          onsubmit="return confirm('ยืนยันการ' + (this.direction.value === 'add' ? 'เพิ่ม' : 'หัก') + ' ' + this.points.value + ' แต้ม ให้ลูกค้ารายนี้?');">
                        @csrf
                        <div class="font-medium text-sm mb-2">เพิ่ม / หักแต้ม</div>
                        <div class="grid grid-cols-12 gap-2">
                            <select name="direction" class="form-select col-span-5">
                                <option value="add">+ เพิ่มแต้ม</option>
                                <option value="deduct">− หักแต้ม</option>
                            </select>
                            <input type="number" name="points" min="1" step="1" class="form-control col-span-7" placeholder="จำนวนแต้ม" required>
                            <input type="text" name="note" maxlength="200" class="form-control col-span-12" placeholder="เหตุผล เช่น ชดเชยงานล่าช้า / ปรับยอดผิดพลาด" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-full mt-2">บันทึกแต้ม</button>
                        <div class="text-xs text-slate-500 mt-1">ระบบจะประเมินระดับสมาชิกใหม่ให้อัตโนมัติหลังบันทึก</div>
                    </form>
                    <form action="{{ route('admin.customers.tier.evaluate', $customer->id) }}" method="POST" class="mt-2">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-full btn-sm">ประเมินระดับสมาชิกใหม่ตอนนี้</button>
                    </form>
                @endcan

                @if($tierHistory->isNotEmpty())
                    <div class="mt-4 border-t border-slate-200/60 pt-3">
                        <div class="font-medium text-sm mb-2">ประวัติระดับสมาชิก</div>
                        @foreach($tierHistory as $h)
                            <div class="text-xs mb-2">
                                <span class="text-slate-500">{{ \Carbon\Carbon::parse($h->created_at)->format('d/m/Y H:i') }}</span>
                                · {{ \App\Services\LoyaltyTierService::reasonLabel($h->reason) }}
                                <div>
                                    @if($h->from_name && $h->from_name !== $h->to_name){{ $h->from_name }} → @endif<b>{{ $h->to_name }}</b>
                                    <span class="text-slate-500">(แต้ม {{ number_format($h->points_12m) }} / ยอดซื้อ ฿{{ number_format($h->spending_12m) }}@if($h->admin_name) · โดย {{ $h->admin_name }}@endif)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @can('invoices.manage')
            {{-- 🌟 ตั้งค่าลูกค้า "วางบิลรายเดือน" — คำสั่งซื้อที่ยังไม่ชำระของลูกค้าคนนี้จะถูกรวมเป็นใบแจ้งหนี้เดียวตอนสิ้นเดือน
                 แทนที่จะให้จ่ายทีละออเดอร์ (ดูรายละเอียดที่เมนู "ใบแจ้งหนี้รายเดือน") --}}
            <div class="box p-5 mt-5">
                <div class="font-medium text-base border-b border-slate-200/60 pb-3 mb-3">ตั้งค่าใบแจ้งหนี้รายเดือน</div>
                <form action="{{ route('admin.customers.invoice-settings', $customer->id) }}" method="POST">
                    @csrf
                    <div class="flex items-center mb-3">
                        <input type="hidden" name="is_invoice_customer" value="0">
                        <input type="checkbox" name="is_invoice_customer" id="is_invoice_customer" class="form-check-input border mr-2" value="1" {{ ($customer->is_invoice_customer ?? false) ? 'checked' : '' }}>
                        <label for="is_invoice_customer">ลูกค้าวางบิลรายเดือน (สรุปยอดออเดอร์ค้างจ่ายเป็นใบแจ้งหนี้เดียวทุกสิ้นเดือน)</label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ช่องทางจ่ายเงินของใบแจ้งหนี้สรุปยอด</label>
                        <select name="invoice_payment_method" class="form-select">
                            <option value="bank_transfer" {{ ($customer->invoice_payment_method ?? 'bank_transfer') === 'bank_transfer' ? 'selected' : '' }}>โอนเงิน (ตรวจสลิปเอง)</option>
                            <option value="gateway" {{ ($customer->invoice_payment_method ?? '') === 'gateway' ? 'selected' : '' }}>ชำระออนไลน์ (บัตร/พร้อมเพย์)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">บันทึก</button>
                </form>
            </div>
            @endcan
        </div>

        <div class="intro-y col-span-12 lg:col-span-8">
            <div class="box p-5">
                <div class="flex items-center border-b border-slate-200/60 pb-5">
                    <div class="font-medium text-base mr-auto">สินค้าและบริการที่ลูกค้าครอบครอง</div>
                    {{-- 🌟 หน้าลูกค้าเป็น Read-only สำหรับทุกแผนก — ปุ่มเพิ่มรายการแสดงเฉพาะคนที่มี customers.manage (IT) --}}
                    @can('customers.manage')
                    <div class="dropdown">
                        <button class="dropdown-toggle btn btn-primary btn-sm flex items-center" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มรายการ <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                        </button>
                        <div class="dropdown-menu w-48">
                            <ul class="dropdown-content">
                                <li>
                                    <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#add-product-modal" class="dropdown-item">
                                        <i data-lucide="package" class="w-4 h-4 mr-2"></i> สินค้า / แพ็กเกจ
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#add-insurance-modal" class="dropdown-item">
                                        <i data-lucide="shield" class="w-4 h-4 mr-2"></i> ประกันภัย
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" data-tw-toggle="modal" data-tw-target="#add-locker-modal" class="dropdown-item">
                                        <i data-lucide="lock" class="w-4 h-4 mr-2"></i> ตู้เซฟนิรภัย (Lockers)
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endcan
                </div>

                <div class="mt-5">
                    @forelse($customerProducts as $cp)
                        <div class="intro-x flex items-center p-3 border-b border-slate-200/60 last:border-0">
                            <div class="mr-auto">
                                <div class="font-medium text-lg">{{ $cp->product_name }}</div>
                                <div class="text-slate-500 mt-1">S/N: <span class="font-medium text-slate-700">{{ $cp->serial_number ?? '-' }}</span></div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-slate-500">วันที่ซื้อ: {{ $cp->purchase_date ? \Carbon\Carbon::parse($cp->purchase_date)->format('d/m/Y') : '-' }}</div>
                                <div class="text-xs {{ $cp->warranty_expire_date && \Carbon\Carbon::parse($cp->warranty_expire_date)->isPast() ? 'text-danger font-medium' : 'text-success' }} mt-1">
                                    หมดประกัน: {{ $cp->warranty_expire_date ? \Carbon\Carbon::parse($cp->warranty_expire_date)->format('d/m/Y') : '-' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-500 py-10">ลูกค้าคนนี้ยังไม่มีประวัติการซื้อสินค้า/บริการ</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <!-- ===================== ประวัติแต้มสะสม ===================== -->
        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="box p-5">
                <div class="font-medium text-base border-b border-slate-200/60 pb-5 mb-3">ประวัติการได้รับ/ใช้แต้ม</div>
                <div class="overflow-x-auto" style="max-height: 420px; overflow-y: auto;">
                    <table class="table table-report -mt-2 w-full">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">วันที่</th>
                                <th class="whitespace-nowrap">รายละเอียด</th>
                                <th class="text-center whitespace-nowrap">ประเภท</th>
                                <th class="text-right whitespace-nowrap">จำนวนแต้ม</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pointHistory as $tx)
                                <tr>
                                    <td class="whitespace-nowrap text-xs">{{ \Carbon\Carbon::parse($tx->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="text-xs">{{ $tx->description ?? '-' }}</td>
                                    <td class="text-center text-xs">
                                        @if($tx->type === 'earn') <span class="text-success">ได้รับแต้ม</span>
                                        @elseif($tx->type === 'redeem') <span class="text-danger">ใช้แต้ม</span>
                                        @else <span class="text-warning">ปรับ/นำเข้า</span>
                                        @endif
                                    </td>
                                    <td class="text-right font-medium {{ $tx->amount < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $tx->amount > 0 ? '+' : '' }}{{ number_format($tx->amount) }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-slate-500 py-5">ยังไม่มีประวัติแต้ม</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================== ของรางวัล/คูปองที่แลกไปแล้ว ===================== -->
        <div class="intro-y col-span-12 lg:col-span-6">
            <div class="box p-5">
                <div class="font-medium text-base border-b border-slate-200/60 pb-5 mb-3">ของรางวัล/คูปองที่แลกไปแล้ว</div>
                <div style="max-height: 420px; overflow-y: auto;">
                    @forelse($redeemedRewards as $rw)
                        <div class="flex items-start p-3 border-b border-slate-200/60 last:border-0">
                            <div class="mr-auto">
                                <div class="font-medium">{{ $rw->reward_title }}</div>
                                <div class="text-slate-500 text-xs mt-0.5">โค้ด: <span class="font-medium text-slate-700">{{ $rw->code }}</span></div>
                                <div class="text-slate-500 text-xs mt-0.5">แลกเมื่อ: {{ \Carbon\Carbon::parse($rw->redeemed_at)->format('d/m/Y H:i') }}</div>
                                @if($rw->status === 'used' && $rw->used_at)
                                    <div class="text-slate-500 text-xs mt-0.5">ใช้เมื่อ: {{ \Carbon\Carbon::parse($rw->used_at)->format('d/m/Y H:i') }}</div>
                                @endif
                            </div>
                            <div class="text-right flex-shrink-0">
                                {{-- 🌟 เปลี่ยนจาก "ใช้คูปองแทนลูกค้า" เป็น ส่งคูปอง/ส่งสินค้า → ไปจัดการที่เมนู "คูปอง/ของรางวัลที่ลูกค้าแลก" --}}
                                <span class="{{ in_array($rw->status, ['used', 'delivered', 'cancelled']) ? 'bg-slate-200 text-slate-500' : 'bg-success text-white' }} text-xs px-2 py-1 rounded d-inline-block mb-2">
                                    {{ \App\Services\RewardService::STATUS_LABELS[$rw->status] ?? $rw->status }}
                                </span><br>
                                <a href="{{ route('admin.reward-redemptions.show', $rw->id) }}" class="btn btn-sm btn-outline-primary whitespace-nowrap">
                                    {{ ($rw->reward_type ?? 'product') === 'product' ? 'ส่งสินค้า' : 'ส่งคูปอง' }}
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-slate-500 py-10">ลูกค้าคนนี้ยังไม่เคยแลกของรางวัล</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- modal เพิ่มรายการให้ลูกค้า — render เฉพาะคนที่มีสิทธิ์เขียน (customers.manage) เพราะ route ฝั่งหลังถูกล็อกไว้แล้ว --}}
    @can('customers.manage')
    <div id="add-product-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.customers.products.store', $customer->id) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">เพิ่มสินค้าให้ลูกค้า</h2>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label class="form-label">เลือกสินค้า/บริการจาก Master</label>
                        <select name="master_product_id" class="form-select" required>
                            <option value="">-- กรุณาเลือก --</option>
                            @foreach($masterProducts as $mp)
                                <option value="{{ $mp->id }}">{{ $mp->name_th }} / {{ $mp->name_en }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">Serial Number (ถ้ามี)</label>
                        <input name="serial_number" type="text" class="form-control" placeholder="เช่น AEG-12345678">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">วันที่ซื้อติดตั้ง</label>
                        <input name="purchase_date" type="date" class="form-control">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">วันหมดประกัน</label>
                        <input name="warranty_expire_date" type="date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <div id="add-insurance-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.customers.insurances.store', $customer->id) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto"><i data-lucide="shield" class="w-4 h-4 inline mr-1"></i> เพิ่มประกันภัยให้ลูกค้า</h2>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label class="form-label">เลือกแพ็กเกจประกันภัย</label>
                        <select name="master_insurance_id" class="form-select" required>
                            <option value="">-- กรุณาเลือกประกัน --</option>
                            @foreach($masterInsurances as $ins)
                                <option value="{{ $ins->id }}">{{ $ins->title_th ?? $ins->title_th ?? 'ประกัน #' . $ins->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">เลขกรมธรรม์ (ถ้ามี)</label>
                        <input name="policy_number" type="text" class="form-control" placeholder="เช่น INS-2026-999">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">วันที่เริ่มคุ้มครอง</label>
                        <input name="purchase_date" type="date" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">วันสิ้นสุดความคุ้มครอง</label>
                        <input name="warranty_expire_date" type="date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>

    <div id="add-locker-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.customers.lockers.store', $customer->id) }}" method="POST" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto"><i data-lucide="lock" class="w-4 h-4 inline mr-1"></i> เพิ่มตู้เซฟเช่าให้ลูกค้า</h2>
                </div>
                <div class="modal-body grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12">
                        <label class="form-label">เลือกตู้เซฟ (Smart Lockers)</label>
                        <select name="master_locker_id" class="form-select" required>
                            <option value="">-- กรุณาเลือกตู้เซฟ --</option>
                            @foreach($masterLockers as $locker)
                                <option value="{{ $locker->id }}">{{ $locker->title_th ?? $locker->type.' : '.$locker->title_th ?? 'ตู้เซฟ #' . $locker->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">หมายเลขตู้ / รหัสตู้ (ถ้ามี)</label>
                        <input name="locker_number" type="text" class="form-control" placeholder="เช่น Locker A-01">
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">วันที่เริ่มเช่า</label>
                        <input name="purchase_date" type="date" class="form-control" required>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <label class="form-label">วันสิ้นสุดสัญญาเช่า</label>
                        <input name="warranty_expire_date" type="date" class="form-control">
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
    @endcan
    @endsection
