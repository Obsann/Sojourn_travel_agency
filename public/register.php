<?php
// public/register.php
require_once '../includes/functions.php';
require_once '../config/database.php';

start_secure_session();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role']; // Basic, unvalidated role selection for demo

    // Validate
    if (empty($email) || empty($password) || empty($fullname)) {
        $message = '<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4"><p class="text-sm text-red-700">All fields are required.</p></div>';
    } else {
        $db = new Database();
        $conn = $db->getConnection();

        // Check email
        $check = $conn->prepare("SELECT UserID FROM Users WHERE Email = :email");
        $check->bindParam(':email', $email);
        $check->execute();

        if ($check->rowCount() > 0) {
            $message = '<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4"><p class="text-sm text-red-700">Email already registered.</p></div>';
        } else {
            try {
                $conn->beginTransaction();

                // Insert User
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO Users (Email, PasswordHash, Role) VALUES (:email, :password, :role)");
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':role', $role);
                $stmt->execute();
                
                $user_id = $conn->lastInsertId();

                // Insert Profile if Customer
                if ($role === 'Customer') {
                    $pStmt = $conn->prepare("INSERT INTO CustomerProfiles (UserID, FullName) VALUES (:uid, :name)");
                    $pStmt->bindParam(':uid', $user_id);
                    $pStmt->bindParam(':name', $fullname);
                    $pStmt->execute();
                }

                $conn->commit();

                // Success
                $message = '<div class="bg-green-50 border-l-4 border-green-400 p-4 mb-4"><p class="text-sm text-green-700">Account created! <a href="/public/login.php" class="font-bold underline">Login here</a>.</p></div>';

            } catch (Exception $e) {
                $conn->rollBack();
                $message = '<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4"><p class="text-sm text-red-700">Registration failed: ' . $e->getMessage() . '</p></div>';
            }
        }
    }
}

$auth_buttons = '
    <a href="/public/login.php" class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Log in</a>
    <a href="/public/register.php" class="bg-brand-600 border border-transparent text-white hover:bg-brand-700 px-4 py-2 rounded-md text-sm font-medium">Sign up</a>
';

load_view('header.html', ['auth_buttons' => $auth_buttons]);
load_view('register.html', ['message' => $message]);
load_view('footer.html');
?>
