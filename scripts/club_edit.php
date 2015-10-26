<?php
    $SCHOOL_YEAR = 2016;
    session_start();
    //Must be authenticated to get to this page
    if(!isset($_SESSION['user'])) {
        exit();
    }
    require 'SQLUtils.php';
    require 'category_utils.php';

    $json = file_get_contents("php://input");
    $_POST = json_decode($json, true);

    $request_type = "";
    if(isset($_POST['request_type'])) {
        $request_type = sanatizeInput($_POST['request_type']);
        if($request_type == "savedraft") {
            $name = $_POST['title'];
            $advisor = explode(" ", $_POST['faculty_advisor']);
            $mission_statement = $_POST['mission_statement'];
            $status = $_POST['club_status'];
            $category = $_POST['category'];

            $conn = getSQLConnectionFromConfig();
            $catId = categoryToId($category, $conn);
            $query = "INSERT INTO taftclubs.club (name, advisor, mission_statement, sticky, status, approved, startDate, category, isJoinable, schoolYear)
            VALUES('$name', (SELECT id FROM sgstudents.seniors_data WHERE last_name = '{$advisor[1]}' AND (preferred_name = '{$advisor[0]}' OR first_name = '{$advisor[0]}')), " .
            "'$mission_statement', 0, {$status}, 0, NOW(), {$catId}, 1, {$SCHOOL_YEAR})";
            $conn->query($query);
            echo $conn->error;
            $clubid = $conn->insert_id;
            foreach($_POST['leaders'] as $person) {
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
                $joinClubQuery = "INSERT INTO taftclubs.clubjoiners (userId, clubId, dateJoined, hasLeft, isLeader) " .
                "VALUES({$subIdQuery}, {$clubid}, NOW(), 0, 1)";
                $conn->query($joinClubQuery);
            }

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
        }
    }
?>
