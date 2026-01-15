<?php
// search.php - Search Packages, Flights & Hotels
require_once 'includes/functions.php';

$query = trim($_GET['q'] ?? '');
$type = $_GET['type'] ?? 'all';

$packages = [];
$flights = [];
$hotels = [];

try {
    $db = db();
    
    // Fetch Packages
    if ($type === 'all' || $type === 'package') {
        if (!empty($query)) {
            $stmt = $db->prepare("SELECT *, 'package' as item_type FROM packages WHERE status = 'available' AND (name LIKE ? OR description LIKE ?)");
            $stmt->execute(["%$query%", "%$query%"]);
        } else {
            $stmt = $db->query("SELECT *, 'package' as item_type FROM packages WHERE status = 'available' ORDER BY created_at DESC");
        }
        $packages = $stmt->fetchAll();
    }
    
    // Fetch Flights
    if ($type === 'all' || $type === 'flight') {
        if (!empty($query)) {
            $stmt = $db->prepare("SELECT *, 'flight' as item_type FROM services WHERE type = 'flight' AND (name LIKE ? OR description LIKE ?)");
            $stmt->execute(["%$query%", "%$query%"]);
        } else {
            $stmt = $db->query("SELECT *, 'flight' as item_type FROM services WHERE type = 'flight'");
        }
        $flights = $stmt->fetchAll();
    }
    
    // Fetch Hotels
    if ($type === 'all' || $type === 'hotel') {
        if (!empty($query)) {
            $stmt = $db->prepare("SELECT *, 'hotel' as item_type FROM services WHERE type = 'hotel' AND (name LIKE ? OR description LIKE ?)");
            $stmt->execute(["%$query%", "%$query%"]);
        } else {
            $stmt = $db->query("SELECT *, 'hotel' as item_type FROM services WHERE type = 'hotel'");
        }
        $hotels = $stmt->fetchAll();
    }
} catch (Exception $e) {
    // Handle error
}

$pageTitle = 'Explore';
require_once 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-12">
    <h1 class="text-3xl font-bold text-gray-900 mb-8">Explore Destinations</h1>
    
    <div class="grid lg:grid-cols-4 gap-8">
        <!-- Filters -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow p-6 sticky top-24">
                <h3 class="font-semibold text-lg mb-4">Filters</h3>
                <form method="GET">
                    <input type="text" name="q" value="<?= e($query) ?>" placeholder="Search..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 mb-4">
                    
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select name="type" class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-4">
                        <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>All</option>
                        <option value="package" <?= $type === 'package' ? 'selected' : '' ?>>Tour Packages</option>
                        <option value="flight" <?= $type === 'flight' ? 'selected' : '' ?>>Flights</option>
                        <option value="hotel" <?= $type === 'hotel' ? 'selected' : '' ?>>Hotels</option>
                    </select>
                    
                    <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-3 rounded-lg font-semibold">
                        Search
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Results -->
        <div class="lg:col-span-3 space-y-12">
            
            <!-- Tour Packages -->
            <?php if (($type === 'all' || $type === 'package') && !empty($packages)): ?>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="bg-brand-100 text-brand-700 p-2 rounded-lg">📦</span> Tour Packages
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <?php foreach ($packages as $pkg): ?>
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                            <img src="<?= e($pkg['image_url'] ?: 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=800') ?>" 
                                 alt="<?= e($pkg['name']) ?>" class="w-full h-48 object-cover">
                            <div class="p-6">
                                <span class="text-xs font-semibold text-brand-600 uppercase">Tour Package</span>
                                <h3 class="text-xl font-bold text-gray-900 mt-2"><?= e($pkg['name']) ?></h3>
                                <p class="text-gray-600 mt-2 line-clamp-2"><?= e($pkg['description']) ?></p>
                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-2xl font-bold text-gray-900">ETB <?= number_format($pkg['price'], 2) ?></span>
                                    <a href="booking.php?type=package&id=<?= $pkg['id'] ?>" 
                                       class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-lg font-medium">
                                        Book Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Flights -->
            <?php if (($type === 'all' || $type === 'flight') && !empty($flights)): ?>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="bg-blue-100 text-blue-700 p-2 rounded-lg">✈️</span> Flights
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <?php foreach ($flights as $flight): ?>
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                            <img src="<?= e($flight['image_url'] ?: 'https://images.unsplash.com/photo-1436491865332-7a61a109cc05?w=800') ?>" 
                                 alt="<?= e($flight['name']) ?>" class="w-full h-40 object-cover">
                            <div class="p-6">
                                <span class="text-xs font-semibold text-blue-600 uppercase">Flight</span>
                                <h3 class="text-xl font-bold text-gray-900 mt-2"><?= e($flight['name']) ?></h3>
                                <p class="text-gray-600 mt-2"><?= e($flight['description']) ?></p>
                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-2xl font-bold text-gray-900">ETB <?= number_format($flight['price'], 2) ?></span>
                                    <a href="booking.php?type=service&id=<?= $flight['id'] ?>" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                                        Book Flight
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Hotels -->
            <?php if (($type === 'all' || $type === 'hotel') && !empty($hotels)): ?>
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="bg-green-100 text-green-700 p-2 rounded-lg">🏨</span> Hotels
                </h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <?php foreach ($hotels as $hotel): ?>
                        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                            <img src="<?= e($hotel['image_url'] ?: 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=800') ?>" 
                                 alt="<?= e($hotel['name']) ?>" class="w-full h-40 object-cover">
                            <div class="p-6">
                                <span class="text-xs font-semibold text-green-600 uppercase">Hotel</span>
                                <h3 class="text-xl font-bold text-gray-900 mt-2"><?= e($hotel['name']) ?></h3>
                                <p class="text-gray-600 mt-2"><?= e($hotel['description']) ?></p>
                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-2xl font-bold text-gray-900">ETB <?= number_format($hotel['price'], 2) ?><span class="text-sm text-gray-500">/night</span></span>
                                    <a href="booking.php?type=service&id=<?= $hotel['id'] ?>" 
                                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
                                        Book Hotel
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Empty State -->
            <?php if (empty($packages) && empty($flights) && empty($hotels)): ?>
                <div class="text-center py-16 text-gray-500">
                    <p class="text-lg">No results found<?= $query ? ' for "' . e($query) . '"' : '' ?>.</p>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
