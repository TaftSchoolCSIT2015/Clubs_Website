<?php session_start(); ?>
<?php
if(!isset($_SESSION['user'])) { //Need to be authenticated to get to this page
    header("Location: index.php");
    exit();
}
require 'scripts/SQLUtils.php';
require 'scripts/index_utils.php';

$conn = getSQLConnectionFromConfig();
?>
<!DOCTYPE html>
 <html>
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" type="text/css" href="stylesheet2.css">
      <script src="js/jquery-2.1.4.min.js"></script>
    </head>
    <body>
      <div id="top_bar">

        <a href="index.php">
            <div id="home_logo">
            </div>
        </a>

        <div id="title">
          <a> Club Registration </a>
        </div>

        <div id="menu_bar">
          <span>
            <?php
                getInputToLoginMenu($conn);
            ?>
          </span>
        </div>

      </div>

      <div id="restofpage">
        <div id="main_body">
          <div class= "text_line">
               <form class="form" >Club Name:
                 <input id="club_name_in" type="text">
               </form>
          </div>

          <div id="leaders_text_line">
               <form id="leaders_form" >Club Leaders:
                 <input  id="add_leader_text" name="add_leader" type="text" >
                 <input id="add_button" name="add_button" type="button" Value="Add Leader" />
               </form>

               <ul>
                    <?php
                        if(isset($_SESSION['name'])) {
                    ?>
                    <li><?php echo $_SESSION['name']?><input class="X_button" type="button" Value="X" /></li>
                    <?php } ?>
               </ul>
          </div>

          <div class= "text_line">
               <form class = "form" >Faculty Advisor:
                 <input id="faculty_advisor_in" list="faculty" type="text" >
                 <datalist id="faculty">
                     <?php
                         $result = $conn->query('SELECT preferred_name, last_name
                                                FROM sgstudents.seniors_data
                                                WHERE role="Faculty"
                                                ORDER BY last_name');
                         if($result->num_rows > 0) {
                             while($item = $result->fetch_assoc()) {
                                 echo '<option value="' .
                                 $item['preferred_name'] . ' ' .
                                 $item['last_name'] . '">';
                             }
                         } else {
                             echo 'SQL ERR: 0 Results';
                         }
                         $conn->close();
                     ?>
                 </datalist>
               </form>

          </div>

          <div id= "mission_text_line">
               <form > <p id="mission_text">Mission Statement:</p>
                 <textarea id="mission_box"> </textarea>
               </form>
          </div>


          <div id = "event_text_line">
             <p id = "mission_text">Planned Events:</p>
             <table>
                 <tr>
                   <td> <form class="event_form" >Title:
                     <input id="event_title" class="event_box" type="text" >
                   </form>
                   </td>
                   <td> <form class="event_form" >Location:
                     <input id="event_loc" class="event_box" type="text" >
                   </form>
                   </td>
                   <td> <form class="event_form" >Date:
                     <input id="event_date" class="event_box" type="date" >
                   </form>
                   </td>
                   <td> <form class="event_form" >Time:
                     <input id="event_time" class="event_box" type="text" >
                   </form>
                   </td>
                   <td> <form>
                     <input id="add_event_button" type="button" Value = "Add" />
                   </form>
                   </td>
                 </tr>
             </table>

             <ul id="event_list">
             </ul>
          </div>

          <div id="save_button">
            Save As Draft
          </div>

          <div id="submit_button">
            Submit
          </div>

        </div>
      </div>
      <script src="js/common.js"></script>
      <script src="registration.js"></script>
    </body>
 </html>
