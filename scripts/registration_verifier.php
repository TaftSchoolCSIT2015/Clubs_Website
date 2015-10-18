<?php
    /*
    Method: GET
    Parameters:
        [field] = field authenticating for
        [value1] = input value 1
        [value2] = input value 2
    */
    require 'SQLUtils.php';
    $field = $value1 = $value2 = "";
    if(isset($_GET['field'])) {
        $field = sanatizeInput($_GET['field']);
        $conn = getSQLConnectionFromConfig();
        if($field == 'add_leader') {
            if(isset($_GET['value1']) && isset($_GET['value2'])) {
                $ret = array('answer' => 0);
                $value1 = sanatizeInput($_GET['value1']);
                $value2 = sanatizeInput($_GET['value2']);
                $result = $conn->query("SELECT EXISTS(SELECT preferred_name, last_name
			  FROM sgstudents.seniors_data
              WHERE (preferred_name = '$value1' OR first_name = '$value1') AND last_name = '$value2') as answer");
                $data = $result->fetch_assoc();
                $ret['answer'] = $data['answer'];
                echo json_encode($ret);
            }
        }
        $conn->close();
    }
?>
