// toggle sidebar
$('#show_hide_sidebar_btn').on('click', function () {
    $("#sidebar").toggle();
    let isVisible = $("#sidebar").is(":visible");


                    // if sidebar is visible make main_content size to 10 else to 12 [full]
                    if (isVisible == true) {
                        $("#main_content").removeClass("col-12 col-sm-12 col-md-12 col-lg-12");
                        $("#main_content").addClass("col-12 col-sm-12 col-md-10 col-lg-10");
                    } else {
                        $("#main_content").removeClass("col-12 col-sm-12 col-md-10 col-lg-10");
                        $("#main_content").addClass("col-12 col-sm-12 col-md-12 col-lg-12");
                    }

    
});