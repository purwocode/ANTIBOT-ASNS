<?php
include('includes/functions.php');
check_login();  // Mengecek apakah user sudah login

// Fungsi untuk menghasilkan shortcode random alfanumerik 6 karakter
function generate_shortcode($length = 6) {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
}

// Menangani form create shortlink
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_link'])) {
    $url = $_POST['url'];
    $shortcode = generate_shortcode();
    save_shortlink($url, $shortcode);
    $message = "Shortlink created: $current_domain/$shortcode";
}

// Menangani update shortlink
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_link'])) {
    $shortcode = trim($_POST['shortcode']);
    $new_url = trim($_POST['new_url']);

    if (!empty($shortcode) && !empty($new_url)) {
        update_shortlink($shortcode, $new_url);
        $message = "Shortlink updated successfully.";
    } else {
        $error = "Shortcode dan URL harus diisi!";
    }
}

// Menangani delete shortlink
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_link'])) {
    $shortcode = trim($_POST['shortcode']);

    if (!empty($shortcode)) {
        delete_shortlink($shortcode);
        $message = "Shortlink deleted successfully.";
    } else {
        $error = "Shortcode tidak boleh kosong!";
    }
}

// Ambil semua shortlink
$shortlinks = get_shortlinks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AntiBot</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/animate.min.css">
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="icon" href="assets/img/favicon.ico" type="image/x-icon">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark-green">
    <div class="container-fluid">
      <a class="navbar-brand" href="/">
        <img src="assets/img/logo.png" alt="Logo" class="logo">
        Eka Syahwat Koncol
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item">
            <a class="nav-link" href="logout.php" id="logout">Logout</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

 <div class="container mt-5">
    <?php if (isset($message)): ?>
      <div class="alert alert-success"> <?php echo htmlspecialchars($message); ?> </div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
      <div class="alert alert-danger"> <?php echo htmlspecialchars($error); ?> </div>
    <?php endif; ?>

    <!-- Create Short Section -->
    <div class="card mb-4 shadow animate__animated animate__fadeInLeft">
      <div class="card-body">
        <h5 class="card-title">Create Short</h5>
        <form method="POST" action="">
          <div class="mb-3">
            <label for="urlInput" class="card-text">Enter URL</label>
            <input type="url" class="form-control" id="urlInput" name="url" required>
          </div>
          <button type="submit" name="create_link" class="btn btn-success">Create Short URL</button>
        </form>
      </div>
    </div>

    <!-- List Short Count Section -->
    <div class="card mb-4 shadow animate__animated animate__fadeInRight">
      <div class="card-body">
        <h5 class="card-title">List Short Count</h5>
        <p class="card-text">Total Short URLs: <span id="shortCount"><?php echo count($shortlinks); ?></span></p>

        <?php if (!empty($shortlinks)): ?>
          <h6 class="card-text mt-3">Original Links & Short Codes</h6>
          <ul class="list-group">
            <?php foreach ($shortlinks as $link): ?>
              <?php list($shortcode, $url) = explode('|', $link); ?>
              <li class="list-group-item">
                <strong>Original:</strong> <?php echo htmlspecialchars($url); ?> |
                <strong>Short:</strong> <?php echo $current_domain; ?>/<?php echo htmlspecialchars($shortcode); ?>

<!-- Form Update -->
<form method="POST" action="" class="mt-2">
  <input type="hidden" name="shortcode" value="<?php echo htmlspecialchars($shortcode); ?>">
  <div class="mb-2">
    <label for="new_url_<?php echo $shortcode; ?>" class="form-label">Update URL:</label>
    <input type="url" id="new_url_<?php echo $shortcode; ?>" class="form-control" name="new_url" placeholder="Enter new URL" required>
  </div>
  <button type="submit" name="update_link" class="btn btn-primary btn-sm">Update</button>
</form>

<!-- Tombol Track & Delete -->
<div class="mt-2 d-flex gap-2">
  <a href="view/<?php echo urlencode($shortcode); ?>" class="btn btn-info btn-sm">Track Visitor</a>
  
  <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this shortlink?')">
    <input type="hidden" name="shortcode" value="<?php echo htmlspecialchars($shortcode); ?>">
    <button type="submit" name="delete_link" class="btn btn-danger btn-sm">Delete</button>
  </form>
</div>

              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="card-text">No short links available.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="script.js"></script>
</body>
</html>
