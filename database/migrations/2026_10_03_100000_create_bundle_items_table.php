<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * «سبد اختصاصی»: a product (category «bundle») made of other products. Its price is the sum of
     * theirs, kept up to date whenever one of them changes price.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // a bundle's line under its name; empty, the site lists what it holds
            $table->text('description')->nullable();
        });

        Schema::create('bundle_items', function (Blueprint $table) {
            $table->id();
            // a bundle goes with its list
            $table->foreignId('bundle_id')->constrained('products')->cascadeOnDelete();
            // a product in a bundle cannot be deleted while it is there: the bundle's price is made of it
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['bundle_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_items');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
