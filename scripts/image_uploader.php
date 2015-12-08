<?php
    require 'SQLUtils.php';

    $headers = getallheaders();

    if(!isset($headers['ClubId'])) {
        echo "ERR: Club Id Header not Set!";
        exit();
    }

    $clubId = $headers['ClubId'];

    if(!empty($_FILES)) {
        $file = $_FILES['file']['tmp_name'];
        $extension = "image/" . pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);

        $conn = getSQLConnectionFromConfig();

        $conn->query("REPLACE INTO taftclubs.clubimages (clubId, data, contentType) VALUES ({$clubId}, '" . $conn->real_escape_string(file_get_contents($file)) . "', '$extension')");

        echo $conn->error;

        $conn->close();
    }
 ?>
