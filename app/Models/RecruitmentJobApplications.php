<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentJobApplications extends Model
{
     protected $table = 'recruitment_job_applications';

    protected $primaryKey = 'applicationId';
    protected $fillable = [
        'applicationId',
        'jobId',
        'applicantId',
        'applicationDate',
        'applicationStatus',
        'coverLetter',
        'resumePath',
        
    ];

    public function job()
    {
        return $this->belongsTo(RecruitmentJobs::class, 'jobId', 'jobId');
    }

public function applicant()
    {
        return $this->belongsTo(User::class, 'applicantId', 'id');
    }

   
}
