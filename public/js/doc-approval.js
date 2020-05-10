(function( $ ) {
    'use strict';

    $(window).load(function() {
        $(".close-notify").on('click', closeNotifier);
        $(".doc-apprv-btn").on('click', ApprovalDocViaAjax);
        $(".doc-decl-btn").on('click', DisapproveDocViaAjax);


    })

    function showTheLoader() {
		const showLoader = `<div class="lds-ring"><div></div><div></div><div></div><div></div></div>
			<p class="info">Please Wait...while Approval is granted</p>`;
		openNotifier(true, showLoader);
	}

    function ApprovalDocViaAjax(e) {
        e.preventDefault();
        var mainParent;
        if ($(e.target).hasClass('is-biz-prem')) {
            mainParent = $(e.target).parent().parent();
        } else {
            mainParent = $(e.target).parent().parent().parent().parent();
        }
        const showLoaderAndMsg = `<div class="lds-ring"><div></div><div></div><div></div><div></div></div>
            <p class="info">Please Wait...while we approve Document</p>`
        openNotifier(true, showLoaderAndMsg);

		const approval_nonce = $("#mtii-doc-nonce").val();
        const doc_id = mainParent.find('.mtii-doc-id').val();
        const doc_title = mainParent.find('.mtii-doc-title').val();
        const reg_catg = $("#reg_catg").val();
        const data = { action: "mtii_signed_doc_approval", doc_id, approval_nonce, doc_title, reg_catg };
        console.log(data);
        console.log(myAjax.ajaxurl);
        $.ajax({
            type : "post",
            dataType : "json",
            url : myAjax.ajaxurl,
            data,
            success: function(response) {
                console.log(response);
                if(response.status == "success") {
                    let msg = `<p class="success">${response.info}</p>`;
                    openNotifier(false, msg);
                    console.log(response);
                } else {
                    let msg = `<p class="error">${response.info}</p>`;
                    openNotifier(false, msg);
                    console.log("another", response);
                    //window.location.reload();
                }
            }
        })
    }

    function DisapproveDocViaAjax(e) {
        e.preventDefault();
        var mainParent;
        if ($(e.target).hasClass('is-biz-prem')) {
            mainParent = $(e.target).parent().parent();
        } else {
            mainParent = $(e.target).parent().parent().parent().parent();
        }
        const showLoaderAndMsg = `<div class="lds-ring"><div></div><div></div><div></div><div></div></div>
            <p class="info">Please Wait...while we decline Registration</p>`
        openNotifier(true, showLoaderAndMsg);

		const approval_nonce = $("#mtii-doc-nonce").val();
        const doc_id = mainParent.find('.mtii-doc-id').val();
        const doc_title = mainParent.find('.mtii-doc-title').val();
        const reg_catg = $("#reg_catg").val();
        const data = { action: "mtii_signed_doc_disapproval", doc_id, approval_nonce, doc_title, reg_catg };
        console.log(data);
        $.ajax({
            type : "post",
            dataType : "json",
            url : myAjax.ajaxurl,
            data,
            success: function(response) {
                console.log(response);
                if(response.status == "success") {
                    let msg = `<p class="success">${response.info}</p>`;
                    openNotifier(false, msg);
                    console.log(response);
                } else {
                    let msg = `<p class="error">${response.info}</p>`;
                    openNotifier(false, msg);
                    console.log("another", response);
                    //window.location.reload();
                }
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
})( jQuery );