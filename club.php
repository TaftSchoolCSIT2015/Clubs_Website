<?php session_start(); ?>
<?php
    require 'scripts/SQLUtils.php';
    require 'scripts/index_utils.php';
    require 'scripts/club_utils.php';

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
        <script>
            var clubName = "<?php echo $clubname ?>";
        </script>
    </head>
    <body>
        <div class="popOut">
            Log In<br>
                Username: <input type="text" name="user"><br>
                Password: <input type="text" name="pass"><br>
                <input name="loginButton" type="submit" value="Log In">
                <div id="loginStatus"></div>
        </div>
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
                    <a><li class="active">About Us</li></a>
                    <a><li>Events</li></a>
                    <a><li>Club Feed</li></a>
                    <a id="club_join_button">
                        <li><?php
                            $isPart = $isLeader = 0;
                            if(isset($_SESSION['user'])) {
                                $isPart = isPartOfClub($_SESSION['user'], $clubname, $conn);
                                $isLeader = isHeadOfClub($_SESSION['user'], $clubname, $conn);
                            }
                            if($isPart == 0) {
                                echo "Join Club";
                            } else if($isLeader == 0) {
                                echo "Leave Club";
                            } else {
                                echo "Edit Club";
                            }
                         ?>
                        </li>
                    </a>
                    <a class="login_nav_bar"><li>
                        <?php
                            getInputToLoginMenu($conn);
                        ?>
                    </li>
                        <ul class="login_menu_hoverable">
                            <li>My Clubs</li>
                            <li>Make A New Club</li>
                            <?php addAdminLink($conn); ?>
                            <li>Log Out</li>
                        </ul>
                    </a>
                </ul>
            </div>
        </div>
        <div class="content">
        </div>
        <script src="js/common.js"></script>
        <script src="club.js"></script>
    </body>
</html>
<?php $conn->close(); ?>
