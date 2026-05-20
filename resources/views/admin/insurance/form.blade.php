<?php
    // ประกาศตัวแปรหลอกๆ เพื่อป้องกัน Midone Template Error
    $first_level_active_index = 'insurances';
    $second_level_active_index = '';
    $third_level_active_index = '';
?>
@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ isset($insurance) ? 'แก้ไขข้อมูลประกันภัย' : 'เพิ่มประกันภัยใหม่' }} - AEG Admin</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ isset($insurance) ? 'แก้ไขข้อมูลประกันภัย' : 'เพิ่มข้อมูลประกันภัยใหม่' }}</h2>
    </div>
    
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-8">
            <form action="{{ isset($insurance) ? route('admin.insurances.update', $insurance->id) : route('admin.insurances.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($insurance)) @method('PUT') @endif
                
                <div class="intro-y box p-5">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label font-medium">ชื่อประกันภัย (ภาษาไทย) <span class="text-danger">*</span></label>
                            <input name="title_th" type="text" class="form-control" value="{{ $insurance->title_th ?? '' }}" required>
                        </div>
                        <div>
                            <label class="form-label font-medium">ชื่อประกันภัย (English)</label>
                            <input name="title_en" type="text" class="form-control" value="{{ $insurance->title_en ?? '' }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 mt-4">
                        <div>
                            <label class="form-label font-medium">รายละเอียดเพิ่มเติม (ภาษาไทย)</label>
                            <textarea name="description_th" class="form-control" rows="5">{{ $insurance->description_th ?? '' }}</textarea>
                            <div class="text-slate-500 text-xs mt-1">สามารถใส่แท็ก HTML ได้ เช่น &lt;ul&gt;&lt;li&gt;ข้อที่ 1&lt;/li&gt;&lt;/ul&gt;</div>
                        </div>
                        <div class="mt-2">
                            <label class="form-label font-medium">รายละเอียดเพิ่มเติม (English)</label>
                            <textarea name="description_en" class="form-control" rows="5">{{ $insurance->description_en ?? '' }}</textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5 pt-5 border-t border-slate-200/60">
                        <div>
                            <label class="form-label font-medium">ลำดับการแสดงผล (Sort Order)</label>
                            <input name="sort_order" type="number" class="form-control" value="{{ $insurance->sort_order ?? '0' }}">
                            <div class="text-slate-500 text-xs mt-1">ตัวเลขน้อยจะแสดงก่อน</div>
                        </div>
                        
                        <div>
                            <label class="form-label font-medium">รูปภาพหน้าปก (Cover Image)</label>
                            <input name="image" type="file" class="form-control" accept="image/*">
                            @if(isset($insurance) && $insurance->image_url)
                                <div class="mt-2 flex items-center">
                                    <div class="w-12 h-12 image-fit zoom-in mr-2">
                                        <img alt="Cover" class="rounded-md border" src="{{ $insurance->image_url }}">
                                    </div>
                                    <a href="{{ $insurance->image_url }}" target="_blank" class="text-primary text-xs underline">ดูรูปภาพปัจจุบัน</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-check mt-5">
                        <input name="is_active" type="hidden" value="0">
                        <input name="is_active" class="form-check-input" type="checkbox" id="active-checkbox" value="1" {{ (!isset($insurance) || $insurance->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label font-medium" for="active-checkbox">เปิดใช้งาน (แสดงบนเว็บไซต์)</label>
                    </div>

                    <div class="text-right mt-6 border-t border-slate-200/60 pt-5">
                        <a href="{{ route('admin.insurances.index') }}" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</a>
                        <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                    </div>
                    
                </div>
            </form>
        </div>
    </div>
@endsection