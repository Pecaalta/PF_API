<?php namespace App\model;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Companyuser extends Model
{

    protected $table = 'company_user';

    protected $fillable = [
        'id_company', 'id_user', 'id_role', 'edit_info', 'delete_company', 'remove_user', 'edit_role', 'active'
    ];

}
