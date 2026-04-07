@extends('../layout/' . $layout)

@section('subhead')
    @if($order->order_status != 1)
    <title>เข้าปฏิบัติงาน - AMR AIR</title>
    @else
    <title>นัดหมายใหม่ - AMR AIR</title>
    @endif
    <link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .gj-icon {
            margin-top: 7px; 
            margin-right: 5px;
        }
    </style>
@endsection

@section('subcontent')
    <div class="intro-y flex items-center mt-8">
    @if($order->order_status != 1)
        <h2 class="text-lg font-medium mr-auto">เข้าปฏิบัติงาน</h2>
    @else
        <h2 class="text-lg font-medium mr-auto">สร้างนัดหมายใหม่</h2>
    @endif
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-6">
            <!-- BEGIN: Form Layout -->
            <form method="post" id="signature-form" action="{{ url('save_order_sign') }}" autocomplete="off">
                @csrf
                <div class="intro-y col-span-12 lg:col-span-6">

                    <div class="intro-y box p-5">
                        <label for="crud-form-1" class="form-label" style="font-size: 18px; font-weight: bold;">คะแนนความพึงพอใจในการให้บริการ</label>
                            <option value="0"></option>
                        <select class="form-control" name="order_sign_point">
                            <option value="1">น้อยมาก</option>
                            <option value="2">น้อย</option>
                            <option value="3">ปานกลาง</option>
                            <option value="4">มาก</option>
                            <option value="5">มากที่สุด</option>
                        </select>
                    </div>

                    <div class="intro-y box p-5">
                    <label for="crud-form-1" class="form-label" style="font-size: 18px; font-weight: bold;">ลายเซ็นต์ลูกค้า</label>
                        <input type="hidden" name="order_customer" value="<?php echo (!empty($order) ? $order->order_customer : ''); ?>">
                        <input type="hidden" name="order_id" value="<?php echo (!empty($order) ? $order->order_id : ''); ?>">
                        <input type="hidden" name="work_id" value="<?php echo (!empty($work_order) ? $work_order->work_id : ''); ?>">
                        <div class="grid grid-cols-12 gap-2 mt-4">
                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">

                                <canvas id="signature-pad" width="550" style="touch-action: none; user-select: none;" height="282"></canvas>

                                <button type="button" id="clear-signature" class="btn btn-warning">ล้างลายเซ็นต์</button>
                                <button type="button" class="btn btn-success" id="save-signature">บันทึกลายเซ็นต์</button>
                                <input type="hidden" name="signature" id="signature-input">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection


@section('script')
    <script src="{{ mix('dist/js/ckeditor-classic.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
        <script type="text/javascript">

        const canvas = document.getElementById('signature-pad');
        const signaturePad = new SignaturePad(canvas);

        document.getElementById('clear-signature').addEventListener('click', function () {
            signaturePad.clear();
        });

        document.getElementById('save-signature').addEventListener('click', function () {
            if (!signaturePad.isEmpty()) {
                document.getElementById('signature-input').value = signaturePad.toDataURL('image/png');
                document.getElementById('signature-form').submit();
            } else {
                alert('กรุณาเซ็นก่อนบันทึก');
            }
        });
        
    </script>
@endsection
