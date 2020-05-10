<?php

$coop_info_json = openssl_decrypt($_REQUEST["n"], 'AES-128-ECB', 'SECRET');
$coop_info_decoded = json_decode($coop_info_json, true);

$coop_name = isset($coop_info_decoded["coop_name"]) ? $coop_info_decoded["coop_name"] : '';
$ward = isset($coop_info_decoded["ward"]) ? $coop_info_decoded["ward"] : '';
$lga = isset($coop_info_decoded["lga"]) ? $coop_info_decoded["lga"] : '';
$ward_code = isset($coop_info_decoded["ward_code"]) ? $coop_info_decoded["ward_code"] : '';
$lga_code = isset($coop_info_decoded["lga_code"]) ? $coop_info_decoded["lga_code"] : '';
$id = isset($coop_info_decoded["id"]) ? $coop_info_decoded["id"] : '';

$coop_info = array(
    "id"        => $id,
    "ward"      => $ward,
    "lga"       => $lga,
    "ward_code" => $ward_code,
    "lga_code"  => $lga_code,
    "coop_name" => $coop_name
);

$id = str_pad($id, 4, '0', STR_PAD_LEFT);
$coop_ref_no = '26/'.$lga_code.'/'.$ward_code.'/'.$id;

$coop_name_lower_case = strtolower(str_replace(" ", "_", trim($coop_name)));

if (urlencode($_REQUEST["n"]) != urlencode(openssl_encrypt(json_encode($coop_info), 'AES-128-ECB', 'SECRET')) ) {
    wp_redirect(site_url('/'));
    exit;
}

if (isset($_REQUEST["downlfi"]) && $_REQUEST["downlfi"]=="y") {
    header('Content-Disposition: attachment; filename="'.$coop_name_lower_case.'".jpg"');
} else {
    header('Content-Type: image/jpeg');
}

$image_path = WP_CONTENT_DIR . "/plugins/mtii-utilities/pdftojpeg/certificate_dummy.jpg";

$img_to_write_over = imagecreatefromjpeg($image_path);

$color = imagecolorallocate($img_to_write_over, 80, 80, 80);
$color2 = imagecolorallocate($img_to_write_over, 0, 0, 0);


$font_path = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/RedHatDisplay-Bold.ttf";
$font_path_italic = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/RedHatDisplay-MediumItalic.ttf";
$font_path_script = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/FREEBSC.ttf";


$texttopright = $coop_ref_no;
$sizetopright =10;
$lefttopright =495;
$toptopright =105;

imagettftext($img_to_write_over, $sizetopright, 0, $lefttopright, $toptopright, $color2, $font_path, $texttopright);

$text = $coop_name;
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

$text1 = "Cooperative Society";
$top1 =302;

$txt_space_lower = imagettfbbox($size, 0, $font_path, $text1);
// Determine text width and height
$txt_width_lower = abs($txt_space_lower[4] - $txt_space_lower[0]);
$txt_height_lower = abs($txt_space_lower[3] - $txt_space_lower[1]);


// set starting x and y coordinates for the text, so that it is horizontally and vertically centered
$left1 = abs($image_width - $txt_width_lower) /2;

imagettftext($img_to_write_over, $size, $angle, $left1, $top1, $color, $font_path, $text1);

$text2 = date("r");
$size2 =10;
$left2 =282;
$top2 =437;


$text3 = "mtii Nasarawa WebApp";
$size3 =7;
$left3 =330;
$top3 =453;


$text4 = "mtiiNasarawawebapp";
$size4 =16;
$left4 =330;
$top4 =487;



imagettftext($img_to_write_over, $size2, $angle, $left2, $top2, $color, $font_path, $text2);

imagettftext($img_to_write_over, $size3, $angle, $left3, $top3, $color, $font_path_italic, $text3);

imagettftext($img_to_write_over, $size4, $angle, $left4, $top4, $color2, $font_path_script, $text4);


imagejpeg($img_to_write_over);

if (isset($_REQUEST["downlfi"]) && $_REQUEST["downlfi"]=="y") {

    $d_img_path = realpath($img_to_write_over);

    readfile($d_img_path, "$coop_name_lower_case.jpg");
}

imagedestroy($img_to_write_over);
?>