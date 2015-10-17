var isHoverMenuMousedOver = false;
var hasBeenClickedHoverMenu = false;
var session = {
        authenticated: false,
        username: "",
};

var toggleLogo = function() {
    $(".logo").slideToggle("fast", "linear");
    $(".search_bar").slideToggle("fast", "linear");
    if(!$(this).children().first().hasClass("active")) {
        $(this).children().first().addClass("active");
    } else {
        $(this).children().first().removeClass("active");
    }
};
var radioify = function() {
    $(".nav a").each(function() {
        if($(this).hasClass("search_symbol") || $(this).hasClass("login_nav_bar")) {
            return;
        }
        $(this).children().first().removeClass("active");
    });
};
var makeWidgetsClickable = function() {
    $(".club_widgets a").click(function() {
        var clubName = $(this).children().first().children().first().html();
        $(this).attr("href", "/club.php?n=" + clubName);
    });
}

var mouseEnterLogInMenu = function() {
    $(".login_menu_hoverable").show();
    isHoverMenuMousedOver = true;
}

var registerNavBar = function(name) {
    $(".nav_bar_active").
    mouseenter(mouseEnterLogInMenu).
    mouseleave(function() {
        if(!hasBeenClickedHoverMenu) {
            $(".login_menu_hoverable").hide();
        }
        isHoverMenuMousedOver = false;
    });
    $(".nav_bar_active").click(function() {
        if(!hasBeenClickedHoverMenu) {
            $(".login_menu_hoverable").show();
            hasBeenClickedHoverMenu = true;
        } else {
            $(".login_menu_hoverable").hide();
            hasBeenClickedHoverMenu = false;
        }
    });
}

var checkAuthentication = function(user, pass) {
    $.ajax({
        url: "isAuthenticated.php",
        type: "POST",
        data: "user=" + user + "&pass=" + pass,
        dataType: "json",
    }).done(function(json) {
        if(!json.success) {
            $("#loginStatus").html("Login Attempt Failed!");
        } else {
            session.authenticated = true;
            $(".popOut").hide();
            $(".login_nav_bar").addClass("nav_bar_active");
            $(".login_nav_bar").children().first().removeClass("active");
            $(".login_nav_bar > li").html("Hello, " + json.preferred_name);
            registerNavBar();
        }
    });
}

$(document).ready(function() {
    $(".search_bar").hide();
    $(".popOut").hide();
    $(".login_menu_hoverable").hide();
    $.ajax({
        url: "/scripts/clubs_searcher.php",
        type: "GET",
        data: 'a=catsearch&v=All',
    }).done(function(html) {
        $(".club_widgets ul").html(html);
        makeWidgetsClickable();
    });
    $(".nav a").click(function() {
        if($(this).hasClass("search_symbol") || $(this).hasClass("login_nav_bar")) {return;}
        radioify();
        $(this).children().first().addClass("active");
        //Send a Query to the Database demanding new data
        var value = $(this).children().first().html();
        var x = 'a=catsearch' + '&v=' + value; //a stands for action, v stands for value
        $.ajax({
            url: "/scripts/clubs_searcher.php",
            type: "GET",
            data: x,
        }).done(function(html) {
            $(".club_widgets ul").html(html);
            makeWidgetsClickable();
        });
    });
    $(".search_symbol").click(toggleLogo);
    $(".login_nav_bar").click(function() {
        if(!$(this).children().first().hasClass("active") && ($(this).children().first().html().indexOf("Log In") >= 0)) {
            $(this).children().first().addClass("active");
            $(".popOut").show();
        } else {
            $(this).children().first().removeClass("active");
            $(".popOut").hide();
        }
    });
    $(".login_menu_hoverable li:last").click(function() { //Log Out Button
        $.ajax({
            url: "logout.php",
            type: "POST",
        });
        window.location = "index.php";
    });
    if($(".login_nav_bar").children().first().html().trim().indexOf("Hello,") >= 0) { //if we have a real name in the nav bar
        $(".login_nav_bar").addClass("nav_bar_active");
        registerNavBar();
    }
    $("input[name='loginButton']").click(function() {
        var user = $("input[name='user']").val();
        var pass = $("input[name='pass']").val();
        checkAuthentication(user, pass);
    });
});
