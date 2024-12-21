<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedemptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id(); // Auto-increment ID field
            $table->foreignId('issuance_id')->constrained('issuances')->onDelete('cascade'); // Foreign key to issuances
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade'); // Foreign key to orders
            $table->timestamp('used_at'); // Timestamp when the redemption is used
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('redemptions');
    }
}
