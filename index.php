<?php
// index.php - Landing Page
require_once 'includes/functions.php';

$pageTitle = 'Home';

// Fetch featured packages
$packages = [];
try {
    $db = db();
    $stmt = $db->query("SELECT * FROM packages WHERE status = 'available' LIMIT 6");
    $packages = $stmt->fetchAll();
} catch (Exception $e) {
    // DB might not be set up yet
}

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="relative bg-gradient-to-br from-brand-900 via-brand-700 to-brand-500 text-white">
    <div class="max-w-7xl mx-auto px-4 py-24 sm:px-6 lg:px-8">
        <div class="max-w-3xl">
            <h1 class="text-4xl md:text-6xl font-bold mb-6">
                Discover Your Next <span class="text-brand-100">Dream Destination</span>
            </h1>
            <p class="text-xl text-brand-100 mb-8">
                Explore the world with curated tours, premium flights, and hand-picked hotels. Your journey starts here.
            </p>
            <div class="flex flex-col sm:flex-row gap-4">
                <a href="search.php" class="bg-white text-brand-600 hover:bg-brand-50 px-8 py-4 rounded-lg font-semibold text-lg text-center">
                    Start Exploring
                </a>
                <a href="register.php" class="border-2 border-white text-white hover:bg-white hover:text-brand-600 px-8 py-4 rounded-lg font-semibold text-lg text-center">
                    Join Now
                </a>
            </div>
        </div>
    </div>
</div>
<br>
<br>

<!-- Search Bar -->
<div class="max-w-4xl mx-auto px-4 -mt-8">
    <form action="search.php" method="GET" class="bg-white rounded-xl shadow-xl p-6">
        <div class="flex flex-col md:flex-row gap-4">
            <input type="text" name="q" placeholder="Search destinations, hotels, tours..."
                class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-8 py-3 rounded-lg font-semibold">
                Search
            </button>
        </div>
    </form>
</div>

<!-- Featured Packages -->
<div class="max-w-7xl mx-auto px-4 py-16">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Packages</h2>
        <p class="text-gray-600 max-w-2xl mx-auto">Hand-picked destinations for your next adventure.</p>
    </div>

    <?php if (empty($packages)): ?>
        <div class="text-center py-12 text-gray-500">
            <p>No packages available yet. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($packages as $pkg): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow">
                    <img src="<?= e($pkg['image_url'] ?: 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=800') ?>" 
                         alt="<?= e($pkg['name']) ?>" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <span class="text-xs font-semibold text-brand-600 uppercase tracking-wide">Tour Package</span>
                        <h3 class="text-xl font-bold text-gray-900 mt-2"><?= e($pkg['name']) ?></h3>
                        <p class="text-gray-600 mt-2 line-clamp-2"><?= e($pkg['description']) ?></p>
                        <div class="mt-4 flex justify-between items-center">
                            <span class="text-2xl font-bold text-gray-900">ETB <?= number_format($pkg['price'], 2) ?></span>
                            <a href="booking.php?type=package&id=<?= $pkg['id'] ?>" 
                               class="bg-brand-50 hover:bg-brand-100 text-brand-700 px-4 py-2 rounded-lg font-medium">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
