<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'Active') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Account is inactive. Please contact administrator.";
            }
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please enter both username and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechStore IS - Login</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
      .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
      .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
  </style>
</head>
<body>
  <div class="auth-wrapper">
    <div class="glass-panel auth-card">
      <div class="auth-header">
        <h1>TechStore IS</h1>
        <p>Welcome back! Please login to your account.</p>
      </div>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <form action="index.php" method="POST">
        <div class="form-group">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" class="form-control" placeholder="Enter 'admin'" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="form-group flex-between text-sm">
          <label style="display: flex; align-items: center; gap: 0.5rem; margin: 0; font-weight: 400; cursor: pointer;">
            <input type="checkbox"> Remember me
          </label>
          <a href="forgot-password.php">Forgot password?</a>
        </div>
        <button type="submit" class="btn btn-primary">Sign In <i data-lucide="arrow-right" style="width: 18px; margin-left: 8px;"></i></button>
      </form>
    </div>
  </div>
  <script>
    lucide.createIcons();
  </script>
</body>
</html>
