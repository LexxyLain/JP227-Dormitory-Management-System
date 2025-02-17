<?php
include_once 'check_session.php'; 
include_once 'navigation.php';
include_once 'db_config.php';

// Ensure a user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Get the logged-in user's name
$loggedInUser = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];

/* 
   1) Fetch total tenants from the 'tenants' table 
      e.g. SELECT COUNT(*) AS totalTenants FROM tenants
*/
$tenantCountQuery = "SELECT COUNT(*) AS totalTenants FROM tenants";
$resultTenantCount = $db->query($tenantCountQuery);
$totalTenants = 0;
if ($resultTenantCount && $row = $resultTenantCount->fetch_assoc()) {
    $totalTenants = (int)$row['totalTenants'];
}

/* 
   2) Fetch number of occupied rooms 
      i.e. rooms that have current_occupants > 0
*/
$occupiedRoomsQuery = "SELECT COUNT(*) AS occupiedRooms FROM rooms WHERE current_occupants > 0";
$resultOccupied = $db->query($occupiedRoomsQuery);
$occupiedRooms = 0;
if ($resultOccupied && $row = $resultOccupied->fetch_assoc()) {
    $occupiedRooms = (int)$row['occupiedRooms'];
}

/* 
   3) Fetch number of unoccupied rooms 
      i.e. rooms that have current_occupants = 0
*/
$unoccupiedRoomsQuery = "SELECT COUNT(*) AS unoccupiedRooms FROM rooms WHERE current_occupants = 0";
$resultUnoccupied = $db->query($unoccupiedRoomsQuery);
$unoccupiedRooms = 0;
if ($resultUnoccupied && $row = $resultUnoccupied->fetch_assoc()) {
    $unoccupiedRooms = (int)$row['unoccupiedRooms'];
}

/* 
   4) (Optional) If you have a 'payments' or other logic for pending payments, 
      you can fetch it here. For now, we use a placeholder value:
*/
$pendingPayments = 2000; // Example placeholder

// Recent activities (replace with dynamic data if needed)
$recentActivities = [
    ['date' => '2024-02-05 14:30:00', 'activity' => 'New tenant check-in', 'user' => 'John Doe', 'status' => 'Completed'],
    ['date' => '2024-02-05 15:00:00', 'activity' => 'Payment received', 'user' => 'Jane Smith', 'status' => 'Processed']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JP 227 Dormitory</title>
  <!-- Link to your separate dashboard CSS -->
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<main>
  <div class="container">
    <!-- Welcome Section -->
    <div class="welcome">
      <h1>Welcome, <?php echo htmlspecialchars($loggedInUser); ?>!</h1>
      <br>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
      <!-- 1) Total Tenants -->
      <div class="stat-card">
        <div class="stat-value"><?php echo $totalTenants; ?></div>
        <div class="stat-label">Total Tenants</div>
      </div>

      <!-- 2) Occupied Rooms -->
      <div class="stat-card">
        <div class="stat-value"><?php echo $occupiedRooms; ?></div>
        <div class="stat-label">Occupied Rooms</div>
      </div>

      <!-- 3) Unoccupied Rooms -->
      <div class="stat-card">
        <div class="stat-value"><?php echo $unoccupiedRooms; ?></div>
        <div class="stat-label">Unoccupied Rooms</div>
      </div>

      <!-- 4) Pending Payments (placeholder) -->
      <div class="stat-card">
        <div class="stat-value">₱<?php echo number_format($pendingPayments, 2); ?></div>
        <div class="stat-label">Pending Payments</div>
      </div>
    </div>

    <!-- Recent Activities Card -->
    <div class="card">
      <div class="card-header">
        <h2 class="card-title">Recent Activities</h2>
        <button class="btn btn-primary btn-sm">View All</button>
      </div>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Date</th>
              <th>Activity</th>
              <th>User</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentActivities as $activity): ?>
            <tr>
              <td><?php echo htmlspecialchars($activity['date']); ?></td>
              <td><?php echo htmlspecialchars($activity['activity']); ?></td>
              <td><?php echo htmlspecialchars($activity['user']); ?></td>
              <td>
                <span class="badge badge-<?php echo strtolower($activity['status']); ?>">
                  <?php echo htmlspecialchars($activity['status']); ?>
                </span>
              </td>
              <td class="actions">
                <button class="btn btn-sm btn-secondary">View</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<script src="script.js"></script>
</body>
</html>
