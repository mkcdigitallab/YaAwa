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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color', 20);
            $table->string('image', 2048);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('origin')->nullable();
            $table->string('unit', 100);
            $table->string('image', 2048);
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('salePrice')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('version')->default(1);
            $table->boolean('active')->default(true);
            $table->boolean('featured')->default(false);
            $table->index(['active', 'category_id'], 'products_catalog_index');
            $table->timestamps();
        });

        Schema::create('deliveryZones', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('fee')->nullable();
            $table->timestamps();
        });
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('percentage');
            $table->boolean('active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->uuid('checkout_tocken')->unique();
            $table->foreignId('deleveryzone_id');
            $table->string('zone_name');
            $table->text('adresse')->nullable();
            $table->string('fulfillmentMode', 20);
            $table->text('note')->nullable();
            $table->unsignedBigInteger('sub_total');
            $table->unsignedTinyInteger('discount_percentage')->default(0);
            $table->unsignedBigInteger('discount_amount')->default(0);
            $table->unsignedBigInteger('total');
            $table->string('statut', 20)->default("en_attente");
            $table->boolean('stock_applied')->default(false);
            $table->boolean('stock_restored')->default(false);
            $table->timestamp('canceled_at')->nullable();
            $table->index(['statut', 'created_at'], 'orders_statut_index');

            $table->timestamps();
        });
        Schema::create('orders_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('products_id');
            $table->string('name');
            $table->string('unit', 100);
            $table->unsignedBigInteger('unit_price');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('line_total');
            $table->timestamps();
            $table->unique(['order_id', 'products_id']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('promotions');
        Schema::dropIfExists('deliveryZones');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
