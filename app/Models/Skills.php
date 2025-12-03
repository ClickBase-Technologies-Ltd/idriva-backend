<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skills extends Model
{
    protected $table = 'skills';

    protected $primaryKey = 'skillId';

    protected $fillable = [
        'userId',
        'skillName',
        'skillLevel',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'userId');
    }

 
}
