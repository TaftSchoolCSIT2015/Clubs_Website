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
    require 'index_utils.php';

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
            '$mission_statement', 0, 1, 0, NOW(), {$catId}, 1, {$SCHOOL_YEAR})";
            $conn->query($query);

            $clubid = $conn->insert_id;

            insertLeaders($_POST['leaders'], $clubid, $conn);

            foreach($_POST['events'] as $event) {
                $eventStuff = explode(", ", $event);
                insertNewEvent($eventStuff[0], $eventStuff[1], $eventStuff[2],
                $eventStuff[3], $clubid, 1, $conn);
            }
        } else if($request_type == "editdraft") {
            //If we are editing a draft, we can operate under the assumption
            //that we can make changes without logging a change in the "Edits" table
            //But we will check that the club_status is a draft first
            $update_index = $_POST['update_index'];
            $result = $conn->query("SELECT EXISTS(
	                                   SELECT *
                                       FROM taftclubs.club
                                       WHERE club.id = {$update_index} AND (club.status != 5)
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
                    $event['time'], $update_index, 1, $conn);
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
        } else if($request_type == "editsubmission") {
            //Figure out where we are in the cycle and add to club edits
            $update_index = $_POST['update_index'];
            $result = $conn->query("SELECT EXISTS(
                                       SELECT *
                                       FROM taftclubs.club
                                       WHERE club.id = {$update_index} AND (club.status = 5)
                                   ) as exist");
            $doesExist = $result->fetch_assoc();
            if($doesExist['exist'] == 0) { //We are not an Approved Club ergo we must go through this submission process
                die("Invalid Submission, Non-Approved club");
            }
            if(isAdmin($conn)) { //If Admin then Automatic approval
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
                        $event['time'], $update_index, 1, $conn);
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
            } else { //If not admin then must go through the Club Edits Table
                $about_us = $_POST['about_us'];
                $events = $_POST['events'];
                $delEvents = $_POST['deleted_events'];
                if($about_us['club_name'] != "") {
                    $clubname = $about_us['club_name'];
                    $query = "INSERT INTO taftclubs.clubedits (personId, clubId, typeOfEdit, oldField, newField, approved)
                                VALUES((SELECT id FROM sgstudents.seniors_data WHERE username = '{$_SESSION['user']}'), {$update_index},
                                        9, (SELECT name FROM taftclubs.club WHERE id = {$update_index}), '$clubname', 0)";
                    $conn->query($query);
                }
                if($about_us['club_missionstatement'] != "") {
                    $mission_statement = $about_us['club_missionstatement'];
                    $query = "INSERT INTO taftclubs.clubedits (personId, clubId, typeOfEdit, oldField, newField, approved)
                                VALUES((SELECT id FROM sgstudents.seniors_data WHERE username = '{$_SESSION['user']}'), {$update_index},
                                        1, (SELECT mission_statement FROM taftclubs.club WHERE id = {$update_index}), '$mission_statement', 0)";
                    $conn->query($query);
                }
                if($about_us['club_category'] != "") {
                    $club_category = $about_us['club_category'];
                    $oldCat = getClubCatId($update_index, $conn);
                    $newCatId = categoryToId($club_category, $conn);

                    $query = "INSERT INTO taftclubs.clubedits (personId, clubId, typeOfEdit, oldField, newField, approved)
                                VALUES((SELECT id FROM sgstudents.seniors_data WHERE username = '{$_SESSION['user']}'), {$update_index},
                                        2, {$oldCat}, {$newCatId}, 0)";
                    $conn->query($query);
                }
                if(sizeof($events) > 0) {
                    foreach($events as $event) {
                        $updateId = $event['updateId'];
                        if($updateId < 0) { //New Event
                            insertNewEvent($event['title'], $event['location'], $event['date'],
                            $event['time'], $update_index, 0, $conn); //New event, Not Approved
                            $query = "INSERT INTO taftclubs.clubedits (personId, clubId, typeOfEdit, oldField, newField, approved, specialId)
                                        VALUES((SELECT id FROM sgstudents.seniors_data WHERE username = '{$_SESSION['user']}'), {$update_index},
                                                3, 'Nothing, New Field', 'Title: {$event['title']} Location: {$event['location']} Date: {$event['date']} Time: {$event['time']}', 0, {$conn->insert_id})";
                            $conn->query($query);
                        } else { //Modified Event
                            $query = "INSERT INTO taftclubs.clubedits (personId, clubId, typeOfEdit, oldField, newField, approved, specialId)
                                        VALUES((SELECT id FROM sgstudents.seniors_data WHERE username = '{$_SESSION['user']}'), {$update_index},
                                                4, (SELECT CONCAT('Title: ', event.description, ' Location: ', event.location, ' DateTime: ', event.date) FROM taftclubs.clubevents as event WHERE event.id = {$updateId}),
                                                'Title: {$event['title']} Location: {$event['location']} Date: {$event['date']} Time: {$event['time']}', 0, {$updateId})";
                            $conn->query($query);
                        }
                    }
                }
                if(sizeof($delEvents) > 0) {
                    foreach($delEvents as $deletedEvent) {
                        $updateId = $deletedEvent;
                        $query = "INSERT INTO taftclubs.clubedits (personId, clubId, typeOfEdit, oldField, newField, approved, specialId)
                                    VALUES((SELECT id FROM sgstudents.seniors_data WHERE username = '{$_SESSION['user']}'), {$update_index},
                                            5, (SELECT CONCAT('Title: ', event.description, ' Location: ', event.location, ' DateTime: ', event.date) FROM taftclubs.clubevents as event WHERE event.id = {$updateId}),
                                            'Delete Event', 0, {$updateId})";
                        $conn->query($query);
                    }
                }
                error_log($conn->error);
            }

        } else if($request_type == "submit_registration") {
            //So, because this isn't in the database we need to put it in!
            $name = $_POST['title'];
            $advisor = explode(" ", $_POST['faculty_advisor']);
            $mission_statement = $_POST['mission_statement'];
            $status = 2; //Awaiting Faculty Approval
            $category = $_POST['category'];

            $catId = categoryToId($category, $conn);

            $query = "INSERT INTO taftclubs.club (name, advisor, mission_statement, sticky, status, approved, startDate, category, isJoinable, schoolYear)
                        VALUES('$name', (SELECT id FROM sgstudents.seniors_data WHERE last_name = '{$advisor[1]}' AND (preferred_name = '{$advisor[0]}' OR first_name = '{$advisor[0]}')),
            '$mission_statement', 0, {$status}, 0, NOW(), {$catId}, 1, {$SCHOOL_YEAR})";

            $conn->query($query);

            $clubid = $conn->insert_id;

            insertLeaders($_POST['leaders'], $clubid, $conn);

            foreach($_POST['events'] as $event) {
                $eventStuff = explode(", ", $event);
                insertNewEvent($eventStuff[0], $eventStuff[1], $eventStuff[2],
                $eventStuff[3], $clubid, 1, $conn);
            }
            //Now we need to notify the faculty member that they need to approve a club

            //1. Get Faculty Email String!
            $emailRes = $conn->query("SELECT username, id FROM sgstudents.seniors_data WHERE last_name = '{$advisor[1]}' AND (preferred_name = '{$advisor[0]}' OR first_name = '$advisor[0]')");
            $facultyUsername = $emailRes->fetch_assoc();
            $facultyId = $facultyUsername['id'];
            $facultyEmail = $facultyUsername['username'] . '@taftschool.org';
            $emailString = "Hello!\r\n\tYou have been requested as a club advisor for the {$name}!\r\nWhose mission statement is: {$mission_statement}";
            $emailString .= "\r\nAnd led by student leaders: {$_POST['leaders'][0]}";
            $emailString .= "\r\nLink to Live Club Page: http://" . $_SERVER['SERVER_NAME'] . "/clubs/club.php?clubId=" . $clubid;
            $emailString .= "\r\nClick this link within 24 hours to accept this invitation: ";
            //2. Generate One-Way Hash with Salt
            $salt = getRandomBytes(32);
            $saltyString = $name . $salt . $advisor[0] . $salt . $mission_statement . $salt;
            $md5HashedString = md5($saltyString) . substr(md5($salt . $facultyId), 0, 8); //24 bytes
            $salt = $saltyString = "";
            $md5HashedString = substr($md5HashedString, 0, 24); //10^28 total possibilities for hash, we should be safe in assuming no collisions
            //3. Put Hash Into Database:
            $conn->query("INSERT INTO taftclubs.faculty_approval_links (hash, clubId, dateIssued)
                            VALUES('$md5HashedString', {$clubid}, NOW())");
            error_log($conn->error);
            //4. Append Hash URL onto Email String
            $emailString .= "http://" . $_SERVER['SERVER_NAME'] . "/clubs/scripts/approve.php?hash=" . $md5HashedString;
            sendMail(array($facultyEmail), "Club Advisor Request", $emailString, "From: TaftClubs <clubs@taftschool.org>", $conn);
            error_log($conn->error);
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

function insertNewEvent($title, $location, $date, $time, $clubid, $isApproved, $conn) {
    $username = $_SESSION['user'];
    $dateConcat = $date . " " . $time . ":00";
    $query = "INSERT INTO taftclubs.clubevents (clubId, posterId, isApproved, isDeleted, description, location, dateCreated, date)
            VALUES({$clubid}, (SELECT id FROM sgstudents.seniors_data WHERE username = '$username'), {$isApproved}, 0, '{$title}', '$location', NOW(), '$dateConcat')";
    $conn->query($query);
}

function updateExistingEvent($eventId, $title, $location, $date, $time, $conn) {
    $dateConcat = $date . " " . $time . ":00";
    $query = "UPDATE taftclubs.clubevents
                SET description = '{$title}', location = '$location', date = '$dateConcat'
                WHERE id = {$eventId}";
    $conn->query($query);
}

function getRandomBytes($numBytes = 16) {
    //Windows Based Systems
    if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        return bin2hex(openssl_random_pseudo_bytes($numBytes));
    } else { //Unix Based Systems
        $urand = fopen("/dev/urandom", "r");
        stream_set_read_buffer($urand, $numBytes);
        $bytes = fread($urand, $numBytes);
        if($bytes === FALSE || strlen($bytes) != $numBytes) {
            throw new RuntimeException("Read of /dev/urandom returned malformed bytes");
        }
        fclose($urand);
        return $bytes;
    }
}
?>
