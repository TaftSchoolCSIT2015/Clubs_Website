<?php
    session_start();
    require 'scripts/SQLUtils.php';
    require 'scripts/index_utils.php';

    $conn = getSQLConnectionFromConfig();

    $backendAdmins = array();

    $result = $conn->query("SELECT username FROM taftclubs.clubadmins");
    if($result->num_rows > 0) {
        while($data = $result->fetch_assoc()) {
            $backendAdmins[] = $data['username'];
        }
    }

    /*General Unlogged in Person*/
    if(!isset($_SESSION['user'])) {
        exit();
    }
    /*authenticated person*/
    $username = $_SESSION['user'];

    if(array_search($username, $backendAdmins) === FALSE) {
        exit();
    }
?>
<table id="approvedClubsTable" border="2">
    <tr>
        <th>Club Name</th>
        <th>Leaders</th>
        <th>Advisor</th>
        <th>Status
            <select>
                <?php
                    $stati = $conn->query("SELECT name FROM taftclubs.clubstatus");
                    if($stati->num_rows > 0) {
                        while($data = $stati->fetch_assoc()) {
                            $val = $data['name'];
                            echo "<option value='$val'>{$val}</option>";
                        }
                    }
                ?>
            </select>
        </th>
        <th>Approve</th>
        <th>Connect</th>
    </tr>
    <?php
        $clubsForOptions = "SELECT";
     ?>
</table>
<?php $conn->close(); ?>
