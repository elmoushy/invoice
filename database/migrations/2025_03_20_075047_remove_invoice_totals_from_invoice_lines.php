<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_total_line_net_amount',
                'invoice_total_tax_amount',
                'invoice_total_with_tax',
                'invoice_due_for_payment'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->string('invoice_total_line_net_amount', 50)->nullable()->after('invoiced_item_tax_category_code');
            $table->string('invoice_total_tax_amount', 50)->nullable()->after('invoice_total_line_net_amount');
            $table->string('invoice_total_with_tax', 50)->nullable()->after('invoice_total_tax_amount');
            $table->string('invoice_due_for_payment', 50)->nullable()->after('invoice_total_with_tax');
        });
    }
};
