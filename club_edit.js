var getAddedLeaders = function() {
    var ret = [];
    $("#leaders_text_line li").each(function() {
        ret.push($(this).html().split("<")[0]);
    });
    return ret;
};

var registerEditAboutUsPage = function() {
    $("#club_name_in").change(function() {
        $.ajax({
            url: "/scripts/club_query.php",
            type: "GET",
            data: "action=doesClubNameExist&value=" + $(this).val(),
            dataType: "json",
        }).done(function(json) {
            if(json.success === "0") {
                dirty.about_us.club_name = $("#club_name_in").val();
            } else if(!(clubName === $("#club_name_in").val())){
                $("#club_name_in").val("Club Name Already Exists...");
            }
        });
    });
    $("#club_type_line form > input[type='radio']").change(function() {
        dirty.about_us.club_category = $("#club_type_line input:checked").val();
    });
    $("#mission_text_line textarea").change(function() {
        dirty.about_us.club_missionstatement = $(this).html();
    });
    $("#add_button").click(function() {
        var input = $("input[name='add_leader']").val();

        if(getAddedLeaders().contains(input)) { //We already have it
            $("input[name='add_leader']").addClass("failedVerify");
            return;
        }

        var splits = input.split(" ");
        var value1 = "null", value2 = "null";
        if(splits.length == 2) {
            value1 = splits[0];
            value2 = splits[1];
        } else if(splits.length == 3) {
            value1 = splits[0] + "%20" + splits[1];
            value2 = splits[2];
        }
        $.ajax({
            url: "/scripts/registration_verifier.php",
            type: "GET",
            data: "field=add_leader&value1=" + value1 + "&value2=" + value2,
            dataType: "json",
        }).done(function(json) {
            if(json.answer === "1") {
                $("#leaders_text_line ul").append("<li>"+ $("input[name='add_leader']").val() +
                "<input class='X_button' type='button' Value='X' /></li>");
                //Make Button Clickable, And update dirty if is in dirty
                $(".X_button").click(function() {
                    //remove from any "dirty data lists..."
                    for(var i = 0; i < dirty.about_us.club_leaders.length; i++) {
                        if(dirty.about_us.club_leaders[i] === $(this).parent("li").html().split("<")[0]) {
                            dirty.about_us.club_leaders.splice(i, 1);
                        }
                    }
                    $(this).parent("li").fadeOut(200, function() {$(this).remove();});
                });
                //Update Dirty Data
                dirty.about_us.club_leaders.push($("input[name='add_leader']").val());
            } else {
                $("input[name='add_leader']").addClass("failedVerify");
            }
        });
    });
}

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
