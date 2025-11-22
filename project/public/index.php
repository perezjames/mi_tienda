<?php
require_once '../inc/auth.php';
require_once '../inc/helpers.php';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
} else {
    redirect('login.php');
}
?>