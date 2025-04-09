<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        'invoice_number',
        'invoice_issue_date',
        'invoice_type_code',
        'invoice_currency_code',
        'payment_currency_code',
        'tax_registration_identifier',
        'invoice_total_line_net_amount',
        'invoice_total_tax_amount',
        'invoice_total_tax_amount_acc_currency',
        'invoice_total_with_tax',
        'invoice_due_for_payment',
        'payment_due_date',
        'business_process_type',
        'specification_identifier',
        'currency_exchange_rate',
        'principal_id',
        'summary_invoice_start_date',
        'summary_invoice_end_date',
        'seller_id',
        'buyer_id',
        'invoice_transaction_type_code',
        'contract_reference',
        'contract_value',
        'billing_frequency',
        'invoice_note',
        'beneficiary_id',
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
        'creditNoteRefInvoice',
        'creditNoteRefInvoice_number',
        'correction_method',
        'reason_for_credit_note',
    ];

    public function seller()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class, 'invoice_id');
    }

    public function taxBreakdowns()
    {
        return $this->hasMany(TaxBreakdown::class, 'invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(PaymentDetail::class, 'invoice_id');
    }

    /**
     * Boot method to add model-level validation.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($invoice) {
            $typeCode = $invoice->invoice_transaction_type_code ?? '';

            // 6th digit (index 5) is '1' → principal_id required
            if (strlen($typeCode) === 8 && substr($typeCode, 5, 1) === '1') {
                if (empty($invoice->principal_id)) {
                    throw new \Exception('The principal_id field is required when Disclosed Agent Billing is selected.');
                }
            }

            // principal_id must not equal seller tax ID
            if (!empty($invoice->principal_id)) {
                $seller = $invoice->seller ?? \App\Models\Seller::find($invoice->seller_id);
                if ($seller && $seller->seller_tax_identifier === $invoice->principal_id) {
                    throw new \Exception('The principal (TRN) must not be equal to the seller tax identifier (TRN).');
                }
            }

            // 4th digit (index 3) is '1' → summary_invoice_start_date & summary_invoice_end_date required
            if (strlen($typeCode) >= 4 && substr($typeCode, 3, 1) === '1') {
                if (empty($invoice->summary_invoice_start_date) || empty($invoice->summary_invoice_end_date)) {
                    throw new \Exception('Both summary invoice start and end dates are required when the Summary Invoice is selected.');
                }

                $startDate = new \DateTime($invoice->summary_invoice_start_date);
                $endDate = new \DateTime($invoice->summary_invoice_end_date);

                if ($endDate < $startDate) {
                    throw new \Exception('Summary invoice end date must be greater than or equal to start date.');
                }
            }

            // 5th digit (index 4) is '1' → billing_frequency required
            if (strlen($typeCode) >= 5 && substr($typeCode, 4, 1) === '1') {
                if (empty($invoice->billing_frequency)) {
                    throw new \Exception('The billing frequency field is required when Continuous Supply is selected.');
                }
            }

            // If billing_frequency = 'others' → invoice_note required
            if (!empty($invoice->billing_frequency) && $invoice->billing_frequency === 'others') {
                if (empty($invoice->invoice_note)) {
                    throw new \Exception('Invoice note is required when billing frequency is "others".');
                }
            }

            // 1st digit (index 0) is '1' → beneficiary_id required
            if (strlen($typeCode) >= 1 && substr($typeCode, 0, 1) === '1') {
                if (empty($invoice->beneficiary_id)) {
                    throw new \Exception('The beneficiary_id field is required when Free Trade Zone is selected.');
                }
            }

            // New check: If 7th digit (index 6) is '1', require delivery address fields
            if (strlen($typeCode) === 8 && substr($typeCode, 6, 1) === '1') {
                if (empty($invoice->deliver_to_address_line_1) || empty($invoice->deliver_to_country_code) || empty($invoice->deliver_to_country_subdivision)) {
                    throw new \Exception('The deliver_to_address_line_1, deliver_to_country_code, and deliver_to_country_subdivision fields are required when the delivery type is selected.');
                }
            }

            if ($invoice->invoice_type_code == '381') {
                if (empty($invoice->creditNoteRefInvoice)) {
                    throw new \Exception('The creditNoteRefInvoice field is required for Credit Notes.');
                }
                if (empty($invoice->reason_for_credit_note)) {
                    throw new \Exception('The reason_for_credit_note field is required for Credit Notes.');
                }
            }
        });
    }
}