<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

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

}
