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

$members = getMembersForClub($clubId, $conn);
?>
<h2>Members:</h2>
<ul>
<?php
foreach($members as $member) {
?>
    <li><?php echo $member; ?><input class="X_button" type="button" value="X"></li>
<?php } ?>
</ul>
<?php $conn->close(); ?>
