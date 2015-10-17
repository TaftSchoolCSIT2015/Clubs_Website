<?php
require 'SQLUtils.php';
$action = $value = "";
if(isset($_GET['a'])) {
    $action = sanatizeInput($_GET['a']);
        if($action == 'catsearch') {
            if(isset($_GET['v'])) {
                $value = sanatizeInput($_GET['v']);
                $conn = getSQLConnectionFromConfig();
                $query = "SELECT c.name as name, c.mission_statement as mission, leader.preferred_name as leader_first, leader.last_name as leader_last, advisor.preferred_name as advisor_first, advisor.last_name as advisor_last
                          FROM taftclubs.club as c
                          INNER JOIN sgstudents.seniors_data as advisor
                          ON c.advisor = advisor.id
                          INNER JOIN taftclubs.clubjoiners as j
                          ON c.id = j.clubId
                          INNER JOIN sgstudents.seniors_data as leader
                          ON leader.id = j.userId
                          INNER JOIN taftclubs.clubcategories as category
                          ON c.category = category.id
                          WHERE j.hasLeft = 0 AND j.isLeader = 1";
                $result = "";
                if($value == 'All') {
                    $result = $conn->query($query);
                } else {
                    $result = $conn->query($query . " AND category.data = '$value'");
                }
                if($result->num_rows > 0) {
                    while($item = $result->fetch_assoc()) {
                        echo constructWidgetString($item['name'], $item['leader_first'], $item['leader_last'],
                        $item['advisor_first'], $item['advisor_last'], $item['mission']);
                    }
                } else {
                    echo "SQL ERR: 0 Results";
                }
                $conn->close();
            } else {
                echo 'FATAL ERROR: VALUE NOT SET';
            }
        }
}

function constructWidgetString($clubname, $leader_first, $leader_last, $advisor_first, $advisor_last, $mission) {
    return '<a><li><h1>' . $clubname . '</h1><p><b>Leader(s): </b>' .
        $leader_first . ' ' . $leader_last . '</p><p><b>Faculty Advisor: </b>' .
            $advisor_first . ' ' . $advisor_last .
            '</p><p><em>' . $mission . '</em></p></li></a>';
}
 ?>
