<?php
// admin/dashboard.php - Admin Dashboard
require_once '../includes/functions.php';

requireRole('admin');

$db = db();

// Handle user deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $deleteId = (int)$_POST['delete_user_id'];
    if ($deleteId !== $_SESSION['user_id']) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$deleteId]);
        setFlash('success', 'User deleted.');
    }
    redirect('dashboard.php');
}

// Handle destination management
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_destination'])) {
    $name = trim($_POST['dest_name'] ?? '');
    $description = trim($_POST['dest_description'] ?? '');
    $imageUrl = trim($_POST['dest_image'] ?? '');
    
    if (!empty($name)) {
        $stmt = $db->prepare("INSERT INTO destinations (name, description, image_url) VALUES (?, ?, ?)");
        $stmt->execute([$name, $description, $imageUrl]);
        setFlash('success', 'Destination added.');
        redirect('dashboard.php');
    }
}

// Stats
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalBookings = $db->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$totalRevenue = $db->query("SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'success'")->fetchColumn();
$totalPackages = $db->query("SELECT COUNT(*) FROM packages")->fetchColumn();

// Users
$users = $db->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 20")->fetchAll();

// Destinations
$destinations = $db->query("SELECT * FROM destinations ORDER BY id DESC")->fetchAll();

$pageTitle = 'Admin Dashboard';
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Admin Dashboard</h1>
    
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-brand-500">
            <p class="text-gray-500 text-sm">Total Users</p>
            <p class="text-3xl font-bold text-gray-900"><?= $totalUsers ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-green-500">
            <p class="text-gray-500 text-sm">Total Bookings</p>
            <p class="text-3xl font-bold text-gray-900"><?= $totalBookings ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-yellow-500">
            <p class="text-gray-500 text-sm">Total Revenue</p>
            <p class="text-3xl font-bold text-gray-900">ETB <?= number_format($totalRevenue, 2) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow p-6 border-l-4 border-purple-500">
            <p class="text-gray-500 text-sm">Packages</p>
            <p class="text-3xl font-bold text-gray-900"><?= $totalPackages ?></p>
        </div>
    </div>
    
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Add Destination -->
        <div>
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-6">Add Destination</h2>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="add_destination" value="1">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="dest_name" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="dest_description" rows="2"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Image URL</label>
                        <input type="url" name="dest_image"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500">
                    </div>
                    
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2 rounded-lg font-medium">
                        Add Destination
                    </button>
                </form>
                
                <!-- Destinations List -->
                <div class="mt-6 pt-6 border-t">
                    <h3 class="font-medium text-gray-900 mb-3">Existing Destinations</h3>
                    <?php if (empty($destinations)): ?>
                        <p class="text-gray-500 text-sm">No destinations yet.</p>
                    <?php else: ?>
                        <ul class="space-y-2">
                            <?php foreach ($destinations as $dest): ?>
                                <li class="text-sm text-gray-600"><?= e($dest['name']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Users -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold">User Management</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="px-4 py-3 text-sm"><?= $user['id'] ?></td>
                                    <td class="px-4 py-3 text-sm"><?= e($user['email']) ?></td>
                                    <td class="px-4 py-3 text-sm"><?= e($user['full_name']) ?></td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="px-2 py-1 rounded text-xs font-medium 
                                            <?= $user['role'] === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                               ($user['role'] === 'agent' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                                                <button type="submit" onclick="return confirm('Delete this user?')"
                                                    class="text-red-600 hover:text-red-800">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-gray-400">(You)</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
