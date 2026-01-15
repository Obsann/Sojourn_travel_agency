<?php
// public/booking.php
require_once '../includes/functions.php';
require_once '../config/database.php';

start_secure_session();
require_login(); // Must be logged in

$db = new Database();
$conn = $db->getConnection();

$type = $_GET['type'] ?? 'package';
$id = $_GET['id'] ?? 0;

// Fetch Item Details
$item_name = 'Unknown Item';
$item_desc = '';
$price = 0;

try {
    if ($type === 'package') {
        $stmt = $conn->prepare("SELECT * FROM TourPackages WHERE PackageID = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $item = $stmt->fetch();
        if ($item) {
            $item_name = $item['PackageName'];
            $item_desc = $item['Description'];
            $price = $item['BasePrice'];
        }
    }
} catch (Exception $e) { abort(500); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process Booking
    $travel_date = $_POST['travel_date'];
    $customer_id = $_SESSION['user_id'];
    
    try {
        $conn->beginTransaction();
        
        // 1. Create Booking
        $sql = "INSERT INTO Bookings (CustomerID, PackageID, Status, TravelDate) VALUES (:cid, :pid, 'Confirmed', :tdate)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':cid' => $customer_id, ':pid' => $id, ':tdate' => $travel_date]);
        $booking_id = $conn->lastInsertId();

        // 2. Create Payment (Simulated Success)
        $pay_sql = "INSERT INTO Payments (BookingID, Amount, PaymentStatus) VALUES (:bid, :amt, 'Validated')";
        $pay_stmt = $conn->prepare($pay_sql);
        $pay_stmt->execute([':bid' => $booking_id, ':amt' => $price]);

        $conn->commit();

        // Redirect to Dashboard with success
        redirect('/public/customer/dashboard.php?booking=success');

    } catch (Exception $e) {
        $conn->rollBack();
        echo "Error: " . $e->getMessage();
    }
    exit;
}

// Navbar Logic
$auth_buttons = '
    <a href="/public/customer/dashboard.php" class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">Dashboard</a>
    <a href="/public/logout.php" class="bg-gray-100 text-gray-900 hover:bg-gray-200 px-4 py-2 rounded-md text-sm font-medium">Log out</a>
';

load_view('header.html', ['auth_buttons' => $auth_buttons]);
load_view('booking.html', [
    'item_name' => $item_name,
    'item_desc' => $item_desc,
    'price' => number_format($price, 2),
    'id' => $id,
    'type' => $type
]);
load_view('footer.html');
?>
