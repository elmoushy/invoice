<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameInvoiceLineBaseAmountToInvoiceLineNetAmountOnInvoiceLinesTable extends Migration
{
    public function up()
    {
        // Adjust the column type as needed.
        DB::statement("ALTER TABLE invoice_lines CHANGE COLUMN invoice_line_base_amount invoice_line_net_amount DECIMAL(15,2)");
    }

    public function down()
    {
        // Revert the change back to the original column name.
        DB::statement("ALTER TABLE invoice_lines CHANGE COLUMN invoice_line_net_amount invoice_line_base_amount DECIMAL(15,2)");
    }
}
