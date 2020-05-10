<?php
function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}

$coop_info_json = openssl_decrypt($_REQUEST["n"], 'AES-128-ECB', 'SECRET');
$coop_info_decoded = json_decode($coop_info_json, true);

$biz_name = isset($coop_info_decoded["biz_name"]) ? $coop_info_decoded["biz_name"] : '';
$lga = isset($coop_info_decoded["lga"]) ? $coop_info_decoded["lga"] : '';
$lga_code = isset($coop_info_decoded["lga_code"]) ? $coop_info_decoded["lga_code"] : '';
$id = isset($coop_info_decoded["id"]) ? $coop_info_decoded["id"] : '';
$biz_nature = isset($coop_info_decoded["biz_nature"]) ? $coop_info_decoded["biz_nature"] : '';
$biz_address = isset($coop_info_decoded["biz_address"]) ? $coop_info_decoded["biz_address"] : '';
$time_of_declaration = isset($coop_info_decoded["time_of_declaration"]) ? $coop_info_decoded["time_of_declaration"] : '';
$day_of_declaration = isset($coop_info_decoded["day_of_declaration"]) ? $coop_info_decoded["day_of_declaration"] : '';
$month_of_declaration = isset($coop_info_decoded["month_of_declaration"]) ? $coop_info_decoded["month_of_declaration"] : '';
$year_of_declaration = isset($coop_info_decoded["year_of_declaration"]) ? $coop_info_decoded["year_of_declaration"] : '';

$coop_info = array(
    "id"                     => $id,
    "lga"                    => $lga,
    "lga_code"               => $lga_code,
    "biz_name"               => $biz_name,
    "biz_nature"             => $biz_nature,
    "biz_address"            => $biz_address,
    "time_of_declaration"    => $time_of_declaration,
    "day_of_declaration"     => $day_of_declaration,
    "month_of_declaration"   => $month_of_declaration,
    "year_of_declaration"    => $year_of_declaration,
);

$id = str_pad($id, 4, '0', STR_PAD_LEFT);
$coop_ref_no = '26/'.$lga_code.'/A94GQ/'.$id;

$coop_name_lower_case = strtolower(str_replace(" ", "_", trim($biz_name)));

if (urlencode($_REQUEST["n"]) != urlencode(openssl_encrypt(json_encode($coop_info), 'AES-128-ECB', 'SECRET'))
    || !isset($_REQUEST["catg"]) || $_REQUEST["catg"] != "biz_prem"
) {
    wp_redirect(site_url('/'));
    exit;
}

if (isset($_REQUEST["downlfi"]) && $_REQUEST["downlfi"]=="y") {
    header('Content-Disposition: attachment; filename="'.$coop_name_lower_case.'".jpg"');
} else {
    header('Content-Type: image/jpeg');
}

$image_path = WP_CONTENT_DIR . "/plugins/mtii-utilities/pdftojpeg/certificate_dummy_biz_prem.jpg";

$img_to_write_over = imagecreatefromjpeg($image_path);

$color = imagecolorallocate($img_to_write_over, 80, 80, 80);
$color2 = imagecolorallocate($img_to_write_over, 0, 0, 0);


$font_path = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/RedHatDisplay-Bold.ttf";
$font_path_italic = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/RedHatDisplay-MediumItalic.ttf";
$font_path_script = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/FREEBSC.ttf";

$texttopright = $coop_ref_no;
$sizetopright =10;
$lefttopright =618;
$toptopright =127;

imagettftext($img_to_write_over, $sizetopright, 0, $lefttopright, $toptopright, $color2, $font_path, $texttopright);

$text = $biz_name;
$size = 16;
$angle = 0;
$top=287;

$txt_space = imagettfbbox($size, 0, $font_path, $text);

// Determine text width and height
$txt_width = abs($txt_space[4] - $txt_space[0]);
$txt_height = abs($txt_space[3] - $txt_space[1]);

$image_width = imagesx($img_to_write_over);
$image_height = imagesy($img_to_write_over);

// set starting x and y coordinates for the text, so that it is horizontally and vertically centered
$left = abs($image_width - $txt_width) /2;

imagettftext($img_to_write_over, $size, $angle, $left, $top, $color2, $font_path, $text);

$text1 = $biz_address;
$top1 =310;

$txt_space_lower = imagettfbbox($size, 0, $font_path, $text1);
// Determine text width and height
$txt_width_lower = abs($txt_space_lower[4] - $txt_space_lower[0]);
$txt_height_lower = abs($txt_space_lower[3] - $txt_space_lower[1]);


// set starting x and y coordinates for the text, so that it is horizontally and vertically centered
$left1 = abs($image_width - $txt_width_lower) /2;

imagettftext($img_to_write_over, $size, $angle, $left1, $top1, $color2, $font_path, $text1);


$text_lga = $lga;
$word_list = array("local government");
foreach ($word_list as &$word) {
    $word = '/\b' . preg_quote($word, '/') . '\b/';
}
$text_lga = ucwords(preg_replace($word_list, '', strtolower($text_lga)));
$top_lga = 340;
$left_lga = 180;

imagettftext($img_to_write_over, $size, $angle, $left_lga, $top_lga, $color2, $font_path, $text_lga);

$text_biz_nature = $biz_nature;
$top_biz_nature = 367;
$left_biz_nature = 320;

imagettftext($img_to_write_over, $size, $angle, $left_biz_nature, $top_biz_nature, $color2, $font_path, $biz_nature);


$text3 = $time_of_declaration;
$size3 =10;
$left3 =190;
$top3 =473;
imagettftext($img_to_write_over, $size3, $angle, $left3, $top3, $color2, $font_path, $text3);

$text_day = ordinal($day_of_declaration);
$left_day = 160;
$top_day = 495;
imagettftext($img_to_write_over, $size3, $angle, $left_day, $top_day, $color2, $font_path, $text_day);

$text_month = $month_of_declaration;
$left_month = 285;
imagettftext($img_to_write_over, $size3, $angle, $left_month, $top_day, $color2, $font_path, $text_month);

$text_year = mb_substr($year_of_declaration, 2) ;
$left_year = 357;
imagettftext($img_to_write_over, $size3, $angle, $left_year, $top_day+1, $color2, $font_path_italic, $text_year);

$text4 = "mtiiNasarawawebapp";
$left4 =560;
$top4 =475;
imagettftext($img_to_write_over, $size, $angle, $left4, $top4, $color2, $font_path_script, $text4);


imagejpeg($img_to_write_over);

if (isset($_REQUEST["downlfi"]) && $_REQUEST["downlfi"]=="y") {

    $d_img_path = realpath($img_to_write_over);

    readfile($d_img_path, "$coop_name_lower_case.jpg");
}

imagedestroy($img_to_write_over);
?>