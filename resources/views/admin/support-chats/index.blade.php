@extends('../layout/side-menu')

@section('subhead')
    <title>รายการแชทติดต่อสอบถาม - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-start sm:items-center mt-10">
        <div class="mr-auto">
            <h2 class="text-lg font-medium">รายการแชทติดต่อสอบถาม (Support Inquiries)</h2>
            <div class="text-slate-500 text-xs mt-1">
                แสดงเฉพาะหัวข้อที่แผนกของคุณรับผิดชอบ:
                @foreach($allowedTopics as $t)
                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-600">{{ $topicLabels[$t] ?? $t }}</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- แท็บกรองตามหัวข้อ + จำนวนเธรดที่รอตอบ --}}
    @php $waitingAll = $waitingByTopic->sum(); @endphp
    <div class="intro-y flex flex-wrap gap-2 mt-5">
        <a href="{{ route('admin.support-chats.index') }}" class="btn btn-sm {{ $topicFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
            ทุกหัวข้อ
            @if($waitingAll > 0)<span class="ml-2 px-1.5 rounded-full text-xs {{ $topicFilter === 'all' ? 'bg-white/30' : 'bg-danger text-white' }}">{{ $waitingAll }}</span>@endif
        </a>
        @foreach($allowedTopics as $t)
            @php $w = $waitingByTopic[$t] ?? 0; @endphp
            <a href="{{ route('admin.support-chats.index', ['topic' => $t]) }}" class="btn btn-sm {{ $topicFilter === $t ? 'btn-primary' : 'btn-outline-secondary' }}">
                {{ $topicLabels[$t] ?? $t }}
                @if($w > 0)<span class="ml-2 px-1.5 rounded-full text-xs {{ $topicFilter === $t ? 'bg-white/30' : 'bg-danger text-white' }}">{{ $w }}</span>@endif
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 overflow-auto box p-5">
            <table class="table table-report -mt-2 w-full">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">ติดต่อล่าสุด</th>
                        <th class="whitespace-nowrap">ลูกค้า</th>
                        <th class="whitespace-nowrap">หัวข้อเรื่อง (Topic)</th>
                        <th class="whitespace-nowrap text-center">สถานะ</th>
                        <th class="text-center w-32">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($chats as $chat)
                        @php $waiting = $chat->last_sender_type === 'customer'; @endphp
                        <tr class="intro-x">
                            <td class="whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($chat->last_contact)->format('d/m/Y H:i') }}
                                <div class="text-xs text-slate-500">{{ $chat->message_count }} ข้อความ</div>
                            </td>
                            <td class="font-medium">
                                {{ trim(($chat->first_name ?? '') . ' ' . ($chat->last_name ?? '')) ?: ($chat->username ?? 'Unknown') }}
                                <br><span class="text-xs text-slate-500 font-normal">{{ $chat->phone }} @if($chat->username) · {{ $chat->username }} @endif</span>
                            </td>
                            <td>
                                <div class="font-medium text-primary">{{ $topicLabels[$chat->topic] ?? $chat->topic }}</div>
                                <div class="text-xs text-slate-500">แผนก: {{ $departmentNames[$chat->topic] ?? '-' }}</div>
                            </td>
                            <td class="text-center">
                                @if($waiting)
                                    <span class="px-2 py-1 rounded-full text-xs font-medium text-danger bg-danger/20 whitespace-nowrap">รอตอบ</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-medium text-success bg-success/20 whitespace-nowrap">ตอบแล้ว</span>
                                @endif
                            </td>
                            <td class="table-report__action w-32 text-center">
                                <a href="{{ route('admin.support-chats.show', ['user_id' => $chat->user_id, 'topic' => $chat->topic]) }}" class="btn btn-sm {{ $waiting ? 'btn-primary' : 'btn-outline-primary' }}">
                                    <i data-lucide="message-square" class="w-4 h-4 mr-1"></i> ตอบแชท
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-slate-500 py-5">ไม่มีรายการแชทติดต่อสอบถามในหัวข้อที่แผนกของคุณรับผิดชอบ</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
