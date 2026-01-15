<?php
// public/admin/dashboard.php
require_once '../../includes/functions.php';
require_once '../../config/database.php';

start_secure_session();
require_role('Admin');

$db = new Database();
$conn = $db->getConnection();

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $uid = $_POST['delete_user_id'];
    // Prevent deleting self
    if ($uid != $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = :id");
        $stmt->execute([':id' => $uid]);
    }
    redirect('/public/admin/dashboard.php');
}

// Stats
$stats = [];
$stats['users'] = $conn->query("SELECT COUNT(*) FROM Users")->fetchColumn();
$stats['bookings'] = $conn->query("SELECT COUNT(*) FROM Bookings")->fetchColumn();

// Users List
$users = $conn->query("SELECT * FROM Users ORDER BY CreatedAt DESC LIMIT 50")->fetchAll();

$rows_html = '';
foreach ($users as $u) {
    // Hide Delete for self or other admins (optional, but good practice specific to this demo)
    $deleteBtn = '';
    if ($u['UserID'] != $_SESSION['user_id']) {
        $deleteBtn = "
        <form method='POST' onsubmit='return confirm(\"Delete user?\");' class='inline'>
            <input type='hidden' name='delete_user_id' value='{$u['UserID']}'>
            <button type='submit' class='text-red-600 hover:text-red-900'>Delete</button>
        </form>";
    }

    $rows_html .= "
    <tr>
        <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>{$u['UserID']}</td>
        <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>{$u['Email']}</td>
        <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>
            <span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800'>{$u['Role']}</span>
        </td>
        <td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>{$u['CreatedAt']}</td>
        <td class='px-6 py-4 whitespace-nowrap text-right text-sm font-medium'>{$deleteBtn}</td>
    </tr>
    ";
}

$auth_buttons = '
    <a href="/public/admin/dashboard.php" class="text-brand-600 font-bold px-3 py-2 rounded-md text-sm">Admin</a>
    <a href="/public/logout.php" class="bg-gray-100 text-gray-900 hover:bg-gray-200 px-4 py-2 rounded-md text-sm font-medium">Log out</a>
';

load_view('header.html', ['auth_buttons' => $auth_buttons]);
load_view('admin/dashboard.html', [
    'user_rows' => $rows_html,
    'total_users' => $stats['users'],
    'total_bookings' => $stats['bookings']
]);
load_view('footer.html');
?>
