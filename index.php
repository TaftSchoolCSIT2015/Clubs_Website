<?php session_start(); ?>
<?php
require 'scripts/SQLUtils.php';
require 'scripts/index_utils.php';

$action = "";

if(isset($_REQUEST['action'])) {
    $action = sanatizeInput($_REQUEST['action']);
}

$conn = getSQLConnectionFromConfig();
?>
<!DOCTYPE>
<html>
    <head>
        <title>Taft Clubs</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="style/common.css">
        <link rel="stylesheet" type="text/css" href="index.css">
        <script src="js/jquery-2.1.4.min.js"></script>
        <script type="text/javascript">
          var loadMyClubs = false;
          <?php
              if($action == "myclubs") {
                  echo "loadMyClubs = true;";
              }
          ?>
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
            <div class="top_bar">
                <div class="search_bar">
                    <form>
                        <input id="dyn_search_bar" type="text" placeholder=" Search...">
                    </form>
                </div>
                <div class="title">
                    <span>Taft Clubs</span>
                </div>
            </div>
            <div class="nav">
                <ul>
                    <a><li class="active">All</li></a>
                    <?php
                        assembleNavMenu($conn);
                    ?>
                    <a class="search_symbol"><li>&#9906;</li></a> <!--&#9906; = Magnifying Glass Character -->
                    <a class="login_nav_bar"><li>
                        <?php
                            getInputToLoginMenu($conn);
                        ?>
                    </li>
                        <ul class="login_menu_hoverable">
                            <li>My Clubs</li>
                            <li>Make A New Club</li>
                            <?php
                                addAdminLink($conn);
                            ?>
                            <li>Log Out</li>
                        </ul>
                    </a>

                </ul>
            </div>
        </div>
        <div class="content">
            <div class="club_widgets">
                <ul>
                </ul>
            </div>
            <div class="event_cal">
                <h1>Upcoming Events</h1><br>
                <ul>
                    <?php
                        $query = "SELECT * FROM taftclubs.clubevents INNER JOIN taftclubs.club ON clubevents.clubId = club.id WHERE date > NOW() ORDER BY date";
                        $result = $conn->query($query);
                        if($result->num_rows > 0) {
                            while($item = $result->fetch_assoc()) {
                                $currentDate = substr($item['date'], 0, 10);
                                echo "<div class='calDate'><h2>" . $currentDate ."</h2><ul>";
                                while(substr($item['date'], 0, 10) == $currentDate && ($item != NULL)) {
                                    $eventTime = date("g:i a", strtotime(substr($item['date'], 10)));
                                    echo "<li><span>{$eventTime}</span> <b>{$item['description']}</b> hosted by <b>{$item['name']}</b> at <b>{$item['location']}</b></li>";
                                    $item = $result->fetch_assoc();
                                }
                                echo "</ul></div><br>";
                            }
                        }
                    ?>
                </ul>
            </div>
        </div>
        <script src="js/common.js"></script>
        <script src="index.js"></script>
    </body>
</html>
<?php $conn->close(); ?>
