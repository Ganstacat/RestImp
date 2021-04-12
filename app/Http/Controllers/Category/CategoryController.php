<?php

namespace App\Http\Controllers\Category;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use App\Transformers\CategoryTransformer;

class CategoryController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        //  We are already tranforming output keys, like
        // internal 'name' shows as 'title' to user, 
        // and when he wants to create new model, 
        // he would provide 'title' field, not 'name'
        //  So this middleware transforms user-provided keys to internal keys.
        $this->middleware('transform.input:'.CategoryTransformer::class)
            ->only(['store', 'update']);
    }    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::all();
        return $this->showAll($categories);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'description' => 'required'
        ];
        $this->validate($request, $rules);
        $newCategory = Category::create($request->all());
        
        return $this->showOne($newCategory, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        return $this->showOne($category);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $category->fill($request->only([
            'name', 'description'
        ]));

        if($category->isClean()){
            $msg = 'You need to specify a different value to update';
            return $this->errorResponse($msg, 422);
        }

        $category->save();

        return $this->showOne($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        $category->delete();
        return $this->showOne($category);
    }
}
