<?php

namespace App\Http\Apis;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Services\JobFilterService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    protected JobFilterService $filterService;

    public function __construct(JobFilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function index(Request $request)
    {
        $query = Job::query()->with(['languages', 'locations', 'categories', 'attributes']);

        if ($request->has('filter')) {
            $this->filterService->applyFilters($query, $request->filter);
        }

        $jobs = $query->get();

        return response()->json($jobs);
    }
}
