<?php namespace App\model;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class companySector extends Model
{

    protected $table = 'company_sector';

    protected $fillable = [
        'id_company', 'id_sector', 'active'
    ];

}
