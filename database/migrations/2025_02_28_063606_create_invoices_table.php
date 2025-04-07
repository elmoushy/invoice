<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('invoice_id');                  // Primary key
            $table->string('invoice_number', 100)->unique();      // NOT NULL UNIQUE
            $table->date('invoice_issue_date');                   // YYYY-MM-DD format
            $table->string('invoice_type_code', 20)->nullable();  // e.g. "Standard", "Reverse"
            $table->char('invoice_currency_code', 3)->nullable(); // ISO 4217 (e.g., "AED", "USD")
            $table->char('payment_currency_code', 3)->nullable(); // Could be different from invoice_currency_code
            $table->string('tax_registration_identifier', 255)->nullable(); // e.g. FTA ID or TRN
            $table->decimal('invoice_total_line_net_amount', 12, 2)->default(0); // Summation of net line items
            $table->decimal('invoice_total_tax_amount', 12, 2)->default(0);      // Summation of tax amounts
            $table->decimal('invoice_total_with_tax', 12, 2)->default(0);        // Net + Tax
            $table->decimal('invoice_due_for_payment', 12, 2)->default(0);       // Calculated total due
            //new column   
            $table->date('payment_due_date')->nullable();                    // IBT-009
            $table->string('business_process_type', 100);                    // IBT-023
            $table->string('specification_identifier', 100);                 // IBT-024
            $table->decimal('currency_exchange_rate', 10, 6)->nullable();    // BTUA E-04
            $table->decimal('invoice_total_tax_amount_acc_currency', 12, 2)->nullable(); // IBT-111


            // Foreign keys
            $table->unsignedBigInteger('seller_id');  // References sellers
            $table->unsignedBigInteger('buyer_id');   // References buyers

            $table->timestamps();

            // Define foreign key constraints
            $table->foreign('seller_id')
                  ->references('seller_id')->on('sellers')
                  ->onDelete('cascade');

            $table->foreign('buyer_id')
                  ->references('buyer_id')->on('buyers')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
