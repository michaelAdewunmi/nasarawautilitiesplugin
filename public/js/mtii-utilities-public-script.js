const $ = jQuery
$("#login_error").css({ 'opacity': 0 }).slideUp();
$("#loginform").css({ 'opacity': 0 });
$("#registerform").css({ 'opacity': 0 });
$("#resetpassform").css({ 'opacity': 0 });
$(".message").css({ 'opacity': 0 }).slideUp();

$(document).ready(function() {

    $('#loginform').css({
        'opacity': '1',
        'transform': 'translate3d(0,0,0)'
    })

    $('#registerform').css({
        'opacity': '1',
        'transform': 'translate3d(0,0,0)'
    })

    $('#lostpasswordform').css({
        'opacity': '1',
        'transform': 'translate3d(0,0,0)'
    })

    $('#resetpassform').css({
        'opacity': '1',
        'transform': 'translate3d(0,0,0)'
    })

    setTimeout(() => {
        $("#login_error").slideDown().css({
            'opacity': 1,
        });
    }, 1500);

    setTimeout(() => {
        $(".message").slideDown().css({
            'opacity': 1,
        });
    }, 1500);

    $("#login_error").slideUp();

    if ($(window).width() > 640) {
        $('#login').css({
            'background': 'rgba(0,0,0,0.7)'
        })
    }

    $(".input").on("focus", function() {
        $("#login_error").slideUp();
    })



})
