<?php
/**
 * SATUSEHAT API Catalog - Helper Functions
 * Reusable PHP functions for all pages
 */

/**
 * Get access token for catalog API
 * 
 * @param string $auth_url Authentication URL
 * @param string $client_id Client ID
 * @param string $client_secret Client Secret
 * @param string $tokenCacheFile Path to token cache file
 * @param string $tokenExpiryFile Path to token expiry file
 * @return string|null Access token or null on failure
 */
function getAccessTokenCatalog($auth_url, $client_id, $client_secret, $tokenCacheFile, $tokenExpiryFile) {
    // Check if we have a valid cached token
    if (file_exists($tokenCacheFile) && file_exists($tokenExpiryFile)) {
        $cachedToken = file_get_contents($tokenCacheFile);
        $cachedExpiry = (int)file_get_contents($tokenExpiryFile);
        
        if ($cachedToken && time() < $cachedExpiry) {
            return $cachedToken;
        }
        // Clear expired cache
        @unlink($tokenCacheFile);
        @unlink($tokenExpiryFile);
    }
    
    // Get new token using cURL
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
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return null;
    }
    
    $result = json_decode($response, true);
    
    if (!$result || !isset($result['access_token'])) {
        return null;
    }
    
    // Cache the token
    $cacheDir = dirname($tokenCacheFile);
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }
    @file_put_contents($tokenCacheFile, $result['access_token']);
    @file_put_contents($tokenExpiryFile, time() + ($result['expires_in'] ?? 3600));
    
    return $result['access_token'];
}

/**
 * Load all modules from app/modules directory
 * 
 * @return array Array of modules
 */
function loadModules() {
    $modules = [];
    $modulesDir = __DIR__ . '/../../app/modules';
    
    if (is_dir($modulesDir)) {
        $moduleFiles = glob($modulesDir . '/*.php');
        foreach ($moduleFiles as $file) {
            $moduleKey = basename($file, '.php');
            $moduleData = include $file;
            if (is_array($moduleData)) {
                $modules[$moduleKey] = $moduleData;
            }
        }
    }
    
    return $modules;
}

/**
 * Get environment configuration
 * 
 * @param array $config Application config
 * @param string $env Environment (sandbox/production)
 * @return array Environment config
 */
function getEnvConfig($config, $env) {
    return $config['satusehat'][$env] ?? $config['satusehat']['sandbox'];
}