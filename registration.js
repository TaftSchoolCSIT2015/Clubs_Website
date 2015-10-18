var getAddedLeaders = function() {
    var ret = new Array();
    $("#leaders_text_line li").each(function() {
        ret.push($(this).html().split("<")[0]);
    });
    return ret;
};

var registerXButtons = function() {
    $(".X_button").click(function() {
        $(this).parent("li").fadeOut(200, function() {$(this).remove();});
    });
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
            url: "/scripts/registration_verifier.php",
            type: "GET",
            data: "field=add_leader&value1=" + value1 + "&value2=" + value2,
            dataType: "json",
        }).done(function(json) {
            if(json.answer === "1") {
                $("#leaders_text_line ul").append("<li>"+ $("input[name='add_leader']").val() +
                "<input class='X_button' type='button' Value='X' /></li>");
                registerXButtons();
            }
        });
    });
    registerXButtons();
});
