<?php
// public/customer/dashboard.php
require_once '../../includes/functions.php';
require_once '../../config/database.php';

start_secure_session();
require_login();
require_role('Customer'); // Ensure only Customers access this

$db = new Database();
$conn = $db->getConnection();
$user_id = $_SESSION['user_id'];

// Handle Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cancel_id'])) {
        $cancel_id = $_POST['cancel_id'];
        $stmt = $conn->prepare("UPDATE Bookings SET Status = 'Cancelled' WHERE BookingID = :bid AND CustomerID = :cid");
        $stmt->execute([':bid' => $cancel_id, ':cid' => $user_id]);
        redirect('/public/customer/dashboard.php');
    }

    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $new_name = $_POST['fullname'];
        $new_phone = $_POST['phone'];
        
        // Update user profile
        $stmt = $conn->prepare("UPDATE CustomerProfiles SET FullName = :name, PhoneNumber = :phone WHERE UserID = :uid");
        $stmt->execute([':name' => $new_name, ':phone' => $new_phone, ':uid' => $user_id]);
        redirect('/public/customer/dashboard.php');
    }
}


// Fetch Bookings
$stmt = $conn->prepare("
    SELECT b.BookingID, b.BookingDate, b.TravelDate, b.Status, p.PackageName 
    FROM Bookings b 
    LEFT JOIN TourPackages p ON b.PackageID = p.PackageID 
    WHERE b.CustomerID = :cid 
    ORDER BY b.BookingID DESC
");
$stmt->execute([':cid' => $user_id]);
$bookings = $stmt->fetchAll();

$rows_html = '';
if (count($bookings) > 0) {
    foreach ($bookings as $row) {
        $statusColor = 'bg-gray-100 text-gray-800';
        if ($row['Status'] == 'Confirmed') $statusColor = 'bg-green-100 text-green-800';
        if ($row['Status'] == 'Cancelled') $statusColor = 'bg-red-100 text-red-800';
        
        $actions = '';
        if ($row['Status'] !== 'Cancelled') {
            $actions = "
            <form method='POST' onsubmit='return confirm(\"Are you sure?\");' class='inline'>
                <input type='hidden' name='cancel_id' value='{$row['BookingID']}'>
                <button type='submit' class='text-red-600 hover:text-red-900'>Cancel</button>
            </form>
            ";
        }

        $rows_html .= "
        <tr>
            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>#{$row['BookingID']}</td>
            <td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>{$row['PackageName']}</td>
            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>{$row['TravelDate']}</td>
            <td class='px-6 py-4 whitespace-nowrap'>
                <span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full {$statusColor}'>
                    {$row['Status']}
                </span>
            </td>
            <td class='px-6 py-4 whitespace-nowrap text-right text-sm font-medium'>
                {$actions}
            </td>
        </tr>
        ";
    }
} else {
    $rows_html = "<tr><td colspan='5' class='px-6 py-4 text-center text-gray-500'>No bookings found.</td></tr>";
}

$auth_buttons = '
    <a href="/public/customer/dashboard.php" class="text-brand-600 font-bold px-3 py-2 rounded-md text-sm">Dashboard</a>
    <a href="/public/logout.php" class="bg-gray-100 text-gray-900 hover:bg-gray-200 px-4 py-2 rounded-md text-sm font-medium">Log out</a>
';

// Get Profile
$pStmt = $conn->prepare("SELECT * FROM CustomerProfiles WHERE UserID = :uid");
$pStmt->execute([':uid' => $user_id]);
$profile = $pStmt->fetch();
$fullname = $profile['FullName'] ?? '';
$phone = $profile['PhoneNumber'] ?? '';

load_view('header.html', ['auth_buttons' => $auth_buttons]);
load_view('customer/dashboard.html', [
    'booking_rows' => $rows_html,
    'user_email' => $_SESSION['email'],
    'fullname' => $fullname,
    'phone' => $phone
]);
load_view('footer.html');
?>
