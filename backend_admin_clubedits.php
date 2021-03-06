<?php
    session_start();
    require 'scripts/SQLUtils.php';
    require 'scripts/index_utils.php';
    require 'scripts/category_utils.php';

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
<table id="editClubsTable" border="2">
    <thead>
        <tr>
            <th>Club Name</th>
            <th>Editor</th>
            <th>Type of Edit</th>
            <th>Old Field</th>
            <th>New Field</th>
            <th>Approve?</th>
            <th>Contact Editor</th>
        </tr>
    <thead>
    <tbody>
        <?php
            $query = "SELECT club.id as cId, club.name as name, CONCAT(editor.preferred_name, ' ', editor.last_name) as editor, lookupTable.data as typeOfEdit, edit.oldField as oldField, edit.newField as newField, edit.id
                        FROM taftclubs.clubedits as edit
                        INNER JOIN taftclubs.club as club
                        ON edit.clubId = club.id
                        INNER JOIN sgstudents.seniors_data as editor
                        ON edit.personId = editor.id
                        INNER JOIN taftclubs.clubedits_lookup as lookupTable
                        ON edit.typeOfEdit = lookupTable.id
                        WHERE edit.approved = 0
                        ORDER BY edit.id";
            $result = $conn->query($query);
            if($result->num_rows > 0) {
                while($data = $result->fetch_assoc()) {
                    if($data['typeOfEdit'] == "Modified Club Category") {
                        $data['oldField'] = idToCategory($data['oldField'], $conn);
                        $data['newField'] = idToCategory($data['newField'], $conn);
                    }
        ?>
                    <tr>
                        <td><a href="club.php?clubId=<?php echo $data['cId']; ?>"><?php echo $data['name']; ?></a></td>
                        <td><?php echo $data['editor']; ?></td>
                        <td><?php echo $data['typeOfEdit']; ?></td>
                        <td><?php echo $data['oldField']; ?></td>
                        <td><?php echo $data['newField']; ?></td>
                        <td data-index="<?php echo $data['id']; ?>"><a>&#10004;</a> <a>&#10008;</a></td>
                        <td><a>&#128231;</a></td>
                    </tr>
        <?php
                }
            }
         ?>
    </tbody>
</table>
<?php $conn->close(); ?>
