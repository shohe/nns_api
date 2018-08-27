<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Offer extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function users()
    {
        return $this->belongsTo(Users::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'cx_id', 'menu', 'price', 'date_time', 'hair_type', 'charity_id', 'distance_range', 'from_location', 'stylist_id', 'comment',
    ];

    /**
    * geometry attribute method
    */
    static function castToGeometry(array $value)
    {
        return DB::raw("(GeomFromText('POINT(" . $value['lat'] . " " . $value['lng'] . ")'))");
    }

    static function getLocationAttribute(string $value)
    {
        $_value = substr($value, strlen('POINT('), strlen($value) - (strlen('POINT(') + 1));
        $_value = explode(" ", $value);
        $ret = [];
        $ret['lat'] = $_value[0];
        $ret['lng'] = $_value[1];
        return $ret;
    }

    public function newQuery($excludeDeleted = true)
    {
        $raw='';
        foreach(array('from_location') as $column){
            $raw .= ' astext('.$column.') as '.$column.' ';
        }
        return parent::newQuery($excludeDeleted)->addSelect('*',DB::raw($raw));
    }

}
