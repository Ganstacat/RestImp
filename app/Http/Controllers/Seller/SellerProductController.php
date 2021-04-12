<?php

namespace App\Http\Controllers\Seller;

use App\Models\User;
use App\Models\Seller;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        //  We are already tranforming output keys, like
        // internal 'name' shows as 'title' to user, 
        // and when he wants to create new model, 
        // he would provide 'title' field, not 'name'
        //  So this middleware transforms user-provided keys to internal keys.
        $this->middleware('transform.input:'.ProductTransformer::class)
            ->only(['store', 'update']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        $products = $seller->products;
        return $this->showAll($products);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, User $seller)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'quantity' => 'required|integer|min:1',
            'image' => 'required|image',
        ];

        $this->validate($request, $rules);

        $data = $request->all();

        $data['status'] = Product::UNAVAILABLE_PRODUCT;
        //laravel detects image in request, and provides store method
        // store('path', *opt* filesystem);
        // it'll store image in filesystem, and return filename
        $data['image'] = $request->image->store('');
        $data['seller_id'] = $seller->id;

        $product = Product::create($data);

        return $this->showOne($product, 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Seller  $seller
     * @param  \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        $rules = [
            'quantity' => 'integer|min:1',
            'status' => 'in:'.Product::AVAILABLE_PRODUCT.','.Product::UNAVAILABLE_PRODUCT,
            'image' => 'image'
        ];

        $this->validate($request, $rules);

        $this->checkSellerOwnsProduct($seller, $product);

        $product->fill($request->only([
            'name',
            'description',
            'quantity',
        ]));

        if($request->has('status')){
            $product->status = $request->status;

            if($product->isAvailable() && $product->categories()->count() === 0){
                $msg = 'An active product must have at least one category';
                return $this->errorResponse($msg, 409);
            }
        }
        if($request->hasFile('image')){
            Storage::delete($product->image);
            $product->image = $request->image->store('');
        }
        if($product->isClean()){
            $msg = 'You need to specify a different value to update';
            return $this->errorResponse($msg, 422);
        }

        $product->save();

        return $this->showOne($product);

    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Seller  $seller
     * @param  \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller, Product $product)
    {
        $this->checkSellerOwnsProduct($seller, $product);
        $product->delete();

        // We are using SoftDelete, so image shouldn't be hard deleted.
        // Storage::delete($product->image);
        return $this->showOne($product);
    }


    protected function checkSellerOwnsProduct(Seller $seller, Product $product)
    {
        if($seller->id !== $product->seller_id){
            $msg = 'Specified seller is not the actual seller of this prouct';
            throw new HttpException(422, $msg);                        
        }
    }
}
