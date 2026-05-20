<?php
require_once 'config.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - TechStore IS</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
  <div class="app-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="logo">
        <i data-lucide="cpu"></i> TechStore
      </div>
      <nav class="nav-links">
        <a href="dashboard.php" class="nav-item active">
          <i data-lucide="layout-dashboard"></i> Dashboard
        </a>
        
        <div class="nav-section-title" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); padding: 0.5rem 1.25rem 0.25rem; font-weight: 600;">Transactions</div>
        <a href="sales.php" class="nav-item">
          <i data-lucide="shopping-cart"></i> Sales
        </a>
        <a href="inventory.php" class="nav-item">
          <i data-lucide="package"></i> Inventory Intake
        </a>
        <a href="services.php" class="nav-item">
          <i data-lucide="wrench"></i> Service Jobs
        </a>
        
        <div class="nav-section-title" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted); padding: 0.5rem 1.25rem 0.25rem; font-weight: 600;">Management</div>
        <a href="users.php" class="nav-item">
          <i data-lucide="users"></i> Users
        </a>
        <a href="reports.php" class="nav-item">
          <i data-lucide="file-bar-chart"></i> Reports
        </a>
        <a href="about.html" class="nav-item">
          <i data-lucide="info"></i> About Program
        </a>
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
        <h2>System Overview</h2>
        <div class="search-bar">
          <i data-lucide="search" style="color: var(--text-muted); width: 18px;"></i>
          <input type="text" placeholder="Search orders, customers...">
        </div>
      </header>

      <div class="grid-cards">
        <div class="glass-panel stat-card">
          <div class="stat-header">
            <span>Total Revenue</span>
            <div class="stat-icon"><i data-lucide="dollar-sign"></i></div>
          </div>
          <div class="stat-value">$714.47</div>
          <div class="stat-change text-success">
            <i data-lucide="trending-up" style="width: 14px;"></i> +12% from last week
          </div>
        </div>

        <div class="glass-panel stat-card">
          <div class="stat-header">
            <span>Total Orders</span>
            <div class="stat-icon"><i data-lucide="shopping-cart"></i></div>
          </div>
          <div class="stat-value">10</div>
          <div class="stat-change text-success">
            <i data-lucide="trending-up" style="width: 14px;"></i> 100% fulfill rate
          </div>
        </div>

        <div class="glass-panel stat-card">
          <div class="stat-header">
            <span>Products</span>
            <div class="stat-icon"><i data-lucide="package"></i></div>
          </div>
          <div class="stat-value">10</div>
          <div class="stat-change text-muted">
            Active in catalog
          </div>
        </div>

        <div class="glass-panel stat-card">
          <div class="stat-header">
            <span>Low Stock Alerts</span>
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--danger);"><i data-lucide="alert-triangle"></i></div>
          </div>
          <div class="stat-value">2</div>
          <div class="stat-change text-danger">
            <i data-lucide="alert-circle" style="width: 14px;"></i> Requires attention
          </div>
        </div>
      </div>

      <h3 style="margin-bottom: 1rem; font-family: 'Outfit';">Recent Orders</h3>
      <div class="table-container">
        <table class="modern-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer</th>
              <th>Date</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>#10001</td>
              <td>Clark Kent</td>
              <td>Oct 01, 2023</td>
              <td><span class="status-badge status-completed">Completed</span></td>
              <td><a href="#"><i data-lucide="eye" style="width: 18px;"></i></a></td>
            </tr>
            <tr>
              <td>#10002</td>
              <td>Bruce Wayne</td>
              <td>Oct 02, 2023</td>
              <td><span class="status-badge status-completed">Completed</span></td>
              <td><a href="#"><i data-lucide="eye" style="width: 18px;"></i></a></td>
            </tr>
            <tr>
              <td>#10004</td>
              <td>Barry Allen</td>
              <td>Oct 04, 2023</td>
              <td><span class="status-badge status-pending">Pending</span></td>
              <td><a href="#"><i data-lucide="eye" style="width: 18px;"></i></a></td>
            </tr>
            <tr>
              <td>#10006</td>
              <td>Arthur Curry</td>
              <td>Oct 06, 2023</td>
              <td><span class="status-badge status-shipped">Shipped</span></td>
              <td><a href="#"><i data-lucide="eye" style="width: 18px;"></i></a></td>
            </tr>
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
