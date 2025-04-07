<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->string('beneficiary_id')->nullable()->after('principal_id');
        });
    }
    
    public function down()
    {
        Schema::table('invoices', function (Illuminate\Database\Schema\Blueprint $table) {
            $table->dropColumn('beneficiary_id');
        });
    }
    
};
