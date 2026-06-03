@extends('../layout/' . $layout)

@section('subhead')
    <title>รายการรีวิวแพ็กเกจ - AEG Admin</title>
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">ประวัติการรีวิวและให้คะแนน (Customer Feedback)</h2>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 overflow-auto box p-5">
            <table class="table table-report -mt-2 w-full datatable">
                <thead>
                    <tr>
                        <th>วันที่รีวิว</th>
                        <th>ชื่อลูกค้า</th>
                        <th>แพ็กเกจ/สินค้า</th>
                        <th class="text-center">คะแนนงานติดตั้ง</th>
                        <th class="text-center">คะแนนพนักงานขาย</th>
                        <th>ข้อเสนอแนะ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reviews as $review)
                        <tr class="intro-x">
                            <td class="whitespace-nowrap">{{ $review->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $review->user->username ?? 'ไม่ระบุ' }}<br><span class="text-xs text-slate-500">{{ $review->user->phone }}</span></td>
                            <td class="font-medium">{{ $review->orderItem->product_name ?? 'ไม่ระบุ' }}</td>
                            <td class="text-center text-warning font-bold">{{ $review->install_rating }} ดาว</td>
                            <td class="text-center text-warning font-bold">{{ $review->sales_rating }} ดาว</td>
                            <td class="text-slate-500">{{ $review->review_text ?: '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
