// prevent form submission when pressing enter button
$(window).keydown(function(event) {
    if (event.keyCode == 13) {
        event.preventDefault();
        return false;
    }
});