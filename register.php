<?php
// register.php - User Registration
require_once 'includes/functions.php';

if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    
    // Validate role
    if (!in_array($role, ['customer', 'agent'])) {
        $role = 'customer';
    }
    
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            $db = db();
            
            // Check if email exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'Email is already registered.';
            } else {
                // Create user
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (email, password, full_name, role) VALUES (?, ?, ?, ?)");
                $stmt->execute([$email, $hashedPassword, $name, $role]);
                
                setFlash('success', 'Account created! Please log in.');
                redirect('login.php');
            }
        } catch (Exception $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}

$pageTitle = 'Register';
require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-xl shadow-lg p-8">
        <h1 class="text-2xl font-bold text-gray-900 text-center mb-2">Create Account</h1>
        <p class="text-gray-600 text-center mb-8">Join Sojourn Travel today</p>
        
        <?php if ($error): ?>
            <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6"><?= e($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <input type="text" name="name" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                    value="<?= e($_POST['name'] ?? '') ?>">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input type="email" name="email" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500"
                    value="<?= e($_POST['email'] ?? '') ?>">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required minlength="6"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">I am a...</label>
                <select name="role" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    <option value="customer">Traveler (Customer)</option>
                    <option value="agent">Travel Agent</option>
                </select>
            </div>
            
            <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-3 rounded-lg font-semibold">
                Create Account
            </button>
        </form>
        
        <p class="text-center text-gray-600 mt-6">
            Already have an account? <a href="login.php" class="text-brand-600 hover:text-brand-700 font-medium">Sign in</a>
        </p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
