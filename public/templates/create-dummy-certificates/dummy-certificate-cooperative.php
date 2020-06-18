<?php
function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}

$reg_info_json = openssl_decrypt($_REQUEST["n"], 'AES-128-ECB', 'XJ34');
$reg_info_decoded = json_decode($reg_info_json, true);

$registered_name = isset($reg_info_decoded["registered_name"]) ? $reg_info_decoded["registered_name"] : '';
$ward = isset($reg_info_decoded["ward"]) ? $reg_info_decoded["ward"] : '';
$lga = isset($reg_info_decoded["lga"]) ? $reg_info_decoded["lga"] : '';
$ward_code = isset($reg_info_decoded["ward_code"]) ? $reg_info_decoded["ward_code"] : '';
$lga_code = isset($reg_info_decoded["lga_code"]) ? $reg_info_decoded["lga_code"] : '';
$id = isset($reg_info_decoded["id"]) ? $reg_info_decoded["id"] : '';
$invoice_number = isset($reg_info_decoded["invoice_number"]) ? $reg_info_decoded["invoice_number"] : '';

if (isset($_REQUEST["catg"]) && $_REQUEST["catg"]=='cooperative') {
    $reg_info = array(
        "id"                => $id,
        "ward"              => $ward,
        "lga"               => $lga,
        "ward_code"         => $ward_code,
        "lga_code"          => $lga_code,
        "registered_name"   => $registered_name,
        "invoice_number"    => $invoice_number
    );
    $coop_doc = get_page_by_title($invoice_number, OBJECT, 'mtii_signed_uploads');
    $date_issued = get_post_meta($coop_doc->ID, 'date_approved', true);
    $date_updated = get_post_meta($coop_doc->ID, 'date_updated', true);
    if (!$date_updated) {
        $reg_date = get_post_meta($coop_doc->ID, 'date_created', true);
    } else {
        $reg_date = $date_updated;
    }
    $image_path = WP_CONTENT_DIR . "/plugins/mtii-utilities/pdftojpeg/certificate_dummy_coop.jpeg";

} else {
    $reg_info = array(
        "id"                => $id,
        "lga"               => $lga,
        "lga_code"          => $lga_code,
        "registered_name"   => $registered_name,
        "invoice_number"    => $invoice_number
    );
    $ngo_doc = get_page_by_title($invoice_number, OBJECT, 'mtii_ngo_lists');
    $date_issued = get_post_meta($ngo_doc->ID, 'date_approved', true);
    $image_path = WP_CONTENT_DIR . "/plugins/mtii-utilities/pdftojpeg/certificate_dummy.jpg";

}

$id = str_pad($id, 4, '0', STR_PAD_LEFT);
$registration_ref_no = isset($_REQUEST["catg"]) && $_REQUEST["catg"]=='cooperative' ?
                '26/'.$lga_code.'/'.$ward_code.'/'.$id : '26/'.$lga_code.'/'.$id;

$registered_name_lower_case = strtolower(str_replace(" ", "_", trim($registered_name)));

if (urlencode($_REQUEST["n"]) != urlencode(openssl_encrypt(json_encode($reg_info), 'AES-128-ECB', 'XJ34')) ) {
    wp_redirect(site_url('/'));
    exit;
}

// echo $registered_name_lower_case." <br />".$registration_ref_no."<br />".$reg_info_json;

if (isset($_REQUEST["downlfi"]) && $_REQUEST["downlfi"]=="y") {
    header('Content-Disposition: attachment; filename="'.$registered_name_lower_case.'.jpg"');
} else {
    header('Content-Type: image/jpeg');
}

$img_to_write_over = imagecreatefromjpeg($image_path);

$color = imagecolorallocate($img_to_write_over, 80, 80, 80);
$color2 = imagecolorallocate($img_to_write_over, 0, 0, 0);


$font_path = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/RedHatDisplay-Bold.ttf";
$font_path_italic = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/RedHatDisplay-MediumItalic.ttf";
$font_path_script = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/FREEBSC.ttf";


$texttopright = $registration_ref_no;
$sizetopright =10;
$lefttopright = $_REQUEST["catg"]=='cooperative' ? 620 : 495;
$toptopright = $_REQUEST["catg"]=='cooperative' ? 156 : 105;

imagettftext($img_to_write_over, $sizetopright, 0, $lefttopright, $toptopright, $color2, $font_path, $texttopright);

if ($_REQUEST["catg"]=='cooperative') {
    $text_coop = $registered_name;
    $size = 12;
    $angle = 0;
    $top= 338;

    $txt_space = imagettfbbox($size, 0, $font_path, $text_coop);

    // Determine text width and height
    $txt_width = abs($txt_space[4] - $txt_space[0]);
    $txt_height = abs($txt_space[3] - $txt_space[1]);

    $image_width = imagesx($img_to_write_over);
    $image_height = imagesy($img_to_write_over);

    // set starting x and y coordinates for the text, so that it is horizontally and vertically centered
    $left = abs($image_width - $txt_width) /2;

    imagettftext($img_to_write_over, $size, $angle, $left, $top, $color, $font_path, $text_coop);
} else {
    $registered_name_split = $lines = explode("\n", wordwrap($registered_name, 35));
    $text = isset($registered_name_split[0]) && $registered_name_split[0] ? $registered_name_split[0] : '';
    $size = 22;
    $angle = 0;
    $top=270;

    $txt_space = imagettfbbox($size, 0, $font_path, $text);

    // Determine text width and height
    $txt_width = abs($txt_space[4] - $txt_space[0]);
    $txt_height = abs($txt_space[3] - $txt_space[1]);

    $image_width = imagesx($img_to_write_over);
    $image_height = imagesy($img_to_write_over);

    // set starting x and y coordinates for the text, so that it is horizontally and vertically centered
    $left = abs($image_width - $txt_width) /2;

    imagettftext($img_to_write_over, $size, $angle, $left, $top, $color, $font_path, $text);

    $text1 = isset($registered_name_split[1]) && $registered_name_split[1] ? $registered_name_split[1] : ''; //isset($_REQUEST["catg"]) && $_REQUEST["catg"]=='cooperative' ? "Cooperative Society" : "Organization";
    $top1 = 302;

    $txt_space_lower = imagettfbbox($size, 0, $font_path, $text1);
    // Determine text width and height
    $txt_width_lower = abs($txt_space_lower[4] - $txt_space_lower[0]);
    $txt_height_lower = abs($txt_space_lower[3] - $txt_space_lower[1]);

    // set starting x and y coordinates for the text, so that it is horizontally and vertically centered
    $left1 = abs($image_width - $txt_width_lower) /2;

    imagettftext($img_to_write_over, $size, $angle, $left1, $top1, $color, $font_path, $text1);
}


$text2 = $date_issued;
$size2 = 10;
$left2 = $_REQUEST["catg"]=='cooperative' ? 330 : 282;
$top2 = $_REQUEST["catg"]=='cooperative' ? 464 : 437;


$text3 = "mtii Nasarawa WebApp";
$size3 = $_REQUEST["catg"]=='cooperative' ? 8 :7;
$left3 = $_REQUEST["catg"]=='cooperative' ? 350 : 330;
$top3 = $_REQUEST["catg"]=='cooperative' ? 483 : 453;


$text4 = "mtiiNasarawawebapp";
$size4 = 16;
$left4 = $_REQUEST["catg"]=='cooperative' ? 380 : 330;
$top4 =  $_REQUEST["catg"]=='cooperative' ? 520 : 487;



imagettftext($img_to_write_over, $size2, $angle, $left2, $top2, $color, $font_path, $text2);

imagettftext($img_to_write_over, $size3, $angle, $left3, $top3, $color, $font_path_italic, $text3);

imagettftext($img_to_write_over, $size4, $angle, $left4, $top4, $color2, $font_path_script, $text4);


/**
 * Other infos related to cooperative alone
 */

if ($_REQUEST["catg"]=='cooperative' ) {
    $date_updated = get_post_meta($coop_doc->ID, 'date_updated', true);
    if (!$date_updated) {
        $reg_date = get_post_meta($coop_doc->ID, 'date_created', true);
    } else {
        $reg_date = $date_updated;
    }
    $time = strtotime($reg_date);
    $month_to_use = date("F", $time);
    $year_to_use = date("y", $time);
    $day_to_use = ordinal(date("d", $time));
    $d_date_size = 12;
    $d_date_from_left_1 =  463;
    $d_date_from_left_2 = 620;
    $d_date_from_top_1 = 300;

    $d_date_from_left_3 =  165;
    $d_date_from_left_4 =  435;
    $d_date_from_top_3 = 320;

    $lga_font_size = 10;
    $d_date_from_left_5 =  195;
    $d_date_from_left_6 =  470;
    $d_date_from_top_5 = 358;

    $d_date_from_top_7 = 395;

    imagettftext($img_to_write_over, $d_date_size, 0, $d_date_from_left_1, $d_date_from_top_1, $color, $font_path, $day_to_use);
    imagettftext($img_to_write_over, $d_date_size, 0, $d_date_from_left_2, $d_date_from_top_1, $color, $font_path, $month_to_use);
    imagettftext($img_to_write_over, $d_date_size, 0, $d_date_from_left_3, $d_date_from_top_3, $color, $font_path, $year_to_use);
    imagettftext($img_to_write_over, $d_date_size, 0, $d_date_from_left_4, $d_date_from_top_3, $color, $font_path, 10);
    imagettftext($img_to_write_over, $lga_font_size, 0, $d_date_from_left_5, $d_date_from_top_5, $color, $font_path, $ward);
    imagettftext($img_to_write_over, $lga_font_size, 0, $d_date_from_left_6, $d_date_from_top_5, $color, $font_path, $lga);
    imagettftext($img_to_write_over, 12, 0, $d_date_from_left_6, $d_date_from_top_7, $color, $font_path, 'Cooperative');

}

imagejpeg($img_to_write_over);

if (isset($_REQUEST["downlfi"]) && $_REQUEST["downlfi"]=="y") {

    $d_img_path = realpath($img_to_write_over);

    readfile($d_img_path, "$registered_name_lower_case.jpg");
}

imagedestroy($img_to_write_over);
?>