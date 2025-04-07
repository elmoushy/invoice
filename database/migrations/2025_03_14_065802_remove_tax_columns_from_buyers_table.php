<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('buyers', function (Blueprint $table) {
            $table->dropColumn(['tax_identifier', 'tax_scheme_code']);
        });
    }
    
    public function down()
    {
        Schema::table('buyers', function (Blueprint $table) {
            // Adjust the column definitions as needed (e.g., type, nullable, etc.)
            $table->string('tax_identifier')->nullable();
            $table->string('tax_scheme_code')->nullable();
        });
    }
    
};
