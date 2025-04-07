<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class RenameUnitMeasureCodeToInvoicedQuantityUnitCodeOnInvoiceLinesTable extends Migration
{
    public function up()
    {
        // Adjust the column type if necessary.
        DB::statement("ALTER TABLE invoice_lines CHANGE COLUMN unit_measure_code invoiced_quantity_unit_code VARCHAR(255)");
    }

    public function down()
    {
        DB::statement("ALTER TABLE invoice_lines CHANGE COLUMN invoiced_quantity_unit_code unit_measure_code VARCHAR(255)");
    }
}

