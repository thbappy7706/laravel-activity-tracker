<?php

namespace thbappy7706\ActivityTracker\Tests;

use Illuminate\Foundation\Auth\User as Authenticatable;
use thbappy7706\ActivityTracker\Traits\HasActivity;

class User extends Authenticatable
{
    use HasActivity;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
