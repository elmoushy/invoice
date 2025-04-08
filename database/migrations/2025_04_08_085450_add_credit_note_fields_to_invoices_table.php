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
            // Make creditNoteRefInvoice numeric. We assume invoice IDs use unsigned big integers.
            $table->unsignedBigInteger('creditNoteRefInvoice')->nullable()->after('invoice_transaction_type_code');
            // Add creditNoteRefInvoice_number as a string.
            $table->string('creditNoteRefInvoice_number')->nullable()->after('creditNoteRefInvoice');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['creditNoteRefInvoice', 'creditNoteRefInvoice_number']);
        });
    }
}
