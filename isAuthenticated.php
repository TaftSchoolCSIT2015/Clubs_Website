<?php session_start(); ?>
<?php
require 'SQLUtils.php';

$response = array();

if(isset($_SESSION['user'])) { //if already logged in for some reason, respond

}

if(isset($_POST['user'])) {
    $user = $_POST['user'];
    $conn = getSQLConnectionFromConfig();
    $loginQuery = $conn->query("SELECT preferred_name, username FROM sgstudents.seniors_data
                                                     WHERE sgstudents.seniors_data.username = '$user'");
    if($loginQuery->num_rows == 1) { //does have one single match
        $data = $loginQuery->fetch_assoc();
        $_SESSION['user'] = $data['username'];
        $response = array('success' => true, 'preferred_name' => $data['preferred_name']);
    } else {
        $response = array('success' => false);
        session_destroy();
    }
    echo json_encode($response);
    $conn->close();
}
?>
