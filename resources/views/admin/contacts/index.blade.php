@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $config['title'] }} - AEG Admin</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-start sm:items-center mt-10">
        <div class="mr-auto">
            <h2 class="text-lg font-medium">{{ $config['title'] }}</h2>
            <div class="text-slate-500 text-xs mt-1">ที่มา: {{ $config['source'] }}</div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible show flex items-center mb-2 mt-5" role="alert">
            <i data-lucide="check-circle" class="w-6 h-6 mr-2"></i> {{ session('success') }}
            <button type="button" class="btn-close text-white" data-tw-dismiss="alert" aria-label="Close"> <i data-lucide="x" class="w-4 h-4"></i> </button>
        </div>
    @endif

    {{-- แท็บกรองตามสถานะ + จำนวน --}}
    @php
        $totalAll = $counts->sum();
        $tabs = ['pending' => $statuses['pending']['label'], 'contacted' => $statuses['contacted']['label'], 'closed' => $statuses['closed']['label'], 'all' => 'ทั้งหมด'];
    @endphp
    <div class="intro-y flex flex-wrap gap-2 mt-5">
        @foreach($tabs as $key => $label)
            @php $n = $key === 'all' ? $totalAll : ($counts[$key] ?? 0); @endphp
            <a href="{{ route('admin.contacts.index', ['type' => $type, 'status' => $key]) }}"
               class="btn {{ $statusFilter === $key ? 'btn-primary' : 'btn-outline-secondary' }} btn-sm">
                {{ $label }}
                <span class="ml-2 px-1.5 rounded-full text-xs {{ $statusFilter === $key ? 'bg-white/30' : 'bg-slate-200 text-slate-600' }}">{{ $n }}</span>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full datatable">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">วันที่ติดต่อ</th>
                        <th class="whitespace-nowrap">ลูกค้า</th>
                        <th class="whitespace-nowrap">{{ $config['ref']['caption'] }}</th>
                        <th class="whitespace-nowrap">ช่วงเวลาที่สะดวก</th>
                        <th>ข้อความ</th>
                        <th class="text-center whitespace-nowrap">สถานะ</th>
                        <th class="whitespace-nowrap">ผู้รับผิดชอบ</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contacts as $c)
                        <tr class="intro-x">
                            <td class="whitespace-nowrap" data-order="{{ $c->created_at }}">
                                <div>{{ \Carbon\Carbon::parse($c->created_at)->format('d/m/Y') }}</div>
                                <div class="text-slate-500 text-xs">{{ \Carbon\Carbon::parse($c->created_at)->format('H:i') }} น. · {{ $c->display_ref }}</div>
                            </td>
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $c->display_name ?: '-' }}</div>
                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">
                                    <i data-lucide="phone" class="w-3 h-3 inline"></i> {{ $c->phone ?? '-' }}
                                </div>
                                <div class="text-slate-500 text-xs whitespace-nowrap">
                                    <i data-lucide="mail" class="w-3 h-3 inline"></i> {{ $c->email ?? '-' }}
                                </div>
                            </td>
                            <td>
                                @if($type === 'sales')
                                    <div class="text-xs"><span class="text-slate-500">หัวข้อ:</span> <span class="font-medium">{{ $c->topic ?? '-' }}</span></div>
                                    @if($c->interest_label || $c->product_name)
                                        <div class="text-xs mt-0.5">{{ $c->interest_label ?? $c->product_name }}@if($c->quantity) × {{ $c->quantity }}@endif</div>
                                    @endif
                                    <div class="text-xs mt-0.5">
                                        <span class="px-1.5 py-0.5 rounded {{ ($c->user_type ?? '') === 'business' ? 'bg-primary/10 text-primary' : 'bg-slate-100 text-slate-500' }}">
                                            {{ ($c->user_type ?? '') === 'business' ? 'ธุรกิจ' : 'บุคคล' }}
                                        </span>
                                        @if(($c->user_type ?? '') === 'business' && $c->company_name)
                                            <span class="text-slate-500 ml-1">{{ $c->company_name }}</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-xs">{{ $c->interest_label ?? '-' }}</div>
                                @endif
                            </td>
                            <td class="text-xs whitespace-nowrap">{{ $c->display_contact_time ?: '-' }}</td>
                            <td class="text-xs text-slate-600" style="max-width: 260px;">
                                {{ \Illuminate\Support\Str::limit($c->display_message ?: '-', 90) }}
                            </td>
                            <td class="text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium whitespace-nowrap {{ $statuses[$c->status]['class'] }}">
                                    {{ $statuses[$c->status]['label'] }}
                                </span>
                            </td>
                            <td class="text-xs whitespace-nowrap">
                                @if($c->assigned_name)
                                    <i data-lucide="user-check" class="w-3 h-3 inline text-success"></i> {{ $c->assigned_name }}
                                @else
                                    <span class="text-slate-400">ยังไม่มอบหมาย</span>
                                @endif
                            </td>
                            <td class="table-report__action text-center">
                                <a class="btn btn-sm btn-primary whitespace-nowrap" href="{{ route('admin.contacts.show', ['type' => $type, 'id' => $c->id]) }}">
                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i> ดำเนินการ
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@section('script')
<script src="{{ asset('dist/js/datatables.min.js') }}"></script>
<script>
    $(document).ready(function(){
        $('.datatable').DataTable({
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" },
            "order": [[ 0, "desc" ]],
            "pageLength": 25,
            "initComplete": function() {
                $('.dataTables_filter input').attr('autocomplete', 'off');
            }
        });
    });
</script>
@endsection
