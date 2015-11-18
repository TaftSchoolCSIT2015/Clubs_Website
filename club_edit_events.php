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

$events = getClubEvents($clubId, $conn);
?>
<table border="5" id="events">
<tr>
<th>Title</th>
<th>Location</th>
<th>Date</th>
<th>Time</th>
<th>RSVP Yes %</th>
<th>Delete</th>
</tr>
<?
foreach($events as $event) {
    if($event['description'] == '') {break;}
    $splits = explode(" ", $event['date']);
    $eventName = $event['description'];
    $eventTime = $splits[1];
    $eventDate = $splits[0];
    $eventLoc = $event['location'];
    $rsvpCount = $event['rsvpCount'];
    $memberCount = $event['memberCount'];
    $percentRSVP = bcdiv(intval($rsvpCount), intval($memberCount), 3) * 100;
?>
    <tr data-index="<?php echo $event['id']; ?>">
        <td><input class="eventEdit" type="text" value='<?php echo $eventName; ?>'></td>
        <td><input class="eventEdit" type="text" value='<?php echo $eventLoc; ?>'></td>
        <td><input class="eventEdit" type="date" value='<?php echo $eventDate; ?>'></td>
        <td><input class="eventEdit" type="time" value='<?php echo $eventTime; ?>'></td>
        <td><?php echo $percentRSVP . "%"; ?></td>
        <td><input class='X_button' type='button' Value='X'></td>
    </tr>
<?php } ?>
</table>
<div id = "event_text_line">
   <p id = "mission_text">Planned Events:</p>
   <table>
       <tr>
         <td> <form class="event_form" >Title:
           <input id="event_title" class="event_box" type="text">
         </form>
         </td>
         <td> <form class="event_form" >Location:
           <input id="event_loc" class="event_box" type="text">
         </form>
         </td>
         <td> <form class="event_form" >Date:
           <input id="event_date" class="event_box" type="date">
         </form>
         </td>
         <td> <form class="event_form" >Time:
           <input id="event_time" class="event_box" type="time">
         </form>
         </td>
         <td> <form>
           <input id="add_event_button" type="button" Value = "Add">
         </form>
         </td>
       </tr>
   </table>
<? $conn->close(); ?>
