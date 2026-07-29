<?php
header('Content-Type: application/json; charset=utf-8');

// Endpoint para recibir imagen o descripción y consultar OpenAI (texto).
// Importante: la API key debe guardarse en una variable de entorno llamada OPENAI_API_KEY

$apiKey = getenv('OPENAI_API_KEY');
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'OPENAI_API_KEY no configurada en el servidor.']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$result = ['success' => true];

// Manejo de archivo subido
if (!empty($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['image']['tmp_name'];
    $origName = basename($_FILES['image']['name']);
    $ext = pathinfo($origName, PATHINFO_EXTENSION);
    $fileName = uniqid('img_') . '.' . $ext;
    $dest = $uploadDir . $fileName;
    if (move_uploaded_file($tmpName, $dest)) {
        // Ruta pública relativa
        $result['image_saved'] = true;
        $result['image_path'] = '/uploads/' . $fileName;
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo guardar la imagen.']);
        exit;
    }
}

$description = null;
if (!empty($_POST['description'])) {
    $description = trim($_POST['description']);
}

// Preparar contenido para OpenAI (descripción y/o imagen si existe)
$shouldCallOpenAI = false;
$callPromptParts = [];
if ($description) {
    $shouldCallOpenAI = true;
    $callPromptParts[] = "Descripcion: " . $description;
}

if (!empty($result['image_saved'])) {
    // Intento: incluir URL pública si es accesible, y opcionalmente base64 si el archivo no es muy grande
    $shouldCallOpenAI = true;
    $imagePath = __DIR__ . '/../uploads/' . basename($result['image_path']);
    $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $imageUrl = $host . $result['image_path'];
    $callPromptParts[] = "Imagen guardada en: " . $imageUrl;

    // Si el archivo es pequeño (< 1MB) lo incluimos en base64 para mayor chance de análisis local
    if (file_exists($imagePath) && filesize($imagePath) < 1024 * 1024) {
        $b = base64_encode(file_get_contents($imagePath));
        $ext = pathinfo($imagePath, PATHINFO_EXTENSION);
        $dataUri = "data:image/" . $ext . ";base64," . $b;
        $callPromptParts[] = "Imagen en base64 (pequeña): " . $dataUri;
    } else {
        $callPromptParts[] = '(Imagen demasiado grande para enviar en base64)';
    }
}

if ($shouldCallOpenAI) {
    $system = 'Eres un asistente que identifica comidas a partir de una descripción o imagen. Responde preferiblemente en JSON con campos: nombre_alimento, porciones_estimadas, sugerencia_corta. Si no estás seguro, usa "unknown".';
    $userContent = implode("\n\n", $callPromptParts) . "\n\nPor favor responde en JSON puro o explica si no puede determinarlo.";

    $useVision = !empty($result['image_saved']);
    if ($useVision) {
        $payload = [
            'model' => 'gpt-4.1-mini',
            'input' => [
                [
                    'role' => 'system',
                    'content' => [
                        ['type' => 'input_text', 'text' => $system]
                    ]
                ],
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'input_text', 'text' => $userContent],
                        ['type' => 'input_image', 'image_url' => $imageUrl]
                    ]
                ]
            ]
        ];
        $endpoint = 'https://api.openai.com/v1/responses';
    } else {
        $payload = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $userContent]
            ],
            'max_tokens' => 400,
        ];
        $endpoint = 'https://api.openai.com/v1/chat/completions';
    }

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    $resp = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $err]);
        exit;
    }

    $data = json_decode($resp, true);
    $assistantText = null;
    if ($useVision) {
        if (!empty($data['output'][0]['content'])) {
            $textParts = [];
            foreach ($data['output'][0]['content'] as $content) {
                if (!empty($content['text'])) {
                    $textParts[] = $content['text'];
                }
            }
            if (!empty($textParts)) {
                $assistantText = trim(implode("\n", $textParts));
            }
        }
    } else {
        if (!empty($data['choices'][0]['message']['content'])) {
            $assistantText = $data['choices'][0]['message']['content'];
        }
    }

    $result['openai_raw'] = $data;
    $result['assistant_text'] = $assistantText;

    // Intentar extraer JSON desde la respuesta
    $parsed = null;
    if ($assistantText) {
        if (preg_match('/(\{.*\})/s', $assistantText, $m)) {
            $maybe = $m[1];
            $j = json_decode($maybe, true);
            if ($j !== null) {
                $parsed = $j;
            }
        }

        if ($parsed === null) {
            $followPayload = [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'Eres un asistente que transforma texto en JSON con campos: nombre_alimento, porciones_estimadas, sugerencia_corta.'],
                    ['role' => 'user', 'content' => "Esta es la salida que devolviste: \n\n" . $assistantText . "\n\nDevuélvelo ahora en JSON con solo ese objeto JSON."]
                ],
                'max_tokens' => 200,
            ];

            $ch2 = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_POST, true);
            curl_setopt($ch2, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ]);
            curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($followPayload));
            $resp2 = curl_exec($ch2);
            $err2 = curl_error($ch2);
            curl_close($ch2);

            if ($resp2 !== false) {
                $data2 = json_decode($resp2, true);
                if (!empty($data2['choices'][0]['message']['content'])) {
                    $maybeJson = $data2['choices'][0]['message']['content'];
                    if (preg_match('/(\{.*\})/s', $maybeJson, $m2)) {
                        $j2 = json_decode($m2[1], true);
                        if ($j2 !== null) {
                            $parsed = $j2;
                            $result['openai_followup_raw'] = $data2;
                            $result['openai_followup_text'] = $maybeJson;
                        }
                    }
                }
            }
        }
    }

    if ($parsed !== null) {
        $result['parsed'] = $parsed;
    }

    echo json_encode($result);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'No se envió descripción ni imagen. Usa el campo "description" o sube una imagen.']);

?>
