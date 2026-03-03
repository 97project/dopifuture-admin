<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function countries(): JsonResponse
    {
        $countries = Country::orderBy('name')->get(['id', 'name', 'code']);
        return response()->json($countries);
    }

    public function states(int $countryId): JsonResponse
    {
        $states = State::where('country_id', $countryId)->orderBy('name')->get(['id', 'name']);
        return response()->json($states);
    }

    public function cities(int $stateId): JsonResponse
    {
        $cities = City::where('state_id', $stateId)->orderBy('name')->get(['id', 'name']);
        return response()->json($cities);
    }
}
