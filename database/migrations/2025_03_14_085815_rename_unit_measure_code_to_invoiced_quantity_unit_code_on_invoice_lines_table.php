<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RenameUnitMeasureCodeToInvoicedQuantityUnitCodeOnInvoiceLinesTable extends Migration
{
    public function up()
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            // Add the new column
            $table->string('invoiced_quantity_unit_code', 255)->nullable();
        });

        // Copy data from old column to new column
        DB::statement('UPDATE invoice_lines SET invoiced_quantity_unit_code = unit_measure_code');

        Schema::table('invoice_lines', function (Blueprint $table) {
            // Drop the old column
            $table->dropColumn('unit_measure_code');
        });
    }

    public function down()
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            // Add the old column back
            $table->string('unit_measure_code', 255)->nullable();
        });

        // Copy data back
        DB::statement('UPDATE invoice_lines SET unit_measure_code = invoiced_quantity_unit_code');

        Schema::table('invoice_lines', function (Blueprint $table) {
            // Drop the new column
            $table->dropColumn('invoiced_quantity_unit_code');
        });
    }
}

