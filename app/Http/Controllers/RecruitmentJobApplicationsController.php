<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentJobApplications;
use App\Models\RecruitmentJobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class RecruitmentJobApplicationsController extends Controller
{
    public function index()
    {
        $applications = RecruitmentJobApplications::with('job')->get();
        return response()->json($applications);
       
    }

   

    public function store(Request $request)
    {
        // Directly get the data from the request
        $data = $request->all();
    
         $validated = $request->validate([
            'jobId' => 'required|integer|exists:recruitment_jobs,jobId',
            'coverLetter' => 'required|string',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048', // 2MB max
            'applicationStatus' => 'required|string',
        ]);

        $validated['applicantId'] = auth()->id();
        $validated['applicationDate'] = now();
        $applications = RecruitmentJobApplications::create($validated);
    
        // Return a response, typically JSON
        return response()->json($applications, 201); // HTTP status code 201: Created
    }



    public function checkApplicationStatus($jobId)
    {
        try {
            $user = Auth::user();
            
            if (!$user) {
                return response()->json([
                    'hasApplied' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $job = RecruitmentJobs::find($jobId);
            
            if (!$job) {
                return response()->json([
                    'hasApplied' => false,
                    'message' => 'Job not found'
                ], 404);
            }

            $application = RecruitmentJobApplications::where('jobId', $jobId)
                ->where('applicantId', $user->id)
                ->first();

            return response()->json([
                'hasApplied' => !is_null($application),
                'application' => $application,
                'applicationStatus' => $application ? $application->status : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'hasApplied' => false,
                'message' => 'Error checking application status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
}
