<?php
$email = $_POST['user'];
header('Location: ' . "index.php?user=" . urlencode($email));
exit;
?>
