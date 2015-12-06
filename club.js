var registerAboutUs = function() {

};

var registerClubEvents = function() {

};

var registerClubFeed = function() {

};

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
    $(".nav ul a:eq(1)").click(function() { //About Us
        $.ajax({
            url: "./club_aboutus.php",
            type: "GET",
            data: "clubId=" + clubId,
        }).done(function(html) {
            $(".content").html(html);
            registerAboutUs();
        });
    });
    $(".nav ul a:eq(2)").click(function() { //Club Events
        $.ajax({
            url: "./club_events.php",
            type: "GET",
            data: "clubId=" + clubId,
        }).done(function(html) {
            $(".content").html(html);
            registerClubEvents();
        });
    });
    $(".nav ul a:eq(3)").click(function() { //Club Feed
        $.ajax({
            url: "./club_feed.php",
            type: "GET",
            data: "clubId=" + clubId,
        }).done(function(html) {
            $(".content").html(html);
            registerClubFeed();
        });
    });
    $("#club_join_button").click(function() {
        var value = $(this).children("li").first().html();
        if(value.trim() === "Join Club") { //Join the club
            $.ajax({
                url: "./scripts/club_query.php",
                type: "GET",
                data: "action=joinClub&value=" + clubId,
            }).done(function() {
                window.location = "club.php?clubId=" + clubId;
            });
        } else if(value.trim() == "Leave Club"){ //Leave the Club
            $.ajax({
                url: "./scripts/club_query.php",
                type: "GET",
                data: "action=leaveClub&value=" + clubId,
            }).done(function() {
                    window.location = "club.php?clubId=" + clubId;
            });
        } else { //Edit the Club
            window.location = "club_edit.php?clubId=" + clubId;
        }
    });
});
