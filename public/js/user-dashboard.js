document.write('<style type="text/css">body{display:none}</style>');
const formatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'NGN',
    minimumFractionDigits: 2
})

const wardsAndCodes = {
    "AKWANGA_LOCAL_GOVERNMENT"  : ["AGYAGA", "ANCHO_BABA", "GWANJE", "ANCHO_NIGHAAN", "ANDAHA", "NUNKU", "GUDI", "MOROA", "AKWANGA_WEST", "AKWANGA_EAST", "NINGO_BOHAR"],
    "AWE_LOCAL_GOVERNMENT"      : ["KANJE/ABUNI", "MADAKI", "MAKWANGIJI", "TUNGA", "RIBI", "AZARA", "GALADIMA", "WUSE","AKIRI","JANGARU"],
    "DOMA_LOCAL_GOVERNMENT"     : [ "ALAGYE", "RUKUBI", "AGBASHI", "DOKA", "AKPANAJA", "MADAKI", "UNG._SARKIN_DAWAKI", "MADAUCHI", "UNG._DANGALADIMA", "SABON_GARI"],
    "KARU_LOCAL_GOVERNMENT"     : ["ASO/KODAPE", "AGADA/BAGAJI", "KARSHI_I", "KARSHI_II", "KEFFIN SHANU/BETTI", "TATTARA/KONDORO", "GITATA", "GURKU/KABUSU", "UKE", "PANDA/KARE", "KARU"],
    "KEANA_LOCAL_GOVERNMENT"    : ["IWAGU", "AMIRI", "OBENE", "OKI", "KADARKO", "KWARA", "ALOSHI", "AGAZA", "GIZA_MADAKI", "GIZA_GALADIMA"],
    "KEFFI_LOCAL_GOVERNMENT"    : ["ANG._IYA_I", "ANG._IYA_II", "TUDUN_KOFA_TV", "GANGAREN_TUDU ", "KEFFI_TOWN_EAST", "YARA", "ANG._RIMI", "SABON_GARI", "JIGWADA", "LIMAN_ABAJI"],
    "KOKONA_LOCAL_GOVERNMENT"   : [ "AGWADA", "KOYA/KANA", "BASSA", "KOKONA", "KOFAR_GWARI", "NINKORO", "HADARI", "DARI", "AMBA", "GARAKU", "YALWA"],
    "LAFIA_LOCAL_GOVERNMENT"    : [ "ADOGI","AGYARAGU_TOFA","AKURBA","ARIKYA","ASSAKIO","ASHIGE","CIROMA","GAYAM","KEFIN_WAMBAI","MAKAMA","SHABU_KWANDARE","WAKWA","ZANWA"],
    "NASARAWA_LOCAL_GOVERNMENT" : [ "UDENIN_GIDA", "AKUM", "UDENIN_MAGAJI", "LOKO", "TUNGA/BAKONO", "GUTO/AISA", "NSW_NORTH", "NSW_EAST", "NSW_CENTRAL", "NSW_MAIN_TOWN",
        "ARA_I", "ARA_II", "LAMINGA", "KONA/ONDA/APAWU", "ODU"],
    "NASARAWA_EGGON_LOCAL_GOVERNMENT" : ["N/EGGON", "UBBE", "IGGA/BURUM_BURUM", "UMME", "MADA_STATION", "LIZZIN_KEFFI", "LAMBAGA/ARIKPA", "KAGBU_WANA", "IKKA_WANGIBI",
        "ENDE", "WAKAMA", "ALOCE/GINDA", "ALOGANI", "AGUNJI"],
    "AGUNJI"                    : [ "AGWATASHI", "DADDARE/RIRI", "OBI", "TUDUN_ADABU", "DUDUGURU", "GWADANYE", "KYAKYALE", "GIDAN_AUSA_I", "GIDAN_AUSA_II", "ADUDU"],
    "TOTO_LOCAL_GOVERNMENT"     : [ "GWARGWADA", "GADAGWA", "BUGA_KARMO", "UMAISHA", "DAUSU", "KANYEHU", "UGYA", "TOTO", "SHAFAN_ABAKWA", "SHAFAN_ABAKWA", "SHEGE", "KATAKPA"],
    "WAMBA_LOCAL_GOVERNMENT"    : [ "ARUM", "MANGAR", "GITTA", "NAKERE", "KONVAH", "WAYO", "WAMBA_WEST", "WAMBA_EAST", "KWARRA", "JIMIYA"]
};

$(document).ready(function() {
    $("#user-section").addClass("js-on");
    $(".children-nav").slideUp();
    $(".hidden-input").slideUp();
    $('body').css('display','block');
    const wardSelect = $("#all-wards");
    const landLordName = $('#landlord-name');
    const landLordAddress = $('#landlord-address');

    landLordName.slideUp();
    landLordAddress.slideUp();
    checkIfApartmentIsRentedChange();


    const allLgas = $("#lga-list");
    const wardSelectVal = wardSelect.val();
    let firstOPtion;
    if (wardSelectVal && wardSelectVal!="") {
        firstOPtion = `<option value="${wardSelectVal}">${wardSelectVal}</option>`;
    } else {
        firstOPtion = `<option value="">Select Ward</option>`;
    }

    wardSelect.html(firstOPtion);
    if (allLgas.val()!="" && [...wardSelect].length>0) {
        loadWardsListFromLga(allLgas.val(), wardSelect);
    }

    const addLeadingZero = (numb) => numb < 10 ? "0"+numb : numb;
    const hours12 = (date) => addLeadingZero((date.getHours() + 24) % 12) || 12;
    const amOrPm = (hr) => hr < 12 ? " AM" : " PM";
    const timeDiv = $("#jstime");
    setInterval(() => {
        const today = new Date;
        const realTime = hours12(today) + ":" + addLeadingZero(today.getMinutes()) + ":" +
            addLeadingZero(today.getSeconds()) + amOrPm(today.getHours());
        timeDiv.html(`<strong>${realTime}</strong>`);
    }, 1000);

    var allUploadsDiv = $(".coop-info-wrapper");
    let searchParams = new URLSearchParams(window.location.search);
    if (searchParams.has('do') && !searchParams.get('do')==="replacements") {
        [...allUploadsDiv].forEach(upload=> {
            const height = $(upload).height()
            console.log(height);
            $(upload).attr("dh", height);
            $(upload).height("300");
        })

        const arrowDown = $(".open-and-close-icon");
        arrowDown.on("click", function(e) {
            const btn = $(e.target);
            const mainCoopWrapper = $(this).prev().find(".coop-info-wrapper");
            const mainCoopWrapperHeight = mainCoopWrapper.height();
            const mainCoopWrapperSavedHeight = mainCoopWrapper.attr("dh");
            if( mainCoopWrapperHeight == "300") {
                mainCoopWrapper.height(Number(mainCoopWrapperSavedHeight)+20);
                btn.html("Click Here to Close").css({
                    "bottom" : 10,
                })
            } else {
                mainCoopWrapper.height("300");
                btn.html("Click Here to Expand").css({
                    "bottom" : 10,
                })
            }
        })
    }



    const sideNav = $("#side-nav");
    const thesideNav = document.getElementById("side-nav");
    $(".content-wrapper").css({
        'min-height': thesideNav.clientHeight+10+"px"
    });
    $(window).on("scroll", function() {
        if(thesideNav.clientHeight - $(window).scrollTop() <= window.innerHeight) {
            $(thesideNav).css({
                    'position' : 'fixed',
                    'bottom': 0,
                    'top': 'unset'
            })
        } else {
            $(thesideNav).css({
                'position' : 'absolute',
                'top': 0,
                'bottom': 'unset'
            })
        }
    });
    $(".mobile-hamburger-toggle").on("click", function() {
        $(this).toggleClass("nav-in-view");
        sideNav.toggleClass("in-view");
    })

    const arrowDownNav = $(".arrow-down");
    //console.log(isNotNgoDirector());

    if (searchParams.has('do') && searchParams.get('do')==="reg"
    ) {
        const ArrowElem = $(arrowDownNav[1]);
        ArrowElem.addClass("children-in-view");
        expandParentNav(ArrowElem, ArrowElem.context.nextElementSibling);
    } else if (searchParams.has('do') && searchParams.get('do')==="pay") {
        const ArrowElem = $(arrowDownNav[0]);
        ArrowElem.addClass("children-in-view");
        expandParentNav(ArrowElem, ArrowElem.context.nextElementSibling);
    }  else if (!isNotNgoDirector() && searchParams.has('do') && searchParams.get('do')==="replacements") {
        const ArrowElem = $(arrowDownNav[1]);
        ArrowElem.addClass("children-in-view");
        expandParentNav(ArrowElem, ArrowElem.context.nextElementSibling);
    }   else if (!isNotNgoDirector() && searchParams.has('do') && searchParams.get('do')==="legal-search") {
        const ArrowElem = $(arrowDownNav[2]);
        ArrowElem.addClass("children-in-view");
        expandParentNav(ArrowElem, ArrowElem.context.nextElementSibling);
    } else if (searchParams.has('do') && searchParams.get('do')==="approve") {
        const ArrowElem = $(arrowDownNav[0]);
        ArrowElem.addClass("children-in-view");
        expandParentNav(ArrowElem, ArrowElem.context.nextElementSibling);
    } else if (searchParams.has('do') && searchParams.get('do')==="adminview") {
        if (isNotNgoDirector()) {
            var navIndex = 1;
        } else {
            var navIndex = 3;
        }
        const ArrowElem = $(arrowDownNav[navIndex]);
        ArrowElem.addClass("children-in-view");
        expandParentNav(ArrowElem, ArrowElem.context.nextElementSibling);
    }

    arrowDownNav.on("click", function() {
        $(this).toggleClass("children-in-view");
        expandParentNav(this, this.nextElementSibling);
    });

    $("#account-type").on("change", function() {
        $(".hidden-input").slideUp();
        if($(this).val()=="cooperative") {
            $("#cooperative-reg-type").slideDown();
        }

    })

    allLgas.on("change", function() {
        wardSelect.html('<option value="">Select Ward</option>');
        const lga = $(this).val();
        loadWardsListFromLga(lga, wardSelect);
    })

    const bizPremisesCatg = $("#payment-type-category");
    const bizPremPayment = $("#payment-type")
    bizPremisesCatg.on("change", function() {
        if(bizPremisesCatg.val()=="Fresh Registration") {
            const newRegPrice = varsMtii.bizPremNewRegPrice
            var selectOptions = '<option value="">Select Payment Type</option>';
            for (const key in newRegPrice) {
                if (newRegPrice[key]=='is_group_description') {
                    selectOptions += `<option style="background-color: #cfcfcf; color: #fff" disabled>${key}</option>`;
                } else {
                    selectOptions += `<option value="${key}">${key} Fresh Registration (${formatter.format(newRegPrice[key])})</option>`;
                }
            }
            bizPremPayment.html(selectOptions)
        } else if(bizPremisesCatg.val()=="Registration Renewal") {
            const renewalPrice = varsMtii.bizPremRenewalPrice
            var selectOptions = '<option value="">Select Payment Type</option>';
            for (const key in renewalPrice) {
                if (renewalPrice[key]=='is_group_description') {
                    selectOptions += `<option style="background-color: #cfcfcf; color: #fff" disabled>${key}</option>`;
                } else {
                    selectOptions += `<option value="${key}">${key} Fresh Registration (${formatter.format(renewalPrice[key])})</option>`;
                }
            }
            bizPremPayment.html(selectOptions)
        }
    })

    const isPremiseRented = $("#is-premise-rented");
    isPremiseRented.on("change", function() {
        checkIfApartmentIsRentedChange($(this).val());
        // if($(this).val()=="Yes") {
        //     landLordName.slideDown();
        //     landLordAddress.slideDown();
        // } else {
        //     landLordName.slideUp().find("input").val("")
        //     landLordAddress.slideUp().find("input").val("")
        // }
    })

    function isNotNgoDirector() {
        return window.isNotNgoDirector && window.isNotNgoDirector==='Yes' ? true : false;
    }

    function checkIfApartmentIsRentedChange(val=null) {
        val = val ? val : $("#is-premise-rented").val();
        if(val=="Yes") {
            landLordName.slideDown();
            landLordAddress.slideDown();
        } else {
            landLordName.slideUp().find("input").val("")
            landLordAddress.slideUp().find("input").val("")
        }
    }

    // $("#testbtn").on("click", function(e) {
    //     e.preventDefault();
    //     var theData = {
    //         "InvoiceNumber": "1000133599",
    //         "PaymentRef": "JC-0000621000133599",
    //         "PaymentDate": "18/02/2020 15:18:11",
    //         "BankCode": null,
    //         "BankName": null,
    //         "BankBranch": null,
    //         "AmountPaid": 10000,
    //         "TransactionDate": "18/02/2020 15:17:50",
    //         "TransactionRef": null,
    //         "Channel": "Web",
    //         "PaymentProvider": "Bank3D",
    //         "Mac": "uhF5UbARZ+V25G/d7SjBs4LNTwZIRch8OT6457H5n58=",
    //         "ResponseCode": "0000",
    //         "ResponseMessage": "Ok",
    //         "RequestReference": "BxO5eSrQPo",
    //         "IsReversal": false,
    //         "PaymentMethod": null,
    //         "Month": 0,
    //         "Year": 0,
    //         "AgencyCode": null,
    //         "PayeAssessmentType": 0
    //     }
    //     alert("Here we go")
    //     $.ajax({
    //         type : "post",
	// 		// dataType : "json",
	// 		url : varsMtii.siteBaseUrl+"/cbscburl",
	// 		data : theData,
	// 		success: function(response) {
	// 			if (response.status == "success") {
    //                 console.log("Yeah");
	// 			} else {
	// 				console.log("Naaaa");
	// 			}
	// 		}
	// 	})
    // })
})

function expandParentNav(elem, childrenNav) {
    const thesideNav = document.getElementById("side-nav");
    if ($(elem).hasClass("children-in-view")) {
        $(childrenNav).slideDown("slow", function() {
            $(".content-wrapper").css({
                'min-height': thesideNav.clientHeight+10+"px"
            });
        });
    } else {
        $(childrenNav).slideUp("slow", function() {
            $(".content-wrapper").css({
                'min-height': thesideNav.clientHeight+10+"px"
            });
        });
    }

}

function loadWardsListFromLga(lga, wardSelect) {
    lga = lga.replace(/ /g, "_");
    const relatedWards = wardsAndCodes[lga];
    const wardsOptions = relatedWards.map(ward=>{
        return `<option value="${ward.replace(/_/g, " ")}">${ward.replace(/_/g, " ")}</option>`
    }).join(" ");
    wardSelect.append(wardsOptions);
}




