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
    "OBI_LOCAL_GOVERNMENT"      : [ "AGWATASHI", "DADDARE/RIRI", "OBI", "TUDUN_ADABU", "DUDUGURU", "GWADANYE", "KYAKYALE", "GIDAN_AUSA_I", "GIDAN_AUSA_II", "ADUDU"],
    "TOTO_LOCAL_GOVERNMENT"     : [ "GWARGWADA", "GADAGWA", "BUGA_KARMO", "UMAISHA", "DAUSU", "KANYEHU", "UGYA", "TOTO", "SHAFAN_ABAKWA", "SHAFAN_ABAKWA", "SHEGE", "KATAKPA"],
    "WAMBA_LOCAL_GOVERNMENT"    : [ "ARUM", "MANGAR", "GITTA", "NAKERE", "KONVAH", "WAYO", "WAMBA_WEST", "WAMBA_EAST", "KWARRA", "JIMIYA"]
};

$(document).ready(function() {
    const wardSelect = $("#covid-wards");
    const allLgas = $("#covid-lga");

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

    allLgas.on("change", function() {
        wardSelect.html('<option value="">Select Ward</option>');
        const lga = $(this).val();
        loadWardsListFromLga(lga, wardSelect);
    })
})

function loadWardsListFromLga(lga, wardSelect) {
    lga = lga.replace(/ /g, "_");
    const relatedWards = wardsAndCodes[lga];
    const wardsOptions = relatedWards.map(ward=>{
        return `<option value="${ward.replace(/_/g, " ")}">${ward.replace(/_/g, " ")}</option>`
    }).join(" ");
    wardSelect.append(wardsOptions);
}