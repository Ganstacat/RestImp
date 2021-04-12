<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\ApiController;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryBuyerController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Category $category)
    {
        $buyers = $category->products() // take products,
            ->whereHas('transactions') // which have transactions,
            ->with('transactions.buyer') // join transactions.buyer,
            ->get() // obtain collections,
            ->pluck('transactions') // select only transactions field,
            ->collapse() // remove empty 外 collections,
            ->pluck('buyer') // select only buyer field
            ->unique('id') // remove repititions,
            ->values(); // get only non-empty fields

        return $this->showAll($buyers);
    }
}
