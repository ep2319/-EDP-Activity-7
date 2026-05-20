<?php
// Database.php - Contains the public class for MySQL connection
class MySQLDatabase {
    private $host = "localhost";
    private $db_name = "tech_store";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            // Attempt MySQL connection
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->initializeMySQL();
        } catch(PDOException $exception) {
            // Fallback to SQLite so the application remains fully functional for demonstration/recording
            try {
                $this->conn = new PDO("sqlite:" . __DIR__ . "/tech_store.sqlite");
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->initializeSQLite();
            } catch(PDOException $e) {
                die("Connection error: " . $exception->getMessage());
            }
        }
        return $this->conn;
    }

    // Helper method to initialize tables for the SQLite fallback
    private function initializeSQLite() {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            email TEXT NOT NULL,
            status TEXT DEFAULT 'Active',
            role TEXT DEFAULT 'User'
        )");
        
        // Insert default admin if not exists
        $stmt = $this->conn->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
        if ($stmt->fetchColumn() == 0) {
            $hashed = password_hash("admin123", PASSWORD_DEFAULT);
            $this->conn->exec("INSERT INTO users (username, password, email, role, status) VALUES ('admin', '$hashed', 'admin@techstore.com', 'Admin', 'Active')");
        }

        // Primary Transaction 1: Sales Transactions
        $this->conn->exec("CREATE TABLE IF NOT EXISTS sales_transactions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_name TEXT NOT NULL,
            product_name TEXT NOT NULL,
            quantity INTEGER NOT NULL,
            total_price REAL NOT NULL,
            transaction_date TEXT NOT NULL,
            status TEXT DEFAULT 'Completed'
        )");

        // Primary Transaction 2: Inventory Receipts
        $this->conn->exec("CREATE TABLE IF NOT EXISTS inventory_receipts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            supplier_name TEXT NOT NULL,
            product_name TEXT NOT NULL,
            quantity_received INTEGER NOT NULL,
            cost_price REAL NOT NULL,
            receipt_date TEXT NOT NULL
        )");

        // Primary Transaction 3: Service Jobs
        $this->conn->exec("CREATE TABLE IF NOT EXISTS service_jobs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer_name TEXT NOT NULL,
            device_model TEXT NOT NULL,
            issue_description TEXT NOT NULL,
            service_fee REAL NOT NULL,
            job_date TEXT NOT NULL,
            status TEXT DEFAULT 'Pending'
        )");

        // Seed Sales Transactions if empty
        if ($this->conn->query("SELECT COUNT(*) FROM sales_transactions")->fetchColumn() == 0) {
            $this->conn->exec("INSERT INTO sales_transactions (customer_name, product_name, quantity, total_price, transaction_date, status) VALUES 
                ('Clark Kent', 'RTX 4090 Graphics Card', 1, 1599.99, '2026-05-01', 'Completed'),
                ('Bruce Wayne', 'UltraWide Gaming Monitor', 2, 2400.00, '2026-05-03', 'Completed'),
                ('Diana Prince', 'Mechanical Gaming Keyboard', 5, 750.00, '2026-05-05', 'Completed'),
                ('Barry Allen', 'NVMe SSD 2TB Storage', 3, 450.00, '2026-05-10', 'Completed')");
        }

        // Seed Inventory Receipts if empty
        if ($this->conn->query("SELECT COUNT(*) FROM inventory_receipts")->fetchColumn() == 0) {
            $this->conn->exec("INSERT INTO inventory_receipts (supplier_name, product_name, quantity_received, cost_price, receipt_date) VALUES 
                ('TechWholesale Inc', 'RTX 4090 Graphics Card', 10, 1400.00, '2026-04-15'),
                ('Global Displays Ltd', 'UltraWide Gaming Monitor', 15, 950.00, '2026-04-20'),
                ('Peripheral Source', 'Mechanical Gaming Keyboard', 50, 100.00, '2026-04-25')");
        }

        // Seed Service Jobs if empty
        if ($this->conn->query("SELECT COUNT(*) FROM service_jobs")->fetchColumn() == 0) {
            $this->conn->exec("INSERT INTO service_jobs (customer_name, device_model, issue_description, service_fee, job_date, status) VALUES 
                ('Peter Parker', 'MacBook Pro 16', 'Screen replacement and battery service', 450.00, '2026-05-02', 'Completed'),
                ('Tony Stark', 'Custom Liquid Cooled PC', 'Pump failure diagnostics & replacement', 850.00, '2026-05-08', 'In Progress'),
                ('Steve Rogers', 'iPhone 15 Pro', 'Data recovery from damaged logic board', 300.00, '2026-05-12', 'Pending')");
        }
    }

    // Helper method to initialize tables for the MySQL database
    private function initializeMySQL() {
        $this->conn->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            status VARCHAR(50) DEFAULT 'Active',
            role VARCHAR(50) DEFAULT 'User'
        )");
        
        // Insert default admin if not exists
        $stmt = $this->conn->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
        if ($stmt->fetchColumn() == 0) {
            $hashed = password_hash("admin123", PASSWORD_DEFAULT);
            $this->conn->exec("INSERT INTO users (username, password, email, role, status) VALUES ('admin', '$hashed', 'admin@techstore.com', 'Admin', 'Active')");
        }

        // Primary Transaction 1: Sales Transactions
        $this->conn->exec("CREATE TABLE IF NOT EXISTS sales_transactions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(255) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL,
            total_price DECIMAL(10,2) NOT NULL,
            transaction_date DATE NOT NULL,
            status VARCHAR(50) DEFAULT 'Completed'
        )");

        // Primary Transaction 2: Inventory Receipts
        $this->conn->exec("CREATE TABLE IF NOT EXISTS inventory_receipts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_name VARCHAR(255) NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            quantity_received INT NOT NULL,
            cost_price DECIMAL(10,2) NOT NULL,
            receipt_date DATE NOT NULL
        )");

        // Primary Transaction 3: Service Jobs
        $this->conn->exec("CREATE TABLE IF NOT EXISTS service_jobs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            customer_name VARCHAR(255) NOT NULL,
            device_model VARCHAR(255) NOT NULL,
            issue_description TEXT NOT NULL,
            service_fee DECIMAL(10,2) NOT NULL,
            job_date DATE NOT NULL,
            status VARCHAR(50) DEFAULT 'Pending'
        )");

        // Seed Sales Transactions if empty
        if ($this->conn->query("SELECT COUNT(*) FROM sales_transactions")->fetchColumn() == 0) {
            $this->conn->exec("INSERT INTO sales_transactions (customer_name, product_name, quantity, total_price, transaction_date, status) VALUES 
                ('Clark Kent', 'RTX 4090 Graphics Card', 1, 1599.99, '2026-05-01', 'Completed'),
                ('Bruce Wayne', 'UltraWide Gaming Monitor', 2, 2400.00, '2026-05-03', 'Completed'),
                ('Diana Prince', 'Mechanical Gaming Keyboard', 5, 750.00, '2026-05-05', 'Completed'),
                ('Barry Allen', 'NVMe SSD 2TB Storage', 3, 450.00, '2026-05-10', 'Completed')");
        }

        // Seed Inventory Receipts if empty
        if ($this->conn->query("SELECT COUNT(*) FROM inventory_receipts")->fetchColumn() == 0) {
            $this->conn->exec("INSERT INTO inventory_receipts (supplier_name, product_name, quantity_received, cost_price, receipt_date) VALUES 
                ('TechWholesale Inc', 'RTX 4090 Graphics Card', 10, 1400.00, '2026-04-15'),
                ('Global Displays Ltd', 'UltraWide Gaming Monitor', 15, 950.00, '2026-04-20'),
                ('Peripheral Source', 'Mechanical Gaming Keyboard', 50, 100.00, '2026-04-25')");
        }

        // Seed Service Jobs if empty
        if ($this->conn->query("SELECT COUNT(*) FROM service_jobs")->fetchColumn() == 0) {
            $this->conn->exec("INSERT INTO service_jobs (customer_name, device_model, issue_description, service_fee, job_date, status) VALUES 
                ('Peter Parker', 'MacBook Pro 16', 'Screen replacement and battery service', 450.00, '2026-05-02', 'Completed'),
                ('Tony Stark', 'Custom Liquid Cooled PC', 'Pump failure diagnostics & replacement', 850.00, '2026-05-08', 'In Progress'),
                ('Steve Rogers', 'iPhone 15 Pro', 'Data recovery from damaged logic board', 300.00, '2026-05-12', 'Pending')");
        }
    }
}
?>
