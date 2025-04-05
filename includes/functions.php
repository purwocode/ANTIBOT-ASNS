<?php
// Fungsi untuk memeriksa login
function check_login() {
    session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit();
    }
}

// Menyimpan shortlink
function save_shortlink($url, $shortcode) {
    file_put_contents('data/shortlink.txt', "$shortcode|$url" . PHP_EOL, FILE_APPEND);
}

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$current_domain = $protocol . $domain;

// Mengambil semua shortlink
function get_shortlinks() {
    $file = 'data/shortlink.txt';
    if (!file_exists($file)) {
        return [];
    }
    return file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

// Fungsi untuk update shortlink
function update_shortlink($shortcode, $new_url) {
    $shortlinks = get_shortlinks();
    $updated = false;
    foreach ($shortlinks as &$link) {
        list($code, $url) = explode('|', $link);
        if ($code === $shortcode) {
            $link = "$shortcode|$new_url";
            $updated = true;
            break;
        }
    }
    if ($updated) {
        file_put_contents('data/shortlink.txt', implode("\n", $shortlinks));
    }
}

// Fungsi untuk menghapus shortlink
function delete_shortlink($shortcode) {
    $shortlinks = get_shortlinks();
    $remaining = array_filter($shortlinks, function($link) use ($shortcode) {
        list($code, $url) = explode('|', $link);
        return $code !== $shortcode;
    });
    file_put_contents('data/shortlink.txt', implode("\n", $remaining));
}

// Fungsi untuk menambah IP blokir
function add_blocked_ip($ip) {
    file_put_contents('data/blocked_ips.txt', "$ip\n", FILE_APPEND);
}
?>
