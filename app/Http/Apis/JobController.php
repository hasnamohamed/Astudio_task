<?php

namespace App\Http\Apis;

use App\Http\Controllers\Controller;
use App\Services\JobFilterService;
use Illuminate\Http\Request;

class JobController extends Controller
{
//    protected $filterService;
//
//    public function __construct(JobFilterService $filterService)
//    {
//        $this->filterService = $filterService;
//    }
//
//    public function index(Request $request)
//    {
//        $filters = $request->all();
//        $jobs = $this->filterService->applyFilters($filters)->get();
//
//        return response()->json($jobs);
//    }
    public function index(Request $request)
    {
        $filterService = new JobFilterService($request->all());
        $query = $filterService->apply();

        // Add sorting, pagination, etc.
        $jobs = $query->with(['languages', 'locations', 'categories', 'attributeValues.attribute'])
            ->published()
            ->paginate($request->get('per_page', 15));

        return response()->json($jobs);
    }

}
