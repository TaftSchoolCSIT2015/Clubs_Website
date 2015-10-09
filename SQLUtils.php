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
?>
