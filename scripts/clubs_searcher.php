<?php
require '../SQLUtils.php';
$action = $value = "";
if(isset($_GET['a'])) {
    $action = sanatizeInput($_GET['a']);
        if($action == 'catsearch') {
            if(isset($_GET['v'])) {
                $value = sanatizeInput($_GET['v']);
                $conn = getSQLConnectionFromConfig();
                $result = "";
                if($value == 'All') {

                    $result = $conn->query('SELECT c.name as name, c.mission_statement as mission, leader.preferred_name as leader_first, leader.last_name as leader_last, advisor.preferred_name as advisor_first, advisor.last_name as advisor_last
                                        FROM taftclubs.club c join sgstudents.seniors_data advisor on c.advisor = advisor.id
                                        join taftclubs.clubjoiners j on c.id = j.clubId
                                        join sgstudents.seniors_data leader on leader.id = j.userId
                                        where j.hasLeft = 0 and j.isLeader = 1');

                } else {
                    $result = $conn->query("SELECT c.name as name, c.mission_statement as mission, leader.preferred_name as leader_first, leader.last_name as leader_last, advisor.preferred_name as advisor_first, advisor.last_name as advisor_last
                                        FROM taftclubs.club c join sgstudents.seniors_data advisor on c.advisor = advisor.id
                                        join taftclubs.clubjoiners j on c.id = j.clubId
                                        join sgstudents.seniors_data leader on leader.id = j.userId
                                        join taftclubs.clubcategories category on c.category = category.id
                                        where j.hasLeft = 0 and j.isLeader = 1 and category.data = '$value'");
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

 ?>
