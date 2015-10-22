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
      <link rel="stylesheet" type="text/css" href="style/common.css">
      <link rel="stylesheet" type="text/css" href="stylesheet2.css">
      <script src="js/jquery-2.1.4.min.js"></script>
    </head>
    <body>
      <div class="header">
          <div class="top_bar">
              <div class="title"><span>Club Registration</span></div>
          </div>
          <div class="nav">
              <ul>
                  <a href="index.php"><li>Home</li></a>
                  <a href="resources/How_to_write_a_mission_statement.pdf"><li>How to Write a Mission Statement</li></a>
                  <a class="login_nav_bar"><li>
                      <?php
                          getInputToLoginMenu($conn);
                      ?>
                  </li>
                      <ul class="login_menu_hoverable">
                          <li>My Clubs</li>
                          <li>Make A New Club</li>
                          <li>Log Out</li>
                      </ul>
                  </a>
              </ul>
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
                 <input  id="add_leader_text" name="add_leader" type="text">
                 <input id="add_button" name="add_button" type="button" Value="Add Leader">
               </form>

               <ul>
                    <?php
                        if(isset($_SESSION['name'])) {
                    ?>
                    <li><?php echo $_SESSION['name']?><input class="X_button" type="button" Value="X" disabled></li>
                    <?php } ?>
               </ul>
          </div>

          <div id="club_type_line">
              <form><h3>Club Category:</h3>
                <input type="radio" name="category" value="Academic" checked>Academic
                <input type="radio" name="category" value="Athletic">Athletic
                <input type="radio" name="category" value="Volunteer">Volunteer
                <input type="radio" name="category" value="Fan">Fan
                <input type="radio" name="category" value="Recreational">Recreational
              </form>
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
                     <input id="event_title" class="event_box" type="text">
                   </form>
                   </td>
                   <td> <form class="event_form" >Location:
                     <input id="event_loc" class="event_box" type="text">
                   </form>
                   </td>
                   <td> <form class="event_form" >Date:
                     <input id="event_date" class="event_box" type="date">
                   </form>
                   </td>
                   <td> <form class="event_form" >Time:
                     <input id="event_time" class="event_box" type="text">
                   </form>
                   </td>
                   <td> <form>
                     <input id="add_event_button" type="button" Value = "Add">
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
