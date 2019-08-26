<?php namespace App\model;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model
{

    protected $table = 'user';

    protected $fillable = [
        'name', 'email', 'password', 'admin', 'active', 'remember_token', 'img'
    ];

    protected $hidden = [
        'password', 'active'
    ];

    public static $rules = [
        "name" => "required",
        "email" => "email|unique:users,email_address",
    ];

}
