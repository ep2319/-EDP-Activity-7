<?php
require_once 'config.php';

// Check if logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Fetch all three sets of transaction data
$stmtSales = $pdo->query("SELECT * FROM sales_transactions ORDER BY id DESC");
$salesData = $stmtSales->fetchAll(PDO::FETCH_ASSOC);

$stmtInv = $pdo->query("SELECT * FROM inventory_receipts ORDER BY id DESC");
$invData = $stmtInv->fetchAll(PDO::FETCH_ASSOC);

$stmtSrv = $pdo->query("SELECT * FROM service_jobs ORDER BY id DESC");
$srvData = $stmtSrv->fetchAll(PDO::FETCH_ASSOC);

// Current user details for report signing placeholder
$currentUser = $_SESSION['username'] ?? 'Administrator';
$currentRole = $_SESSION['role'] ?? 'System Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Advanced Report Generator - TechStore IS</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <!-- Include ExcelJS CDN -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script>
  <!-- Include Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
      .tabs-header { display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 10px; }
      .tab-btn { background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--text-muted); padding: 0.75rem 1.5rem; border-radius: 0.75rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 8px; }
      .tab-btn:hover { color: var(--text-main); background: rgba(255,255,255,0.1); }
      .tab-btn.active { background: linear-gradient(135deg, var(--primary), var(--secondary)); color: white; border-color: transparent; box-shadow: 0 4px 15px rgba(99,102,241,0.3); }
      .tab-pane { display: none; }
      .tab-pane.active { display: block; }
      
      .grid-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px; }
      .search-box { background: rgba(0,0,0,0.2); border: 1px solid var(--border); padding: 0.5rem 1rem; border-radius: 0.5rem; color: white; outline: none; width: 250px; }
      
      .chart-container-box { background: rgba(0,0,0,0.2); border: 1px solid var(--border); border-radius: 1rem; padding: 20px; margin-top: 25px; height: 350px; position: relative; }
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
        <a href="services.php" class="nav-item"><i data-lucide="wrench"></i> Service Jobs</a>
        
        <div class="nav-section-title">Management</div>
        <a href="users.php" class="nav-item"><i data-lucide="users"></i> Users</a>
        <a href="reports.php" class="nav-item active"><i data-lucide="file-bar-chart"></i> Reports</a>
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
        <h2>Report Generation Module</h2>
        <div>
          <span style="color: var(--text-muted); font-size: 0.9rem;">Interactive Data Grids & Multi-Sheet Excel Exports</span>
        </div>
      </header>

      <!-- Tabs Header -->
      <div class="tabs-header">
        <button class="tab-btn active" onclick="switchTab('sales')">
          <i data-lucide="shopping-cart"></i> Sales Reports
        </button>
        <button class="tab-btn" onclick="switchTab('inventory')">
          <i data-lucide="package"></i> Inventory Intake Reports
        </button>
        <button class="tab-btn" onclick="switchTab('services')">
          <i data-lucide="wrench"></i> Service Jobs Reports
        </button>
      </div>

      <!-- TAB 1: SALES REPORT -->
      <div id="pane-sales" class="tab-pane active">
        <div class="glass-panel" style="padding: 20px;">
          <div class="grid-controls">
            <div>
              <h3 style="font-family: 'Outfit';">Sales Transaction Data Grid</h3>
              <p style="font-size: 0.85rem; color: var(--text-muted);">Showing live sales records ready for formal export</p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
              <input type="text" class="search-box" id="search-sales" placeholder="Filter customer/item..." onkeyup="filterGrid('sales')">
              <button class="btn btn-primary" style="padding: 0.5rem 1rem; width: auto;" onclick="exportExcelReport('sales')">
                <i data-lucide="file-spreadsheet" style="width: 18px; margin-right: 8px;"></i> Export to MS Excel Template
              </button>
            </div>
          </div>

          <div class="table-container" style="max-height: 400px; overflow-y: auto;">
            <table class="modern-table" id="table-sales">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Customer Name</th>
                  <th>Product/Item</th>
                  <th>Qty</th>
                  <th>Total Price</th>
                  <th>Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($salesData as $row): ?>
                <tr class="grid-row">
                  <td>#<?php echo $row['id']; ?></td>
                  <td class="searchable"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                  <td class="searchable"><?php echo htmlspecialchars($row['product_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                  <td data-value="<?php echo $row['total_price']; ?>">$<?php echo number_format($row['total_price'], 2); ?></td>
                  <td><?php echo htmlspecialchars($row['transaction_date']); ?></td>
                  <td><span class="status-badge status-completed"><?php echo htmlspecialchars($row['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Chart Visualization inside tab -->
          <div class="chart-container-box">
            <canvas id="canvas-sales"></canvas>
          </div>
        </div>
      </div>

      <!-- TAB 2: INVENTORY REPORT -->
      <div id="pane-inventory" class="tab-pane">
        <div class="glass-panel" style="padding: 20px;">
          <div class="grid-controls">
            <div>
              <h3 style="font-family: 'Outfit';">Inventory Receipts Data Grid</h3>
              <p style="font-size: 0.85rem; color: var(--text-muted);">Intake logs from supplier shipments</p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
              <input type="text" class="search-box" id="search-inventory" placeholder="Filter supplier/product..." onkeyup="filterGrid('inventory')">
              <button class="btn btn-primary" style="padding: 0.5rem 1rem; width: auto;" onclick="exportExcelReport('inventory')">
                <i data-lucide="file-spreadsheet" style="width: 18px; margin-right: 8px;"></i> Export to MS Excel Template
              </button>
            </div>
          </div>

          <div class="table-container" style="max-height: 400px; overflow-y: auto;">
            <table class="modern-table" id="table-inventory">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Supplier / Vendor</th>
                  <th>Product Name</th>
                  <th>Qty Received</th>
                  <th>Unit Cost</th>
                  <th>Total Shipment Cost</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($invData as $row): ?>
                <tr class="grid-row">
                  <td>#REC-<?php echo $row['id']; ?></td>
                  <td class="searchable"><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                  <td class="searchable"><?php echo htmlspecialchars($row['product_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['quantity_received']); ?></td>
                  <td>$<?php echo number_format($row['cost_price'], 2); ?></td>
                  <td data-value="<?php echo $row['cost_price'] * $row['quantity_received']; ?>">$<?php echo number_format($row['cost_price'] * $row['quantity_received'], 2); ?></td>
                  <td><?php echo htmlspecialchars($row['receipt_date']); ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Chart Visualization inside tab -->
          <div class="chart-container-box">
            <canvas id="canvas-inventory"></canvas>
          </div>
        </div>
      </div>

      <!-- TAB 3: SERVICE JOBS REPORT -->
      <div id="pane-services" class="tab-pane">
        <div class="glass-panel" style="padding: 20px;">
          <div class="grid-controls">
            <div>
              <h3 style="font-family: 'Outfit';">Service & Repair Jobs Data Grid</h3>
              <p style="font-size: 0.85rem; color: var(--text-muted);">Active and completed tech support jobs</p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center;">
              <input type="text" class="search-box" id="search-services" placeholder="Filter customer/device..." onkeyup="filterGrid('services')">
              <button class="btn btn-primary" style="padding: 0.5rem 1rem; width: auto;" onclick="exportExcelReport('services')">
                <i data-lucide="file-spreadsheet" style="width: 18px; margin-right: 8px;"></i> Export to MS Excel Template
              </button>
            </div>
          </div>

          <div class="table-container" style="max-height: 400px; overflow-y: auto;">
            <table class="modern-table" id="table-services">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Customer Name</th>
                  <th>Device Model</th>
                  <th>Reported Issue</th>
                  <th>Service Fee</th>
                  <th>Date</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($srvData as $row): ?>
                <tr class="grid-row">
                  <td>#SRV-<?php echo $row['id']; ?></td>
                  <td class="searchable"><?php echo htmlspecialchars($row['customer_name']); ?></td>
                  <td class="searchable"><?php echo htmlspecialchars($row['device_model']); ?></td>
                  <td><?php echo htmlspecialchars($row['issue_description']); ?></td>
                  <td data-value="<?php echo $row['service_fee']; ?>">$<?php echo number_format($row['service_fee'], 2); ?></td>
                  <td><?php echo htmlspecialchars($row['job_date']); ?></td>
                  <td><span class="status-badge status-shipped"><?php echo htmlspecialchars($row['status']); ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Chart Visualization inside tab -->
          <div class="chart-container-box">
            <canvas id="canvas-services"></canvas>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- JavaScript Application Data & Export Engines -->
  <script>
    // Initialize standard lucide icons
    lucide.createIcons();

    // Data payload injected from server side
    const rawSalesData = <?php echo json_encode($salesData); ?>;
    const rawInvData = <?php echo json_encode($invData); ?>;
    const rawSrvData = <?php echo json_encode($srvData); ?>;
    const currentUser = <?php echo json_encode($currentUser); ?>;
    const currentRole = <?php echo json_encode($currentRole); ?>;

    // References to our initialized chart objects
    let chartSales, chartInv, chartSrv;

    // Draw high quality custom company logo dynamically to base64 string
    function getCompanyLogoBase64() {
        const canvas = document.createElement('canvas');
        canvas.width = 120;
        canvas.height = 120;
        const ctx = canvas.getContext('2d');
        
        // Dynamic vibrant background gradient
        const grad = ctx.createLinearGradient(0, 0, 120, 120);
        grad.addColorStop(0, '#6366f1');
        grad.addColorStop(1, '#ec4899');
        ctx.fillStyle = grad;
        ctx.beginPath();
        ctx.arc(60, 60, 56, 0, Math.PI * 2);
        ctx.fill();
        
        // Premium outline border
        ctx.strokeStyle = '#ffffff';
        ctx.lineWidth = 4;
        ctx.stroke();
        
        // Centered Monogram
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 44px sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('TS', 60, 60);
        
        return canvas.toDataURL('image/png');
    }

    // Initialize all custom Chart.js visual charts with animations off for high fidelity data extract
    window.addEventListener('DOMContentLoaded', () => {
        // 1. Sales Chart: Bar chart of revenue per product
        const ctxSales = document.getElementById('canvas-sales').getContext('2d');
        const salesProdMap = {};
        rawSalesData.forEach(r => {
            salesProdMap[r.product_name] = (salesProdMap[r.product_name] || 0) + parseFloat(r.total_price);
        });
        chartSales = new Chart(ctxSales, {
            type: 'bar',
            data: {
                labels: Object.keys(salesProdMap),
                datasets: [{
                    label: 'Total Generated Revenue ($)',
                    data: Object.values(salesProdMap),
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                    borderColor: '#6366f1',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false, // Ensures image conversion extracts the fully rendered state immediately
                plugins: {
                    legend: { labels: { color: '#f8fafc' } },
                    title: { display: true, text: 'Visualized Sales Revenue Distribution', color: '#94a3b8' }
                },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });

        // 2. Inventory Chart: Doughnut chart of costs per supplier
        const ctxInv = document.getElementById('canvas-inventory').getContext('2d');
        const invSupMap = {};
        rawInvData.forEach(r => {
            const cost = parseFloat(r.cost_price) * parseInt(r.quantity_received);
            invSupMap[r.supplier_name] = (invSupMap[r.supplier_name] || 0) + cost;
        });
        chartInv = new Chart(ctxInv, {
            type: 'doughnut',
            data: {
                labels: Object.keys(invSupMap),
                datasets: [{
                    data: Object.values(invSupMap),
                    backgroundColor: ['#6366f1', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { position: 'right', labels: { color: '#f8fafc' } },
                    title: { display: true, text: 'Intake Shipment Cost by Vendor Supplier', color: '#94a3b8' }
                }
            }
        });

        // 3. Service Chart: Bar chart of service fees by device model
        const ctxSrv = document.getElementById('canvas-services').getContext('2d');
        const srvDevMap = {};
        rawSrvData.forEach(r => {
            srvDevMap[r.device_model] = (srvDevMap[r.device_model] || 0) + parseFloat(r.service_fee);
        });
        chartSrv = new Chart(ctxSrv, {
            type: 'bar',
            data: {
                labels: Object.keys(srvDevMap),
                datasets: [{
                    label: 'Service & Support Fees ($)',
                    data: Object.values(srvDevMap),
                    backgroundColor: 'rgba(236, 72, 153, 0.8)',
                    borderColor: '#ec4899',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: false,
                plugins: {
                    legend: { labels: { color: '#f8fafc' } },
                    title: { display: true, text: 'Service Fees Accumulated across Device Models', color: '#94a3b8' }
                },
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } }
                }
            }
        });
    });

    // Tab view switcher logic
    function switchTab(type) {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        
        // Find triggering button based on inline click call
        const btns = document.querySelectorAll('.tab-btn');
        if (type === 'sales') btns[0].classList.add('active');
        if (type === 'inventory') btns[1].classList.add('active');
        if (type === 'services') btns[2].classList.add('active');
        
        document.getElementById(`pane-${type}`).classList.add('active');
    }

    // Dynamic front-end grid text filter
    function filterGrid(type) {
        const query = document.getElementById(`search-${type}`).value.toLowerCase();
        const rows = document.querySelectorAll(`#table-${type} tbody tr.grid-row`);
        rows.forEach(row => {
            const textContent = Array.from(row.querySelectorAll('.searchable')).map(td => td.textContent).join(' ').toLowerCase();
            row.style.display = textContent.includes(query) ? '' : 'none';
        });
    }

    // Advanced Export Module: Builds real .xlsx with Sheet 1 (Header/Logo/Data/Signature) and Sheet 2 (Graph)
    async function exportExcelReport(type) {
        // Instantiate new ExcelJS workbook
        const workbook = new ExcelJS.Workbook();
        workbook.creator = currentUser;
        workbook.created = new Date();

        // Worksheet 1: Record List Template
        const sheet1 = workbook.addWorksheet('Data Report');
        // Worksheet 2: Visual Chart Sheet
        const sheet2 = workbook.addWorksheet('Data Visualization');

        // Configure Sheet 1 Columns & Layout formatting
        sheet1.views = [{ showGridLines: true }];
        
        // Grab dynamic base64 logo string and inject into workbook media dictionary
        const logoBase64 = getCompanyLogoBase64();
        const logoId = workbook.addImage({
            base64: logoBase64,
            extension: 'png',
        });
        
        // Place logo on top left cell
        sheet1.addImage(logoId, {
            tl: { col: 0, row: 0 },
            ext: { width: 55, height: 55 }
        });

        // Set row padding for logo rendering
        sheet1.getRow(1).height = 35;
        sheet1.getRow(2).height = 25;
        sheet1.getRow(3).height = 15;

        // Populate beautiful corporate report headers next to logo space
        sheet1.mergeCells('B1:F1');
        const titleCell = sheet1.getCell('B1');
        titleCell.value = 'TECHSTORE INFORMATION SYSTEM';
        titleCell.font = { name: 'Segoe UI', size: 18, bold: true, color: { argb: 'FF6366F1' } };
        titleCell.alignment = { vertical: 'middle', horizontal: 'left' };

        sheet1.mergeCells('B2:F2');
        const subTitleCell = sheet1.getCell('B2');
        let reportTitleName = "";
        if (type === 'sales') reportTitleName = "Sales Transactions Master Ledger";
        if (type === 'inventory') reportTitleName = "Inventory Vendor Shipment Intake Records";
        if (type === 'services') reportTitleName = "Service & Repair Operations Log";
        
        subTitleCell.value = `${reportTitleName} — Exported on ${new Date().toLocaleDateString()}`;
        subTitleCell.font = { name: 'Segoe UI', size: 11, italic: true, color: { argb: 'FF555555' } };
        subTitleCell.alignment = { vertical: 'middle', horizontal: 'left' };

        // Determine specific columns based on target transaction type
        let columnsConfig = [];
        let dataRows = [];
        let targetChart = null;

        if (type === 'sales') {
            columnsConfig = [
                { header: 'Trans ID', key: 'id', width: 12 },
                { header: 'Customer Name', key: 'cust', width: 25 },
                { header: 'Product Description', key: 'prod', width: 30 },
                { header: 'Quantity Sold', key: 'qty', width: 15 },
                { header: 'Total Price ($)', key: 'price', width: 18 },
                { header: 'Transaction Date', key: 'date', width: 18 },
                { header: 'Record Status', key: 'status', width: 15 }
            ];
            rawSalesData.forEach(r => {
                dataRows.push({
                    id: `#${r.id}`, cust: r.customer_name, prod: r.product_name,
                    qty: parseInt(r.quantity), price: parseFloat(r.total_price),
                    date: r.transaction_date, status: r.status
                });
            });
            targetChart = chartSales;
        } else if (type === 'inventory') {
            columnsConfig = [
                { header: 'Receipt ID', key: 'id', width: 15 },
                { header: 'Supplier Vendor', key: 'sup', width: 25 },
                { header: 'Product Item Name', key: 'prod', width: 30 },
                { header: 'Qty Received', key: 'qty', width: 15 },
                { header: 'Unit Cost ($)', key: 'cost', width: 15 },
                { header: 'Total Cost ($)', key: 'total', width: 18 },
                { header: 'Date Received', key: 'date', width: 18 }
            ];
            rawInvData.forEach(r => {
                const total = parseFloat(r.cost_price) * parseInt(r.quantity_received);
                dataRows.push({
                    id: `#REC-${r.id}`, sup: r.supplier_name, prod: r.product_name,
                    qty: parseInt(r.quantity_received), cost: parseFloat(r.cost_price),
                    total: total, date: r.receipt_date
                });
            });
            targetChart = chartInv;
        } else if (type === 'services') {
            columnsConfig = [
                { header: 'Ticket ID', key: 'id', width: 15 },
                { header: 'Customer Client', key: 'cust', width: 25 },
                { header: 'Device Model', key: 'dev', width: 25 },
                { header: 'Reported Issue Summary', key: 'issue', width: 35 },
                { header: 'Service Fee ($)', key: 'fee', width: 18 },
                { header: 'Job Date', key: 'date', width: 18 },
                { header: 'Status', key: 'status', width: 15 }
            ];
            rawSrvData.forEach(r => {
                dataRows.push({
                    id: `#SRV-${r.id}`, cust: r.customer_name, dev: r.device_model,
                    issue: r.issue_description, fee: parseFloat(r.service_fee),
                    date: r.job_date, status: r.status
                });
            });
            targetChart = chartSrv;
        }

        // Apply column maps starting exactly at Table Header Row (Row 5)
        sheet1.columns = columnsConfig;
        
        // Shift columns to format headers exactly at row 5
        const headerRow = sheet1.getRow(5);
        headerRow.values = columnsConfig.map(c => c.header);
        headerRow.font = { name: 'Segoe UI', bold: true, color: { argb: 'FFFFFFFF' } };
        headerRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F172A' } };
        headerRow.alignment = { vertical: 'middle', horizontal: 'center' };
        headerRow.height = 25;

        // Iterate data array and insert rows
        let currentRowNum = 6;
        dataRows.forEach(rowObj => {
            const dataRow = sheet1.getRow(currentRowNum);
            dataRow.values = columnsConfig.map(c => rowObj[c.key]);
            dataRow.font = { name: 'Segoe UI', size: 10 };
            dataRow.alignment = { vertical: 'middle', horizontal: 'left' };
            
            // Format numeric prices properly
            columnsConfig.forEach((col, idx) => {
                if (col.key === 'price' || col.key === 'cost' || col.key === 'total' || col.key === 'fee') {
                    dataRow.getCell(idx + 1).numFmt = '$#,##0.00';
                    dataRow.getCell(idx + 1).alignment = { horizontal: 'right' };
                } else if (col.key === 'qty') {
                    dataRow.getCell(idx + 1).alignment = { horizontal: 'center' };
                }
            });
            
            // Alternating striped backgrounds for premium layout
            if (currentRowNum % 2 === 1) {
                dataRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } };
            }
            currentRowNum++;
        });

        // Add optional totals row line
        const totalRow = sheet1.getRow(currentRowNum);
        totalRow.font = { name: 'Segoe UI', bold: true };
        totalRow.getCell(1).value = "TOTALS";
        
        // Sum calculations
        let sumColIdx = -1;
        let sumValue = 0;
        if (type === 'sales') { sumColIdx = 5; dataRows.forEach(r => sumValue += r.price); }
        if (type === 'inventory') { sumColIdx = 6; dataRows.forEach(r => sumValue += r.total); }
        if (type === 'services') { sumColIdx = 5; dataRows.forEach(r => sumValue += r.fee); }
        
        if (sumColIdx !== -1) {
            totalRow.getCell(sumColIdx).value = sumValue;
            totalRow.getCell(sumColIdx).numFmt = '$#,##0.00';
            totalRow.getCell(sumColIdx).alignment = { horizontal: 'right' };
        }
        
        // Add upper line frame for the totals row
        totalRow.eachCell(cell => {
            cell.border = { top: { style: 'thin', color: { argb: 'FFCCCCCC' } }, bottom: { style: 'double', color: { argb: 'FF333333' } } };
        });

        // ==========================================
        // SIGNATURE PLACEHOLDER BLOCK (User Signature requirement)
        // ==========================================
        const sigStartRow = currentRowNum + 3;
        
        sheet1.getCell(`B${sigStartRow}`).value = 'Generated / Prepared By:';
        sheet1.getCell(`B${sigStartRow}`).font = { name: 'Segoe UI', bold: true, color: { argb: 'FF6366F1' } };
        sheet1.getCell(`C${sigStartRow}`).value = `${currentUser} — ${currentRole}`;
        sheet1.getCell(`C${sigStartRow}`).font = { name: 'Segoe UI', bold: true };

        sheet1.getCell(`B${sigStartRow + 2}`).value = 'Authorized Signature Placeholder:';
        sheet1.getCell(`B${sigStartRow + 2}`).font = { name: 'Segoe UI', bold: true, color: { argb: 'FF6366F1' } };
        sheet1.getCell(`C${sigStartRow + 2}`).value = '__________________________________________________';
        sheet1.getCell(`C${sigStartRow + 2}`).font = { color: { argb: 'FF888888' } };

        sheet1.getCell(`B${sigStartRow + 3}`).value = 'Date Approved / Signed:';
        sheet1.getCell(`B${sigStartRow + 3}`).font = { name: 'Segoe UI', bold: true, color: { argb: 'FF6366F1' } };
        sheet1.getCell(`C${sigStartRow + 3}`).value = '______ / ______ / 2026';
        sheet1.getCell(`C${sigStartRow + 3}`).font = { color: { argb: 'FF888888' } };


        // ==========================================
        // SHEET 2: EMBED GRAPHICAL VISUALIZATION IMAGE
        // ==========================================
        sheet2.views = [{ showGridLines: true }];
        sheet2.columns = [{ width: 5 }, { width: 100 }];
        
        sheet2.getCell('B2').value = `${reportTitleName} — Extracted Graphical Analytics`;
        sheet2.getCell('B2').font = { name: 'Segoe UI', size: 14, bold: true, color: { argb: 'FFEC4899' } };
        
        if (targetChart) {
            // Retrieve image payload from canvas
            const base64Chart = targetChart.toBase64Image();
            const graphImageId = workbook.addImage({
                base64: base64Chart,
                extension: 'png',
            });
            
            // Render high definition graph starting below label placeholder
            sheet2.addImage(graphImageId, {
                tl: { col: 1, row: 3 },
                ext: { width: 750, height: 420 }
            });
        }

        // Trigger native download output pipeline
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        
        const downloadLink = document.createElement('a');
        downloadLink.href = URL.createObjectURL(blob);
        downloadLink.download = `${type}_report_export_${Date.now()}.xlsx`;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }
  </script>
</body>
</html>
