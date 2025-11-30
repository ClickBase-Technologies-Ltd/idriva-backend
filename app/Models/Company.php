<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'companies';

    protected $primaryKey = 'companyId';

    protected $fillable = [
        'companyId',
        'companyName',
        'companyAddress',
        'companyEmail',
        'companyPhone',
        'companyWebsite',
        'companyDescription',
        'companyLogo',
        'companyIndustry',
        'companySize',
        'companyLocation',
        'companyFoundedYear',
        'companyStatus',
        'createdBy',
    ];
}
