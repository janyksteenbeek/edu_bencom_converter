<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Press extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    public $dates = ['date'];
}
