<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->string('customer_name');
            $table->string('customer_mobile', 30);
            $table->string('customer_email')->nullable();
            $table->string('customer_address');
            $table->string('shipping_city', 120)->nullable();
            $table->string('shipping_area', 120)->nullable();
            $table->decimal('subtotal_amount', 15, 2);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('shipping_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('payment_method', 30)->default('cash_on_delivery');
            $table->string('payment_status', 20)->default('due');
            $table->string('order_status', 20)->default('pending');
            $table->text('notes')->nullable();
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
