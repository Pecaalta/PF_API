<?php namespace App\model;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class companyrole extends Model
{

    protected $table = 'company_role';

    protected $fillable = [
        'id', 'id_company', 'key_add', 'edit_info', 'delete_company', 'remove_user', 'edit_role', 'active'
    ];

}
