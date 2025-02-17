<?php
include_once 'check_session.php'; // Enforces that a user is logged in
include 'navigation.php';
include 'db_config.php';
include './class/class.user.php';

$user = new User($db);

// Only Managers can add, edit, or delete users
$canManageUsers = false;
if (isset($_SESSION['user']['position']) && $_SESSION['user']['position'] === "Manager") {
    $canManageUsers = true;
}

// Handle delete action if ?delete_id= is in the URL
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    if ($user->deleteUser($id)) {
        header("Location: users.php?success=User deleted successfully");
        exit;
    } else {
        echo "Error deleting user.";
    }
}

// Handle edit form submission (synchronous form submission)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_user'])) {
    $id = $_POST['id'];
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $status = $_POST['status'];

    if ($user->editUser($id, $firstName, $lastName, $email, $position, $status)) {
        header("Location: users.php?success=User updated successfully");
        exit;
    } else {
        echo "Error updating user.";
    }
}

// Fetch users from the database
$query = "SELECT ID, first_name, last_name, email, position, status, last_login FROM users";
$result = $db->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users - JP 227 Dormitory</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <style>
    /* Simple modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.4);
    }
    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
    }
    .close {
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
    }
    /* Pill styles for status */
    .badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 20px;
      color: #fff;
      font-weight: 500;
      font-size: 0.85rem;
    }
    .badge-active { background-color: #28a745; } /* Green for Active */
    .badge-inactive { background-color: #dc3545; } /* Red for Inactive */
  </style>
</head>
<body>
  <main>
    <div class="container">
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">Users Management</h2>
          <!-- Only show Add User button if the logged-in user is a Manager -->
          <?php if ($canManageUsers): ?>
              <button id="addUserBtn" class="btn btn-primary btn-sm">Add User</button>
          <?php endif; ?>
        </div>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Position</th>
                <th>Status</th>
                <th>Last Login</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="usersTable">
              <?php
              if ($result->num_rows > 0) {
                  while ($row = $result->fetch_assoc()) {
                      // Format last_login: if not empty, display date and time; else show "Never"
                      $lastLogin = !empty($row['last_login'])
                          ? date('Y-m-d H:i:s', strtotime($row['last_login']))
                          : 'Never';
                      // Use pill design for status
                      $statusBadge = ($row['status'] == 'Active')
                          ? '<span class="badge badge-active">Active</span>'
                          : '<span class="badge badge-inactive">Inactive</span>';
                      echo "<tr>
                          <td>{$row['ID']}</td>
                          <td>{$row['first_name']}</td>
                          <td>{$row['last_name']}</td>
                          <td>{$row['email']}</td>
                          <td>{$row['position']}</td>
                          <td>{$statusBadge}</td>
                          <td>{$lastLogin}</td>
                          <td>";
                      if ($canManageUsers) {
                          echo "<button class='btn btn-primary btn-edit' data-id='{$row['ID']}'>Edit</button>
                                <button class='btn btn-danger btn-delete' data-id='{$row['ID']}'>Delete</button>";
                      } else {
                          echo "";
                      }
                      echo "</td>
                      </tr>";
                  }
              } else {
                  echo "<tr><td colspan='8'>No users found.</td></tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Add User Modal (Only for Managers) -->
  <?php if ($canManageUsers): ?>
  <div id="addUserModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Add New User</h3>
        <span id="closeModal" class="close">&times;</span>
      </div>
      <form action="process/process_add_user.php" method="POST">
        <div class="form-group">
          <label for="firstName">First Name:</label>
          <input type="text" id="firstName" name="first_name" required>
        </div>
        <div class="form-group">
          <label for="lastName">Last Name:</label>
          <input type="text" id="lastName" name="last_name" required>
        </div>
        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
          <label for="password">Password:</label>
          <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
          <label for="position">Position:</label>
          <select id="position" name="position" required>
              <option value="Manager">Manager</option>
              <option value="Staff">Staff</option>
              <option value="Assistant">Assistant</option>
          </select>
        </div>
        <div class="form-group">
          <label for="status">Status:</label>
          <select id="status" name="status" required>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
          </select>
        </div>
        <div class="form-group">
          <button type="submit" class="btn btn-primary">Add User</button>
          <button type="button" id="cancelModal" class="btn btn-secondary">Cancel</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- Edit User Modal -->
  <div id="editUserModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Edit User</h3>
        <span id="closeEditModal" class="close">&times;</span>
      </div>
      <form id="editUserForm" method="POST">
        <input type="hidden" name="id" id="editId">
        <div class="form-group">
          <label for="editFirstName">First Name:</label>
          <input type="text" id="editFirstName" name="first_name" required>
        </div>
        <div class="form-group">
          <label for="editLastName">Last Name:</label>
          <input type="text" id="editLastName" name="last_name" required>
        </div>
        <div class="form-group">
          <label for="editEmail">Email:</label>
          <input type="email" id="editEmail" name="email" required>
        </div>
        <div class="form-group">
          <label for="editPosition">Position:</label>
          <select id="editPosition" name="position" required>
              <option value="Manager">Manager</option>
              <option value="Staff">Staff</option>
              <option value="Assistant">Assistant</option>
          </select>
        </div>
        <div class="form-group">
          <label for="editStatus">Status:</label>
          <select id="editStatus" name="status" required>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
          </select>
        </div>
        <div class="form-group">
          <button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
          <button type="button" id="cancelEditModal" class="btn btn-secondary">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Modal behavior for Add User
    const addUserBtn = document.getElementById('addUserBtn');
    const addUserModal = document.getElementById('addUserModal');
    const closeModal = document.getElementById('closeModal');
    const cancelModal = document.getElementById('cancelModal');

    if(addUserBtn) {
        addUserBtn.addEventListener('click', () => {
            addUserModal.style.display = 'block';
        });
    }
    if(closeModal) {
        closeModal.addEventListener('click', () => {
            addUserModal.style.display = 'none';
        });
    }
    if(cancelModal) {
        cancelModal.addEventListener('click', () => {
            addUserModal.style.display = 'none';
        });
    }
    window.addEventListener('click', (event) => {
        if (event.target === addUserModal) {
            addUserModal.style.display = 'none';
        }
    });

    // Modal behavior for Edit User
    const editUserModal = document.getElementById('editUserModal');
    const closeEditModal = document.getElementById('closeEditModal');
    const cancelEditModal = document.getElementById('cancelEditModal');

    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', () => {
            // Get data from the row; data-id is set on the button or fallback to first cell text
            const row = button.closest('tr');
            const id = button.getAttribute('data-id') || row.cells[0].textContent;
            const firstName = row.cells[1].textContent;
            const lastName = row.cells[2].textContent;
            const email = row.cells[3].textContent;
            const position = row.cells[4].textContent;
            const status = row.cells[5].textContent;
            
            document.getElementById('editId').value = id;
            document.getElementById('editFirstName').value = firstName;
            document.getElementById('editLastName').value = lastName;
            document.getElementById('editEmail').value = email;
            document.getElementById('editPosition').value = position;
            document.getElementById('editStatus').value = status;
            
            editUserModal.style.display = 'block';
        });
    });

    closeEditModal.addEventListener('click', () => {
        editUserModal.style.display = 'none';
    });
    cancelEditModal.addEventListener('click', () => {
        editUserModal.style.display = 'none';
    });
    window.addEventListener('click', (event) => {
        if (event.target === editUserModal) {
            editUserModal.style.display = 'none';
        }
    });

    // Delete User
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', () => {
            const id = button.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this user?')) {
                window.location.href = `users.php?delete_id=${id}`;
            }
        });
    });
  </script>
</body>
</html>
