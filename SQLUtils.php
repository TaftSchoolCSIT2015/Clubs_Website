<?php
$db_config = array();
/*
*Returns a SQL Connection to the Schema defined in config.ini
*/
function getSQLConnectionFromConfig() {
    $db_config = parse_ini_file("config.ini");
    $conn = new mysqli($db_config['ipad'], $db_config['user'],
                       $db_config['pass']);
    if($conn->connect_error) {
        die("Connection failed: ". $conn->connect_error);
    }
    return $conn;
}

function constructWidgetString($clubname, $leader_first, $leader_last, $advisor_first, $advisor_last, $mission) {
    return '<a href=""><li><h1>' . $clubname . '</h1><p><b>Leader(s): </b>' .
        $leader_first . ' ' . $leader_last . '</p><p><b>Faculty Advisor: </b>' .
            $advisor_first . ' ' . $advisor_last .
            '</p><p><em>' . $mission . '</em></p></li></a>';
}

function sanatizeInput($input) {
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}
?>
