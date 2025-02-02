<?php

session_start();
header('Access-Control-Allow-Origin: *');

require 'vendor/serpapi/google-search-results-php/google-search-results.php';
require 'vendor/serpapi/google-search-results-php/restclient.php';
require 'config/serpapi-config.php';

$query = [
 "q" => ($_GET['q'] ?? ''),
 "engine" => "google_images",
 "ijn" => "0",
];

$search = new GoogleSearchResults('ae25786ca2d55c46dcd777126c354a10e311abfa435427fa2012cf582f1e4ed5');
$result = $search->get_json($query);
$images_results = $result->images_results;

echo '<center>';

    echo '<div style="width: 45%; display: inline-block; vertical-align: top;">';
    $i = 0;
    foreach ($images_results as $key => $value) {
        if($i <= 13) {
            echo '<img src="' . $value->original . '" style="width: 95%; margin: 3%;"/>';
        }

        $i++;
    }
    echo '</div>';

    echo '<div style="width: 45%; display: inline-block; vertical-align: top;">';
    $i = 0;
    foreach ($images_results as $key => $value) {
        if($i >= 14 && $i < 26) {
            echo '<img src="' . $value->original . '" style="width: 95%; margin: 3%;"/>';
        }

        $i++;
    }
    echo '</div>';

echo '</center>';


// $url = 'https://serpapi.com/search.json?q=apple&engine=google_images&ijn=0';

// // Initialize cURL session
// $ch = curl_init($url);

// // Set options
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// // Execute the request
// $response = curl_exec($ch);

// // Check for errors
// if (curl_errno($ch)) {
//     echo 'Error:' . curl_error($ch); 
// } else {
//     // Decode the JSON response
//     $data = json_decode($response, true);

//     // // Access the data
//     // $name = $data['name'];
//     // $age = $data['age'];

//     echo '<center>';

//     // var_dump($data);
//     foreach ($images_results as $key => $value) {
//         echo '<img src="' . $value->original . '" style="width: 30%; margin: 5%; display: inline-block; vertical-align: top;"/>';
//     }

//     echo '</center>';
// }

// // Close the cURL session
// curl_close($ch);