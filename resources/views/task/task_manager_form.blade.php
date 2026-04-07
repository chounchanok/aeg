@extends('../layout/' . $layout)

@section('subhead')
    <title>ใบงานใหม่ - AMR AIR</title>
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
        <h2 class="text-lg font-medium mr-auto">สร้างใบงานใหม่</h2>
    </div>
    <form method="post" id="save_task" enctype="multipart/form-data">
    <div class="grid grid-cols-12 gap-6 mt-5">
    
        <div class="intro-y col-span-12 lg:col-span-6">
            @csrf
            <!-- BEGIN: Form Layout -->
            <input type="hidden" name="order_id" class="order_id" value="{{ (!empty($order) ? $order->order_id : '') }}">
            <div class="intro-y box p-5">
            <label for="crud-form-1" class="form-label" style="font-size: 18px; font-weight: bold;">ข้อมูลลูกค้า</label>
                <div class="grid grid-cols-12 gap-2 mt-4">
                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                        <label>รหัสลูกค้า</label><label style="float: right;"> ลูกค้ารายปี&nbsp;&nbsp;<input type="checkbox" name="customer_annual" {{ (!empty($customer) ? ($customer->customer_annual == 'T' ? 'checked' : '') : '') }} value="T"></label>
                        <div class="mt-2">
                        <input type="text" class="form-control w-full fillcusno" name="customer_code" id="customer_code" value="{{ (!empty($customer) ? $customer->customer_code : '') }}" required>
                        </div>
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label for="crud-form-1" class="form-label">ชื่อจริง</label>
                        <input type="text" class="form-control w-full" name="customer_firstname" id="customer_firstname" value="{{ (!empty($customer) ? $customer->customer_firstname : '') }}" required>
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label for="crud-form-1" class="form-label">นามสกุล</label>
                        <input type="text" class="form-control w-full" name="customer_lastname" id="customer_lastname" value="{{ (!empty($customer) ? $customer->customer_lastname : '') }}">
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                        <label for="crud-form-1" class="form-label">ชื่อบริษัท</label>
                        <input type="text" class="form-control w-full" name="customer_company" id="customer_company" value="{{ (!empty($customer) ? $customer->customer_company : '') }}">
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label for="crud-form-1" class="form-label">เบอร์ติดต่อ</label>
                        <input type="text" class="form-control w-full cusno" name="customer_tel" id="customer_tel" onkeyup="cusno()" value="{{ (!empty($customer) ? $customer->customer_tel : '') }}" maxlength="10" required>
                        <font color="red" size="1">* สามารถใส่เบอร์เพื่อค้นหาลูกค้าได้</font>
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label for="crud-form-1" class="form-label">เบอร์ติดต่อ (สำรอง)</label>
                        <input type="text" class="form-control w-full" name="customer_tel2" id="customer_tel2" value="{{ (!empty($customer) ? $customer->customer_tel2 : '') }}" maxlength="10">
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                        <label>ที่อยู่ลูกค้า</label>
                        <div class="mt-2">
                            <textarea class="form-control" rows="5" id="customer_address" name="customer_address" required>{{ (!empty($customer) ? $customer->customer_address : '') }}</textarea>
                        </div>
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                        <label>ที่อยู่ในการติดตั้ง &nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="checkbox" class="form-check-input same_address" onclick="same_address()" value="Y">
                                สถานที่เดียวกันกับที่อยู่ </label>
                        <div class="mt-2">
                            <textarea class="form-control" id="customer_setup_address" name="customer_setup_address" id="customer_setup_address" rows="5">{{ (!empty($customer) ? $customer->customer_setup_address : '') }}</textarea>
                        </div>
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                        <label for="crud-form-1" class="form-label">ลิงก์ที่อยู่ในการติดตั้งจาก Google Map</label>
                        <input type="text" class="form-control w-full" id="customer_googlemap" name="customer_googlemap" placeholder="" value="{{ (!empty($customer) ? $customer->customer_googlemap : '') }}">
                    </div>


                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label for="crud-form-1" class="form-label">พนักงานที่ทำรายการ</label>
                        <?php  
                            if(!empty($order)){
                                $employee_task = DB::Table('employee')->where('employee_id',$order->order_employee)->first();
                                if(!empty($employee_task)){
                                    $employee_name = $employee_task->employee_name;
                                }
                            }else{
                                $employee_task = DB::Table('employee')->where('employee_id',Auth::user()->employee_id)->first();
                                if(!empty($employee_task)){
                                    $employee_name = $employee_task->employee_name;
                                }

                            }
                        ?>
                        <input type="text" class="form-control w-full" readonly value="{{ (!empty($employee_name) ? $employee_name : '') }}">
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label for="crud-form-1" class="form-label">เบอร์ติดต่อ</label>
                        <input type="text" class="form-control w-full" name="order_employee_tel" id="order_employee_tel" value="{{ (!empty($order) ? $order->order_employee_tel : '') }}" maxlength="10">
                    </div>
                </div>
            </div>
            <!-- END: Form Layout -->
        </div>

        <div class="intro-y col-span-12 lg:col-span-6">
                <!-- BEGIN: Form Layout -->
                <div class="intro-y box p-5">
                <label for="crud-form-1" class="form-label" style="font-size: 18px; font-weight: bold;">ข้อมูลบริการ</label>

                    <div id="product-container">
                        @if(!empty($order_product))
                            @foreach($order_product as $_listproduct)
                            <div class="product-row grid grid-cols-12 gap-4 mt-4">
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                                    <label for="crud-form-1" class="form-label">สินค้า</label>
                                    <button type="button" for="crud-form-1" class="btn btn-sm btn-danger" onclick="remove_line(this)" style="float: right; padding: 4px 6px 4px 6px;">X</button>
                                    <select class="form-control service_product select2" name="service_product[]">
                                        @foreach ($product as $_product)
                                        <option value="{{ $_product->product_id }}" ref="{{ $_product->product_price }}" {{ ($_listproduct->orderproduct_product == $_product->product_id ? 'selected' : '') }}>{{ $_product->product_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-4 2xl:col-span-4">
                                    <label for="crud-form-1" class="form-label">จำนวน</label>
                                    <input type="number" class="form-control w-full" name="service_qty[]" onkeyup="summary_item()" value="{{ round($_listproduct->orderproduct_qty,2) }}" step="0.01">
                                </div>
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-4 2xl:col-span-4">
                                    <label for="crud-form-1" class="form-label">ราคาต่อชิ้น</label>
                                    <input type="number" class="form-control w-full" name="service_price_qty[]" onkeyup="summary_item()" value="{{ ($_listproduct->orderproduct_price / ($_listproduct->orderproduct_qty > 0 ? $_listproduct->orderproduct_qty : 1)) }}" step="0.01">
                                </div>
                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-4 2xl:col-span-4">
                                    <label for="crud-form-1" class="form-label">ราคา</label>
                                    <input type="number" class="form-control w-full product_price" onkeyup="summary()" name="service_price[]" value="{{ round($_listproduct->orderproduct_price,2) }}" step="0.01">
                                </div>
                            </div>
                            @endforeach
                        @else
                        <div class="product-row grid grid-cols-12 gap-4 mt-4">
                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                                <label for="crud-form-1" class="form-label">สินค้า</label>
                                <button type="button" for="crud-form-1" class="btn btn-sm btn-danger" onclick="remove_line(this)" style="float: right; padding: 4px 6px 4px 6px;">X</button>
                                <select class="form-control service_product select2" name="service_product[]">
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
                                <label for="crud-form-1" class="form-label">ราคา</label>
                                <input type="number" class="form-control w-full product_price" onkeyup="summary()" name="service_price[]" step="0.01">
                            </div>
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

                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                            <div id="installment-wrapper">
                                <?php 
                                if(!empty($order)){
                                    if(!empty($order->order_installment)){
                                            $installment = json_decode($order->order_installment, true);
                                            if(!empty($order->order_installment_date)){
                                                $_installment_date = json_decode($order->order_installment_date, true);
                                            }else{
                                                $_installment_date = array();
                                            }
                                            if(!empty($installment)){
                                                foreach ($installment as $key => $_installment) {
                                                       echo '<div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                                                                <label>แบ่งชำระ งวดที่ '.($key+1).'</label>
                                                                <div class="grid grid-cols-12 gap-2">
                                                                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                                                                        <input type="number" class="form-control text-right w-ful" name="order_installment[]" value="'.$_installment.'" placeholder="" step="0.05">
                                                                    </div>
                                                                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                                                                        <input type="text" class="form-control datepicker_installment w-full" name="order_installment_date[]" value="'.(array_key_exists($key, $_installment_date) ? $_installment_date[$key] : '').'" placeholder="วันที่ชำระ">
                                                                    </div>
                                                                </div>
                                                            </div>';
                                                }
                                            }
                                    }else{
                                        echo '
                                        <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                                            <label>แบ่งชำระ งวดที่ 1</label>
                                            <div class="grid grid-cols-12 gap-2">
                                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                                                    <input type="number" class="form-control text-right w-full" name="order_installment[]" value="" placeholder="" step="0.05">
                                                </div>
                                                <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                                                    <input type="text" class="form-control datepicker_installment w-full" name="order_installment[]" value="" placeholder="วันที่ชำระ" step="0.05">
                                                </div>
                                            </div>
                                        </div>';
                                    }
                                }else{
                                    echo '
                                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                                        <label>แบ่งชำระ งวดที่ 1</label>
                                        <div class="grid grid-cols-12 gap-2">
                                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                                                <input type="number" class="form-control text-right w-full" name="order_installment[]" value="" placeholder="" step="0.05">
                                            </div>
                                            <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                                                <input type="text" class="form-control datepicker_installment w-full" name="order_installment[]" value="" placeholder="วันที่ชำระ" step="0.05">
                                            </div>
                                        </div>
                                    </div>';
                                }
                                    
                                ?>    
                            </div>

                            <button type="button" class="btn btn-primary mt-4" id="add-installment">➕ เพิ่มงวดชำระ</button>
                            <button type="button" class="btn btn-danger mt-2" id="remove-installment">➖ ลบงวดล่าสุด</button>
                        </div>
                            
                    </div>
                </div>
            <!-- END: Form Layout -->
        </div>

        <!-- LINE CHAT -->
        <!-- <div class="intro-y col-span-12 lg:col-span-4">
            <div class="intro-y box p-5">
                <select id="userID" class="form-select select2">
                </select>
                <div id="chat-box" style="height: 500px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;"></div>
                <textarea id="message" class="form-control" placeholder="พิมพ์ข้อความ..."></textarea>
                <input type="file" name="message-file" id="message-file" style="display: none;">
                <button type="button" class="btn btn-primary" id="button_copy">ข้อมูลออเดอร์</button>
                <button type="button" class="btn btn-warning" id="button_file" disabled>เลือกไฟล์เพื่อส่ง</button>
                <button type="button" class="btn btn-success" id="send" disabled>ส่ง</button>
            </div>
        </div> -->

        <div class="intro-y col-span-12 lg:col-span-6">
            <!-- BEGIN: Form Layout -->
            <div class="intro-y box p-5">
            <label for="crud-form-1" class="form-label" style="font-size: 18px; font-weight: bold;">ข้อมูลเพิ่มเติม</label>
                
                <div class="grid grid-cols-12 gap-2 mt-4">

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12 mt-3">
                        <label>ภาพหน้างาน</label>
                        <div class="mt-2">
                            <input type="file" class="form-control text-right w-full mt-3" name="order_work_front[]" value="" multiple accept="image/*">
                        </div>
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

                    <?php 
                    if(!empty($order)){
                        $notification = DB::Table('notification')->where('notification_order',$order->order_id)->get();
                    }else{
                        $notification = null;
                    }
                    ?>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                        <label for="crud-form-1" class="form-label">กำหนดวันล่วงหน้า</label>
                        <input type="number" class="form-control" id="show_inputdate" placeholder="ใส่จำนวนครั้งต่อปี" value="{{ (!empty($notification) ? count($notification) : '') }}" step="1">

                        @if(!empty($notification))
                            @foreach($notification as $key => $_noti)
                                <input type="text" class="form-control w-full datepicker_s" placeholder="" id='notification_date_{{$key}}' name="notification_date[]" value="<?php echo (!empty($_noti) ? date('d/m/Y', strtotime($_noti->notification_date)) : date('d/m/Y') ); ?>">
                            @endforeach
                        @endif
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                        <label for="crud-form-1" class="form-label">หมายเหตุ</label>
                        <textarea class="editor" name="order_comment"><?php echo html_entity_decode(!empty($order) ? $order->order_comment : ''); ?></textarea>
                    </div>
                    
                </div>
            </div>
        </div>

        <div class="intro-y col-span-12 lg:col-span-6">
            <!-- BEGIN: Form Layout -->
            <div class="intro-y box p-5">
            <label for="crud-form-1" class="form-label" style="font-size: 18px; font-weight: bold;">ข้อมูลการเงิน</label>
                
                <div class="grid grid-cols-12 gap-2 mt-4">
                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label for="crud-form-1" class="form-label">ประเภทบิล</label>
                        <select class="form-control w-full change_order_type" name="order_type">
                            <option {{ (!empty($order) ? ($order->order_type == "A" ? 'selected' : '') : '' ) }} value="A">A : บิล Vat</option>
                            <option {{ (!empty($order) ? ($order->order_type == "B" ? 'selected' : '') : '' ) }} value="B">B : บิล Non-Vat</option>
                            <option {{ (!empty($order) ? ($order->order_type == "C" ? 'selected' : '') : '' ) }} value="C">C : ไม่รับบิล</option>
                        </select>
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label for="crud-form-1" class="form-label">เลขที่ออเดอร์</label>
                        <input type="text" class="form-control w-full" placeholder="" name="order_no" value="{{ (!empty($order) ? $order->order_no : $order_no ) }}">
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label for="crud-form-1" class="form-label">เลขที่เอกสาร</label>
                        <input type="text" class="form-control w-full order_docno" placeholder="" name="order_docno" value="{{ (!empty($order) ? $order->order_docno : $order_docno ) }}">
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label for="crud-form-1" class="form-label">ใบแจ้งหนี้</label> 
                        @if(!empty($order) && !empty($order->order_file))
                            <a href="{{ Storage::url($order->order_file) }}" target="_blank"><button style="float: right; padding: 4px 6px 4px 6px;" class="btn btn-pending" type="button">ดูเอกสาร</button></a>
                        @endif
                        <input type="file" class="form-control w-full" placeholder="" name="order_file" value="{{ (!empty($order) ? $order->order_file : '' ) }}">
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label for="crud-form-1" class="form-label">วันที่สั่งซื้อ</label>
                        <input type="text" class="form-control w-full datepicker_s" placeholder="" id='order_date' name="order_date" value="<?php echo (!empty($order) ? date('d/m/Y', strtotime($order->order_date)) : date('d/m/Y') ); ?>">
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6">
                        <label>สถานะใบงาน</label>
                        <div class="form-switch mt-2">
                            <select class="form form-control" name="order_status">
                                <option {{ (!empty($order) ? ($order->order_status == '1' ? 'selected' : '' ) : '') }} value="1">{{ 'รอนัดหมาย' }}</option>
                                <option {{ (!empty($order) ? ($order->order_status == '2' ? 'selected' : '' ) : '') }} value="2">{{ 'รอดำเนินการ' }}</option>
                                <option {{ (!empty($order) ? ($order->order_status == '3' ? 'selected' : '' ) : '') }} value="3">{{ 'รอชำระเงิน' }}</option>
                                <option {{ (!empty($order) ? ($order->order_status == '4' ? 'selected' : '' ) : '') }} value="4">{{ 'ดำเนินการเสร็จสิ้น' }}</option>
                                <option {{ (!empty($order) ? ($order->order_status == '5' ? 'selected' : '' ) : '') }} value="5">{{ 'ยกเลิกออเดอร์' }}</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12">
                        <label>ออกใบกำกับภาษี</label>
                        <div class="form-switch mt-2">
                            <input type="checkbox" class="form-check-input checktax" name="order_tax" onclick="checktax()" value="Y" <?php echo (!empty($order) ? (!empty($order->order_taxno) ? 'checked' : '') : ''); ?> >
                        </div>
                    </div> 

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6 tax_detail" <?php echo (!empty($order) ? (!empty($order->order_taxno) ? '' : 'style="display: none;"' ) : 'style="display: none;"' ); ?> >
                        <label for="crud-form-1" class="form-label">ชื่อ บุคคล/นิติบุคคล</label>
                        <input type="text" class="form-control w-full" placeholder="" name="order_taxname" id="order_taxname" value="{{ (!empty($order) ? $order->order_taxname : '') }}">
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6 tax_detail" <?php echo (!empty($order) ? (!empty($order->order_taxno) ? '' : 'style="display: none;"' ) : 'style="display: none;"' ); ?> >
                        <label for="crud-form-1" class="form-label">เลขประจำตัวผู้เสียภาษี</label>
                        <input type="text" class="form-control w-full" placeholder="" name="order_taxno" id="order_taxno" value="{{ (!empty($order) ? $order->order_taxno : '') }}" maxlength="13">
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6 tax_detail" <?php echo (!empty($order) ? (!empty($order->order_taxno) ? '' : 'style="display: none;"' ) : 'style="display: none;"' ); ?> >
                        <label for="crud-form-1" class="form-label">เบอร์โทร</label>
                        <input type="tel" class="form-control w-full" placeholder="" name="order_taxtel" id="order_taxtel" value="{{ (!empty($order) ? $order->order_taxtel : '') }}" maxlength="10">
                    </div>
                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-6 2xl:col-span-6 tax_detail" <?php echo (!empty($order) ? (!empty($order->order_taxno) ? '' : 'style="display: none;"' ) : 'style="display: none;"' ); ?> >
                        <label for="crud-form-1" class="form-label">อีเมล</label>
                        <input type="email" class="form-control w-full" placeholder="" name="order_taxemail" id="order_taxemail" value="{{ (!empty($order) ? $order->order_taxemail : '') }}">
                    </div>

                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12 tax_detail" <?php echo (!empty($order) ? (!empty($order->order_taxno) ? '' : 'style="display: none;"' ) : 'style="display: none;"' ); ?> >
                        <label>ที่อยู่สำหรับออกใบกำกับภาษี &nbsp;&nbsp;&nbsp;&nbsp; 
                            <input type="checkbox" class="form-check-input same_address_bill" onclick="same_address_bill()" value="Y"> สถานที่เดียวกันกับที่อยู่ </label>
                        <div class="mt-2">
                            <textarea class="form-control" name="order_taxaddress" id="order_taxaddress" rows="5">{{ (!empty($order) ? $order->order_taxaddress : '') }}</textarea>
                        </div>
                    </div>

                    <?php 
                        $dayName = '';
                        if(!empty($order)){
                            $_work_order = DB::Table('employee_work')->where('work_order',$order->order_id)->first();
                            if(!empty($_work_order->work_date)){
                                $workDate = $_work_order->work_date; // 2025-03-11
                                $dayName = 'วัน'.\Carbon\Carbon::parse($workDate)->locale('th')->translatedFormat('l').'ที่ '.date('d/m/Y', strtotime($_work_order->work_date));
                            }
                        }
                    ?>

                    <input type="hidden" name="work_date" value="{{ $dayName }}">

                </div>
                <div class="text-right mt-5">
                    <button type="button" class="btn btn-primary" id="button_copy">ข้อมูลออเดอร์</button>
                    <button type="reset" class="btn btn-outline-secondary w-24 mr-1">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary w-24">บันทึก</button>
                </div>
            </div>
            <!-- END: Form Layout -->
        </div>

        
    </div>
    </form>
@endsection

@section('script')
    <script src="{{ mix('dist/js/ckeditor-classic.js') }}"></script>
    <script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {

            $('#show_inputdate').on('input', function () {
                let timesPerYear = parseInt($(this).val());
                let container = $(this).closest('div');
                container.find("input[name='notification_date[]']").remove();

                if (!timesPerYear || timesPerYear <= 0){
                    $(this).val(0)
                };

                let today = new Date();
                let baseDay = today.getDate(); // ใช้วันที่วันนี้ เช่น 30
                let baseMonth = today.getMonth() + 1; // เดือนถัดไป
                let baseYear = today.getFullYear();

                let intervalMonths = 12 / timesPerYear;

                for (let i = 1; i <= timesPerYear; i++) {
                    let totalMonths = baseMonth + Math.round(i * intervalMonths);
                    let targetYear = baseYear + Math.floor(totalMonths / 12);
                    let targetMonth = totalMonths % 12;
                    console.log('targetYear, targetMonth, baseDay : '+targetYear, targetMonth, baseDay);
                    if (targetMonth === 0) {
                        targetMonth = 12;
                        targetYear -= 1;
                    }

                    // สร้างวัน
                    let nextDate = new Date(targetYear, targetMonth - 1, baseDay);
                    console.log('Next Round '+i+' : '+nextDate);
                    // ถ้าเดือนนั้นไม่มีวันที่ 30 เช่น ก.พ.
                    if (nextDate.getDate() !== baseDay) {
                        nextDate.setDate(0); // วันสุดท้ายของเดือนก่อนหน้า
                    }

                    let formattedDate = nextDate.toLocaleDateString('en-GB', {
                        day: '2-digit', month: '2-digit', year: 'numeric'
                    });

                    let input = `<input type="text" class="form-control w-full datepicker_s mt-2" id="notification_date_${i}" name="notification_date[]" placeholder="รอบที่ ${i}" value="${formattedDate}">`;
                    container.append(input);
                }

                $('.datepicker_s').each(function () {
                    if ($(this).data('datepicker')) {
                        $(this).datepicker('destroy');
                    }
                    $(this).datepicker({
                        autoclose: true,
                        format: 'dd/mm/yyyy',
                    });
                });

                $('.datepicker_installment').each(function () {
                    if ($(this).data('datepicker_installment')) {
                        $(this).datepicker('destroy');
                    }
                    $(this).datepicker({
                        format: 'dd/mm/yyyy'
                    });
                });
            });


            $('.datepicker_s').each(function () {
                if ($(this).data('datepicker')) {
                    $(this).datepicker('destroy');
                }
                $(this).datepicker({
                    autoclose: true,
                    format: 'dd/mm/yyyy'
                });
            });

            $('.datepicker_installment').each(function () {
                if ($(this).data('datepicker_installment')) {
                    $(this).datepicker('destroy');
                }
                $(this).datepicker({
                    format: 'dd/mm/yyyy'
                });
            });

            // เริ่มนับจำนวนงวดจากงวดที่มีอยู่
            let installmentIndex = $('#installment-wrapper input[name="order_installment[]"]').length || 1;

            $('#add-installment').click(function() {
                installmentIndex++;

                let installmentHtml = `
                    <div class="intro-y col-span-12 sm:col-span-12 md:col-span-12 2xl:col-span-12 installment-item">
                        <label>แบ่งชำระ งวดที่ ${installmentIndex}</label>
                        <div class="mt-2">
                            <input type="number" class="form-control text-right w-full" name="order_installment[]" value="" placeholder="" step="0.05">
                        </div>
                    </div>
                `;
                $('#installment-wrapper').append(installmentHtml);
            });

            $('#remove-installment').click(function() {
                if (installmentIndex > 1) {
                    $('#installment-wrapper .installment-item').last().remove();
                    installmentIndex--;
                } else {
                    alert('ต้องมีอย่างน้อย 1 งวด');
                }
            });
        });

        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("button_copy").addEventListener("click", function () {
                const copy = formatOrderData();
                navigator.clipboard.writeText(copy).then(() => {
                    alert("คัดลอกข้อมูลออเดอร์แล้ว");
                });
            });

            function formatOrderData() {
                const orderDate = document.querySelector('[name="order_date"]').value; // เปลี่ยนเป็น dynamic ถ้าต้องการ
                const group = document.querySelector('[name="order_type"]').value;
                const saleAdmin = "{{ Auth::user()->name }}";
                const orderNo = document.querySelector('[name="order_no"]').value;
                const documentNo = document.querySelector('[name="order_docno"]').value;
                const work_date = document.querySelector('[name="work_date"]').value;

                const customerCode = document.querySelector("#customer_code").value;
                const firstName = document.querySelector("#customer_firstname").value;
                const lastName = document.querySelector("#customer_lastname").value;
                const company = document.querySelector("#customer_company").value;
                const tel1 = document.querySelector("#customer_tel").value;
                const tel2 = document.querySelector("#customer_tel2").value;
                const address = document.querySelector("#customer_address").value.trim();
                const mapLink = document.querySelector("#customer_googlemap").value;
                let customerName = '';
                // รวมชื่อเต็มลูกค้า
                if(company.length > 0){
                    customerName = company;
                }else{
                    customerName = `${firstName} ${lastName}`;
                }
                // ดึงรายการสินค้า
                const productRows = document.querySelectorAll("#product-container .product-row");
                let workDetails = "";
                let sum = 0;
                productRows.forEach((row) => {
                    const product = row.querySelector(".service_product").selectedOptions[0].textContent;
                    const qty = parseFloat(row.querySelector('[name="service_qty[]"]').value || "0");
                    const price = parseFloat(row.querySelector('[name="service_price[]"]').value || "0");
                    const total = qty * price;
                    sum += total;
                    workDetails += `${product} ${qty} ชิ้น = ${total.toLocaleString()} บาท\n`;
                });

                const discount = parseFloat(document.querySelector(".discount").value || "0");
                const balance = parseFloat(document.querySelector(".balance").value || "0");
                const deposit = parseFloat(document.querySelector(".deposit").value || "0");
                const total = parseFloat(document.querySelector(".total").value || "0");

                const output = `วันที่สั่งซื้อ: ${orderDate}
กลุ่มงาน: ${group}
Sale Admin: ${saleAdmin}
Order No.: ${orderNo}
เอกสารเลขที่: ${documentNo}

รหัสลูกค้า: ${customerCode}
ชื่อลูกค้า: ${customerName}
เบอร์โทร: ${tel1}${tel2 ? " , " + tel2 : ""}
ที่อยู่: ${address}
แผนที่: ${mapLink}

งาน:
${workDetails}รวม ${sum.toLocaleString()} บาท
หักส่วนลด ${discount.toLocaleString()} บาท
คงเหลือ ${balance.toLocaleString()} บาท
มัดจำแล้ว ${deposit.toLocaleString()} บาท
เหลือชำระ ${total.toLocaleString()} บาท

วันที่นัดบริการ ${work_date}`;

                return output;
            }
        });
    </script>

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
            $('.change_order_type').change(function(){
                let order_type = $(this).val();
                let order_id = $('.order_id').val();
                $.ajax({
                    'dataType': 'json',
                    'type': 'post',
                    'url': "{{url('count_order_type')}}",
                    'data': {
                        'order_id' : order_id,
                        'order_type' : order_type,
                        '_token': "{{ csrf_token() }}"
                    },
                    'success': function (data) {
                        console.log(data.order_docno);
                        $('.order_docno').val(data.order_docno);
                    }
                });
            });
            $('#button_copy').click(function(){
                $('input[name="service_product"]').val();
                $('input[name="service_qty"]').val();
                $('input[name="service_price"]').val();
            });
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

        function remove_line(obj) {
            // ลบแถวที่ถูกคลิก
            console.log(obj);
            $(obj).closest('.product-row').remove();
            summary_item(); // อัปเดตยอดรวมหลังจากลบแถว
        }   

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

        function summary_item() {
            // ใช้กับทุกแถว product-row
            $('.product-row').each(function () {
                var row = $(this); // แถวปัจจุบัน

                var qty = parseFloat(row.find('input[name="service_qty[]"]').val()) || 0;
                var price_per_unit = parseFloat(row.find('input[name="service_price_qty[]"]').val()) || 0;

                var total = qty * price_per_unit;
                console.log('Total Item : '+total);

                row.find('input[name="service_price[]"]').val(total.toFixed(2));
            });
            summary();
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
            // เลือก element ของ product-row ที่ต้องการโคลน
            var productRow = document.querySelector('.product-row');
            var clonedRow = productRow.cloneNode(true);

            // รีเซ็ตค่า input fields
            clonedRow.querySelectorAll('input').forEach(function(input) {
                input.value = ''; // ล้างค่าของ input
            });

            // รีเซ็ตค่า select fields (เลือกค่า default)
            clonedRow.querySelectorAll('select').forEach(function(select) {
                select.selectedIndex = 0; // รีเซ็ตค่า select กลับไปที่ index แรก
                select.removeAttribute('data-select2-id'); // ลบ data ที่ Select2 ใช้
            });

            // ลบ select2-hidden-accessible และ select2-container ที่ค้างอยู่ (ถ้ามี)
            $(clonedRow).find('select').removeClass('select2-hidden-accessible').removeAttr('data-select2-id').next('.select2-container').remove();

            // เพิ่ม clonedRow ไปยัง product-container
            document.getElementById('product-container').appendChild(clonedRow);

            // Re-initialize Select2 เฉพาะ select ที่อยู่ใน clonedRow
            $(clonedRow).find('select').select2();
        }

        // LINE CHAT 

        // function loadChat() {
        //     $.get("/get-line-chats", function(response) {
        //         const messages = response.messages;
        //         const users = response.users;
        //         const user_ID = response.session;

        //         $("#userID").html('<option value="">-- เลือกผู้ใช้ --</option>');
        //         users.forEach(user => {
        //             if(user.user_userID == user_ID){
        //                 var selected = 'selected';
        //                 $('#button_file, #send').prop('disabled',false);
        //             }else{
        //                 var selected = '';
        //             }
        //             $("#userID").append(`<option ${selected} value="${user.user_userID}">${user.user_userName}</option>`);
        //         });

        //         $("#chat-box").html("");

        //         messages.forEach(chat => {
        //             if (chat.user_id == user_ID) {
        //                 let messageContent = '';
        //                 let statusText = chat.status === 'read' ? '<span style="color: gray; font-size: 12px;">✔ อ่านแล้ว</span>' : '';

        //                 if (chat.type === 'File') {
        //                     let isImage = /\.(jpg|jpeg|png|gif)$/i.test(chat.message);
        //                     if (isImage) {
        //                         if (chat.user_name === 'Admin') {
        //                             messageContent = `
        //                                 <div style="position: relative; display: inline-block; max-width: 200px; float: right;">
        //                                     <a href="/${chat.message}" data-lightbox="chat-image" data-title="${chat.user_name}">
        //                                         <img src="/${chat.message}" style="max-width: 100%; border-radius: 10px;" />
        //                                     </a>
        //                                 </div>
        //                                 <div style="clear: both;"></div>  <!-- Clear float to avoid overlap -->
        //                             `;
        //                         } else {
        //                             messageContent = `
        //                                 <div style="position: relative; display: inline-block; max-width: 200px;">
        //                                     <a href="/${chat.message}" data-lightbox="chat-image" data-title="${chat.user_name}">
        //                                         <img src="/${chat.message}" style="max-width: 100%; border-radius: 10px;" />
        //                                     </a>
        //                                 </div>
        //                             `;
        //                         }
        //                     } else {
        //                         messageContent = `<a href="/${chat.message}" download>📎 ดาวน์โหลดไฟล์</a>`;
        //                     }
        //                 } else {
        //                     messageContent = chat.message;
        //                 }

        //                 // สำหรับข้อความ
        //                 if (chat.type === 'File') {
        //                     if (chat.user_name === 'Admin') {
        //                         $("#chat-box").append(`<p style="text-align: right;"><strong>${chat.user_name}:</strong></p>
        //                             ${messageContent}<br>${statusText}`);
        //                     } else {
        //                         $("#chat-box").append(`<p><strong>${chat.user_name}:</strong></p>
        //                             ${messageContent}<br>${statusText}`);
        //                     }
        //                 } else {
        //                     if (chat.user_name === 'Admin') {
        //                         $("#chat-box").append(`<p style="text-align: right;"><strong>${chat.user_name}:</strong></p>
        //                             <p style="text-align: right;padding: 4px;margin-bottom: 4px;margin-top: 4px;background-color: lightgray;color: black;border-radius: 5px;">${messageContent}<br>${statusText}</p>`);
        //                     } else {
        //                         $("#chat-box").append(`<p><strong>${chat.user_name}:</strong></p>
        //                             <p style="text-align: left;padding: 4px;margin-bottom: 4px;margin-top: 4px;background-color: lightgray;color: black;border-radius: 5px;">${messageContent}<br>${statusText}</p>`);
        //                     }
        //                 }
        //             }
        //         });


        //         $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);
        //     });
        // }


        // $("#send").click(function() {
        //     let msg = $("#message").val();
        //     let userID = $("#userID").val();
        //     $.post("/api/send-line-message", { message: msg, userID: userID }, function() {
        //         $("#message").val("");
        //         loadChat();
        //     });
        // });

        // $('#button_file').click(function() {
        //     $('#message-file').click();  // จำลองการคลิกที่ input[type="file"]
        // });

        // $("#message-file").on("change", function(event) {
        //     const formData = new FormData();
        //     formData.append('file', event.target.files[0]);  // เลือกไฟล์แรกที่ผู้ใช้เลือก
        //     formData.append('userID', $("#userID").val());  // userID ที่จะส่งไป

        //     // ส่งไฟล์ไปยัง server
        //     $.ajax({
        //         url: "/api/send-line-file",
        //         type: "POST",
        //         data: formData,
        //         processData: false,  // บอกไม่ให้ jQuery ประมวลผลข้อมูลเป็นรูปแบบปกติ
        //         contentType: false,  // ใช้ Content-Type เป็น multipart/form-data สำหรับการอัปโหลดไฟล์
        //         success: function(response) {
        //             console.log(response);
        //             loadChat();  // โหลดแชทใหม่หลังจากส่งไฟล์
        //         },
        //         error: function(xhr, status, error) {
        //             console.error('Error sending file:', error);
        //         }
        //     });
        // });

        // $('#userID').on('change', function() {
        //     const userId = $(this).val();

        //     if (userId) {
        //         $.post("/mark-as-read", { user_id: userId, _token: "{{ csrf_token() }}" }, function(response) {
        //             console.log('marked as read', response);
        //             loadChat(); // reload แชทหลังจากอ่านแล้ว
        //         });

        //         $('#button_file, #send').prop('disabled',false);
        //     }
        // });

        // setInterval(loadChat, 3000); // โหลดแชททุก 3 วินาที
        // loadChat();

        // LINE CHAT 
            
    </script>
@endsection
