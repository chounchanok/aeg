# สรุปสิ่งที่ฝั่ง Mobile ต้องแก้ไข — QA Feedback แอป AEG

อ้างอิงอีเมล "ข้อเสนอแนะและปัญหาที่พบจากการทดสอบแอปพลิเคชัน AEG" (4 ก.ย. 2569)
**Deadline จาก K.Jai (Assistant Director): จันทร์ 7 ก.ย. 2569**

> ⚠️ อีเมลของ K.Jai มี 4 ข้อเพิ่มเติมนอกเหนือจาก 6 ข้อหลักด้านล่าง (homepage display, quick menu, payment→invoice/receipt link, update items in shopping) — **ยังไม่ได้วิเคราะห์ในรอบนี้** ต้องตรวจแยกอีกครั้งก่อนถึง deadline

---

## 1. แอปปิดตัวเอง/เด้งออกระหว่างใช้งาน (Critical)
**Mobile ต้องทำเองทั้งหมด** ไม่มี API เกี่ยวข้อง
- ตรวจสอบ crash ระหว่างเลือกสินค้า/กรอกฟอร์ม/แลกรางวัล/ชำระเงิน
- ทำระบบบันทึก Crash Log
- บันทึกข้อมูลฟอร์ม/ตะกร้าสินค้าไว้ชั่วคราว ให้กลับมาทำต่อได้โดยไม่ต้องเริ่มใหม่
- ตรวจสอบการหมดอายุ Session ระหว่างใช้งาน

## 2. ตรวจคะแนนก่อนแลกรางวัล (Critical)
**Backend แก้แล้ว — mobile ต้อง integrate ใหม่**
- `GET /ease-club/rewards/{rewardId}` (auth) → เพิ่ม field `current_points`, `points_missing`, `can_redeem`
- **(7 ก.ย.)** `GET /ease-club/rewards/{rewardId}` → เพิ่ม field `return_policy` (เงื่อนไขการยกเลิกหรือคืนคะแนน, string|null), `shipping_fee` (ค่าจัดส่ง บาท, number — 0 = ส่งฟรี), `delivery_estimate` (ระยะเวลาจัดส่ง เช่น "3-5 วันทำการ", string|null) — แอดมินกรอกจากหลังบ้าน
- `POST /ease-club/rewards/{rewardId}/redeem` → กันคะแนนติดลบ/แลกซ้ำจากกดยืนยันซ้ำเร็วๆ แล้ว (ฝั่ง backend)

Mobile ต้องทำ:
- ใช้ `can_redeem` ปิดปุ่มแลกเมื่อคะแนนไม่พอ
- แสดงข้อความ เช่น "คุณมี 0 คะแนน รางวัลนี้ใช้ 26,250 คะแนน คุณยังขาดอีก 26,250 คะแนน" จาก `current_points`/`points_missing`
- disable ปุ่มยืนยันระหว่างรอ response (กันกดซ้ำฝั่ง UI ด้วย แม้ backend กันไว้แล้ว)

## 3. ระบบซื้อสินค้าผ่านแอป (High)
**Backend แก้บางส่วน — มี gap**
- `GET /ecommerce/products`, `/products/{id}` → เพิ่ม field: `stock_quantity, brand, model, warranty_months, return_policy_th, shipping_fee, install_fee, compatible_with`
- ใหม่: `POST /ecommerce/quote-requests`, `GET /ecommerce/quote-requests`, `GET /ecommerce/quote-requests/{id}` (auth) — สำหรับสินค้าที่ต้อง "ขอใบเสนอราคา" (`is_contact_only = true`)

Mobile ต้องทำ:
- แยกปุ่มตาม `is_contact_only`: false = ซื้อเลย/ซื้อพร้อมติดตั้ง (ใช้ cart/checkout เดิม), true = "ขอใบเสนอราคา" (เรียก endpoint ใหม่ ส่ง `product_id, quantity, site_address, site_image_url (multipart), preferred_survey_date, detail`)
- หน้ารายละเอียดสินค้า: แสดง field ใหม่ทั้งหมดที่เพิ่มมา
- หน้าติดตามสถานะคำขอใบเสนอราคา (ใช้ `GET /ecommerce/quote-requests`)

**Gap (ยังไม่ทำ):** ไม่มี API บันทึก Serial Number / วันเริ่ม-หมดประกัน หลังส่งมอบ/ติดตั้งสินค้า ผูกกับ order — QA ขอไว้แต่ backend ยังไม่รองรับ

## 4. ความสม่ำเสมอของหน้าจอก่อน/หลังล็อกอิน (High)
**Mobile ต้องทำเองทั้งหมด** (UI/UX design) ไม่มี API เกี่ยวข้อง
- จำนวน/ชื่อ/ลำดับเมนูด้านล่างให้เหมือนกันก่อน-หลังล็อกอิน (ปัจจุบันก่อนล็อกอิน 3 เมนู หลังล็อกอิน 5 เมนู)
- ผู้ใช้ที่ยังไม่ล็อกอินควรเห็นเมนูครบ แต่กดฟังก์ชันที่ต้องใช้ข้อมูลสมาชิกแล้วค่อยเด้งให้ล็อกอิน

## 5. การเชื่อมต่อกับระบบหลังบ้าน (High)
**Backend ทำแบบ scoped-down เท่านั้น — ไม่กระทบ mobile โดยตรง**
- ทำแค่: แจ้งเตือนอัตโนมัติไปยังแผนกที่เกี่ยวข้อง (หลังบ้าน/แอดมิน) เมื่อมีแจ้งซ่อมใหม่ / สั่งซื้อจ่ายเงินแล้ว / คำขอใบเสนอราคาใหม่ / คำขอติดต่อฝ่ายขายใหม่
- **ยังไม่ทำ**: full workflow (หมายเลขงาน → ใบเสนอราคา → อนุมัติ+ชำระเงิน → เตรียมอุปกรณ์ → มอบหมายช่าง → ปิดงาน → ออกเอกสาร+บันทึกรับประกันอัตโนมัติ) — เป็น scope ที่ตัดออกไปก่อน
- Mobile: ไม่ต้องทำอะไรเพิ่มสำหรับข้อนี้

## 6. แบบฟอร์มติดต่อฝ่ายขาย (Medium)
**Backend แก้เกือบครบ — มี gap เล็กน้อย**
- `POST /user/contact-admin` (auth) → auto-fill ชื่อ/นามสกุล/อีเมล/เบอร์/ที่อยู่จากโปรไฟล์+ที่อยู่ default, รับ `province/district/subdistrict/zipcode` แยกช่อง, `product_id + quantity`, `detail`, `image` (multipart), `user_type` (business/personal) + `company_name`, ออก `request_number` อัตโนมัติ + `status`
- ใหม่: `GET /user/contact-admin/my-requests` (auth) — ติดตามสถานะ

Mobile ต้องทำ:
- เปลี่ยน label "ติดต่อแอดมิน" → "ติดต่อฝ่ายขาย"/"ขอรับคำปรึกษา", ปุ่ม "Submit" → "ส่งข้อมูล"/"ยืนยันคำขอ"
- dropdown จังหวัด/เขต-อำเภอ/แขวง-ตำบล/รหัสไปรษณีย์ แยกช่อง (ส่งเป็น `province/district/subdistrict/zipcode`)
- เพิ่มช่องสินค้า+จำนวนที่สนใจ (auto-fill ได้ถ้าเข้ามาจากหน้าสินค้า), ช่องรายละเอียด, อัปโหลดรูปสถานที่
- toggle ธุรกิจ/บุคคล (`user_type`) — ซ่อน/แสดงช่องบริษัทตามประเภท
- แสดงหมายเลขคำขอหลังส่ง + หน้าติดตามสถานะ (`GET /user/contact-admin/my-requests`)
- ข้อความ validation ระบุ field ที่ขาดชัดเจน เช่น "กรุณากรอกนามสกุล" แทน "กรุณากรอก"

**อัปเดต 7 ก.ย. — contract ใหม่ของ `POST /user/contact-admin` (ไม่ auto-fill จากโปรไฟล์แล้ว เก็บตามที่แอปส่งมาเท่านั้น):**
- REQUIRED: `user_type` (business|personal), `first_name`, `last_name`, `address_full`, `email`, `phone`, `topic`
- OPTIONAL - ADDRESS: `province`, `district`, `subdistrict`, `zipcode`
- OPTIONAL - PRODUCT: `product_id`, `product_name`, `quantity`
- OPTIONAL - DETAIL: `detail`
- OPTIONAL - BUSINESS: `company_name`, `tax_id`, `branch`
- OPTIONAL - CONTACT: `preferred_contact_time`
- OPTIONAL - FILE: `image` (multipart, รูปภาพ ≤ 10MB)
- Response: `{ id, request_number, status }` / validation error 422 พร้อมข้อความระบุ field ที่ขาด

---

## สรุป Gap ฝั่ง Backend ที่ยังไม่ได้ทำ (ไม่ใช่งาน mobile แต่กระทบ feature)
1. บันทึก Serial Number/วันรับประกันหลังส่งมอบสินค้า (ข้อ 3)
2. Full workflow order→quote→อนุมัติ→เตรียมอุปกรณ์→มอบหมายช่าง→เอกสาร+รับประกัน (ข้อ 5)
3. ~~tax_id + สาขา ในฟอร์มติดต่อฝ่ายขายกรณีธุรกิจ (ข้อ 6)~~ — ทำแล้ว 7 ก.ย. (migration `2026_09_07_100000`)
4. 4 ข้อเพิ่มเติมจาก K.Jai (homepage, quick menu, payment→invoice/receipt, update shopping items) — ยังไม่ได้วิเคราะห์
