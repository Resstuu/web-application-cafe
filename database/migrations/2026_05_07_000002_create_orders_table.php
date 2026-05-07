<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('customer_name');
            $table->string('table_number');
            $table->enum('source', ['customer', 'kasir'])->default('customer');
            $table->enum('status', ['pending_payment', 'confirmed', 'partially_done', 'done', 'cancelled', 'payment_failed'])->default('pending_payment');
            $table->enum('payment_status', ['belum_bayar', 'pending', 'lunas', 'gagal'])->default('pending');
            $table->unsignedInteger('total')->default(0);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
