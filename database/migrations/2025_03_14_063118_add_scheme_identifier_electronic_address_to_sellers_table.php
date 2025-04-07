<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sellers', function (Blueprint $table) {
            if (!Schema::hasColumn('sellers', 'scheme_identifier_electronic_address')) {
                $table->string('scheme_identifier_electronic_address')->nullable()->after('scheme_identifier');
            }
        });
    }
    
    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            if (Schema::hasColumn('sellers', 'scheme_identifier_electronic_address')) {
                $table->dropColumn('scheme_identifier_electronic_address');
            }
        });
    }
    
};
