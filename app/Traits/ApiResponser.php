<?php

namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

//Generalizing api responses
trait ApiResponser
{
	private function successResponse($data, $code)
	{
		return response()->json($data, $code);
	}

	protected function errorResponse($message, $code)
	{
		return response()->json(['error' => $message, 'code' => $code], $code);
	}

	protected function showAll(Collection $collection, $code = 200)
	{
		if($collection->isEmpty()){
			return $this->successResponse(['data' => $collection], $code);			
		}

		$transformer = $collection->first()->transformer;

		$collection = $this->filterData($collection, $transformer);
		$collection = $this->sortData($collection, $transformer);
		$collection = $this->paginate($collection);
		
		$collection = $this->transformData($collection, $transformer);

		$collection = $this->cacheResponse($collection);

		// not 'data' => collection, because 'fractal' automatically
		// adds data=> ...
		return $this->successResponse($collection, $code);
	}

	protected function showOne(Model $instance, $code = 200)
	{
		$transformer = $instance->transformer;
		$instance = $this->transformData($instance, $transformer);

		return $this->successResponse($instance, $code);
	}

	protected function showMessage($msg, $code = 200)
	{
		return $this->successResponse(['data' => $msg], $code);
	}

	protected function sortData(Collection $collection, $transformer)
	{
		if(request()->has('sort_by')){
			$attribute =$transformer::originalAttribute(request()->sort_by);
			// if attribute does not exist, sortBy wouldn't throw an error, 
			// and collection would be in default order. 

			// sortBy->{$attribute} means
			//  suppose $attribute === 'id', then in runtime this would become
			//  sortBy->id 
			$collection = $collection->sortBy->{$attribute}; 
		}
		return $collection;
	}

	protected function filterData(Collection $collection, $transformer)
	{
		foreach(request()->query() as $index => $value){
			$attribute = $transformer::originalAttribute($index);

			if(isset($attribute, $value)){
				$collection = $collection->where($attribute, $value);
			}
		}

		return $collection;
	}

	protected function paginate(Collection $collection)
	{
		$rules = [
			'per_page' => 'integer|min:2|max:50'
		];
		Validator::validate(request()->all(), $rules);

		$perPage = request()->has('per_page') ? (int) request()->per_page : 15;

		$page = LengthAwarePaginator::resolveCurrentPage();
		
		$results = $collection->slice(($page-1) * $perPage, $perPage)->values();

		// this option allows us to resolve path to next and previous page
		// depending on current page
		$options = ['path'=> LengthAwarePaginator::resolveCurrentPath()];
		$paginated = new LengthAwarePaginator(
			$results, 
			$collection->count(), 
			$perPage, 
			$page, 
			$options
		);

		// append all the other query parameters to the "next page" link
		$paginated->appends(request()->all());
		return $paginated;
	}

	protected function transformData($data, $transformer)
	{
		//fractal is helper provided by spatie/laravel-fractal
		$transformation = fractal($data, new $transformer);
		// toArray, because 'fractal' returns instance of class Fractal,
		// and the other code doesn't know how to deal with it. 
		return $transformation->toArray();
	}

	protected function cacheResponse($data)
	{
		$url = request()->url();

		$query = request()->query();
		// sorting, so '?page=2&order_by=name' and '?order_by=name&page=2'
		// would give the same identifier for Cache::remember
		ksort($query); 

		$queryString = http_build_query($query);

		$fullUrl = $url.'?'.$queryString;
		return Cache::remember($fullUrl, 30, function() use($data){
			return $data;
		});
	}


}