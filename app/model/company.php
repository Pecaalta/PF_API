<?php namespace App\model;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Company extends Model
{

    protected $table = 'company';

    protected $fillable = [
        'id', 'name', 'rut', 'description', 'debts', 'personal', 'x', 'y', 'active'
    ];

}
