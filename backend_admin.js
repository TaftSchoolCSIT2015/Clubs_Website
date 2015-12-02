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
    $(".nav a").eq(2).click(function() { //Club Edits Nav
        $.ajax({
            url: "backend_admin_clubedits.php",
            type: "GET",
        }).done(function(html) {
            $(".content").html(html);
            registerClubEdits();
        })
    })
    $(".nav a").eq(3).click(function() { //List of Approved Clubs Nav
        window.open("backend_admin_approvedclubslist.php", '_blank');
    });
});

var registerDBUpdateLinks = function() {
    //Get links
    $("#approvedClubsTable tbody > tr").each(function() {
        $(this).children("td").eq(4).children("a").click(function() {
            var dbIndex = $(this).data("index");
            if($(this).html() === "Approve Club?") {
                $.ajax({
                    type: "GET",
                    url: "scripts/club_query.php",
                    data: "action=adminApproveClub&value=" + dbIndex,
                }).done(function(json) {
                    window.location = "backend_admin.php";
                });
            } else if($(this).html() === "Delete Club?") {
                $.ajax({
                    type: "GET",
                    url: "scripts/club_query.php",
                    data: "action=adminDeleteClub&value=" + dbIndex,
                }).done(function(json) {
                    window.location = "backend_admin.php";
                })
            } else if($(this).html() === "Reject Club?") {
                $.ajax({
                    type: "GET",
                    url: "scripts/club_query.php",
                    data: "action=adminRejectClub&value=" + dbIndex,
                }).done(function() {
                    window.location = "backend_admin.php";
                });
            }
        });
    });
};

var registerClubApplications = function() {
    $("select").change(function() {
        var value = $(this).val();
        $.ajax({
            url: "scripts/clubs_searcher.php",
            type: "GET",
            data: "a=adminsearch&v=" + value,
            dataType: "html",
        }).done(function(html) {
            $("#approvedClubsTable tbody").html(html);
            registerDBUpdateLinks();
        });
    });
    registerDBUpdateLinks();
};

var registerClubEdits = function() {

};
