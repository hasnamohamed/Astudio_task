<?php

namespace App\Http\Apis;

use App\Http\Controllers\Controller;
use App\Services\JobFilterService;
use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request)
    {
        $filterService = new JobFilterService($request->all());
        $query = $filterService->apply();
        $jobs = $query->with(['languages', 'locations', 'categories', 'attributeValues.attribute'])->get();
        return response()->json($jobs);

    }
}
