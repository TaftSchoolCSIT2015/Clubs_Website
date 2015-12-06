<?php
    if(!isset($_GET['clubId'])) {
        exit();
    }

    require 'scripts/SQLUtils.php';
    require 'scripts/club_utils.php';

    $clubId = sanatizeInput($_GET['clubId']);

    $conn = getSQLConnectionFromConfig();

    $events = getClubEvents($clubId, $conn);
?>
<h2>Events: </h2>
<ul class="events">
    <?php
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
            <li>
                <div><?php echo $eventName; ?></div>
                <div><?php echo $eventLoc; ?></div>
                <div><?php echo $eventDate; ?></div>
                <div><?php echo $eventTime; ?></div>
                <div><?php echo $percentRSVP; ?></div>
                <input type="button" class="rsvpBut" value="RSVP">
            </li>
     <?php
        }
      ?>
</ul>
<?
    $conn->close();
?>
