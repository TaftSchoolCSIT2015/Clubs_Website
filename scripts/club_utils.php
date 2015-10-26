<?php
function isPartOfClub($username, $club, $conn) {
    $query = "SELECT EXISTS(
               SELECT * FROM taftclubs.club as club
               INNER JOIN taftclubs.clubjoiners as joinee
               ON joinee.clubId = club.id
               INNER JOIN sgstudents.seniors_data as user
               ON joinee.userId = user.id
               WHERE user.username = '$username' AND club.name = '$club' AND joinee.hasLeft = 0
               ) as answer";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['answer'];
}

function isHeadOfClub($username, $club, $conn) {
    $query = "SELECT EXISTS(
               SELECT * FROM taftclubs.club as club
               INNER JOIN taftclubs.clubjoiners as joinee
               ON joinee.clubId = club.id
               INNER JOIN sgstudents.seniors_data as user
               ON joinee.userId = user.id
               WHERE user.username = '$username' AND club.name = '$club' AND joinee.hasLeft = 0 AND joinee.isLeader = 1
               ) as answer";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['answer'];
}

function joinClub($username, $club, $conn) {
    $query = "INSERT INTO taftclubs.clubjoiners (userId, clubId, dateJoined, hasLeft, isLeader)
                VALUES(
	               (SELECT user.id
                   FROM sgstudents.seniors_data as user
                   WHERE user.username = '$username'),
                   (SELECT club.id
                   FROM taftclubs.club as club
                   WHERE club.name = '$club'),
                   NOW(),
                   0,
                   0
                )";
    $conn->query($query);
}

function leaveClub($username, $club, $conn) {
    $query = "UPDATE taftclubs.clubjoiners as joinee
              SET joinee.hasLeft = 1
              WHERE (joinee.clubId = (SELECT club.id FROM taftclubs.club as club WHERE club.name = '$club')
              AND joinee.userId = (SELECT user.id FROM sgstudents.seniors_data as user WHERE user.username = '$username'))";
    $conn->query($query);
}

function getAboutClub($club, $conn) {
    $query = "SELECT club.mission_statement
                FROM taftclubs.club as club
                WHERE club.name = '$club'";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['mission_statement'];
}

function getLeadersForClub($club, $conn) {
    $query = "SELECT GROUP_CONCAT(DISTINCT leader.preferred_name, ' ', leader.last_name) as leader_name
                FROM taftclubs.club as club
                INNER JOIN taftclubs.clubjoiners as j
                ON j.clubId = club.id
                INNER JOIN sgstudents.seniors_data as leader
                ON j.userId = leader.id
                WHERE club.name = '$club' AND j.isLeader = 1 AND j.hasLeft = 0";
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
                WHERE club.name = '$club'";
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
                WHERE club.name = '$club'";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['mission_statement'];
}

function getClubEvents($club, $conn) {
    $query = "SELECT event.description, event.location, event.date, COUNT(rsvp.id) as rsvpCount, COUNT(members.id) memberCount
                FROM taftclubs.clubevents as event
                INNER JOIN taftclubs.club as club
                ON event.clubId = club.id
                INNER JOIN taftclubs.clubs_rsvp as rsvp
                ON rsvp.eventId = event.id
                INNER JOIN taftclubs.clubjoiners as members
                ON (members.clubId = club.id AND members.userId = rsvp.userId)
                WHERE event.isDeleted = 0 AND club.name = '$club' AND members.hasLeft = 0 AND rsvp.reply = 1;";
    $result = $conn->query($query);
    $ret = array();
    if($result->num_rows > 0) {
        while($data = $result->fetch_assoc()) {
            $event = array("description" => $data['description'],
                           "location" => $data['location'],
                           "date" => $data['date'],
                           "rsvpCount" => $data['rsvpCount'],
                           "memberCount" => $data['memberCount']);
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
                WHERE club.name = '$club'";
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
                WHERE club.name = '$club' AND member.hasLeft = 0";
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

function getClubDatabaseIndex($club, $conn) {
    $query = "SELECT id FROM taftclubs.club WHERE name = '$club'";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['id'];
}

function doesClubNameExist($club, $conn) {
    $query = "SELECT EXISTS(
	           SELECT * FROM taftclubs.club
               WHERE name = '$club') as result";
    $result = $conn->query($query);
    $data = $result->fetch_assoc();
    return $data['result'];
}
?>
