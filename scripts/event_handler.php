<?php
session_start();
//Must be authenticated to get to this page
if(!isset($_SESSION['user'])) {
    echo "NO USER";
    exit();
}

require 'SQLUtils.php';

$action = "";
$eventId = -1;

if(!isset($_GET['action']) || !isset($_GET['eventId'])) {
    echo "Parameters not set!";
    exit();
}

$action = $_GET['action'];
$eventId = $_GET['eventId'];

$conn = getSQLConnectionFromConfig();

if($action == "rsvpEvent") {
    $username = $_SESSION['user'];
    $query = "INSERT INTO taftclubs.clubs_rsvp (userId, eventId, reply)
                VALUES((SELECT id FROM sgstudents.seniors_data WHERE username = '$username'), {$eventId}, 1)";
    $conn->query($query);
}

$conn->close();
?>
