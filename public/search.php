<?php
// public/search.php
require_once '../includes/functions.php';
require_once '../config/database.php';

start_secure_session();

$db = new Database();
$conn = $db->getConnection();

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : 'all';

$results_html = '';

try {
    // 1. Search Packages
    $sql = "SELECT PackageID as ID, PackageName as Name, Description, BasePrice as Price, ImageURL, 'package' as Type FROM TourPackages WHERE AvailabilityStatus = 'Available'";
    $params = [];

    if (!empty($query)) {
        $sql .= " AND (PackageName LIKE :q OR Description LIKE :q)";
        $params[':q'] = "%$query%";
    }

    // (If we had distinct tables for flights/hotels populated, we'd UNION here, or conditional logic)
    // For simplicity of this demo, we assume the user searches Packages primarily.
    
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    $items = $stmt->fetchAll();

    if (count($items) > 0) {
        foreach ($items as $item) {
            $price = number_format($item['Price'], 2);
            $img = $item['ImageURL'] ? $item['ImageURL'] : 'https://images.unsplash.com/photo-1507608616759-54f48f0af0ee?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80';
            
            $results_html .= "
            <div class='bg-white rounded-lg shadow overflow-hidden'>
                <img class='h-48 w-full object-cover' src='{$img}' alt=''>
                <div class='p-6'>
                    <div class='flex justify-between items-start'>
                        <div>
                            <span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 uppercase tracking-wide'>
                                {$item['Type']}
                            </span>
                        </div>
                        <p class='text-xl font-bold text-gray-900'>\${$price}</p>
                    </div>
                    <a href='#' class='block mt-2'>
                        <p class='text-lg font-semibold text-gray-900'>{$item['Name']}</p>
                        <p class='mt-2 text-sm text-gray-500 line-clamp-3'>{$item['Description']}</p>
                    </a>
                    <div class='mt-4'>
                        <a href='/public/booking.php?type={$item['Type']}&id={$item['ID']}' class='block w-full text-center bg-brand-600 border border-transparent rounded-md py-2 text-white font-medium hover:bg-brand-700'>
                            Book Now
                        </a>
                    </div>
                </div>
            </div>
            ";
        }
    } else {
        $results_html = '<p class="col-span-2 text-center text-gray-500 py-12">No results found matching your criteria.</p>';
    }

} catch (Exception $e) {
    $results_html = '<p class="text-red-500">Error fetching results.</p>';
}

// Navbar Logic
$auth_buttons = '';
if (is_logged_in()) {
    $dashboard_link = '/public/customer/dashboard.php';
    if ($_SESSION['role'] === 'Admin') $dashboard_link = '/public/admin/dashboard.php';
    if ($_SESSION['role'] === 'Agent') $dashboard_link = '/public/agent/dashboard.php';

    $auth_buttons = '
        <a href="'.$dashboard_link.'" class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
        <a href="/public/logout.php" class="bg-gray-100 text-gray-900 hover:bg-gray-200 px-4 py-2 rounded-md text-sm font-medium">Log out</a>
    ';
} else {
    $auth_buttons = '
        <a href="/public/login.php" class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Log in</a>
        <a href="/public/register.php" class="bg-brand-600 text-white hover:bg-brand-700 px-4 py-2 rounded-md text-sm font-medium">Sign up</a>
    ';
}

load_view('header.html', ['auth_buttons' => $auth_buttons]);
load_view('search.html', ['search_results' => $results_html, 'query' => htmlspecialchars($query)]);
load_view('footer.html');
?>
