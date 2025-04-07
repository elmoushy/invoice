<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            if (!Schema::hasColumn('buyers', 'scheme_identifier_electronic_address')) {
                $table->string('scheme_identifier_electronic_address', 50)->after('scheme_identifier')->nullable();
            }
        });
    }
    
    public function down(): void
    {
        Schema::table('buyers', function (Blueprint $table) {
            if (Schema::hasColumn('buyers', 'scheme_identifier_electronic_address')) {
                $table->dropColumn('scheme_identifier_electronic_address');
            }
        });
    }
    
};
