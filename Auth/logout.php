<?php
session_start();

$tujuanRedirect = '../index.php'; 

if (isset($_SESSION['admin_login'])) {
    $tujuanRedirect = 'login.php'; 
} 
elseif (isset($_SESSION['user_login']) || isset($_SESSION['user_id'])) {
    $tujuanRedirect = 'user_login.php';
}
session_unset();
session_destroy();
header("Location: " . $tujuanRedirect);
exit;
?>