<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\ApiController;
use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerProductController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer)
    {
        // Eager loading:
        // Query: select * from products where transaction_id in ([transactions ids])
        // result: transaction["ID", "Name", "...", "PRODUCT"=> ["product data ..."] ]
        $products = $buyer->transactions()->with('product')
            ->get()
            ->pluck('product'); //Pluck - get only that index from model

        return $this->showAll($products);
    }
}
