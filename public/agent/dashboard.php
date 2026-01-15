<?php
// public/agent/dashboard.php
require_once '../../includes/functions.php';
require_once '../../config/database.php';

start_secure_session();
require_role('Agent');

$db = new Database();
$conn = $db->getConnection();
$agent_id = $_SESSION['user_id'];

// Handle Form Posts
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create') {
        $name = $_POST['name'];
        $price = $_POST['price'];
        $desc = $_POST['description'];
        $img = $_POST['image_url'];
        $status = $_POST['status'];

        $sql = "INSERT INTO TourPackages (AgentID, PackageName, Description, BasePrice, AvailabilityStatus, ImageURL) VALUES (:aid, :name, :desc, :price, :status, :img)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':aid' => $agent_id,
            ':name' => $name,
            ':desc' => $desc,
            ':price' => $price,
            ':status' => $status,
            ':img' => $img
        ]);
        redirect('/public/agent/dashboard.php');
    }

    if (isset($_POST['delete_id'])) {
        // Handle deletion
        $del_id = $_POST['delete_id'];
        $stmt = $conn->prepare("DELETE FROM TourPackages WHERE PackageID = :id AND AgentID = :aid");
        $stmt->execute([':id' => $del_id, ':aid' => $agent_id]);
        redirect('/public/agent/dashboard.php');
    }
}

// Fetch Packages
$stmt = $conn->prepare("SELECT * FROM TourPackages WHERE AgentID = :aid ORDER BY PackageID DESC");
$stmt->execute([':aid' => $agent_id]);
$packages = $stmt->fetchAll();

$rows_html = '';
if (count($packages) > 0) {
    foreach ($packages as $p) {
        $rows_html .= "
        <tr>
            <td class='px-6 py-4'>
                <div class='text-sm font-medium text-gray-900'>{$p['PackageName']}</div>
                <div class='text-sm text-gray-500 trunctate max-w-xs'>{$p['Description']}</div>
            </td>
            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>\${$p['BasePrice']}</td>
            <td class='px-6 py-4 whitespace-nowrap'>
                <span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800'>
                    {$p['AvailabilityStatus']}
                </span>
            </td>
             <td class='px-6 py-4 whitespace-nowrap text-right text-sm font-medium'>
                <form method='POST' onsubmit='return confirm(\"Delete package?\");' class='inline'>
                    <input type='hidden' name='delete_id' value='{$p['PackageID']}'>
                    <button type='submit' class='text-red-600 hover:text-red-900'>Delete</button>
                </form>
            </td>
        </tr>
        ";
    }
} else {
    $rows_html = "<tr><td colspan='4' class='px-6 py-4 text-center text-gray-500'>No packages created yet.</td></tr>";
}

// Fetch Bookings for Agent's Packages
$bStmt = $conn->prepare("
    SELECT b.BookingID, b.BookingDate, b.Status, u.Email, p.PackageName 
    FROM Bookings b
    JOIN TourPackages p ON b.PackageID = p.PackageID
    JOIN Users u ON b.CustomerID = u.UserID
    WHERE p.AgentID = :aid
    ORDER BY b.BookingDate DESC
");
$bStmt->execute([':aid' => $agent_id]);
$agent_bookings = $bStmt->fetchAll();

$booking_rows = '';
if (count($agent_bookings) > 0) {
    foreach ($agent_bookings as $b) {
        $booking_rows .= "
        <tr>
            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>#{$b['BookingID']}</td>
            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>{$b['PackageName']}</td>
            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>{$b['Email']}</td>
            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>{$b['Status']}</td>
            <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>{$b['BookingDate']}</td>
        </tr>";
    }
} else {
    $booking_rows = "<tr><td colspan='5' class='px-6 py-4 text-center text-gray-500'>No bookings received yet.</td></tr>";
}

$auth_buttons = '
    <a href="/public/agent/dashboard.php" class="text-brand-600 font-bold px-3 py-2 rounded-md text-sm">Dashboard</a>
    <a href="/public/logout.php" class="bg-gray-100 text-gray-900 hover:bg-gray-200 px-4 py-2 rounded-md text-sm font-medium">Log out</a>
';

load_view('header.html', ['auth_buttons' => $auth_buttons]);
load_view('agent/dashboard.html', [
    'package_rows' => $rows_html,
    'booking_rows' => $booking_rows
]);
load_view('footer.html');
?>
