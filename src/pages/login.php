<?php
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $user  = login($email, $pass);
    if ($user) {
        header('Location: ' . BASE_PATH . '/');
        exit;
    }
    $error = 'Invalid email or password.';
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Login — <?= APP_NAME ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= BASE_PATH ?>/assets/css/style.css">
</head>
<body class="login-wrap">
<div class="login-card">
  <div class="card shadow-lg">
    <div class="card-body p-4">

      <div class="text-center mb-4">
        <i class="bi bi-telephone-fill login-brand-icon mb-2 d-block"></i>
        <h4 class="mb-0 fw-bold"><?= APP_NAME ?></h4>
        <p class="text-muted small mb-0">PBX Call Center Dashboard</p>
      </div>

      <?php if ($error): ?>
      <div class="alert alert-danger py-2 small"><?= h($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_PATH ?>/login">
        <div class="mb-3">
          <label for="email" class="form-label">Email Address</label>
          <input type="email" id="email" name="email" class="form-control"
                 placeholder="you@company.com"
                 value="<?= h($_POST['email'] ?? '') ?>" required autofocus>
        </div>
        <div class="mb-4">
          <label for="password" class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control"
                 placeholder="Your password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">
          <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
        </button>
      </form>

    </div>
  </div>
  <p class="text-center text-muted small mt-3">Nigeria ISP PBX Management v<?= APP_VERSION ?></p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
