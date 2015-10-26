$(document).ready(function() {
    $(".login_menu_hoverable li:first").click(function() { //My Clubs Button
        $(".login_menu_hoverable").hide();
        window.location = "index.php?action=myclubs"; //Redirect to index
    });
    $(".nav a").click(function() {
        if($(this).hasClass("login_nav_bar")) {return;}
        radioify();
        $(this).children().first().addClass("active");
    });
    $(".nav ul a:eq(1)").click(function() { //About Us Editing
        $.ajax({
            url: "club_edit_aboutus.php",
            type: "GET",
            data: "club=" + clubName,
        }).done(function(html) {
            $(".dynamic").html(html);
        });
    });
    $(".nav ul a:eq(2)").click(function() { //Club Edits Editing
        $.ajax({
            url: "club_edit_events.php",
            type: "GET",
            data: "club=" + clubName,
        }).done(function(html) {
            $(".dynamic").html(html);
        });
    });
    $(".nav ul a:eq(3)").click(function() { //Club Feed Editing
        $.ajax({
            url: "club_edit_feed.php",
            type: "GET",
            data: "club=" + clubName,
        }).done(function(html) {
            $(".dynamic").html(html);
        });
    });
    $(".nav ul a:eq(4)").click(function() { //Club Member Editing
        $.ajax({
            url: "club_edit_members.php",
            type: "GET",
            data: "club=" + clubName,
        }).done(function(html) {
            $(".dynamic").html(html);
        });
    });
});
