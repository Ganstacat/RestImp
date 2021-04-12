<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\ApiController;
use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerSellerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer)
    {
        // Buyer < Transactions >- Products > Seller
        // Eager loading with nested relationship
        $sellers = $buyer->transactions()->with('product.seller')
            ->get()
            ->pluck('product.seller')
            ->unique('id') // Because there can be many sellers for product
            ->values();
            // Because 'unique' removes duplicates and left empty spaces,
            // and 'values' removes those empty spaces
            

        return $this->showAll($sellers);
    }
}
