# สรุป API สำหรับทีม Mobile — อัปเดต 9 ต.ค. 2569

รอบนี้แก้ตามคอมเมนต์ลูกค้า 10 ข้อ ทุก endpoint อยู่ใต้ `/api` และ endpoint ที่ระบุว่า (auth) ต้องส่ง `Authorization: Bearer <token>`
รูปแบบ response เหมือนเดิม: `{ "status": "success" | "error", "message": "...", "data": ... }`

> **ก่อนทดสอบ:** ฝั่ง backend ต้องรัน `php artisan migrate` ก่อน (มี migration ใหม่ 4 ไฟล์ วันที่ 2026_10_09_1000xx)

| # | เรื่อง | Endpoint | สถานะสำหรับ Mobile |
|---|--------|----------|-----------------|
| 1 | ตู้เซฟ: วันว่าง + ต่ออายุ | **ใหม่** `GET /smart-lockers/search-available`, `GET /smart-lockers/{id}/availability`, `GET /main/expiring-services`, `POST /smart-lockers/bookings/{id}/renew` | มีอยู่แล้ว (ดู `docs/smart-locker-mobile-api.md`) |
| 2 | คูปอง/ของรางวัลที่แลก | `GET /rewards/my-codes-all`, **ใหม่** `GET /rewards/my-coupons`, `GET /rewards/my-codes/{id}`, **ใหม่** `POST /rewards/my-codes/{id}/use` | **ต้อง integrate ใหม่** |
| 3 | สิทธิพิเศษตาม Tier | `/ease-club/*`, `/main/recommended-privileges`, `/search` | response เดิม แต่ซ่อนรางวัลที่ Tier ไม่ถึง |
| 5 | url ในแจ้งเตือน | `GET /user/notifications` + push data | **ต้อง integrate ใหม่** (กดแล้วไปต่อ) |
| 6 | จำนวนแจ้งเตือนที่ยังไม่อ่าน | **ใหม่** `GET /user/notifications/unread-count`, `POST /user/notifications/read-all` | **ใหม่** |
| 7 | ค้นหาสินค้า | `GET /search?q=` | ค้นจากชื่ออย่างเดียวแล้ว (ไม่ต้องแก้แอป) |
| 8 | ลบที่อยู่ | **ใหม่** `DELETE /ecommerce/addresses/{id}` | **ใหม่** |
| 9 | รูปแบนเนอร์/ป๊อปอัพภาษาอังกฤษ | `GET /main/banners`, `/main/popup-ads`, `/ease-club/banners*` | ใช้ field ใหม่ `display_image_url*` |
| — | สินค้าจับกลุ่มหลังเพิ่มลงตะกร้า | `POST /ecommerce/cart/add`, `GET /ecommerce/cart` | ดูหัวข้อท้ายเอกสาร |

ข้อ 4 (คอลัมน์ Tier/แต้ม) และข้อ 10 (ขนาดรูป) เป็นงานหลังบ้านอย่างเดียว ไม่กระทบ API

---

## 1. ตู้เซฟนิรภัย — วันว่าง/ไม่ว่าง และต่ออายุ

### ใหม่: `GET /smart-lockers/search-available` — เลือกช่วงวันก่อน แล้วระบบหาตู้ว่างให้ (public)
Query:
- `from` (บังคับ, YYYY-MM-DD, ตั้งแต่วันนี้)
- ระยะเวลา ส่งอย่างใดอย่างหนึ่ง: `duration_months` (แนะนำ เพราะตรงกับการจอง) หรือ `to` (วันสุดท้ายที่ใช้ตู้ นับรวม)
- `type` = PRIME | PRIVILEGE, `category_id` (ไม่บังคับ)

**มีตู้ว่าง** (HTTP 200)
```json
{
  "status": "success",
  "message": "มีตู้เซฟว่างในช่วงเวลาที่เลือก",
  "data": {
    "is_available": true,
    "smart_locker_id": 12,
    "requested": { "from": "2026-11-01", "to": "2027-01-31", "duration_months": 3, "duration_days": 92 },
    "available_lockers": [ { "id": 12, "smart_locker_id": 12, "locker_number": "PR-001", "type": "PRIME", "category_id": 1, "title": "...", "price": 1500, "image_url": "..." } ]
  }
}
```
นำ `smart_locker_id` + `from` (เป็น `start_date`) + `duration_months` ไปเรียก `POST /smart-lockers/book` ต่อ (ถ้าค้นด้วย `to` ต้องแปลงเป็นจำนวนเดือนก่อนจอง เพราะ API จองรับเป็นเดือน)

**ไม่มีตู้ว่าง** (HTTP 200 แต่ `status` = `"unavailable"`)
```json
{
  "status": "unavailable",
  "message": "ช่วงเวลาที่คุณเลือกไม่มี locker ว่าง โปรดเลือกใหม่อีกครั้ง",
  "data": {
    "is_available": false,
    "smart_locker_id": null,
    "requested": { "from": "2026-11-01", "to": "2027-01-31", "duration_months": 3, "duration_days": 92 },
    "free_ranges": [
      { "id": 12, "locker_number": "PR-001", "...": "...", "free_ranges": [ { "from": "2026-11-01", "to": "2026-11-20", "days": 20 } ] }
    ],
    "suggestions": [
      { "id": 12, "locker_number": "PR-001", "...": "...", "start_date": "2026-12-15", "end_date": "2027-03-14" }
    ]
  }
}
```
- `free_ranges` = ตู้ที่ว่างบางช่วงในช่วงที่ลูกค้าเลือก (บอกว่าว่างวันไหนบ้าง)
- `suggestions` = วันเริ่มที่ใกล้ที่สุด (ภายใน 180 วัน) ที่ตู้ว่างครบตามระยะเวลาเดิม สูงสุด 5 ตู้ เรียงจากวันที่เร็วสุด กดเลือกแล้วจองต่อได้เลย
- ต้องเช็ค `status` / `is_available` ไม่ใช่ดูแค่ HTTP code

เส้นเดิม `GET /smart-lockers/{id}/availability` ยังใช้ได้เหมือนเดิม (สำหรับปฏิทินรายตู้)


API ไม่เปลี่ยนจากเอกสาร `docs/smart-locker-mobile-api.md` สรุปสั้นๆ:

- `GET /smart-lockers/{lockerId}/availability?from=YYYY-MM-DD&to=YYYY-MM-DD` → `dates[]: { date, is_available, reason: null | booked | admin_block | maintenance }` ใช้ทำปฏิทินเลือกวัน
- `GET /main/expiring-services` (auth) → รายการใกล้หมดอายุภายใน 30 วัน รายการตู้เซฟมี `service_type: "smart_locker"`, `can_renew: true` และ `renewal.endpoint`
- กดปุ่ม **ต่ออายุ** → `POST /smart-lockers/bookings/{bookingId}/renew` body `{ "duration_months": 12, "payment_gateway": "bbl" }` → ได้ `payment_url` ไปเปิดใน WebView สัญญาเดิมจะต่ออายุหลังชำระเงินสำเร็จ

(หลังบ้านมีปฏิทินแสดงว่าลูกค้าคนไหนจองตู้ไหน เลขที่ออเดอร์อะไร แล้ว — ไม่กระทบแอป)

---

## 2. คูปอง / ของรางวัลที่ลูกค้าแลก (ใหม่)

### ประเภทของรางวัล (`reward_type`)

| ค่า | ความหมาย | หลังลูกค้ากด "ใช้คูปอง" |
|-----|----------|--------------------------|
| `product` | สินค้า ต้องจัดส่ง | สถานะเป็น `shipping_confirm` (ยืนยันการจัดส่ง) จากนั้นหลังบ้านเปลี่ยนเป็น `processing` → `shipping` → `delivered` |
| `voucher` | วอยเชอร์ แอดมินกรอกรหัสส่งให้ | ใช้ได้เมื่อแอดมินส่งรหัสแล้ว (`can_use = true`) กดแล้วเป็น `used` |
| `discount` | ส่วนลดในแอป ใช้ตอน checkout | ส่ง `display_code` ใน `reward_code` ตอน checkout หรือกดใช้เองแล้วเป็น `used` |

### สถานะ (`status`) — แอปแสดง `status_label` ได้เลย

| status | status_label | ใครเปลี่ยน |
|--------|--------------|-----------|
| `active` | ยังไม่ได้ใช้ | หลังแลกสำเร็จ |
| `shipping_confirm` | ยืนยันการจัดส่ง | ลูกค้ากดใช้คูปอง (สินค้า) |
| `processing` | กำลังดำเนินการ | **หลังบ้านเท่านั้น** |
| `shipping` | กำลังจัดส่ง | **หลังบ้านเท่านั้น** (มี `tracking_number`) |
| `delivered` | จัดส่งสำเร็จ | **หลังบ้านเท่านั้น** |
| `used` | ใช้แล้ว | ลูกค้ากดใช้ (วอยเชอร์/ส่วนลด) หรือใช้ตอน checkout |
| `cancelled` | ยกเลิก | หลังบ้าน |

### `POST /rewards/redeem` และ `POST /ease-club/rewards/{rewardId}/redeem` (auth) — แลกของรางวัล
Body เหมือนเดิม (`reward_id` สำหรับเส้นแรก) ที่อยู่ไม่บังคับแล้ว ส่งตอนแลกหรือตอนกด "ใช้คูปอง" ก็ได้:
```json
{ "reward_id": 5, "customer_name": "สมชาย", "customer_phone": "0812345678", "address_id": 12 }
```
Response `data` เพิ่ม `id` (ใช้เรียก endpoint ด้านล่าง), `reward_type`, `display_code`, `status`
Error ใหม่: `403` เมื่อ Tier ไม่ถึง, `422` เมื่อ `address_id` ไม่ใช่ที่อยู่ของลูกค้าเอง

### `GET /rewards/my-codes-all` (auth) — ของรางวัลทั้งหมดที่เคยแลก
Query (ไม่บังคับ): `status=active,shipping` (คั่นด้วย comma) และ `reward_type=product|voucher|discount`
แต่ละรายการ:
```json
{
  "id": 31,
  "reward_id": 5,
  "reward_title": "กระเป๋าผ้า AEG",
  "reward_title_en": "AEG Tote Bag",
  "image_url": "https://.../rewards/a.webp",
  "reward_type": "product",
  "reward_type_label": "สินค้า (จัดส่ง)",
  "is_coupon": false,
  "code": "RWD-AB12CD34",
  "display_code": null,
  "voucher_code": null,
  "voucher_note": null,
  "is_waiting_code": false,
  "discount_amount": 0,
  "status": "shipping",
  "status_label": "กำลังจัดส่ง",
  "can_use": false,
  "redeemed_date": "2026-10-01 10:00:00",
  "requested_at": "2026-10-02 09:00:00",
  "used_at": null,
  "delivered_at": null,
  "shipping": {
    "customer_name": "สมชาย", "customer_phone": "0812345678",
    "address_id": 12, "address_text": null, "address": { "...": "แถวจาก customer_addresses" },
    "carrier": "Kerry", "tracking_number": "KEX123456"
  }
}
```
- `display_code` = รหัสที่ให้แสดงบนแอป (วอยเชอร์: รหัสจากแอดมิน, ส่วนลด: รหัสจากแอดมินหรือ `RWD-...`) ถ้าเป็น `null` ไม่ต้องแสดงรหัส
- `is_waiting_code = true` → แสดง "รอเจ้าหน้าที่ส่งรหัส" และปิดปุ่มใช้
- `can_use` → เปิด/ปิดปุ่ม "ใช้คูปอง"
- field เดิม (`customer_name`, `address_id`, `category_id` ฯลฯ) ยังส่งอยู่ แอปเวอร์ชันเก่าไม่พัง

### `GET /rewards/my-codes` (auth)
เหมือนเดิม คือคูปองส่วนลดที่ยังใช้ได้สำหรับหน้า checkout แต่ตอนนี้แต่ละรายการมีรูปแบบเดียวกับด้านบน ให้ส่ง `display_code` เป็น `reward_code` ตอน checkout/buy-now (ระบบรับได้ทั้ง `RWD-...` และรหัสจากแอดมิน)

### `GET /rewards/my-coupons` (auth) — คูปองที่ยังไม่ได้ใช้ ไม่รวมสินค้า (ใหม่)
คืนเฉพาะวอยเชอร์และส่วนลดในแอปที่ `status = active` ใช้ทำหน้า "คูปองของฉัน"
Query (ไม่บังคับ): `reward_type=voucher` หรือ `reward_type=discount`
แต่ละรายการรูปแบบเดียวกับ `my-codes-all` (`shipping` เป็น `null`) รวมวอยเชอร์ที่ยังรอรหัสจากแอดมินด้วย (`is_waiting_code = true`, `can_use = false`, `display_code = null`) แอปควรแสดงว่า "รอเจ้าหน้าที่ส่งรหัส"
ต่างจาก `GET /rewards/my-codes` ที่คืนเฉพาะคูปองที่มีมูลค่าส่วนลด (สำหรับเลือกตอน checkout)

### `GET /rewards/my-codes/{id}` (auth) — รายละเอียด + timeline (ใหม่)
รูปแบบเดียวกับด้านบน และเพิ่ม `timeline: [{ status, status_label, note, actor: customer|admin|system, created_at }]` ใช้ทำหน้าติดตามสถานะการจัดส่ง

### `POST /rewards/my-codes/{id}/use` (auth) — ลูกค้ากด "ใช้คูปอง" (ใหม่)
- **สินค้า** ส่งที่อยู่ให้แอดมินตรวจสอบ (ส่งได้ทั้ง `address_id` และ `address_text` ถ้าไม่ส่งจะใช้ค่าที่ให้ไว้ตอนแลก):
  ```json
  { "address_id": 12, "customer_name": "สมชาย", "customer_phone": "0812345678" }
  ```
  ถ้าส่ง `address_id` โดยไม่ส่งชื่อ/เบอร์ ระบบใช้ `contact_name`/`contact_phone` ของที่อยู่นั้น
  สำเร็จ → `status = shipping_confirm` ("ยืนยันการจัดส่ง") หลังจากนั้นแอปเปลี่ยนสถานะเองไม่ได้
- **วอยเชอร์/ส่วนลด** ไม่ต้องส่ง body → `status = used`
- Response `data` = รายละเอียดคูปองพร้อม `timeline`
- Error: `400` ใช้ไปแล้ว หรือวอยเชอร์ยังไม่ได้รับรหัส, `404` ไม่พบ, `422` ที่อยู่ไม่ครบหรือไม่ใช่ของลูกค้า

**แจ้งเตือน:** เมื่อแอดมินส่งรหัสหรือเปลี่ยนสถานะการจัดส่ง ลูกค้าจะได้รับแจ้งเตือน `type: privilege`, `target_type: reward_code`, `target_id: {id}` → กดแล้วเปิดหน้ารายละเอียดคูปอง

---

## 3. สิทธิพิเศษผูกกับ Tier ลูกค้า

Tier อ่านจาก `customer_wallets.current_tier_id` (Advance < Platinum < Beyond เรียงตาม `min_spending`) ระบบ**ซ่อน**ของรางวัลที่ `minimum_tier_required` สูงกว่า Tier ของลูกค้า ส่วนรางวัลที่ไม่กำหนด Tier ทุกคนเห็น

| Endpoint | สิ่งที่เปลี่ยน |
|----------|----------------|
| `GET /ease-club/overview` | `advance_exclusive` เดิมเคย hardcode Advance ตอนนี้เป็นรางวัลตาม Tier จริง เพิ่ม `tier` และ `tier_exclusive` (ข้อมูลเดียวกัน) ต้องส่ง token ถ้ามี (ไม่ส่ง = guest เห็นเฉพาะ Tier ต่ำสุด) |
| `GET /ease-club/categories/{id}/rewards` | ซ่อนรางวัลที่ Tier ไม่ถึง และซ่อนรางวัลที่ปิดใช้งาน (`is_active = false`) ควรส่ง token |
| `GET /ease-club/rewards/{id}`, `/ease-club/rewards_guest/{id}` | Tier ไม่ถึง → `404` เพิ่ม field `reward_type` |
| `GET /main/recommended-privileges` | สุ่มเฉพาะรางวัลที่ Tier ถึงและเปิดใช้งาน |
| `GET /search` | ผลลัพธ์ `rewards` กรองตาม Tier (ส่ง token ถ้ามี) |
| แลกรางวัล | Tier ไม่ถึง → `403` |

> endpoint public อย่าง overview/rewards ใช้ token แบบไม่บังคับ แอปควรแนบ `Authorization` ทุกครั้งที่ล็อกอินอยู่

---

## 5. แจ้งเตือนกดแล้วไปต่อได้ (url / target)

### `GET /user/notifications?type=all|general|promotion|privilege|order|service|invoice` (auth)
แต่ละรายการเพิ่ม:
```json
{
  "id": 88, "title": "...", "body": "...", "type": "order", "is_read": false,
  "url": null,
  "target_type": "order",
  "target_id": 1234,
  "action": "in_app",
  "is_clickable": true
}
```
และมี `unread_count` อยู่ที่ระดับเดียวกับ `data` ด้วย

| `action` | แอปทำอะไร |
|----------|-----------|
| `in_app` | เปิดหน้าภายในแอปตาม `target_type` + `target_id` |
| `url` | เปิด `url` ในเบราว์เซอร์/WebView |
| `none` | กดแล้วแค่ mark read |

`target_type` ที่ใช้ตอนนี้: `order` (รายละเอียดออเดอร์), `service_request` (งานซ่อม), `invoice` (ใบแจ้งหนี้), `reward_code` (คูปองของฉัน → `/rewards/my-codes/{id}`), `reward` (ของรางวัล EASE CLUB), `product` (สินค้า), `smart_locker_booking` (การจองตู้เซฟ)
แจ้งเตือนอัตโนมัติ (ออเดอร์/แจ้งซ่อม/ใบแจ้งหนี้/คูปอง) จะมี target ทุกครั้ง แจ้งเตือนที่แอดมินส่งเองอาจมี target, url หรือไม่มีทั้งคู่ก็ได้
แจ้งเตือนเก่าก่อนวันนี้ไม่มี target (`action = none`)

**Push (FCM) data payload** มี key เดียวกัน: `notification_id`, `target_type`, `target_id`, `url`, `type` (ค่าเป็น string ทั้งหมด) ใช้ตอนกด push เพื่อนำทางและเรียก read

### `POST /user/notifications/{id}/read` (auth)
เหมือนเดิม และตอนนี้ `data` คืน `{ "unread_count": 3 }` ใช้อัปเดต badge ได้ทันที

---

## 6. จำนวนแจ้งเตือนที่ยังไม่อ่าน (ใหม่)

### `GET /user/notifications/unread-count` (auth)
```json
{ "status": "success", "data": { "unread_count": 5, "by_type": { "order": 2, "promotion": 3 } } }
```
ใช้แสดง badge บนไอคอนกระดิ่ง และแยกตามแท็บด้วย `by_type`

### `POST /user/notifications/read-all` (auth) — อ่านทั้งหมด (เพิ่มให้)
Body/query ไม่บังคับ: `type=promotion` เพื่ออ่านเฉพาะแท็บนั้น → `data: { updated, unread_count }`

---

## 7. ค้นหาสินค้า — ค้นจากชื่อเท่านั้น

`GET /search?q=คำค้น` ตอนนี้ค้นจาก `name_th`/`name_en` (สินค้า), `title_th`/`title_en` (ตู้เซฟ/รางวัล/ประกัน) และหมายเลขตู้ ไม่ค้นใน description แล้ว
Response เหมือนเดิม ไม่ต้องแก้แอป

---

## 8. ลบที่อยู่ (ใหม่)

### `DELETE /ecommerce/addresses/{id}` (auth)
ถ้า client ส่ง DELETE ไม่ได้ ใช้ `POST /ecommerce/addresses/{id}/delete` แทน
```json
{ "status": "success", "message": "ลบที่อยู่เรียบร้อยแล้ว", "data": { "id": 12, "mode": "deleted" } }
```
- `mode: "deleted"` = ลบจริง, `"archived"` = ที่อยู่เคยใช้กับออเดอร์/แจ้งซ่อม/จองตู้/ของรางวัล จึงซ่อนแทนการลบเพื่อเก็บประวัติ (ฝั่งแอปทำเหมือนกันคือเอาออกจากรายการ)
- ถ้าลบที่อยู่หลัก ระบบตั้งที่อยู่ล่าสุดที่เหลือเป็นที่อยู่หลักให้
- `404` เมื่อไม่พบหรือไม่ใช่ของลูกค้า
- `GET /ecommerce/addresses` และ `/addresses/{id}` จะไม่คืนที่อยู่ที่ลบแล้ว

---

## 9. รูปแบนเนอร์/ป๊อปอัพภาษาอังกฤษ

ส่ง header `Accept-Language: en` (หรือ `th`) แล้วใช้ field ที่เลือกภาษาให้แล้ว:

| Endpoint | field ใหม่ |
|----------|-----------|
| `GET /main/banners`, `GET /ease-club/banners`, `GET /ease-club/banners/category` | `display_image_url` (desktop/ทั่วไป), `display_image_url_m` (สำหรับมือถือ แนะนำให้แอปใช้ตัวนี้) และ raw `image_url_en`, `image_url_m_en` |
| `GET /main/popup-ads` | `display_image_url`, `image_url_en` |

ถ้าแอดมินไม่ได้อัปโหลดรูปอังกฤษ ระบบคืนรูปภาษาไทยให้แทน แอปจึงใช้ `display_image_url*` ได้เสมอ field เดิม (`image_url`, `image_url_m`) ยังส่งอยู่

ขนาดรูปที่หลังบ้านแนะนำ (ใช้อ้างอิงตอนออกแบบ UI): แบนเนอร์มือถือ 732×500 px (1.46:1), แบนเนอร์ desktop 2592×800 px (3.24:1), ป๊อปอัพ 1080×1350 px (4:5), ของรางวัล 800×600 px (4:3), สินค้า 1000×850 px

---

## สินค้าจับกลุ่ม (Bundle) — แนะนำหลังเพิ่มสินค้าลงตะกร้า

**มีอยู่แล้วและใช้ได้** (ทำไว้ตั้งแต่ 28 ก.ย.) รอบนี้เพิ่มให้ `POST /cart/add` คืนข้อมูลบันเดิลทันที แอปจะได้ไม่ต้องยิงซ้ำ

### Flow ที่แนะนำ
1. ลูกค้ากดเพิ่มสินค้า → `POST /ecommerce/cart/add` (auth) body `{ "product_id": 10, "quantity": 1 }`
2. ถ้า response มี `bundle_suggestions` → แสดง bottom sheet/การ์ด **"ซื้อคู่กันรับราคาชุด"**
3. ลูกค้ากดเพิ่มสินค้าที่แนะนำ → เรียก `POST /ecommerce/cart/add` ด้วย `product_id` ใน `missing_products` และ `quantity = need_qty`
4. ครบชุดแล้ว รายการย้ายจาก `bundle_suggestions` ไปอยู่ใน `applied_bundles` และส่วนลดถูกหักใน `net_total` อัตโนมัติ
5. หน้าตะกร้าใช้ `GET /ecommerce/cart` (โครงสร้างเดียวกัน) ส่วน checkout คำนวณส่วนลดซ้ำที่ server เสมอ

### `POST /ecommerce/cart/add` — response (ใหม่ เดิม `data: null`)
```json
{
  "status": "success",
  "message": "เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว",
  "data": {
    "cart_count": 3,
    "applied_bundles": [],
    "bundle_discount_amount": 0,
    "bundle_suggestions": [
      {
        "bundle_id": 2,
        "name_th": "ชุดกล้อง + เซนเซอร์ประตู",
        "name_en": "Camera + Door Sensor Set",
        "bundle_price": 4990,
        "savings": 800,
        "missing_products": [
          { "product_id": 15, "name_th": "เซนเซอร์ประตู", "name_en": "Door Sensor", "price": 1290, "image_url": "https://...", "need_qty": 1 }
        ]
      }
    ]
  }
}
```

### `GET /ecommerce/cart` (auth)
- `summary.applied_bundles[]`: `{ bundle_id, name_th, name_en, original_total, bundle_price, savings }` — ชุดที่ครบแล้วและได้ส่วนลด
- `summary.bundle_discount_amount`: ส่วนลดรวมจากบันเดิล (รวมอยู่ใน `summary.net_total` แล้ว)
- `bundle_suggestions[]`: เหมือนด้านบน (ตอนนี้ `missing_products` มี `image_url` ด้วย)

### ข้อควรรู้
- แนะนำเฉพาะบันเดิลที่ในตะกร้ามีสินค้าอยู่แล้วอย่างน้อย 1 ชิ้นแต่ยังไม่ครบชุด เรียงจากชุดที่ประหยัดมากสุด
- สินค้าแพ็กเกจที่คิดตาม `duration_months` ไม่นับในบันเดิล
- แอปไม่ต้องคำนวณส่วนลดเอง ใช้ `net_total` จาก server
- ตอนนี้ยังไม่มี endpoint แสดงบันเดิลในหน้ารายละเอียดสินค้า (ก่อนเพิ่มลงตะกร้า) ถ้าต้องการแจ้ง backend ได้
