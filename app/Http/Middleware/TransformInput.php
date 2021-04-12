<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TransformInput
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $transformer)
    {
        $transformedInput = [];

        foreach ($request->request->all() as $input => $value) {
            // $input = 'identifier'
            // originalAttr($input) = 'id'
            // transformedInput['id'] = $value
            $transformedInput[$transformer::originalAttribute($input)] = $value;
        }

        $request->replace($transformedInput);

        $response = $next($request);

        // if we have an error, we transform error string and keys
        // 'id' => 'specify id, booya'
        // becomes
        // 'identifier' => 'specify identifier, booya'
        // maybe should move this to the other middleware? Or not, huh
        if( isset($response->exception) && 
            $response->exception instanceof ValidationException
        ){
            $data = $response->getData();

            $transformedErrors = [];

            foreach($data->error as $field => $error){
                $transformedField = $transformer::transformedAttribute($field);

                $transError = str_replace($field, $transformedField, $error);
                $transformedErrors[$transformedField] = $transError;
            }

            $data->error = $transformedErrors;

            $response->setData($data);
        }

        return $response;
    }
}
