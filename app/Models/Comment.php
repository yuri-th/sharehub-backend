<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'tweet_id', 'comment'];

    public function user()
    {
        return $this->belongsTo('App\Models\User', "user_id");
    }

    public function tweet()
    {
        return $this->belongsTo('App\Models\Tweet', "tweet_id");
    }

}
