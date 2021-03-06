<?php

    require 'SQLUtils.php';
    require 'club_utils.php';
    require 'index_utils.php';

    session_start();
    $session = isset($_SESSION['user']);
    $sessionUser = "";
    if($session) {
        $sessionUser = $_SESSION['user'];
    }

    $action = "";
    $value = "";
    $response = array("success" => 0, "sqlError" => "");
    if(isset($_GET['action'])) {
        $conn = getSQLConnectionFromConfig();
        $action = sanatizeInput($_GET['action']);
        if(($action == "isPartOfClub") && $session && isset($_GET['value'])) {
            $username = $sessionUser;
            $value = sanatizeInput($_GET['value']);
            $response['success'] = isPartOfClub($username, $value, $conn);
        } else if($action == "joinClub" && $session && isset($_GET['value'])) {
            $username = $sessionUser;
            $value = sanatizeInput($_GET['value']);
            joinClub($username, $value, $conn);
            $response['success'] = 1;
        } else if($action == "leaveClub" && $session && isset($_GET['value'])) {
            $username = $sessionUser;
            $value = sanatizeInput($_GET['value']);
            leaveClub($username, $value, $conn);
            $response['success'] = 1;
            echo json_encode($response);
        } else if($action == "doesClubNameExist" && isset($_GET['value'])) {
            $value = sanatizeInput($_GET['value']);
            $response['success'] = doesClubNameExist($value, $conn);
        } else if(isAdmin($conn) && ($action == "adminApproveClub") && isset($_GET['value'])) {
            $value = sanatizeInput($_GET['value']);
            $conn->query("UPDATE taftclubs.club SET approved = 1, status = 5 WHERE id = {$value}");
        } else if(isAdmin($conn) && ($action == "adminDeleteClub") && isset($_GET['value'])) {
            $value = sanatizeInput($_GET['value']);
            $conn->query("UPDATE taftclubs.club SET approved = 0, status = 3 WHERE id = {$value}");
        } else if(isAdmin($conn) && ($action == "adminRejectClub") && isset($_GET['value'])) {
            $value = sanatizeInput($_GET['value']);
            $conn->query("UPDATE taftclubs.club SET approved = 0, status = 7 WHERE id = {$value}");
        } else if(isAdmin($conn) && ($action == "modifiedclubname") && isset($_GET['value'])) {
            $value = sanatizeInput($_GET['value']);
            $conn->query("UPDATE taftclubs.club SET name = (SELECT newField FROM taftclubs.clubedits WHERE id = {$value}) WHERE id = (SELECT clubId FROM taftclubs.clubedits WHERE id = {$value})");
            $conn->query("UPDATE taftclubs.clubedits SET approved = 1 WHERE id = {$value}");
        } else if(isAdmin($conn) && ($action == "modifiedmissionstatement") && isset($_GET['value'])) {
            $value = sanatizeInput($_GET['value']);
            $conn->query("UPDATE taftclubs.club SET mission_statement = (SELECT newField FROM taftclubs.clubedits WHERE id = {$value}) WHERE id = (SELECT clubId FROM taftclubs.clubedits WHERE id = {$value})");
            $conn->query("UPDATE taftclubs.clubedits SET approved = 1 WHERE id = {$value}");
        } else if(isAdmin($conn) && ($action == "modifiedclubcategory") && isset($_GET['value'])) {
            $value = sanatizeInput($_GET['value']);
            $conn->query("UPDATE taftclubs.club SET category = (SELECT newField FROM taftclubs.clubedits WHERE id = {$value}) WHERE id = (SELECT clubId FROM taftclubs.clubedits WHERE id = {$value})");
            $conn->query("UPDATE taftclubs.clubedits SET approved = 1 WHERE id = {$value}");
        } else if(isAdmin($conn) && ($action == "addedclubevent") && isset($_GET['value'])) {
            $value = sanatizeInput($_GET['value']);
            $conn->query("UPDATE taftclubs.clubevents SET isApproved = 1 WHERE id = (SELECT specialId FROM taftclubs.clubedits WHERE id = {$value})");
            $conn->query("UPDATE taftclubs.clubedits SET approved = 1 WHERE id = {$value}");
        } else if(isAdmin($conn) && ($action == "modifiedclubevent") && isset($_GET['value'])) {
            $value = sanatizeInput($_GET['value']);
            $result = $conn->query("SELECT newField FROM taftclubs.clubedits WHERE id = {$value}");
            $data = $result->fetch_assoc();
            error_log($data['newField']);
            $values = explode("<span>", $data['newField']);
            for($i = 1; $i < sizeof($values); $i++) {
                $values[$i] = substr($values[$i], 0, stripos($values[$i], "</span>"));
            }
            $conn->query("UPDATE taftclubs.clubevents SET description = '{$values[1]}', location = '{$values[2]}', date = '{$values[3]} {$values[4]}' WHERE id = (SELECT specialId FROM taftclubs.clubedits WHERE id = {$value})");
            $conn->query("UPDATE taftclubs.clubedits SET approved = 1 WHERE id = {$value}");
        } else if(isAdmin($conn) && ($action == "deletedclubevent") && isset($_GET['value'])) {
            $value = sanatizeInput($_GET['value']);
            $conn->query("UPDATE taftclubs.clubevents SET isApproved = 0 WHERE id = (SELECT specialId FROM taftclubs.clubedits WHERE id = {$value})");
            $conn->query("UPDATE taftclubs.clubedits SET approved = 1 WHERE id = {$value}");
        } else if(isAdmin($conn) && ($action == "noapproveclubedit") && isset($_GET['value'])) {
            $value = sanatizeInput($_GET['value']);
            $conn->query("UPDATE taftclubs.clubedits SET approved = 2 WHERE id = {$value}");
        }
        $response['sqlError'] = $conn->error;
        $conn->close();
    }
    echo json_encode($response);
 ?>
