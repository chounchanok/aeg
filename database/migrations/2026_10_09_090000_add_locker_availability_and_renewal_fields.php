<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('smart_locker_unavailable_dates')) {
            Schema::create('smart_locker_unavailable_dates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('smart_locker_id')->constrained('smart_lockers')->cascadeOnDelete();
                $table->date('date');
                $table->string('note')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->unique(['smart_locker_id', 'date']);
            });
        }

        if (Schema::hasTable('locker_bookings')) {
            Schema::table('locker_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('locker_bookings', 'address_id')) {
                    $table->unsignedBigInteger('address_id')->nullable();
                }
                if (!Schema::hasColumn('locker_bookings', 'custom_address_text')) {
                    $table->text('custom_address_text')->nullable();
                }
                if (!Schema::hasColumn('locker_bookings', 'gateway_transaction_id')) {
                    $table->string('gateway_transaction_id')->nullable();
                }
                if (!Schema::hasColumn('locker_bookings', 'gateway_response')) {
                    $table->json('gateway_response')->nullable();
                }
                if (!Schema::hasColumn('locker_bookings', 'renewal_of_booking_id')) {
                    $table->unsignedBigInteger('renewal_of_booking_id')->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('smart_locker_unavailable_dates');

        if (Schema::hasTable('locker_bookings')) {
            Schema::table('locker_bookings', function (Blueprint $table) {
                foreach (['address_id', 'custom_address_text', 'gateway_transaction_id', 'gateway_response', 'renewal_of_booking_id'] as $column) {
                    if (Schema::hasColumn('locker_bookings', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
