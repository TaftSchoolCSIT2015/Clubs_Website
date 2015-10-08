<?php
    function loadConfig() {
        $db_array = parse_ini_file("config.ini");
        $GLOBALS['db_server_name'] = $db_array['ipad'];
        $GLOBALS['db_username'] = $db_array['user'];
        $GLOBALS['db_password'] = $db_array['pass'];
        $GLOBALS['db_dbname'] = $db_array['name'];
    }

    function getSQLConnection() {
        $conn = new mysqli($GLOBALS['db_server_name'], $GLOBALS['db_username'],
                           $GLOBALS['db_password']);

        if($conn->connect_error) {
            die("Connection failed: ". $conn->connect_error);
        }
        echo "Connected Successfully";
    }
?>
