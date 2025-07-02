<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTaxAndIdentifierColumnsToInvoiceLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->string('tax_exemption_reason')->nullable();
            $table->string('tax_exemption_reason_code')->nullable();
            $table->string('scheme_idenifier_IBT_157_1')->nullable();
            // Store as a string to allow leading zeros and to check its length easily
            $table->string('Item_Standard_Identifier')->nullable();
        });

        // Note: SQLite check constraint syntax is different, so we'll skip this for now
        // The application layer should handle validation for Item_Standard_Identifier length
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropColumn('tax_exemption_reason');
            $table->dropColumn('tax_exemption_reason_code');
            $table->dropColumn('scheme_idenifier_IBT_157_1');
            $table->dropColumn('Item_Standard_Identifier');
        });
    }
}
