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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_receipt'])) {
    $supplier_name = trim($_POST['supplier_name']);
    $product_name = trim($_POST['product_name']);
    $quantity_received = (int)$_POST['quantity_received'];
    $cost_price = (float)$_POST['cost_price'];
    $receipt_date = $_POST['receipt_date'] ?: date('Y-m-d');
    
    try {
        $stmt = $pdo->prepare("INSERT INTO inventory_receipts (supplier_name, product_name, quantity_received, cost_price, receipt_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$supplier_name, $product_name, $quantity_received, $cost_price, $receipt_date]);
        $message = "Inventory intake shipment logged successfully.";
    } catch(PDOException $e) {
        $error = "Error logging intake: " . $e->getMessage();
    }
}

// Fetch inventory receipts
$query = "SELECT * FROM inventory_receipts";
$params = [];
if ($search) {
    $query .= " WHERE supplier_name LIKE ? OR product_name LIKE ?";
    $params = ["%$search%", "%$search%"];
}
$query .= " ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$receipts = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inventory Intake - TechStore IS</title>
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
        <a href="inventory.php" class="nav-item active"><i data-lucide="package"></i> Inventory Intake</a>
        <a href="services.php" class="nav-item"><i data-lucide="wrench"></i> Service Jobs</a>
        
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
        <h2>Inventory Intake Receipts</h2>
        <form method="GET" action="inventory.php" class="search-bar">
          <i data-lucide="search" style="color: var(--text-muted); width: 18px;"></i>
          <input type="text" name="search" placeholder="Search supplier or product..." value="<?php echo htmlspecialchars($search); ?>">
        </form>
      </header>

      <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>

      <!-- Form to log intake -->
      <div class="glass-panel form-container">
        <h3 style="margin-bottom: 1rem; font-family: 'Outfit';">Log Shipment Intake</h3>
        <form method="POST" action="inventory.php">
          <div class="form-grid">
            <div class="form-group">
              <label>Supplier/Vendor Name</label>
              <input type="text" name="supplier_name" class="form-control" placeholder="e.g. Corsair Global" required>
            </div>
            <div class="form-group">
              <label>Product Name</label>
              <input type="text" name="product_name" class="form-control" placeholder="e.g. DDR5 RAM 32GB" required>
            </div>
            <div class="form-group">
              <label>Quantity Received</label>
              <input type="number" name="quantity_received" class="form-control" min="1" value="10" required>
            </div>
            <div class="form-group">
              <label>Unit Cost Price ($)</label>
              <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="e.g. 120.00" required>
            </div>
            <div class="form-group">
              <label>Receipt Date</label>
              <input type="date" name="receipt_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
          </div>
          <div style="margin-top: 10px;">
            <button type="submit" name="add_receipt" class="btn btn-primary" style="width: auto;">
              <i data-lucide="file-plus" style="width: 18px; margin-right: 8px;"></i> Log Intake Shipment
            </button>
          </div>
        </form>
      </div>

      <!-- Data Grid Table -->
      <h3 style="margin-bottom: 1rem; font-family: 'Outfit';">Inventory Receipt Records</h3>
      <div class="table-container">
        <table class="modern-table">
          <thead>
            <tr>
              <th>Receipt ID</th>
              <th>Supplier Vendor</th>
              <th>Product/Item</th>
              <th>Qty Received</th>
              <th>Unit Cost Price</th>
              <th>Total Shipment Cost</th>
              <th>Date Received</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($receipts)): ?>
            <tr>
                <td colspan="7" style="text-align: center; color: var(--text-muted);">No inventory receipts logged.</td>
            </tr>
            <?php else: foreach ($receipts as $r): ?>
            <tr>
              <td>#REC-<?php echo $r['id']; ?></td>
              <td style="font-weight: 500;"><?php echo htmlspecialchars($r['supplier_name']); ?></td>
              <td><?php echo htmlspecialchars($r['product_name']); ?></td>
              <td><span class="status-badge status-shipped"><?php echo htmlspecialchars($r['quantity_received']); ?> units</span></td>
              <td>$<?php echo number_format($r['cost_price'], 2); ?></td>
              <td style="font-weight: 600;">$<?php echo number_format($r['cost_price'] * $r['quantity_received'], 2); ?></td>
              <td><?php echo htmlspecialchars($r['receipt_date']); ?></td>
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
