<?php
// Determine base path for links
$scriptPath = $_SERVER['SCRIPT_NAME'];
$basePath = '';
if (strpos($scriptPath, '/admin/') !== false || strpos($scriptPath, '/agent/') !== false || strpos($scriptPath, '/customer/') !== false) {
    $basePath = '../';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>Sojourn Travel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?= $basePath ?>index.php" class="flex items-center gap-2">
                        <svg class="h-8 w-8 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-bold text-xl text-gray-900">Sojourn</span>
                    </a>
                    <div class="hidden sm:ml-8 sm:flex sm:space-x-4">
                        <a href="<?= $basePath ?>index.php" class="text-gray-700 hover:text-brand-600 px-3 py-2 text-sm font-medium">Home</a>
                        <a href="<?= $basePath ?>search.php" class="text-gray-700 hover:text-brand-600 px-3 py-2 text-sm font-medium">Explore</a>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <?php if (isLoggedIn()): ?>
                        <?php 
                        $dashboardUrl = $basePath . 'customer/dashboard.php';
                        if ($_SESSION['role'] === 'admin') $dashboardUrl = $basePath . 'admin/dashboard.php';
                        if ($_SESSION['role'] === 'agent') $dashboardUrl = $basePath . 'agent/dashboard.php';
                        ?>
                        <a href="<?= $dashboardUrl ?>" class="text-gray-700 hover:text-brand-600 px-3 py-2 text-sm font-medium">Dashboard</a>
                        <a href="<?= $basePath ?>logout.php" class="bg-gray-100 hover:bg-gray-200 text-gray-900 px-4 py-2 rounded-lg text-sm font-medium">Logout</a>
                    <?php else: ?>
                        <a href="<?= $basePath ?>login.php" class="text-gray-700 hover:text-brand-600 px-3 py-2 text-sm font-medium">Login</a>
                        <a href="<?= $basePath ?>register.php" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium">Sign Up</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <?php if ($flash = getFlash()): ?>
    <div class="max-w-7xl mx-auto px-4 mt-4">
        <div class="p-4 rounded-lg <?= $flash['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
            <?= e($flash['message']) ?>
        </div>
    </div>
    <?php endif; ?>

    <main class="flex-grow">
