<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;


class SellerScope implements Scope
{
	public function apply(Builder $builder, Model $model)
	{
		// This will be added to builder, whenewer we're trying to get
		// model with this scope. Scope is added in Model.boot method
		$builder->has('products');
	}
}