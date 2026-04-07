@extends('../layout/' . $layout)

@section('subhead')
    <title>เพิ่มรายงานความคืบหน้า - AEG</title>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            เพิ่มรายงานความคืบหน้าสำหรับสินทรัพย์: {{ $asset->asset_name }}
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12">
            <form id="asset-report-form" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="asset_id" value="{{ $asset->asset_id }}">

                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12">
                        <label class="form-label">ปัญหาที่พบ/อาการที่แจ้ง</label>
                        <textarea name="problem" class="form-control" rows="3" required placeholder="ระบุอาการเสียหรือปัญหา..."></textarea>
                    </div>
                    <div class="col-span-12">
                        <label class="form-label">รายละเอียดการแก้ไข</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="ระบุวิธีแก้ไข (ถ้ามี)..."></textarea>
                    </div>
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">แนบรูปภาพประกอบ</label>
                        <input type="file" name="image[]" class="form-control" multiple accept="image/*">
                    </div>
                    <div class="col-span-12 md:col-span-6">
                        <label class="form-label">เอกสารอ้างอิง (ใบเสร็จ/ใบส่งของ)</label>
                        <input type="file" name="document[]" class="form-control" multiple accept=".pdf,.jpg,.png">
                    </div>
                </div>
                <div class="text-right mt-5">
                    <button type="button" onclick="submitAssetReport()" class="btn btn-warning w-32">บันทึกรายงานซ่อม</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@once
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                $('.select2-multiple').select2({
                    placeholder: "เลือกรายชื่อ",
                    allowClear: true
                });
            });
        </script>

        {{-- Alpine JS ของเดิม --}}
        <script src="//unpkg.com/alpinejs" defer></script>
    @endpush
@endonce

@section('script')
<script>
    function submitAssetReport() {
        let form = $('#asset-report-form')[0];
        let formData = new FormData(form);

        // แสดง Loading (ถ้ามี)
        $('#btn-submit-report').addClass('loading').attr('disabled', true);

        $.ajax({
            url: "{{ route('report.asset.store') }}",
            type: 'POST',
            data: formData,
            processData: false, // จำเป็นสำหรับไฟล์
            contentType: false, // จำเป็นสำหรับไฟล์
            success: function(response) {
                alert('บันทึกรายงานสำเร็จ');
                location.reload(); // โหลดหน้าใหม่เพื่อแสดงรายการล่าสุด
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let errorMessage = xhr.responseJSON.message;
                alert('เกิดข้อผิดพลาด: ' + errorMessage);
                console.log(errors);
            },
            complete: function() {
                $('#btn-submit-report').removeClass('loading').attr('disabled', false);
            }
        });
    }
</script>
@endsection
