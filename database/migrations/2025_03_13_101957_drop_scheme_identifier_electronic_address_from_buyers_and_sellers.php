<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropSchemeIdentifierElectronicAddressFromBuyersAndSellers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Drop column from buyers table
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn('scheme_identifier_electronic_address');
        });

        // Drop column from sellers table
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('scheme_identifier_electronic_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Re-add column to buyers table
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('scheme_identifier_electronic_address', 50)->after('buyer_passport_issuing_country');
        });

        // Re-add column to sellers table
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('scheme_identifier_electronic_address', 50)->after('scheme_identifier');
        });
    }
}
