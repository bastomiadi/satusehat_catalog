<?php
/**
 * SATUSEHAT API Catalog Platform - Configuration
 * Native PHP + Tailwind CSS
 */

// Load .env file
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

return [
    'app' => [
        'name' => 'SATUSEHAT API Catalog',
        'version' => '1.0.0',
        'environment' => $_ENV['APP_ENV'] ?? 'development',
        'debug' => $_ENV['APP_DEBUG'] ?? true,
    ],
    
    'satusehat' => [
        'sandbox' => [
            'auth_url' => $_ENV['SATUSEHAT_AUTH_URL'] ?? 'https://api-satusehat-stg.dto.kemkes.go.id/oauth2/v1',
            'base_url' => $_ENV['SATUSEHAT_BASE_URL'] ?? 'https://api-satusehat-stg.dto.kemkes.go.id',
            'consent_url' => $_ENV['SATUSEHAT_CONSENT_URL'] ?? 'https://api-satusehat-stg.dto.kemkes.go.id/consent/v1',
            'organization_id' => $_ENV['SATUSEHAT_ORGANIZATION_ID'] ?? '',
            'client_id' => $_ENV['SATUSEHAT_CLIENT_ID'] ?? '',
            'client_secret' => $_ENV['SATUSEHAT_CLIENT_SECRET'] ?? '',
        ],
        'production' => [
            'auth_url' => $_ENV['SATUSEHAT_PROD_AUTH_URL'] ?? 'https://api-satusehat.kemkes.go.id/oauth2/v1',
            'base_url' => $_ENV['SATUSEHAT_PROD_BASE_URL'] ?? 'https://api-satusehat.kemkes.go.id',
            'consent_url' => $_ENV['SATUSEHAT_PROD_CONSENT_URL'] ?? 'https://api-satusehat.kemkes.go.id/consent/v1',
            'organization_id' => $_ENV['SATUSEHAT_PROD_ORGANIZATION_ID'] ?? '',
            'client_id' => $_ENV['SATUSEHAT_PROD_CLIENT_ID'] ?? '',
            'client_secret' => $_ENV['SATUSEHAT_PROD_CLIENT_SECRET'] ?? '',
        ],
    ],
];