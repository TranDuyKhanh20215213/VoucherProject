<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\City;

class CountryCityDropdownController extends Controller
{
    public function fetchCountry()
    {
        $data['countries'] = Country::get(["name", "id"]);

        return response()->json($data);
    }

    public function fetchCity(Request $request)
    {
        $request->validate([
            'country_id' => 'required|exists:countries,id',
        ]);

        $data['cities'] = City::where("country_id", $request->country_id)
                              ->get(["name", "id"]);

        return response()->json($data);
    }
}
