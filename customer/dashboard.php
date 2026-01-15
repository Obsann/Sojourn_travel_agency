<?php
// customer/dashboard.php - Customer Dashboard
require_once '../includes/functions.php';

requireRole('customer');

$db = db();
$userId = $_SESSION['user_id'];

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $stmt = $db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND customer_id = ?");
    $stmt->execute([(int)$_POST['cancel_id'], $userId]);
    setFlash('success', 'Booking cancelled.');
    redirect('dashboard.php');
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (!empty($name)) {
        $stmt = $db->prepare("UPDATE users SET full_name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$name, $phone, $userId]);
        $_SESSION['name'] = $name;
        setFlash('success', 'Profile updated.');
        redirect('dashboard.php');
    }
}

// Fetch user
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Fetch bookings
$stmt = $db->prepare("
    SELECT b.*, p.name as package_name, p.price 
    FROM bookings b 
    LEFT JOIN packages p ON b.package_id = p.id 
    WHERE b.customer_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

$pageTitle = 'My Dashboard';
require_once '../includes/header.php';
?>

<div class="max-w-6xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">My Dashboard</h1>
    
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Bookings -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="text-xl font-semibold">My Bookings</h2>
                    <a href="../search.php" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg text-sm font-medium">
                        Book New Trip
                    </a>
                </div>
                
                <?php if (empty($bookings)): ?>
                    <div class="p-8 text-center text-gray-500">
                        No bookings yet. <a href="../search.php" class="text-brand-600 hover:underline">Start exploring!</a>
                    </div>
                <?php else: ?>
                    <div class="divide-y">
                        <?php foreach ($bookings as $booking): ?>
                            <div class="p-6 flex justify-between items-center">
                                <div>
                                    <h3 class="font-semibold text-gray-900"><?= e($booking['package_name'] ?? 'Unknown Package') ?></h3>
                                    <p class="text-sm text-gray-500">
                                        Travel Date: <?= date('M j, Y', strtotime($booking['travel_date'])) ?>
                                    </p>
                                    <p class="text-sm text-gray-500">ETB <?= number_format($booking['price'] ?? 0, 2) ?></p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                                        <?= $booking['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 
                                           ($booking['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') ?>">
                                        <?= ucfirst($booking['status']) ?>
                                    </span>
                                    <?php if ($booking['status'] !== 'cancelled'): ?>
                                        <form method="POST" class="inline ml-2">
                                            <input type="hidden" name="cancel_id" value="<?= $booking['id'] ?>">
                                            <button type="submit" onclick="return confirm('Cancel this booking?')" 
                                                class="text-red-600 hover:text-red-800 text-sm">Cancel</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Profile -->
        <div>
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-6">Profile</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="update_profile" value="1">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" value="<?= e($user['email']) ?>" disabled
                            class="w-full px-4 py-2 border rounded-lg bg-gray-50 text-gray-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" value="<?= e($user['full_name']) ?>" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="<?= e($user['phone']) ?>"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500">
                    </div>
                    
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2 rounded-lg font-medium">
                        Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
