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
<table border="5" id="events">
    <thead>
        <tr>
            <th><strong>What</strong></th>
            <th><strong>Where</strong></th>
            <th><strong>When</strong></th>
            <th><strong>Time</strong></th>
            <th><strong>RSVP %</strong></th>
            <th><strong>RSVP</strong></th>
        </tr>
    </thead>
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
            $eventId = $event['id'];
            $percentRSVP = bcdiv(intval($rsvpCount), intval($memberCount), 3) * 100;
     ?>
            <tr data-index="<?php echo $eventId; ?>">
                <td><?php echo $eventName; ?></td>
                <td><?php echo $eventLoc; ?></td>
                <td><?php echo $eventDate; ?></td>
                <td><?php echo $eventTime; ?></td>
                <td><?php echo $percentRSVP; ?></td>
                <td><input data-index="<?php echo $eventId; ?>" type="button" class="rsvpBut" value="RSVP"></td>
            </tr>
     <?php
        }
      ?>
</table>
<?php
    $conn->close();
?>
