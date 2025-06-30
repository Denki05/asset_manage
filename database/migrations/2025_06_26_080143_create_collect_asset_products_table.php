<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCollectAssetProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('collect_asset_products', function (Blueprint $table) {
            $table->string('id')->primary(); // Gunakan VARCHAR sebagai primary key
            $table->string('product_code')->unique();
            $table->string('product_name');
            $table->string('brand_name')->nullable();       // optional
            $table->string('searah')->nullable();           // berasal dari tabel lain saat sync API
            $table->text('note')->nullable();               // catatan khusus atau deskripsi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('collect_asset_products');
    }
}
