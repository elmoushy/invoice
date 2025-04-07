<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('contract_reference')->nullable();
            $table->decimal('contract_value', 15, 2)->nullable();
            $table->string('billing_frequency')->nullable();
            $table->text('invoice_note')->nullable();
        });
    }
    
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'contract_reference',
                'contract_value',
                'billing_frequency',
                'invoice_note'
            ]);
        });
    }
    
};
