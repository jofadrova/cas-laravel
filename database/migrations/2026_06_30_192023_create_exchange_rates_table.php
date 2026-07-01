<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();

            $table->date('rate_date')->unique();

            $table->decimal('usd_bob', 10, 4);
            $table->decimal('ufv_bob', 10, 4);

            $table->string('source', 150);
            $table->string('source_url')->nullable();

            $table->timestamp('api_timestamp')->nullable();

            $table->timestamps();

            $table->index('rate_date');
            $table->decimal('usd_variation',10,4)->nullable();
            $table->decimal('ufv_variation',10,4)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
