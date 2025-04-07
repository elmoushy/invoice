<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentDetailsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_details', function (Blueprint $table) {
            $table->bigIncrements('payment_id');       // Primary key
            $table->unsignedBigInteger('invoice_id');  // FK to invoices
            $table->date('payment_date')->nullable();  // YYYY-MM-DD if provided
            $table->string('payment_means_type_code', 20);    // e.g. "credit_transfer", "cash"
            $table->string('payment_account_identifier', 100); // Required if means_type_code=credit_transfer
            $table->decimal('paid_amount', 12, 2)->nullable(); // Amount paid
            $table->decimal('rounding_amount', 12, 2)->nullable(); // If any rounding adjustments
            $table->decimal('amount_due_for_payment', 12, 2)->nullable(); 
            // = invoice_total_with_tax - paid_amount + rounding_amount (calculated or stored)
            //new column
            $table->string('scheme_identifier', 50)->nullable();             // IBT-084-1
            $table->string('payment_account_name', 255)->nullable();         // IBT-085
            $table->string('payment_service_provider_id', 100)->nullable();  // IBT-086
            $table->string('payment_card_number', 20)->nullable();           // IBT-087


            $table->timestamps();

            // Foreign key
            $table->foreign('invoice_id')
                  ->references('invoice_id')->on('invoices')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_details');
    }
}