<?php
session_start();
session_destroy(); // Menghancurkan sesi
header('Location: /login');
exit();
?>
