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
        Schema::create('eligibilities', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->unsignedBigInteger('voucher_id'); // Foreign key to vouchers
            $table->unsignedBigInteger('product_id'); // Foreign key to products

            // Foreign key constraints
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eligibilities');
    }
};
