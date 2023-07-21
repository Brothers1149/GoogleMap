<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class MapController extends Controller{

    public function getDirections(Request $request){
        Log::info($request);
        // restaurants
        $response = \GoogleMaps::load('directions')->setParam([
            'origin' =>'place_id:'.$request->input('locationStart')
            , 'destination' => 'place_id:'.$request->input('locationEnd')
        ])->get();

        return response()->json(json_decode($response),200,['Content-Type' => 'application/json;charset=UTF-8', 'Charset' => 'utf-8'], JSON_UNESCAPED_UNICODE);
    }

    public function getRestaurants(Request $request){
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');

        // Validate latitude and longitude inputs
        Log::info($request);

        $client = new \GuzzleHttp\Client();

        // Get nearby cafes using Google Places API
        $response = $client->get("https://maps.googleapis.com/maps/api/place/nearbysearch/json", [
            'query' => [
                'key' => 'AIzaSyCZi9MB9rv5iXekRpDfNkJilgNmyFvUhb0',
                'location' => $latitude . ',' . $longitude,
                'radius' => 1000, // The radius in meters to search for cafes
                'type' => 'restaurant',
            ]
        ]);
        return $response;
        
    }

}
