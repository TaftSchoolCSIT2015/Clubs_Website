<?php session_start(); ?>
<?php
    require 'scripts/SQLUtils.php';
    require 'scripts/index_utils.php';

    $conn = getSQLConnectionFromConfig();

    $backendAdmins = array();

    $result = $conn->query("SELECT username FROM taftclubs.clubadmins");
    if($result->num_rows > 0) {
        while($data = $result->fetch_assoc()) {
            $backendAdmins[] = $data['username'];
        }
    }

    /*General Unlogged in Person*/
    if(!isset($_SESSION['user'])) {
        header("Location: index.php");
        exit();
    }
    /*authenticated person*/
    $username = $_SESSION['user'];

    if(array_search($username, $backendAdmins) === FALSE) {
        header("Location: index.php");
        exit();
    }

    $query = "SELECT club.name, CONCAT('\"', CONCAT_WS(', ', GROUP_CONCAT(DISTINCT people.preferred_name, ' ', people.last_name SEPARATOR ', ')), '\"') as leaders, CONCAT(advisor.preferred_name, ' ', advisor.last_name) as advisor
	               FROM taftclubs.club as club
	               INNER JOIN taftclubs.clubjoiners as j
	               ON club.id = j.clubId
	               INNER JOIN sgstudents.seniors_data as people
	               ON j.userId = people.id
	               INNER JOIN taftclubs.clubstatus as cstat
	               ON cstat.id = club.status
                   INNER JOIN sgstudents.seniors_data as advisor
                   ON club.advisor = advisor.id
	               WHERE (cstat.name = 'Active' AND j.isLeader = 1 AND j.hasLeft = 0)
	               GROUP BY club.name";
    $qResult = $conn->query($query);
    header("Content-Type: text/comma-seperated-values; charset=utf-8");
    header("Content-Disposition: attachment; filename=TaftClubsList.csv");
    echo "Club Name,Student Leaders(s),Faculty Advisor\n";
    if($qResult->num_rows > 0) {
        while($data = $qResult->fetch_assoc()) {
            echo $data['name'] . "," . $data['leaders'] . "," . $data['advisor'] . "\r\n"; //Windows Compatible! CRLF
        }
    }
?>
<?php $conn->close(); ?>
