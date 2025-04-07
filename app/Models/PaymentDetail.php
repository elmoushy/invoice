<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_id';

    // Add the new columns to the fillable array
    protected $fillable = [
        'invoice_id',
        'payment_date',
        'credit_transfer',
        'payment_means_type_code',
        'payment_account_identifier',
        'scheme_identifier',
        'payment_account_name',
        'payment_service_provider_identifier',
        'payment_card_number',
        'paid_amount',
        'rounding_amount',
        'amount_due_for_payment',
    
        // new fields
        'payment_card_primary_account_number',
        'expiry_date',
        'cvv'
    ];
    

    /**
     * For PCI-DSS, do NOT expose CVV in arrays/JSON.
     */
    protected $hidden = [
        'cvv',
    ];

    /**
     * Use Eloquent's encryption casts for the card data.
     * This ensures the values are stored encrypted in the DB.
     */
    protected $casts = [
        'payment_card_primary_account_number' => 'encrypted',
        'expiry_date' => 'encrypted',
        'cvv' => 'encrypted',
    ];

    /**
     * Example: Accessor to display only the last 4 digits of the card number.
     * In your blade or Vue, you can call $paymentDetail->masked_card_number.
     */
    public function getMaskedCardNumberAttribute()
    {
        // If you truly are storing a token, you can just return the token here
        // Or if you do store the real encrypted number, proceed with decryption & masking:
        $rawValue = $this->payment_card_primary_account_number;
        if (!$rawValue) {
            return null;
        }

        // Show only the last 4 digits
        $last4 = substr($rawValue, -4);
        return '**** **** **** ' . $last4;
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
