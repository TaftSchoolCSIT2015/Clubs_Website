<?php session_start(); ?>
<?php
if(!isset($_SESSION['user']) && !isset($_GET['clubId'])) { //Need to be authenticated to get to this page
    header("Location: index.php");
    exit();
}

$clubId = $_GET['clubId'];
$username = $_SESSION['user'];

require 'scripts/SQLUtils.php';
require 'scripts/index_utils.php';
require 'scripts/club_utils.php';

$conn = getSQLConnectionFromConfig();

$isLeader = isHeadOfClub($username, $clubId, $conn) | isAdmin($conn);

if(!$isLeader) {
    header("Location: index.php");
    $conn->close();
    exit();
}
$clubname = getClubName($clubId, $conn);
?>
<div class="text_line">
    <form><b>Club Name:</b>
        <input id="club_name_in" type="text" value="<?php echo $clubname; ?>">
    </form>
</div>
<div id="leaders_text_line">
    <form><b>Club Leaders:</b>
        <?php
            if(isAdmin($conn)) {
        ?>
        <input id="add_leader_text" list="students" name="add_leader" type="text">
        <datalist id="students">
            <?php
                $result = $conn->query("SELECT preferred_name, last_name
                                       FROM sgstudents.seniors_data
                                       WHERE role = 'Student'
                                       ORDER BY last_name");
                if($result->num_rows > 0) {
                    while($item = $result->fetch_assoc()) {
                        echo '<option value="' .
                        $item['preferred_name'] . ' ' .
                        $item['last_name'] . '">';
                    }
                } else {
                    echo 'SQL ERR: 0 Results';
                }
            ?>
        </datalist>
        <input id="add_button" name="add_button" type="button" value="Add Leader">
        <?php } ?>
        <ul>
            <?php
                $leaders = explode(",", getLeadersForClub($clubId, $conn));
                foreach($leaders as $leader) {
                    ?>
                    <li><?php echo $leader; ?><?php if(isAdmin($conn)) {?><input class="X_button" type="button" value="X"><?php }?></li>
                    <?
                }
            ?>
        </ul>
    </form>
</div>

<div class="text_line">
    <p><b>Faculty Advisor:</b> <?php echo getClubAdvisor($clubId, $conn); ?></p>
</div>

<div id="club_type_line" class="text_line">
    <form><b>Club Category:</b>
        <?php echo getCheckedClubCategoryHTML($clubId, $conn); ?>
    </form>
</div>
<div id="mission_text_line" class="text_line">
    <form><b>Mission Statement:</b>
        <textarea id="mission_box"><?php echo getClubMissionStatement($clubId, $conn); ?></textarea>
    </form>
</div>

<div id="file_upload" class="text_line">
    Drag Club Image Here!
  <!--<form action="./scripts/image_uploader.php" class="dropzone" id="image_uploader">
  </form>-->
</div>
