<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    /**
     * Display a paginated listing of buyers.
     */
    public function index()
    {
        $buyers = Buyer::paginate(15);
        return response()->json(['status' => 200, 'data' => $buyers]);
    }

    /**
     * Store a newly created buyer in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'buyer_name'                         => 'required|string|max:255',
            'buyer_tax_identifier'               => 'required|string|max:255',
            'legal_identifier'                   => 'nullable|string|max:255',
            'electronic_address'                 => 'required|string|max:255',
            'address_line1'                      => 'required|string|max:255',
            'city'                               => 'required|string|max:100',
            'country_code'                       => 'required|string|size:2',
            'country_subdivision'                => 'required|string|max:50',
            'buyer_legal_registration_type'      => 'nullable|string|max:50',
            'authority_code'                     => 'nullable|string|max:100',
            'buyer_passport_issuing_country'     => 'nullable|string|size:2',
            'scheme_identifier'                  => 'nullable|string|max:50',
            'scheme_identifier_electronic_address' => 'required|string|max:50'
        ]);
    
        // Compute seller_postal_address by concatenating address_line1, city, country_code, and country_subdivision.
        $validatedData['seller_postal_address'] = $validatedData['address_line1'] . ', ' 
        . $validatedData['city'] . ', ' 
        . $validatedData['country_code'] . ', ' 
        . $validatedData['country_subdivision'];
        
        $buyer = Buyer::create($validatedData);
    
        return response()->json([
            'status'  => 201,
            'message' => 'Buyer created successfully',
            'data'    => $buyer
        ]);
    }
    
    

    /**
     * Display the specified buyer details.
     */
    public function show($id)
    {
        $buyer = Buyer::findOrFail($id);

        return response()->json(['status' => 200, 'data' => $buyer]);
    }

    /**
     * Update the specified buyer in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'buyer_name' => 'sometimes|required|string|max:255',
            'Buyer_Tax_Identifier' => 'sometimes|required|string|max:255',
            'legal_identifier' => 'nullable|string|max:255',
            'tax_identifier' => 'nullable|string|max:50',
            'electronic_address' => 'sometimes|required|string|max:255',
            'address_line1' => 'sometimes|required|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'country_code' => 'sometimes|required|string|size:2',
            'country_subdivision' => 'sometimes|required|string|max:50',
            'buyer_legal_registration_type' => 'nullable|string|max:50',
            'authority_code' => 'nullable|string|max:100',
            'buyer_passport_issuing_country' => 'nullable|string|size:2',
            'scheme_identifier_electronic_address' => 'sometimes|required|string|max:50',
        ]);

        $buyer = Buyer::findOrFail($id);
        $buyer->update($request->all());

        return response()->json([
            'status' => 200,
            'message' => 'Buyer updated successfully',
            'data' => $buyer
        ]);
    }

    /**
     * Remove the specified buyer from storage.
     */
    public function destroy($id)
    {
        $buyer = Buyer::findOrFail($id);
        $buyer->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Buyer deleted successfully'
        ]);
    }
}
