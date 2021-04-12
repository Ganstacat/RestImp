<?php

namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\CategoryTransformer;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{	
	use HasFactory;
	use SoftDeletes;

	public $transformer = CategoryTransformer::class;

	protected $dates = ['deleted_at'];


	// those values can be massively assigned
	// when we use Category::create(['name' => 'a12w', 'description' => 'sss']);
	protected $fillable = [
	    'name',
	    'description'
	];

	protected $hidden = [
		'pivot' //removes 'pivot' field from every response.
	];

	/*
	* Many to many relationship:
	* product belongs to many categories, (Product has belongsToMany:Category)
	* category belongs to many products
	* also we need to create pivot table: category_product
	* whcih will store category_id and product_id
	*/
	public function products()
	{
		// %opt% second arg: name of the pivot table
		return $this->belongsToMany(Product::class); 
	}
}
