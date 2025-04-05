<?php
session_start();
if (isset($_SESSION['user'])) {
    header('Location: /dashboard');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi login (hanya contoh sederhana)
    if ($username == 'admin' && $password == 'admin123') {
        $_SESSION['user'] = $username;
        header('Location: /dashboard');
        exit();
    } else {
        $error = "Invalid credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/animate.min.css">
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="icon" href="favicon.ico" type="image/x-icon">
</head>
<body class="login-page">
  <div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card p-4 shadow animate__animated animate__fadeIn" style="width: 100%; max-width: 400px;">
      <div class="text-center mb-4">
        <img src="assets/img/logo.png" alt="Logo" class="logo">
        <h2 class="card-text">Login</h2>
      </div>
      <form method="POST">
        <div class="mb-3">
            <?php if (isset($error)) { echo "<p class='text-danger'>$error</p>"; } ?>

          <label for="username" class="card-text">Username</label>
          <input type="text" name="username" class="form-control" id="username" required>
        </div>
        <div class="mb-3">
          <label for="password" class="card-text">Password</label>
          <input type="password" name="password" class="form-control" id="password" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>
</html>