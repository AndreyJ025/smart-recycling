<?php

session_start();
header('Access-Control-Allow-Origin: *');


$url = 'https://serpapi.com/search.json?q=' . ($_GET['q'] ?? 'apple') . '&engine=google_images&ijn=0';

// Initialize cURL session
$ch = curl_init($url);

// Set options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch); 
} else {
    // Decode the JSON response
    $data = json_decode($response, true);

    // // Access the data
    // $name = $data['name'];
    // $age = $data['age'];

    echo '<center>';

    // var_dump($data);
    foreach ($data['suggested_searches'] as $key => $value) {
        echo '<img src="' . $value['thumbnail'] . '" style="width: 30%; margin: 5%; display: inline-block; vertical-align: top;"/>';
    }

    echo '</center>';
}

// Close the cURL session
curl_close($ch);