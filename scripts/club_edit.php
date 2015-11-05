<?php
    $SCHOOL_YEAR = 2016;
    session_start();
    //Must be authenticated to get to this page
    if(!isset($_SESSION['user'])) {
        echo "NO USER";
        exit();
    }

    require 'SQLUtils.php';
    require 'category_utils.php';
    require 'club_utils.php';

    $json = file_get_contents("php://input");
    $_POST = json_decode($json, true);

    $request_type = "";
    if(isset($_POST['request_type'])) {
        $request_type = sanatizeInput($_POST['request_type']);
        $conn = getSQLConnectionFromConfig();
        if($request_type == "savedraft") {
            $name = $_POST['title'];
            $advisor = explode(" ", $_POST['faculty_advisor']);
            $mission_statement = $_POST['mission_statement'];
            $status = $_POST['club_status'];
            $category = $_POST['category'];

            $catId = categoryToId($category, $conn);

            $query = "INSERT INTO taftclubs.club (name, advisor, mission_statement, sticky, status, approved, startDate, category, isJoinable, schoolYear)
                        VALUES('$name', (SELECT id FROM sgstudents.seniors_data WHERE last_name = '{$advisor[1]}' AND (preferred_name = '{$advisor[0]}' OR first_name = '{$advisor[0]}')),
            '$mission_statement', 0, {$status}, 0, NOW(), {$catId}, 1, {$SCHOOL_YEAR})";
            $conn->query($query);
            echo $conn->error;
            $clubid = $conn->insert_id;

            insertLeaders($_POST['leaders'], $clubid, $conn);

            $last_leader = $conn->insert_id;
            foreach($_POST['events'] as $event) {
                $events = explode(", ", $event);
                $dateSplits = explode("-", $events[2]);
                $date = date("Y-m-d H:i:s", mktime(0,0,0, intval($dateSplits[1]), intval($dateSplits[2]), intval($dateSplits[0])));
                $addEventQuery = "INSERT INTO taftclubs.clubevents (clubId, posterId, isApproved, isDeleted, description, location, dateCreated, date) " .
                "VALUES({$clubid}, {$last_leader}, 0, 0, '{$events[0]} at {$events[3]}', '{$events[1]}', NOW(), '{$date}')";
                $conn->query($addEventQuery);
            }

            $conn->close();
        } else if($request_type = "editdraft") {
            //If we are editing a draft, we can operate under the assumption
            //that we can make changes without logging a change in the "Edits" table
            //But we will check that the club_status is a draft first
            $update_index = $_POST['update_index'];
            $result = $conn->query("SELECT EXISTS(
	                                   SELECT *
                                       FROM taftclubs.club
                                       WHERE club.id = {$update_index} AND club.status = 1
                                   ) as exist");
            $doesExists = $result->fetch_assoc();
            if($doesExists['exist'] == 0) {
                die("UNAUTHORIZED ACTION");
            }

            //Personal Club Data
            $about_us = $_POST['about_us'];
            $queryBuilder = "UPDATE taftclubs.club as club SET ";
            if($about_us['club_name'] != "") {
                $clubname = $about_us['club_name'];
                $queryBuilder .= "club.name = '$clubname', ";
            }
            if($about_us['club_category'] != "") {
                $club_category = $about_us['club_category'];
                $catInt = categoryToId($club_category, $conn);
                $queryBuilder .= "club.category = {$catInt}, ";
            }
            if($about_us['club_missionstatement'] != "") {
                $club_mission = $about_us['club_missionstatement'];
                $queryBuilder .= "club.mission_statement = '$club_mission', ";
            }
            //Make Nice!
            if(substr($queryBuilder, -2) == ", ") {
                $queryBuilder = substr($queryBuilder, 0, -2);
            }
            $queryBuilder .= " WHERE club.id = {$update_index}";
            $conn->query($queryBuilder);

            //Leader Data
            $leaders = $about_us['club_leaders'];
            $deletedLeaders = $about_us['deleted_leaders'];
            insertLeaders($leaders, $update_index, $conn);
            //If we are a draft then dont delete leaders!
            deleteLeaders($deletedLeaders, $update_index, $conn, true);

            //Event Data
            $events = $_POST['events'];
            $delEvents = $_POST['deleted_events'];
            foreach($events as $event) {
                $updateId = $event['updateId'];
                if($updateId < 0) { //New Event
                    insertNewEvent($event['title'], $event['location'], $event['date'],
                    $event['time'], $update_index, $conn);
                } else { //Existing Event
                    updateExistingEvent($updateId, $event['title'], $event['location'],
                    $event['date'], $event['time'], $conn);
                }
            }
            foreach($delEvents as $deletedEvent) {
                $updateId = $deletedEvent;
                $conn->query("UPDATE taftclubs.clubevents
                                SET isDeleted = 1
                                WHERE id = {$updateId}");
            }
        }
        $conn->close();
    }

function insertLeaders($leaders, $clubid, $conn) {
    foreach($leaders as $person) {
        $names = explode(" ", $person);
        $first = $last = "";
        if(sizeof($names) == 2) {
            $first = $names[0];
            $last = $names[1];
        } else if(sizeof($names) == 3) {
            $first = $names[0] . " " . $names[1];
            $last = $names[2];
        }
        $subIdQuery = "(SELECT id FROM sgstudents.seniors_data WHERE last_name = '{$last}' AND (preferred_name = '{$first}' OR first_name = '{$first}'))";
        $joinClubQuery = "REPLACE INTO taftclubs.clubjoiners (userId, clubId, dateJoined, hasLeft, isLeader) " .
        "VALUES({$subIdQuery}, {$clubid}, NOW(), 0, 1)";
        $conn->query($joinClubQuery);
    }
}

function deleteLeaders($leaders, $clubid, $conn, $isDraft) {
    foreach($leaders as $person) {
        $names = explode(" ", $person);
        $first = $last = "";
        if(sizeof($names) == 2) {
            $first = $names[0];
            $last = $names[1];
        } else if(sizeof($names) == 3) {
            $first = $names[0] . " " . $names[1];
            $last = $names[2];
        }
        $subIdQuery = "(SELECT id FROM sgstudents.seniors_data WHERE last_name = '{$last}' AND (preferred_name = '{$first}' OR first_name = '{$first}'))";
        $joinClubQuery = "UPDATE taftclubs.clubjoiners
                            SET isLeader = 0";
        if($isDraft) {
            $joinClubQuery .= ", hasLeft = 1";
        }
        $joinClubQuery .= " WHERE userId = {$subIdQuery} AND clubId = {$clubid}";
        $conn->query($joinClubQuery);
    }
}

function insertNewEvent($title, $location, $date, $time, $clubid, $conn) {
    $username = $_SESSION['user'];
    $dateSplits = explode("-", $date);
    $newDate = date("Y-m-d H:i:s", mktime(0,0,0, intval($dateSplits[1]), intval($dateSplits[2]), intval($dateSplits[0])));
    $query = "INSERT INTO taftclubs.clubevents (clubId, posterId, isApproved, isDeleted, description, location, dateCreated, date)
            VALUES({$clubid}, (SELECT id FROM sgstudents.seniors_data WHERE username = '$username'), 0, 0, '{$title} at {$time}', '$location', NOW(), '$newDate')";
    $conn->query($query);
}

function updateExistingEvent($eventId, $title, $location, $date, $time, $conn) {
    $dateSplits = explode("-", $date);
    $newDate = date("Y-m-d H:i:s", mktime(0,0,0, intval($dateSplits[1]), intval($dateSplits[2]), intval($dateSplits[0])));
    $query = "UPDATE taftclubs.clubevents
                SET description = '{$title} at {$time}', location = '$location', date = '$newDate'
                WHERE id = {$eventId}";
    $conn->query($query);
}
?>
