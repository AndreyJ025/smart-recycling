<?php
session_start();
header('Access-Control-Allow-Origin: *');

require 'vendor/serpapi/google-search-results-php/google-search-results.php';
require 'vendor/serpapi/google-search-results-php/restclient.php';
require 'config/serpapi-config.php';

if (isset($_GET['q'])) {
    $query = [
        "q" => $_GET['q'],
        "engine" => "google_images",
        "ijn" => "0"
    ];

    try {
        $search = new GoogleSearchResults($serpapi_key);
        $result = $search->get_json($query);

        if (isset($result->images_results)) {
            echo '<div class="grid grid-cols-2 gap-4 p-4">';
            foreach (array_slice($result->images_results, 0, 20) as $image) {
                echo '<div class="relative group overflow-hidden rounded-xl">';
                echo '<img src="' . htmlspecialchars($image->original) . '" 
                           alt="Search result" 
                           class="w-full h-48 object-cover transition-transform duration-300 group-hover:scale-110"/>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="text-center text-white/80 p-4">No images found</div>';
        }
    } catch (Exception $e) {
        echo '<div class="text-center text-red-500 p-4">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>