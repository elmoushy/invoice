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
        Schema::table('payment_details', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_details', 'credit_transfer')) {
                $table->string('credit_transfer')->nullable()->after('payment_date');
            }
            if (!Schema::hasColumn('payment_details', 'scheme_identifier')) {
                $table->string('scheme_identifier')->nullable()->after('credit_transfer');
            }
            if (!Schema::hasColumn('payment_details', 'payment_service_provider_identifier')) {
                $table->string('payment_service_provider_identifier')->nullable()->after('scheme_identifier');
            }
            if (!Schema::hasColumn('payment_details', 'payment_account_name')) {
                $table->string('payment_account_name')->nullable()->after('payment_service_provider_identifier');
            }
            if (!Schema::hasColumn('payment_details', 'payment_account_identifier')) {
                $table->string('payment_account_identifier')->nullable()->after('payment_account_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_details', function (Blueprint $table) {
            $table->dropColumn([
                'credit_transfer',
                'scheme_identifier',
                'payment_service_provider_identifier',
                'payment_account_name',
                'payment_account_identifier'
            ]);
        });
    }
};
