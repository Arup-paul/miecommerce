<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('miaccounts_external_id')->nullable()->after('id')->index();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('miaccounts_external_id')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('miaccounts_external_id');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('miaccounts_external_id');
        });
    }
};
