<?php
// Read the JSON file
$json = file_get_contents('data.json');

// Decode the JSON data
$data = json_decode($json, true);

// Check if the data was decoded properly
if (json_last_error() === JSON_ERROR_NONE) {
    // Count the number of IDs
    $count = count($data);
    echo "The number of IDs in the JSON file is: " . $count;
} else {
    echo "Error decoding JSON: " . json_last_error_msg();
}
?>