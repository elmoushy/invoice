<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUnnecessaryColumnsFromSellersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Drops the following columns:
     * - tax_identifier: Not provided by the form.
     * - tax_scheme_code: Shown conditionally but not reliably sent.
     * - seller_postal_address: Computed server-side.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['tax_identifier', 'tax_scheme_code', 'seller_postal_address']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * Re-adds the dropped columns in case of rollback.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('tax_identifier')->nullable();
            $table->string('tax_scheme_code')->nullable();
            $table->string('seller_postal_address')->nullable();
        });
    }
}

