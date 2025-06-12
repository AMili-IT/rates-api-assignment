<?php
// Set headers to accept JSON and allow POST from any origin
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

// Reject requests that are not POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Only POST method allowed."]);
    exit;
}

// Read raw POST data and decode JSON
$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Check for required fields
if (!$data || !isset($data["Ages"], $data["Arrival"], $data["Departure"])) {
    echo json_encode(["error" => "Invalid input."]);
    exit;
}

// Convert date from dd/mm/yyyy to yyyy-mm-dd (required by remote API)
function convertDate($date) {
    $d = DateTime::createFromFormat('d/m/Y', $date);
    return $d ? $d->format('Y-m-d') : null;
}

// Prepare payload to send to remote API
$convertedPayload = [
    "Unit Type ID" => -2147483637, // Using test Unit Type ID
    "Arrival" => convertDate($data["Arrival"]),
    "Departure" => convertDate($data["Departure"]),
    "Guests" => []
];

// Classify each guest as Adult or Child based on age
foreach ($data["Ages"] as $age) {
    $convertedPayload["Guests"][] = [
        "Age Group" => ($age < 18) ? "Child" : "Adult"
    ];
}

// Send payload to remote API via curl
$ch = curl_init("https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($convertedPayload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);

// Handle response
if ($response === false) {
    echo json_encode(["error" => "API request failed", "details" => curl_error($ch)]);
} else {
    $responseData = json_decode($response, true);
    echo json_encode([
        "message" => "Rates fetched successfully",
        "unit" => $data["Unit Name"],
        "date_range" => $data["Arrival"] . " to " . $data["Departure"],
        "rate_response" => $responseData
    ]);
}

curl_close($ch);
