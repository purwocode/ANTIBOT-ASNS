<?php

// Fungsi untuk mendapatkan IP pengunjung
function get_client_ip() {
    $ip_sources = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'HTTP_CF_CONNECTING_IP',
        'REMOTE_ADDR'
    ];

    foreach ($ip_sources as $source) {
        if (!empty($_SERVER[$source])) {
            if ($source === 'HTTP_X_FORWARDED_FOR') {
                $ip_list = explode(',', $_SERVER[$source]);
                return trim($ip_list[0]);
            }
            return $_SERVER[$source];
        }
    }
    return 'IP TIDAK DITEMUKAN';
}

// Fungsi untuk menambahkan IP ke .htaccess jika terdeteksi sebagai diblokir
function addToHtaccess($ip) {
    $htaccessFile = '.htaccess';
    $denyRule = "Deny from $ip";
    
    if (file_exists($htaccessFile)) {
        $content = file_get_contents($htaccessFile);
        if (strpos($content, $denyRule) === false) {
            file_put_contents($htaccessFile, "\n$denyRule", FILE_APPEND);
        }
    } else {
        file_put_contents($htaccessFile, "Order Allow,Deny\nAllow from all\n$denyRule\n");
    }
}

// Fungsi untuk mengambil data dari API
function fetch_api_response($url, $headers) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        die('Error: ' . curl_error($ch));
    }
    curl_close($ch);
    return $response;
}

// Fungsi untuk menyimpan log visitor berdasarkan shortcode
function saveVisitorLog($shortcode, $ip, $isp, $visitor_type) {
    $log_entry = date('Y-m-d H:i:s') . " - Shortcode: $shortcode - IP: $ip - ISP: $isp - VISITOR: $visitor_type\n";
    file_put_contents('data/visitor_logs.txt', $log_entry, FILE_APPEND);
}

// Dapatkan IP pengunjung
$ip_address = get_client_ip();

// Dapatkan shortcode dari parameter URL
$shortcode = isset($_GET['code']) ? trim($_GET['code']) : '';

if (empty($shortcode)) {
    die("Shortcode tidak ditemukan!");
}

// URL API untuk pengecekan IP
$api_url = "https://botak.vercel.app/api/ip-checker?ip=$ip_address";
$headers = [
    'accept: application/json',
    'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36'
];

// Ambil respons dari API
$response = fetch_api_response($api_url, $headers);
$response_data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Gagal decode JSON: " . json_last_error_msg());
}

// Ambil informasi ISP dari API (default ke "UNKNOWN" jika tidak tersedia)
$isp = isset($response_data['userIsp']) ? $response_data['userIsp'] : 'UNKNOWN';

// Tentukan apakah visitor adalah manusia atau bot
$visitor_type = (isset($response_data['isBlocked']) && $response_data['isBlocked'] === true) ? 'BOT' : 'HUMAN';

// Simpan kunjungan visitor ke file log dengan ISP dan status visitor
saveVisitorLog($shortcode, $ip_address, $isp, $visitor_type);

// Periksa apakah status "isBlocked" ada di dalam respons dan bernilai true
if (isset($response_data['isBlocked']) && $response_data['isBlocked'] === true) {
    // Tambahkan IP ke .htaccess
    addToHtaccess($ip_address);
    
    // Redirect ke Bing jika IP diblokir
    header('Location: https://www.bing.com');
    exit();
}

// Fungsi untuk mengambil URL dari shortcode di file shortlink.txt
function load_shortlink_url($file_path, $shortcode) {
    if (!file_exists($file_path)) {
        die("File shortlink.txt tidak ditemukan!");
    }
    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $parts = explode('|', $line);
        if (count($parts) == 2 && trim($parts[0]) === $shortcode) {
            return trim($parts[1]); // Ambil URL dari kolom kedua
        }
    }
    return false;
}

// Redirect ke URL asli jika ditemukan di shortlink.txt
$shortlink_url = load_shortlink_url('data/shortlink.txt', $shortcode);
if ($shortlink_url) {
    header("Location: $shortlink_url");
    exit();
} else {
    die("URL tidak ditemukan dalam shortlink.txt!");
}

?>
