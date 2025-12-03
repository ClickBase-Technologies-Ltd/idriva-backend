<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecruitmentJobs;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    public function index()
    {
        $jobs = RecruitmentJobs::with('company')->get();
        return response()->json($jobs);
       
    }

   

   public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'companyId' => 'required|integer',
            'jobTitle' => 'required|string',
        'jobTitle' => 'required|string',
        'jobDescription' => 'required|string',
        'jobLocation' => 'required|string',
        'jobType' => 'required|string',
        'salary' => 'required|string',
        'applicationDeadline' => 'date',
            'jobImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 5MB max
        ]);

        // Handle logo upload
        if ($request->hasFile('jobImage')) {
            $logoFile = $request->file('jobImage');
            $logoPath = $logoFile->store('job-images', 'public');
            $validated['jobImage'] = $logoPath;
        } else {
            $validated['jobImage'] = null;
        }

        // Create the company
        $validated['postedBy'] = auth()->id();
        $job = RecruitmentJobs::create($validated);

        // Return a response, typically JSON
        return response()->json($job, 201); // HTTP status code 201: Created
    }


    public function show($id)
    {
        $job = RecruitmentJobs::find($id);
        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }
        return response()->json($job);
    }


   public function myJobs()
{
    $jobs = RecruitmentJobs::where('postedBy', auth()->id())
        ->with('company')
        ->withCount('applications')  // <-- important
        ->get();

    if ($jobs->isEmpty()) {
        return response()->json(['message' => 'No jobs found'], 404);
    }

    return response()->json($jobs);
}


    public function update(Request $request, $id)
    {
        // Find the company
        $job = RecruitmentJobs::findOrFail($id);

        // Validate the request data
        $validated = $request->validate([
            'companyId' => 'integer',
            'jobTitle' => 'required|string',
        'jobTitle' => 'required|string',
        'jobDescription' => 'required|string',
        'jobLocation' => 'required|string',
        'jobType' => 'required|string',
        'salary' => 'required|integer',
        'applicationDeadline' => 'date',
        'jobStatus' => 'required|string',
            'jobImage' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle logo upload
        if ($request->hasFile('jobImage')) {
            // Delete old logo if exists
            if ($job->jobImage) {
                Storage::disk('public')->delete($job->jobImage);
            }

            $logoFile = $request->file('jobImage');
            $logoPath = $logoFile->store('job-images', 'public');
            $validated['jobImage'] = $logoPath;
        } else {
            // Keep the existing logo if no new file is uploaded
            $validated['jobImage'] = $job->jobImage;
        }

        // Update the company
        $job->update($validated);

        return response()->json($job, 200);
    }

    public function destroy($id)
    {
        $job = RecruitmentJobs::find($id);
        if (!$job) {
            return response()->json(['message' => 'Job not found'], 404);
        }

        $job->delete();
        return response()->json(['message' => 'Job deleted successfully']);
    }
}
