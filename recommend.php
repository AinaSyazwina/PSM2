<?php
$host = '127.0.0.1';
$port = 5000;
$waitTimeoutInSeconds = 1;

// Attempt to connect to the Flask server
if ($fp = @fsockopen($host, $port, $errCode, $errStr, $waitTimeoutInSeconds)) {
    fclose($fp); // Close the connection if it's successful
} else {
    // Start the Flask server if it's not running
    exec('C:\\xampp\\htdocs\\library\\run_flask.bat');
    sleep(5);  // Give it some time to start
}

session_start();
include 'config.php';

// Redirect non-students to the index page
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php');
    exit;
}

// Use memberID from the session
$memberID = $_SESSION['memberID'] ?? 'default_member_id';

// Function to fetch recommendations using the memberID
function fetchRecommendations($memberID) {
    $url = "http://localhost:5000/recommend/" . $memberID;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return ["error" => 'cURL error: ' . curl_error($ch)];
    }

    curl_close($ch);
    if (!$response) return ["Error" => "Failed to fetch data"];

    return json_decode($response, true) ?: ["Error" => "No recommendations available"];
}

// Check if the recommendations need to be updated or fetched for the first time
if (!isset($_SESSION['recommendations']) || $memberID != $_SESSION['last_checked_member_id']) {
    $_SESSION['recommendations'] = fetchRecommendations($memberID);
    $_SESSION['last_checked_member_id'] = $memberID;
}

$recommendations = $_SESSION['recommendations'] ?? [];

// Display the recommendations for debugging
echo '<pre>' . print_r($recommendations, true) . '</pre>';
?>
