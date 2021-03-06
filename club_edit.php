<?php session_start(); ?>
<?php
if(!isset($_SESSION['user']) && !isset($_GET['clubId'])) { //Need to be authenticated to get to this page
    header("Location: index.php");
    exit();
}

$clubId = $_GET['clubId'];
$username = $_SESSION['user'];

require 'scripts/SQLUtils.php';
require 'scripts/index_utils.php';
require 'scripts/club_utils.php';

$conn = getSQLConnectionFromConfig();

$isLeader = isHeadOfClub($username, $clubId, $conn);

if(!$isLeader && !(isAdmin($conn) == 1)) {
    header("Location: index.php");
    $conn->close();
    exit();
}
$clubname = getClubName($clubId, $conn);
?>
<!DOCTYPE html>
 <html>
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" type="text/css" href="style/common.css">
      <link rel="stylesheet" type="text/css" href="club_edit.css">
      <link rel="stylesheet" type="text/css" href="stylesheet2.css">
      <script src="js/jquery-2.1.4.min.js"></script>
      <link rel="stylesheet" type="text/css" href="js/dropzone-4.2.0/dropzone.min.css">
      <script src="js/dropzone-4.2.0/dropzone.min.js"></script>
      <script type="text/javascript">
        var clubName = "<?php echo $clubname; ?>";
        var clubId = <?php echo $clubId; ?>;

        var dirty = {
            update_index: <?php echo $clubId; ?>,
            about_us: {
                club_name: null,
                club_leaders: [],
                deleted_leaders: [],
                club_category: null,
                club_missionstatement: null,
            },
            events: [],
            deleted_events: [],
        };
      </script>
    </head>
    <body>
        <div class="header">
            <div class="top_bar">
                <div class="title"><span><?php echo $clubname; ?> Edit</span></div>
            </div>
            <div class="nav">
                <ul>
                    <a href="index.php"><li>Home</li></a>
                    <a><li class="active">Edit About Us</li></a>
                    <a><li>Edit Events</li></a>
                    <a><li>Edit Club Feed</li></a>
                    <a><li>Edit Club Members</li></a>
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
            <div class="dynamic">
                <script type="text/javascript">
                    $.ajax({
                        url: "club_edit_aboutus.php",
                        type: "GET",
                        data: "clubId=" + "<?php echo $clubId; ?>",
                    }).done(function(html) {
                        $(".dynamic").html(html);
                        PageStates.About_Us.registerJavascript();
                    });
                </script>
            </div>

            <div class="footer">
                <?php
                    if(!isClubApproved($clubId, $conn)) {
                 ?>
                <div id="save_button">
                    Save As Draft
                </div>
                <?php
                    }
                 ?>
                <div id="submit_button">
                    Submit
                </div>
            </div>
        </div>
        <script type="text/javascript" src="js/common.js"></script>
        <script type="text/javascript" src="club_edit.js"></script>
    </body>
</html>
<?php $conn->close(); ?>
