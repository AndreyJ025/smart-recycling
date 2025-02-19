<?php
session_start();
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/../vendor/serper/serper-search-results.php';
require_once __DIR__ . '/../config/serper-config.php';

if (isset($_GET['q'])) {
    try {
        $serper = new SerperSearchResults($serper_key);
        $result = $serper->search_images($_GET['q']);

        if (isset($result->images)) {
            echo '<div class="grid grid-cols-2 gap-4 p-4">';
            foreach (array_slice($result->images, 0, 20) as $image) {
                echo '<div class="relative group overflow-hidden rounded-xl">';
                echo '<img src="' . htmlspecialchars($image->imageUrl) . '" 
                           alt="' . htmlspecialchars($image->title) . '" 
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