<?php
function isPartOfClub($username, $club, $conn) {
    $query = "SELECT EXISTS(
               SELECT * FROM taftclubs.club as club
               INNER JOIN taftclubs.clubjoiners as joinee
               ON joinee.clubId = club.id
               INNER JOIN sgstudents.seniors_data as user
               ON joinee.userId = user.id
               WHERE user.username = '$username' AND club.id = {$club} AND joinee.hasLeft = 0
               ) as answer";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['answer'];
}

function getClubName($club, $conn) {
    $result = $conn->query("SELECT club.name as name FROM taftclubs.club WHERE club.id = {$club}");
    $data = $result->fetch_assoc();
    return $data['name'];
}

function isHeadOfClub($username, $club, $conn) {
    $query = "SELECT EXISTS(
               SELECT *
               FROM taftclubs.clubjoiners as joinee
               INNER JOIN sgstudents.seniors_data as user
               ON joinee.userId = user.id
               WHERE user.username = '$username' AND joinee.clubId = {$club} AND joinee.hasLeft = 0 AND joinee.isLeader = 1
               ) as answer";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['answer'];
}

function joinClub($username, $club, $conn) {
    $query = "REPLACE INTO taftclubs.clubjoiners (userId, clubId, dateJoined, hasLeft, isLeader)
                VALUES(
	               (SELECT user.id
                   FROM sgstudents.seniors_data as user
                   WHERE user.username = '$username'),
                   (SELECT club.id
                   FROM taftclubs.club as club
                   WHERE club.id = {$club}),
                   NOW(),
                   0,
                   0
                )";
    $conn->query($query);
}

function leaveClub($username, $club, $conn) {
    $query = "UPDATE taftclubs.clubjoiners as joinee
              SET joinee.hasLeft = 1
              WHERE (joinee.clubId = (SELECT club.id FROM taftclubs.club as club WHERE club.id = {$club})
              AND joinee.userId = (SELECT user.id FROM sgstudents.seniors_data as user WHERE user.username = '$username'))";
    $conn->query($query);
}

function getAboutClub($club, $conn) {
    $query = "SELECT club.mission_statement
                FROM taftclubs.club as club
                WHERE club.id = {$club}";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['mission_statement'];
}

function getLeadersForClub($club, $conn) {
    $query = "SELECT GROUP_CONCAT(DISTINCT leader.preferred_name, ' ', leader.last_name) as leader_name
                FROM taftclubs.clubjoiners as j
                INNER JOIN sgstudents.seniors_data as leader
                ON j.userId = leader.id
                WHERE j.clubId = {$club} AND j.isLeader = 1 AND j.hasLeft = 0";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['leader_name'];
}

function getClubCategories($conn) {
    $query = "SELECT data FROM taftclubs.clubcategories";
    $result = $conn->query($query);
    $ret = array();
    if($result->num_rows > 0) {
        while($data = $result->fetch_assoc()) {
            array_push($ret, $data['data']);
        }
    }
    return $ret;
}

function getClubCategory($club, $conn) {
    $query = "SELECT cat.data
                FROM taftclubs.club
                INNER JOIN taftclubs.clubcategories as cat
                ON cat.id = club.category
                WHERE club.id = {$club}";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['data'];
}

function getCheckedClubCategoryHTML($club, $conn) {
    $categories = getClubCategories($conn);
    $thisCat = getClubCategory($club, $conn);
    $html = "";
    foreach($categories as $category) {
        $html .= "<input type='radio' name='category' value='$category'";
        if($thisCat == $category) {
            $html .= " checked";
        }
        $html .= ">{$category}";
    }
    return $html;
}

function getClubMissionStatement($club, $conn) {
    $query = "SELECT club.mission_statement
                FROM taftclubs.club
                WHERE club.id = {$club}";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['mission_statement'];
}

function getClubEvents($club, $conn) {
    $query = "SELECT event.id, event.description, event.location, event.date, COUNT(rsvp.id) as rsvpCount, COUNT(members.id) memberCount
                FROM taftclubs.clubevents as event
                INNER JOIN taftclubs.club as club
                ON (event.clubId = club.id AND club.id = {$club})
                LEFT OUTER JOIN taftclubs.clubs_rsvp as rsvp
                ON (rsvp.eventId = event.id AND rsvp.reply = 1)
                LEFT OUTER JOIN taftclubs.clubjoiners as members
                ON (members.clubId = club.id AND members.hasLeft = 0)
                WHERE event.isDeleted = 0
                GROUP BY event.id";
    $result = $conn->query($query);
    $ret = array();
    if($result->num_rows > 0) {
        while($data = $result->fetch_assoc()) {
            $event = array("description" => $data['description'],
                           "location" => $data['location'],
                           "date" => $data['date'],
                           "rsvpCount" => $data['rsvpCount'],
                           "memberCount" => $data['memberCount'],
                           "id" => $data['id']);
            array_push($ret, $event);
        }
    }
    return $ret;
}

function getClubFeedPosts($club, $conn) {
    $query = "SELECT post.content, post.dateCreated, CONCAT(poster.preferred_name, ' ', poster.last_name) as poster_name
                FROM taftclubs.clubfeed as post
                INNER JOIN taftclubs.club as club
                ON post.clubId = club.id
                INNER JOIN sgstudents.seniors_data as poster
                ON post.posterId = poster.id
                WHERE club.id = {$club}";
    $result = $conn->query($query);
    $ret = array();
    if($result->num_rows > 0) {
        while($data = $result->fetch_assoc()) {
            $post = array("content" => $data['content'],
                          "dateCreated" => $data['dateCreated'],
                          "poster" => $data['poster_name']);
            array_push($ret, $post);
        }
    }
    return $ret;
}

function getMembersForClub($club, $conn) {
    $query = "SELECT DISTINCT CONCAT(student.preferred_name, ' ', student.last_name) as name
                FROM taftclubs.club as club
                INNER JOIN taftclubs.clubjoiners as member
                ON club.id = member.clubId
                INNER JOIN sgstudents.seniors_data as student
                ON member.userId = student.id
                WHERE club.id = {$club} AND member.hasLeft = 0";
    $result = $conn->query($query);
    $ret = array();
    if($result->num_rows > 0) {
        while($data = $result->fetch_assoc()) {
            $member = $data['name'];
            $ret[] = $member;
        }
    }
    return $ret;
}

function doesClubNameExist($club, $conn) {
    $query = "SELECT EXISTS(
	           SELECT * FROM taftclubs.club
               WHERE name = '$club') as result";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['result'];
}

function getClubAdvisor($club, $conn) {
    $query = "SELECT CONCAT(faculty.preferred_name, ' ', faculty.last_name) as name
                FROM taftclubs.club as club
                INNER JOIN sgstudents.seniors_data as faculty
                ON faculty.id = club.advisor
                WHERE club.id = {$club}";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['name'];
}

function getAboutUsClubPageHTML($club, $conn) {
    $query = "SELECT club.name as name, GROUP_CONCAT(leader.preferred_name, ' ', leader.last_name SEPARATOR ', ') as leaders, CONCAT(advisor.preferred_name, ' ', advisor.last_name) as advisor, club.mission_statement as mission
                FROM taftclubs.club as club
                INNER JOIN taftclubs.clubjoiners as lead
                ON (lead.clubId = club.id AND lead.hasLeft = 0 AND lead.isLeader = 1)
                INNER JOIN sgstudents.seniors_data as leader
                ON (lead.userId = leader.id)
                INNER JOIN sgstudents.seniors_data as advisor
                ON (club.advisor = advisor.id)
                WHERE club.id = {$club}";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    $html = "<h2>{$data['name']}</h2>";
    $html .= "<p><strong>Our Leaders: </strong><em>{$data['leaders']}</em></p>";
    $html .= "<p><strong>Faculty Advisor: </strong><em>{$data['advisor']}</em></p>";
    $html .= "<p><strong>Our Mission: </strong><em>{$data['mission']}</em></p>";
    return $html;
}

function isClubApproved($club, $conn) {
    $result = $conn->query("SELECT club.approved FROM taftclubs.club WHERE club.id = {$club}");
    $data = $result->fetch_assoc();
    return (intval($data['approved']) === 1) ? true : false;
}

function getClubStatus($club, $conn) {
    $result = $conn->query("SELECT club.status FROM taftclubs.club WHERE club.id = {$club}");
    $data = $result->fetch_assoc();
    return $data['status'];
}
?>
