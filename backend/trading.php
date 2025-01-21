<?php
// Set headers to allow cross-origin requests and JSON responses
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// File containing the pre-stored prices
$priceFile = "price.csv";

// Function to fetch prices from the CSV file
function getPrices($filename) {
    if (!file_exists($filename)) {
        return [];
    }
    return array_map(function ($line) {
        return floatval(preg_replace('/[^0-9.]/', '', $line)); // Remove non-numeric characters
    }, file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
}

// Get all prices from the file
$prices = getPrices($priceFile);

if (empty($prices)) {
    echo json_encode(["error" => "Price file is empty or missing."]);
    exit;
}

// Calculate the current cycle based on the server time
$cycleTime = 60; // 60-second cycle
$currentCycle = floor(time() / $cycleTime) % count($prices);

// Get the current price based on the cycle
$currentPrice = $prices[$currentCycle];

// Calculate the remaining time for the countdown
$elapsedTime = time() % $cycleTime;
$countdown = $cycleTime - $elapsedTime;

// Return the price and countdown as a JSON response
echo json_encode([
    "price" => $currentPrice,
    "countdown" => $countdown
]);
?>
