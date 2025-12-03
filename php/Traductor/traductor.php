<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// traductor.php - endpoint que usa la configuración en config.php para llamar a Azure Translator v3
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    exit;
}

// Cargar configuración
$config = [];
// Al usar __DIR__ . '/config.php', nos aseguramos de que busque 'config.php'
// en la misma carpeta que 'traductor.php'.
if (file_exists(__DIR__ . '/config.php')) {
    $config = include __DIR__ . '/config.php';
} else {
    // Esto te ayudará a debuggear si la ruta sigue mal
    error_log("Error Fatal: config.php no se encontró en " . __DIR__);
    // Detenemos la ejecución porque la API Key es crítica
    http_response_code(500); 
    echo json_encode(['ok' => false, 'error' => 'Error de configuración del servidor.']);
    exit;
}

// Extraer credenciales
$azureKey = $config['translator']['key'] ?? getenv('AZURE_TRANSLATOR_KEY') ?: '';
$azureEndpoint = $config['translator']['endpoint'] ?? getenv('AZURE_TRANSLATOR_ENDPOINT') ?: 'https://api.cognitive.microsofttranslator.com/';
$azureRegion = $config['translator']['location'] ?? getenv('AZURE_TRANSLATOR_REGION') ?: '';

// Leer entrada JSON
$input = file_get_contents('php://input');
$params = json_decode($input, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'JSON malformado']);
    exit;
}

// --- LÓGICA MODIFICADA PARA ARRAYS ---
$texts = $params['texts'] ?? []; // Recibimos "texts" (plural)
$to = trim($params['to'] ?? 'es');
$from = trim($params['from'] ?? '');
// --- FIN DE MODIFICACIÓN ---

// Validaciones
if (empty($texts) || !is_array($texts)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Parámetro "texts" (array) requerido']);
    exit;
}
if (empty($azureKey)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Clave de Azure Translator no configurada']);
    exit;
}

// Construir request a Azure Translator v3
$query = ['api-version' => '3.0', 'to' => $to, 'textType' => 'html']; // Añadimos textType=html
if (!empty($from)) $query['from'] = $from;
$url = rtrim($azureEndpoint, '/') . '/translate?' . http_build_query($query);

// --- CONSTRUIR BODY PARA AZURE CON ARRAY ---
$body_azure = [];
foreach ($texts as $t) {
    // Asegurarse de que el texto es string
    $body_azure[] = ['Text' => (string)$t];
}
$body = json_encode($body_azure);
// --- FIN DE MODIFICACIÓN ---

$headers = [
    'Content-Type: application/json; charset=UTF-8',
    'Ocp-Apim-Subscription-Key: ' . $azureKey
];
if (!empty($azureRegion)) {
    $headers[] = 'Ocp-Apim-Subscription-Region: ' . $azureRegion;
}

// Ejecutar cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Aumentar timeout por si son muchos textos
$response = curl_exec($ch);
$err = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($err) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'cURL error: ' . $err]);
    exit;
}

// El error 429 de Azure se verá aquí
if ($httpCode === 429) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Too Many Requests', 'details' => json_decode($response)]);
    exit;
}

if ($httpCode < 200 || $httpCode >= 300) {
    http_response_code($httpCode);
    echo json_encode(['ok' => false, 'error' => 'Error de Azure API', 'details' => json_decode($response)]);
    exit;
}

$data = json_decode($response, true);

// --- EXTRAER ARRAY DE TRADUCCIONES ---
$translated_texts = [];
if ($data && is_array($data)) {
    foreach ($data as $item) {
        if (isset($item['translations'][0]['text'])) {
            $translated_texts[] = $item['translations'][0]['text'];
        } else {
            // Poner el original si falla una traducción
            $translated_texts[] = '[Traducción fallida]'; 
        }
    }
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Respuesta de API inesperada', 'details' => $data]);
    exit;
}

if (count($translated_texts) !== count($texts)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Conteo de traducciones no coincide', 'details' => $data]);
    exit;
}

// Éxito: Devolver el array de textos traducidos
echo json_encode(['ok' => true, 'data' => $translated_texts]);
?>