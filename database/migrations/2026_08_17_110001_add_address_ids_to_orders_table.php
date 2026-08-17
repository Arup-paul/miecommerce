<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('billing_address_id')->nullable()->after('shipping_area')
                ->constrained('addresses')->nullOnDelete();
            $table->foreignId('shipping_address_id')->nullable()->after('billing_address_id')
                ->constrained('addresses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['billing_address_id']);
            $table->dropForeign(['shipping_address_id']);
            $table->dropColumn(['billing_address_id', 'shipping_address_id']);
        });
    }
};
