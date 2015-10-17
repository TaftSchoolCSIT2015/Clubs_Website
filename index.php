<?php session_start(); ?>
<?php
require 'scripts/SQLUtils.php';
require 'scripts/index_utils.php';

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
                    <a class="more_categories"><li>>></li></a>
                    <a class="search_symbol"><li>&#9906;</li></a> <!--&#9906; = Magnifying Glass Character -->
                    <a class="login_nav_bar"><li>
                        <?php
                            getInputToLoginMenu($conn);
                            $conn->close();
                        ?>
                    </li>
                        <ul class="login_menu_hoverable">
                            <li>My Clubs</li>
                            <li>Make A New Club</li>
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
        </div>
        <script src="js/common.js"></script>
        <script src="index.js"></script>
    </body>
</html>
