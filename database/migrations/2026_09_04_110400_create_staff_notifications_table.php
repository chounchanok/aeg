<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * แจ้งเตือนภายในระบบหลังบ้าน — ตอบโจทย์เมล QA ข้อ 5 บางส่วน ("ควรหลีกเลี่ยงการให้
 * พนักงานนำข้อมูลจากแอปพลิเคชันไปบันทึกใหม่ในระบบอื่น") โดยส่งแจ้งเตือนไปยัง "แผนก"
 * (role) ที่เกี่ยวข้องโดยอัตโนมัติเมื่อมีรายการใหม่เข้ามา (แจ้งซ่อม/คำสั่งซื้อ/ขอใบเสนอราคา/
 * ติดต่อฝ่ายขาย) แทนที่จะต้องคอยเข้าไปเช็คเองหรือมีคนบอกต่อ
 *
 * ขอบเขต (สำคัญ): นี่เป็นการแจ้งเตือนแบบ "shared inbox ต่อแผนก" ไม่ใช่ workflow engine
 * เต็มรูปแบบตามที่อธิบายไว้ในเมล (ใบเสนอราคา→อนุมัติ→คลังจัดเตรียม→มอบหมายช่าง→ปิดงาน
 * →ออกเอกสารรับประกัน) เพราะงานนั้นใหญ่กว่ามากและต้องออกแบบ state machine ใหม่ทั้งระบบ
 * — role_id ไม่ null หมายถึงแจ้งไปยังทุกคนในแผนกนั้น (is_read เป็นสถานะร่วมของทั้งแผนก
 * ใครกดอ่านแล้วถือว่าแผนกนั้นเห็นแล้ว ไม่ใช่ per-user read state)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('cascade')
                ->comment('แจ้งไปยังทุกคนในแผนกนี้ (shared) — ถ้า null ให้ดูที่ user_id แทน');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')
                ->comment('แจ้งเจาะจงคนเดียว — ใช้แทน role_id ในกรณีที่ต้องการ');
            $table->string('title');
            $table->text('body');
            $table->string('link_url')->nullable()->comment('ลิงก์ไปหน้ารายการที่เกี่ยวข้องในระบบ admin');
            $table->string('type')->nullable()->comment('service_request, order, quote_request, contact_admin');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_notifications');
    }
};
