<?php
/**
 * SATUSEHAT API Catalog Platform
 * Native PHP + Tailwind CSS
 * Version 3.0 - Modern UI
 */

// Load configuration
$config = include __DIR__ . '/../config/config.php';

// SATUSEHAT Configuration
$satusehat = $config['satusehat'];
$currentEnv = isset($_GET['env']) ? $_GET['env'] : 'sandbox';
$envConfig = $satusehat[$currentEnv];

$org_id = $envConfig['organization_id'];

// Define modules
$modules = [
    'patient' => ['label' => 'Patient', 'icon' => '👤', 'description' => 'Patient management resources including registration, identification, and demographic data'],
    'encounter' => ['label' => 'Encounter', 'icon' => '📅', 'description' => 'Encounter resources for patient visits including outpatient, inpatient, and emergency visits'],
    'condition' => ['label' => 'Condition', 'icon' => '🤒', 'description' => 'Diagnosis and problem list'],
    'observation' => ['label' => 'Observation', 'icon' => '📊', 'description' => 'Vital signs, lab results, and clinical measurements'],
    'procedure' => ['label' => 'Procedure', 'icon' => '🔪', 'description' => 'Procedure records and surgical interventions'],
    'allergyintolerance' => ['label' => 'AllergyIntolerance', 'icon' => '⚠️', 'description' => 'Allergy and intolerance records'],
    'careplan' => ['label' => 'CarePlan', 'icon' => '📝', 'description' => 'Care plans and care team coordination'],
    'clinicalimpression' => ['label' => 'ClinicalImpression', 'icon' => '📋', 'description' => 'Clinical impressions and assessments'],
    'medication' => ['label' => 'Medication', 'icon' => '💊', 'description' => 'Medication resources including ingredients and formulations'],
    'medicationrequest' => ['label' => 'MedicationRequest', 'icon' => '💊', 'description' => 'Medication prescriptions and orders'],
    'medicationdispense' => ['label' => 'MedicationDispense', 'icon' => '📦', 'description' => 'Medication dispensing records and fulfillment'],
    'medicationstatement' => ['label' => 'MedicationStatement', 'icon' => '📝', 'description' => 'Medication statements and patient medication history'],
    'specimen' => ['label' => 'Specimen', 'icon' => '🧪', 'description' => 'Specimen resources for lab tests and diagnostics'],
    'diagnosticreport' => ['label' => 'DiagnosticReport', 'icon' => '🧪', 'description' => 'Laboratory results and diagnostic reports'],
    'servicerequest' => ['label' => 'ServiceRequest', 'icon' => '📋', 'description' => 'Service requests and orders'],
    'appointment' => ['label' => 'Appointment', 'icon' => '🗓️', 'description' => 'Appointments and scheduling'],
    'consent' => ['label' => 'Consent', 'icon' => '✅', 'description' => 'Patient consent management'],
    'coverage' => ['label' => 'Coverage', 'icon' => '🛡️', 'description' => 'Insurance coverage information'],
    'organization' => ['label' => 'Organization', 'icon' => '🏢', 'description' => 'Healthcare organization details'],
    'immunization' => ['label' => 'Immunization', 'icon' => '💉', 'description' => 'Immunization records and vaccination tracking'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATUSEHAT API Catalog - Home</title>
    <?php include __DIR__ . '/inc/styles.php'; ?>
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include __DIR__ . '/inc/header.php'; ?>

    <main class="container mx-auto px-4 py-8 pt-20">
        <div class="max-w-7xl mx-auto">
            <!-- View All Catalog Button -->
            <div class="mb-6 text-right">
                <a href="catalog.php" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg font-semibold hover:bg-opacity-90 transition-colors shadow-lg hover:shadow-xl">
                    <i class="fas fa-th-list mr-2"></i>
                    Lihat Semua Katalog
                </a>
            </div>

            <!-- Modules Grid -->
            <div class="mb-4">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">API Modules</h2>
                <p class="text-gray-600 mb-6">Select a module to view available API endpoints and test them directly</p>
            </div>

            <!-- Modules Grid (All modules without categorization) -->
            <div class="module-grid">
                <?php foreach ($modules as $key => $module): ?>
                <div class="bg-white rounded-xl shadow card-hover p-5 cursor-pointer" onclick="window.location.href='catalog.php?module=<?= $key ?>&env=<?= $currentEnv ?>'">
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-100 rounded-lg p-3">
                            <span class="text-2xl"><?= $module['icon'] ?></span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800"><?= $module['label'] ?></h3>
                            <p class="text-gray-600 text-sm mt-1"><?= $module['description'] ?></p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-400 text-sm"></i>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/inc/footer.php'; ?>
</body>
</html>