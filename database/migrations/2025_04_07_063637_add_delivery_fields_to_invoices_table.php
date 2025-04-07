<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryFieldsToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('deliver_to_address_line_1')->nullable();
            $table->string('deliver_to_address_line_2')->nullable();
            $table->string('deliver_to_address_line_3')->nullable();
            $table->string('deliver_to_post_code')->nullable();
            $table->string('deliver_to_country_code')->nullable();
            $table->string('deliver_to_country_subdivision')->nullable();
            $table->string('deliver_to_city')->nullable();
            $table->string('deliver_to_party_name')->nullable();
            $table->string('deliver_to_location_identifier')->nullable();
            $table->string('location_scheme_identifier')->nullable();
            $table->date('actual_delivery_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'deliver_to_address_line_1',
                'deliver_to_address_line_2',
                'deliver_to_address_line_3',
                'deliver_to_post_code',
                'deliver_to_country_code',
                'deliver_to_country_subdivision',
                'deliver_to_city',
                'deliver_to_party_name',
                'deliver_to_location_identifier',
                'location_scheme_identifier',
                'actual_delivery_date',
            ]);
        });
    }
}
