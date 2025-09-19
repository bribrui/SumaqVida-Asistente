<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$apiKey = 'AIzaSyBRnL22jaSDIOBIlpUrsPfujaJ7GqVsp8M';

$input = json_decode(file_get_contents('php://input'), true);
$userMessage = isset($input['message']) ? trim($input['message']) : '';

if ($userMessage === '') {
  echo json_encode(['reply' => '¡Hola! Soy IntiBot 😊 ¿En qué puedo ayudarte hoy?']);
  exit;
}
if (strlen($userMessage) > 1000) {
  echo json_encode(['reply' => 'El mensaje es muy largo. Intenta resumir.']);
  exit;
}

$data = [
  'contents' => [
    [
      'role' => 'user',
      'parts' => [
        ['text' => 'Responde de forma breve y amigable como IntiBot: ' . $userMessage]
      ]
    ]
  ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Content-Type: application/json',
  'X-goog-api-key: ' . $apiKey
]);

$response = curl_exec($ch);

if ($response === false) {
  echo json_encode(['reply' => 'No se pudo conectar a la API de IntiBot.']);
  curl_close($ch);
  exit;
}
curl_close($ch);

$responseData = json_decode($response, true);

if (isset($responseData['candidates'][0]['content']['parts'][0]['text']) &&
    !empty($responseData['candidates'][0]['content']['parts'][0]['text'])) {
  echo json_encode([
    'reply' => $responseData['candidates'][0]['content']['parts'][0]['text']
  ]);
} else if (isset($responseData['error']['message'])) {
  echo json_encode([
    'reply' => 'Error de IntiBot: ' . $responseData['error']['message']
  ]);
} else {
  echo json_encode([
    'reply' => 'Lo siento, no pude entenderte.'
  ]);
}