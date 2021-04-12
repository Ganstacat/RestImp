<?php

namespace App\Transformers;

use App\Models\Seller;
use League\Fractal\TransformerAbstract;

class SellerTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        //
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Seller $seller)
    {
        // we removed isAdmin field, so it won't show up in responses...
        return [
            'identifier' => (int) $seller->id,
            'name' => (string) $seller->name,
            'email' => (string) $seller->email,
            'isVerified' => (int) $seller->verified,
            'creationDate' => (string) $seller->created_at,
            'lastChange' => (string) $seller->updated_at,
            'deletedDate' => isset($seller->deleted_at) ? (string) $seller->deleted_at : null,
        
            // HATEOAS - we're sending to the client links related to this
            // resource, 
            'links' => [
                [
                    'rel' => 'self',
                    'href' => route('sellers.show', $seller->id),
                ],
                [
                    'rel' => 'user',
                    'href' => route('users.show', $seller->id),
                ],
                [
                    'rel' => 'seller.categories',
                    'href' => route('sellers.categories.index', $seller->id),
                ],
                [
                    'rel' => 'seller.buyers',
                    'href' => route('sellers.buyers.index', $seller->id),
                ],
                [
                    'rel' => 'seller.products',
                    'href' => route('sellers.products.index', $seller->id),
                ],
                [
                    'rel' => 'seller.transactions',
                    'href' => route('sellers.transactions.index', $seller->id),
                ],
            ]
        ];
    }

    // We need to map transformed names to the original ones,
    // so we can use them in sortBy method.
    // also it constraints fields, by which sorting can be performed
    public static function originalAttribute($index)
    {
        $attributes = [
            'identifier' => 'id',
            'name' => 'name',
            'email' => 'email',
            'isVerified' => 'verified',
            'creationDate' => 'created_at',
            'lastChange' => 'updated_at',
            'deletedDate' => 'deleted_at'
        ];


        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    // tranforming attributes back. We need this to show correct validation 
    // errors.
    public static function transformedAttribute($index)
    {
        $attributes = [
            'id' => 'identifier',
            'name' => 'name',
            'email' => 'email',
            'verified' => 'isVerified',
            'created_at' => 'creationDate',
            'updated_at' => 'lastChange',
            'deleted_at' => 'deletedDate'
        ];


        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
