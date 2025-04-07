<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSellerPostalAddressToBuyersAndSellers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the seller_postal_address column to buyers table
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('seller_postal_address', 255)->nullable()->after('country_subdivision');
        });

        // Add the seller_postal_address column to sellers table
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('seller_postal_address', 255)->nullable()->after('country_subdivision');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the seller_postal_address column from buyers table
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn('seller_postal_address');
        });

        // Remove the seller_postal_address column from sellers table
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('seller_postal_address');
        });
    }
}
