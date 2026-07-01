<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exchange_rates', function (Blueprint $table) {


            $table->decimal('usd_variation_percent', 8, 4)
                  ->nullable()
                  ->after('usd_variation');



            $table->decimal('ufv_variation_percent', 8, 4)
                  ->nullable()
                  ->after('ufv_variation');

        });
    }

    public function down(): void
    {
        Schema::table('exchange_rates', function (Blueprint $table) {

            $table->dropColumn([
                'usd_variation',
                'usd_variation_percent',
                'ufv_variation',
                'ufv_variation_percent',
            ]);

        });
    }
};
