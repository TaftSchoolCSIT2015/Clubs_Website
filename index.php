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
                    </li></a>
                </ul>
            </div>
        </div>
        <div class="content">
            <div class="club_widgets">
                <ul>
                </ul>
            </div>
        </div>
        <script type="text/javascript">
            var toggleLogo = function() {
                $(".logo").slideToggle("fast", "linear");
                $(".search_bar").slideToggle("fast", "linear");
                if(!$(this).children().first().hasClass("active")) {
                    $(this).children().first().addClass("active");
                } else {
                    $(this).children().first().removeClass("active");
                }
            };
            var radioify = function() {
                $("#nav a").each(function() {
                    if($(this).hasClass("search_symbol") || $(this).hasClass("login_nav_bar")) {
                        return;
                    }
                    $(this).children().first().removeClass("active");
                });
            };
            var makeWidgetsClickable = function() {
                $(".club_widgets a").click(function() {
                    var clubName = $(this).children().first().children().first().html();
                    $(this).attr("href", "/club.php?n=" + clubName);
                });
            }

            $(document).ready(function() {
                $(".search_bar").hide();
                $(".popOut").hide();
                $.ajax({
                    url: "/scripts/clubs_searcher.php",
                    type: "GET",
                    data: 'a=catsearch&v=All',
                }).done(function(html) {
                    $(".club_widgets ul").html(html);
                    makeWidgetsClickable();
                });
                $("#nav a").click(function() {
                    if($(this).hasClass("search_symbol") || $(this).hasClass("login_nav_bar")) {return;}
                    radioify();
                    $(this).children().first().addClass("active");
                    //Send a Query to the Database demanding new data
                    var value = $(this).children().first().html();
                    var x = 'a=catsearch' + '&v=' + value; //a stands for action, v stands for value
                    $.ajax({
                        url: "/scripts/clubs_searcher.php",
                        type: "GET",
                        data: x,
                    }).done(function(html) {
                        $(".club_widgets ul").html(html);
                        makeWidgetsClickable();
                    });
                });
                $(".search_symbol").click(toggleLogo);
                $(".login_nav_bar").click(function() {
                    if(!$(this).children().first().hasClass("active") && ($(this).children().first().html().indexOf("Log In") >= 0)) {
                        $(this).children().first().addClass("active");
                        $(".popOut").show();
                    } else {
                        $(this).children().first().removeClass("active");
                        $(".popOut").hide();
                    }
                });
            });
        </script>
    </body>
</html>
<?php exit(); ?>
