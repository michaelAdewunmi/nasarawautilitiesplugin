(function( $ ) {
    'use strict';

    $(window).load(function() {
        $(".close-notify").on('click', closeNotifier);
        $(".doc-apprv-btn").on('click', ApprovalDocViaAjax);
        $(".doc-decl-btn").on('click', DisapproveDocViaAjax);
        $("#coop-search").focus(filterMembersBasedOnInput);
        $("#searched-details").slideUp();
        setTimeout(() => {
            $("#search-wrapper").slideDown()
        }, 500);
    });

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
        $.ajax({
            type : "post",
            dataType : "json",
            url : mtiiAdminData.ajaxurl,
            data,
            success: function(response) {
                // console.log(response);
                if(response.status == "success") {
                    let msg = `<p class="success">${response.info}</p>`;
                    openNotifier(false, msg);
                } else {
                    let msg = `<p class="error">${response.info}</p>`;
                    openNotifier(false, msg);
                    //window.location.reload();
                }
            }
        })
    }

    function DisapproveDocViaAjax(e) {
        e.preventDefault();
        var reason_for_decline = prompt("Please Enter your reason for Declining");
        if(reason_for_decline != null && reason_for_decline.trim() != "") {
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
            const data = { action: "mtii_signed_doc_disapproval", doc_id, approval_nonce, doc_title, reg_catg, reason_for_decline };
            // console.log(data);
            $.ajax({
                type : "post",
                dataType : "json",
                url : mtiiAdminData.ajaxurl,
                data,
                success: function(response) {
                    // console.log(response);
                    if(response.status == "success") {
                        let msg = `<p class="success">${response.info}</p>`;
                        openNotifier(false, msg);
                    } else {
                        let msg = `<p class="error">${response.info}</p>`;
                        openNotifier(false, msg);
                        //window.location.reload();
                    }
                }
            })
        } else {
            alert("Sorry! You can't decline a registration without a reason");
        }
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

function filterMembersBasedOnInput(e) {
    closeDetailModal();
    reg_catg = $("#reg_catg").val();
    const thedata = mtiiAdminData.ngo_and_coop_list;
    if (reg_catg==="Cooperative") {
        var dCoopAndNgoData = JSON.parse(thedata.coop);
    } else if(reg_catg==="Business Premise") {
        var dCoopAndNgoData = JSON.parse(thedata.biz_prem);
    } else {
        var dCoopAndNgoData = JSON.parse(thedata.ngo);
    }
    // console.log(dCoopAndNgoData);
    $(document).on('keyup', function(ev) {
        const dInput = $(e.target);
        const dInputVal = dInput.val();
        const membersToRender = dCoopAndNgoData.filter( name =>name.toUpperCase().replace(/  /g, " ").includes(dInputVal.toUpperCase()));
        const pluralizeWord =  membersToRender.length>0 ? 's' : '';
        if (dInputVal!="") {
            const memberAsHtml = membersToRender.map(
                member => `
                <p onclick="getOrgInfo('${member.replace(/ /g, "_")}')" class="coop-member-list">
                    ${member}&nbsp; <span class='detail-btn'>Click for Details</span>
                </p>
                `
            ).join("");
            $("#members-wrapper").html(
                `<p class="coop-member-list result">
                    Search Result: ${membersToRender.length} member${pluralizeWord} with similar names found.
                </p>${memberAsHtml}
            `);
        } else {
            $("#members-wrapper").html("");
        }

    });
}

function getOrgInfo(name) {
    if (localStorage.getItem('mtii_is_on_ajax')) {
        alert("It seems there is a present request, Please wait a bit and try again");
        return;
    }
    const showLoaderAndMsg = `<div class="lds-ring for-approval-detail"><div></div><div></div><div></div><div></div></div>
                <p id="detail-info" class="info">Please Wait...while we fetch details</p>`;
    const searchDetailsDiv = $("#searched-details");
    searchDetailsDiv.html(showLoaderAndMsg).css({'height': '500px'}).slideDown()
    const approval_nonce = $("#mtii-doc-nonce").val();
    const reg_catg = $("#reg_catg").val();
    const org_to_get = name;
    const data = { action: "get_org_details_coop_or_ngo", approval_nonce, reg_catg, org_to_get };

    localStorage.setItem("mtii_is_on_ajax", "yes");
    $.ajax({
        type : "post",
        dataType : "json",
        url : mtiiAdminData.ajaxurl,
        data,
        success: function(response) {
            // console.log(response);
            if(response.status == "success") {
                if (reg_catg==="Cooperative") {
                    if (response.org && response.org!=null) {
                        const {
                            invoice_number_filled_against,  lga_of_proposed_society, date_of_establisment,
                            name_of_president, number_of_president, name_of_vice, number_of_vice,
                            name_of_secretary, number_of_secretary, ward_of_proposed_society,
                            name_of_approved_society
                        } = response.org;
                        const theHtmlVal = `
                            <h6>Cooperative Name (Approved): ${name_of_approved_society}</h6>
                            ${createParagraphTag('invoice Number', invoice_number_filled_against)}
                            ${createParagraphTag('Date Established', date_of_establisment)}
                            ${createParagraphTag('Local Government', lga_of_proposed_society)}
                            ${createParagraphTag('Ward of Society', ward_of_proposed_society)}
                            ${createParagraphTag('Name of President', name_of_president)}
                            ${createParagraphTag('Number of President', number_of_president)}
                            ${createParagraphTag('Name of Vice', name_of_vice)}
                            ${createParagraphTag('Number of Vice', number_of_vice)}
                            ${createParagraphTag('Name of Secretary', name_of_secretary)}
                            ${createParagraphTag('Number of Secretary', number_of_secretary)}
                            <p class="close-modal" onClick="closeDetailModal()">Close<p>
                        `
                        searchDetailsDiv.html(theHtmlVal).css({ 'overflow': 'auto', 'padding': '60px 30px'});
                    } else {
                        searchDetailsDiv.html(`
                        <h6 class="abs-align-centre">No Record found for this cooperative</h6>
                        <p class="close-modal" onClick="closeDetailModal()">Close<p>
                        `)
                    }
                } else if (reg_catg==="Business Premise") {
                    if (response.org && response.org!=null) {
                        const {
                            invoice_number_filled_against, address_of_premise, lga_of_company, date_of_registration,
                            director_one_name, director_one_number, name_of_company, director_two_name, director_two_number,
                            director_three_name, director_three_number, name_of_declarator, position_of_declarator
                        } = response.org;
                        const theHtmlVal = `
                            <h6>Business Premise Name: ${name_of_company}</h6>
                            ${createParagraphTag('invoice Number', invoice_number_filled_against)}
                            ${createParagraphTag('Address of Premise', address_of_premise)}
                            ${createParagraphTag('Date Established', date_of_registration)}
                            ${createParagraphTag('Local Government Area', lga_of_company)}
                            ${createParagraphTag('Name of Coordinator', director_one_name)}
                            ${createParagraphTag('Number of Coordinator', director_one_number)}
                            ${createParagraphTag('Name of Coordinator', director_two_name)}
                            ${createParagraphTag('Number of Asst Coordinator', director_two_number)}
                            ${createParagraphTag('Name of Secretary', director_three_name)}
                            ${createParagraphTag('Number of Secretary', director_three_number)}
                            ${createParagraphTag('Number of Attester', name_of_declarator)}
                            ${createParagraphTag('Number of Attester', position_of_declarator)}
                            <p class="close-modal" onClick="closeDetailModal()">Close<p>
                        `
                        searchDetailsDiv.html(theHtmlVal).css({ 'overflow': 'auto', 'padding': '60px 30px'});
                    } else {
                        searchDetailsDiv.html(`
                        <h6 class="abs-align-centre">No Record found for this NGO/CBO</h6>
                        <p class="close-modal" onClick="closeDetailModal()">Close<p>
                        `)
                    }
                } else{
                    if (response.org && response.org!=null) {
                        const {
                            invoice_number_filled_against,  lga_of_proposed_organization, date_of_establishment,
                            name_of_coordinator, number_of_coordinator, name_of_assistant_coordinator,
                            number_of_assistant_coordinator, name_of_secretary, number_of_secretary,
                            name_of_approved_organization, name_of_attester
                        } = response.org;
                        const theHtmlVal = `
                            <h6>Cooperative Name (Approved): ${name_of_approved_organization}</h6>
                            ${createParagraphTag('invoice Number', invoice_number_filled_against)}
                            ${createParagraphTag('Date Established', date_of_establishment)}
                            ${createParagraphTag('Local Government Area', lga_of_proposed_organization)}
                            ${createParagraphTag('Name of Coordinator', name_of_coordinator)}
                            ${createParagraphTag('Number of Coordinator', number_of_coordinator)}
                            ${createParagraphTag('Name of Coordinator', name_of_assistant_coordinator)}
                            ${createParagraphTag('Number of Asst Coordinator', number_of_assistant_coordinator)}
                            ${createParagraphTag('Name of Secretary', name_of_secretary)}
                            ${createParagraphTag('Number of Secretary', number_of_secretary)}
                            ${createParagraphTag('Number of Attester', name_of_attester)}
                            <p class="close-modal" onClick="closeDetailModal()">Close<p>
                        `
                        searchDetailsDiv.html(theHtmlVal).css({ 'overflow': 'auto', 'padding': '60px 30px'});
                    } else {
                        searchDetailsDiv.html(`
                        <h6 class="abs-align-centre">No Record found for this NGO/CBO</h6>
                        <p class="close-modal" onClick="closeDetailModal()">Close<p>
                        `)
                    }
                }
                // let msg = `<p class="success">${response.info}</p>`;
                // openNotifier(false, msg);
                //console.log(response);
            } else {
                // let msg = `<p class="error">${response.info}</p>`;
                // openNotifier(false, msg);
                console.log("another", response);
                //window.location.reload();
            }
        },
        complete: function() {
            localStorage.removeItem("mtii_is_on_ajax");
        }
    })
}

function createParagraphTag(description, info) {
    if (description && info) {
        return `
        <p class="inline-input body">
            <span>
                ${description}
                <span class="as-placeholder">${info}</span>
            </span>
        </p>
        `;
    }
}

function closeDetailModal(){
    $("#searched-details").slideUp().html('').css({'height': '0'});
}
