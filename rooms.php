<?php
include_once 'check_session.php'; 
include_once 'navigation.php';
include 'db_config.php';

// Get search & floor filter values from GET
$search = $_GET['search'] ?? '';
$floor  = $_GET['floor'] ?? '';

// Build the base query
$roomsQuery = "
    SELECT rooms.room_id, rooms.room_number, rooms.capacity, rooms.current_occupants, rooms.price_per_month,
        GROUP_CONCAT(CONCAT(tenants.first_name, ' ', tenants.last_name) SEPARATOR ', ') AS tenants_list
    FROM rooms
    LEFT JOIN tenants ON rooms.room_number = tenants.room_number
    WHERE 1=1
";

// If a search term is provided, filter room_number (partial match)
if (!empty($search)) {
    $searchEsc = $db->real_escape_string($search);
    $roomsQuery .= " AND rooms.room_number LIKE '%$searchEsc%'";
}

// If a floor is selected, filter room_number that starts with that digit
if (!empty($floor)) {
    $floorEsc = $db->real_escape_string($floor);
    $roomsQuery .= " AND rooms.room_number LIKE '$floorEsc%'";
}

// Group by room_id to combine tenant names and order by room_id (FIFO)
$roomsQuery .= " GROUP BY rooms.room_id ORDER BY rooms.room_id ASC";

$rooms = $db->query($roomsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - JP 227 Dormitory</title>
    <link href="css/styles.css" rel="stylesheet">
    <style>
        .full-room {
            background-color: #ffcccc; /* Light red background for full rooms */
        }
        /* Filter & Search Container styling */
        .filter-container {
            margin: 1rem 0;
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        .filter-container input[type="text"],
        .filter-container select {
            padding: 0.4rem;
            font-size: 0.9rem;
        }
        .filter-container button {
            padding: 0.4rem 1rem;
        }
    </style>
</head>
<body>
<main>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Room Management</h2>
            </div>

            <!-- Search & Filter Form -->
            <div class="filter-container">
                <form method="GET" action="rooms.php" style="display: flex; gap: 1rem;">
                    <!-- Search by room number -->
                    <input type="text" name="search" placeholder="Search by room number" value="<?= htmlspecialchars($search); ?>">
                    
                    <!-- Floor filter: 1st floor, 2nd floor, 3rd floor -->
                    <select name="floor">
                        <option value="" <?= ($floor == '' ? 'selected' : ''); ?>>All floors</option>
                        <option value="1" <?= ($floor === '1' ? 'selected' : ''); ?>>1st floor</option>
                        <option value="2" <?= ($floor === '2' ? 'selected' : ''); ?>>2nd floor</option>
                        <option value="3" <?= ($floor === '3' ? 'selected' : ''); ?>>3rd floor</option>
                    </select>
                    <button type="submit" class="apply2">Apply</button>
                </form>
            </div>
            <!-- End Search & Filter -->

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Room No.</th>
                            <th>Capacity</th>
                            <th>Occupied</th>
                            <th>Price/Month</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($room = $rooms->fetch_assoc()): ?>
                            <tr class="<?= ($room['current_occupants'] >= $room['capacity'] && $room['current_occupants'] != 2) ? 'full-room' : ''; ?>">
                                <td><?= htmlspecialchars($room['room_number']); ?></td>
                                <td><?= htmlspecialchars($room['capacity']); ?></td>
                                <td><?= htmlspecialchars($room['current_occupants']); ?></td>
                                <td><?= htmlspecialchars($room['price_per_month']); ?></td>
                                <td>
                                    <button class="btn btn-primary view-tenants-btn" data-room-number="<?= htmlspecialchars($room['room_number']); ?>" data-tenants="<?= htmlspecialchars($room['tenants_list']); ?>">
                                        View Tenants
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Tenant Dialog -->
<div id="tenantDialog" class="modal" style="display: none;">
    <div class="modal-content card">
        <div class="modal-header">
            <h3>Room Tenants</h3>
            <span class="close-btn">&times;</span>
        </div>
        <div class="modal-body">
            <p id="tenantList"></p>
        </div>
    </div>
</div>

<script>
    const tenantDialog = document.getElementById('tenantDialog');
    const tenantList = document.getElementById('tenantList');
    const closeBtn = tenantDialog.querySelector('.close-btn');

    document.querySelectorAll('.view-tenants-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            tenantList.textContent = btn.dataset.tenants || 'No tenants assigned to this room.';
            tenantDialog.style.display = 'block';
        });
    });

    closeBtn.addEventListener('click', () => {
        tenantDialog.style.display = 'none';
    });

    window.addEventListener('click', (e) => {
        if (e.target === tenantDialog) {
            tenantDialog.style.display = 'none';
        }
    });
</script>
</body>
</html>
