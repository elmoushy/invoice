<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('seller_postal_address')->nullable()->after('address_line1'); 
        });
    }

    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('seller_postal_address');
        });
    }
};
