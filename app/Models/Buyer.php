<?php

namespace App\Models;

use App\Models\User;
use App\Scopes\BuyerScope;
use App\Models\Transaction;
use App\Transformers\BuyerTransformer;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Buyer extends User
{
    use HasFactory;

    public $transformer = BuyerTransformer::class;

    protected static function boot()
    {
    	parent::boot();
        // Without global scope, when we are using implicit binding,
        // eloquent will return the regular user
        // global scope specifies, how to distinguish between user and buyer.
    	static::addGlobalScope(new BuyerScope);
    }

    public function transactions()
    {
    	return $this->hasMany(Transaction::class);
    }
}
