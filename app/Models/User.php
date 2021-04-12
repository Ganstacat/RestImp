<?php

namespace App\Models;

use App\Mail\UserCreated;
use Illuminate\Support\Str;
use App\Mail\UserMailChanged;
use Illuminate\Support\Facades\Mail;
use App\Transformers\UserTransformer;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;



class User extends Authenticatable
{
    use HasFactory, Notifiable;
    use SoftDeletes;

    const VERIFIED_USER = '1';
    const UNVERIFIED_USER = '0';

    const ADMIN_USER = 'true';
    const REGULAR_USER = 'false';

    public $transformer = UserTransformer::class;

    // so Buyer and Seller can inherit users table
    protected $table = 'users';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        
        'verified',
        'verification_token',
        'admin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     * Removes those fields from responses
     * 
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',

        'verification_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function isAdmin()
    {
        return $this->admin === User::ADMIN_USER;
    }

    public function isVerified()
    {
        return $this->verified === User::VERIFIED_USER;
    }

    public static function generateVerificationCode()
    {
        $code = Str::random(40);
        return $code;
    }


    // Mutators (change value before inserting it in model), and
    // Accessors (change value before giving it out)
    public function setNameAttribute($name)
    {
        $this->attributes['name'] = strtolower($name);
    }
    public function getNameAttribute($name)
    {
        return ucwords($name);
    }
    public function setEmailAttribute($email)
    {
        $this->attributes['email'] = strtolower($email);
    }

    protected static function booted()
    {
        //retry if fails(how many times, what to do, interval between);
        static::created(function($user){
            // also we can type just $user, laravel will implicitly
            // take 'email' field.
            retry(5, function() use ($user){
                Mail::to($user->email)->send(new UserCreated($user));
            }, 100);
        });

        static::updated(function($user){
            if($user->isDirty('email')){
                retry(5, function() use ($user){
                    Mail::to($user)->send(new UserMailChanged($user));
                }, 100);
            }
        });
    }
}
