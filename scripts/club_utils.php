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

}
?>
