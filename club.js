$(document).ready(function() {
    $(".nav a").click(function() {
        if($(this).hasClass("login_nav_bar")) {return;}
        radioify();
        $(this).children().first().addClass("active");
    });

    $(".login_menu_hoverable li:first").click(function() { //My Clubs Button
        $(".login_menu_hoverable").hide();
        window.location = "index.php?action=myclubs"; //Redirect to index
    });
    $("#club_join_button").click(function() {
        var value = $(this).children("li").first().html();
        if(value.trim() === "Join Club") { //Join the club
            $.ajax({
                url: "/scripts/club_query.php",
                type: "GET",
                data: "action=joinClub&value=" + clubName,
            }).done(function() {
                window.location = "club.php?n=" + clubName;
            });
        } else if(value.trim() == "Leave Club"){ //Leave the Club
            $.ajax({
                url: "/scripts/club_query.php",
                type: "GET",
                data: "action=leaveClub&value=" + clubName,
            }).done(function() {
                    window.location = "club.php?n=" + clubName;
            });
        } else {
            /*TODO::*/
        }
    });
});
