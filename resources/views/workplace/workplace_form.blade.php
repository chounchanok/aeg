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
    <form method="post" action="{{ url('save_order_work') }}" autocomplete="off" enctype="multipart/form-data">
        <div class="grid grid-cols-12 gap-6 mt-5 mb-10">
            <div class="intro-y col-span-12 lg:col-span-6">
                @csrf
                <input type="hidden" name="order_id" value="{{ (!empty($order) ? $order->order_id : '') }}">
                <div class="intro-y box p-5">
                    <div class="grid grid-cols-12 gap-4 mt-4">
                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                            <label for="crud-form-1" class="form-label">ชื่อลูกค้า</label>
                            <input id="crud-form-1" type="text" class="form-control w-full" name="booking_name" value="{{ $customer->customer_firstname.' '.$customer->customer_lastname }}" readonly>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                            <label>สถานะใบงาน</label>
                            <div class="form-switch mt-2">
                                <select class="form form-control" name="order_status" disabled>
                                    <option {{ ($order->order_status == '1' ? 'selected' : '' ) }} value="1">{{ 'รอนัดหมาย' }}</option>
                                    <option {{ ($order->order_status == '2' ? 'selected' : '' ) }} value="2">{{ 'รอดำเนินการ' }}</option>
                                    <option {{ ($order->order_status == '3' ? 'selected' : '' ) }} value="3">{{ 'รอชำระเงิน' }}</option>
                                    <option {{ ($order->order_status == '4' ? 'selected' : '' ) }} value="4">{{ 'ดำเนินการเสร็จสิ้น' }}</option>
                                    <option {{ ($order->order_status == '5' ? 'selected' : '' ) }} value="5">{{ 'ยกเลิกออเดอร์' }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                            <label for="crud-form-1" class="form-label">ชื่อบริษัท</label>
                            <input type="text" class="form-control w-full" name="customer_company" id="customer_company" value="{{ (!empty($customer) ? $customer->customer_company : '') }}">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">รายละเอียดใบงาน</label>
                        @if(!empty($order_product))
                            @foreach($order_product as $_listproduct)
                            <div class="grid grid-cols-12 gap-4 mt-4">
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                                    <label for="crud-form-1" class="form-label">สินค้า</label>
                                    <select class="form-control" disabled>
                                        @foreach ($product as $_product)
                                        <option value="{{ $_product->product_id }}" ref="{{ $_product->product_price }}" {{ ($_listproduct->orderproduct_product == $_product->product_id ? 'selected' : '') }}>{{ $_product->product_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-2 2xl:col-span-2">
                                    <label for="crud-form-1" class="form-label">จำนวน</label>
                                    <input type="number" class="form-control w-full" value="{{ $_listproduct->orderproduct_qty }}" step="0.01" disabled>
                                </div>
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-2 2xl:col-span-2">
                                    <label for="crud-form-1" class="form-label">ราคาต่อชิ้น</label>
                                    <input type="number" class="form-control w-full" value="{{ ($_listproduct->orderproduct_price / ($_listproduct->orderproduct_qty > 0 ? $_listproduct->orderproduct_qty : 1)) }}" step="0.01" disabled>
                                </div>
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-2 2xl:col-span-2">
                                    <label for="crud-form-1" class="form-label">ราคา</label>
                                    <input type="number" class="form-control w-full product_price" value="{{ $_listproduct->orderproduct_price }}" step="0.01" disabled>
                                </div>
                            </div>
                            @endforeach
                        @endif
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12 mt-3">
                        <label>ภาพหน้างาน</label>
                        <div class="mt-2">
                        @if(!empty($order_image_front))
                            @foreach($order_image_front as $_front)
                                <a href="{{ Storage::url($_front->image_path) }}" data-lightbox="order-gallery">
                                    <img src="{{ Storage::url($_front->image_path) }}" alt="Order Image" width="150">
                                </a>
                            @endforeach

                        @endif
                        </div>
                    </div>
                    
                </div>
            </div>

            <div class="intro-y col-span-12 lg:col-span-6">
                <!-- BEGIN: Form Layout -->
                <div class="intro-y box p-5">
                <label for="crud-form-1" class="form-label" style="font-size: 18px; font-weight: bold;">ข้อมูลบริการเพิ่มเติม</label>
                    <div id="product-container">
                        @if(count($order_product_addon) > 0)
                            @foreach($order_product_addon as $_listproduct)
                            <div class="product-row grid grid-cols-12 gap-4 mt-4">
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                                    <label for="crud-form-1" class="form-label">สินค้า</label>
                                    <select class="form-control service_product select2" name="service_product[]" disabled>
                                        <option value="">- เลือกสินค้า -</option>
                                        @foreach ($product as $_product)
                                        <option value="{{ $_product->product_id }}" ref="{{ $_product->product_price }}" {{ ($_listproduct->orderproduct_product == $_product->product_id ? 'selected' : '') }}>{{ $_product->product_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-2 2xl:col-span-2">
                                    <label for="crud-form-1" class="form-label">จำนวน</label>
                                    <input type="number" class="form-control w-full" name="service_qty[]" onkeyup="summary_item()" value="{{ $_listproduct->orderproduct_qty }}" step="0.01" disabled>
                                </div>
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-2 2xl:col-span-2">
                                    <label for="crud-form-1" class="form-label">ราคาต่อชิ้น</label>
                                    <input type="number" class="form-control w-full" name="service_qty[]" onkeyup="summary_item()" value="{{ ($_listproduct->orderproduct_price / ($_listproduct->orderproduct_qty > 0 ? $_listproduct->orderproduct_qty : 1)) }}" step="0.01" disabled>
                                </div>
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-2 2xl:col-span-2">
                                    <label for="crud-form-1" class="form-label">รวม</label>
                                    <input type="number" class="form-control w-full product_price" onkeyup="summary()" name="service_price[]" value="{{ $_listproduct->orderproduct_price }}" step="0.01" disabled>
                                </div>
                            </div>
                            @endforeach
                        @else
                        <div class="product-row grid grid-cols-12 gap-4 mt-4">
                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                                <label for="crud-form-1" class="form-label">สินค้า</label>
                                <select class="form-control service_product select2" name="service_product[]">
                                    <option value="">- เลือกสินค้า -</option>
                                    @foreach ($product as $_product)
                                    <option value="{{ $_product->product_id }}" ref="{{ $_product->product_price }}">{{ $_product->product_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-4 2xl:col-span-4">
                                <label for="crud-form-1" class="form-label">จำนวน</label>
                                <input type="number" class="form-control w-full" name="service_qty[]" onkeyup="summary_item()" step="0.01">
                            </div>
                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-4 2xl:col-span-4">
                                <label for="crud-form-1" class="form-label">ราคาต่อชิ้น</label>
                                <input type="number" class="form-control w-full" name="service_price_qty[]" onkeyup="summary_item()" step="0.01">
                            </div>
                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-4 2xl:col-span-4">
                                <label for="crud-form-1" class="form-label">รวม</label>
                                <input type="number" class="form-control w-full product_price" onkeyup="summary()" name="service_price[]" step="0.01">
                            </div>
                        </div>

                        <div id="product-template" class="product-row grid grid-cols-12 gap-4 mt-4 hidden">
                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                                <label for="crud-form-1" class="form-label">สินค้า</label>
                                <select class="form-control service_product" name="service_product[]">
                                    <option value="">- เลือกสินค้า -</option>
                                    @foreach ($product as $_product)
                                    <option value="{{ $_product->product_id }}" ref="{{ $_product->product_price }}">{{ $_product->product_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-4 2xl:col-span-4">
                                <label for="crud-form-1" class="form-label">จำนวน</label>
                                <input type="number" class="form-control w-full" name="service_qty[]" onkeyup="summary_item()" step="0.01">
                            </div>
                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-4 2xl:col-span-4">
                                <label for="crud-form-1" class="form-label">ราคาต่อชิ้น</label>
                                <input type="number" class="form-control w-full" name="service_price_qty[]" onkeyup="summary_item()" step="0.01">
                            </div>
                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-4 2xl:col-span-4">
                                <label for="crud-form-1" class="form-label">รวม</label>
                                <input type="number" class="form-control w-full product_price" onkeyup="summary()" name="service_price[]" step="0.01">
                            </div>
                        </div>

                        <div id="product-container">
                        </div>
                        @endif
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12 mt-4 text-right">
                        <button type="button" class="btn btn-success" onclick="add_product()">+ เพิ่มรายการสินค้า</button>
                    </div>

                    <div class="grid grid-cols-12 gap-2 mt-4">

                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                            <label>รวม</label>
                            <div class="mt-2">
                                <input type="number" class="form-control text-right w-full sumprice" name="order_sumprice" value="{{ (!empty($order) ? $order->order_sumprice : '0.00' ) }}" onkeyup="summary()" placeholder="" step="0.05">
                            </div>
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                            <label>ส่วนลด</label>
                            <div class="mt-2">
                                <input type="number" class="form-control text-right w-full discount" name="order_discount" value="{{ (!empty($order) ? $order->order_discount : '0.00' ) }}" onkeyup="summary()" placeholder="" step="0.05">
                            </div>
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                            <label>คงเหลือ</label>
                            <div class="mt-2">
                                <input type="number" class="form-control text-right w-full balance" name="order_balance" value="{{ (!empty($order) ? $order->order_balance : '0.00' ) }}" onkeyup="summary()" placeholder="" step="0.05">
                            </div>
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                            <label>มัดจำ</label>
                            <div class="mt-2">
                                <input type="number" class="form-control text-right w-full deposit" name="order_deposit" value="{{ (!empty($order) ? $order->order_deposit : '0.00' ) }}" onkeyup="summary()" placeholder="" step="0.05">
                            </div>
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                            <label>ยอดชำระจริง</label>
                            <div class="mt-2">
                                <input type="number" class="form-control text-right w-full total" name="order_total" value="{{ (!empty($order) ? $order->order_total : '0.00' ) }}" onkeyup="summary()" placeholder="" step="0.05">
                            </div>
                        </div>
                    </div>
                </div>
            <!-- END: Form Layout -->
            </div>

            <div class="intro-y col-span-12 lg:col-span-6">
                <!-- BEGIN: Form Layout -->
                <div class="intro-y box p-5">
                <label for="crud-form-1" class="form-label" style="font-size: 18px; font-weight: bold;">หลักฐานการดำเนินการทั้งหมด</label>

                    <div class="grid grid-cols-12 gap-2 mt-4">

                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12 mt-3">
                            <label>กรุณาแนบหลักฐานการปฏิบัตกงาน ทั้งที่อยู่ในรายการและที่เพิ่มเติม อุปกรณ์ที่ลูกค้าจำเป็นต้องชำระเพิ่ม และบริการอื่นๆ โดยการถ่ายภาพและเขียนบันทึกลงในช่องรายละเอียด</label>
                            <div class="mt-2">
                                <input type="file" class="form-control text-right w-full mt-3" name="order_work_image[]" value="" multiple required accept="image/*">
                            </div>
                        </div>

                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12 mt-3">
                            <label>รายละเอียด</label>
                            <div class="mt-2">
                                <textarea class="editor" name="order_comment"><?php echo html_entity_decode(!empty($order) ? $order->order_comment : ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-5">
                        <button type="reset" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                        <button type="submit" class="btn btn-primary w-24">ถัดไป</button>
                    </div>
                </div>
            <!-- END: Form Layout -->
            </div>
        </div>
    </form>
@endsection


@section('script')
    <script src="{{ mix('dist/js/ckeditor-classic.js') }}"></script>
        <script type="text/javascript">
            
        function formatAllInputs() {
            $('input[type="number"]').each(function () {
                // รับค่า input และแปลงเป็น float
                var value = parseFloat($(this).val()) || 0;

                // อัปเดตค่าใน input (ฟอร์แมตทศนิยม 2 ตำแหน่ง)
                $(this).val(value.toFixed(2));
            });
        }

        // เรียกใช้ตอนโหลดหน้า
        $(document).ready(function () {
            formatAllInputs();
            $('.select2').select2();
        });

        function collectProductsData() {
            const productRows = document.querySelectorAll('#product-container .product-row');
            const products = [];
            const quantities = [];
            const prices = [];

            productRows.forEach(row => {
                const productSelect = row.querySelector('select[name="service_product[]"]');
                const quantityInput = row.querySelector('input[name="service_qty[]"]');
                const priceInput = row.querySelector('input[name="service_price[]"]');

                if (productSelect && quantityInput && priceInput) {
                    products.push(productSelect.value);
                    quantities.push(quantityInput.value);
                    prices.push(priceInput.value);
                }
            });

            return {
                service_product: products,
                service_qty: quantities,
                service_price: prices
            };
        }

        $('#save_task').on('submit', function (e) {
            e.preventDefault(); // ป้องกันการ submit ฟอร์มแบบปกติ

            // สร้าง FormData และรวมข้อมูลจากฟอร์มทั้งหมด
            const formData = new FormData(this);

            // เพิ่มข้อมูลที่รวบรวมด้วย JavaScript
            const productData = collectProductsData();
            productData.service_product.forEach((product, index) => {
                formData.append(`service_product[${index}]`, product);
            });
            productData.service_qty.forEach((qty, index) => {
                formData.append(`service_qty[${index}]`, qty);
            });
            productData.service_price.forEach((price, index) => {
                formData.append(`service_price[${index}]`, price);
            });

            $.ajax({
                url: '{{ url("save_task_order") }}', // URL สำหรับส่งข้อมูล (แก้ไขตามจริง)
                type: 'POST',
                data: formData,
                contentType: false, // ต้องใช้ false เพราะส่งเป็น FormData
                processData: false, // ปิดการแปลงข้อมูล (FormData ไม่ต้องแปลง)
                success: function (response) {
                    // ดำเนินการเมื่อสำเร็จ
                    alert('บันทึกข้อมูลสำเร็จ');
                    console.log(response);
                    if($('input[name="order_id"]').val()){
                        window.location.reload();
                    }else{
                        window.location.href = '{{ url("task_manager_list") }}';
                    }
                },
                error: function (error) {
                    // ดำเนินการเมื่อมีข้อผิดพลาด
                    alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                    console.error(error);
                },
            });
        });


        function checktax(){
            if($('.checktax').is(':checked')){
                $('.tax_detail').css('display','block');
            }else{
                $('.tax_detail').css('display','none');
            }
        }

        function same_address(){
            if($('.same_address').is(':checked')){
                var customer_address = $('#customer_address').val();
                $('#customer_setup_address').val(customer_address);
            }else{
                $('#customer_setup_address').val(null);
            }
        }

        function same_address_bill(){
            if($('.same_address_bill').is(':checked')){
                var customer_name = $('#customer_firstname').val()+' '+$('#customer_lastname').val();
                var customer_tel = $('#customer_tel').val();
                var customer_address = $('#customer_address').val();
                $('#order_taxname').val(customer_name);
                $('#order_taxtel').val(customer_tel);
                $('#order_taxaddress').val(customer_address);
            }else{
                $('#order_taxname').val(null);
                $('#order_taxtel').val(null);
                $('#order_taxaddress').val(null);
            }
        }

        function summary_item() {
            // ใช้กับทุกแถว product-row
            $('.product-row').each(function () {
                var row = $(this); // แถวปัจจุบัน

                var qty = parseFloat(row.find('input[name="service_qty[]"]').val()) || 0;
                var price_per_unit = parseFloat(row.find('input[name="service_price_qty[]"]').val()) || 0;

                var total = qty * price_per_unit;

                row.find('input[name="service_price[]"]').val(total.toFixed(2));
            });
            summary();
        }

        function cusno(){
            var cusno = $('.cusno').val();
            var fillcusno = 'S'+cusno;
            $('.fillcusno').val(fillcusno);
            $.ajax({
                'dataType': 'json',
                'type': 'post',
                'url': "{{url('check_customer')}}",
                'data': {
                    'customer_code' : fillcusno,
                    '_token': "{{ csrf_token() }}"
                },
                'success': function (data) {
                    console.log(data);
                    if(data){
                        $('#customer_firstname').val(data.customer_firstname);
                        $('#customer_lastname').val(data.customer_lastname);
                        $('#customer_tel2').val(data.customer_tel2);
                        $('#customer_address').val(data.customer_address);
                        $('#customer_setup_address').val(data.customer_setup_address);
                        $('#customer_googlemap').val(data.customer_googlemap);
                    }else{
                        $('#customer_firstname').val(null);
                        $('#customer_lastname').val(null);
                        $('#customer_tel2').val(null);
                        $('#customer_address').val(null);
                        $('#customer_setup_address').val(null);
                        $('#customer_googlemap').val(null);
                        
                    }
                }
            });
        }

        function summary(){
            var sumprice = 0;
            $('.product_price').each(function(){
                sumprice =  parseFloat(sumprice) + parseFloat($(this).val());
            });
            $('.sumprice').val(sumprice.toFixed(2));
            var discount = $('.discount').val();
            var balance = parseFloat(sumprice) - parseFloat(discount);
            $('.balance').val(balance.toFixed(2));
            var deposit = $('.deposit').val();
            var total = parseFloat(balance) - parseFloat(deposit);
            $('.total').val(total.toFixed(2));
        }

        function add_product() {
            var clonedRow = $('#product-template').clone().removeAttr('id').removeClass('hidden');
            $('#product-container').append(clonedRow);
            clonedRow.find('select').select2();
        }

        // Event listener สำหรับเปลี่ยน select และคำนวณราคา
        $(document).on('change', '.service_product', function () {
            var row = $(this).closest('.product-row'); // ค้นหาแถวที่เกี่ยวข้อง
            var product_price = parseFloat($(this).find(':selected').attr('ref')) || 0; // ดึงค่า ref (ราคา)
            console.log('Price : '+product_price);
            var qty = parseFloat(row.find('input[name="service_qty[]"]').val()) || 0; // ดึงจำนวน (qty)
            console.log('Qty : '+qty);
            var total_price = product_price * qty; // คำนวณราคา
            console.log('Total : '+total_price);

            // ใส่ผลลัพธ์ลงใน input[name="service_price[]"]
            row.find('input[name="service_price[]"]').val(total_price.toFixed(2));
            summary();
        });

        // Event listener สำหรับเปลี่ยนจำนวน (qty) และคำนวณราคาใหม่
        $(document).on('input', 'input[name="service_qty[]"]', function () {
            var row = $(this).closest('.product-row'); // ค้นหาแถวที่เกี่ยวข้อง
            console.log('Row:', row);

            // ดึงค่า `value` ของ select ที่เลือกในแถวปัจจุบัน
            var selectedOption = row.find('.service_product option:selected'); // ดึง <option> ที่เลือก
            var product_price = parseFloat(selectedOption.attr('ref')) || 0; // ดึง ref (ราคา)
            console.log('Selected Product Price:', product_price);

            // ดึงค่าจำนวน (qty)
            var qty = parseFloat($(this).val()) || 0; // ดึงค่าจาก input
            console.log('Qty:', qty);

            // คำนวณราคา
            var total_price = product_price * qty;
            console.log('Total:', total_price);

            // อัปเดตราคาผลลัพธ์
            row.find('input[name="service_price[]"]').val(total_price.toFixed(2));

            // เรียกใช้ summary()
            summary();
        });



            
    </script>
@endsection
