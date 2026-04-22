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
                </div>
            </div>
        </div>

        <div class="intro-y col-span-12 lg:col-span-8">
            <div class="box p-5">
                <div class="flex items-center border-b border-slate-200/60 pb-5">
                    <div class="font-medium text-base mr-auto">สินค้าและบริการที่ลูกค้าครอบครอง</div>
                    <button class="btn btn-primary btn-sm" data-tw-toggle="modal" data-tw-target="#add-product-modal">
                        <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มสินค้าให้ลูกค้า
                    </button>
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
                                <option value="{{ $mp->id }}">{{ $mp->name }}</option>
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
    @endsection