<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/serper-config.php';

function searchRecyclingCenters($query) {
    global $serper_api_key;
    
    $curl = curl_init();
    
    // Force search within Manila
    $searchQuery = $query . " Manila Philippines";

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://google.serper.dev/maps',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode([
            'q' => $searchQuery,
            'gl' => 'ph',  // Philippines
            'hl' => 'en',  // English
            'location' => 'Manila, Metro Manila, Philippines',
            'num' => 20    // Get more results to filter
        ]),
        CURLOPT_HTTPHEADER => array(
            'X-API-KEY: ' . $serper_api_key,
            'Content-Type: application/json'
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);

    if ($err) {
        return json_encode([
            'success' => false,
            'error' => 'Failed to fetch data: ' . $err
        ]);
    }

    try {
        $data = json_decode($response, true);
        $centers = [];

        if (isset($data['places']) && is_array($data['places'])) {
            foreach ($data['places'] as $place) {
                // Check if the location is in Manila
                $address = strtolower($place['address'] ?? '');
                if (strpos($address, 'manila') !== false) {
                    // Format hours if available
                    $hours = null;
                    if (isset($place['workingHours'])) {
                        $hours = is_array($place['workingHours']) 
                            ? implode("\n", $place['workingHours']) 
                            : $place['workingHours'];
                    }

                    $centers[] = [
                        'name' => $place['title'] ?? 'N/A',
                        'address' => $place['address'] ?? 'N/A',
                        'rating' => $place['rating'] ?? null,
                        'reviews' => $place['reviewsCount'] ?? 0,
                        'phone' => $place['phoneNumber'] ?? null,
                        'website' => $place['website'] ?? null,
                        'hours' => $hours,
                        'latitude' => $place['latitude'] ?? null,
                        'longitude' => $place['longitude'] ?? null
                    ];
                }
            }
        }

        return json_encode([
            'success' => true,
            'centers' => $centers
        ]);
    } catch (Exception $e) {
        return json_encode([
            'success' => false,
            'error' => 'Failed to process data: ' . $e->getMessage()
        ]);
    }
}

// Handle the request
if (isset($_GET['q'])) {
    echo searchRecyclingCenters($_GET['q']);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'No query provided'
    ]);
}