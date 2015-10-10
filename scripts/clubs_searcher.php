<?php
require '../SQLUtils.php';

$action = $value = "";

if(isset($_GET['a'])) {
    $action = sanatizeInput($_GET['a']);
    $a = explode('?', $action)[0];
        if($a == 'catsearch') {
            if(isset($_GET['a'])) {
                $value = explode('?', $action)[1];
                $value = substr($value, 2, strlen($value));
                $conn = getSQLConnectionFromConfig();
                $result = $conn->query('SELECT uClub.name as name, uClub.mission_statement as mission, uClub.preferred_name as leader_first, uClub.last_name as leader_last, advisor.preferred_name as advisor_first, advisor.last_name as advisor_last
FROM sgstudents.seniors_data as advisor
INNER JOIN	(
	SELECT aClub.name, aClub.mission_statement, aClub.advisor, people.preferred_name, people.last_name
	FROM sgstudents.seniors_data as people
	INNER JOIN (
        SELECT theClub.name, theClub.mission_statement, theClub.advisor, joiner.userId
		FROM taftclubs.clubjoiners as joiner
		INNER JOIN (
			SELECT club.name, club.mission_statement, club.advisor, clubcategories.data, club.id
            FROM taftclubs.club
            INNER JOIN taftclubs.clubcategories
            ON club.category = clubcategories.id
            WHERE clubcategories.data =' . '"' . $value . '"' . '
            ) as theClub
		ON joiner.clubId = theClub.id
        WHERE joiner.hasLeft = 0 AND joiner.isLeader = 1
        ) as aClub
	ON aClub.userId = people.id
    ) as uClub
ON uClub.advisor = advisor.id
');
                if($result->num_rows > 0) {
                    while($item = $result->fetch_assoc()) {
                        echo constructWidgetString($item['name'], $item['leader_first'], $item['leader_last'],
                        $item['advisor_first'], $item['advisor_last'], $item['mission']);
                    }
                } else {
                    echo "SQL ERR: 0 Results";
                }
            } else {
                echo 'FATAL ERROR: VALUE NOT SET';
            }
            break;
        }
}

 ?>
