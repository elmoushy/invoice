<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\TaxBreakdown;
use App\Models\PaymentDetail;

class Case1InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['seller', 'buyer', 'lines', 'taxBreakdowns', 'payments']);
        
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('invoice_number', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('seller', function ($sellerQuery) use ($searchTerm) {
                      $sellerQuery->where('seller_name', 'like', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('buyer', function ($buyerQuery) use ($searchTerm) {
                      $buyerQuery->where('buyer_name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }
        
        // Optional filter by status (assumes a 'status' column exists)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Sort by date (invoice_issue_date) newest/oldest
        if ($request->filled('sort_date')) {
            $query->orderBy('invoice_issue_date', $request->sort_date);
        }

        // Sort by amount (invoice_total_with_tax) newest/oldest
        if ($request->filled('sort_amount')) {
            $query->orderBy('invoice_total_with_tax', $request->sort_amount);
        }
        
        $invoices = $query->paginate(5);
        return response()->json(['status' => 200, 'data' => $invoices]);
    }

    public function index2(Request $request)
    {
        $query = Invoice::query();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('invoice_number', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('seller', function ($sellerQuery) use ($searchTerm) {
                      $sellerQuery->where('seller_name', 'like', '%' . $searchTerm . '%');
                  })
                  ->orWhereHas('buyer', function ($buyerQuery) use ($searchTerm) {
                      $buyerQuery->where('buyer_name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->select('invoice_id', 'invoice_number')->get();
        return response()->json(['status' => 200, 'data' => $invoices]);
    }

    
    /**
     * Generate an auto-incremented invoice number in the format INV-YYYY-XXX.
     *
     * @param string $invoiceIssueDate
     * @return string
     */
    protected function generateInvoiceNumber($invoiceIssueDate)
    {
        $year = date('Y', strtotime($invoiceIssueDate));
        $prefix = "INV-" . $year . "-";

        // Retrieve the latest invoice number for the given year
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extract the numeric sequence (last three characters)
            $lastSequence = (int) substr($lastInvoice->invoice_number, -3);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        // Format the sequence as three digits
        return $prefix . sprintf('%03d', $newSequence);
    }
    
    private function analyzeGtin($code)
    {
        // 1) Must be digits only
        if (!ctype_digit($code)) {
            return "The code must contain digits only.";
        }

        // 2) Must have length 8, 12, 13, or 14
        $length = strlen($code);
        if (!in_array($length, [8, 12, 13, 14])) {
            return "The code length ($length) is not one of 8, 12, 13, or 14.";
        }

        // 3) Compute the check digit:

        // Separate the check digit (last digit) from the main part
        $checkDigit = (int) substr($code, -1);
        $data = substr($code, 0, -1);

        // Reverse the data (excluding the check digit)
        $reversedData = strrev($data);
        $sum = 0;

        // Multiply odd positions by 3, even by 1 (counting from the RIGHT)
        // i=0 => rightmost => odd => x3
        // i=1 => next => even => x1
        for ($i = 0; $i < strlen($reversedData); $i++) {
            $digit = (int) $reversedData[$i];
            $sum += ($i % 2 === 0) ? ($digit * 3) : $digit;
        }

        // Find the calculated check digit
        $calculatedCheckDigit = (10 - ($sum % 10)) % 10;

        // 4) Compare check digits
        if ($calculatedCheckDigit !== $checkDigit) {
            return "Check digit mismatch (expected $calculatedCheckDigit, got $checkDigit).";
        }

        // 5) If we got here, it's fully valid. Return the GTIN type:
        return match ($length) {
            8  => 'GTIN-8',
            12 => 'GTIN-12',
            13 => 'GTIN-13',
            14 => 'GTIN-14',
        };
    }

    public function store(Request $request)
    {
        $invoice = DB::transaction(function () use ($request) {
            // 1. VALIDATE
            $validatedData = $request->validate([
                // -----------------------------------------
                // Invoice main fields
                // -----------------------------------------
                'invoice_issue_date' => 'required|date',
                'invoice_type_code' => 'nullable|string',
                'invoice_currency_code' => 'nullable|string|size:3',
                'payment_currency_code' => 'nullable|string|size:3',
                'tax_registration_identifier' => 'nullable|string',
                'invoice_transaction_type_code' => 'required|string',
                'invoice_total_line_net_amount' => 'nullable|max:50',
                'invoice_total_tax_amount' => 'nullable|max:50',
                'invoice_total_with_tax' => 'nullable|max:50',
                'invoice_due_for_payment' => 'nullable|max:50',
                'payment_due_date' => 'nullable|date',
                'business_process_type' => 'required|string',
                'specification_identifier' => 'required|string',
                'currency_exchange_rate' => 'nullable|numeric',
                'invoice_total_tax_amount_acc_currency' => 'nullable|numeric',
                'principal_id' => [
                    'nullable',
                ],
                'summary_invoice_start_date'           => 'nullable|date',
                'summary_invoice_end_date'             => 'nullable|date',
                'seller_id' => 'required|exists:sellers,seller_id',
                'buyer_id' => 'required|exists:buyers,buyer_id',
                'contract_reference' => 'nullable|string|max:255',
                'contract_value' => 'nullable|numeric|min:0',
                'billing_frequency' => 'nullable|string|in:daily,weekly,monthly,quarterly,others',
                'invoice_note' => 'nullable|string|max:255',
                'beneficiary_id' => 'nullable|string|max:255',
                'deliver_to_address_line_1' => 'nullable|string|max:255',
                'deliver_to_address_line_2' => 'nullable|string|max:255',
                'deliver_to_address_line_3' => 'nullable|string|max:255',
                'deliver_to_post_code' => 'nullable|string|max:50',
                'deliver_to_country_code' => 'nullable|string|max:3',
                'deliver_to_country_subdivision' => 'nullable|string|max:255',
                'deliver_to_city' => 'nullable|string|max:255',
                'deliver_to_party_name' => 'nullable|string|max:255',
                'deliver_to_location_identifier' => 'nullable|string|max:255',
                'location_scheme_identifier' => 'nullable|string|max:50',
                'actual_delivery_date' => 'nullable|date',
                'creditNoteRefInvoice' => 'nullable|numeric',
                'creditNoteRefInvoice_number' => 'nullable|string',
                'correction_method' => 'nullable|string',
                'reason_for_credit_note' => 'nullable|string',

                
                // -----------------------------------------
                // Invoice lines
                // -----------------------------------------
                'invoice_lines' => 'required|array|min:1',
                'invoice_lines.*.invoice_line_identifier' => 'required|string|max:50',
                'invoice_lines.*.invoiced_quantity' => 'required|numeric|min:0',
                'invoice_lines.*.invoiced_quantity_unit_code' => 'required|string|max:10',
                'invoice_lines.*.item_net_price' => 'required|numeric|min:0',
                'invoice_lines.*.item_gross_price' => 'required|numeric|min:0',
                'invoice_lines.*.item_description' => 'required|string',
                'invoice_lines.*.item_classification' => 'nullable|string|max:255',
                'invoice_lines.*.invoice_line_net_amount' => 'required|numeric',
                'invoice_lines.*.item_price_base_quantity' => 'required|numeric|min:1',
                'invoice_lines.*.invoiced_item_tax_rate' => 'nullable|numeric',
                'invoice_lines.*.item_name' => 'required|string|max:255',
                'invoice_lines.*.vat_line_amount' => 'required|numeric',
                'invoice_lines.*.item_type' => 'nullable|string|max:50',

                'invoice_lines.*.classification_scheme_identifier' => 'nullable|string||max:10',
                'invoice_lines.*.sac_scheme_identifier' => 'nullable|string|max:10',
                'invoice_lines.*.discount_type' => 'nullable|string|max:50',
                'invoice_lines.*.discount_value' => 'nullable|numeric|min:0',
                'invoice_lines.*.invoiced_item_tax_category_code' => 'nullable|string|max:50',
                'invoice_lines.*.tax_exemption_reason' => 'nullable|string|max:255',
                'invoice_lines.*.tax_exemption_reason_code' => 'nullable|string|max:255',
                'invoice_lines.*.Item_Standard_Identifier' => [
                    'nullable',
                    'regex:/^(?:\d{8}|\d{12}|\d{13}|\d{14})$/'
                ],

                // We'll fill scheme_idenifier_IBT_157_1 ourselves:
                'invoice_lines.*.scheme_idenifier_IBT_157_1' => 'nullable|string|max:50',

                // -----------------------------------------
                // Tax Breakdowns
                // -----------------------------------------
                'tax_breakdowns' => 'required|array|min:1',
                'tax_breakdowns.*.tax_category_code' => 'required|string|max:50',
                'tax_breakdowns.*.tax_category_rate' => 'nullable|numeric',
                'tax_breakdowns.*.taxable_amount' => 'required|numeric',
                'tax_breakdowns.*.tax_amount' => 'required|numeric',

                // -----------------------------------------
                // Payment Details
                // -----------------------------------------
                'payment_details' => 'required|array|min:1',
                'payment_details.*.payment_date' => 'nullable|date',
                'payment_details.*.credit_transfer' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($request) {
                        $parts = explode('.', $attribute);
                        $index = $parts[1] ?? null;
                        $paymentDetail = $request->payment_details[$index] ?? null;

                        if ($paymentDetail &&
                            isset($paymentDetail['payment_means_type_code']) &&
                            $paymentDetail['payment_means_type_code'] === 'credit_transfer') {
                            // Auto-generate credit_transfer by concatenating required fields
                            $request->merge([
                                'payment_details' => array_replace($request->payment_details, [
                                    $index => array_merge($paymentDetail, [
                                        'credit_transfer' => trim(
                                            ($paymentDetail['payment_account_identifier'] ?? '') . ' ' .
                                            ($paymentDetail['payment_account_name'] ?? '') . ' ' .
                                            ($paymentDetail['payment_service_provider_identifier'] ?? '') . ' ' .
                                            ($paymentDetail['scheme_identifier'] ?? '')
                                        )
                                    ])
                                ])
                            ]);
                        }
                    }
                ],
                'payment_details.*.payment_means_type_code' => 'required|string|max:20',
                'payment_details.*.paid_amount' => 'nullable|numeric|min:0',
                'payment_details.*.rounding_amount' => 'nullable|numeric',
                'payment_details.*.amount_due_for_payment' => 'nullable|numeric',
                'payment_details.*.payment_account_identifier' => 'nullable|string|max:100',
                'payment_details.*.payment_account_name' => 'nullable|string|max:255',
                'payment_details.*.payment_service_provider_identifier' => 'nullable|string|max:100',
                'payment_details.*.scheme_identifier' => 'nullable|string|max:50',
                'payment_details.*.payment_card_number' => 'nullable|string|max:20',
                'payment_details.*.payment_card_primary_account_number' => 'nullable|string|max:25',
                'payment_details.*.expiry_date' => 'nullable|string|max:5',
                'payment_details.*.cvv' => 'nullable|string|max:4',
            ]);

            if($validatedData['deliver_to_country_code'] === 'AE') {
                if($validatedData['deliver_to_country_subdivision'] === 'AUH') {
                    $identifier_number = '12345';
                } elseif($validatedData['deliver_to_country_subdivision'] === 'DXB') {
                    $identifier_number = '56789';
                } elseif($validatedData['deliver_to_country_subdivision'] === 'SHJ') {
                    $identifier_number = '23456';
                } elseif($validatedData['deliver_to_country_subdivision'] === 'UAQ') {
                    $identifier_number = '34567';
                } elseif($validatedData['deliver_to_country_subdivision'] === 'FUJ') {
                    $identifier_number = '45678';
                } elseif($validatedData['deliver_to_country_subdivision'] === 'AJM') {
                    $identifier_number = '67890';
                } elseif($validatedData['deliver_to_country_subdivision'] === 'RAK') {
                    $identifier_number = '78901';
                }
                $validatedData['deliver_to_location_identifier']= $validatedData['deliver_to_country_code'] . '-' 
                    . $validatedData['deliver_to_country_subdivision'] 
                    . '-' . $identifier_number;
                $validatedData['location_scheme_identifier'] = '0088';
            }

            // 2. For each invoice line, if Item_Standard_Identifier is given, run the GTIN check
            foreach ($validatedData['invoice_lines'] as $i => $line) {
                $code = $line['Item_Standard_Identifier'] ?? null;

                if (!empty($code)) {
                    // Run the step-by-step check-digit math and get either "GTIN-13" or an error reason
                    $result = $this->analyzeGtin($code);

                    if (str_starts_with($result, 'GTIN')) {
                        // It's valid, so store e.g. "GTIN-13" in scheme_idenifier_IBT_157_1
                        $validatedData['invoice_lines'][$i]['scheme_idenifier_IBT_157_1'] = $result;
                    } else {
                        // It's an error reason, throw a validation exception
                        throw ValidationException::withMessages([
                            "invoice_lines.$i.Item_Standard_Identifier" => $result
                        ]);
                    }
                }
            }

            // 3. AUTO-GENERATE INVOICE NUMBER
            $validatedData['invoice_number'] = $this->generateInvoiceNumber($validatedData['invoice_issue_date']);

            // 4. CREATE INVOICE
            $invoice = Invoice::create($validatedData);

            // 5. INSERT LINES
            foreach ($validatedData['invoice_lines'] as $line) {
                $line['invoice_id'] = $invoice->invoice_id;
                InvoiceLine::create($line);
            }

            // 6. INSERT TAX BREAKDOWNS
            foreach ($validatedData['tax_breakdowns'] as $taxBreakdown) {
                $taxBreakdown['invoice_id'] = $invoice->invoice_id;
                TaxBreakdown::create($taxBreakdown);
            }

            // 7. INSERT PAYMENT DETAILS
            foreach ($validatedData['payment_details'] as $paymentDetail) {
                $paymentDetail['invoice_id'] = $invoice->invoice_id;
                PaymentDetail::create($paymentDetail);
            }

            // 8. LOAD RELATIONSHIPS & RETURN
            $invoice->load(['seller', 'buyer', 'lines', 'taxBreakdowns', 'payments']);

            return response()->json([
                'status' => 201,
                'message' => 'Invoice created successfully',
                'data' => $invoice
            ]);
        });

        return $invoice;
    }

    // Retrieve full invoice with related data
    public function show($id)
    {
        $invoice = Invoice::with(['seller','buyer','lines', 'taxBreakdowns', 'payments'])->findOrFail($id);
        return response()->json(['status' => 200, 'data' => $invoice]);
    }

    public function update(Request $request, $id)
    {
        // Find the invoice or fail with a 404 error.
        $invoice = Invoice::findOrFail($id);
    
        $updatedInvoice = DB::transaction(function () use ($request, $invoice) {
            // 1. VALIDATE
            $validatedData = $request->validate([
                // -----------------------------------------
                // Invoice main fields
                // -----------------------------------------
                'invoice_issue_date' => 'required|date',
                'invoice_type_code' => 'nullable|string',
                'invoice_currency_code' => 'nullable|string|size:3',
                'payment_currency_code' => 'nullable|string|size:3',
                'tax_registration_identifier' => 'nullable|string',
                'invoice_transaction_type_code' => 'required|string',
                'invoice_total_line_net_amount' => 'nullable|max:50',
                'invoice_total_tax_amount' => 'nullable|max:50',
                'invoice_total_with_tax' => 'nullable|max:50',
                'invoice_due_for_payment' => 'nullable|max:50',
                'payment_due_date' => 'nullable|date',
                'business_process_type' => 'required|string',
                'specification_identifier' => 'required|string',
                'currency_exchange_rate' => 'nullable|numeric',
                'invoice_total_tax_amount_acc_currency' => 'nullable|numeric',
                'principal_id' => ['nullable'],
                'summary_invoice_start_date'           => 'nullable|date',
                'summary_invoice_end_date'             => 'nullable|date',
                'seller_id' => 'required|exists:sellers,seller_id',
                'buyer_id' => 'required|exists:buyers,buyer_id',
                'contract_reference' => 'nullable|string|max:255',
                'contract_value' => 'nullable|numeric|min:0',
                'billing_frequency' => 'nullable|string|in:daily,weekly,monthly,quarterly,others',
                'invoice_note' => 'nullable|string|max:255',
                'beneficiary_id' => 'nullable|string|max:255',
                'deliver_to_address_line_1' => 'nullable|string|max:255',
                'deliver_to_address_line_2' => 'nullable|string|max:255',
                'deliver_to_address_line_3' => 'nullable|string|max:255',
                'deliver_to_post_code' => 'nullable|string|max:50',
                'deliver_to_country_code' => 'nullable|string|max:3',
                'deliver_to_country_subdivision' => 'nullable|string|max:255',
                'deliver_to_city' => 'nullable|string|max:255',
                'deliver_to_party_name' => 'nullable|string|max:255',
                'deliver_to_location_identifier' => 'nullable|string|max:255',
                'location_scheme_identifier' => 'nullable|string|max:50',
                'actual_delivery_date' => 'nullable|date',
    
                // -----------------------------------------
                // Invoice lines
                // -----------------------------------------
                'invoice_lines' => 'required|array|min:1',
                'invoice_lines.*.invoice_line_identifier' => 'required|string|max:50',
                'invoice_lines.*.invoiced_quantity' => 'required|numeric|min:0',
                'invoice_lines.*.invoiced_quantity_unit_code' => 'required|string|max:10',
                'invoice_lines.*.item_net_price' => 'required|numeric|min:0',
                'invoice_lines.*.item_gross_price' => 'required|numeric|min:0',
                'invoice_lines.*.item_description' => 'required|string',
                'invoice_lines.*.item_classification' => 'nullable|string|max:255',
                'invoice_lines.*.invoice_line_net_amount' => 'required|numeric',
                'invoice_lines.*.item_price_base_quantity' => 'required|numeric|min:1',
                'invoice_lines.*.invoiced_item_tax_rate' => 'nullable|numeric',
                'invoice_lines.*.item_name' => 'required|string|max:255',
                'invoice_lines.*.vat_line_amount' => 'required|numeric|min:0',
                'invoice_lines.*.item_type' => 'nullable|string|max:50',
    
                'invoice_lines.*.classification_scheme_identifier' => 'nullable|string|max:10',
                'invoice_lines.*.sac_scheme_identifier' => 'nullable|string|max:10',
                'invoice_lines.*.discount_type' => 'nullable|string|max:50',
                'invoice_lines.*.discount_value' => 'nullable|numeric|min:0',
                'invoice_lines.*.invoiced_item_tax_category_code' => 'nullable|string|max:50',
                'invoice_lines.*.tax_exemption_reason' => 'nullable|string|max:255',
                'invoice_lines.*.tax_exemption_reason_code' => 'nullable|string|max:255',
                'invoice_lines.*.Item_Standard_Identifier' => [
                    'nullable',
                    'regex:/^(?:\d{8}|\d{12}|\d{13}|\d{14})$/'
                ],
                'invoice_lines.*.scheme_idenifier_IBT_157_1' => 'nullable|string|max:50',
    
                // -----------------------------------------
                // Tax Breakdowns
                // -----------------------------------------
                'tax_breakdowns' => 'required|array|min:1',
                'tax_breakdowns.*.tax_category_code' => 'required|string|max:50',
                'tax_breakdowns.*.tax_category_rate' => 'nullable|numeric',
                'tax_breakdowns.*.taxable_amount' => 'required|numeric|min:0',
                'tax_breakdowns.*.tax_amount' => 'required|numeric|min:0',
    
                // -----------------------------------------
                // Payment Details
                // -----------------------------------------
                'payment_details' => 'required|array|min:1',
                'payment_details.*.payment_date' => 'nullable|date',
                'payment_details.*.credit_transfer' => [
                    'nullable',
                    function ($attribute, $value, $fail) use ($request) {
                        $parts = explode('.', $attribute);
                        $index = $parts[1] ?? null;
                        $paymentDetail = $request->payment_details[$index] ?? null;
    
                        if ($paymentDetail &&
                            isset($paymentDetail['payment_means_type_code']) &&
                            $paymentDetail['payment_means_type_code'] === 'credit_transfer') {
                            // Auto-generate credit_transfer by concatenating required fields
                            $request->merge([
                                'payment_details' => array_replace($request->payment_details, [
                                    $index => array_merge($paymentDetail, [
                                        'credit_transfer' => trim(
                                            ($paymentDetail['payment_account_identifier'] ?? '') . ' ' .
                                            ($paymentDetail['payment_account_name'] ?? '') . ' ' .
                                            ($paymentDetail['payment_service_provider_identifier'] ?? '') . ' ' .
                                            ($paymentDetail['scheme_identifier'] ?? '')
                                        )
                                    ])
                                ])
                            ]);
                        }
                    }
                ],
                'payment_details.*.payment_means_type_code' => 'required|string|max:20',
                'payment_details.*.paid_amount' => 'nullable|numeric|min:0',
                'payment_details.*.rounding_amount' => 'nullable|numeric',
                'payment_details.*.amount_due_for_payment' => 'nullable|numeric',
                'payment_details.*.payment_account_identifier' => 'nullable|string|max:100',
                'payment_details.*.payment_account_name' => 'nullable|string|max:255',
                'payment_details.*.payment_service_provider_identifier' => 'nullable|string|max:100',
                'payment_details.*.scheme_identifier' => 'nullable|string|max:50',
                'payment_details.*.payment_card_number' => 'nullable|string|max:20',
                'payment_details.*.payment_card_primary_account_number' => 'nullable|string|max:25',
                'payment_details.*.expiry_date' => 'nullable|string|max:5',
                'payment_details.*.cvv' => 'nullable|string|max:4',
            ]);
    
            // 2. Apply additional business logic (e.g. compute deliver_to_location_identifier if country code is AE)
            if ($validatedData['deliver_to_country_code'] === 'AE') {
                if ($validatedData['deliver_to_country_subdivision'] === 'AUH') {
                    $identifier_number = '12345';
                } elseif ($validatedData['deliver_to_country_subdivision'] === 'DXB') {
                    $identifier_number = '56789';
                } elseif ($validatedData['deliver_to_country_subdivision'] === 'SHJ') {
                    $identifier_number = '23456';
                } elseif ($validatedData['deliver_to_country_subdivision'] === 'UAQ') {
                    $identifier_number = '34567';
                } elseif ($validatedData['deliver_to_country_subdivision'] === 'FUJ') {
                    $identifier_number = '45678';
                } elseif ($validatedData['deliver_to_country_subdivision'] === 'AJM') {
                    $identifier_number = '67890';
                } elseif ($validatedData['deliver_to_country_subdivision'] === 'RAK') {
                    $identifier_number = '78901';
                }
                $validatedData['deliver_to_location_identifier'] = $validatedData['deliver_to_country_code'] . '-' .
                    $validatedData['deliver_to_country_subdivision'] . '-' . $identifier_number;
                $validatedData['location_scheme_identifier'] = '0088';
            }
    
            // 3. For each invoice line, if Item_Standard_Identifier is given, run the GTIN check
            foreach ($validatedData['invoice_lines'] as $i => $line) {
                $code = $line['Item_Standard_Identifier'] ?? null;
                if (!empty($code)) {
                    $result = $this->analyzeGtin($code);
                    if (str_starts_with($result, 'GTIN')) {
                        $validatedData['invoice_lines'][$i]['scheme_idenifier_IBT_157_1'] = $result;
                    } else {
                        throw ValidationException::withMessages([
                            "invoice_lines.$i.Item_Standard_Identifier" => $result
                        ]);
                    }
                }
            }
    
            // 4. Optionally update the invoice number if the invoice issue date has changed.
            if ($invoice->invoice_issue_date !== $validatedData['invoice_issue_date']) {
                $validatedData['invoice_number'] = $this->generateInvoiceNumber($validatedData['invoice_issue_date']);
            }
    
            // 5. UPDATE INVOICE
            $invoice->update($validatedData);
    
            // 6. UPDATE INVOICE LINES:
            // Here we delete the current lines and re-create them based on the incoming data.
            $invoice->lines()->delete();
            foreach ($validatedData['invoice_lines'] as $line) {
                $line['invoice_id'] = $invoice->invoice_id;
                InvoiceLine::create($line);
            }
    
            // 7. UPDATE TAX BREAKDOWNS:
            $invoice->taxBreakdowns()->delete();
            foreach ($validatedData['tax_breakdowns'] as $taxBreakdown) {
                $taxBreakdown['invoice_id'] = $invoice->invoice_id;
                TaxBreakdown::create($taxBreakdown);
            }
    
            // 8. UPDATE PAYMENT DETAILS:
            $invoice->payments()->delete();
            foreach ($validatedData['payment_details'] as $paymentDetail) {
                $paymentDetail['invoice_id'] = $invoice->invoice_id;
                PaymentDetail::create($paymentDetail);
            }
    
            // 9. LOAD RELATIONSHIPS & RETURN THE UPDATED INVOICE
            $invoice->load(['seller', 'buyer', 'lines', 'taxBreakdowns', 'payments']);
    
            return response()->json([
                'status' => 200,
                'message' => 'Invoice updated successfully',
                'data' => $invoice
            ]);
        });
    
        return $updatedInvoice;
    }    

}
