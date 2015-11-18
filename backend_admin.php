<?php session_start(); ?>
<?php
    require 'scripts/SQLUtils.php';
    require 'scripts/index_utils.php';

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
                    <a href="index.php"><li>Home</li></a>
                    <a><li class="active">Club Applications</li></a>
                    <a><li>Club Edits</li></a>
                    <a><li>List of Approved Clubs</li></a>
                    <a class="login_nav_bar">
                        <li>
                            <?php
                                getInputToLoginMenu($conn);
                             ?>
                       </li>
                       <ul class="login_menu_hoverable">
                           <li>My Clubs</li>
                           <li>Make A New Club</li>
                           <li class="backend_admin_link">Admin Page</li>
                           <li>Log Out</li>
                       </ul>
                   </a>
                </ul>
            </div>
        </div>
        <div class="content">
            <script type="text/javascript">
                $.ajax({
                    url: "backend_admin_approveclubs.php",
                    type: "GET",
                }).done(function(html) {
                    $(".content").html(html);
                    registerClubApplications();
                });
            </script>
        </div>
        <input type="file">
        <script type="text/javascript" src="js/common.js"></script>
        <script type="text/javascript" src="backend_admin.js"></script>
    </body>
</html>
<?php $conn->close(); ?>
