<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMissingFieldsToInvoiceAndPaymentTables extends Migration
{
    public function up()
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_lines', 'discount_value')) {
                $table->decimal('discount_value', 10, 2)->nullable()->after('invoice_line_net_amount');
            }
            if (!Schema::hasColumn('invoice_lines', 'invoiced_item_tax_category_code')) {
                $table->string('invoiced_item_tax_category_code', 50)->nullable()->after('discount_value');
            }
            if (!Schema::hasColumn('invoice_lines', 'invoice_total_line_net_amount')) {
                $table->string('invoice_total_line_net_amount', 50)->nullable()->after('invoiced_item_tax_category_code');
            }
            if (!Schema::hasColumn('invoice_lines', 'invoice_total_tax_amount')) {
                $table->string('invoice_total_tax_amount', 50)->nullable()->after('invoice_total_line_net_amount');
            }
            if (!Schema::hasColumn('invoice_lines', 'invoice_total_with_tax')) {
                $table->string('invoice_total_with_tax', 50)->nullable()->after('invoice_total_tax_amount');
            }
            if (!Schema::hasColumn('invoice_lines', 'invoice_due_for_payment')) {
                $table->string('invoice_due_for_payment', 50)->nullable()->after('invoice_total_with_tax');
            }
        });
    
        // Schema::table('payment_details', function (Blueprint $table) {
        //     if (!Schema::hasColumn('payment_details', 'expiry_date')) {
        //         $table->string('expiry_date', 5)->nullable()->after('payment_card_primary_account_number');
        //     }
        //     if (!Schema::hasColumn('payment_details', 'cvv')) {
        //         $table->string('cvv', 4)->nullable()->after('expiry_date');
        //     }
        // });        
    }
    
    public function down()
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropColumn([
                'discount_value',
                'invoiced_item_tax_category_code',
                'invoice_total_line_net_amount',
                'invoice_total_tax_amount',
                'invoice_total_with_tax',
                'invoice_due_for_payment'
            ]);
        });
    
        // Schema::table('payment_details', function (Blueprint $table) {
        //     $table->dropColumn([
        //         'expiry_date',
        //         'cvv'
        //     ]);
        // });
    }
    
}
