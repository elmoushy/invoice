<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceLinesTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->bigIncrements('line_id');                 // Primary key
            $table->unsignedBigInteger('invoice_id');         // FK to invoices
            $table->string('invoice_line_identifier', 50);    // Unique within the invoice
            $table->decimal('invoiced_quantity', 10, 2);      // NOT NULL
            $table->string('unit_measure_code', 10);          // NOT NULL (e.g. "PCS", "KG", etc.)
            $table->decimal('item_net_price', 12, 2);         // Must be >= 0
            $table->decimal('item_gross_price', 12, 2);       // Must be >= item_net_price
            $table->text('item_description');                 // Detailed item info
            $table->string('item_classification', 255)->nullable(); 
            // "If provided" suggests it could be NULL if not applicable

            // Calculated: invoiced_quantity * item_net_price, minus allowances, etc.
            $table->decimal('invoice_line_base_amount', 12, 2);

            //new column
            $table->decimal('item_price_base_quantity', 10, 2)->default(1);      // IBT-149
            $table->decimal('invoiced_item_tax_rate', 5, 2)->nullable();         // IBT-152
            $table->string('item_name', 255);                                    // IBT-153
            $table->decimal('vat_line_amount', 12, 2)->default(0);               // BTUA E-08
            $table->string('item_type', 50)->nullable();                         // BTUA E-13
            $table->string('classification_scheme_identifier', 10)->nullable();  // IBT-158-1
            $table->string('sac_scheme_identifier', 10)->nullable();             // BTUAE-17-1


            $table->timestamps();

            // Foreign key to invoices
            $table->foreign('invoice_id')
                  ->references('invoice_id')->on('invoices')
                  ->onDelete('cascade');

            // Ensure uniqueness of invoice_line_identifier within the same invoice
            $table->unique(['invoice_id', 'invoice_line_identifier']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_lines');
    }
}