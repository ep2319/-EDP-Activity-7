<?php
require_once 'config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if ($email) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // In a real application, you'd send an email with a secure token.
            // For this demonstration, we'll reset it to a default password.
            $newPassword = "password123";
            $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $updateStmt->execute([$hashed, $user['id']]);
            
            $message = "Password has been reset to: <strong>$newPassword</strong>. Please login and change it.";
        } else {
            $error = "No account found with that email address.";
        }
    } else {
        $error = "Please enter a valid email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TechStore IS - Recover Password</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <script src="https://unpkg.com/lucide@latest"></script>
  <style>
      .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px; }
      .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
      .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
  </style>
</head>
<body>
  <div class="auth-wrapper">
    <div class="glass-panel auth-card">
      <div class="auth-header">
        <h1>Recover Access</h1>
        <p>Enter your email to receive recovery instructions.</p>
      </div>
      
      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if ($message): ?>
        <div class="alert alert-success"><?php echo $message; ?></div>
      <?php endif; ?>

      <form action="forgot-password.php" method="POST">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="admin@techstore.com" required>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-bottom: 1rem;">Reset Password <i data-lucide="key" style="width: 18px; margin-left: 8px;"></i></button>
        <div class="text-center text-sm" style="text-align: center;">
          <a href="index.php" style="display: inline-flex; align-items: center; gap: 0.25rem;"><i data-lucide="arrow-left" style="width: 14px;"></i> Back to Login</a>
        </div>
      </form>
    </div>
  </div>
  <script>
    lucide.createIcons();
  </script>
</body>
</html>
