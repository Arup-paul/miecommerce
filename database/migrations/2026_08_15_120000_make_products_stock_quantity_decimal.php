<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * MiAccounts sells by weight and volume, so stock arrives fractional.
     *
     * An integer column silently rounded 16606.5 to 16607, overstating stock
     * on every fractional product. Decimal(15,4) matches the precision the
     * ERP ledger actually carries.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE products MODIFY stock_quantity DECIMAL(15,4) NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE products MODIFY stock_quantity INT NOT NULL DEFAULT 0');
    }
};
