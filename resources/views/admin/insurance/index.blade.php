<?php
    // ประกาศตัวแปรหลอกๆ เพื่อป้องกัน Midone Template Error
    $first_level_active_index = 'insurances';
    $second_level_active_index = '';
    $third_level_active_index = '';
?>
@extends('../layout/' . $layout)

@section('subhead')
    <title>จัดการข้อมูลประกันภัย - AEG Admin</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">รายการข้อมูลประกันภัย (Insurances)</h2>
    
    @if(session('success'))
        <div class="alert alert-success show mb-2 mt-5" role="alert">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            <a href="{{ route('admin.insurances.create') }}" class="btn btn-primary shadow-md mr-2">
                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> เพิ่มประกันภัยใหม่
            </a>
        </div>
        
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible box p-5">
            <table class="table table-report -mt-2 w-full datatable">
                <thead>
                    <tr>
                        <th class="w-16">ลำดับ</th>
                        <th class="w-24">รูปภาพหน้าปก</th>
                        <th>ชื่อประกันภัย (Title)</th>
                        <th class="text-center">สถานะ</th>
                        <th class="text-center w-56">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($insurances as $item)
                        <tr class="intro-x">
                            <td class="text-center font-medium">{{ $item->sort_order }}</td>
                            <td>
                                <div class="w-16 h-10 image-fit zoom-in">
                                    <img alt="Insurance Cover" class="rounded-md border shadow-sm" 
                                         src="{{ $item->image_url ?? asset('dist/images/preview-1.jpg') }}">
                                </div>
                            </td>
                            <td>
                                <div class="font-medium whitespace-nowrap">{{ $item->title_th }}</div>
                                <div class="text-slate-500 text-xs mt-0.5">{{ $item->title_en ?: 'ไม่มีชื่อภาษาอังกฤษ' }}</div>
                            </td>
                            <td class="text-center">
                                <div class="flex items-center justify-center {{ $item->is_active ? 'text-success' : 'text-danger' }}">
                                    <i data-lucide="{{ $item->is_active ? 'check-square' : 'x-square' }}" class="w-4 h-4 mr-1"></i>
                                    {{ $item->is_active ? 'เปิดใช้งาน' : 'ปิดซ่อน' }}
                                </div>
                            </td>
                            <td class="table-report__action w-56 text-center">
                                <div class="flex justify-center items-center">
                                    <a class="btn btn-sm btn-outline-primary mr-3" href="{{ route('admin.insurances.edit', $item->id) }}">
                                        <i data-lucide="edit" class="w-4 h-4 mr-1"></i> แก้ไข
                                    </a>
                                    
                                    <form action="{{ route('admin.insurances.destroy', $item->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบข้อมูลประกันภัยนี้?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger flex items-center">
                                            <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ลบ
                                        </button>
                                    </form>
                                </div>
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
            "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" }
        });
    });
</script>
@endsection