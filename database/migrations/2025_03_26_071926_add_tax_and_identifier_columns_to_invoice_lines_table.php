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

        // Add a check constraint to ensure Item_Standard_Identifier is 8, 12, 13, or 14 characters long
        DB::statement("
            ALTER TABLE invoice_lines 
            ADD CONSTRAINT chk_Item_Standard_Identifier_length 
            CHECK (CHAR_LENGTH(Item_Standard_Identifier) IN (8, 12, 13, 14))
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the check constraint (note: the syntax may vary depending on your database)
        DB::statement("ALTER TABLE invoice_lines DROP CONSTRAINT IF EXISTS chk_Item_Standard_Identifier_length");

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropColumn('tax_exemption_reason');
            $table->dropColumn('tax_exemption_reason_code');
            $table->dropColumn('scheme_idenifier_IBT_157_1');
            $table->dropColumn('Item_Standard_Identifier');
        });
    }
}
