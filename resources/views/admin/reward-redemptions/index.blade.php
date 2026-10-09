@extends('../layout/' . $layout)

@section('subhead')
    <title>คูปอง/ของรางวัลที่ลูกค้าแลก - AEG Admin</title>
@endsection

@php
    $typeLabels = \App\Services\RewardService::TYPE_LABELS;
    $statusLabels = \App\Services\RewardService::STATUS_LABELS;
    $statusClass = [
        'active' => 'bg-slate-100 text-slate-600',
        'shipping_confirm' => 'bg-warning/20 text-warning',
        'processing' => 'bg-blue-100 text-blue-700',
        'shipping' => 'bg-indigo-100 text-indigo-700',
        'delivered' => 'bg-success/20 text-success',
        'used' => 'bg-success/20 text-success',
        'cancelled' => 'bg-danger/20 text-danger',
    ];
@endphp

@section('subcontent')
    <div class="intro-y flex items-center mt-10">
        <h2 class="text-lg font-medium mr-auto">คูปอง/ของรางวัลที่ลูกค้าแลก</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success show mt-5">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger show mt-5">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-12 gap-4 mt-5">
        <a href="{{ route('admin.reward-redemptions.index', ['status' => 'shipping_confirm']) }}" class="col-span-12 sm:col-span-4 box p-4 zoom-in" style="padding: 15px;">
            <div class="text-slate-500 text-xs">รอตรวจสอบที่อยู่ / ยืนยันการจัดส่ง</div>
            <div class="text-2xl font-medium mt-1 text-warning">{{ number_format($counts['shipping_confirm']) }}</div>
        </a>
        <a href="{{ route('admin.reward-redemptions.index', ['reward_type' => 'voucher', 'status' => 'active']) }}" class="col-span-12 sm:col-span-4 box p-4 zoom-in" style="padding: 15px;">
            <div class="text-slate-500 text-xs">วอยเชอร์ที่ยังไม่ได้ส่งรหัส</div>
            <div class="text-2xl font-medium mt-1 text-primary">{{ number_format($counts['waiting_voucher']) }}</div>
        </a>
        <a href="{{ route('admin.reward-redemptions.index', ['status' => 'processing,shipping']) }}" class="col-span-12 sm:col-span-4 box p-4 zoom-in" style="padding: 15px;">
            <div class="text-slate-500 text-xs">กำลังดำเนินการ / กำลังจัดส่ง</div>
            <div class="text-2xl font-medium mt-1">{{ number_format($counts['in_progress']) }}</div>
        </a>
    </div>

    <form method="GET" class="intro-y box p-5 mt-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="form-label text-xs">ค้นหา</label>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control w-64" placeholder="ชื่อลูกค้า / เบอร์ / โค้ด / ของรางวัล">
        </div>
        <div>
            <label class="form-label text-xs">ประเภท</label>
            <select name="reward_type" class="form-select w-56">
                <option value="">ทั้งหมด</option>
                @foreach($typeLabels as $k => $v)
                    <option value="{{ $k }}" @selected(request('reward_type') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">สถานะ</label>
            <select name="status" class="form-select w-48">
                <option value="">ทั้งหมด</option>
                @foreach($statusLabels as $k => $v)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary">กรอง</button>
        <a href="{{ route('admin.reward-redemptions.index') }}" class="btn btn-outline-secondary">ล้าง</a>
    </form>

    <div class="intro-y box p-5 mt-5 overflow-auto">
        <table class="table table-report -mt-2 w-full">
            <thead>
                <tr>
                    <th class="whitespace-nowrap">วันที่แลก</th>
                    <th class="whitespace-nowrap">ลูกค้า</th>
                    <th class="whitespace-nowrap">ของรางวัล</th>
                    <th class="whitespace-nowrap">ประเภท</th>
                    <th class="whitespace-nowrap">โค้ด / รหัสที่ส่งให้</th>
                    <th class="text-center whitespace-nowrap">สถานะ</th>
                    <th class="text-center whitespace-nowrap">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($codes as $c)
                    @php
                        $type = $c->reward_type ?: 'product';
                        $needsAction = $c->status === 'shipping_confirm' || ($type === 'voucher' && $c->status === 'active' && empty($c->voucher_code));
                    @endphp
                    <tr class="intro-x">
                        <td class="whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <a href="{{ route('admin.customers.show', $c->user_id) }}" class="font-medium text-primary whitespace-nowrap">
                                {{ trim(($c->first_name ?? '') . ' ' . ($c->last_name ?? '')) ?: $c->username }}
                            </a>
                            <div class="text-slate-500 text-xs">{{ $c->phone ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="font-medium">{{ $c->reward_title }}</div>
                            <div class="text-slate-500 text-xs">{{ number_format($c->points_required) }} Pt</div>
                        </td>
                        <td class="text-sm whitespace-nowrap">{{ $typeLabels[$type] ?? $type }}</td>
                        <td class="text-sm">
                            <div class="font-mono">{{ $c->code }}</div>
                            @if($type !== 'product')
                                <div class="text-xs {{ $c->voucher_code ? 'text-success' : 'text-warning' }}">
                                    {{ $c->voucher_code ? 'ส่งรหัสแล้ว: ' . $c->voucher_code : ($type === 'voucher' ? 'ยังไม่ส่งรหัส' : 'ใช้โค้ด RWD') }}
                                </div>
                            @elseif($c->tracking_number)
                                <div class="text-xs text-slate-500">Tracking: {{ $c->tracking_number }}</div>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="px-2 py-1 rounded-full text-xs whitespace-nowrap {{ $statusClass[$c->status] ?? '' }}">{{ $statusLabels[$c->status] ?? $c->status }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.reward-redemptions.show', $c->id) }}" class="btn btn-sm {{ $needsAction ? 'btn-primary' : 'btn-outline-secondary' }} whitespace-nowrap">
                                @if($type === 'product')
                                    <i data-lucide="truck" class="w-4 h-4 mr-1"></i> ส่งสินค้า
                                @else
                                    <i data-lucide="ticket" class="w-4 h-4 mr-1"></i> ส่งคูปอง
                                @endif
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-slate-500">ไม่พบรายการ</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $codes->links() }}</div>
    </div>
@endsection
