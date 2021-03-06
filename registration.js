var getAddedLeaders = function() {
    var ret = [];
    $("#leaders_text_line li").each(function() {
        ret.push($(this).html().split("<")[0]);
    });
    return ret;
};

var getAddedEvents = function() {
    var ret = [];
    $("#event_list li").each(function() {
        ret.push($(this).html().split("<")[0]);
    });
    return ret;
};

var registerXButtons = function() {
    $(".X_button").click(function() {
        $(this).parent("li").fadeOut(200, function() {$(this).remove();});
    });
};

var getCategory = function() {
    return $("#club_type_line input:checked").val();
};

var getInputtedData = function() {
    var ret = {};
    ret.title = $("#club_name_in").val();
    ret.leaders = getAddedLeaders();
    ret.faculty_advisor = $("#faculty_advisor_in").val();
    ret.mission_statement = $("#mission_box").val();
    ret.events = getAddedEvents();
    ret.category = getCategory();
    return ret;
};

$(document).ready(function() {
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
            url: "./scripts/registration_verifier.php",
            type: "GET",
            data: "field=add_leader&value1=" + value1 + "&value2=" + value2,
            dataType: "json",
        }).done(function(json) {
            if(json.answer === "1") {
                $("#leaders_text_line ul").append("<li>"+ $("input[name='add_leader']").val() +
                "<input class='X_button' type='button' value='X'></li>");
                registerXButtons();
            } else {
                $("input[name='add_leader']").addClass("failedVerify");
            }
        });
    });
    $("#add_event_button").click(function() {
        var error = false;
        var title = $("#event_title");
        var loc = $("#event_loc");
        var date = $("#event_date");
        var time = $("#event_time");
        if(title.val().length == 0) {
            title.addClass("failedVerify");
            error = true;
        }
        if(loc.val().length == 0) {
            loc.addClass("failedVerify");
            error = true;
        }
        if(time.val().length == 0) {
            time.addClass("failedVerify");
            error = true;
        }
        if(!error) {
            $("#event_list").append("<li>" + title.val() + ", " + loc.val() +
            ", " + date.val() + ", " + time.val() + "<input class='X_button' type='button' value='X'></li>");
            registerXButtons();
            $("#event_title").val("");
            $("#event_loc").val("");
            $("#event_date").val("");
            $("#event_time").val("");
        }
    });
    $("input[name='add_leader']").change(function() {
        $(this).removeClass("failedVerify");
    });
    $("#save_button").click(function() {
        var fields = getInputtedData();
        fields.club_status = 1; //1=Draft
        fields.request_type = "savedraft";
        $.ajax({
            url: "./scripts/club_edit.php",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(fields),
            processData: false,
        }).done(function(response) {
                    alert(fields);
            window.location = "index.php";
        });
    });
    $("#submit_button").click(function() {
        var fields = getInputtedData();
        if(fields.events.length < 3) {
            alert("Must have more than 3 events to submit a club");
        } else {
            fields.club_status = 2; //2 = Awaiting faculty approval
            fields.request_type = "submit_registration";
            $.ajax({
                url: "./scripts/club_edit.php",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify(fields),
                processData: false,
            }).done(function(response) {
                window.location = "index.php";
            });
        }
    });
    $(".login_menu_hoverable li:first").click(function() { //My Clubs Button
        $(".login_menu_hoverable").hide();
        window.location = "index.php?action=myclubs"; //Redirect to index
    });
    registerXButtons();
});
