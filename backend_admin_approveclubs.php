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
    <thead>
        <tr>
            <th>Club Name</th>
            <th>Leaders</th>
            <th>Advisor</th>
            <th>Status
                <select>
                    <?php
                        $stati = $conn->query("SELECT name FROM taftclubs.clubstatus ORDER BY listOrder");
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
    <thead>
    <tbody>
    <?php
        $clubsForOptions = "SELECT club.id, club.name, CONCAT_WS(', ', GROUP_CONCAT(DISTINCT people.preferred_name, ' ', people.last_name SEPARATOR ', ')) as leaders, CONCAT(advisor.preferred_name, ' ', advisor.last_name) as advisor, cstat.name as status
	                           FROM taftclubs.club as club
	                           INNER JOIN taftclubs.clubjoiners as j
	                           ON club.id = j.clubId
	                           INNER JOIN sgstudents.seniors_data as people
	                           ON j.userId = people.id
	                           INNER JOIN taftclubs.clubstatus as cstat
	                           ON cstat.id = club.status
                               INNER JOIN sgstudents.seniors_data as advisor
                               ON club.advisor = advisor.id
	                           WHERE (cstat.listOrder = 0 AND j.isLeader = 1 AND j.hasLeft = 0)
	                           GROUP BY club.id";
        $result = $conn->query($clubsForOptions);
        if($result->num_rows > 0) {
            while($data = $result->fetch_assoc()) {
    ?>
                <tr>
                    <td><a href="club.php?clubId=<?php echo $data['id']; ?>"><?php echo $data['name']; ?></a></td>
                    <td><?php echo $data['leaders']; ?></td>
                    <td><?php echo $data['advisor']; ?></td>
                    <td><?php echo $data['status']; ?></td>
                    <td data-index="<?php echo $data['id']; ?>"><a>Delete Club?</a></td>
                    <td><a>&#128231;</a></td>
                </tr>
    <?php
            }
        }
     ?>
    </tbody>
</table>
<?php $conn->close(); ?>
