<?php
include('includes/functions.php');
check_login(); // Mengecek apakah user sudah login

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

// Fungsi pembacaan log
function get_logs_for_shortcode($shortcode, $log_file) {
    $logs = [];
    $total_visitors = 0;
    $total_humans = 0;
    $total_bots = 0;

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

    return [$logs, $total_visitors, $total_humans, $total_bots];
}

// Handle permintaan AJAX untuk realtime
if (isset($_GET['ajax']) && $_GET['ajax'] == '1' && !empty($_GET['shortcode'])) {
    [$logs, $total_visitors, $total_humans, $total_bots] = get_logs_for_shortcode($_GET['shortcode'], $log_file);

    $data = [
        'html' => '',
        'total' => $total_visitors,
        'humans' => $total_humans,
        'bots' => $total_bots
    ];

    foreach (array_reverse($logs) as $log) {
        $data['html'] .= "<tr>
                            <td>{$log[1]}</td>
                            <td>{$log[3]}</td>
                            <td>{$log[4]}</td>
                            <td>{$log[5]}</td>
                          </tr>";
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Untuk tampilan awal
[$logs, $total_visitors, $total_humans, $total_bots] = get_logs_for_shortcode($shortcode, $log_file);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Track Visitor - <?php echo htmlspecialchars($shortcode); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
  <div class="container mt-5">
    <h2>Track Visitors for Shortcode: <?php echo htmlspecialchars($shortcode); ?></h2>
    <p id="visitorCount">Total Visitors: <?php echo $total_visitors; ?> | Humans: <?php echo $total_humans; ?> | Bots: <?php echo $total_bots; ?></p>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>Waktu Kunjungan</th>
          <th>IP Address</th>
          <th>ISP</th>
          <th>Visitor Type</th>
        </tr>
      </thead>
      <tbody id="visitorTableBody">
        <?php
          if (!empty($logs)) {
            foreach (array_reverse($logs) as $log) {
              echo "<tr>
                      <td>{$log[1]}</td>
                      <td>{$log[3]}</td>
                      <td>{$log[4]}</td>
                      <td>{$log[5]}</td>
                    </tr>";
            }
          } else {
            echo "<tr><td colspan='4'>No visitor logs available.</td></tr>";
          }
        ?>
      </tbody>
    </table>

    <!-- Tombol Reset -->
    <form method="post" onsubmit="return confirm('Apakah kamu yakin ingin menghapus semua log untuk shortcode ini?')">
      <input type="hidden" name="shortcode" value="<?php echo htmlspecialchars($shortcode); ?>">
      <button type="submit" name="reset" value="1" class="btn btn-danger mt-3">Reset Log</button>
      <a href="/" class="btn btn-secondary mt-3">Back to Shortlink List</a>
    </form>
  </div>

  <script>
    setInterval(() => {
      const table = document.getElementById('visitorTableBody');
      const counter = document.getElementById('visitorCount');

      fetch(`?ajax=1&shortcode=<?php echo $shortcode; ?>`)
        .then(res => res.json())
        .then(data => {
          table.innerHTML = data.html;
          counter.textContent = `Total Visitors: ${data.total} | Humans: ${data.humans} | Bots: ${data.bots}`;
        });
    }, 5000);
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
