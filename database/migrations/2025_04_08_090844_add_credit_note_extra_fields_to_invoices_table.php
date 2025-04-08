<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditNoteExtraFieldsToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Only add the columns if they do not exist
            if (!Schema::hasColumn('invoices', 'correction_method')) {
                $table->string('correction_method')->nullable()->after('creditNoteRefInvoice_number');
            }
            if (!Schema::hasColumn('invoices', 'reason_for_credit_note')) {
                $table->string('reason_for_credit_note')->nullable()->after('correction_method');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['correction_method', 'reason_for_credit_note']);
        });
    }
}
