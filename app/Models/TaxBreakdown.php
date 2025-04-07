<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxBreakdown extends Model
{
    use HasFactory;

    protected $primaryKey = 'tax_id';

    protected $fillable = [
        'invoice_id',
        'tax_category_code',
        'tax_category_rate',
        'taxable_amount',
        'tax_amount'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}