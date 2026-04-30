@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-12">
            <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">ภาพรวมระบบ (General Report)</h2>
                    </div>
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-lucide="shopping-cart" class="report-box__icon text-primary"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">฿{{ number_format($totalSales, 0) }}</div>
                                    <div class="text-base text-slate-500 mt-1">ยอดขายที่สำเร็จแล้ว</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-lucide="wrench" class="report-box__icon text-pending"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $pendingService }}</div>
                                    <div class="text-base text-slate-500 mt-1">งานซ่อมรอรับเรื่อง</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-lucide="users" class="report-box__icon text-warning"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ number_format($totalCustomers) }}</div>
                                    <div class="text-base text-slate-500 mt-1">จำนวนลูกค้าสมาชิก</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-lucide="package" class="report-box__icon text-success"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $activeProducts }}</div>
                                    <div class="text-base text-slate-500 mt-1">สินค้า/บริการที่เปิดขาย</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-8 mt-8">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">สถิติยอดขาย 7 วันย้อนหลัง</h2>
                    </div>
                    <div class="intro-y box p-5 mt-12 sm:mt-5">
                        <canvas id="salesChart" height="150"></canvas>
                    </div>
                </div>
                <div class="col-span-12 lg:col-span-4 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">งานแจ้งซ่อมล่าสุด</h2>
                    </div>
                    <div class="mt-5">
                        @foreach($recentServices as $rs)
                        <div class="intro-y">
                            <div class="box px-4 py-4 mb-3 flex items-center zoom-in">
                                <div class="ml-4 mr-auto">
                                    <div class="font-medium">{{ $rs->username }}</div>
                                    <div class="text-slate-500 text-xs mt-0.5">{{ \Carbon\Carbon::parse($rs->created_at)->format('d/m/Y H:i') }}</div>
                                </div>
                                <div class="py-1 px-2 rounded-full text-xs bg-slate-100 text-slate-600 font-medium">
                                    {{ $rs->status }}
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <a href="{{ route('admin.service-requests') }}" class="intro-y w-full block text-center rounded-md py-3 border border-dotted border-slate-400 dark:border-darkmode-300 text-slate-500">ดูทั้งหมด</a>
                    </div>
                </div>
                </div>
        </div>
    </div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($salesData['labels']) !!},
            datasets: [{
                label: 'ยอดขาย (บาท)',
                data: {!! json_encode($salesData['values']) !!},
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endsection