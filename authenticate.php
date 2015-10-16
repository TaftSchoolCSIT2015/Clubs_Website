<?php
require 'SQLUtils.php';

$email = $_POST['user'];

$conn = getSQLConnectionFromConfig();
$loginQuery = $conn->query("SELECT preferred_name FROM sgstudents.seniors_data
                                                 WHERE sgstudents.seniors_data.email = '$email'");
if($loginQuery->num_rows == 1) { /* Does have 1 single match for an email in the database, therefore redirect*/
    header('Location: ' . "index.php?user=" . urlencode($email));
    $conn->close();
    exit();
} else {
?>
<html>
    <head>
        <title>Login Failed Redirect</title>
        <META http-equiv="refresh" content="5;URL=index.php">
    </head>
    <body>
        <center>Your login attempt failed! Automatically redirecting you to the homepage. If you are
            not redirected automatically click this <a href="index.php">Link</a>
        </center>
    </body>
</html>
<?php
}
$conn->close();
exit();
?>
