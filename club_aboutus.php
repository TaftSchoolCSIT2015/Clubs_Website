<?php
    if(!isset($_GET['clubId'])) {
        exit();
    }

    require 'scripts/SQLUtils.php';
    require 'scripts/club_utils.php';

    $clubId = sanatizeInput($_GET['clubId']);

    $conn = getSQLConnectionFromConfig();

    echo getAboutUsClubPageHTML($clubId, $conn);

    $conn->close();
 ?>
