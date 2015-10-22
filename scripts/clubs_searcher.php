<?php
/*
Method: GET
Parameters:
    [a] = Action, for control flow: values-> {catsearch=Category Search} | {userclub=Get a users clubs}
    [v] = input value: For "catsearch" is the category
*/
session_start();
require 'SQLUtils.php';
$action = $value = "";
if(isset($_GET['a'])) {
    $action = sanatizeInput($_GET['a']);
    $conn = getSQLConnectionFromConfig();
    $endOfQuery =  " GROUP BY c.id
              ORDER BY c.status DESC, j.isLeader DESC, c.id ASC";
        if($action == 'catsearch') {
            if(isset($_GET['v'])) {
                $value = sanatizeInput($_GET['v']);
                $query = "SELECT c.name as name, c.mission_statement as mission, CONCAT_WS(', ', GROUP_CONCAT(DISTINCT leader.preferred_name, ' ', leader.last_name SEPARATOR ', ')) as leader_name, advisor.preferred_name as advisor_first, advisor.last_name as advisor_last
                          FROM taftclubs.club as c
                          INNER JOIN sgstudents.seniors_data as advisor
                          ON c.advisor = advisor.id
                          INNER JOIN taftclubs.clubjoiners as j
                          ON c.id = j.clubId
                          INNER JOIN sgstudents.seniors_data as leader
                          ON leader.id = j.userId
                          INNER JOIN taftclubs.clubcategories as category
                          ON c.category = category.id
                          WHERE j.hasLeft = 0 AND j.isLeader = 1 AND c.approved = 1 AND c.status = 5";
                $result = "";
                if($value == 'All') {
                    $result = $conn->query($query . $endOfQuery);
                } else {
                    $result = $conn->query($query . " AND category.data = '$value'" . $endOfQuery);
                }
                if($result->num_rows > 0) {
                    while($item = $result->fetch_assoc()) {
                        echo constructWidgetString($item['name'], $item['leader_name'],
                        $item['advisor_first'], $item['advisor_last'], $item['mission'], 5);
                    }
                } else {
                    echo "SQL ERR: 0 Results";
                }
            } else {
                echo 'FATAL ERROR: MALFORMED QUERY->VALUE NOT SET';
            }
        } else if($action == 'userclub' && isset($_SESSION['user'])) {
            $value = sanatizeInput($_GET['v']);
            $user = $_SESSION['user'];
            $query = "SELECT c.name as name, c.mission_statement as mission, CONCAT_WS(', ', GROUP_CONCAT(DISTINCT leader.preferred_name, ' ', leader.last_name SEPARATOR ', ')) as leader_name, advisor.preferred_name as advisor_first, advisor.last_name as advisor_last, c.status as status
                                        FROM taftclubs.club as c
                                        INNER JOIN sgstudents.seniors_data as advisor
                                        ON c.advisor = advisor.id
                                        INNER JOIN taftclubs.clubjoiners as leader_inClub
                                        ON c.id = leader_inClub.clubId
                                        INNER JOIN sgstudents.seniors_data as leader
                                        ON leader.id = leader_inClub.userId
                                        INNER JOIN taftclubs.clubjoiners as j
                                        ON c.id = j.clubId
                                        INNER JOIN sgstudents.seniors_data as student
                                        ON student.id = j.userId
                                        INNER JOIN taftclubs.clubcategories as category
                                        ON c.category = category.id
                                        WHERE leader_inClub.hasLeft = 0 AND leader_inClub.isLeader = 1 AND j.hasLeft = 0 AND student.username = '$user'";
            $result = "";
            if($value == 'All') {
                $result = $conn->query($query . $endOfQuery);
            } else {
                $result = $conn->query($query . " AND category.data = '$value'" . $endOfQuery);
            }
            if($result->num_rows > 0) {
                while($item = $result->fetch_assoc()) {
                    echo constructWidgetString($item['name'], $item['leader_name'],
                    $item['advisor_first'], $item['advisor_last'], $item['mission'], $item['status']);
                }
            } else {
                echo "SQL ERR: 0 Results";
            }

        } else {
            echo "FATAL ERROR: MALFORMED QUERY->VALUE NOT SET";
        }
        $conn->close();
}

function constructWidgetString($clubname, $leader_name, $advisor_first, $advisor_last, $mission, $status) {
    $leaders = explode(", ", $leader_name);
    $leadersString = implode(" and ", $leaders);
    $class = ($status == 5) ? "" : "not_approved_club";
    return "<a class='$class'><li><h1>" . $clubname . '</h1><p><b>Leader(s): </b>' .
        $leadersString . '</p><p><b>Faculty Advisor: </b>' .
            $advisor_first . ' ' . $advisor_last .
            '</p><p><em>' . $mission . '</em></p></li></a>';
}
 ?>
