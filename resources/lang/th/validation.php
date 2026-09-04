<?php

/**
 * ข้อความแจ้งเตือนตอน validate ฟอร์ม (ภาษาไทย)
 * ก่อนหน้านี้โปรเจกต์ไม่มีไฟล์นี้เลย ทำให้ error กลายเป็นข้อความ default ของ Laravel
 * (ภาษาอังกฤษ และกำกวม เช่น "The first name field is required.") — ตรงกับที่เมล QA
 * ข้อ 6 ระบุว่า "ข้อความแจ้งเตือนควรระบุข้อมูลที่ขาดให้ชัดเจน เช่น กรุณากรอกนามสกุล
 * แทนข้อความ กรุณากรอก" — ไฟล์นี้ทำให้ทุกฟอร์มในระบบ (ไม่ใช่แค่ฟอร์มเดียว) ได้รับ
 * ข้อความที่อ่านเข้าใจง่ายเป็นภาษาไทยไปด้วยกันทั้งหมด
 */

return [

    'accepted' => ':attribute ต้องได้รับการยอมรับ',
    'accepted_if' => ':attribute ต้องได้รับการยอมรับ เมื่อ :other เป็น :value',
    'active_url' => ':attribute ไม่ใช่ URL ที่ถูกต้อง',
    'after' => ':attribute ต้องเป็นวันที่หลังจาก :date',
    'after_or_equal' => ':attribute ต้องเป็นวันที่ตั้งแต่ :date เป็นต้นไป',
    'alpha' => ':attribute ต้องเป็นตัวอักษรเท่านั้น',
    'alpha_dash' => ':attribute ต้องเป็นตัวอักษร ตัวเลข ขีดกลาง หรือขีดล่างเท่านั้น',
    'alpha_num' => ':attribute ต้องเป็นตัวอักษรหรือตัวเลขเท่านั้น',
    'array' => ':attribute ต้องเป็นชุดข้อมูล (array)',
    'before' => ':attribute ต้องเป็นวันที่ก่อน :date',
    'before_or_equal' => ':attribute ต้องเป็นวันที่ก่อนหรือเท่ากับ :date',
    'between' => [
        'array' => ':attribute ต้องมีจำนวนระหว่าง :min ถึง :max รายการ',
        'file' => ':attribute ต้องมีขนาดระหว่าง :min ถึง :max กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าระหว่าง :min ถึง :max',
        'string' => ':attribute ต้องมีความยาวระหว่าง :min ถึง :max ตัวอักษร',
    ],
    'boolean' => ':attribute ต้องเป็นค่า true หรือ false',
    'confirmed' => ':attribute ไม่ตรงกับการยืนยัน',
    'date' => ':attribute ต้องเป็นวันที่ที่ถูกต้อง',
    'date_equals' => ':attribute ต้องเป็นวันที่เดียวกับ :date',
    'date_format' => ':attribute ต้องตรงตามรูปแบบ :format',
    'different' => ':attribute และ :other ต้องไม่เหมือนกัน',
    'digits' => ':attribute ต้องมี :digits หลัก',
    'digits_between' => ':attribute ต้องมีระหว่าง :min ถึง :max หลัก',
    'distinct' => ':attribute มีค่าซ้ำกัน',
    'email' => ':attribute ต้องเป็นอีเมลที่ถูกต้อง',
    'ends_with' => ':attribute ต้องลงท้ายด้วย: :values',
    'exists' => 'ไม่พบ :attribute ที่เลือกในระบบ',
    'file' => ':attribute ต้องเป็นไฟล์',
    'filled' => ':attribute ต้องไม่เว้นว่าง',
    'gt' => [
        'array' => ':attribute ต้องมีมากกว่า :value รายการ',
        'file' => ':attribute ต้องมีขนาดมากกว่า :value กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่ามากกว่า :value',
        'string' => ':attribute ต้องมีความยาวมากกว่า :value ตัวอักษร',
    ],
    'gte' => [
        'array' => ':attribute ต้องมีอย่างน้อย :value รายการ',
        'file' => ':attribute ต้องมีขนาดตั้งแต่ :value กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าตั้งแต่ :value',
        'string' => ':attribute ต้องมีความยาวตั้งแต่ :value ตัวอักษร',
    ],
    'image' => ':attribute ต้องเป็นไฟล์รูปภาพ',
    'in' => ':attribute ที่เลือกไม่ถูกต้อง',
    'in_array' => ':attribute ไม่มีอยู่ใน :other',
    'integer' => ':attribute ต้องเป็นจำนวนเต็ม',
    'ip' => ':attribute ต้องเป็นหมายเลข IP ที่ถูกต้อง',
    'json' => ':attribute ต้องเป็นข้อความ JSON ที่ถูกต้อง',
    'lt' => [
        'array' => ':attribute ต้องมีน้อยกว่า :value รายการ',
        'file' => ':attribute ต้องมีขนาดน้อยกว่า :value กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าน้อยกว่า :value',
        'string' => ':attribute ต้องมีความยาวน้อยกว่า :value ตัวอักษร',
    ],
    'lte' => [
        'array' => ':attribute ต้องมีไม่เกิน :value รายการ',
        'file' => ':attribute ต้องมีขนาดไม่เกิน :value กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าไม่เกิน :value',
        'string' => ':attribute ต้องมีความยาวไม่เกิน :value ตัวอักษร',
    ],
    'max' => [
        'array' => ':attribute ต้องมีไม่เกิน :max รายการ',
        'file' => ':attribute ต้องมีขนาดไม่เกิน :max กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าไม่เกิน :max',
        'string' => ':attribute ต้องมีความยาวไม่เกิน :max ตัวอักษร',
    ],
    'mimes' => ':attribute ต้องเป็นไฟล์ประเภท: :values',
    'min' => [
        'array' => ':attribute ต้องมีอย่างน้อย :min รายการ',
        'file' => ':attribute ต้องมีขนาดอย่างน้อย :min กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าอย่างน้อย :min',
        'string' => ':attribute ต้องมีความยาวอย่างน้อย :min ตัวอักษร',
    ],
    'not_in' => ':attribute ที่เลือกไม่ถูกต้อง',
    'numeric' => ':attribute ต้องเป็นตัวเลข',
    'regex' => ':attribute มีรูปแบบไม่ถูกต้อง',
    'required' => 'กรุณากรอก:attribute',
    'required_if' => 'กรุณากรอก:attribute เมื่อ :other เป็น :value',
    'required_unless' => 'กรุณากรอก:attribute เว้นแต่ :other อยู่ใน :values',
    'required_with' => 'กรุณากรอก:attribute เมื่อระบุ :values',
    'required_with_all' => 'กรุณากรอก:attribute เมื่อระบุ :values',
    'required_without' => 'กรุณากรอก:attribute เมื่อไม่ได้ระบุ :values',
    'required_without_all' => 'กรุณากรอก:attribute เมื่อไม่ได้ระบุ :values ทั้งหมด',
    'same' => ':attribute และ :other ต้องตรงกัน',
    'size' => [
        'array' => ':attribute ต้องมี :size รายการ',
        'file' => ':attribute ต้องมีขนาด :size กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่า :size',
        'string' => ':attribute ต้องมีความยาว :size ตัวอักษร',
    ],
    'starts_with' => ':attribute ต้องขึ้นต้นด้วย: :values',
    'string' => ':attribute ต้องเป็นข้อความ',
    'unique' => ':attribute นี้มีอยู่ในระบบแล้ว',
    'uploaded' => ':attribute อัปโหลดไม่สำเร็จ',
    'url' => ':attribute ต้องเป็น URL ที่ถูกต้อง',

    /*
    |--------------------------------------------------------------------------
    | ชื่อฟิลด์ (Custom Attributes) — แปลชื่อคอลัมน์ที่ใช้บ่อยในระบบให้อ่านง่าย
    | ฟิลด์ที่ไม่ได้ระบุไว้ในนี้ Laravel จะโชว์ชื่อคอลัมน์ดิบแทน (เช่น customer_name)
    |--------------------------------------------------------------------------
    */
    'attributes' => [
        'name' => 'ชื่อ',
        'first_name' => 'ชื่อ',
        'last_name' => 'นามสกุล',
        'username' => 'ชื่อผู้ใช้งาน',
        'email' => 'อีเมล',
        'phone' => 'เบอร์โทรศัพท์',
        'password' => 'รหัสผ่าน',
        'role' => 'ตำแหน่ง/สิทธิ์',
        'role_ids' => 'แผนก',

        // ฟอร์มติดต่อฝ่ายขาย/แอดมิน
        'topic' => 'หัวข้อ',
        'user_type' => 'ประเภทผู้ใช้',
        'company_name' => 'ชื่อบริษัท',
        'preferred_contact_time' => 'ช่วงเวลาที่สะดวกให้ติดต่อกลับ',
        'province' => 'จังหวัด',
        'district' => 'เขต/อำเภอ',
        'subdistrict' => 'แขวง/ตำบล',
        'zipcode' => 'รหัสไปรษณีย์',
        'address_full' => 'ที่อยู่',
        'detail' => 'รายละเอียด',
        'image' => 'รูปภาพ',

        // สินค้า/ใบเสนอราคา
        'product_id' => 'สินค้า',
        'quantity' => 'จำนวน',
        'price' => 'ราคา',
        'stock_quantity' => 'จำนวนคงเหลือ',
        'brand' => 'ยี่ห้อ',
        'model' => 'รุ่น',
        'warranty_months' => 'ระยะเวลารับประกัน',
        'shipping_fee' => 'ค่าจัดส่ง',
        'install_fee' => 'ค่าติดตั้ง',
        'site_address' => 'สถานที่สำรวจหน้างาน',
        'site_image' => 'รูปภาพหน้างาน',
        'preferred_survey_date' => 'วันที่นัดสำรวจ',

        // แลกของรางวัล
        'customer_name' => 'ชื่อผู้รับ',
        'customer_phone' => 'เบอร์โทรศัพท์ผู้รับ',
        'address_id' => 'ที่อยู่จัดส่ง',
        'address_text' => 'ที่อยู่จัดส่ง',
    ],

];
