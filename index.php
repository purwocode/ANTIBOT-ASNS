<?php
session_start();

// Ambil slug dari URL
$slug = isset($_GET['slug']) ? $_GET['slug'] : null;

// Daftar halaman yang diizinkan
$allowed_pages = ['dashboard', 'login', 'profile']; // tambah sesuai kebutuhan

if ($slug && in_array($slug, $allowed_pages)) {
    $file = $slug . '.php';

    if (file_exists($file)) {
        include $file;
        exit();
    } else {
        http_response_code(404);
        echo "Halaman tidak ditemukan.";
        exit();
    }
}

// Jika tidak ada slug, redirect default
if (isset($_SESSION['user'])) {
    header('Location: /dashboard');
    exit();
} else {
    header('Location: /login');
    exit();
}
