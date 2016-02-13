var toggleLogo = function() {
    $(".logo").slideToggle("fast", "linear");
    $(".search_bar").slideToggle("fast", "linear");
    if(!$(this).children().first().hasClass("active")) {
        $(this).children().first().addClass("active");
    } else {
        $(this).children().first().removeClass("active");
    }
};

$(document).ready(function() {
    $(".search_bar").hide();
    var queryAction = (loadMyClubs) ? "userclub" : "catsearch";
    $.ajax({
        url: "./scripts/clubs_searcher.php",
        type: "GET",
        data: 'a=' + queryAction + '&v=All',
    }).done(function(html) {
        $(".club_widgets ul").html(html);
        $(".club_widgets a").mouseenter(function() {
            $(this).children("li").children("p, h1").addClass("club_widgets_hovered");
        }).mouseleave(function() {
            $(this).children("li").children("p, h1").removeClass("club_widgets_hovered");
        });
    });
    $(".nav a").click(function() {
        if($(this).hasClass("search_symbol") || $(this).hasClass("login_nav_bar")) {return;}
        radioify();
        $(this).children().first().addClass("active");
        //Send a Query to the Database demanding new data
        var value = $(this).children().first().html();
        var x = 'a=catsearch' + '&v=' + value; //a stands for action, v stands for value
        $.ajax({
            url: "./scripts/clubs_searcher.php",
            type: "GET",
            data: x,
        }).done(function(html) {
            $(".club_widgets ul").html(html);
            $(".club_widgets a").mouseenter(function() {
                $(this).children("li").children("p, h1").addClass("club_widgets_hovered");
            }).mouseleave(function() {
                $(this).children("li").children("p, h1").removeClass("club_widgets_hovered");
            });
        });
    });
    $("")
    $(".login_menu_hoverable li:first").click(function() { //My Clubs Button
        $(".login_menu_hoverable").hide();
        $.ajax({
            url: "./scripts/clubs_searcher.php",
            type: "GET",
            data: 'a=userclub&v=All',
        }).done(function(html) {
            $(".club_widgets ul").html(html);
            $(".club_widgets a").mouseenter(function() {
                $(this).children("li").children("p, h1").addClass("club_widgets_hovered");
            }).mouseleave(function() {
                $(this).children("li").children("p, h1").removeClass("club_widgets_hovered");
            });
        });
    });
    $(".search_symbol").click(toggleLogo);
});
