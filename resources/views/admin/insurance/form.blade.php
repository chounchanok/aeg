<?php
    // ประกาศตัวแปรหลอกๆ เพื่อป้องกัน Midone Template Error
    $first_level_active_index = 'insurances';
    $second_level_active_index = '';
    $third_level_active_index = '';
?>
@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ isset($insurance) ? 'แก้ไขข้อมูลประกันภัย' : 'เพิ่มประกันภัยใหม่' }} - AEG Admin</title>
    
    <!-- 🌟 โหลด CSS ของ Summernote -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    
    <style>
        /* ปรับแต่ง Summernote ให้เข้ากับ Theme ของ Tailwind / Midone */
        .note-editor.note-frame {
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            background: white;
        }
        .note-editor .note-toolbar {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 0.375rem 0.375rem 0 0;
        }
        .note-editable {
            font-family: inherit;
        }
    </style>
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
                            <!-- 🌟 ใส่ id ให้ textarea เพื่อเรียกใช้ Summernote -->
                            <textarea id="editor_th" name="description_th" class="form-control">{{ $insurance->description_th ?? '' }}</textarea>
                        </div>
                        <div class="mt-4">
                            <label class="form-label font-medium">รายละเอียดเพิ่มเติม (English)</label>
                            <!-- 🌟 ใส่ id ให้ textarea เพื่อเรียกใช้ Summernote -->
                            <textarea id="editor_en" name="description_en" class="form-control">{{ $insurance->description_en ?? '' }}</textarea>
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

                        <div>
                            <label class="form-label font-medium">รูปภาพด้านใน (Inner Image)</label>
                            <input name="image_inside" type="file" class="form-control" accept="image/*">
                            @if(isset($insurance) && $insurance->image_inside_url)
                                <div class="mt-2 flex items-center">
                                    <div class="w-12 h-12 image-fit zoom-in mr-2">
                                        <img alt="Inner" class="rounded-md border" src="{{ $insurance->image_inside_url }}">
                                    </div>
                                    <a href="{{ $insurance->image_inside_url }}" target="_blank" class="text-primary text-xs underline">ดูรูปภาพปัจจุบัน</a>
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

@section('script')
    <!-- 🌟 โหลด jQuery (Summernote ต้องใช้ jQuery) และ JS ของ Summernote -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // ตั้งค่า Summernote ทั่วไป
            var summernoteOptions = {
                height: 250, // ความสูงของกล่องข้อความ
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            };

            // เปิดใช้งาน Summernote ทั้ง 2 กล่อง
            $('#editor_th').summernote(summernoteOptions);
            $('#editor_en').summernote(summernoteOptions);
        });
    </script>
@endsection