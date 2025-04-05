<?php
include('includes/functions.php');
check_login(); // Mengecek apakah user sudah login

// Redirect hanya jika pakai query string (URL lama)
if (isset($_GET['shortcode']) && strpos($_SERVER['REQUEST_URI'], '/view/') === false) {
    $short = $_GET['shortcode'];
    $pg = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    header("Location: /view/$short/" . $pg, true, 301);
    exit;
}

// Ambil shortcode dan page dari URL path
$uri_parts = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$shortcode = isset($uri_parts[1]) ? trim($uri_parts[1]) : '';
$page = isset($uri_parts[2]) ? (int)$uri_parts[2] : 1;
if ($page < 1) $page = 1;

$log_file = 'data/visitor_logs.txt';
$logs = [];
$total_visitors = 0;
$total_humans = 0;
$total_bots = 0;

// RESET LOG jika form dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset']) && $_POST['shortcode'] === $shortcode) {
    if (file_exists($log_file)) {
        $file_logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $filtered_logs = array_filter($file_logs, function($log) use ($shortcode) {
            return strpos($log, "Shortcode: $shortcode") === false;
        });
        file_put_contents($log_file, implode("\n", $filtered_logs) . "\n");
        header("Location: /view/$shortcode");
        exit;
    }
}

// Cek apakah file log ada
if (file_exists($log_file)) {
    $file_logs = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($file_logs as $log) {
        preg_match('/(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) - Shortcode: (\S+) - IP: (\S+) - ISP: ([^ -]+(?: [^ -]+)*) - VISITOR: (\S+)/', $log, $matches);
        if ($matches && $matches[2] == $shortcode) {
            $logs[] = $matches;
            $total_visitors++;
            if ($matches[5] == 'HUMAN') {
                $total_humans++;
            } else {
                $total_bots++;
            }
        }
    }
}

// Pagination setup
$per_page = 20;
$start = ($page - 1) * $per_page;
$total_pages = ceil(count($logs) / $per_page);
$logs_to_display = array_slice($logs, $start, $per_page);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Track Visitor - <?php echo htmlspecialchars($shortcode); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h2>Track Visitors for Shortcode: <?php echo htmlspecialchars($shortcode); ?></h2>
    <p>Total Visitors: <?php echo $total_visitors; ?> | Humans: <?php echo $total_humans; ?> | Bots: <?php echo $total_bots; ?></p>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>Waktu Kunjungan</th>
          <th>IP Address</th>
          <th>ISP</th>
          <th>Visitor Type</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($logs_to_display)) {
            foreach ($logs_to_display as $log) {
                echo "<tr>
                        <td>{$log[1]}</td>
                        <td>{$log[3]}</td>
                        <td>{$log[4]}</td>
                        <td>{$log[5]}</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='4'>No visitor logs available.</td></tr>";
        } ?>
      </tbody>
    </table>

    <!-- Pagination -->
    <nav>
      <ul class="pagination">
        <?php if ($page > 1): ?>
          <li class="page-item"><a class="page-link" href="/view/<?php echo $shortcode; ?>/<?php echo $page - 1; ?>">Previous</a></li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
            <a class="page-link" href="/view/<?php echo $shortcode; ?>/<?php echo $i; ?>"><?php echo $i; ?></a>
          </li>
        <?php endfor; ?>
        
        <?php if ($page < $total_pages): ?>
          <li class="page-item"><a class="page-link" href="/view/<?php echo $shortcode; ?>/<?php echo $page + 1; ?>">Next</a></li>
        <?php endif; ?>
      </ul>
    </nav>

    <!-- Tombol Reset Log -->
    <form method="post" onsubmit="return confirm('Apakah kamu yakin ingin menghapus semua log untuk shortcode ini?')">
      <input type="hidden" name="shortcode" value="<?php echo htmlspecialchars($shortcode); ?>">
      <button type="submit" name="reset" value="1" class="btn btn-danger mt-3">Reset Log</button>
      <a href="/" class="btn btn-secondary mt-3">Back to Shortlink List</a>
    </form>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
