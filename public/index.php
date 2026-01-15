<?php
// public/index.php
require_once '../includes/functions.php';
require_once '../config/database.php';

start_secure_session();

// 1. Prepare Header Data
$base_url = get_base_url();
$auth_buttons = '';
if (is_logged_in()) {
    $dashboard_link = $base_url . '/public/customer/dashboard.php'; // Default
    if ($_SESSION['role'] === 'Admin') $dashboard_link = $base_url . '/public/admin/dashboard.php';
    if ($_SESSION['role'] === 'Agent') $dashboard_link = $base_url . '/public/agent/dashboard.php';

    $auth_buttons = '
        <a href="'.$dashboard_link.'" class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
        <a href="'.$base_url.'/public/logout.php" class="bg-gray-100 text-gray-900 hover:bg-gray-200 px-4 py-2 rounded-md text-sm font-medium">Log out</a>
    ';
} else {
    $auth_buttons = '
        <a href="'.$base_url.'/public/login.php" class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Log in</a>
        <a href="'.$base_url.'/public/register.php" class="bg-brand-600 text-white hover:bg-brand-700 px-4 py-2 rounded-md text-sm font-medium">Sign up</a>
    ';
}

// 2. Fetch Data (Featured Packages)
// Since DB might be empty, we handle gracefully.
$db = new Database();
$conn = $db->getConnection();
$featured_html = '<p class="col-span-3 text-center text-gray-500">No packages available at the moment. Check back soon!</p>';

if ($conn) {
    // Attempt to fetch 3 random active packages
    try {
        $stmt = $conn->prepare("SELECT * FROM TourPackages WHERE AvailabilityStatus = 'Available' LIMIT 3");
        $stmt->execute();
        $packages = $stmt->fetchAll();

        if (count($packages) > 0) {
            $featured_html = '';
            foreach ($packages as $pkg) {
                $price = number_format($pkg['BasePrice'], 2);
                $img = $pkg['ImageURL'] ? $pkg['ImageURL'] : 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80';
                
                $featured_html .= "
                <div class='flex flex-col rounded-lg shadow-lg overflow-hidden'>
                    <div class='flex-shrink-0'>
                        <img class='h-48 w-full object-cover' src='{$img}' alt=''>
                    </div>
                    <div class='flex-1 bg-white p-6 flex flex-col justify-between'>
                        <div class='flex-1'>
                            <p class='text-sm font-medium text-brand-600'>Tour Package</p>
                            <a href='/public/package.php?id={$pkg['PackageID']}' class='block mt-2'>
                                <p class='text-xl font-semibold text-gray-900'>{$pkg['PackageName']}</p>
                                <p class='mt-3 text-base text-gray-500 line-clamp-3'>{$pkg['Description']}</p>
                            </a>
                        </div>
                        <div class='mt-6 flex items-center justify-between'>
                            <p class='text-2xl font-bold text-gray-900'>\${$price}</p>
                            <a href='{$base_url}/public/booking.php?type=package&id={$pkg['PackageID']}' class='bg-brand-50 hover:bg-brand-100 text-brand-700 px-4 py-2 rounded-md text-sm font-medium'>
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
                ";
            }
        }
    } catch (Exception $e) {
        // Silent fail or log
    }
}

// 3. Render Views
load_view('header.html', ['auth_buttons' => $auth_buttons]);
load_view('index.html', ['featured_packages' => $featured_html]);
load_view('footer.html');
?>
