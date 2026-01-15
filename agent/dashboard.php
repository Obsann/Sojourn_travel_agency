<?php
// agent/dashboard.php - Agent Dashboard
require_once '../includes/functions.php';

requireRole('agent');

$db = db();
$agentId = $_SESSION['user_id'];

// Handle package creation with file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_package'])) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $status = $_POST['status'] ?? 'available';
    $imageUrl = '';
    
    // Handle file upload
    if (isset($_FILES['package_image']) && $_FILES['package_image']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $ext = strtolower(pathinfo($_FILES['package_image']['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed) && $_FILES['package_image']['size'] <= $maxSize) {
            $filename = uniqid('pkg_') . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/packages/';
            
            // Ensure directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            if (move_uploaded_file($_FILES['package_image']['tmp_name'], $uploadDir . $filename)) {
                $imageUrl = 'uploads/packages/' . $filename;
            }
        }
    }
    
    if (!empty($name) && $price > 0) {
        $stmt = $db->prepare("INSERT INTO packages (agent_id, name, description, price, image_url, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$agentId, $name, $description, $price, $imageUrl, $status]);
        setFlash('success', 'Package created successfully!');
        redirect('dashboard.php');
    }
}

// Handle package deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    // Get the package to delete its image
    $stmt = $db->prepare("SELECT image_url FROM packages WHERE id = ? AND agent_id = ?");
    $stmt->execute([(int)$_POST['delete_id'], $agentId]);
    $package = $stmt->fetch();
    
    // Delete the image file if it exists in uploads folder
    if ($package && !empty($package['image_url']) && strpos($package['image_url'], 'uploads/') === 0) {
        $imagePath = __DIR__ . '/../' . $package['image_url'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    $stmt = $db->prepare("DELETE FROM packages WHERE id = ? AND agent_id = ?");
    $stmt->execute([(int)$_POST['delete_id'], $agentId]);
    setFlash('success', 'Package deleted.');
    redirect('dashboard.php');
}

// Fetch packages
$stmt = $db->prepare("SELECT * FROM packages WHERE agent_id = ? ORDER BY created_at DESC");
$stmt->execute([$agentId]);
$packages = $stmt->fetchAll();

// Fetch bookings for agent's packages
$stmt = $db->prepare("
    SELECT b.*, p.name as package_name, u.email as customer_email 
    FROM bookings b 
    JOIN packages p ON b.package_id = p.id 
    JOIN users u ON b.customer_id = u.id 
    WHERE p.agent_id = ? 
    ORDER BY b.created_at DESC
");
$stmt->execute([$agentId]);
$bookings = $stmt->fetchAll();

$pageTitle = 'Agent Dashboard';
require_once '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Agent Dashboard</h1>
    
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Create Package -->
        <div>
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h2 class="text-xl font-semibold mb-6">Create Package</h2>
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <input type="hidden" name="create_package" value="1">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Package Name</label>
                        <input type="text" name="name" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (ETB)</label>
                        <input type="number" name="price" step="0.01" min="0" required
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Package Image</label>
                        <input type="file" name="package_image" accept="image/*"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500">
                        <p class="text-xs text-gray-500 mt-1">Accepted: JPG, PNG, GIF, WebP (Max 5MB)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-brand-500">
                            <option value="available">Available</option>
                            <option value="soldout">Sold Out</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-2 rounded-lg font-medium">
                        Create Package
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Packages & Bookings -->
        <div class="lg:col-span-2 space-y-8">
            <!-- My Packages -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold">My Packages</h2>
                </div>
                <?php if (empty($packages)): ?>
                    <div class="p-8 text-center text-gray-500">No packages created yet.</div>
                <?php else: ?>
                    <div class="divide-y">
                        <?php foreach ($packages as $pkg): ?>
                            <div class="p-4 flex justify-between items-center">
                                <div>
                                    <h3 class="font-semibold"><?= e($pkg['name']) ?></h3>
                                    <p class="text-sm text-gray-500">ETB <?= number_format($pkg['price'], 2) ?></p>
                                </div>
                                <div class="flex items-center gap-4">
                                    <span class="px-2 py-1 rounded text-xs font-medium 
                                        <?= $pkg['status'] === 'available' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                        <?= ucfirst($pkg['status']) ?>
                                    </span>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="delete_id" value="<?= $pkg['id'] ?>">
                                        <button type="submit" onclick="return confirm('Delete this package?')"
                                            class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Customer Bookings -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-6 border-b">
                    <h2 class="text-xl font-semibold">Customer Bookings</h2>
                </div>
                <?php if (empty($bookings)): ?>
                    <div class="p-8 text-center text-gray-500">No bookings yet.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Package</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td class="px-4 py-3 text-sm">#<?= $b['id'] ?></td>
                                        <td class="px-4 py-3 text-sm"><?= e($b['package_name']) ?></td>
                                        <td class="px-4 py-3 text-sm"><?= e($b['customer_email']) ?></td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 rounded text-xs font-medium 
                                                <?= $b['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                                <?= ucfirst($b['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm"><?= date('M j, Y', strtotime($b['travel_date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
