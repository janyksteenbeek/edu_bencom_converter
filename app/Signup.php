<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Signup extends Model
{
    public static $COLOR_GREEN = 'GREEN';
    public static $COLOR_GREY = 'GREY';

    protected $guarded = [];
    public $timestamps = false;
    public $dates = ['signed_up_at'];
}
