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
    public function offer()
    {
        return $this->hasMany(Offer::class);
    }

    public function review()
    {
        return $this->hasMany(Review::class);
    }

    public function requests()
    {
        return $this->hasMany(Requests::class);
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

    function getSalonLocation()
    {
        $value = substr($this->salon_location, strlen('POINT('), strlen($this->salon_location) - (strlen('POINT(') + 1));
        $value = explode(" ", $value);
        $ret = [];
        $ret['lat'] = floatval($value[0]);
        $ret['lng'] = floatval($value[1]);
        return $ret;
    }

    public function newQuery($excludeDeleted = true)
    {
        $raw='';
        foreach(array('salon_location') as $column){
            $raw .= ' astext('.$column.') as '.$column.' ';
        }
        return parent::newQuery($excludeDeleted)->addSelect('*',DB::raw($raw));
    }
}
