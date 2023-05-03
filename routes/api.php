<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/weather', function(Request $request) {
    $weatherData = "It is cloudy today!";
    return response()->json($weatherData);
});