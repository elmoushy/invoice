<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_details', function (Blueprint $table) {
            // If you truly must store the card, make these columns nullable
            $table->string('payment_card_primary_account_number')->nullable();
            $table->string('expiry_date')->nullable();
            $table->string('cvv')->nullable();
        });
    }

    public function down()
    {
        Schema::table('payment_details', function (Blueprint $table) {
            $table->dropColumn([
                'payment_card_primary_account_number',
                'expiry_date',
                'cvv',
            ]);
        });
    }
};
