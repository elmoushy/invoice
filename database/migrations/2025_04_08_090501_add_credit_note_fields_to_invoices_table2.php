<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditNoteFieldsToInvoicesTable2 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Change creditNoteRefInvoice to an unsigned big integer so it is stored as a number.
            $table->unsignedBigInteger('creditNoteRefInvoice')->nullable()->after('invoice_transaction_type_code');
            // Add creditNoteRefInvoice_number as a string.
            $table->string('creditNoteRefInvoice_number')->nullable()->after('creditNoteRefInvoice');
            // Add correction_method and reason_for_credit_note as strings.
            $table->string('correction_method')->nullable()->after('creditNoteRefInvoice_number');
            $table->string('reason_for_credit_note')->nullable()->after('correction_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'creditNoteRefInvoice',
                'creditNoteRefInvoice_number',
                'correction_method',
                'reason_for_credit_note'
            ]);
        });
    }
}
