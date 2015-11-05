<?php session_start(); ?>
<?php
    require 'scripts/SQLUtils.php';

    $conn = getSQLConnectionFromConfig();

    $backendAdmins = array();

    $result = $conn->query("SELECT username FROM taftclubs.clubadmins");
    if($result->num_rows > 0) {
        while($data = $result->fetch_assoc()) {
            $backendAdmins[] = $data['username'];
        }
    }

    /*General Unlogged in Person*/
    if(!isset($_SESSION['user'])) {
        header("Location: index.php");
        exit();
    }
    /*authenticated person*/
    $username = $_SESSION['user'];

    if(array_search($username, $backendAdmins) === FALSE) {
        header("Location: index.php");
        exit();
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Backend Administration</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="style/common.css">
        <link rel="stylesheet" type="text/css" href="backend_admin.css">
        <script src="js/jquery-2.1.4.min.js"></script>
    </head>
    <body>
        <div class="header">
            <div class="title">
                <span>Backend Administration Page</span>
            </div>
            <div class="nav">
                <ul>
                    <li>Test</li>
                </ul>
            </div>
        </div>
        <script type="text/javascript" src="js/common.js"></script>
    </body>
</html>
