<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellersTable extends Migration
{
    public function up()
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->bigIncrements('seller_id');   // Auto-increment PK
            $table->string('seller_name', 255);   // NOT NULL
            $table->string('seller_tax_identifier', 255)->nullable();   // may be NULL
            $table->string('legal_identifier', 255)->nullable();  // If provided
            $table->string('tax_identifier', 50)->nullable();     // Optional/Conditional
            $table->string('electronic_address', 255);            // Must include a valid scheme
            $table->string('address_line1', 255);                 // NOT NULL
            $table->string('city', 100);                          // NOT NULL
            $table->string('country_code', 2);                    // NOT NULL (ISO 3166-1)
            $table->string('country_subdivision', 50);            // NOT NULL (e.g. state/emirate)
            //new column
            $table->string('seller_legal_registration_type', 50)->nullable(); // BTUA E-15
            $table->string('authority_name', 100)->nullable();                // BTUA E-12
            $table->string('passport_issuing_country_code', 2)->nullable();   // BTUA E-18
            $table->string('scheme_identifier', 50)->nullable();              // IBT-030-1
            $table->string('scheme_identifier_electronic_address', 50);       // IBT-034-1

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sellers');
    }
}
