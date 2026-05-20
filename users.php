<?php
require_once 'config.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$action = $_GET['action'] ?? 'list';
$search = $_GET['search'] ?? '';
$message = '';
$error = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $status = $_POST['status'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, email, role, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $password, $email, $role, $status]);
            $message = "User added successfully.";
        } catch(PDOException $e) {
            $error = "Error adding user: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_user'])) {
        $id = $_POST['id'];
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $status = $_POST['status'];
        
        try {
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET email = ?, role = ?, status = ?, password = ? WHERE id = ?");
                $stmt->execute([$email, $role, $status, $password, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET email = ?, role = ?, status = ? WHERE id = ?");
                $stmt->execute([$email, $role, $status, $id]);
            }
            $message = "User updated successfully.";
        } catch(PDOException $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
    } elseif (isset($_POST['toggle_status'])) {
        $id = $_POST['id'];
        $new_status = $_POST['new_status'];
        $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $id]);
        $message = "User status updated.";
    }
}

// Fetch users for list
$query = "SELECT * FROM users";
$params = [];
if ($search) {
    $query .= " WHERE username LIKE ? OR email LIKE ?";
    $params = ["%$search%", "%$search%"];
}
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users - TechStore IS</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
      .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
      .alert-success { background-color: #d4edda; color: #155724; }
      .alert-danger { background-color: #f8d7da; color: #721c24; }
      .form-container { background: rgba(255,255,255,0.05); padding: 20px; border-radius: 8px; margin-bottom: 20px; }
  </style>
</head>
<body>
  <div class="app-layout">
    <aside class="sidebar">
      <div class="logo"><i data-lucide="cpu"></i> TechStore</div>
      <nav class="nav-links">
        <a href="dashboard.php" class="nav-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
        
        <div class="nav-section-title" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); padding: 0.5rem 1.25rem 0.25rem; font-weight: 600;">Transactions</div>
        <a href="sales.php" class="nav-item"><i data-lucide="shopping-cart"></i> Sales</a>
        <a href="inventory.php" class="nav-item"><i data-lucide="package"></i> Inventory Intake</a>
        <a href="services.php" class="nav-item"><i data-lucide="wrench"></i> Service Jobs</a>
        
        <div class="nav-section-title" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); padding: 0.5rem 1.25rem 0.25rem; font-weight: 600;">Management</div>
        <a href="users.php" class="nav-item active"><i data-lucide="users"></i> Users</a>
        <a href="reports.php" class="nav-item"><i data-lucide="file-bar-chart"></i> Reports</a>
        <a href="about.html" class="nav-item"><i data-lucide="info"></i> About Program</a>
      </nav>
      <div class="user-profile">
        <div class="avatar"><?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?></div>
        <div class="user-info">
          <p><?php echo htmlspecialchars($_SESSION['username']); ?></p>
          <span><?php echo htmlspecialchars($_SESSION['role']); ?></span>
        </div>
        <a href="logout.php" style="margin-left: auto; color: var(--text-muted);"><i data-lucide="log-out"></i></a>
      </div>
    </aside>

    <main class="main-content">
      <header class="page-header">
        <h2>User Management</h2>
        <form method="GET" action="users.php" class="search-bar">
          <i data-lucide="search" style="color: var(--text-muted); width: 18px;"></i>
          <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
      </header>

      <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>

      <?php if ($action === 'list'): ?>
      <div style="margin-bottom: 1rem;">
          <a href="users.php?action=add" class="btn btn-primary"><i data-lucide="user-plus" style="width: 18px; margin-right: 5px;"></i> Add New User</a>
      </div>
      <div class="table-container">
        <table class="modern-table">
          <thead>
            <tr>
              <th>Username</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user): ?>
            <tr>
              <td><?php echo htmlspecialchars($user['username']); ?></td>
              <td><?php echo htmlspecialchars($user['email']); ?></td>
              <td><?php echo htmlspecialchars($user['role']); ?></td>
              <td>
                  <span class="status-badge <?php echo $user['status'] === 'Active' ? 'status-completed' : 'status-danger'; ?>">
                      <?php echo $user['status']; ?>
                  </span>
              </td>
              <td style="display: flex; gap: 10px;">
                  <a href="users.php?action=edit&id=<?php echo $user['id']; ?>" title="Edit"><i data-lucide="edit" style="width: 18px;"></i></a>
                  <form method="POST" style="display:inline;">
                      <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                      <input type="hidden" name="new_status" value="<?php echo $user['status'] === 'Active' ? 'Inactive' : 'Active'; ?>">
                      <button type="submit" name="toggle_status" style="background:none; border:none; color:inherit; cursor:pointer;" title="Toggle Status">
                          <i data-lucide="<?php echo $user['status'] === 'Active' ? 'user-x' : 'user-check'; ?>" style="width: 18px; color: <?php echo $user['status'] === 'Active' ? 'var(--danger)' : 'var(--success)'; ?>;"></i>
                      </button>
                  </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php elseif ($action === 'add'): ?>
      <div class="form-container">
          <h3>Add New Account</h3>
          <form method="POST" action="users.php">
              <div class="form-group">
                  <label>Username</label>
                  <input type="text" name="username" class="form-control" required>
              </div>
              <div class="form-group">
                  <label>Email</label>
                  <input type="email" name="email" class="form-control" required>
              </div>
              <div class="form-group">
                  <label>Password</label>
                  <input type="password" name="password" class="form-control" required>
              </div>
              <div class="form-group">
                  <label>Role</label>
                  <select name="role" class="form-control">
                      <option value="User">User</option>
                      <option value="Admin">Admin</option>
                  </select>
              </div>
              <div class="form-group">
                  <label>Status</label>
                  <select name="status" class="form-control">
                      <option value="Active">Active</option>
                      <option value="Inactive">Inactive</option>
                  </select>
              </div>
              <button type="submit" name="add_user" class="btn btn-primary">Save User</button>
              <a href="users.php" class="btn" style="background: rgba(255,255,255,0.1); color: white;">Cancel</a>
          </form>
      </div>

      <?php elseif ($action === 'edit'): 
          $id = $_GET['id'];
          $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
          $stmt->execute([$id]);
          $editUser = $stmt->fetch();
          if ($editUser):
      ?>
      <div class="form-container">
          <h3>Update Account Profile</h3>
          <form method="POST" action="users.php">
              <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
              <div class="form-group">
                  <label>Username (Cannot be changed)</label>
                  <input type="text" class="form-control" value="<?php echo htmlspecialchars($editUser['username']); ?>" disabled>
              </div>
              <div class="form-group">
                  <label>Email</label>
                  <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($editUser['email']); ?>" required>
              </div>
              <div class="form-group">
                  <label>New Password (leave blank to keep current)</label>
                  <input type="password" name="password" class="form-control">
              </div>
              <div class="form-group">
                  <label>Role</label>
                  <select name="role" class="form-control">
                      <option value="User" <?php if($editUser['role'] === 'User') echo 'selected'; ?>>User</option>
                      <option value="Admin" <?php if($editUser['role'] === 'Admin') echo 'selected'; ?>>Admin</option>
                  </select>
              </div>
              <div class="form-group">
                  <label>Status</label>
                  <select name="status" class="form-control">
                      <option value="Active" <?php if($editUser['status'] === 'Active') echo 'selected'; ?>>Active</option>
                      <option value="Inactive" <?php if($editUser['status'] === 'Inactive') echo 'selected'; ?>>Inactive</option>
                  </select>
              </div>
              <button type="submit" name="update_user" class="btn btn-primary">Update Profile</button>
              <a href="users.php" class="btn" style="background: rgba(255,255,255,0.1); color: white;">Cancel</a>
          </form>
      </div>
      <?php endif; endif; ?>
    </main>
  </div>
  <script>
    lucide.createIcons();
  </script>
</body>
</html>
