<?php
/**
 * SATUSEHAT API Catalog - Complete Module Endpoints
 * Native PHP + Tailwind CSS
 * Version 3.0 - Modular Architecture
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Load helpers and configuration
require_once __DIR__ . '/inc/helpers.php';
$config = include __DIR__ . '/../config/config.php';

// SATUSEHAT Configuration
$satusehat = $config['satusehat'];
$currentEnv = isset($_GET['env']) ? $_GET['env'] : 'sandbox';
$module = isset($_GET['module']) ? $_GET['module'] : 'patient';
$envConfig = $satusehat[$currentEnv];

$auth_url = $envConfig['auth_url'];
$base_url = $envConfig['base_url'];
$org_id = $envConfig['organization_id'];
$client_id = $envConfig['client_id'];
$client_secret = $envConfig['client_secret'];

// Token handling
$tokenCacheFile = __DIR__ . '/../storage/cache/token_' . $currentEnv . '.cache';
$tokenExpiryFile = __DIR__ . '/../storage/cache/token_' . $currentEnv . '.expiry';

// Get or refresh token
$token = '';
$tokenExpiry = 0;

if (file_exists($tokenCacheFile) && file_exists($tokenExpiryFile)) {
    $cachedToken = file_get_contents($tokenCacheFile);
    $cachedExpiry = (int)file_get_contents($tokenExpiryFile);
    
    // Only use cached token if it's still valid
    if ($cachedToken && time() < $cachedExpiry) {
        $token = $cachedToken;
        $tokenExpiry = $cachedExpiry;
    } else {
        // Clear expired cache
        @unlink($tokenCacheFile);
        @unlink($tokenExpiryFile);
    }
}

// If no valid token, get a new one
if (!$token) {
    $token = getAccessTokenCatalog($auth_url, $client_id, $client_secret, $tokenCacheFile, $tokenExpiryFile);
}

// Load all modules from app/modules directory
$modules = loadModules();
$currentModule = $modules[$module] ?? $modules['patient'] ?? null;

// Fallback if modules couldn't be loaded
if (!$currentModule) {
    $currentModule = [
        'label' => 'Patient',
        'icon' => '👤',
        'description' => 'Patient management resources',
        'endpoints' => []
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATUSEHAT API Catalog - <?= htmlspecialchars($currentModule['label']) ?></title>
    <?php include __DIR__ . '/inc/styles.php'; ?>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/inc/header.php'; ?>

    <main class="container mx-auto px-4 py-8 pt-20">
        <div class="flex gap-6">
            <!-- Sidebar - Modules -->
            <div class="w-64 flex-shrink-0">
                <div class="sticky top-20 z-30 space-y-4">
                    <div class="bg-white rounded-xl shadow p-4">
                        <div class="mb-3">
                            <label class="block text-xs font-medium text-gray-500 mb-1">Environment</label>
                            <select onchange="window.location.href='catalog.php?module=<?= $module ?>&env='+this.value" class="w-full border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-primary">
                                <option value="sandbox" <?= $currentEnv === 'sandbox' ? 'selected' : '' ?>>Sandbox</option>
                                <option value="production" <?= $currentEnv === 'production' ? 'selected' : '' ?>>Production</option>
                            </select>
                        </div>
                        <div class="text-xs text-gray-500">
                            <div>Org: <?= htmlspecialchars(substr($org_id, 0, 20) . '...') ?></div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow p-4">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="font-bold text-gray-800">Modules</h3>
                            <span class="text-xs text-gray-500"><?= count($modules) ?> modules</span>
                        </div>
                        
                        <div class="mb-3">
                            <input type="text" id="moduleSearch" placeholder="Search modules..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary">
                        </div>
                        
                        <div class="space-y-1 max-h-96 overflow-y-auto" id="moduleList">
                            <?php foreach ($modules as $key => $mod): ?>
                            <a href="catalog.php?module=<?= $key ?>&env=<?= $currentEnv ?>" class="module-item <?= $module === $key ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100' ?> px-2 py-1.5 rounded text-sm flex items-center transition" data-label="<?= strtolower($mod['label']) ?>" data-key="<?= $key ?>">
                                <span class="mr-2"><?= $mod['icon'] ?></span>
                                <span class="truncate"><?= $mod['label'] ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1">
                <!-- Rate Limit Warning -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-circle text-yellow-600 mr-2 mt-0.5"></i>
                        <div>
                            <h4 class="font-semibold text-yellow-800">Important: SATUSEHAT API Rate Limits</h4>
                            <p class="text-yellow-700 text-sm mt-1">
                                The SATUSEHAT API has strict rate limits. If you encounter authentication errors, 
                                <strong>wait 1 minute</strong> before retrying. Rate limit: 1 request per minute after a failed attempt.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Current Module Header -->
                <div class="bg-white rounded-xl shadow p-6 mb-6">
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-100 rounded-lg p-3">
                            <span class="text-3xl"><?= htmlspecialchars($currentModule['icon'] ?? '👤') ?></span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($currentModule['label'] ?? 'Patient') ?></h2>
                            <p class="text-gray-600"><?= htmlspecialchars($currentModule['description'] ?? 'Patient management resources') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Endpoints List -->
                <div class="space-y-6">
                    <?php foreach ($currentModule['endpoints'] ?? [] as $endpoint): ?>
                    <div class="bg-white rounded-xl shadow overflow-hidden">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($endpoint['label']) ?></h3>
                                    <p class="text-gray-600 text-sm mt-1"><?= htmlspecialchars($endpoint['description']) ?></p>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <span class="px-3 py-1 rounded font-bold text-sm method-<?= strtolower($endpoint['method']) ?>">
                                        <?= htmlspecialchars($endpoint['method']) ?>
                                    </span>
                                    <code class="bg-gray-100 px-2 py-1 rounded text-sm"><?= htmlspecialchars($endpoint['path']) ?></code>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-6">
                            <?php if ($endpoint['method'] === 'POST' || $endpoint['method'] === 'PUT' || $endpoint['method'] === 'PATCH'): ?>
                            <form method="POST" class="api-test-form space-y-4">
                                <input type="hidden" name="env" value="<?= $currentEnv ?>">
                                <input type="hidden" name="method" value="<?= $endpoint['method'] ?>">
                                <input type="hidden" name="path" value="<?= $endpoint['path'] ?>">
                                
                                <?php if (!empty($endpoint['params'])): ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($endpoint['params'] as $param): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?= htmlspecialchars($param['placeholder']) ?></label>
                                        <input type="text" name="params[<?= $param['name'] ?>]" value="<?= htmlspecialchars($param['default'] ?? '') ?>" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Request Body (JSON)</label>
                                    <textarea name="body" rows="8" class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-mono"><?= htmlspecialchars(json_encode($endpoint['body'] ?? [], JSON_PRETTY_PRINT)) ?></textarea>
                                </div>
                                
                                <button type="submit" class="bg-primary text-white px-6 py-2 rounded text-sm hover:bg-blue-700">
                                    <i class="fas fa-play mr-1"></i> Test API
                                </button>
                            </form>
                            <?php else: ?>
                            <form method="GET" class="api-test-form space-y-4">
                                <input type="hidden" name="env" value="<?= $currentEnv ?>">
                                <input type="hidden" name="method" value="<?= $endpoint['method'] ?>">
                                <input type="hidden" name="path" value="<?= $endpoint['path'] ?>">
                                
                                <?php if (!empty($endpoint['params'])): ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($endpoint['params'] as $param): ?>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1"><?= htmlspecialchars($param['placeholder']) ?></label>
                                        <input type="text" name="params[<?= $param['name'] ?>]" value="<?= htmlspecialchars($param['default'] ?? '') ?>" class="w-full border border-gray-300 rounded px-3 py-2 text-sm">
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <button type="submit" class="bg-primary text-white px-6 py-2 rounded text-sm hover:bg-blue-700">
                                    <i class="fas fa-play mr-1"></i> Test API
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/inc/footer.php'; ?>

    <!-- API Response Modal -->
    <div id="apiModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[80vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-bold text-gray-800">API Response</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="flex-1 overflow-hidden">
                <div id="modalContent" class="p-4 max-h-[60vh] overflow-auto">
                    <div class="text-center py-8">
                        <i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i>
                        <p class="mt-2 text-gray-500">Loading...</p>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t flex justify-end space-x-2">
                <button onclick="closeModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800">Close</button>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/inc/scripts.php'; ?>
    
    <script>
        // Handle form submission via AJAX
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form.api-test-form');
            
            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    const method = formData.get('method');
                    const path = formData.get('path');
                    const env = formData.get('env');
                    
                    // Show modal with loading
                    const modalContent = document.getElementById('modalContent');
                    modalContent.innerHTML = '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-3xl text-gray-400"></i><p class="mt-2 text-gray-500">Calling API...</p></div>';
                    openModal();
                    
                    // Build request body
                    let bodyData = null;
                    const bodyInput = form.querySelector('textarea[name="body"]');
                    if (bodyInput && bodyInput.value.trim()) {
                        try {
                            bodyData = JSON.parse(bodyInput.value);
                        } catch(e) {
                            bodyData = bodyInput.value;
                        }
                    }
                    
                    // Build params
                    const params = {};
                    const paramInputs = form.querySelectorAll('input[name^="params"]');
                    paramInputs.forEach(function(input) {
                        const name = input.name.replace('params[', '').replace(']', '');
                        params[name] = input.value;
                    });
                    
                    // Make API call
                    fetch('api_call.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            env: env,
                            method: method,
                            path: path,
                            params: params,
                            body: bodyData
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('HTTP error! status: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        let displayData = data.data;
                        if (data.data && typeof data.data === 'object') {
                            displayData = JSON.stringify(data.data, null, 2);
                        } else if (data.data && typeof data.data === 'string') {
                            displayData = data.data;
                        } else {
                            displayData = JSON.stringify(data, null, 2);
                        }
                        
                        const methodClass = method === 'POST' ? 'green' : 'blue';
                        const escapedDisplayData = escapeHtml(displayData);
                        modalContent.innerHTML = `
                            <div class="mb-3">
                                <span class="bg-${methodClass}-100 text-${methodClass}-800 px-3 py-1 rounded font-bold text-sm">
                                    ${method}
                                </span>
                                <code class="bg-gray-100 px-2 py-1 rounded text-sm ml-2">${data.path || path}</code>
                            </div>
                            <div class="bg-gray-900 rounded p-4 overflow-x-auto relative">
                                <button onclick="copyFromPre(this)" class="absolute top-2 right-2 bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm flex items-center">
                                    <i class="fas fa-copy mr-1"></i> Copy
                                </button>
                                <pre id="responseContent" class="text-green-400 font-mono text-sm mt-6">${escapedDisplayData}</pre>
                            </div>
                        `;
                    })
                    .catch(error => {
                        const errorMsg = escapeHtml(error.message);
                        modalContent.innerHTML = `
                            <div class="bg-red-100 border border-red-200 rounded p-4 relative">
                                <button onclick="copyFromPre(this)" class="absolute top-2 right-2 bg-gray-700 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm flex items-center">
                                    <i class="fas fa-copy mr-1"></i> Copy
                                </button>
                                <div class="flex items-center mt-6">
                                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                    <div>
                                        <h4 class="font-bold text-red-800">Error</h4>
                                        <pre class="text-red-700 text-sm">${errorMsg}</pre>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                });
            });
        });

        // Module search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('moduleSearch');
            const moduleItems = document.querySelectorAll('.module-item');
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    moduleItems.forEach(function(item) {
                        const label = item.getAttribute('data-label');
                        const key = item.getAttribute('data-key');
                        
                        if (label.includes(searchTerm) || key.includes(searchTerm)) {
                            item.style.display = 'flex';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
