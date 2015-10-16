<?php
require 'SQLUtils.php';
$email = urldecode($_GET['user']);
if(empty($email)) {
    $email = "no";
}
?>
<!DOCTYPE>
<html>
    <head>
        <title>Taft Clubs</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" type="text/css" href="index.css">
        <link rel="stylesheet" type="text/css" href="login.css">
        <script src="js/jquery-2.1.4.min.js"></script>
    </head>
    <body>
        <div class="popOut">
            Log In
            <form action="authenticate.php" method="post">
                Username: <input type="text" name="user"><br>
                Password: <input type="text" name="pass"><br>
                <input type="submit" value="Log In">
            </form>
        </div>
        <div class="header">
            <div class="top_bar">
                <div class="search_bar">
                    <form>
                        <input id="dyn_search_bar" type="text" placeholder=" Search...">
                    </form>
                </div>
                <div class="logo">
                    <span>Taft Clubs</span>
                </div>
            </div>
            <div id="nav">
                <ul>
                    <a><li class="active">All</li></a>
                    <?php
                        $amt = 5;
                        $conn = getSQLConnectionFromConfig();
                        $result = $conn->query('SELECT data FROM taftclubs.clubcategories ORDER BY id LIMIT ' . $amt);
                        if($result->num_rows > 0) {
                            while($item = $result->fetch_assoc()) {
                                echo '<a><li>' . $item['data'] . '</li></a>';
                            }
                        } else {
                            echo "SQL ERROR: 0 results";
                        }
                    ?>
                    <a class="more_categories"><li>>></li></a>
                    <a class="search_symbol"><li>&#9906;</li></a> <!--&#9906; = Magnifying Glass Character -->
                    <a class="login_nav_bar"><li>
                        <?php
                            $loginQuery = $conn->query('SELECT preferred_name FROM sgstudents.seniors_data
                            WHERE sgstudents.seniors_data.email = "' . $email . '";');
                            if($loginQuery->num_rows == 1) {
                                $data = $loginQuery->fetch_assoc();
                                echo "Hello, " . $data['preferred_name'];
                            } else {
                                echo "Log In";
                            }
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
        <script src="index.js"></script>
    </body>
</html>
