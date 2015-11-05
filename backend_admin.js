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
});
