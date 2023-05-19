<?php
use Illuminate\Http\Request;

function getGeolocationData($ipAddress)
{
    $url = "http://ip-api.com/json/$ipAddress";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if ($response === false) {
        error_log('cURL error: ' . curl_error($ch));
        return ['error' => 'An error occurred while fetching the geolocation data.'];
    }

    $data = json_decode($response, true);

    if (is_array($data) && isset($data['lat']) && isset($data['lon']) && isset($data['city']) && isset ($data['regionName']) && isset($data['country'])) {
       
        $filtered_data = [
            'latitude' => $data['lat'],
            'longitude' => $data['lon'],
            'city' => $data['city'],
            'region' => $data['regionName'],
            'country' => $data['country']
        ];

    } else {
        return ['error' => 'Invalid or incomplete geolocation data.'];
    }

    curl_close($ch);

    return $filtered_data;
}

Route::post('/weather', function (Request $request) {
    $ipAddress = $request->input('IpAddress');
    if (!$ipAddress) {
        return response()->json(['error' => 'Missing required parameter: ip_address'], 400);
    }

    $geolocationData = getGeolocationData($ipAddress);

    $weatherapiKey = getenv('WEATHER_API_KEY');

    $url = "http://api.weatherapi.com/v1/current.json?key=$weatherapiKey&q={$geolocationData['latitude']},{$geolocationData['longitude']}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if ($response === false) {
        error_log('cURL error: ' . curl_error($ch));
        return ['error' => 'An error occurred while fetching the weather data.'];
    }

    $data = json_decode($response, true);

    $filtered_data = [
        'Geolocation' => $geolocationData['city'] . ', ' . $geolocationData['region'] . ', ' . $geolocationData['country'],
        'TemperatureC' => $data['current']['temp_c'],
        'TemperatureF' => $data['current']['temp_f'],
        'Condition' => $data['current']['condition']['text'],
        'Humidity' => $data['current']['humidity'],
        'Wind' => $data['current']['wind_kph']
    ];

    return response()->json($filtered_data);
});