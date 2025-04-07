<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSchemeIdentifierElectronicAddressToBuyersAndSellers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add the column to the buyers table
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('scheme_identifier_electronic_address', 50)->nullable()->after('buyer_passport_issuing_country');
        });

        // Add the column to the sellers table
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('scheme_identifier_electronic_address', 50)->nullable()->after('scheme_identifier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the column from the buyers table
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn('scheme_identifier_electronic_address');
        });

        // Remove the column from the sellers table
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('scheme_identifier_electronic_address');
        });
    }
}
