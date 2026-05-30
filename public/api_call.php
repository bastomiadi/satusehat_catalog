<?php
/**
 * SATUSEHAT API Call Handler
 * Native PHP + JSON API
 */

// Error reporting - disable display_errors in production
$isProduction = ($_ENV['APP_ENV'] ?? 'development') === 'production';
error_reporting(E_ALL);
ini_set('display_errors', $isProduction ? 0 : 1);

session_start();

// Check if this is an API request (JSON)
$isApiRequest = false;
$input = null;

// Get content type from headers
$contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $isApiRequest = true;
    $input = json_decode(file_get_contents('php://input'), true);
}

// Load configuration
$config = include __DIR__ . '/../config/config.php';

// SATUSEHAT Configuration
$satusehat = $config['satusehat'];
$currentEnv = $isApiRequest && isset($input['env']) ? $input['env'] : ($_GET['env'] ?? 'sandbox');
$envConfig = $satusehat[$currentEnv];

$auth_url = $envConfig['auth_url'];
$base_url = $envConfig['base_url'];
$org_id = $envConfig['organization_id'];
$client_id = $envConfig['client_id'];
$client_secret = $envConfig['client_secret'];

// Token cache (in production, use Redis or database)
$tokenCacheFile = __DIR__ . '/../storage/cache/token_' . $currentEnv . '.cache';
$tokenExpiryFile = __DIR__ . '/../storage/cache/token_' . $currentEnv . '.expiry';

// Get or refresh token
function getAccessToken($auth_url, $client_id, $client_secret, $tokenCacheFile = null, $tokenExpiryFile = null) {
    // Check if we have a cached token
    if ($tokenCacheFile && $tokenExpiryFile && file_exists($tokenCacheFile) && file_exists($tokenExpiryFile)) {
        $cachedToken = file_get_contents($tokenCacheFile);
        $expiryTime = (int)file_get_contents($tokenExpiryFile);
        
        // Only use cached token if it's still valid (not expired)
        if ($cachedToken && time() < $expiryTime) {
            return ['access_token' => $cachedToken, 'from_cache' => true];
        }
        
        // Clear expired cache
        @unlink($tokenCacheFile);
        @unlink($tokenExpiryFile);
    }
    
    // Use cURL for better compatibility
    // SSL verification - can be disabled for development, enable for production
    $verifySSL = !($isProduction ?? false);
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $auth_url . '/accesstoken?grant_type=client_credentials',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'client_id' => $client_id,
            'client_secret' => $client_secret,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => $verifySSL,
        CURLOPT_SSL_VERIFYHOST => $verifySSL ? 2 : 0,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    // Debug: Log the raw response for troubleshooting
    if ($error) {
        return ['error' => 'cURL Error: ' . $error, 'data' => null];
    }
    
    $result = json_decode($response, true);
    
    if (!$result || !isset($result['access_token'])) {
        // Extract error from FHIR OperationOutcome or standard OAuth error
        $errorMsg = 'Failed to get token';
        if (isset($result['issue'][0]['details']['text'])) {
            $errorMsg = $result['issue'][0]['details']['text'];
        } elseif (isset($result['error_description'])) {
            $errorMsg = $result['error_description'];
        } elseif (isset($result['error'])) {
            $errorMsg = $result['error'];
        } elseif (isset($result['message'])) {
            $errorMsg = $result['message'];
        }
        return ['error' => $errorMsg, 'data' => $result, 'raw_response' => $response];
    }
    
    // Cache the token
    if ($tokenCacheFile && $tokenExpiryFile && isset($result['expires_in'])) {
        $cacheDir = dirname($tokenCacheFile);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }
        @file_put_contents($tokenCacheFile, $result['access_token']);
        @file_put_contents($tokenExpiryFile, time() + $result['expires_in']);
    }
    
    return ['access_token' => $result['access_token'], 'data' => $result];
}

// Make API call
function makeApiCall($base_url, $token, $method, $path, $org_id, $body = null) {
    // Add /fhir-r4/v1/ prefix if not already present
    // if (strpos($path, '/fhir-r4/v1/') !== 0) {
    //     $path = '/fhir-r4/v1/' . ltrim($path, '/');
    // }
    // Ensure no double slashes
    $url = rtrim($base_url, '/') . '/' . ltrim($path, '/');
    
    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/fhir+json',
        'Accept: application/fhir+json',
        'X-Organization-ID: ' . $org_id
    ];
    
    $options = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'ignore_errors' => true,
        ],
        'ssl' => [
            'verify_peer' => $isProduction ?? false,
            'verify_peer_name' => $isProduction ?? false,
        ]
    ];
    
    if ($body) {
        $options['http']['content'] = $body;
    }
    
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    // Return both response and the final path used
    return ['response' => $response, 'path' => $path];
}

// Handle request - support both JSON and form data
if ($isApiRequest && $input) {
    $method = $input['method'] ?? 'GET';
    $path = $input['path'] ?? '';
    $params = $input['params'] ?? [];
    $body = $input['body'] ?? null;
    // Convert body to JSON string if it's an array/object
    if (is_array($body) || is_object($body)) {
        $body = json_encode($body);
    }
} else {
    $method = $_REQUEST['method'] ?? 'GET';
    $path = $_REQUEST['path'] ?? '';
    $params = $_REQUEST['params'] ?? [];
    $body = $_REQUEST['body'] ?? null;
}

// Validate HTTP method
$allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];
if (!in_array(strtoupper($method), $allowedMethods)) {
    $method = 'GET';
}

// Validate environment
if (!isset($satusehat[$currentEnv])) {
    $currentEnv = 'sandbox';
    $envConfig = $satusehat[$currentEnv];
}

// Sanitize path (prevent directory traversal)
$path = str_replace(['../', '..\\'], '', $path);

// Replace path parameters
if (!empty($params)) {
    foreach ($params as $key => $value) {
        $path = str_replace('{' . $key . '}', $value, $path);
    }
}

// Get token (with caching)
$tokenResult = getAccessToken($auth_url, $client_id, $client_secret, $tokenCacheFile, $tokenExpiryFile);
$token = $tokenResult['access_token'] ?? null;
$tokenError = $tokenResult['error'] ?? null;
$_SESSION['org_id'] = $org_id;

// Debug: Log token result (only in development mode)
if (isset($_GET['debug']) && $_GET['debug'] === '1' && !($isProduction ?? false)) {
    // Sanitize sensitive data before logging
    $safeTokenResult = $tokenResult;
    if (isset($safeTokenResult['access_token'])) {
        $safeTokenResult['access_token'] = '[REDACTED]';
    }
    error_log('Token Result: ' . print_r($safeTokenResult, true));
}

// Make API call
$apiResult = makeApiCall($base_url, $token, $method, $path, $org_id, $body);
$response = $apiResult['response'];
$finalPath = $apiResult['path'];
$result = json_decode($response, true);

// Debug: Log API response (only in development mode)
if (isset($_GET['debug']) && $_GET['debug'] === '1' && !($isProduction ?? false)) {
    error_log('API Response: ' . $response);
}

// Return JSON for API requests
if ($isApiRequest) {
    header('Content-Type: application/json');
    
    $output = [
        'method' => $method,
        'path' => rtrim($base_url, '/') . '/' . ltrim($finalPath, '/'),
        'success' => $token && !$tokenError,
        'token_error' => $tokenError,
        'data' => $result,
    ];
    
    // Only include raw_response in debug mode and non-production
    if (isset($_GET['debug']) && $_GET['debug'] === '1' && !($isProduction ?? false)) {
        $output['raw_response'] = $response;
    }
    
    echo json_encode($output);
    exit;
}
?>