<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'tweet_text',];

    public function user()
    {
        return $this->belongsTo('App\Models\User', "user_id");
    }

    public function getName()
    {
        return ($this->user)->name;
    }

}
