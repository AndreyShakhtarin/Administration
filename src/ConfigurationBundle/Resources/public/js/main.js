var url = window.location.pathname, link;
link = url.replace('/app_dev.php/', '');
$(document).ready(function () {
    id = $('#' + link);
    id.addClass('box-shadows');
    id.children('a').append('<i class="icon-arrow_big_left cl-olivie"></i>');
})