@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9">
             <div class="grid grid-cols-12 gap-6">
                <div class="col-span-12 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">
                            ภาพรวมระบบ (General Report)
                        </h2>
                    </div>
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-lucide="user" class="report-box__icon text-primary"></i> 
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $stats['total_users'] }}</div>
                                    <div class="text-base text-slate-500 mt-1">ลูกค้าทั้งหมด</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-lucide="wrench" class="report-box__icon text-pending"></i> 
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $stats['pending_requests'] }}</div>
                                    <div class="text-base text-slate-500 mt-1">แจ้งซ่อมรอดำเนินการ</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection