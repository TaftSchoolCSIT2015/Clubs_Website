function EditPageState(isThePageChangedLocally, updateTheDOMWithDirties, registerJQuery) {
    this.isPageChangedLocally = isThePageChangedLocally;
    this.updateDOMWithDirties = updateTheDOMWithDirties;
    this.registerJavascript = registerJQuery;
    this.doNewRenderCycle = function() {
        if(this.isPageChangedLocally()) {
            this.updateDOMWithDirties();
        }
        this.registerJavascript();
    };
}

function EventMetadata(title, location, date, time, updateId) {
    this.title = title;
    this.location = location;
    this.date = date;
    this.time = time;
    this.updateId = updateId;
}

var isAboutUsPageChangedLocally = function() {
    var about_us_obj = dirty.about_us;
    for(var a in about_us_obj) {
        if(about_us_obj.hasOwnProperty(a)) {
            var val = about_us_obj[a];
            if(!(val === null) && !(Array.isArray(val) && val.length === 0)) {
                return true;
            }
        }
    }
    return false;
};

var updateAboutUsDom = function() {
    var about_us_obj = dirty.about_us;
    if(!(about_us_obj.clubname === null)) { //Update Club Name
        $("#club_name_in").val(about_us_obj.club_name);
    }
    if(!(about_us_obj.club_leaders === [])) { //Update Club Leaders
        for(var i = 0; i < about_us_obj.club_leaders.length; i++) {
            var html = "<li>" + about_us_obj.club_leaders[i] +
            "<input class='X_button' type='button' value='X'></li>";
            $("#leaders_text_line ul").append(html);
        }
    }
    if(!(about_us_obj.club_category === null)) { //Update Club Category
        var elem = $("#club_type_line form").html();
        elem = elem.replace('checked=""', "");
        var splits = elem.split("<input");
        var len = about_us_obj.club_category.length;
        var newHtml = "";
        for(var j in splits) {
            if(splits.hasOwnProperty(j)) {
                if(splits[j].indexOf(about_us_obj.club_category) >= 0) {
                    var first = splits[j].slice(0, splits[j].indexOf(about_us_obj.club_category) + len + 1);
                    newHtml += (first + " checked>" + about_us_obj.club_category + "<input");
                } else {
                    newHtml += splits[j] + "<input";
                }
            }
        }
        $("#club_type_line form").html(newHtml);
    }
    if(!(about_us_obj.club_missionstatement === null)) { //Update Club Mission Statement
        $("#mission_box").html(about_us_obj.club_missionstatement);
    }
};

var getAddedLeaders = function() {
    var ret = [];
    $("#leaders_text_line li").each(function() {
        ret.push($(this).html().split("<")[0]);
    });
    return ret;
};

var registerXButtons = function() {
    $(".X_button").click(function() {
        var leaderName = $(this).parent("li").html().split("<")[0];
        if($(this).parent("li").css("text-decoration") === "line-through") { //is already striken through
            //Add to list of regular leaders to add
            dirty.about_us.club_leaders.push(leaderName);
            //iterate through deleted leaders and swap the values via the splice function
            for(var i = 0; i < dirty.about_us.deleted_leaders.length; i++) {
                if(dirty.about_us.deleted_leaders[i] === leaderName) {
                    dirty.about_us.deleted_leaders.splice(i, 1);
                    break;
                }
            }
            //Take away strikethrough
            $(this).parent("li").css("text-decoration", "none");
        } else {
            //Add to the deleted leaders list because we are now "deleted"
            dirty.about_us.deleted_leaders.push(leaderName);
            //FIND THE DIRTY DATA AND REMOVE IT
            for(var i = 0; i < dirty.about_us.club_leaders.length; i++) {
                if(dirty.about_us.club_leaders[i] === leaderName) {
                    dirty.about_us.club_leaders.splice(i, 1);
                    break;
                }
            }
            //Add strikethrough effect
            $(this).parent("li").css("text-decoration", "line-through");
        }
    });
};

var registerEditAboutUsPage = function() {
    $("#club_name_in").change(function() {
        $.ajax({
            url: "./scripts/club_query.php",
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
    $("#mission_box").change(function() {
        dirty.about_us.club_missionstatement = $(this).val();
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
            url: "./scripts/registration_verifier.php",
            type: "GET",
            data: "field=add_leader&value1=" + value1 + "&value2=" + value2,
            dataType: "json",
        }).done(function(json) {
            if(json.answer === "1") {
                $("#leaders_text_line ul").append("<li>"+ $("input[name='add_leader']").val() +
                "<input class='X_button' type='button' Value='X' /></li>");
                //Make Button Clickable, And update dirty if is in dirty
                registerXButtons();
                //Update Dirty Data
                dirty.about_us.club_leaders.push($("input[name='add_leader']").val());
            } else {
                $("input[name='add_leader']").addClass("failedVerify");
            }
        });
    });
    $("div#file_upload").dropzone({
                                url: "./scripts/image_uploader.php",
                                maxFilesize: 5,
                                paramName: "file",
                                maxFiles: 1,
                                acceptedFiles: "image/*",
                                previewsContainer: null,
                                method: "post",
                            }).addClass("dropzone");
    registerXButtons();
}

var isEditEventsPageChangedLocally = function() {
    if(dirty.events.length > 0 || dirty.deleted_events.length > 0) {
        return true;
    }
    return false;
};

var getIndexForEventId = function(eventId) {
    for(var i = 0; i < dirty.events.length; i++) {
        if(dirty.events[i].updateId === eventId) {
            return i;
        }
    }
    return -1;
};

var registerEventXButton = function() {
    $(".X_button").click(function() {
        //remove from any "dirty data lists..."
        //alert($(this).parents("tr").css("border-color"));
        if($(this).parents("tr").css("border-color") != "rgb(255, 0, 0)") { //Clean, not striken through
            var updateIndex = $(this).parents("tr").data("index");
            //Add to dirty array
            dirty.deleted_events.push(updateIndex);
            //remove from clean array
            var index = getIndexForEventId(updateIndex);
            if(index > -1) {
                dirty.events.splice(index, 1);
            }
            //Add Styling
            $(this).parents("tr").css("border-color", "red");
        } else {
            var updateIndex = $(this).parents("tr").data("index");
            //remove from dirty array
            dirty.deleted_events = dirty.deleted_events.filter(function(element, index, array) {
                return (element !== updateIndex);
            });
            var eventId = $(this).parents("tr").data("index");
            var eventTitle = $(this).parents("tr").children(":eq(0)").children("input:text").val();
            var eventLoc = $(this).parents("tr").children(":eq(1)").children("input:text").val();
            var eventDate = $(this).parents("tr").children(":eq(2)").children("input").val();
            var eventTime = $(this).parents("tr").children(":eq(3)").children("input").val();
            var event = new EventMetadata(eventTitle, eventLoc, eventDate, eventTime, eventId);
            dirty.events.push(event);
            $(this).parents("tr").css("border-color", "black");
        }

    });
};

var getStringForEvent = function(value, type) {
    return "<td><input class='eventEdit' type='" + type + "' value='" + value + "'></td>";
};

var eventIdCounter = 0;

var registerEditEventsPage = function() {
    $(".eventEdit").bind('input', function() {
        var eventId = $(this).parents("tr").data("index");
        var eventTitle = $(this).parents("tr").children(":eq(0)").children("input:text").val();
        var eventLoc = $(this).parents("tr").children(":eq(1)").children("input:text").val();
        var eventDate = $(this).parents("tr").children(":eq(2)").children("input").val();
        var eventTime = $(this).parents("tr").children(":eq(3)").children("input").val();
        var event = new EventMetadata(eventTitle, eventLoc, eventDate, eventTime, eventId);
        var index = getIndexForEventId(eventId);
        if(index > -1) { //replace
            dirty.events[index] = event;
        } else { //add new
            dirty.events.push(event);
        }
    });
    $("#add_event_button").click(function() {
        var eventTitle = $("#event_title").val();
        var eventLoc = $("#event_loc").val();
        var eventDate = $("#event_date").val();
        var eventTime = $("#event_time").val();
        var event = new EventMetadata(eventTitle, eventLoc, eventDate, eventTime, --eventIdCounter);
        $("#events").append("<tr data-index='" + eventIdCounter + "'>" + getStringForEvent(eventTitle, "text") +
        getStringForEvent(eventLoc, "text") + getStringForEvent(eventDate, "date") + getStringForEvent(eventTime, "time") +
        "<td>0%</td><td><input class='X_button' type='button' value='X'></td></tr>");
        dirty.events.push(event);
        registerEventXButton();
    });
    registerEventXButton();
};

var getEventMetadataForUpdateId = function(eventId) {
    var i = getIndexForEventId(eventId);
    if(i > -1) {
        return dirty.events[i];
    }
    return null;
};

var updateEventsDom = function() {
    var events = dirty.events;
    var del_events = dirty.deleted_events;
    $("#events tr").each(function() {
        var index = $(this).data("index");
        if(index === null) {
            return;
        }
        if(dirty.deleted_events.contains(index)) {
            $(this).remove();
            return;
        }
        var metadata = getEventMetadataForUpdateId(index);
        if(!(metadata === null)) { //update stuff
            $(this).html(getStringForEvent(metadata.title, "text") + getStringForEvent(metadata.location, "text") +
                getStringForEvent(metadata.date, "date") + getStringForEvent(metadata.time, "time") +
                "<td>0%</td><td><input class='X_button' type='button' value='X'></td>");
                registerEventXButton();
        }
    });
    for(var j = 0; j < events.length; j++) {
        if(events[j].updateId < 0) { //append
            $("#events").append("<tr data-index='" + events[j].updateId + "'>" + getStringForEvent(events[j].title, "text") +
            getStringForEvent(events[j].location, "text") + getStringForEvent(events[j].date, "date") + getStringForEvent(events[j].time, "time") +
            "<td>0%</td><td><input class='X_button' type='button' value='X'></td></tr>")
        }
    }

};

var PageStates = {
    About_Us: new EditPageState(isAboutUsPageChangedLocally, updateAboutUsDom, registerEditAboutUsPage),
    Events: new EditPageState(isEditEventsPageChangedLocally, updateEventsDom, registerEditEventsPage),
};

var pushChangesToDatabase = function(action) {
    if(action == "Draft") {
        dirty.request_type = "editdraft";
    } else {
        dirty.request_type = "editsubmission";
    }
    $.ajax({
        url: "./scripts/club_edit.php",
        type: "POST",
        data: JSON.stringify(dirty),
        contentType: "application/json",
        processData: false,
    }).done(function(json) {
        window.location = "club_edit.php?clubId=" + clubId;
    });
};

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
            url: "./club_edit_aboutus.php",
            type: "GET",
            data: "clubId=" + clubId,
        }).done(function(html) {
            $(".dynamic").html(html);
            PageStates.About_Us.doNewRenderCycle();
        });
    });
    $(".nav ul a:eq(2)").click(function() { //Club Edits Editing
        $.ajax({
            url: "./club_edit_events.php",
            type: "GET",
            data: "clubId=" + clubId,
        }).done(function(html) {
            $(".dynamic").html(html);
            PageStates.Events.doNewRenderCycle();
        });
    });
    $(".nav ul a:eq(3)").click(function() { //Club Feed Editing
        $.ajax({
            url: "./club_edit_feed.php",
            type: "GET",
            data: "clubId=" + clubId,
        }).done(function(html) {
            $(".dynamic").html(html);
        });
    });
    $(".nav ul a:eq(4)").click(function() { //Club Member Editing
        $.ajax({
            url: "./club_edit_members.php",
            type: "GET",
            data: "clubId=" + clubId,
        }).done(function(html) {
            $(".dynamic").html(html);
        });
    });
    $("#save_button").click(function() {
        pushChangesToDatabase("Draft");
    });
    $("#submit_button").click(function() {
        pushChangesToDatabase("Submit");
    });
});
