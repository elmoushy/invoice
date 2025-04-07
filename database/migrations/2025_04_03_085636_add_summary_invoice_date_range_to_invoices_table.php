<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSummaryInvoiceDateRangeToInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('summary_invoice_start_date')->nullable()->after('principal_id');
            $table->date('summary_invoice_end_date')->nullable()->after('summary_invoice_start_date');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['summary_invoice_start_date', 'summary_invoice_end_date']);
        });
    }
}
