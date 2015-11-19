<?php
/*
Method: GET
Parameters:
    [a] = Action, for control flow: values-> {catsearch=Category Search} | {userclub=Get a users clubs}
    [v] = input value: For "catsearch" is the category
*/
session_start();

require 'SQLUtils.php';
require 'club_utils.php';

$action = $value = "";
if(isset($_GET['a'])) {
    $action = sanatizeInput($_GET['a']);
    $conn = getSQLConnectionFromConfig();
    $endOfQuery =  " GROUP BY c.id
              ORDER BY j.isLeader DESC, c.status ASC, c.name ASC";
        if($action == 'catsearch') {
            if(isset($_GET['v'])) {
                $username = "";
                if(isset($_SESSION['user'])) {
                    $username = sanatizeInput($_SESSION['user']);
                }
                $value = sanatizeInput($_GET['v']);
                $query = "SELECT c.id as id, c.name as name, c.mission_statement as mission, CONCAT_WS(', ', GROUP_CONCAT(DISTINCT leader.preferred_name, ' ', leader.last_name SEPARATOR ', ')) as leader_name, advisor.preferred_name as advisor_first, advisor.last_name as advisor_last
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
                        echo constructCatSearchWidgetString($item['name'], $item['id'], $item['leader_name'],
                        $item['advisor_first'], $item['advisor_last'], $item['mission'], isPartOfClub($username, $item['id'], $conn));
                    }
                } else {
                    echo "Oops, There doesn't seem to be anything here yet! Try creating a club for this category!";
                }
            } else {
                echo 'FATAL ERROR: MALFORMED QUERY->VALUE NOT SET';
            }
        } else if($action == 'userclub' && isset($_SESSION['user'])) {
            $value = sanatizeInput($_GET['v']);
            $user = $_SESSION['user'];
            $query = "SELECT c.id as id, c.name as name, c.mission_statement as mission, CONCAT_WS(', ', GROUP_CONCAT(DISTINCT leader.preferred_name, ' ', leader.last_name SEPARATOR ', ')) as leader_name, advisor.preferred_name as advisor_first, advisor.last_name as advisor_last, c.status as status
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
                                        WHERE leader_inClub.hasLeft = 0 AND leader_inClub.isLeader = 1 AND j.hasLeft = 0 AND (c.status != 3 OR c.status != 6) AND student.username = '$user'";
            $result = "";
            if($value == 'All') {
                $result = $conn->query($query . $endOfQuery);
            } else {
                $result = $conn->query($query . " AND category.data = '$value'" . $endOfQuery);
            }
            if($result->num_rows > 0) {
                while($item = $result->fetch_assoc()) {
                    echo constructMyClubsWidgetString($item['name'], $item['id'], $item['leader_name'],
                    $item['advisor_first'], $item['advisor_last'], $item['mission'], $item['status'], isHeadOfClub($user, $item['id'], $conn));
                }
            } else {
                echo "Oops, There doesn't seem to be anything here yet! Join a club to have it displayed here!";
            }

        } else if($action == 'adminsearch') {
            $value = sanatizeInput($_GET['v']);
            $query = "SELECT club.id, club.name, CONCAT_WS(', ', GROUP_CONCAT(DISTINCT people.preferred_name, ' ', people.last_name SEPARATOR ', ')) as leaders, CONCAT(advisor.preferred_name, ' ', advisor.last_name) as advisor, cstat.name as status
    	                           FROM taftclubs.club as club
    	                           INNER JOIN taftclubs.clubjoiners as j
    	                           ON club.id = j.clubId
    	                           INNER JOIN sgstudents.seniors_data as people
    	                           ON j.userId = people.id
    	                           INNER JOIN taftclubs.clubstatus as cstat
    	                           ON cstat.id = club.status
                                   INNER JOIN sgstudents.seniors_data as advisor
                                   ON club.advisor = advisor.id
    	                           WHERE (cstat.name = '$value' AND j.isLeader = 1 AND j.hasLeft = 0)
    	                           GROUP BY club.id";
            $result = $conn->query($query);
            if($result->num_rows > 0) {
                while($data = $result->fetch_assoc()) {
                    $linkContents = "";
                    if($value === "Active") {
                        $linkContents = "<a data-index=\"{$data['id']}\">Delete Club?</a>";
                    } else if($value === "Deleted") {
                        $linkContents = "<a data-index=\"{$data['id']}\">Undelete Club?</a>";
                    } else if($value === "Waiting for Admin Approval") {
                        $linkContents = "<a data-index=\"{$data['id']}\">Approve Club?</a>";
                    } else if($value === "Past") {
                        $linkContents = "<a data-index=\"{$data['id']}\">Restore Club?</a>";
                    } else if($value === "Pending Faculty Approval") {
                        $linkContents = "<a data-index=\"{$data['id']}\">Reject Club?</a>";
                    } else {
                        $linkContents = "<a>&#10004;</a> <a>&#10008;</a>";
                    }
                    echo constructAdminSearchTablesRow($data['name'], $data['id'], $data['leaders'], $data['advisor'], $data['status'], $linkContents);
                }
            } else {
                echo "<tr><td>Could Not Find Any Data for the Selected Category</td></tr>";
            }
        } else {
            echo "FATAL ERROR: MALFORMED QUERY->VALUE NOT SET";
        }
        $conn->close();
}

function constructWidgetString($clubname, $clubId, $leadersString, $advisor_first, $advisor_last, $mission, $class) {
    return "<a class='$class' href='club.php?clubId={$clubId}'><li><h1>" . $clubname . '</h1><p><b>Leader(s): </b>' .
        $leadersString . '</p><p><b>Faculty Advisor: </b>' .
            $advisor_first . ' ' . $advisor_last .
            '</p><p><em>' . $mission . '</em></p></li></a>';
}

function constructMyClubsWidgetString($clubname, $clubId, $leader_name, $advisor_first, $advisor_last, $mission, $status, $isLeader) {
    $leaders = explode(", ", $leader_name);
    $leadersString = implode(" and ", $leaders);
    $class = ($status == 5) ? "" : "not_approved_club";
    $class .= ($isLeader) ? " leader_of_club" : "";
    $class = ltrim($class);
    return constructWidgetString($clubname, $clubId, $leadersString, $advisor_first, $advisor_last, $mission, $class);
}

function constructCatSearchWidgetString($clubname, $clubId, $leader_name, $advisor_first, $advisor_last, $mission, $isMember) {
    $leaders = explode(", ", $leader_name);
    $leadersString = implode(" and ", $leaders);
    $class = ($isMember) ? "is_part_of_club" : "";
    return constructWidgetString($clubname, $clubId, $leadersString, $advisor_first, $advisor_last, $mission, $class);
}

function constructAdminSearchTablesRow($clubname, $clubId, $leaders, $advisor, $status, $approveTableData) {
    $row = "<tr>";
    $row .= "<td><a href='club.php?clubId={$clubId}'>{$clubname}</a></td>";
    $row .= "<td>{$leaders}</td>";
    $row .= "<td>{$advisor}</td>";
    $row .= "<td>{$status}</td>";
    //$row .= "<td><a>&#10004;</a> <a>&#10008;</a></td>";
    $row .= "<td>" . $approveTableData . "</td>";
    $row .= "<td><a>&#128231;</a></td>";
    $row .= "</tr>";
    return $row;
}
 ?>
