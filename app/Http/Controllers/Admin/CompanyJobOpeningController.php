<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanyJobOpening;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyJobOpeningController extends Controller
{
    /**
     * Display a listing of job openings.
     */
    public function index(): Response
    {
        $jobOpenings = CompanyJobOpening::byPriority()
            ->get()
            ->map(function ($job) {
                return [
                    'id' => $job->id,
                    'title' => $job->title,
                    'department' => $job->department,
                    'location' => $job->location,
                    'type' => $job->type,
                    'status' => $job->status,
                    'posted_ago' => $job->posted_ago,
                    'priority' => $job->priority,
                    'is_expired' => $job->is_expired,
                ];
            });

        return Inertia::render('Admin/JobOpenings/Index', [
            'jobOpenings' => $jobOpenings,
        ]);
    }

    /**
     * Show the form for creating a new job opening.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/JobOpenings/Create', [
            'jobTypes' => CompanyJobOpening::getJobTypes(),
            'statusOptions' => CompanyJobOpening::getStatusOptions(),
        ]);
    }

    /**
     * Store a newly created job opening.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'type' => 'required|in:full-time,part-time,contract,internship',
            'experience_level' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'required|array|min:1',
            'requirements.*' => 'required|string',
            'salary_range' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,filled',
            'contact_email' => 'nullable|email|max:255',
            'priority' => 'nullable|integer|min:0|max:100',
            'application_deadline' => 'nullable|date|after:today',
        ]);

        // Clean up requirements array
        $validated['requirements'] = array_filter($validated['requirements'], function($req) {
            return !empty(trim($req));
        });

        $jobOpening = CompanyJobOpening::create($validated);

        return redirect()
            ->route('admin.job-openings.index')
            ->with('success', 'Job opening created successfully!');
    }

    /**
     * Display the specified job opening.
     */
    public function show(CompanyJobOpening $jobOpening): Response
    {
        return Inertia::render('Admin/JobOpenings/Show', [
            'jobOpening' => $jobOpening,
        ]);
    }

    /**
     * Show the form for editing the specified job opening.
     */
    public function edit(CompanyJobOpening $jobOpening): Response
    {
        return Inertia::render('Admin/JobOpenings/Edit', [
            'jobOpening' => [
                'id' => $jobOpening->id,
                'title' => $jobOpening->title,
                'department' => $jobOpening->department,
                'location' => $jobOpening->location,
                'type' => $jobOpening->type,
                'experience_level' => $jobOpening->experience_level,
                'description' => $jobOpening->description,
                'requirements' => $jobOpening->requirements,
                'salary_range' => $jobOpening->salary_range,
                'status' => $jobOpening->status,
                'contact_email' => $jobOpening->contact_email,
                'priority' => $jobOpening->priority,
                'application_deadline' => $jobOpening->application_deadline?->format('Y-m-d'),
            ],
            'jobTypes' => CompanyJobOpening::getJobTypes(),
            'statusOptions' => CompanyJobOpening::getStatusOptions(),
        ]);
    }

    /**
     * Update the specified job opening.
     */
    public function update(Request $request, CompanyJobOpening $jobOpening)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'type' => 'required|in:full-time,part-time,contract,internship',
            'experience_level' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'required|array|min:1',
            'requirements.*' => 'required|string',
            'salary_range' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,filled',
            'contact_email' => 'nullable|email|max:255',
            'priority' => 'nullable|integer|min:0|max:100',
            'application_deadline' => 'nullable|date',
        ]);

        // Clean up requirements array
        $validated['requirements'] = array_filter($validated['requirements'], function($req) {
            return !empty(trim($req));
        });

        $jobOpening->update($validated);

        return redirect()
            ->route('admin.job-openings.index')
            ->with('success', 'Job opening updated successfully!');
    }

    /**
     * Remove the specified job opening.
     */
    public function destroy(CompanyJobOpening $jobOpening)
    {
        $jobOpening->delete();

        return redirect()
            ->route('admin.job-openings.index')
            ->with('success', 'Job opening deleted successfully!');
    }
}
