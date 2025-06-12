<?php
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$arrival = DateTime::createFromFormat('d/m/Y', $input['Arrival'])->format('Y-m-d');
$departure = DateTime::createFromFormat('d/m/Y', $input['Departure'])->format('Y-m-d');

$guests = array_map(function ($age) {
    return ['Age Group' => $age < 18 ? 'Child' : 'Adult'];
}, $input['Ages']);

$payload = [
    "Unit Type ID" => -2147483637,
    "Arrival" => $arrival,
    "Departure" => $departure,
    "Guests" => $guests
];

$ch = curl_init("https://dev.gondwana-collection.com/Web-Store/Rates/Rates.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

echo $response;