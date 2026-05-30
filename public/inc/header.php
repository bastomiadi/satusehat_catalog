<?php
/**
 * SATUSEHAT API Catalog - Header Component
 * Reusable header with navigation
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$currentEnv = isset($_GET['env']) ? $_GET['env'] : 'sandbox';
?>
<header class="gradient-bg text-white shadow-lg sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <i class="fas fa-heartbeat text-2xl"></i>
                <div>
                    <h1 class="text-xl font-bold">SATUSEHAT API Catalog</h1>
                    <p class="text-sm opacity-90">FHIR R4 Integration Platform</p>
                </div>
            </div>
            <nav class="flex space-x-2">
                <a href="index.php" class="px-4 py-2 <?= $currentPage === 'index.php' ? 'bg-white text-primary rounded-lg font-semibold text-sm' : 'bg-white bg-opacity-20 rounded-lg text-sm hover:bg-opacity-30' ?>">Home</a>
                <a href="catalog.php" class="px-4 py-2 <?= $currentPage === 'catalog.php' ? 'bg-white text-primary rounded-lg font-semibold text-sm' : 'bg-white bg-opacity-20 rounded-lg text-sm hover:bg-opacity-30' ?>">API Catalog</a>
            </nav>
        </div>
    </div>
</header>