@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ isset($project) ? 'แก้ไขโครงการ' : 'สร้างโครงการใหม่' }} - AEG</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ isset($project) ? 'แก้ไขโครงการ: ' . $project->project_name : 'กรอกรายละเอียดโครงการใหม่' }}
        </h2>
    </div>

    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y box p-5">
            <form id="project-report-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->project_id }}">

                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">ความคืบหน้า (%)</label>
                        <input type="number" name="progress" class="form-control" min="0" max="100" value="{{ $project->progress }}" required>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">รายละเอียดการดำเนินงาน</label>
                        <textarea name="detail" class="form-control" rows="3" placeholder="ระบุสิ่งที่ทำวันนี้..."></textarea>
                    </div>
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">ปัญหาที่พบ (ถ้ามี)</label>
                        <textarea name="problem" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea name="note" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">แนบรูปภาพ (หลายรูป)</label>
                        <input type="file" name="image[]" class="form-control" multiple accept="image/*">
                    </div>
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">แนบเอกสาร (PDF/Doc)</label>
                        <input type="file" name="document[]" class="form-control" multiple accept=".pdf,.doc,.docx">
                    </div>
                </div>
                <div class="text-right mt-5">
                    <button type="button" onclick="submitProjectReport()" class="btn btn-primary w-32">ส่งรายงาน</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
<script src="https://code.jquery.com/jquery-3.7.1.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="//unpkg.com/alpinejs" defer></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "คลิกเพื่อเลือก...",
            allowClear: true
        });
    });

    function submitProjectReport() {
        let formData = new FormData($('#project-report-form')[0]);
        $.ajax({
            url: "{{ route('report.project.store') }}", // ตั้งชื่อ route ตาม controller storeProjectReport
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                alert('บันทึกรายงานสำเร็จ');
                location.reload();
            },
            error: function(err) {
                alert('เกิดข้อผิดพลาด: ' + err.responseJSON.message);
            }
        });
    }
</script>
@endsection
