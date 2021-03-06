<?php session_start(); ?>
<?php
    require 'scripts/SQLUtils.php';
    require 'scripts/index_utils.php';
    require 'scripts/club_utils.php';

    $conn = getSQLConnectionFromConfig();

    $clubId = 0;
    $clubname = "";
    if(isset($_GET['clubId'])) {
        $clubId = sanatizeInput($_GET['clubId']);
    }
    $clubname = getClubName($clubId, $conn);
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
            var clubName = "<?php echo $clubname; ?>";
            var clubId = <?php echo $clubId; ?>;
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
                    <a href="index.php"><li>Home</li></a>
                    <a><li class="active">About Us</li></a>
                    <a><li>Events</li></a>
                    <a id="club_join_button">
                        <li><?php
                            $isPart = $isLeader = 0;
                            if(isset($_SESSION['user'])) {
                                $isPart = isPartOfClub($_SESSION['user'], $clubId, $conn);
                                $isLeader = isHeadOfClub($_SESSION['user'], $clubId, $conn) | isAdmin($conn);
                            }
                            if($isLeader == 1) {
                                echo "Edit Club";
                            } else if($isPart == 0) {
                                echo "Join Club";
                            } else {
                                echo "Leave Club";
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
            <?php
                echo getAboutUsClubPageHTML($clubId, $conn);
             ?>
        </div>
        <div class="event_cal">
            <?php
                echo "<h2>Event Calander</h2>";
                $query2 = "SELECT * FROM taftclubs.clubevents WHERE date > NOW() AND clubId = {$clubId} ORDER BY date";
                $result2 = $conn->query($query2);
                if($result2->num_rows > 0) {
                    while($item = $result2->fetch_assoc()) {
                        echo "<br><li><b>{$item['description']}</b> at <b>{$item['location']}</b> on <b>{$item['date']}</b></li>";
                    }
                }
             ?>
        </div>
        <script src="js/common.js"></script>
        <script src="club.js"></script>
    </body>
</html>
<?php $conn->close(); ?>
