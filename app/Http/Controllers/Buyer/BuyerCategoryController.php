<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\ApiController;
use App\Models\Buyer;
use Illuminate\Http\Request;

class BuyerCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Buyer $buyer)
    {
        $categories = $buyer->transactions()->with('product.categories')
            ->get()
            ->pluck('product.categories')
            // Product has many categories, so we have
            // [transaction->product->[categories]], collection inside collection
            // In response this looks like { { category: ... } }
            // 'collabse' remove outside collections, { category: ...}
            ->collapse()
            ->unique('id')
            ->values();

        return $this->showAll($categories);
    }
}
