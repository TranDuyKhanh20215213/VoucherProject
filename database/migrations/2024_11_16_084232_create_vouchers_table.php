<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('name')->unique(); // Unique name
            $table->text('description'); // Description of the voucher
            $table->integer('type_discount'); // Type of discount (e.g., percentage or fixed amount)
            $table->float('discount_amount', 8, 2); // Discount amount
            $table->timestamp('created');
            $table->timestamp('expired_at'); // Timestamp for expiration
            $table->json('rule'); // Rule stored as JSON
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
}
