<?php

declare(strict_types=1);

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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete()->index();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete()->index();
            $table->unsignedInteger('quantity');
            $table->decimal('base_unit_price', 15, 2)->unsigned();
            $table->decimal('discount_percent', 5, 2)->unsigned()->default(0);
            $table->decimal('unit_price', 15, 2)->unsigned();
            $table->decimal('line_total', 15, 2)->unsigned();
            $table->timestamps();

            $table->unique(['order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
