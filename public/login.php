<?php
// public/login.php
require_once '../includes/functions.php';
require_once '../config/database.php';

start_secure_session();

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = '<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4"><p class="text-sm text-red-700">Please fill in all fields.</p></div>';
    } else {
        $db = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("SELECT UserID, PasswordHash, Role FROM Users WHERE Email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['PasswordHash'])) {
            // Login Success
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['role'] = $user['Role'];
            $_SESSION['email'] = $email;

            // Redirect based on role
            if ($user['Role'] === 'Admin') redirect('/public/admin/dashboard.php');
            elseif ($user['Role'] === 'Agent') redirect('/public/agent/dashboard.php');
            else redirect('/public/customer/dashboard.php');

        } else {
            $error_message = '<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4"><p class="text-sm text-red-700">Invalid email or password.</p></div>';
        }
    }
}

// 2. Prepare Header Data (Nav Links logic is essentially static for logout state, but let's be consistent)
$auth_buttons = '
    <a href="/public/login.php" class="text-gray-900 border-b-2 border-brand-500 px-3 py-2 rounded-md text-sm font-medium">Log in</a>
    <a href="/public/register.php" class="bg-brand-600 text-white hover:bg-brand-700 px-4 py-2 rounded-md text-sm font-medium">Sign up</a>
';

load_view('header.html', ['auth_buttons' => $auth_buttons]);
load_view('login.html', ['error_message' => $error_message]);
load_view('footer.html');
?>
