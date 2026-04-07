@extends('../layout/' . $layout)

@section('subhead')
    <title>ลิสท์งานทั้งหมด - Build Smooth</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">ลิสท์งานทั้งหมด</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <!-- BEGIN: Data List -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-form datatable -mt-2" style="background-color: white;">
                <thead>
                    <tr>
                        <th class="text-center" >#</th>
                        <th class="whitespace-nowrap">ชื่อลูกค้า</th>
                        <th class="text-center whitespace-nowrap">เบอร์ติดต่อ</th>
                        <th class="text-center whitespace-nowrap">วันที่นัดหมาย</th>
                        <th class="text-center whitespace-nowrap">สถานะ</th>
                        <th class="text-center whitespace-nowrap">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; $show_status = ''; $show_color = ''; //dd($order); ?>
                    @foreach ($order as $_order)
                        @if($_order->order_status == 1)
                            <?php $show_color = 'text-warning'; $show_status = 'รอนัดหมาย'; ?>
                        @elseif($_order->order_status == 2)
                            <?php $show_color = 'text-warning'; $show_status = 'รอดำเนินการ'; ?>
                        @elseif($_order->order_status == 3)
                            <?php $show_color = 'text-warning'; $show_status = 'รอชำระเงิน'; ?>
                        @elseif($_order->order_status == 4)
                            <?php $show_color = 'text-success'; $show_status = 'ดำเนินการเสร็จสิ้น'; ?>
                        @elseif($_order->order_status == 5)
                            <?php $show_color = 'text-danger'; $show_status = 'ยกเลิกออเดอร์'; ?>
                        @endif
                        <?php 
                            $_work_order = DB::Table('employee_work')->where('work_order',$_order->order_id)->first();
                        ?>
                        <tr class="intro-x">
                            <td class="text-center">{{ $i }}</td>
                            <td>
                                <a href="" class="font-medium whitespace-nowrap">{{ (!empty($_order->customer_company) ? $_order->customer_company : $_order->customer_firstname.' '.$_order->customer_lastname) }}</a>
                            </td>
                            <td class="text-center">{{ $_order->customer_tel }}</td>
                            <td class="text-center">{{ (!empty($_work_order->work_date) ? date('d/m/Y', strtotime($_work_order->work_date)).' '.$_work_order->work_timestart.' ถึง '.$_work_order->work_timeend : '-') }}</td>
                            <td class="w-40">
                                <div class="flex items-center justify-center {{ $show_color }}">
                                    <i data-lucide="check-square" class="w-4 h-4 mr-2"></i> {{ $show_status }}
                                </div>
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <a href="{{ url('workplace_form_edit').'/'.$_order->order_id }}" class="flex items-center mr-3 <?php echo ($_order->order_status == 1 ? 'text-success' : 'text-info' ); ?> " href="javascript:;">
                                        <i data-lucide="check-square" class="w-4 h-4 mr-1"></i> {{ ($_order->order_status == 1 ? 'ทำนัดหมาย' : 'ตรวจสอบงาน') }}
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php $i++; ?>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- END: Data List -->
    </div>
    <!-- BEGIN: Delete Confirmation Modal -->
    <div id="delete-confirmation-modal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5">Are you sure?</div>
                        <div class="text-slate-500 mt-2">Do you really want to delete these records? <br>This process cannot be undone.</div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Cancel</button>
                        <button type="button" class="btn btn-danger w-24">Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirmation Modal -->
@endsection

@section('script')
@endsection
<script src="https://code.jquery.com/jquery-1.12.4.js" integrity="sha256-Qw82+bXyGq6MydymqBxNPYTaUXXq7c8v3CwiYwLLNXU=" crossorigin="anonymous"></script>
<script src="{{ asset('dist/js/datatables.min.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function(){
        new DataTable('.datatable');
    });
</script>
