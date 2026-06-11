@extends('../layout/side-menu')

@section('subhead')
    <title>รายการแชทติดต่อสอบถาม - AEG Admin</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายการแชทติดต่อสอบถาม (Support Inquiries)</h2>
    
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 overflow-auto box p-5">
            <table class="table table-report -mt-2 w-full">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">ติดต่อล่าสุด</th>
                        <th class="whitespace-nowrap">ลูกค้า</th>
                        <th class="whitespace-nowrap text-center">หัวข้อเรื่อง (Topic)</th>
                        <th class="text-center w-32">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($chats as $chat)
                        <tr class="intro-x">
                            <td class="whitespace-nowrap">{{ \Carbon\Carbon::parse($chat->last_contact)->format('d/m/Y H:i') }}</td>
                            <td class="font-medium">
                                {{ $chat->username ?? 'Unknown' }} 
                                <br><span class="text-xs text-slate-500">{{ $chat->phone }}</span>
                            </td>
                            <td class="text-center font-medium text-primary">{{ $chat->topic }}</td>
                            <td class="table-report__action w-32 text-center">
                                <a href="{{ route('admin.support-chats.show', ['user_id' => $chat->user_id, 'topic' => $chat->topic]) }}" class="btn btn-sm btn-primary">
                                    <i data-lucide="message-square" class="w-4 h-4 mr-1"></i> ตอบแชท
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-slate-500 py-5">ไม่มีรายการแชทติดต่อสอบถาม</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection