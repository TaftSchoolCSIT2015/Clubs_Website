<?php session_start(); ?>
<?php
    require 'SQLUtils.php';
    require 'scripts/index_utils.php';

    $conn = getSQLConnectionFromConfig();

    $clubname = "";
    if(isset($_GET['n'])) {
        $clubname = sanatizeInput($_GET['n']);
    }
 ?>
 <!DOCTYPE html>
<html>
    <head>
        <title><?php echo $clubname . " Home Page"; ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="style/common.css">
        <link rel="stylesheet" type="text/css" href="club.css">
        <script src="js/jquery-2.1.4.min.js"></script>
    </head>
    <body>
        <div class="header">
            <div class="title">
                <span>
                <?php
                    echo $clubname . " Page";
                ?>
                </span>
            </div>
            <div class="nav">
                <ul>
                    <a class="active">About Us</a>
                </ul>
            </div>
        </div>
        <div class="content">
        </div>
        <script src="js/common.js"></script>
        <script src="club.js"></script>
    </body>
</html>
