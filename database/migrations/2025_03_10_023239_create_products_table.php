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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('kode_produk');
            $table->string('nama_produk');
            $table->decimal('harga', 10, 2);
            $table->string('gambar')->nullable();
            $table->string('barcode')->nullable();
            $table->foreignId('outlet_id')->nullable()->constrained('outlets')->onDelete('cascade');
            $table->foreignId('kategori_id')->nullable()->constrained('categories')->onDelete('cascade');           
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
