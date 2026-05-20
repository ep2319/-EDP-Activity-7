<?php
require_once 'config.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$search = $_GET['search'] ?? '';
$message = '';
$error = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $customer_name = trim($_POST['customer_name']);
    $device_model = trim($_POST['device_model']);
    $issue_description = trim($_POST['issue_description']);
    $service_fee = (float)$_POST['service_fee'];
    $job_date = $_POST['job_date'] ?: date('Y-m-d');
    $status = $_POST['status'] ?? 'Pending';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO service_jobs (customer_name, device_model, issue_description, service_fee, job_date, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$customer_name, $device_model, $issue_description, $service_fee, $job_date, $status]);
        $message = "Service/Repair ticket created successfully.";
    } catch(PDOException $e) {
        $error = "Error creating service ticket: " . $e->getMessage();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $job_id = (int)$_POST['job_id'];
    $new_status = $_POST['new_status'];
    try {
        $stmt = $pdo->prepare("UPDATE service_jobs SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $job_id]);
        $message = "Ticket #$job_id status updated to $new_status.";
    } catch(PDOException $e) {
        $error = "Error updating status: " . $e->getMessage();
    }
}

// Fetch service jobs
$query = "SELECT * FROM service_jobs";
$params = [];
if ($search) {
    $query .= " WHERE customer_name LIKE ? OR device_model LIKE ? OR issue_description LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$query .= " ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$jobs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Service & Repair Jobs - TechStore IS</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
      .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
      .alert-success { background-color: #d4edda; color: #155724; }
      .alert-danger { background-color: #f8d7da; color: #721c24; }
      .form-container { background: rgba(255,255,255,0.03); padding: 20px; border-radius: 1rem; margin-bottom: 25px; border: 1px solid var(--border); }
      .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
      .nav-section-title { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); padding: 0.5rem 1.25rem 0.25rem; font-weight: 600; }
  </style>
</head>
<body>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo"><i data-lucide="cpu"></i> TechStore</div>
      <nav class="nav-links">
        <a href="dashboard.php" class="nav-item"><i data-lucide="layout-dashboard"></i> Dashboard</a>
        
        <div class="nav-section-title">Transactions</div>
        <a href="sales.php" class="nav-item"><i data-lucide="shopping-cart"></i> Sales</a>
        <a href="inventory.php" class="nav-item"><i data-lucide="package"></i> Inventory Intake</a>
        <a href="services.php" class="nav-item active"><i data-lucide="wrench"></i> Service Jobs</a>
        
        <div class="nav-section-title">Management</div>
        <a href="users.php" class="nav-item"><i data-lucide="users"></i> Users</a>
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

    <!-- Main Content -->
    <main class="main-content">
      <header class="page-header">
        <h2>Service & Repair Orders</h2>
        <form method="GET" action="services.php" class="search-bar">
          <i data-lucide="search" style="color: var(--text-muted); width: 18px;"></i>
          <input type="text" name="search" placeholder="Search customer, device, issue..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
      </header>

      <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>

      <!-- Form to create service ticket -->
      <div class="glass-panel form-container">
        <h3 style="margin-bottom: 1rem; font-family: 'Outfit';">Create New Support/Repair Ticket</h3>
        <form method="POST" action="services.php">
          <div class="form-grid">
            <div class="form-group">
              <label>Customer Name</label>
              <input type="text" name="customer_name" class="form-control" placeholder="e.g. Stephen Strange" required>
            </div>
            <div class="form-group">
              <label>Device Model</label>
              <input type="text" name="device_model" class="form-control" placeholder="e.g. Dell XPS 15 Laptop" required>
            </div>
            <div class="form-group" style="grid-column: span 2;">
              <label>Reported Issue Description</label>
              <input type="text" name="issue_description" class="form-control" placeholder="e.g. Overheating and fan making loud grinding noises" required>
            </div>
            <div class="form-group">
              <label>Est. Service Fee ($)</label>
              <input type="number" step="0.01" name="service_fee" class="form-control" placeholder="e.g. 150.00" required>
            </div>
            <div class="form-group">
              <label>Job Date</label>
              <input type="date" name="job_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
              <label>Initial Status</label>
              <select name="status" class="form-control">
                <option value="Pending">Pending</option>
                <option value="In Progress">In Progress</option>
                <option value="Completed">Completed</option>
              </select>
            </div>
          </div>
          <div style="margin-top: 10px;">
            <button type="submit" name="add_service" class="btn btn-primary" style="width: auto;">
              <i data-lucide="tool" style="width: 18px; margin-right: 8px;"></i> Create Service Ticket
            </button>
          </div>
        </form>
      </div>

      <!-- Data Grid Table -->
      <h3 style="margin-bottom: 1rem; font-family: 'Outfit';">Active & Historical Support Tickets</h3>
      <div class="table-container">
        <table class="modern-table">
          <thead>
            <tr>
              <th>Ticket ID</th>
              <th>Customer</th>
              <th>Device Model</th>
              <th>Reported Issue</th>
              <th>Service Fee</th>
              <th>Date</th>
              <th>Current Status</th>
              <th>Update Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($jobs)): ?>
            <tr>
                <td colspan="8" style="text-align: center; color: var(--text-muted);">No service/repair jobs recorded.</td>
            </tr>
            <?php else: foreach ($jobs as $job): ?>
            <tr>
              <td>#SRV-<?php echo $job['id']; ?></td>
              <td style="font-weight: 500;"><?php echo htmlspecialchars($job['customer_name']); ?></td>
              <td><span style="color: var(--secondary); font-weight: 500;"><?php echo htmlspecialchars($job['device_model']); ?></span></td>
              <td><?php echo htmlspecialchars($job['issue_description']); ?></td>
              <td style="font-weight: 600;">$<?php echo number_format($job['service_fee'], 2); ?></td>
              <td><?php echo htmlspecialchars($job['job_date']); ?></td>
              <td>
                <?php 
                  $badgeClass = 'status-pending';
                  if ($job['status'] === 'Completed') $badgeClass = 'status-completed';
                  elseif ($job['status'] === 'In Progress') $badgeClass = 'status-shipped';
                ?>
                <span class="status-badge <?php echo $badgeClass; ?>">
                  <?php echo htmlspecialchars($job['status']); ?>
                </span>
              </td>
              <td>
                <form method="POST" action="services.php" style="display: flex; gap: 5px; align-items: center;">
                  <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                  <select name="new_status" class="form-control" style="padding: 0.25rem 0.5rem; font-size: 0.8rem; width: auto; background: rgba(0,0,0,0.3);">
                    <option value="Pending" <?php if($job['status']==='Pending') echo 'selected'; ?>>Pending</option>
                    <option value="In Progress" <?php if($job['status']==='In Progress') echo 'selected'; ?>>In Progress</option>
                    <option value="Completed" <?php if($job['status']==='Completed') echo 'selected'; ?>>Completed</option>
                  </select>
                  <button type="submit" name="update_status" class="btn btn-primary" style="padding: 0.25rem 0.5rem; font-size: 0.75rem; width: auto;" title="Apply status">
                    <i data-lucide="check" style="width: 14px;"></i>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
  <script>
    lucide.createIcons();
  </script>
</body>
</html>
