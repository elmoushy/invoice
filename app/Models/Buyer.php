<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use HasFactory;

    protected $primaryKey = 'buyer_id';

    protected $fillable = [
        'buyer_name',
        'buyer_tax_identifier',
        'legal_identifier',
        'electronic_address',
        'address_line1',
        'city',
        'country_code',
        'country_subdivision',
        'buyer_legal_registration_type',
        'authority_code',
        'buyer_passport_issuing_country',
        'scheme_identifier',
        'scheme_identifier_electronic_address',
        'seller_postal_address'
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'buyer_id');
    }
}
