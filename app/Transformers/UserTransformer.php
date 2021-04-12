<?php

namespace App\Transformers;

use App\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
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
    public function transform(User $user)
    {
        // we can even change value types, like 
        // 'id' => (string) $user->id
        return [
            'identifier' => (int) $user->id,
            'name' => (string) $user->name,
            'email' => (string) $user->email,
            'isVerified' => (int) $user->verified,
            'isAdmin' => ($user->admin === User::ADMIN_USER),
            'creationDate' => (string) $user->created_at,
            'lastChange' => (string) $user->updated_at,
            'deletedDate' => isset($user->deleted_at) ? (string) $user->deleted_at : null,

            // HATEOAS - we're sending to the client links related to this
            // resource, 
            'links' => [
                [
                    'rel' => 'self',
                    'href' => route('users.show', $user->id),
                ],
            ]
        ];
    }

    // We need to map transformed names to the original ones,
    // so we can use them in sortBy method.
    // also it defines fields, which could be used in sort operations.
    public static function originalAttribute($index)
    {
        $attributes = [
            'identifier' => 'id',
            'name' => 'name',
            'email' => 'email',
            'isVerified' => 'verified',
            'isAdmin' => 'admin',
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
            'admin' => 'isAdmin',
            'created_at' => 'creationDate',
            'updated_at' => 'lastChange',
            'deleted_at' => 'deletedDate'
        ];


        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
