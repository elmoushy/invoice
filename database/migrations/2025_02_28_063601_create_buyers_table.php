<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyersTable extends Migration
{
    public function up()
    {
        Schema::create('buyers', function (Blueprint $table) {
            $table->bigIncrements('buyer_id');    // Auto-increment PK
            $table->string('buyer_name', 255);    // NOT NULL
            $table->string('buyer_tax_identifier', 255);    // NOT NULL
            $table->string('legal_identifier', 255)->nullable(); // If provided
            $table->string('tax_identifier', 50)->nullable();    // Mandatory if VAT-registered
            $table->string('electronic_address', 255);           // Must include a valid scheme
            $table->string('address_line1', 255);                // NOT NULL
            $table->string('city', 100);                         // NOT NULL
            $table->string('country_code', 2);                   // NOT NULL (ISO 3166-1)
            $table->string('country_subdivision', 50);           // NOT NULL
            //new column
            $table->string('buyer_legal_registration_type', 50)->nullable(); // BTUA E-16
            $table->string('authority_code', 100)->nullable();               // BTUA E-11
            $table->string('buyer_passport_issuing_country', 2)->nullable(); // BTUA E-19
            $table->string('scheme_identifier_electronic_address', 50);      // IBT-049-1

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('buyers');
    }
}
