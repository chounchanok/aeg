@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $config['short'] }} {{ $contact->display_ref }} - AEG Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css" rel="stylesheet" />
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-start sm:items-center mt-8">
        <div class="mr-auto">
            <h2 class="text-lg font-medium">
                {{ $config['short'] }} · {{ $contact->display_ref }}
                <span class="ml-2 px-2 py-1 rounded-full text-xs font-medium align-middle {{ $statuses[$contact->status]['class'] }}">{{ $statuses[$contact->status]['label'] }}</span>
            </h2>
            <div class="text-slate-500 text-xs mt-1">
                ติดต่อเมื่อ {{ \Carbon\Carbon::parse($contact->created_at)->format('d/m/Y H:i') }} น. · ที่มา: {{ $config['source'] }}
            </div>
        </div>
        <a href="{{ route('admin.contacts.index', ['type' => $type]) }}" class="btn btn-outline-secondary mt-3 sm:mt-0">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> กลับหน้ารายการ
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show flex items-center mb-2 mt-5" role="alert">
            <i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> {{ session('success') }}
            <button type="button" class="btn-close text-white" data-tw-dismiss="alert" aria-label="Close"> <i data-lucide="x" class="w-4 h-4"></i> </button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible show flex items-center mb-2 mt-5" role="alert">
            <i data-lucide="alert-circle" class="w-6 h-6 mr-2"></i> {{ $errors->first() }}
            <button type="button" class="btn-close text-white" data-tw-dismiss="alert" aria-label="Close"> <i data-lucide="x" class="w-4 h-4"></i> </button>
        </div>
    @endif

    <div class="grid grid-cols-12 gap-6 mt-5">
        {{-- ซ้าย: รายละเอียดที่ลูกค้าติดต่อเข้ามา + timeline --}}
        <div class="intro-y col-span-12 lg:col-span-8">
            <div class="box p-5">
                <div class="flex items-center border-b border-slate-200/60 pb-4 mb-4">
                    <i data-lucide="user" class="w-5 h-5 mr-2 text-primary"></i>
                    <div class="font-medium text-base">ข้อมูลผู้ติดต่อและรายละเอียด</div>
                </div>

                <div class="grid grid-cols-12 gap-4 gap-y-4">
                    <div class="col-span-12 sm:col-span-6">
                        <div class="text-slate-500 text-xs">ชื่อผู้ติดต่อ</div>
                        <div class="font-medium mt-1">{{ $contact->display_name ?: '-' }}</div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="text-slate-500 text-xs">เบอร์โทรศัพท์</div>
                        <div class="font-medium mt-1"><a href="tel:{{ $contact->phone }}" class="text-primary">{{ $contact->phone ?? '-' }}</a></div>
                    </div>
                    <div class="col-span-12 sm:col-span-3">
                        <div class="text-slate-500 text-xs">อีเมล</div>
                        <div class="font-medium mt-1 break-all"><a href="mailto:{{ $contact->email }}" class="text-primary">{{ $contact->email ?? '-' }}</a></div>
                    </div>

                    @if($type === 'sales')
                        <div class="col-span-12 sm:col-span-6">
                            <div class="text-slate-500 text-xs">หัวข้อที่ติดต่อ</div>
                            <div class="font-medium mt-1">{{ $contact->topic ?? '-' }}</div>
                        </div>
                        <div class="col-span-12 sm:col-span-6">
                            <div class="text-slate-500 text-xs">ประเภทผู้ติดต่อ</div>
                            <div class="font-medium mt-1">
                                @if(($contact->user_type ?? '') === 'business')
                                    <span class="px-2 py-0.5 rounded bg-primary/10 text-primary text-xs">ธุรกิจ</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-xs">บุคคลทั่วไป</span>
                                @endif
                            </div>
                        </div>
                        @if(($contact->user_type ?? '') === 'business' || $contact->company_name || $contact->tax_id)
                            <div class="col-span-12 sm:col-span-6">
                                <div class="text-slate-500 text-xs">บริษัท</div>
                                <div class="font-medium mt-1">{{ $contact->company_name ?? '-' }}</div>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <div class="text-slate-500 text-xs">เลขประจำตัวผู้เสียภาษี</div>
                                <div class="font-medium mt-1">{{ $contact->tax_id ?? '-' }}</div>
                            </div>
                            <div class="col-span-12 sm:col-span-3">
                                <div class="text-slate-500 text-xs">สาขา</div>
                                <div class="font-medium mt-1">{{ $contact->branch ?? '-' }}</div>
                            </div>
                        @endif
                        <div class="col-span-12">
                            <div class="text-slate-500 text-xs">ที่อยู่</div>
                            <div class="font-medium mt-1">
                                {{ $contact->address_full ?? '-' }}
                                @php $parts = array_filter([$contact->subdistrict ?? null, $contact->district ?? null, $contact->province ?? null, $contact->zipcode ?? null]); @endphp
                                @if(!empty($parts))
                                    <div class="text-slate-500 text-xs mt-0.5">{{ implode(' · ', $parts) }}</div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="col-span-12 sm:col-span-6">
                        <div class="text-slate-500 text-xs">{{ $config['ref']['caption'] }}</div>
                        <div class="font-medium mt-1">
                            {{ $contact->interest_label ?? ($contact->product_name ?? '-') }}
                            @if($type === 'sales' && ($contact->quantity ?? null))
                                <span class="text-slate-500 text-xs">× {{ $contact->quantity }} ชิ้น</span>
                            @endif
                            @if($type === 'sales' && $contact->interest_label && ($contact->product_name ?? null) && $contact->product_name !== $contact->interest_label)
                                <div class="text-slate-500 text-xs mt-0.5">ลูกค้าระบุ: {{ $contact->product_name }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-6">
                        <div class="text-slate-500 text-xs">ช่วงเวลาที่สะดวกให้ติดต่อกลับ</div>
                        <div class="font-medium mt-1">{{ $contact->display_contact_time ?: '-' }}</div>
                    </div>
                    <div class="col-span-12">
                        <div class="text-slate-500 text-xs">ข้อความ / รายละเอียดเพิ่มเติม</div>
                        <div class="mt-1 whitespace-pre-line bg-slate-50 dark:bg-darkmode-400 rounded-md p-3 text-sm">{{ $contact->display_message ?: '-' }}</div>
                    </div>

                    @if($type === 'sales' && ($contact->image_url ?? null))
                        <div class="col-span-12">
                            <div class="text-slate-500 text-xs mb-1">รูปภาพที่แนบมา</div>
                            <a href="{{ $contact->image_url }}" data-lightbox="contact-image" class="w-32 h-32 image-fit zoom-in rounded-md overflow-hidden block border">
                                <img src="{{ $contact->image_url }}" alt="แนบมากับคำขอ" class="rounded-md">
                            </a>
                        </div>
                    @endif

                    @if($type === 'sales' && ($contact->user_id ?? null))
                        <div class="col-span-12">
                            <a href="{{ route('admin.customers.show', $contact->user_id) }}" class="text-primary text-sm">
                                <i data-lucide="external-link" class="w-4 h-4 inline"></i> ดูข้อมูลสมาชิกที่ส่งคำขอนี้ (ลูกค้าและแพ็กเกจ)
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Timeline การดำเนินการ --}}
            <div class="box p-5 mt-5">
                <div class="flex items-center border-b border-slate-200/60 pb-4 mb-4">
                    <i data-lucide="activity" class="w-5 h-5 mr-2 text-primary"></i>
                    <div class="font-medium text-base mr-auto">ประวัติการดำเนินการ</div>
                    <span class="text-slate-500 text-xs">{{ $activities->count() }} รายการ</span>
                </div>

                @forelse($activities as $a)
                    <div class="flex items-start py-3 {{ !$loop->last ? 'border-b border-slate-200/60 border-dashed' : '' }}">
                        <div class="w-9 h-9 flex-shrink-0 rounded-full flex items-center justify-center mr-3 {{ in_array($a->action, ['status_changed', 'assigned']) ? 'bg-slate-100 text-slate-500' : 'bg-primary/10 text-primary' }}">
                            <i data-lucide="{{ $actionIcons[$a->action] ?? 'circle' }}" class="w-4 h-4"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center">
                                <span class="font-medium">{{ $actionLabels[$a->action] ?? $a->action }}</span>
                                @if($a->action === 'status_changed')
                                    <span class="ml-2 text-xs">
                                        <span class="px-1.5 py-0.5 rounded-full {{ $statuses[$a->from_status]['class'] ?? 'bg-slate-100' }}">{{ $statuses[$a->from_status]['label'] ?? ($a->from_status ?? '-') }}</span>
                                        <i data-lucide="arrow-right" class="w-3 h-3 inline mx-1"></i>
                                        <span class="px-1.5 py-0.5 rounded-full {{ $statuses[$a->to_status]['class'] ?? 'bg-slate-100' }}">{{ $statuses[$a->to_status]['label'] ?? ($a->to_status ?? '-') }}</span>
                                    </span>
                                @endif
                            </div>
                            @if($a->note)
                                <div class="text-sm text-slate-600 dark:text-slate-400 mt-1 whitespace-pre-line">{{ $a->note }}</div>
                            @endif
                            <div class="text-slate-500 text-xs mt-1">
                                โดย {{ $a->staff_first_name ?: ($a->staff_username ?? 'ระบบ') }} · {{ \Carbon\Carbon::parse($a->created_at)->format('d/m/Y H:i') }} น.
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-slate-500 py-8">
                        <i data-lucide="inbox" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                        ยังไม่มีการดำเนินการกับรายการนี้ — เริ่มบันทึกได้จากฟอร์มด้านขวา
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ขวา: ฟอร์มบันทึกการดำเนินการ / เปลี่ยนสถานะ / มอบหมาย --}}
        <div class="intro-y col-span-12 lg:col-span-4">
            <div class="box p-5">
                <div class="flex items-center border-b border-slate-200/60 pb-4 mb-4">
                    <i data-lucide="clipboard-check" class="w-5 h-5 mr-2 text-primary"></i>
                    <div class="font-medium text-base">บันทึกการดำเนินการ</div>
                </div>

                <form action="{{ route('admin.contacts.update', ['type' => $type, 'id' => $contact->id]) }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">สิ่งที่ดำเนินการ</label>
                        <select name="action" class="form-select">
                            <option value="">— ไม่ระบุ (เปลี่ยนสถานะ/มอบหมายอย่างเดียว) —</option>
                            @foreach($actions as $key => $a)
                                <option value="{{ $key }}" {{ old('action') === $key ? 'selected' : '' }}>{{ $a['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายละเอียด / ผลการติดต่อ</label>
                        <textarea name="note" class="form-control" rows="4" placeholder="เช่น โทรแล้ว ลูกค้าขอให้ส่งใบเสนอราคาทางอีเมล / ลูกค้าไม่รับสาย นัดโทรใหม่พรุ่งนี้ 10:00">{{ old('note') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สถานะรายการ</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $key => $s)
                                <option value="{{ $key }}" {{ old('status', $contact->status) === $key ? 'selected' : '' }}>{{ $s['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="text-slate-500 text-xs mt-1">รอดำเนินการ → ติดต่อแล้ว (คุยกับลูกค้าแล้ว) → ปิดงาน (จบเรื่อง)</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">ผู้รับผิดชอบ</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">— ยังไม่มอบหมาย —</option>
                            @foreach($assignableStaff as $s)
                                <option value="{{ $s->id }}" {{ (string) old('assigned_to', $contact->assigned_to) === (string) $s->id ? 'selected' : '' }}>
                                    {{ $s->display_name }}{{ (int) $s->id === (int) auth()->id() ? ' (ฉัน)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">
                        <i data-lucide="save" class="w-4 h-4 mr-1"></i> บันทึก
                    </button>
                </form>
            </div>

            <div class="box p-5 mt-5">
                <div class="font-medium mb-2">สรุปรายการ</div>
                <div class="flex justify-between text-sm py-1"><span class="text-slate-500">เลขที่อ้างอิง</span><span class="font-medium">{{ $contact->display_ref }}</span></div>
                <div class="flex justify-between text-sm py-1"><span class="text-slate-500">สถานะ</span><span class="font-medium">{{ $statuses[$contact->status]['label'] }}</span></div>
                <div class="flex justify-between text-sm py-1"><span class="text-slate-500">ผู้รับผิดชอบ</span><span class="font-medium">{{ $contact->assigned_name ?? 'ยังไม่มอบหมาย' }}</span></div>
                <div class="flex justify-between text-sm py-1"><span class="text-slate-500">อัปเดตล่าสุด</span><span class="font-medium">{{ \Carbon\Carbon::parse($contact->updated_at)->format('d/m/Y H:i') }}</span></div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/js/lightbox.min.js"></script>
@endsection
