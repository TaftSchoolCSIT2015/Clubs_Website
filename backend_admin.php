<?php session_start(); ?>
<?php
    $BACKEND_ADMIN_USERNAME = "skoshi";
    if(!isset($_SESSION['user'])) {
        header("Location: index.php");
        exit();
    }
    $username = $_SESSION['user'];
    if($username != $BACKEND_ADMIN_USERNAME) {
        header("Location: index.php");
        exit();
    }
?>
