<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 🌟 ระบบใบแจ้งหนี้รายเดือนอัตโนมัติ (Invoice / Billing System)
 * รองรับ 2 รูปแบบตามที่แจ้งไว้:
 *   1. type=contract  → ใบแจ้งหนี้จากสัญญา/บริการรายเดือน (service_contracts) เช่น ค่าบำรุงรักษารายเดือน
 *   2. type=statement → ใบแจ้งหนี้/ใบกำกับภาษีสรุปยอดรายเดือน (รวมคำสั่งซื้อ/บริการของลูกค้าในเดือนนั้น)
 * ทั้งสองแบบเลือกช่องทางจ่ายได้ต่อลูกค้า/สัญญา (gateway ผ่าน BBL เดิม หรือ bank_transfer แนบสลิปเอง)
 *
 * หมายเหตุสำคัญ (Thai Tax Law): invoice_number ต้องเรียงลำดับไม่ขาดตอนต่อเดือน (ดู
 * InvoiceGenerationService::nextInvoiceNumber() ที่ lockForUpdate ตอนออกเลขที่)
 *
 * ⚠️ ไม่ได้ใส่ DB unique constraint กันใบแจ้งหนี้ซ้ำ เพราะ MySQL unique index มองว่า NULL
 * แต่ละแถวไม่เท่ากัน (service_contract_id เป็น NULL ทุกแถวของ type=statement) — กันซ้ำด้วยการเช็ค
 * exists() ในโค้ดแทน (ดู InvoiceGenerationService)
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. ตารางสัญญา/บริการรายเดือน (Service Contract) — ต้นทางของใบแจ้งหนี้ type=contract
        Schema::create('service_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique(); // เช่น CTR-2026-0001
            // 🌟 ไม่ใส่ onDelete('cascade') ตั้งใจ — กันลบ user ที่มีสัญญา/ประวัติการเงินอยู่ทิ้งไปเฉยๆ (เหมือน orders.user_id เดิม)
            $table->foreignId('user_id')->constrained('users');
            // 🌟 ผูกกับแพ็กเกจ/สินค้าที่ลูกค้าซื้อไว้ได้ (ไม่บังคับ) เช่น สัญญาบำรุงรักษาของแพ็กเกจกล้องวงจรปิด
            $table->foreignId('customer_product_id')->nullable()->constrained('customer_products')->nullOnDelete();
            $table->string('title'); // เช่น "สัญญาบำรุงรักษารายเดือน - กล้องวงจรปิด"
            $table->text('description')->nullable();
            $table->decimal('billing_amount', 12, 2); // ยอดที่เรียกเก็บทุกรอบบิล
            $table->unsignedTinyInteger('billing_day')->default(1); // วันที่ของเดือนที่จะออกบิล (1-28)
            $table->enum('payment_method', ['gateway', 'bank_transfer'])->default('bank_transfer');
            $table->date('start_date');
            $table->date('end_date')->nullable(); // ว่าง = ไม่มีกำหนดสิ้นสุด
            $table->enum('status', ['active', 'paused', 'cancelled', 'completed'])->default('active');
            $table->date('next_billing_month')->nullable(); // เดือนถัดไปที่ต้องออกบิล (เก็บเป็นวันที่ 1 ของเดือน)
            $table->date('last_invoiced_month')->nullable(); // เดือนล่าสุดที่ออกบิลไปแล้ว
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'next_billing_month']);
        });

        // 2. ตารางใบแจ้งหนี้ (Invoice) — ใช้ร่วมกันทั้ง 2 แบบ แยกด้วยคอลัมน์ type
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // เช่น INV-202610-0001 (เรียงลำดับไม่ขาดตอนต่อเดือน)
            $table->enum('type', ['contract', 'statement']);
            // 🌟 ไม่ใส่ onDelete('cascade') ตั้งใจ — ใบแจ้งหนี้เป็นเอกสารทางบัญชี ต้องเก็บไว้แม้ user จะถูกลบ (เหมือน orders.user_id เดิม)
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('service_contract_id')->nullable()->constrained('service_contracts')->nullOnDelete();
            $table->date('billing_month'); // เดือนที่ใบแจ้งหนี้นี้ครอบคลุม (เก็บเป็นวันที่ 1 ของเดือน)
            $table->date('issue_date');
            $table->date('due_date');

            // 🌟 Snapshot ข้อมูลลูกค้า ณ วันที่ออกบิล กันกรณีลูกค้าแก้ไขโปรไฟล์ภายหลัง แต่ใบกำกับภาษีเก่าต้องคงเดิม
            $table->string('customer_name_snapshot')->nullable();
            $table->string('customer_tax_id_snapshot', 20)->nullable();
            $table->string('customer_branch_snapshot', 100)->nullable();
            $table->text('customer_address_snapshot')->nullable();

            $table->decimal('subtotal', 12, 2);
            $table->decimal('vat_amount', 12, 2)->default(0); // VAT 7% เฉพาะกรณีลูกค้ามีเลขผู้เสียภาษี (ใบกำกับภาษีเต็มรูป)
            $table->decimal('total_amount', 12, 2);

            $table->enum('payment_method', ['gateway', 'bank_transfer']);
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->dateTime('paid_at')->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->string('payment_slip_url')->nullable(); // สลิปโอนเงินที่ลูกค้าแนบ (bank_transfer)
            $table->text('admin_note')->nullable();
            $table->enum('generated_by', ['cron', 'manual'])->default('cron');
            $table->timestamps();

            $table->index(['user_id', 'type', 'billing_month']);
            $table->index(['status', 'due_date']);
        });

        // 3. ตารางรายการในใบแจ้งหนี้ (Invoice Items)
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->string('description');
            $table->string('reference_type')->nullable(); // order / customer_product / contract / adjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('amount', 12, 2);
            $table->timestamps();
        });

        // 4. ตั้งค่าระดับลูกค้าสำหรับใบแจ้งหนี้รายเดือนแบบสรุปยอด (type=statement)
        //    - is_invoice_customer: ลูกค้าประเภท "วางบิลรายเดือน" (ปกติเป็นลูกค้าองค์กร/มีเลขผู้เสียภาษี) — คำสั่งซื้อของ
        //      ลูกค้ากลุ่มนี้ที่ยังไม่ชำระ (pending_payment) จะถูกรวบมาออกเป็นใบแจ้งหนี้เดียวตอนสิ้นเดือน แทนที่จะจ่ายทีละออเดอร์
        //    - invoice_payment_method: ช่องทางจ่ายเงินของใบแจ้งหนี้แบบสรุปยอด (type=contract ใช้ payment_method ของ
        //      service_contracts แทน — ตามที่กำหนดว่า "เลือกได้ต่อลูกค้า/สัญญา")
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->boolean('is_invoice_customer')->default(false)->after('branch');
            $table->enum('invoice_payment_method', ['gateway', 'bank_transfer'])->default('bank_transfer')->after('is_invoice_customer');
        });
    }

    public function down(): void
    {
        Schema::table('customer_profiles', function (Blueprint $table) {
            $table->dropColumn(['invoice_payment_method', 'is_invoice_customer']);
        });

        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('service_contracts');
    }
};
