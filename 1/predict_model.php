<?php
// Input data to be passed to the model (example data)
$input_data = [1.5, 2.3, 3.1, 4.7]; // Replace with actual data

// Convert PHP array to JSON
$input_json = json_encode($input_data);

// Call the Python script
$command = escapeshellcmd("python3 predict.py '$input_json'");
$output = shell_exec($command);

// Decode the output JSON from Python
$result = json_decode($output, true);

if (isset($result['prediction'])) {
    echo "Prediction: " . implode(', ', $result['prediction']);
} else {
    echo "Error: Unable to get prediction";
}
?>
