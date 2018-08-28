<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Requests extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'offer_id', 'stylist_id', 'price', 'comment',
    ];

}
