@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - AEG</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-12">
            <div class="grid grid-cols-12 gap-6">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">ภาพรวมระบบ</h2>
                        <a href="" class="ml-auto flex items-center text-primary">
                            <i data-lucide="refresh-ccw" class="w-4 h-4 mr-3"></i> Reload Data
                        </a>
                    </div>
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-lucide="folder-kanban" class="report-box__icon text-primary"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $projectCount }}</div>
                                    <div class="text-base text-slate-500 mt-1">โครงการทั้งหมด</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-lucide="package" class="report-box__icon text-pending"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $assetCount }}</div>
                                    <div class="text-base text-slate-500 mt-1">สินทรัพย์ในระบบ</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 sm:col-span-6 xl:col-span-4 intro-y">
                            <div class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-lucide="users" class="report-box__icon text-success"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $userCount }}</div>
                                    <div class="text-base text-slate-500 mt-1">ผู้ใช้งานทั้งหมด</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: General Report -->

                <!-- BEGIN: Recent Projects -->
                <div class="col-span-12 mt-6">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">โครงการล่าสุด</h2>
                        <div class="sm:ml-auto mt-3 sm:mt-0 relative text-slate-500">
                            <i data-lucide="search" class="w-4 h-4 z-10 absolute my-auto inset-y-0 ml-3 left-0"></i>
                            <input type="text" class="form-control sm:w-56 box pl-10" placeholder="ค้นหาโครงการ...">
                        </div>
                    </div>
                    <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                        <table class="table table-report sm:mt-2">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">ชื่อโครงการ</th>
                                    <th class="text-center whitespace-nowrap">สถานะ</th>
                                    <th class="text-center whitespace-nowrap">วันที่สร้าง</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentProjects as $project)
                                    <tr class="intro-x">
                                        <td>
                                            <a href="#" class="font-medium whitespace-nowrap">{{ $project->project_name }}</a>
                                            <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ Str::limit($project->description, 50) }}</div>
                                        </td>
                                        <td class="w-40 text-center">
                                            <div class="flex items-center justify-center whitespace-nowrap text-success">
                                                <i data-lucide="check-square" class="w-4 h-4 mr-2"></i> {{ $project->status }}
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $project->created_at->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    <tr class="intro-x">
                                        <td colspan="3" class="text-center">ยังไม่มีข้อมูลโครงการ</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="intro-y flex flex-wrap sm:flex-row sm:flex-nowrap items-center mt-3">
                        <nav class="w-full sm:w-auto sm:mr-auto">
                           {{-- Pagination links can be added here if needed --}}
                        </nav>
                        <a href="#" class="btn btn-primary shadow-md mr-2">ดูโครงการทั้งหมด</a>
                    </div>
                </div>
                <!-- END: Recent Projects -->
            </div>
        </div>
    </div>
@endsection
