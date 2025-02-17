<?php
include_once 'navigation.php';
include 'db_config.php';
include 'class/class_tenants.php';

// --- AJAX Search Handling ---
if (isset($_GET['ajax']) && $_GET['ajax'] == 'true' && isset($_GET['q'])) {
    $q = $_GET['q'];
    $qEsc = $db->real_escape_string($q);
    
    // Search by first name starting with the query (case-insensitive)
    $sql = "SELECT * FROM tenants WHERE first_name LIKE '{$qEsc}%' ORDER BY tenant_id ASC";
    $result = $db->query($sql);
    
    $tenants = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $tenants[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($tenants);
    exit;
}

// --- End AJAX Search Handling ---

// Get filter values from GET (if present)
$search  = $_GET['search'] ?? '';
$filter  = $_GET['filter'] ?? '';

// Build the base query
$sql = "SELECT * FROM tenants WHERE 1=1";

// Apply the single dropdown filter (for status or payment)
if (!empty($filter)) {
    $filter = $db->real_escape_string($filter);
    if ($filter == 'Active' || $filter == 'Inactive') {
        $sql .= " AND status = '$filter'";
    } elseif ($filter == 'paid' || $filter == 'partial' || $filter == 'unpaid') {
        $sql .= " AND payment = '$filter'";
    }
}

// Apply search (if provided via normal form submission)
if (!empty($search)) {
    $searchEsc = $db->real_escape_string($search);
    $sql .= " AND (first_name LIKE '%$searchEsc%' OR last_name LIKE '%$searchEsc%')";
}

// Always order FIFO (by tenant_id ascending)
$sql .= " ORDER BY tenant_id ASC";

$result = $db->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Tenants - JP 227 Dormitory</title>
  <link href="css/tenants.css" rel="stylesheet">
</head>
<body>
<main>
    <div class="container">
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Tenants List</h2>
          <button class="btn btn-primary btn-update" id="addTenantBtn">Add Tenant</button>
        </div>

        <!-- Search & Filter Form -->
        <div class="filter-container">
          <input type="text" id="searchInput" placeholder="Search by first name..." value="<?= htmlspecialchars($search); ?>">
          <select id="filterDropdown" name="filter">
              <option value="" <?= ($filter == '' ? 'selected' : ''); ?>>All</option>
              <option value="Active"   <?= ($filter=='Active' ? 'selected' : ''); ?>>Active</option>
              <option value="Inactive" <?= ($filter=='Inactive' ? 'selected' : ''); ?>>Inactive</option>
              <option value="paid"     <?= ($filter=='paid' ? 'selected' : ''); ?>>Paid</option>
              <option value="partial"  <?= ($filter=='partial' ? 'selected' : ''); ?>>Partial</option>
              <option value="unpaid"   <?= ($filter=='unpaid' ? 'selected' : ''); ?>>Unpaid</option>
          </select>
          <button id="applyFilter" class="applybtn">Apply</button>
        </div>
        <!-- End Search & Filter Form -->

        <div class="table-container">
          <table>
            <thead>
              <tr>
                <!-- ID column removed -->
                <th>Name</th>
                <th>Age</th>
                <th>Phone Number</th>
                <th>Guardian Contact</th>
                <th>Status</th>
                <th>Payment</th>
                <th>Room Number</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="tenantsTable">
              <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($tenant = $result->fetch_assoc()): ?>
                  <?php 
                    $statusClass  = 'badge-'.strtolower($tenant['status']);
                    $paymentClass = 'badge-'.strtolower($tenant['payment']);
                  ?>
                  <tr 
                    data-tenant-id="<?= $tenant['tenant_id'] ?>"
                    data-first-name="<?= htmlspecialchars($tenant['first_name'], ENT_QUOTES) ?>"
                    data-last-name="<?= htmlspecialchars($tenant['last_name'], ENT_QUOTES) ?>"
                    data-age="<?= htmlspecialchars($tenant['age'], ENT_QUOTES) ?>"
                    data-phone-number="<?= htmlspecialchars($tenant['phone_number'], ENT_QUOTES) ?>"
                    data-guardian-contact-number="<?= htmlspecialchars($tenant['guardian_contact_number'], ENT_QUOTES) ?>"
                    data-status="<?= htmlspecialchars($tenant['status'], ENT_QUOTES) ?>"
                    data-payment="<?= htmlspecialchars($tenant['payment'], ENT_QUOTES) ?>"
                    data-room-number="<?= htmlspecialchars($tenant['room_number'], ENT_QUOTES) ?>"
                  >
                    <td><?= htmlspecialchars($tenant['first_name'].' '.$tenant['last_name']); ?></td>
                    <td><?= htmlspecialchars($tenant['age']); ?></td>
                    <td><?= htmlspecialchars($tenant['phone_number']); ?></td>
                    <td><?= htmlspecialchars($tenant['guardian_contact_number']); ?></td>
                    <td><span class="badge <?= $statusClass; ?>"><?= htmlspecialchars($tenant['status']); ?></span></td>
                    <td><span class="badge <?= $paymentClass; ?>"><?= htmlspecialchars($tenant['payment']); ?></span></td>
                    <td><?= htmlspecialchars($tenant['room_number']); ?></td>
                    <td>
                      <button class="btn btn-primary btn-update" onclick="editTenant(<?= $tenant['tenant_id']; ?>)">Edit</button>
                      <button class="btn btn-danger btn-delete" onclick="deleteTenant(<?= $tenant['tenant_id']; ?>)">Delete</button>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="8">No tenants found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
</main>

<!-- Add Tenant Modal -->
<div id="addTenantModal" class="modal">
  <div class="modal-content card">
    <div class="modal-header">
      <h3>Add New Tenant</h3>
      <span class="close-btn" id="closeAddModal">&times;</span>
    </div>
    <form action="process/process_tenants.php" method="POST">
      <input type="hidden" name="action" value="add">
      <div class="form-group">
        <label for="tenantFirstName">First Name:</label>
        <input type="text" id="tenantFirstName" name="first_name" required>
      </div>
      <div class="form-group">
        <label for="tenantLastName">Last Name:</label>
        <input type="text" id="tenantLastName" name="last_name" required>
      </div>
      <div class="form-group">
        <label for="tenantAge">Age:</label>
        <input type="number" id="tenantAge" name="age" required maxlength="2">
      </div>
      <div class="form-group">
        <label for="tenantPhoneNumber">Phone Number:</label>
        <input type="text" id="tenantPhoneNumber" name="phone_number" required maxlength="11" pattern="\d{11}">
      </div>
      <div class="form-group">
        <label for="tenantGuardianContact">Guardian Contact:</label>
        <input type="text" id="tenantGuardianContact" name="guardian_contact_number" required maxlength="11" pattern="\d{11}">
      </div>
      <div class="form-group">
        <label for="tenantStatus">Status:</label>
        <select id="tenantStatus" name="status" required>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>
      <div class="form-group">
        <label for="tenantPayment">Payment Status:</label>
        <select id="tenantPayment" name="payment" required>
          <option value="paid">Paid</option>
          <option value="partial">Partial</option>
          <option value="unpaid">Unpaid</option>
        </select>
      </div>
      <div class="form-group">
        <label for="tenantRoom">Room Number:</label>
        <select id="tenantRoom" name="room_number" required>
          <option value="" disabled selected>Select a room</option>
          <?php
          $room_query = "SELECT room_id, room_number FROM rooms";
          $roomRes = $db->query($room_query);
          if ($roomRes->num_rows > 0) {
              while ($row = $roomRes->fetch_assoc()) {
                  echo "<option value='" . htmlspecialchars($row['room_number']) . "'>" . htmlspecialchars($row['room_number']) . "</option>";
              }
          } else {
              echo "<option value='' disabled>No rooms available</option>";
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary">Add Tenant</button>
        <button type="button" id="cancelAddModal" class="btn btn-secondary">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Tenant Modal -->
<div id="editTenantModal" class="modal">
  <div class="modal-content card">
    <div class="modal-header">
      <h3>Edit Tenant</h3>
      <span class="close-btn" id="closeEditModal">&times;</span>
    </div>
    <form id="editTenantForm" action="process/process_tenants.php" method="POST">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="tenant_id" id="editTenantId">
      <div class="form-group">
        <label for="editFirstName">First Name:</label>
        <input type="text" id="editFirstName" name="first_name" required>
      </div>
      <div class="form-group">
        <label for="editLastName">Last Name:</label>
        <input type="text" id="editLastName" name="last_name" required>
      </div>
      <div class="form-group">
        <label for="editAge">Age:</label>
        <input type="number" id="editAge" name="age" required maxlength="2">
      </div>
      <div class="form-group">
        <label for="editPhoneNumber">Phone Number:</label>
        <input type="text" id="editPhoneNumber" name="phone_number" required maxlength="11" pattern="\d{11}">
      </div>
      <div class="form-group">
        <label for="editGuardianContact">Guardian Contact:</label>
        <input type="text" id="editGuardianContact" name="guardian_contact_number" required maxlength="11" pattern="\d{11}">
      </div>
      <div class="form-group">
        <label for="editStatus">Status:</label>
        <select id="editStatus" name="status" required>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>
      <div class="form-group">
        <label for="editPayment">Payment Status:</label>
        <select id="editPayment" name="payment" required>
          <option value="paid">Paid</option>
          <option value="partial">Partial</option>
          <option value="unpaid">Unpaid</option>
        </select>
      </div>
      <div class="form-group">
        <label for="editRoom">Room Number:</label>
        <select id="editRoom" name="room_number" required>
          <?php
          $room_query2 = "SELECT room_id, room_number FROM rooms";
          $roomRes2 = $db->query($room_query2);
          while ($row = $roomRes2->fetch_assoc()) {
              echo "<option value='" . htmlspecialchars($row['room_number']) . "'>" . htmlspecialchars($row['room_number']) . "</option>";
          }
          ?>
        </select>
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary">Update Tenant</button>
        <button type="button" id="cancelEditModal" class="btn btn-secondary">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
    // AJAX Live Search
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('keyup', function() {
        const query = searchInput.value;
        fetch(`tenants.php?ajax=true&q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('tenantsTable');
                tbody.innerHTML = "";
                if(data.length > 0){
                    data.forEach(tenant => {
                        const statusClass = 'badge-' + tenant.status.toLowerCase();
                        const paymentClass = 'badge-' + tenant.payment.toLowerCase();
                        const row = document.createElement('tr');
                        row.setAttribute('data-tenant-id', tenant.tenant_id);
                        row.setAttribute('data-first-name', tenant.first_name);
                        row.setAttribute('data-last-name', tenant.last_name);
                        row.setAttribute('data-age', tenant.age);
                        row.setAttribute('data-phone-number', tenant.phone_number);
                        row.setAttribute('data-guardian-contact-number', tenant.guardian_contact_number);
                        row.setAttribute('data-status', tenant.status);
                        row.setAttribute('data-payment', tenant.payment);
                        row.setAttribute('data-room-number', tenant.room_number);
                        row.innerHTML = `
                          <td>${tenant.first_name} ${tenant.last_name}</td>
                          <td>${tenant.age}</td>
                          <td>${tenant.phone_number}</td>
                          <td>${tenant.guardian_contact_number}</td>
                          <td><span class="badge ${statusClass}">${tenant.status}</span></td>
                          <td><span class="badge ${paymentClass}">${tenant.payment}</span></td>
                          <td>${tenant.room_number}</td>
                          <td>
                              <button class="btn btn-primary btn-update" onclick="editTenant(${tenant.tenant_id})">Edit</button>
                              <button class="btn btn-danger btn-delete" onclick="deleteTenant(${tenant.tenant_id})">Delete</button>
                          </td>
                        `;
                        tbody.appendChild(row);
                    });
                } else {
                    tbody.innerHTML = "<tr><td colspan='8'>No tenants found.</td></tr>";
                }
            });
    });

    // Filter Apply Button: reload page with selected filter and search term
    document.getElementById('applyFilter').addEventListener('click', () => {
        const searchVal = document.getElementById('searchInput').value;
        const filterVal = document.getElementById('filterDropdown').value;
        window.location.href = `tenants.php?search=${encodeURIComponent(searchVal)}&filter=${encodeURIComponent(filterVal)}`;
    });

    // Modal behavior for Add Tenant
    document.getElementById('addTenantBtn').addEventListener('click', () => {
        document.getElementById('addTenantModal').style.display = 'block';
    });
    document.getElementById('closeModal').addEventListener('click', () => {
        document.getElementById('addTenantModal').style.display = 'none';
    });
    document.getElementById('cancelAddModal').addEventListener('click', () => {
        document.getElementById('addTenantModal').style.display = 'none';
    });
    window.addEventListener('click', (event) => {
        if (event.target === document.getElementById('addTenantModal')) {
            document.getElementById('addTenantModal').style.display = 'none';
        }
        if (event.target === document.getElementById('editTenantModal')) {
            document.getElementById('editTenantModal').style.display = 'none';
        }
    });

    // Edit Tenant Modal Handlers
    function editTenant(tenantId) {
        const row = document.querySelector(`tr[data-tenant-id='${tenantId}']`);
        document.getElementById('editTenantId').value         = row.getAttribute('data-tenant-id');
        document.getElementById('editFirstName').value        = row.getAttribute('data-first-name');
        document.getElementById('editLastName').value         = row.getAttribute('data-last-name');
        document.getElementById('editAge').value              = row.getAttribute('data-age');
        document.getElementById('editPhoneNumber').value      = row.getAttribute('data-phone-number');
        document.getElementById('editGuardianContact').value  = row.getAttribute('data-guardian-contact-number');
        document.getElementById('editStatus').value           = row.getAttribute('data-status');
        document.getElementById('editPayment').value          = row.getAttribute('data-payment');
        document.getElementById('editRoom').value             = row.getAttribute('data-room-number');
        document.getElementById('editTenantModal').style.display = 'block';
    }

    // Delete Tenant Function
    function deleteTenant(tenantId) {
        if (confirm('Are you sure you want to delete this tenant?')) {
            fetch(`process/process_tenants.php?action=delete&id=${tenantId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        }
    }

    // Edit Modal Close Handlers
    document.getElementById('closeEditModal').addEventListener('click', () => {
        document.getElementById('editTenantModal').style.display = 'none';
    });
    document.getElementById('cancelEditModal').addEventListener('click', () => {
        document.getElementById('editTenantModal').style.display = 'none';
    });
</script>
</body>
</html>
