<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxSchemeCodeToBuyersAndSellers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // In the buyers table, add tax_scheme_code after buyer_passport_issuing_country
        Schema::table('buyers', function (Blueprint $table) {
            $table->string('tax_scheme_code', 50)->nullable()->after('buyer_passport_issuing_country');
        });

        // In the sellers table, add tax_scheme_code after scheme_identifier
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('tax_scheme_code', 50)->nullable()->after('scheme_identifier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove tax_scheme_code from buyers table
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn('tax_scheme_code');
        });

        // Remove tax_scheme_code from sellers table
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('tax_scheme_code');
        });
    }
}
