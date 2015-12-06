<?php
    if(!isset($_GET['clubId'])) {
        exit();
    }

    require 'scripts/SQLUtils.php';
    require 'scripts/club_utils.php';

    $clubId = sanatizeInput($_GET['clubId']);

    $conn = getSQLConnectionFromConfig();

    $posts = getClubFeedPosts($clubId, $conn);
?>
<h2>Posts: </h2>
<ul class="posts">
    <?php
        foreach($posts as $post) {
            $poster = $post['poster'];
            $datePosted = $post['dateCreated'];
            $content = $post['content'];
    ?>
            <li>
                <div>Posted By: <?php echo $poster; ?></div>
                <div>On: <?php echo $datePosted; ?></div>
                <div><?php echo $content; ?></div>
            </li>
    <?php
        }
     ?>
</ul>
<?php
    $conn->close();
 ?>
