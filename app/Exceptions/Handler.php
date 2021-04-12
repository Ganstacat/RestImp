<?php

namespace App\Exceptions;

use Exception;
use Throwable;
use App\Traits\ApiResponser;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (ValidationException $e, $request) {
            $errors = $e->validator->errors()->getMessages();
            return $this->errorResponse($errors, 422);
        });

        $this->renderable(function (MethodNotAllowedHttpException $e, $request) {
            return $this->errorResponse('Invalid method for the request', 405);
        });


        $this->renderable(function (AuthenticationException $e, $request){
            return $this->errorResponse('Unathenticated', 401);
        });

        $this->renderable(function (AuthorizationException $e, $request){
            return $this->errorResponse($e->getMessage(), 403); //403 no permissions
        });

        $this->renderable(function (QueryException $e, $request){
            $errorCode = $e->errorInfo[1];

            if($errorCode === 1451){
                $msg = 'Cannot remove this resource permamently, '.
                       'it is related with other resource';
                return $this->errorResponse($msg, 409);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, $request){
            $prevE = $e->getPrevious();
            if($prevE instanceof ModelNotFoundException){
                $modelName = class_basename($prevE->getModel());
                $msg = $modelName.' with this id does not exist';
                return $this->errorResponse($msg, 404);
            }
            return $this->errorResponse('The specified URL cannod be found', 404);
        });

        $this->renderable(function (HttpException $e, $request){
            return $this->errorResponse($e->getMessage(), $e->getStatusCode());
        });

        $this->renderable(function (Exception $e, $request){
            dd($e);

            if(!config('app.debug')){
                //If app is not in debug, show generic error without info
                //If debug, it'll ignore this and throw full exception
                $msg = 'Unexpected exception, try later';
                return $this->errorResponse($msg, 500);   
            }
        });



        // $this->reportable(function (Throwable $e) {
        //     //
        // });
    }
}
