<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
    * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function offers()
    {
        return $this->hasMany(Offers::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
    * geometry attribute method
    */
    function setSalonLocation(array $value)
    {
        $this->attributes['salon_location'] = DB::raw("(GeomFromText('POINT(" . $value['lat'] . " " . $value['lng'] . ")'))");
    }
}
