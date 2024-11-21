<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVouchersTable extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id(); // Auto-increment ID field
            $table->string('name');
            $table->text('description');
            $table->boolean('type_discount');
            $table->float('discount_amount', 8, 2); // Example: 10.00
            $table->timestamp('created_at');
            $table->timestamp('expired_at');

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
}
