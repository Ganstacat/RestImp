<?php

namespace App\Http\Controllers\Product;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Transformers\CategoryTransformer;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProductCategoryController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Product $product)
    {
        $categories = $product->categories;

        return $this->showAll($categories);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @param  \App\Models\Category $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product, Category $category)
    {
        // Establish relationship between product and category.
        
        // attach([$mod->id]) -- attach model to related model, 
        //  can attach many models with the same id
        
        // sync([$mod->id]) -- remove others models from relationship and 
        //  add only specified one(s).
        //  If there are multiple instances of models with the same id,
        //  it'll perserve all of them.
        
        // syncWithoutDetach([$mod->id]) -- attach model, but if there already is 
        //  model the same id, it wont add anything. No dublicates!
        $product->categories()->syncWithoutDetaching([$category->id]);

        return $this->showAll($product->categories);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @param  \App\Models\Category $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product, Category $category)
    {
        $this->checkProductHasCategory($product, $category);

        $product->categories()->detach($category->id);

        return $this->showAll($product->categories);
    }

    protected function checkProductHasCategory(Product $product, Category $category)
    {
        if(!$product->categories()->find($category->id)){
            $msg = 'Product does not have category with this id';
            throw new HttpException(404, $msg);
        }
    }
}
