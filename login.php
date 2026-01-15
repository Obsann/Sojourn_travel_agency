<?php
// login.php - User Login
require_once 'includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        try {
            $db = db();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];
                
                setFlash('success', 'Welcome back, ' . $user['full_name'] . '!');
                
                // Redirect based on role
                if ($user['role'] === 'admin') redirect('admin/dashboard.php');
                if ($user['role'] === 'agent') redirect('agent/dashboard.php');
                redirect('customer/dashboard.php');
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
        }
    }
}

$pageTitle = 'Login';
require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-2xl font-bold text-gray-900 text-center mb-2">Welcome Back</h1>
        <p class="text-gray-600 text-center mb-8">Sign in to your account</p>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6"><?= e($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                    value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            
            <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-3 rounded-lg font-semibold">
                Sign In
            </button>
        </form>
        
        <p class="text-center text-gray-600 mt-6">
            Don't have an account? <a href="register.php" class="text-brand-600 hover:text-brand-700 font-medium">Sign up</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
