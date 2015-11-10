<?php
function getInputToLoginMenu($conn) {
    if(!isset($_SESSION['user'])) {
        echo "Log In";
    } else {
        $user = $_SESSION['user'];
        $loginQuery = $conn->query("SELECT preferred_name FROM sgstudents.seniors_data
        WHERE sgstudents.seniors_data.username ='$user'");
        if($loginQuery->num_rows == 1) {
            $data = $loginQuery->fetch_assoc();
            echo "Hello, " . $data['preferred_name'];
        } else {
            echo "SESSION MALFORMED! DESTROYING";
            session_destroy();
        }
    }
}

function assembleNavMenu($conn) {
    $amt = 5;
    $result = $conn->query('SELECT data FROM taftclubs.clubcategories ORDER BY id LIMIT ' . $amt);
    if($result->num_rows > 0) {
        while($item = $result->fetch_assoc()) {
            echo '<a><li>' . $item['data'] . '</li></a>';
        }
    } else {
        echo "SQL ERROR: 0 results";
    }
}

function isAdmin($conn) {
    $backendAdmins = array();

    $result = $conn->query("SELECT username FROM taftclubs.clubadmins");
    if($result->num_rows > 0) {
        while($data = $result->fetch_assoc()) {
            $backendAdmins[] = $data['username'];
        }
    }
    if(isset($_SESSION['user']) && (array_search($_SESSION['user'], $backendAdmins) !== FALSE)) {
        return 1;
    }
    return 0;
}
function addAdminLink($conn) {
    if(isAdmin($conn) == 1) {
        echo "<li class='backend_admin_link'>Admin Page</li>";
    }
}

function sendMail(array $recievers, $subject, $message) {
    //TODO:: Implement Function
}
?>
