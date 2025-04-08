<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditNoteFieldsToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('creditNoteRefInvoice')->nullable()->after('invoice_transaction_type_code');
            $table->string('correction_method')->nullable()->after('creditNoteRefInvoice');
            $table->string('reason_for_credit_note')->nullable()->after('correction_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['creditNoteRefInvoice', 'correction_method', 'reason_for_credit_note']);
        });
    }
}
