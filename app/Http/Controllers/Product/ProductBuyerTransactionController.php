<?php

namespace App\Http\Controllers\Product;

use App\Models\User;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Transformers\TransactionTransformer;

class ProductBuyerTransactionController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        //  We are already tranforming output keys, like
        // internal 'name' shows as 'title' to user, 
        // and when he wants to create new model, 
        // he would provide 'title' field, not 'name'
        //  So this middleware transforms user-provided keys to internal keys.
        $this->middleware('transform.input:'.TransactionTransformer::class)
            ->only(['store']);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product @product
     * @param  \App\Models\Buyer @buyer
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product, User $buyer)
    {
        $rules = [
            'quantity' => 'required|integer|min:1',
        ];
        $this->validate($request, $rules);

        if($product->seller_id  === $buyer->id){
            $msg = 'The buyer must be different from the seller';
            return $this->errorResponse($msg, 409);
        }
        if(!$buyer->isVerified()){
            $msg = 'The buyer must be a verified user';
            return $this->errorResponse($msg, 409);            
        }
        if(!$product->seller->isVerified()){
            $msg = 'The seller must be a verified user';
            return $this->errorResponse($msg, 409);
        }
        if(!$product->isAvailable()){
            $msg = 'The product is not available';
            return $this->errorResponse($msg, 409);
        }
        if($request->quantity > $product->quantity){
            $msg = 'The product does not have enough units for this transaction';
            return $this->errorResponse($msg, 409);
        }
        //Transaction: if this operation fails, db will be 
        //returned to the state before this closure function.
        //(i.e., if a product is bought simultaneosly many times and its quantity
        //becomes negative, laravel will rollback last transaction(s))
        //
        // function() use (...) — closure, all variables inside the 'use' are
        // grabbed from the outher scope. 
        return DB::transaction( function() use($request, $product, $buyer){
            $product->quantity -= $request->quantity;
            $product->save();

            $transaction = Transaction::create([
                'quantity' => $request->quantity,
                'buyer_id' => $buyer->id,
                'product_id' => $product->id
            ]);

            return $this->showOne($transaction, 201);
        });     

    }
}
