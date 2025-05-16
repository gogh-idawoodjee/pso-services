<?php
// app/Models/ExternalUser.php
namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Laravel\Sanctum\HasApiTokens;

class ExternalUser implements Authenticatable
{
    use HasApiTokens, AuthenticatableTrait;

    public $id;
    public $name;

    public function __construct($id, $name = null)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword()
    {
        return '';
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value)
    {
    }

    public function getRememberTokenName()
    {
        return null;
    }
}
