<?php
function idToCategory($id, $conn) {
    $result = $conn->query("SELECT data FROM taftclubs.clubcategories WHERE id = " . $id);
    $data = $result->fetch_assoc();
    return $data['data'];
}

function categoryToId($category, $conn) {
    $result = $conn->query("SELECT id FROM taftclubs.clubcategories WHERE data = '$category'");
    $data = $result->fetch_assoc();
    return $data['id'];
}
?>
