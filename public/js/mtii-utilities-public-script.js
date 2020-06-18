const $ = jQuery
$("#login_error").css({ 'opacity': 0 }).slideUp();
$("#loginform").css({ 'opacity': 0 });
$("#registerform").css({ 'opacity': 0 });
$("#resetpassform").css({ 'opacity': 0 });
$(".message").css({ 'opacity': 0 }).slideUp();

$(document).ready(function() {
    $(".close-notify").on('click', closeNotifier);
    $("#go-reg").on('click', GoToRegistrationPage);

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

    function GoToRegistrationPage(e) {
        //console.log(e.target);
        e = e || window.event;
        e.preventDefault();
        const ajaxOn = localStorage.getItem("mtii_is_on_ajax");
        if(ajaxOn==="yes") {
            return;
        }

        console.log(e.target.dataset.orgsource);

        const showLoaderAndMsg = `<div class="lds-ring dlarge"><div></div><div></div><div></div><div></div></div>
            <p class="info">Please wait while we confirm payment...</p>`
        openNotifier(true, showLoaderAndMsg);

        const ajax_nonce = e.target.dataset.thenonce;
        const org_source = e.target.dataset.orgsource;
        const data = { action: 'verify_inv_payment_for_reg', ajax_nonce, org_source };
        console.log(data);
        localStorage.setItem("mtii_is_on_ajax", "yes");

        $.ajax({
            type : "post",
            dataType : "json",
            url : `${varsMtii.siteBaseUrl}/mtii-ajax-for-sub`,
            data,
            //http://thegov.local/user-dashboard/?do=succ&succ=gotopay&invnum=Y%2FQBUQ2Dl%2BudXyKn67T3yQ%3D%3D
            success: function(response) {
                console.log(response);
                if(response.status == "success") {
                    let msg = `<div class="lds-ring"><div></div><div></div><div></div><div></div></div>
                    <p style="color: green;">${response.info}</p>`;
                    openNotifier(false, msg);
                    if (response.for_payment_redirect=="true") {
                        window.location.href = `${varsMtii.siteBaseUrl}/user-dashboard?do=reg&org_source=${response.org_source}`
                    }
                    console.log(response);
                } else {
                    const dbtn = `<p id="go-reg-ajax-btn" onclick="GoToRegistrationPage()" data-orgsource="${response.org_source}"
                    data-thenonce="${response.new_nonce}" class="round-btn-mtii blue-bg">Try Again</p>`;
                    let msg = `<p class="error" style="color: red;">${response.info}<br />${dbtn}</p>`;
                    openNotifier(false, msg);
                    console.log("another", response);
                }
            },
            complete: function() {
                localStorage.removeItem("mtii_is_on_ajax")
            }
        })
    }

    function openNotifier(hideBtn=false, msg) {
		$("#msg").html(msg);
        $("#notification").css({
            'opacity' : '1',
            'z-index': '99999999999999999999999999999999999999999'
        });

        $(".notifier").css({
            'transform' : 'scaleY(1)',
        });
        if(!hideBtn) {
            $("#notification-btn").show();
        } else {
            $("#notification-btn").hide();
		}
    }

    function closeNotifier() {
        $("#notification").css({
            'opacity' : '0',
            'z-index': '-20',
        });
        $(".notifier").css({ 'transform' : 'scaleY(0)' });
        $(window).scrollTop(0);
        $(this).html("Close Notification");
		$("#msg").html("");
	}
