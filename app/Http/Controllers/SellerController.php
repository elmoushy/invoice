<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    /**
     * Display a listing of the sellers.
     */
    public function index()
    {
        $sellers = Seller::paginate(15);
        return response()->json(['status' => 200, 'data' => $sellers]);
    }

    /**
     * Store a newly created seller.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'seller_name' => 'required|string|max:255',
            'seller_Tax_Identifier' => 'nullable|string|max:255',
            'legal_identifier' => 'nullable|string|max:255',
            'electronic_address' => 'required|string|max:255',
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'country_code' => 'required|string|size:2',
            'country_subdivision' => 'required|string|max:50',
            'seller_legal_registration_type' => 'nullable|string|max:50',
            'authority_name' => 'nullable|string|max:100',
            'passport_issuing_country_code' => 'nullable|string|size:2',
            'scheme_identifier' => 'nullable|string|max:50',
            'scheme_identifier_electronic_address' => 'required|string|max:50',
        ]);
    
        // Compute seller_postal_address by concatenating address_line1, city, country_code, and country_subdivision.
        $validatedData['seller_postal_address'] = $validatedData['address_line1'] . ', ' 
        . $validatedData['city'] . ', ' 
        . $validatedData['country_code'] . ', ' 
        . $validatedData['country_subdivision'];
        
        $seller = Seller::create($validatedData);
    
        return response()->json([
            'status' => 201,
            'message' => 'Seller created successfully',
            'data' => $seller
        ]);
    }
    
    

    /**
     * Display the specified seller.
     */
    public function show($id)
    {
        $seller = Seller::findOrFail($id);

        return response()->json(['status' => 200, 'data' => $seller]);
    }

    /**
     * Remove the specified seller.
     */
    public function destroy($id)
    {
        $seller = Seller::findOrFail($id);
        $seller->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Seller deleted successfully'
        ]);
    }
}
