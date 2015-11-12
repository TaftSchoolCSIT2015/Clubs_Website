$(document).ready(function() {
    //Radiofy the Nav bar
    $(".nav a").click(function() {
        if($(this).hasClass("login_nav_bar")) {return;}
        radioify();
        $(this).children().first().addClass("active");
    });

    //Make "My Clubs" button on hoverable work
    $(".login_menu_hoverable li:first").click(function() { //My Clubs Button
        $(".login_menu_hoverable").hide();
        window.location = "index.php?action=myclubs"; //Redirect to index
    });

    $(".nav a").eq(1).click(function() { //Club Applications Nav
        $.ajax({
            url: "backend_admin_approveclubs.php",
            type: "GET",
        }).done(function(html) {
            $(".content").html(html);
            registerClubApplications();
        });
    });
    $(".nav a").eq(3).click(function() { //List of Approved Clubs Nav
        window.open("backend_admin_approvedclubslist.php", '_blank');
    });
});

var registerClubApplications = function() {
    $("select").change(function() {
        var value = $(this).val();
        $.ajax({
            url: "/scripts/clubs_searcher.php",
            type: "GET",
            data: "a=adminsearch&v=" + value,
            dataType: "html",
        }).done(function(html) {
            $("#approvedClubsTable tbody").html(html);
        });
    });
};
