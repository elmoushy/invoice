<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameInvoiceLineBaseAmountToInvoiceLineNetAmountOnInvoiceLinesTable extends Migration
{
    public function up()
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            // Add the new column
            $table->decimal('invoice_line_net_amount', 15, 2)->nullable();
        });

        // Copy data from old column to new column
        DB::statement('UPDATE invoice_lines SET invoice_line_net_amount = invoice_line_base_amount');

        Schema::table('invoice_lines', function (Blueprint $table) {
            // Drop the old column
            $table->dropColumn('invoice_line_base_amount');
        });
    }

    public function down()
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            // Add the old column back
            $table->decimal('invoice_line_base_amount', 15, 2)->nullable();
        });

        // Copy data back
        DB::statement('UPDATE invoice_lines SET invoice_line_base_amount = invoice_line_net_amount');

        Schema::table('invoice_lines', function (Blueprint $table) {
            // Drop the new column
            $table->dropColumn('invoice_line_net_amount');
        });
    }
}
