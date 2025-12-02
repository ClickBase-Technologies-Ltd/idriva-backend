<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skills extends Model
{
    protected $table = 'skills';

    protected $primaryKey = 'educationId';

    protected $fillable = [
        'userId',
        'skillName',
        'skillLevel',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

    public function likes()
    {
        return $this->hasMany(PostLikes::class, 'postId');
    }

    public function shares()
    {
        return $this->hasMany(PostShares::class, 'postId');
    }
}
