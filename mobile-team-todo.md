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

## 7. แชทติดต่อสอบถาม — ต้องเลือก "หัวข้อ" ก่อนคุยกับเจ้าหน้าที่ (7 ก.ย.)
**Backend แก้แล้ว — mobile ต้อง integrate ใหม่**
- หลังบ้านแยกแชทตามแผนกแล้ว (Insurance เห็นเฉพาะประกัน, Sec Admin เห็นระบบรักษาความปลอดภัย+บริการช่าง ฯลฯ) ดังนั้นทุกข้อความต้องมี `topic` ที่ถูกต้อง ไม่งั้นไปไม่ถึงแผนก
- ใหม่: `GET /api/support-chats/topics` (public) → `{ data: [ { key, label, label_en, icon } ], default_topic }` — ใช้ทำหน้าเลือกหัวข้อ
- `GET /api/support-chats/history?topic={key}` และ `POST /api/support-chats/send { topic, message }` → `topic` **บังคับ** และต้องเป็น key จากรายการด้านบน (ส่งค่าอื่นได้ 422) — `general` ยังใช้ได้ (= สอบถามทั่วไป → Sales Admin)
- key ปัจจุบัน: `general`, `security-system`, `technician-service`, `insurance`, `locker`, `ease-club`, `application` (ชุดเดียวกับหมวดของแชทบอท `GET /api/chatbot/topics` → ถ้าลูกค้ากด "คุยกับเจ้าหน้าที่" จากในหมวดบอท ให้ส่ง key หมวดนั้นเป็น `topic` ได้เลย ไม่ต้องให้เลือกซ้ำ)

Mobile ต้องทำ:
- ก่อนเข้าหน้าแชทกับเจ้าหน้าที่ ให้เลือกหัวข้อจาก `GET /support-chats/topics` (หรือ auto-select จากหมวดบอทที่อยู่)
- แสดงชื่อหัวข้อที่กำลังคุย + ปุ่มเปลี่ยนหัวข้อ (ประวัติแชทแยกกันต่อหัวข้อ)
- Pusher channel `support-chat.{user_id}` รวมทุกหัวข้อ → กรอง `messageData.topic` ให้ตรงกับหัวข้อที่เปิดอยู่ก่อนแสดง

## 8. ลิงก์ใบเสนอราคา/ใบเสร็จรับเงิน (payment→invoice/receipt link) (15 ก.ย.)
**Backend แก้แล้ว — mobile ต้อง integrate ใหม่**
- แอดมินอัปโหลดไฟล์ใบเสนอราคา/ใบเสร็จ (PDF หรือรูปภาพ) ให้คำสั่งซื้อ/ใบแจ้งซ่อมแต่ละรายการได้จากหลังบ้านแล้ว (สร้างเมื่อไหร่ก็ได้ ไม่บังคับต้องมีตั้งแต่แรก)
- `GET /ecommerce/orders` และ `GET /ecommerce/orders/{id}` (auth) → เพิ่ม field `quotation_url`, `receipt_url` (string url หรือ `null` ถ้ายังไม่มี) — เป็นลิงก์ตรง ดาวน์โหลด/เปิดดูได้เลยไม่ต้องยิง API เพิ่ม
- `GET /service-requests` และ `GET /service-requests/{id}` (auth) → เพิ่ม field `quotation_url`, `receipt_url` เช่นกัน
- **(15 ก.ย.)** `GET /user/my-packages` (auth) → แต่ละรายการใน `service_history` ของแต่ละแพ็กเกจ เพิ่ม `quotation_url`, `receipt_url` ด้วย (ผูกกับใบแจ้งซ่อมครั้งนั้นๆ) ให้แอปแสดงปุ่มโหลดเอกสารในประวัติการซ่อมแต่ละครั้งได้เลย
- เมื่อแอดมินอัปโหลดเอกสารใหม่ ระบบจะส่ง Notification (in-app + push) ให้ลูกค้าอัตโนมัติ — `type` ที่ส่งมาใน push data payload คือ `order_document` (คำสั่งซื้อ) หรือ `service_request_document` (ใบแจ้งซ่อม)

Mobile ต้องทำ:
- หน้ารายละเอียดคำสั่งซื้อ/ใบแจ้งซ่อม: ถ้า `quotation_url`/`receipt_url` ไม่เป็น null ให้แสดงปุ่ม "ดาวน์โหลดใบเสนอราคา" / "ดาวน์โหลดใบเสร็จ" เปิดลิงก์นั้นตรงๆ (เปิดในเบราว์เซอร์ในแอป หรือดาวน์โหลดไฟล์ก็ได้)
- รองรับ push notification ชนิดใหม่ 2 แบบข้างต้น ให้กดแล้วพาไปหน้ารายละเอียดออเดอร์/ใบแจ้งซ่อมนั้น

**Gap:** ยังไม่มี full workflow ออกใบเสนอราคาอัตโนมัติจากระบบ (แอดมินต้องสร้างไฟล์เองแล้วอัปโหลด ไม่ใช่ auto-generate PDF จากรายการสินค้า)

## 9. Popup โฆษณาหน้าแรก — สลับรูป + จำกัด 1 ครั้ง/วัน (15 ก.ย.)
**Backend แก้แล้ว (contract เปลี่ยน) — mobile ยังไม่เคย integrate เรื่องนี้มาก่อน ต้องทำใหม่ทั้งหมด**
- หลังบ้านอัปโหลดรูป popup พร้อมกันได้หลายไฟล์ในครั้งเดียวแล้ว (สร้างเป็นหลาย record เรียงตามลำดับไฟล์ที่เลือก)
- `GET /main/popup-ads` (public) — **เปลี่ยน contract**: เดิมคืน array ของ popup ทั้งหมด → ตอนนี้คืน `data` เป็น **object เดียว** (รูปถัดไปที่ควรแสดง) หรือ `null` (ถ้าวันนี้แสดงครบทุกรูปที่ Active แล้ว)
  - Query param ใหม่ (ไม่บังคับ): `shown_ids` = list ของ popup ad id ที่แอปเคยแสดงให้ผู้ใช้เห็นไปแล้ว "วันนี้" คั่นด้วยคอมมา เช่น `?shown_ids=1,4,7`
  - **แอปต้องเก็บสถานะเองฝั่ง local** (เช่น SharedPreferences/UserDefaults): เก็บ `{date, shown_ids[]}` — ถ้าวันที่เก็บไว้ไม่ตรงกับวันนี้ ให้เคลียร์ `shown_ids` เป็น `[]` ก่อน (ขึ้นวันใหม่ = เริ่มรอบใหม่) แล้วค่อยเรียก API
  - เมื่อ API ส่ง ad กลับมาไม่เป็น null และแอปแสดง popup นั้นให้ผู้ใช้เห็นแล้ว ให้เพิ่ม id นั้นเข้า `shown_ids` ที่เก็บไว้ (สำหรับใช้ครั้งถัดไป)

Mobile ต้องทำ (ยังไม่เคยมี popup ads ในแอปมาก่อน — ทำใหม่ทั้งหมด):
- เรียก `GET /main/popup-ads?shown_ids=...` ทุกครั้งที่เข้าหน้าแรก/เปิดแอป
- ถ้า `data` ไม่เป็น null → แสดง popup (รูป `image_url`, กดแล้วเปิด `link_url` ถ้ามี) แล้วบันทึก id ลง local storage ตามด้านบน
- ถ้า `data` เป็น null → ไม่ต้องแสดงอะไร

## 10. สินค้าจับกลุ่ม (Bundle) — ซื้อคู่กันรับราคาชุด (28 ก.ย.)
**Backend แก้แล้ว (contract เพิ่มใหม่ ไม่กระทบ field เดิม) — mobile ยังไม่เคย integrate เรื่องนี้มาก่อน ต้องทำใหม่ทั้งหมด**
- แอดมินหลังบ้านสร้าง "ชุดสินค้า" ได้แล้ว (เลือกสินค้าตั้งแต่ 2 ชิ้นขึ้นไป + ตั้ง "ราคาชุด" เอง ไม่ใช่ % ส่วนลด)
- `GET /ecommerce/cart` (auth) → **เพิ่ม field ใหม่ใน response** โดย field เดิมทั้งหมดยังอยู่เหมือนเดิม:
  - `summary.bundle_discount_amount` — ส่วนลดจากบันเดิลที่ครบชุดแล้ว (บวกรวมเข้ากับ `discount_amount` เดิมใน `net_total` ให้แล้วอัตโนมัติ ไม่ต้องคำนวณเองฝั่งแอป)
  - `summary.applied_bundles` — array ของชุดที่ครบแล้ว `[{ bundle_id, name_th, name_en, original_total, bundle_price, savings }]`
  - `bundle_suggestions` (นอก summary) — array คำแนะนำ "ซื้อเพิ่มอีกนิดรับราคาชุด" สำหรับบันเดิลที่มีสินค้าในตะกร้าอยู่แล้วบางส่วน: `[{ bundle_id, name_th, name_en, bundle_price, savings, missing_products: [{ product_id, name_th, name_en, price, need_qty }] }]`
- `POST /ecommerce/checkout` (auth) → response เพิ่ม `bundle_discount`, `applied_bundles` (โครงสร้างเดียวกับด้านบน) ส่วนลดบันเดิลถูกคำนวณซ้ำฝั่ง server เสมอ (ไม่เชื่อค่าจาก client) และรวมเข้า `discount`/`total_amount` เดิมให้แล้ว
- **หมายเหตุสำคัญ**: สินค้าประเภทแพ็กเกจที่คิดราคาตาม `duration_months` (เช่น แพ็กเกจรายเดือน) จะไม่ถูกนับรวมในบันเดิล เพราะราคาต่อชิ้นถูกคูณจำนวนเดือนไปแล้ว เทียบราคาปกติของบันเดิลตรงๆ ไม่ได้ — ยังไม่รองรับในเวอร์ชันนี้

Mobile ต้องทำ (ฟีเจอร์ใหม่ทั้งหมด):
- หน้าตะกร้า: ถ้า `summary.applied_bundles` ไม่ว่าง ให้โชว์ badge/แถบบอกว่า "ได้รับส่วนลดชุด X" พร้อมยอดที่ประหยัดได้ (ยอดสุทธิ `net_total` คำนวณให้แล้วจาก backend ไม่ต้องคิดเลขเอง)
- หน้าตะกร้า (หรือหลังเพิ่มสินค้าลงตะกร้า): ถ้า `bundle_suggestions` ไม่ว่าง ให้โชว์การ์ดแนะนำ "ซื้อ {missing_products.name_th} เพิ่มอีก {need_qty} ชิ้น รับราคาชุด {bundle_price} บาท (ประหยัด {savings} บาท)" พร้อมปุ่มเพิ่มสินค้านั้นลงตะกร้าได้เลย (เรียก `POST /ecommerce/cart/add` เดิม)
- ไม่ต้องคำนวณส่วนลดบันเดิลเองฝั่งแอป ให้เชื่อค่าที่ backend ส่งกลับมาเสมอ (ทั้ง preview ตอนดูตะกร้า และยอดจริงตอน checkout)

## 11. ใบแจ้งหนี้รายเดือนอัตโนมัติ (Invoices) (28 ก.ย.)
**Backend ใหม่ทั้งหมด (feature ใหม่) — mobile ยังไม่เคย integrate เรื่องนี้มาก่อน**
- ระบบสร้างใบแจ้งหนี้ให้ลูกค้าโดยอัตโนมัติทุกวัน (cron) แบ่งเป็น 2 ประเภท (แยกด้วย field `type`):
  1. `contract` — จากสัญญาบริการรายเดือนที่แอดมินสร้างให้ลูกค้า (เช่น ค่าบำรุงรักษารายเดือน)
  2. `statement` — สรุปยอดคำสั่งซื้อที่ยังไม่ชำระของลูกค้ากลุ่ม "วางบิลรายเดือน" (แอดมินตั้งค่าเฉพาะราย) รวมเป็นใบแจ้งหนี้เดียวตอนสิ้นเดือน แทนที่จะจ่ายทีละออเดอร์
- ช่องทางจ่ายเงินเลือกได้ต่อลูกค้า/สัญญา: `gateway` (ชำระผ่าน BBL App-to-App เหมือนคำสั่งซื้อ/จองตู้เซฟเดิม) หรือ `bank_transfer` (ลูกค้าแนบสลิปเอง รอแอดมินตรวจสอบและกดยืนยันด้วยมือ)

**Endpoints ใหม่ (auth:sanctum, prefix `/user`):**
- `GET /user/invoices` → `{ data: [ { id, invoice_number, type, billing_month (YYYY-MM), issue_date, due_date, subtotal, vat_amount, total_amount, payment_method, status, is_overdue, paid_at, payment_slip_url } ] }`
  - `status`: `pending` (รอชำระ) / `paid` (ชำระแล้ว) / `overdue` (เกินกำหนด — ปัจจุบันระบบยังไม่ auto-flip เป็น overdue ในฐานข้อมูล ให้เช็คจาก `is_overdue` แทนไปก่อน) / `cancelled`
- `GET /user/invoices/{id}` → เหมือนด้านบน + `items: [{ description, quantity, unit_price, amount }]` (รายการย่อยในใบแจ้งหนี้ เช่น รายชื่อคำสั่งซื้อที่ถูกรวม หรือชื่อสัญญา)
- `POST /user/invoices/{id}/pay` → ใช้เฉพาะใบแจ้งหนี้ที่ `payment_method = gateway` และ `status = pending` เท่านั้น → คืน `{ payment_url }` (ลิงก์ WebView จ่ายผ่าน BBL เหมือนคำสั่งซื้อ — **แอปต้องต่อ `/{type}` ท้าย URL เอง** เช่น `/qrcode`, `/creditcard`, `/all` แบบเดียวกับที่ทำกับ `payment_url` ของคำสั่งซื้ออยู่แล้ว)
- `POST /user/invoices/{id}/upload-slip` (multipart) → field `slip` (ไฟล์รูป/PDF ≤10MB) — ใช้แนบสลิปโอนเงิน ใช้ได้กับใบแจ้งหนี้ที่ `status = pending` เท่านั้น (ไม่จำกัดว่าต้อง `payment_method = bank_transfer` ฝั่ง backend แต่ตั้งใจให้ใช้กับช่องทางนี้) → คืน `{ payment_slip_url }` — หลังอัปโหลดสถานะยังเป็น `pending` จนกว่าแอดมินจะตรวจสลิปแล้วกดยืนยันจากหลังบ้าน (ไม่ auto-paid ทันที)
- `GET /user/profile` → response เดิมของ `profile` object จะมี field ใหม่ `is_invoice_customer` (true/false) และ `invoice_payment_method` (`gateway`/`bank_transfer`) ติดมาด้วยอัตโนมัติ (แอดมินเป็นคนตั้งค่าให้ ลูกค้าแก้เองไม่ได้)

Mobile ต้องทำ (ฟีเจอร์ใหม่ทั้งหมด):
- เพิ่มเมนู "ใบแจ้งหนี้ของฉัน" ในหน้าโปรไฟล์/บัญชี — แสดงรายการจาก `GET /user/invoices` (แนะนำแยก tab รอชำระ/ชำระแล้ว โดยดูจาก `status`/`is_overdue`)
- หน้ารายละเอียดใบแจ้งหนี้: แสดงรายการย่อย (`items`), ยอดก่อน VAT/VAT/ยอดรวม, วันครบกำหนด
  - ถ้า `payment_method = gateway` และ `status = pending` → ปุ่ม "ชำระเงิน" เรียก `POST /user/invoices/{id}/pay` แล้วเปิด `payment_url` ใน WebView
  - ถ้า `payment_method = bank_transfer` และ `status = pending` → แสดงข้อมูลบัญชีธนาคารสำหรับโอน (ยังไม่มี endpoint ส่งเลขบัญชีจาก backend — ใช้ข้อมูลคงที่ในแอปไปก่อน หรือรอ backend เพิ่ม endpoint นี้) + ปุ่ม "แนบสลิปโอนเงิน" เรียก `POST /user/invoices/{id}/upload-slip`
  - ถ้า `payment_slip_url` ไม่เป็น null → แสดงว่า "แนบสลิปแล้ว รอตรวจสอบ" พร้อมรูปสลิปที่แนบไป
- Push notification ชนิดใหม่: `type: "invoice"` (มีใบแจ้งหนี้ใหม่ / ชำระเงินสำเร็จ) — data payload มี `invoice_id`, `invoice_number` ให้กดแล้วพาไปหน้ารายละเอียดใบแจ้งหนี้นั้น

**Gap ที่ยังไม่มี (แจ้งไว้ก่อน กันมือถือรอ):**
- ยังไม่มี endpoint ส่งข้อมูลบัญชีธนาคารสำหรับให้ลูกค้าโอนเงิน (`bank_transfer`) — ต้องคุยกับฝ่ายบัญชีว่าจะ hardcode ในแอปหรือให้ backend เพิ่ม config
- ใบแจ้งหนี้ยังไม่มี PDF ให้ดาวน์โหลด (มีแต่ข้อมูลรายการใน JSON) — ถ้าต้องการไฟล์ PDF ทางการ ต้องคุยเพิ่มเป็นเฟสถัดไป
- field `status = overdue` ในฐานข้อมูลยังไม่ auto-update (ยังเป็น `pending` ค้างไว้แม้เลย due_date) ให้เช็คความล่าช้าจาก `is_overdue` ที่ API คำนวณให้แทน

---

## สรุป Gap ฝั่ง Backend ที่ยังไม่ได้ทำ (ไม่ใช่งาน mobile แต่กระทบ feature)
1. บันทึก Serial Number/วันรับประกันหลังส่งมอบสินค้า (ข้อ 3)
2. Full workflow order→quote→อนุมัติ→เตรียมอุปกรณ์→มอบหมายช่าง→เอกสาร+รับประกัน (ข้อ 5)
3. ~~tax_id + สาขา ในฟอร์มติดต่อฝ่ายขายกรณีธุรกิจ (ข้อ 6)~~ — ทำแล้ว 7 ก.ย. (migration `2026_09_07_100000`)
4. 4 ข้อเพิ่มเติมจาก K.Jai (homepage, quick menu, payment→invoice/receipt, update shopping items) — **payment→invoice/receipt เสร็จแล้ว (ข้อ 8 ด้านบน)**, ที่เหลือ (homepage, quick menu, update shopping items) ยังไม่ได้วิเคราะห์
5. ใบเสนอราคายังเป็นการอัปโหลดไฟล์โดยแอดมินเอง ไม่ใช่ระบบ auto-generate PDF จากรายการสินค้า/บริการ
6. **(28 ก.ย.)** สินค้าจับกลุ่ม (ข้อ 10) และ **ระบบใบแจ้งหนี้รายเดือนอัตโนมัติ (ข้อ 11)** — โค้ด/schema เสร็จแล้วทั้งคู่ แต่ **ยังไม่ได้รัน migration บนเครื่อง production** เพราะ `device_bash` ที่ใช้ทำงานเซสชันนี้เข้าถึง PHP/MySQL ของเครื่องจริงไม่ได้ (เป็น sandbox แยกต่างหาก) — **ต้องรัน `php artisan migrate` เองบนเครื่อง production ก่อน** ตาราง `product_bundles`, `product_bundle_items`, `service_contracts`, `invoices`, `invoice_items` และคอลัมน์ใหม่ต่างๆ (`orders.bundle_discount`, `customer_profiles.is_invoice_customer`, `customer_profiles.invoice_payment_method`) ถึงจะถูกสร้างจริง — ก่อนรัน migrate ทั้งสองฟีเจอร์นี้จะ error ทันทีถ้ามีคนเรียกใช้
7. **(28 ก.ย.)** ระบบใบแจ้งหนี้รายเดือนอัตโนมัติ — เขียนโค้ดเสร็จแล้ว (schema + service + cron command + หน้าแอดมิน + API มือถือ) แต่ **ยังไม่ได้ตั้ง Windows Task Scheduler ให้รัน `php artisan schedule:run` บนเครื่อง production** เพราะ session นี้เข้าไม่ถึง Windows OS จริงๆ — ถ้ายังไม่ตั้ง cron จะไม่มีใบแจ้งหนี้ออกอัตโนมัติเลย (ใช้ปุ่ม "สร้างใบแจ้งหนี้ตอนนี้" ในหลังบ้านแทนไปก่อนได้) ดูคำแนะนำตั้งค่าด้านล่าง
8. ใบแจ้งหนี้ยังไม่มี endpoint ส่งเลขบัญชีธนาคารสำหรับลูกค้าโอนเงิน และยังไม่มี PDF ให้ดาวน์โหลด (ดู Gap ในข้อ 11 ด้านบน)

---

## วิธีตั้งค่าที่ต้องทำเองบนเครื่อง production (Windows/XAMPP) — สำหรับข้อ 6-7

**1. รัน migration (ครั้งเดียว):**
```
cd C:\xampp\htdocs\aeg
php artisan migrate
```

**2. ตั้ง Windows Task Scheduler ให้รัน Laravel Scheduler ทุกนาที** (จำเป็นสำหรับใบแจ้งหนี้รายเดือนอัตโนมัติ — ถ้าไม่ตั้งข้อนี้ ระบบจะไม่ออกใบแจ้งหนี้ให้เองเลย ต้องกดปุ่ม "สร้างใบแจ้งหนี้ตอนนี้" ในหลังบ้านแทนไปก่อน):
เปิด Command Prompt แบบ Administrator แล้วรัน (แก้ path ให้ตรงกับเครื่องจริงถ้าไม่ได้ติดตั้งที่ `C:\xampp`):
```
schtasks /create /tn "AEG Laravel Scheduler" /tr "C:\xampp\php\php.exe C:\xampp\htdocs\aeg\artisan schedule:run" /sc minute /mo 1 /ru SYSTEM
```
ตรวจสอบว่าตั้งสำเร็จ: `schtasks /query /tn "AEG Laravel Scheduler"`
งานที่ตั้งไว้ในโค้ด (`bootstrap/app.php` → `withSchedule`) คือ `invoices:generate` รันทุกวันตอนตี 1 — รันรายวันเพราะสัญญาแต่ละอันมีวันออกบิลไม่ตรงกัน และถ้าเครื่องปิด/ล่มในวันที่ควรออกบิล ระบบจะไล่ตามออกบิลย้อนหลังให้เองในวันถัดไปของเดือนเดียวกัน
