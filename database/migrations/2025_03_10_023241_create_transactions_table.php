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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->onDelete('restrict');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('ppn', 10, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->string('nomor_invoice');
            $table->enum('payment_method', ['tunai', 'qris', 'kartuKredit']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
