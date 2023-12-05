<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class User extends Model
{
    protected $fillable = ['name', 'email', 'firebase_uid',];

    public function tweet()
    {
        return $this->hasMany('App\Models\Tweet');
    }

    public function like()
    {
        return $this->hasMany('App\Models\Like');
    }

}
