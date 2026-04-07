@extends('../layout/' . $layout)

@section('subhead')
    <title>ใบงานทั้งหมด - Build Smooth</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('dist/css/datatables.min.css') }}">
@endsection

@section('subcontent')
    <h2 class="intro-y text-lg font-medium mt-10">ใบงานทั้งหมด</h2>
    <div class="grid grid-cols-12 gap-6 mt-5">

        <form method="POST" action="{{ url('task_manager_list') }}" class="intro-y col-span-12 flex flex-wrap sm:flex-nowrap items-center mt-2">
            @csrf
            <select class="form-control col-span-5 sm:col-span-5 md:col-span-5 2xl:col-span-5" name="status">
                <option {{ ($status == '0' ? 'selected' : '') }} value="0">ทั้งหมด</option>
                <option {{ ($status == '1' ? 'selected' : '') }} value="1">รอนัดหมาย</option>
                <option {{ ($status == '2' ? 'selected' : '') }} value="2">รอดำเนินการ</option>
                <option {{ ($status == '3' ? 'selected' : '') }} value="3">รอชำระเงิน</option>
                <option {{ ($status == '4' ? 'selected' : '') }} value="4">ดำเนินการเสร็จสิ้น</option>
                <option {{ ($status == '5' ? 'selected' : '') }} value="5">ยกเลิกออเดอร์</option>
            </select>
            <button type="submit" class="btn btn-success col-span-1 sm:col-span-1 md:col-span-1 2xl:col-span-1">ค้นหา</button>

            <div class="dropdown col-span-1 sm:col-span-1 md:col-span-1 2xl:col-span-1">
                <button type="button" class="dropdown-toggle btn px-2 box" aria-expanded="false" data-tw-toggle="dropdown">
                    <span class="w-5 h-5 flex items-center justify-center">
                        <i class="w-4 h-4" data-lucide="plus"></i>
                    </span>
                </button>
                <div class="dropdown-menu w-40">
                    <ul class="dropdown-content">
                        <li>
                            <a href="" class="dropdown-item">
                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export to Excel
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </form>

        <a href="{{ url('task_manager_form') }}"><button class="btn btn-primary shadow-md mr-2" style="width: 110px;">เพิ่มใบงานใหม่</button></a>

        <!-- BEGIN: Data List -->
        <div class="intro-y col-span-12 overflow-auto lg:overflow-visible">
            <table class="table table-form datatable -mt-2" style="background-color: white;">
                <thead>
                    <tr>
                        <th class="text-center" >#</th>
                        <th >ประเภท</th>
                        <th >เลขที่ออเดอร์</th>
                        <th >ชื่อลูกค้า</th>
                        <th >ติดต่อ</th>
                        <th >ยอดเงิน</th>
                        <th >สถานะ</th>
                        <th >ผู้ทำ</th>
                        <th >จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $i = 1; 
                        $show_color = ''; 
                        $show_status[1] = 'รอนัดหมาย';
                        $show_status[2] = 'รอดำเนินการ';
                        $show_status[3] = 'รอชำระเงิน';
                        $show_status[4] = 'ดำเนินการเสร็จสิ้น';
                        $show_status[5] = 'ยกเลิกออเดอร์';
                    ?>
                    @foreach ($order as $_order)
                        @if($_order->order_status == 1)
                            <?php $show_color = 'text-warning'; ?>
                        @elseif($_order->order_status == 2)
                            <?php $show_color = 'text-warning'; ?>
                        @elseif($_order->order_status == 3)
                            <?php $show_color = 'text-warning'; ?>
                        @elseif($_order->order_status == 4)
                            <?php $show_color = 'text-success'; ?>
                        @elseif($_order->order_status == 5)
                            <?php $show_color = 'text-danger'; ?>
                        @endif
                        <?php 
                            $employee = DB::table('employee')->where('employee_id',$_order->order_employee)->first();
                        ?>
                        <tr class="intro-x">
                            <td class="text-center">{{ $i }}</td>
                            <td class="text-center">{{ $_order->order_type }}</td>
                            <td class="text-center">{{ $_order->order_no }}</td>
                            <td>{{ (!empty($_order->customer_company) ? $_order->customer_company : $_order->customer_firstname.' '.$_order->customer_lastname) }}</td>
                            <td>{{ $_order->customer_tel }}</td>
                            <td class="text-center">{{ number_format($_order->order_total,2,'.',',') }}</td>
                            <td class="w-40">
                                <div class="flex items-center justify-center {{ $show_color }}">
                                    <select class="form form-control change_status" ref="{{ $_order->order_id }}">
                                        <option {{ ($_order->order_status == '1' ? 'selected' : '' ) }} value="1">{{ 'รอนัดหมาย' }}</option>
                                        <option {{ ($_order->order_status == '2' ? 'selected' : '' ) }} value="2">{{ 'รอดำเนินการ' }}</option>
                                        <option {{ ($_order->order_status == '3' ? 'selected' : '' ) }} value="3">{{ 'รอชำระเงิน' }}</option>
                                        <option {{ ($_order->order_status == '4' ? 'selected' : '' ) }} value="4">{{ 'ดำเนินการเสร็จสิ้น' }}</option>
                                        <option {{ ($_order->order_status == '5' ? 'selected' : '' ) }} value="5">{{ 'ยกเลิกออเดอร์' }}</option>
                                    </select>
                                </div>
                            </td>
                            <td>{{ (!empty($employee) ? $employee->employee_name : '-') }}</td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <a class="flex items-center mr-3 text-warning" href="{{ url('task_manager_edit') }}/{{ $_order->order_id }}">
                                        <i data-lucide="check-square" class="w-4 h-4 mr-1 "></i> แก้ไขรายการ
                                    </a>
                                    <a class="flex items-center text-danger" href="javascript:;" data-tw-toggle="modal" ref="{{ $_order->order_id }}">
                                        <i data-lucide="trash-2" class="w-4 h-4 mr-1"></i> ยกเลิกรายการ
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
@endsection
@section('script')
<script type="text/javascript">
    $(document).ready(function () {
        // Initialize DataTable
        new DataTable('.datatable');

        // Event delegation for dynamically generated elements
        // ใช้ SweetAlert2 Confirm ก่อนยิง AJAX
        $(document).on('change', '.change_status', function () {
            var order_id = $(this).attr('ref'); // รับค่า order_id
            var order_status = $(this).val(); // รับค่า order_status

            console.log(order_id); // Debug ค่า order_id
            console.log(order_status); // Debug ค่า order_status

            // แสดง SweetAlert Confirm Dialog
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to change the order status?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // ถ้าผู้ใช้กดยืนยัน (Yes, change it!)
                    $.ajax({
                        url: '{{ url("change_status_order") }}',
                        data: {
                            'order_id': order_id,
                            'order_status': order_status,
                            '_token': "{{ csrf_token() }}"
                        },
                        type: 'POST',
                        success: function (response) {
                            // แสดงข้อความสำเร็จด้วย SweetAlert
                            Swal.fire(
                                'Success!',
                                'Order status has been changed.',
                                'success'
                            ).then(() => {
                                location.reload(); // Reload หน้าเมื่อผู้ใช้ปิด SweetAlert
                            });
                        },
                        error: function (xhr, status, error) {
                            // แสดงข้อความผิดพลาดด้วย SweetAlert
                            Swal.fire(
                                'Error!',
                                'An error occurred while changing the order status.',
                                'error'
                            );
                        }
                    });
                } else {
                    // ถ้าผู้ใช้กด Cancel
                    console.log('User canceled the status change');
                }
            });
        });

        // Delete logic
        $(document).on('click', 'a[data-tw-toggle="modal"]', function () {
            var orderId = $(this).attr('ref');
            console.log(orderId);

            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to delete this order?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {

                    $.ajax({
                        url: '{{ url("delete_task_order") }}/' + orderId,
                        type: 'GET',
                        success: function (response) {
                            alert('Order deleted successfully!');
                            $('#delete-confirmation-modal').modal('hide');
                            location.reload();
                        },
                        error: function (xhr, status, error) {
                            alert('Error occurred while deleting the order.');
                        }
                    });
                } else {
                    // ถ้าผู้ใช้กด Cancel
                    console.log('User canceled the status change');
                }
            });
        });
    });
</script>
@endsection
