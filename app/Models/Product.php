<?php

namespace App\Models;

use App\Models\Seller;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Model;
use App\Transformers\ProductTransformer;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    const AVAILABLE_PRODUCT = 'available';
    const UNAVAILABLE_PRODUCT = 'unavailable';

    public $transformer = ProductTransformer::class;

    protected $dates = ['deleted_at'];
    
    protected $fillable = [
    	'name',
    	'description',
    	'quantity',
    	'status', // can be available or unavailable
    	'image',
    	'seller_id'	
    ];

    protected $hidden = [
        'pivot' //removes 'pivot' field from every response.
    ];

    // protected $dispatchedEvents = [
    //     'updated'
    // ];

    protected static function booted()
    {
        //Listen to the product updates
        static::updated(function($product){
            echo "kek";
            if( $product->quantity === 0 && $product->isAvailable()){
                $product->status = Product::UNAVAILABLE_PRODUCT;
                $product->save();
            }
        });
    }


    public function isAvailable()
    {
    	return $this->status === Product::AVAILABLE_PRODUCT;
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    
    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
