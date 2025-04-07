<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxBreakdownsTable extends Migration
{
    public function up()
    {
        Schema::create('tax_breakdowns', function (Blueprint $table) {
            $table->bigIncrements('tax_id');           // Primary key
            $table->unsignedBigInteger('invoice_id');  // FK to invoices
            $table->string('tax_category_code', 50);   // e.g. "Standard rate", "Reverse charge", etc.
            $table->decimal('tax_category_rate', 5, 2)->nullable(); // e.g. 5.00
            $table->decimal('taxable_amount', 12, 2);  // Sum of net amounts for this category
            $table->decimal('tax_amount', 12, 2);      // = taxable_amount * tax_category_rate / 100

            $table->timestamps();

            // Foreign key
            $table->foreign('invoice_id')
                  ->references('invoice_id')->on('invoices')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tax_breakdowns');
    }
}