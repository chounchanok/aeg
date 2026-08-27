# Chat Bot API — สรุปเส้นทางที่เพิ่มใหม่ (สำหรับทีม Mobile)

Base URL: `https://<domain>/api`
รูปแบบ response ทุกเส้นทาง (ตาม `ApiResponseTrait` เดิมของระบบ):

```json
{ "status": "success" | "error", "message": "...", "data": { ... } }
```

ถ้า request ไม่ผ่าน validation จะได้ HTTP 422 ตาม default ของ Laravel:
```json
{ "message": "...", "errors": { "field": ["..."] } }
```

**Auth**: ทุกเส้นทางด้านล่างเป็น **public route** (ไม่มี middleware `auth:sanctum` บังคับ) แต่ 2 เส้นทาง (`/chatbot/search`, `/chatbot/leads`) จะเช็คว่ามี Bearer token ที่ valid หรือไม่ *ภายใน controller* — ถ้าไม่มี/ไม่ valid จะตอบกลับ `require_membership: true` แทนที่จะ error 401 ดังนั้น **แนะนำให้แนบ `Authorization: Bearer <token>` ไปด้วยเสมอถ้าผู้ใช้ล็อกอินอยู่** เพื่อให้ใช้งานสองเส้นนี้ได้

---

## ⚠️ 0. `GET /faqs` — เส้นทางเดิมที่มีอยู่แล้ว แต่เปลี่ยนรูปแบบ response (Breaking Change)

เส้นทางนี้**ไม่ใช่ของใหม่** แต่ **response เปลี่ยนโครงสร้างไปจากเดิมทั้งหมด** เพื่อให้ FAQ บนแอปตรงกับหน้าเว็บ `/faq` และดึงจากชุดข้อมูลเดียวกับ Chat Bot (`chatbot_service_faqs`) แทนตาราง `faqs` เดิม

**ก่อนแก้** `data` เป็น array แบนๆ ของแถวจากตาราง `faqs` (มี `category`, `question_en`, `answer_en`)

**หลังแก้** `data` เป็น array ของ "หมวดหลัก" แต่ละหมวดมี `faqs` ซ้อนอยู่ข้างใน (เฉพาะหมวดที่มีคำถามจริงเท่านั้นถึงจะโผล่มา):
```json
[
  {
    "id": 1,
    "key": "security-system",
    "name_th": "ระบบรักษาความปลอดภัย",
    "name_en": "Security System",
    "icon": "fa-shield-halved",
    "sort_order": 1,
    "is_active": 1,
    "faqs": [
      {
        "id": 10,
        "question_th": "สัญญาณเตือนร้องขึ้นมาโดยไม่ทราบสาเหตุ",
        "answer_th": "...",
        "service_id": 5,
        "service_name": "ระบบสัญญาณกันขโมย (Burglar Alarm System)",
        "topic_id": 1
      }
    ]
  }
]
```

🚨 **ถ้าแอปเวอร์ชันที่ปล่อยไปแล้วเรียกเส้นนี้อยู่และ parse โครงสร้างเดิม (flat array + field `category`) โค้ดจะพังหรือแสดงผลผิด** — ให้ทีม mobile ปรับ parser ให้รองรับโครงสร้างใหม่นี้ก่อน deploy backend ตัวนี้ขึ้น production หรือถ้ายังไม่พร้อม แจ้งกลับมาได้ จะช่วยทำเป็น endpoint ใหม่แยกต่างหากแทนการแก้ของเดิมก็ได้

---

## 1. FAQ Bot แบบเดิม (เวอร์ชันแรก — เก็บไว้เผื่อใช้ แต่แนะนำให้ใช้ชุดเมนูปุ่มกดด้านล่างแทน)

### `POST /faq-bot/ask`
จับคู่คำถามอิสระกับตาราง `faqs` เดิม (ไม่มีเมนู ไม่มี escalate ตามเวลาทำการ)

Body:
```json
{ "message": "สัญญาณเตือนไม่ทำงาน" }
```

Response `data`:
```json
{
  "matched": true,
  "faq_id": 12,
  "question": "...",
  "answer": "...",
  "category": "...",
  "score": 78.5
}
```

---

## 2. Chat Bot เมนูปุ่มกด (ชุดใหม่ — ให้ mobile ใช้ชุดนี้)

### `GET /chatbot/topics`
ดึงเมนูหมวดหลัก 6 หมวด (ไม่ต้องล็อกอิน)

Response `data`: array ของ
```json
{ "id": 1, "key": "security-system", "name_th": "ระบบรักษาความปลอดภัย", "name_en": "Security System", "icon": "fa-shield-halved", "sort_order": 1, "is_active": 1 }
```

### `GET /chatbot/topics/{topicId}/services`
ดึงบริการย่อยของหมวดนั้น (ไม่ต้องล็อกอิน)

⚠️ เส้นนี้คืน **raw row จาก DB** — `extra_info` ยังเป็น JSON string (ยังไม่ decode) และ `has_technician_contact` / `has_purchase_interest` / `has_claim` เป็น `0`/`1` (ไม่ใช่ boolean) ใช้เส้นนี้แค่แสดงชื่อบริการเป็นปุ่มก็พอ ถ้าต้องการรายละเอียดเต็มให้เรียก endpoint ถัดไป

Response `data`: array ของ
```json
{ "id": 5, "topic_id": 1, "key": "burglar-alarm", "name_th": "ระบบสัญญาณกันขโมย (Burglar Alarm System)", "info_th": "...", "info_en": "...", "extra_info": null, "has_technician_contact": 1, "has_purchase_interest": 1, "has_claim": 0, "purchase_link_route": null, "sort_order": 0, "is_active": 1 }
```

### `GET /chatbot/services/{serviceId}`
ดึงรายละเอียดบริการ + FAQ (ไม่ต้องล็อกอิน) — เส้นนี้ข้อมูล **ผ่านการ decode/แปลงชนิดแล้ว** ใช้เส้นนี้ตอนจะแสดงหน้ารายละเอียด

Response `data`:
```json
{
  "id": 5,
  "topic_id": 1,
  "name_th": "ระบบสัญญาณกันขโมย (Burglar Alarm System)",
  "info_th": "...",
  "info_en": "...",
  "extra_info": { "หัวข้อย่อย": "เนื้อหา..." },
  "has_technician_contact": true,
  "has_purchase_interest": true,
  "has_claim": false,
  "purchase_link_route": null,
  "faqs": [
    { "id": 10, "question_th": "สัญญาณเตือนร้องขึ้นมาโดยไม่ทราบสาเหตุ", "answer_th": "..." }
  ]
}
```

`purchase_link_route` (ถ้าไม่เป็น null) คือชื่อ route ฝั่งเว็บ (เช่น `lockers`, `services`) — ฝั่ง mobile ให้ map เป็นหน้าจอในแอปเอง ไม่ใช่ URL ที่ยิงตรงได้

### `POST /chatbot/search`
พิมพ์คำถามอิสระ ให้บอทจับ keyword หาคำตอบ (**ต้องแนบ token ถึงจะใช้ได้ — ไม่งั้นได้ `require_membership: true`**)

Body:
```json
{ "message": "แบตอุปกรณ์อยู่ได้กี่ปี", "topic_id": 1 }
```
`topic_id` ใส่หรือไม่ใส่ก็ได้ — ถ้าใส่จะจำกัดการค้นหาเฉพาะหมวดนั้น ถ้าไม่ใส่ค้นหาทุกหมวด

Response `data` (กรณีตอบได้):
```json
{ "matched": true, "question": "...", "answer": "...", "score": 82.3, "require_membership": false }
```

Response `data` (กรณีตอบไม่ได้ — ต้อง escalate):
```json
{
  "matched": false,
  "question": null,
  "answer": null,
  "score": 12.0,
  "require_membership": false,
  "escalation_message": "ทางเราได้รับเรื่องแล้ว ขออนุญาติส่งต่อเรื่องให้เจ้าหน้าที่ ..."
}
```
`escalation_message` จะเป็นข้อความคนละแบบตามเวลาทำการ (จันทร์-เสาร์ 09:00-18:00 เวลาไทย = ในเวลา / นอกเหนือจากนั้น = นอกเวลาทำการ) — ฝั่ง mobile ควรแสดงข้อความนี้แล้วพาไปหน้าแชทกับเจ้าหน้าที่ต่อ (ของเดิมที่มีอยู่แล้ว: `/support-chats/history`, `/support-chats/send`)

Response `data` (กรณียังไม่ล็อกอิน):
```json
{ "require_membership": true, "matched": false }
```

### `POST /chatbot/leads`
"สนใจซื้อบริการ" หรือ "แจ้งเคลม" — เก็บ lead ให้แอดมินติดต่อกลับ (**ต้องแนบ token — ไม่งั้นได้ `require_membership: true`**)

Body:
```json
{
  "type": "purchase",
  "topic_key": "security-system",
  "service_name": "ระบบสัญญาณกันขโมย (Burglar Alarm System)",
  "name": "สมชาย ใจดี",
  "phone": "0812345678",
  "email": "somchai@example.com",
  "message": "สนใจติดตั้งที่บ้าน 2 ชั้น"
}
```
`type` ต้องเป็น `"purchase"` หรือ `"claim"` เท่านั้น | `name`, `phone` บังคับ | ที่เหลือ optional

Response `data`:
```json
{ "require_membership": false }
```

### `POST /chatbot/rating`
ให้คะแนนความพึงพอใจตอนจบการสนทนา (**ไม่ต้องล็อกอินก็ใช้ได้**)

Body:
```json
{ "rating": 5, "comment": "ตอบเร็วดีค่ะ" }
```
`rating` บังคับ ต้องเป็นเลข 1-5 | `comment` optional

Response: `data` เป็น `null`, `message` = "ขอบคุณสำหรับคะแนนครับ"

---

## หมายเหตุสำหรับ dev mobile

1. เส้นทางทั้งหมดอยู่ภายใต้ `routes/api.php` (ไม่ใช่ web) เรียกได้ตรง ๆ ไม่ต้องผ่านหน้าเว็บ
2. ปุ่ม "ติดต่อเจ้าหน้าที่" / "แจ้งซ่อม" ในสเปกเดิม ให้ผูกกับ endpoint ที่มีอยู่แล้วในระบบ (ไม่ใช่ของใหม่ชุดนี้):
   - ประวัติแชทเจ้าหน้าที่: `GET /support-chats/history` (auth:sanctum)
   - ส่งข้อความหาเจ้าหน้าที่: `POST /support-chats/send` (auth:sanctum)
   - แจ้งซ่อม: ใช้ flow เดิมของแอป (เลือกอุปกรณ์/แพ็กเกจ → แจ้งซ่อม) ไม่มี endpoint ใหม่จาก Chat Bot ที่ระบุอุปกรณ์ให้อัตโนมัติ
3. การจับคำถามเป็น text-matching (similar_text + คำ overlap) ไม่ใช่ AI/LLM — ความแม่นยำขึ้นกับว่าคำถามใกล้เคียงกับข้อมูลที่มีมากแค่ไหน
4. ยังไม่มี endpoint แนบรูป/ไฟล์สำหรับ "แจ้งเคลม" — ตอนนี้เก็บแค่ข้อความ ถ้าต้องการแนบรูปต้องคุยเพิ่มเรื่อง endpoint upload แยก
