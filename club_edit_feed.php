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

$isLeader = isHeadOfClub($username, $clubId, $conn) | isAdmin($conn);

if(!$isLeader) {
    header("Location: index.php");
    $conn->close();
    exit();
}

$posts = getClubFeedPosts($clubId, $conn);
?>
<div class="add_club_feed_event text_line">
    <form>
        Post Content:
        <textarea>
        </textarea>
        <input type="button" value="Post">
    </form>
</div>
<div class="club_events">
    <h3>Posts:</h3>
    <table border="1">
        <tr>
        <th>Poster</th>
        <th>Date Posted</th>
        <th>Content</th>
        <th>Delete</th>
        </tr>
        <?php
        foreach($posts as $post) {
            $poster = $post['poster'];
            $datePosted = $post['dateCreated'];
            $content = $post['content'];
        ?>
        <tr>
            <td><?php echo $poster; ?></td>
            <td><?php echo $datePosted; ?></td>
            <td><textarea><?php echo $content; ?></textarea></td>
            <td><input class="X_button" type="button" value="X"></td>
        </tr>
        <?php
        }
        ?>
    </table>
</div>
<? $conn->close(); ?>
