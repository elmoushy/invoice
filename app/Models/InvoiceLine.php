<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    use HasFactory;

    protected $primaryKey = 'line_id';

    protected $fillable = [
        'invoice_id',
        'invoice_line_identifier',
        'invoiced_quantity',
        'invoiced_quantity_unit_code',
        'item_net_price',
        'item_gross_price',
        'item_price_base_quantity',
        'invoiced_item_tax_rate',
        'item_name',
        'item_description',
        'vat_line_amount',
        'item_type',
        'item_classification',
        'classification_scheme_identifier',
        'sac_scheme_identifier',
        'invoice_line_net_amount',

        // New fields from the frontend
        'discount_type',
        'discount_value',
        'invoiced_item_tax_category_code',


        'tax_exemption_reason',
        'tax_exemption_reason_code',
        'scheme_idenifier_IBT_157_1',
        'Item_Standard_Identifier',

    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}