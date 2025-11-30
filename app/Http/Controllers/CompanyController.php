<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return response()->json($companies);
    }



    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'companyName' => 'required|string|max:255',
            'companyDescription' => 'required|string',
            'companyAddress' => 'required|string',
            'companyEmail' => 'required|email',
            'companyPhone' => 'nullable|string',
            'companyWebsite' => 'nullable|url',
            'companyIndustry' => 'required|string',
            'companySize' => 'required|string',
            'companyLocation' => 'required|string',
            'companyFoundedYear' => 'nullable|integer|min:1900|max:' . date('Y'),
            // 'companyStatus' => 'required|in:active,inactive',
            'companyLogo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 5MB max
        ]);

        // Handle logo upload
        if ($request->hasFile('companyLogo')) {
            $logoFile = $request->file('companyLogo');
            $logoPath = $logoFile->store('company-logos', 'public');
            $validated['companyLogo'] = $logoPath;
        } else {
            $validated['companyLogo'] = null;
        }

        // Create the company
        $validated['createdBy'] = auth()->id();
        $company = Company::create($validated);

        // Return a response, typically JSON
        return response()->json($company, 201); // HTTP status code 201: Created
    }


    public function show($id)
    {
        $company = Company::find($id);
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }
        return response()->json($company);
    }


    public function myCompanies()
    {
        $company = Company::where('companyStatus', 'active')
            ->where('createdBy', auth()->id())
            ->first();
        if (!$company) {
            return response()->json(['message' => 'No companies found'], 404);
        }
        return response()->json($company);
    }

    public function update(Request $request, $id)
    {
        // Find the company
        $company = Company::findOrFail($id);

        // Validate the request data
        $validated = $request->validate([
            'companyName' => 'required|string|max:255',
            'companyDescription' => 'required|string',
            'companyAddress' => 'required|string',
            'companyEmail' => 'required|email',
            'companyPhone' => 'nullable|string',
            'companyWebsite' => 'nullable|url',
            'companyIndustry' => 'required|string',
            'companySize' => 'required|string',
            'companyLocation' => 'required|string',
            'companyFoundedYear' => 'nullable|integer|min:1900|max:' . date('Y'),
            'companyStatus' => 'required|in:active,inactive',
            'companyLogo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // 5MB max
        ]);

        // Handle logo upload
        if ($request->hasFile('companyLogo')) {
            // Delete old logo if exists
            if ($company->companyLogo) {
                Storage::disk('public')->delete($company->companyLogo);
            }

            $logoFile = $request->file('companyLogo');
            $logoPath = $logoFile->store('company-logos', 'public');
            $validated['companyLogo'] = $logoPath;
        } else {
            // Keep the existing logo if no new file is uploaded
            $validated['companyLogo'] = $company->companyLogo;
        }

        // Update the company
        $company->update($validated);

        return response()->json($company, 200);
    }

    public function destroy($id)
    {
        $company = Company::find($id);
        if (!$company) {
            return response()->json(['message' => 'Company not found'], 404);
        }

        $company->delete();
        return response()->json(['message' => 'Company deleted successfully']);
    }
}
