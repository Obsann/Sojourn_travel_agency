<?php
// booking.php - Booking Page
require_once 'includes/functions.php';

requireLogin();

$type = $_GET['type'] ?? 'package';
$id = (int)($_GET['id'] ?? 0);

$item = null;
$error = '';
$itemType = 'package'; // package or service

try {
    $db = db();
    if ($type === 'package' && $id > 0) {
        $stmt = $db->prepare("SELECT * FROM packages WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        $itemType = 'package';
    } elseif ($type === 'service' && $id > 0) {
        $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();
        $itemType = 'service';
    }
} catch (Exception $e) {
    $error = 'Failed to load item.';
}

if (!$item) {
    setFlash('error', 'Package not found.');
    redirect('search.php');
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $travelDate = $_POST['travel_date'] ?? '';
    
    if (empty($travelDate)) {
        $error = 'Please select a travel date.';
    } else {
        try {
            $db->beginTransaction();
            
            // Create booking based on type
            if ($itemType === 'package') {
                $stmt = $db->prepare("INSERT INTO bookings (customer_id, package_id, travel_date, status) VALUES (?, ?, ?, 'confirmed')");
                $stmt->execute([$_SESSION['user_id'], $id, $travelDate]);
            } else {
                $stmt = $db->prepare("INSERT INTO bookings (customer_id, service_id, travel_date, status) VALUES (?, ?, ?, 'confirmed')");
                $stmt->execute([$_SESSION['user_id'], $id, $travelDate]);
            }
            $bookingId = $db->lastInsertId();
            
            // Create payment
            $stmt = $db->prepare("INSERT INTO payments (booking_id, amount, status) VALUES (?, ?, 'success')");
            $stmt->execute([$bookingId, $item['price']]);
            
            $db->commit();
            
            setFlash('success', 'Booking confirmed! Your trip is scheduled for ' . date('F j, Y', strtotime($travelDate)));
            redirect('customer/dashboard.php');
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Booking failed. Please try again.';
        }
    }
}

$pageTitle = 'Book ' . $item['name'];
require_once 'includes/header.php';
?>

<div class="max-w-2xl mx-auto px-4 py-12">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <img src="<?= e($item['image_url'] ?: 'https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=800') ?>" 
             alt="<?= e($item['name']) ?>" class="w-full h-48 object-cover">
        
        <div class="p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2"><?= e($item['name']) ?></h1>
            <p class="text-gray-600 mb-4"><?= e($item['description']) ?></p>
            <p class="text-3xl font-bold text-brand-600 mb-6">ETB <?= number_format($item['price'], 2) ?></p>
            
            <?php if ($error): ?>
                <div class="bg-red-50 text-red-700 px-4 py-3 rounded-lg mb-6"><?= e($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Travel Date</label>
                    <input type="date" name="travel_date" required min="<?= date('Y-m-d') ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                </div>
                
                <div class="border-t pt-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Payment Details (Simulated)</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <input type="text" placeholder="Card Number" value="4242 4242 4242 4242"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                        </div>
                        <input type="text" placeholder="MM/YY" value="12/25"
                            class="px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                        <input type="text" placeholder="CVC" value="123"
                            class="px-4 py-3 border border-gray-300 rounded-lg bg-gray-50">
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-brand-600 hover:bg-brand-700 text-white py-4 rounded-lg font-semibold text-lg">
                    Confirm & Pay ETB <?= number_format($item['price'], 2) ?>
                </button>
                
                <a href="search.php" class="block text-center text-gray-500 hover:text-gray-700">Cancel</a>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
