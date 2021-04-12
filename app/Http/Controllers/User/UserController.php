<?php

namespace App\Http\Controllers\User;

use App\Models\User;
use App\Mail\UserCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Transformers\UserTransformer;
use App\Http\Controllers\ApiController;

class UserController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('transform.input:'.UserTransformer::class)
            ->only(['store', 'update']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();

        return $this->showAll($users);
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
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ];

        $this->validate($request, $rules);

        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        // explicitly replace those values, to prevent user from setting them
        $data['verified'] = User::UNVERIFIED_USER; 
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        // massive assigment, is posible becaouse of $fillable field
        $user = User::create($data);

        //201 = has been successfully created
        return $this->showOne($user, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        // we can also do
        // show($id)
        // $user = User::findOrFail($id);
        // 
        // we can pass $user because of implicit binding

        return $this->showOne($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $rules = [
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'min:6|confirmed',
            'admin' => 'in:'.User::ADMIN_USER.','.User::REGULAR_USER,
        ];

        $this->validate($request, $rules);

        if($request->has('name')){
            $user->name = $request->name;
        }
        if($request->has('email') && $user->email !== $request->email){
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email = $request->email;
        }
        if($request->has('password')){
            $user->password = bcrypt($request->password);
        }
        if($request->has('admin')){
            if(!$user->isVerified()){
                $msg = 'Only verified users can modify the admin field';
                // 409 means 'conflict'
                return $this->errorResponse($msg, 409);
            }

            $user->admin = $request->admin;
        }

        //isDirty method returns true, if model has been changed
        //isClean is opposite
        if($user->isClean()){
            $msg = 'You need to specify a different value to update';
            // 422 means 'understood, but cannod process'
            return $this->errorResponse($msg, 422);
        }

        $user->save();

        return $this->showOne($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        $user->delete();
        return $this->showOne($user);
    }

    public function verify($token)
    {
        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::VERIFIED_USER;
        $user->verification_token = null;
        $user->email_verified_at = now();

        $user->save();

        return $this->showMessage('Account has been verified');
    }

    public function resend(User $user)
    {
        if($user->isVerified()){
            $msg = 'This user is already verified';
            return $this->errorResponse($msg, 409);
        }

        retry(5, function() use ($user){
            Mail::to($user)->send(new UserCreated($user));
        }, 100);

        return $this->showMessage('The verification email has been resend');
    }
}