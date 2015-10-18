var isHoverMenuMousedOver = false;
var hasBeenClickedHoverMenu = false;
var session = {
        authenticated: false,
        username: "",
};

Array.prototype.contains = function(arg) {
    if(this.length == 0) {
        return false;
    }
    for(var i = 0; i < this.length; i++) {
        if(this[i] === arg) {
            return true;
        }
    }
    return false;
}

var radioify = function() {
    $(".nav a").each(function() {
        if($(this).hasClass("search_symbol") || $(this).hasClass("login_nav_bar")) {
            return;
        }
        $(this).children().first().removeClass("active");
    });
};

var registerNavBar = function(name) {
    $(".nav_bar_active").
    mouseenter(function() {
        $(".login_menu_hoverable").show();
        isHoverMenuMousedOver = true;
    }).
    mouseleave(function() {
        if(!hasBeenClickedHoverMenu) {
            $(".login_menu_hoverable").hide();
        }
        isHoverMenuMousedOver = false;
    });
};

var checkAuthentication = function(user, pass) {
    $.ajax({
        url: "/scripts/authenticate.php",
        type: "POST",
        data: "user=" + user + "&pass=" + pass,
        dataType: "json",
    }).done(function(json) {
        if(!json.success) {
            $("#loginStatus").html("Login Attempt Failed!");
        } else {
            session.authenticated = true;
            session.username = json.username;
            $(".popOut").hide();
            $(".login_nav_bar").addClass("nav_bar_active");
            $(".login_nav_bar").children().first().removeClass("active");
            $(".login_nav_bar > li").html("Hello, " + json.preferred_name);
            registerNavBar();
        }
    });
};

$(document).ready(function() {
    //Hide the pop out login menu and the dropdown from the login
    $(".popOut").hide();
    $(".login_menu_hoverable").hide();
    //To toggle the popping up of the login
    $(".login_nav_bar").click(function() {
        if(!$(this).children().first().hasClass("active") && ($(this).children().first().html().indexOf("Log In") >= 0)) {
            $(this).children().first().addClass("active");
            $(".popOut").show();
        } else {
            $(this).children().first().removeClass("active");
            $(".popOut").hide();
        }
    });
    $(".login_menu_hoverable").children("li:nth-child(2)").click(function() { //Make a new club button
        window.location = "../registration2.html";
    });
    $(".login_menu_hoverable li:last").click(function() { //Log Out Button
        $.ajax({
            url: "/scripts/logout.php",
            type: "POST",
        }).done(function(response) {
            session.authenticated = false;
            session.username = "";
            window.location = "../index.php";
        });
    });
    //Detect authentication
    if($(".login_nav_bar").children().first().html() != null && $(".login_nav_bar").children().first().html().trim().indexOf("Hello,") >= 0) { //if we have a real name in the nav bar
        session.authenticated = true;
        session.username = $(".login_nav_bar").children().first().html().trim().substring(7);
        $(".login_nav_bar").addClass("nav_bar_active");
        registerNavBar();
    }
    //Check authentication from pop out box
    $("input[name='loginButton']").click(function() {
        var user = $("input[name='user']").val();
        var pass = $("input[name='pass']").val();
        checkAuthentication(user, pass);
    });
});
