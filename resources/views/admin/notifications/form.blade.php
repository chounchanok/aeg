@extends('../layout/side-menu')

@section('subhead')
    <title>ส่งแจ้งเตือนใหม่ - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">ส่งข้อความแจ้งเตือน (Push Notification)</h2>
    </div>
    
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-8">
            <form action="{{ route('admin.notifications.store') }}" method="POST">
                @csrf
                
                @if(session('error'))
                    <div class="alert alert-danger show mb-4" role="alert">{{ session('error') }}</div>
                @endif

                <div class="intro-y box p-5">
                    <div class="mb-4">
                        <label class="form-label font-medium">ส่งถึงใคร (Send To) <span class="text-danger">*</span></label>
                        <select name="send_to" class="form-select" required>
                            <option value="all">📢 ลูกค้าทั้งหมดในระบบ (Broadcast)</option>
                            <optgroup label="ส่งให้เฉพาะบุคคล">
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->username }} ({{ $c->phone }})</option>
                                @endforeach
                            </optgroup>
                        </select>
                        <div class="text-slate-500 text-xs mt-1">หากเลือก Broadcast ระบบจะทำการสร้างแจ้งเตือนส่งให้ลูกค้าทุกคน</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-medium">ประเภทแจ้งเตือน (Tab) <span class="text-danger">*</span></label>
                        <div class="flex flex-col sm:flex-row mt-2">
                            <div class="form-check mr-4">
                                <input id="type-general" class="form-check-input" type="radio" name="type" value="general" checked>
                                <label class="form-check-label" for="type-general">ทั่วไป (ทั้งหมด)</label>
                            </div>
                            <div class="form-check mr-4 mt-2 sm:mt-0">
                                <input id="type-promotion" class="form-check-input" type="radio" name="type" value="promotion">
                                <label class="form-check-label" for="type-promotion">โปรโมชัน (Promotion)</label>
                            </div>
                            <div class="form-check mr-4 mt-2 sm:mt-0">
                                <input id="type-privilege" class="form-check-input" type="radio" name="type" value="privilege">
                                <label class="form-check-label" for="type-privilege">สิทธิพิเศษ (Privilege)</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label font-medium">หัวข้อแจ้งเตือน (Title) <span class="text-danger">*</span></label>
                        <input name="title" type="text" class="form-control" placeholder="เช่น: พอยท์แลกส่วนลดสุดคุ้ม 30%" required>
                    </div>

                    <div class="mb-5">
                        <label class="form-label font-medium">รายละเอียด (Message Body) <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="4" placeholder="เช่น: ส่วนลดค่าติดตั้งบริการสูงสุด 30%..." required></textarea>
                    </div>

                    <div class="text-right border-t border-slate-200/60 pt-5 mt-5">
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary w-32"><i data-lucide="send" class="w-4 h-4 mr-2"></i> ส่งแจ้งเตือน</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection