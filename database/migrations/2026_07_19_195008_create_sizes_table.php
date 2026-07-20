<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        // حذف جدول‌های قبلی (اگر وجود دارند)
        Schema::dropIfExists('product_size');
        Schema::dropIfExists('sizes');

        // ایجاد یک جدول واحد برای سایزهای محصولات
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('size_name'); // مثلاً: S, M, L, XL
            $table->boolean('stock')->default(0);
            $table->timestamps();

            // هر محصول فقط یک بار می‌تواند یک سایز خاص داشته باشد
            $table->unique(['product_id', 'size_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sizes');
    }
};
