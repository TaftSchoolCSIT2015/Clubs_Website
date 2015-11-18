<?php
    session_start();
    $session = isset($_SESSION['user']);
    $sessionUser = "";
    if($session) {
        $sessionUser = $_SESSION['user'];
    }
    session_write_close();

    require 'SQLUtils.php';
    require 'club_utils.php';

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
        }
        $response['sqlError'] = $conn->error;
        $conn->close();
    }
    echo json_encode($response);
 ?>
