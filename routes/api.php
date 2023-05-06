<?php

use Illuminate\Http\Request;

Route::post('/geolocation', function(Request $request) {
    $ipAddress = $request->input('ip_address');
    if (!$ipAddress) {
        return response()->json(['error' => 'Missing required parameter: ip_address'], 400);
    }

    $url = "http://ip-api.com/json/$ipAddress";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if ($response === false) {
        error_log('cURL error: ' . curl_error($ch));
        return response()->json(['error' => 'An error occurred while fetching the geolocation data.'], 500);
    }

    $data = json_decode($response, true);

    if (is_array($data) && isset($data['lat']) && isset($data['lon']) && isset($data['city']) && isset($data['country'])) {
        $city = $data['city'];
        $region = $data['regionName'];
        $country = $data['country'];
    } else {
        return response()->json(['error' => 'Invalid or incomplete geolocation data.'], 400);
    }

    curl_close($ch);

    $filtered_data = [
        'city' => $city,
        'region' => $region,
        'country' => $country
    ];

    return response()->json(['data' => $filtered_data]);
});