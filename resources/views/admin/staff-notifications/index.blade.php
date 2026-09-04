@extends('../layout/' . $layout)

@section('subhead')
    <title>แจ้งเตือนภายในระบบ - AEG Admin</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">แจ้งเตือนภายในระบบ (แผนกของฉัน)</h2>
    <div class="text-slate-500 text-sm mt-1">แจ้งเตือนอัตโนมัติเมื่อมีรายการใหม่เข้ามาในแผนกที่ท่านดูแล เช่น แจ้งซ่อมใหม่ คำสั่งซื้อใหม่ คำขอใบเสนอราคา และคำขอติดต่อฝ่ายขาย</div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            @if($notifications->isEmpty())
                <div class="text-center text-slate-400 py-10">ยังไม่มีแจ้งเตือน</div>
            @else
                <table class="table table-report -mt-2 w-full">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">แผนก</th>
                            <th class="whitespace-nowrap">หัวข้อ</th>
                            <th class="whitespace-nowrap">รายละเอียด</th>
                            <th class="text-center whitespace-nowrap">เวลา</th>
                            <th class="text-center whitespace-nowrap">สถานะ</th>
                            <th class="text-center whitespace-nowrap">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notifications as $n)
                            <tr class="intro-x {{ $n->is_read ? '' : 'font-medium' }}">
                                <td class="whitespace-nowrap">{{ $n->role_name ?? '-' }}</td>
                                <td class="whitespace-nowrap">{{ $n->title }}</td>
                                <td>{{ $n->body }}</td>
                                <td class="text-center whitespace-nowrap">{{ \Carbon\Carbon::parse($n->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    @if($n->is_read)
                                        <span class="text-slate-400">อ่านแล้ว</span>
                                    @else
                                        <span class="text-primary">ยังไม่ได้อ่าน</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($n->link_url)
                                        <a href="{{ $n->link_url }}" class="btn btn-outline-secondary btn-sm mr-1">ดูรายการ</a>
                                    @endif
                                    @if(!$n->is_read)
                                        <button type="button" class="btn btn-primary btn-sm btn-mark-read" data-id="{{ $n->id }}">ทำเครื่องหมายว่าอ่านแล้ว</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    document.querySelectorAll('.btn-mark-read').forEach(function(btn) {
        btn.addEventListener('click', function() {
            let id = btn.dataset.id;
            axios.post(`/admin/staff-notifications/${id}/read`).then(function() {
                location.reload();
            });
        });
    });
</script>
@endsection
