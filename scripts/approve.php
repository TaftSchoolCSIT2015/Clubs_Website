<?php

require 'SQLUtils.php';

$hash = "";
if(!isset($_REQUEST['hash'])) {
    exit();
}

$hash = $_REQUEST['hash'];

if(strlen($hash) != 24) {
    exit();
}

$conn = getSQLConnectionFromConfig();

$result = $conn->query("SELECT link.clubId, link.dateIssued as dateIssued, link.dateClicked , club.name
                        FROM taftclubs.faculty_approval_links as link
                        INNER JOIN taftclubs.club as club
                        ON club.id = link.clubId
                        WHERE link.hash = '$hash'");
//Handle non-existant hash
if(!($result->num_rows > 0)) {
    echo "No faculty advisor request exists for the given parameters. Malformed URL?";
    $conn->close();
    exit();
}
$data = $result->fetch_assoc();
//Handle if already clicked!
if($data['dateClicked'] == "NULL") {
    echo "This club has already been activated!";
    $conn->close();
    exit();
}
//Are we in a good time Interval
if(!isIntervalGood($data['dateIssued'])) {
    echo "This interval has expired!";
    $conn->close();
    exit();
}
//Now that we logic out of the way, lets do some club approving
$conn->query("UPDATE taftclubs.club SET status = 4 WHERE club.id = {$data['clubId']}"); //Update to Pending Admin Approval Status
//Show that this link has now been clicked
$conn->query("UPDATE taftclubs.faculty_approval_links SET dateClicked = NOW() WHERE hash = '$hash'");
echo "Faculty Approval Complete!";


function isIntervalGood($initial) {
    $now = new DateTime("now");
    if(((new DateTime($initial))->add(new DateInterval("P1D"))) > ($now)) { //1 Day from the issuing date is in the future
        return true;
    }
    return false;
}
?>
<?php $conn->close(); ?>
