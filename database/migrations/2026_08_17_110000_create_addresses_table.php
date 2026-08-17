<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('label')->nullable();
            $table->string('recipient_name');
            $table->string('phone');
            $table->string('address_line');
            $table->string('city')->nullable();
            $table->string('area')->nullable();
            $table->boolean('is_default_billing')->default(false);
            $table->boolean('is_default_shipping')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_default_billing'], 'default_billing_address_per_user');
            $table->index(['user_id', 'is_default_shipping'], 'default_shipping_address_per_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
