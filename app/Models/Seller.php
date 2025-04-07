<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $primaryKey = 'seller_id';

    protected $fillable = [
        'seller_name',
        'seller_tax_identifier',
        'legal_identifier',
        'electronic_address',
        'address_line1',
        'city',
        'country_code',
        'country_subdivision',
        'seller_legal_registration_type',
        'authority_name',
        'passport_issuing_country_code',
        'scheme_identifier',
        'scheme_identifier_electronic_address',
        'seller_postal_address'
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'seller_id');
    }
}
