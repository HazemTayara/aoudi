<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menafest_id')->constrained()->onDelete('cascade'); // المنفست
            $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete(); // الرحلة
            $table->string('order_number'); // الايصال
            $table->string('content')->default('طرد'); // المحتوى
            $table->integer('count')->default(1); // العدد
            $table->string('sender'); // المرسل
            $table->string('recipient'); //المرسل اليه
            $table->enum('pay_type', ['مسبق', 'تحصيل']); // نوع الدفع
            $table->decimal('amount', 10, 2)->default(0); // الملبغ
            $table->decimal('anti_charger', 10, 2)->default(0); // ضد الشحن
            $table->decimal('transmitted', 10, 2)->default(0); // المحول
            $table->decimal('miscellaneous', 10, 2)->default(0); //متفرقات متنوعة
            $table->decimal('discount', 10, 2)->default(0); // الخصم
            $table->boolean('is_paid')->default(false); // تم الاستلام
            $table->dateTime('paid_at')->nullable()->default(null); // تم الأستلام بتاريخ 
            $table->boolean('is_exist')->default(true); // Fixed syntax, added default
            $table->string('notes')->nullable()->default(null); // ملاحظات
            $table->dateTime('assigned_at')->nullable()->default(null); // تاريخ الأضافة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};