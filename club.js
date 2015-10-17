$(document).ready(function() {
    $(".nav a").click(function() {
        if($(this).hasClass("login_nav_bar")) {return;}
        radioify();
        $(this).children().first().addClass("active");
    });
    $(".login_menu_hoverable li:last").click(function() { //Log Out Button
        $.ajax({
            url: "logout.php",
            type: "POST",
        }).done(function(response) {
            window.location = "index.php";
        });
    });
});
