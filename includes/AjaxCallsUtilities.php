<?php
/**
 * Fired during plugin activation.
 * This class defines all code necessary to for receiving Ajax Calls
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
namespace MtiiUtilities;

use MtiiUtilities\TasksPerformer;
use MtiiUtilities\MtiiRelatedInformation;

/**
 * This class defines all code necessary to for handling Ajax Calls
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class AjaxCallsUtilities
{
    /**
     * The version of this plugin.
     *
     * @access private
     *
     * @var string $version    The ID of this plugin.
     *
     * @since 1.0.0
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @access private
     *
     * @var string $version    The current version of this plugin.
     *
     * @since 1.0.0
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     *
     * @since 1.0.0
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    private function ordinal($number) {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13))
            return $number. 'th';
        else
            return $number. $ends[$number % 10];
    }

    /**
     * A Function to approve document
     *
     * @param [string] $invoice_number_from_doc_title The title of the document to be approved
     * @param [string] $doc_id    The id of the document to be approved
     *
     * @return [array] $result
     */
    private function mtii_approve_uploaded_doc($invoice_number_from_doc_title, $doc_id, $reg_catg)
    {
        $result = array();
        $doc_info = $reg_catg=="Cooperative" ?
            get_page_by_title($invoice_number_from_doc_title, OBJECT, 'mtii_signed_uploads') :
            get_page_by_title($invoice_number_from_doc_title, OBJECT, 'mtii_ngo_lists');

        if ($doc_info->ID == $doc_id) {
            $already_approved = $reg_catg=="Cooperative" ? get_post_meta($doc_id, 'admin_approved', true) :
                get_post_meta($doc_id, 'is_admin_approved', true);

            if ($already_approved=="true" || $already_approved=="Approved") {
                $result['status'] = "Already Approved";
            } else {
                global $mtii_db_coop_main_form;
                global $mtii_ngo_cbo_db_table;
                $reg_catg_info = $reg_catg=='Cooperative' ?
                $mtii_db_coop_main_form->get_by('invoice_number_filled_against', $invoice_number_from_doc_title)
                : $mtii_ngo_cbo_db_table->get_by('invoice_number_filled_against', $invoice_number_from_doc_title);
                $reg_catg_info = (array) $reg_catg_info;
                if ($reg_catg=="Cooperative") {
                    $reg_catg_info["admin_approved"] = "Approved";
                    $reg_catg_info["approved_to_exist"] = true;
                    $doc_id_from_db = $reg_catg_info["application_form_id"];
                    $reg_catg_info["name_of_approved_society"] = $reg_catg_info["name_of_proposed_society"];
                    $updated_coop_premise = $mtii_db_coop_main_form->update($doc_id_from_db, $reg_catg_info);
                    if ($updated_coop_premise && !$wpdb->last_error) {
                        $meta_update =  update_post_meta($doc_id, 'admin_approved', 'true');
                    } else {
                        $result['status'] = "error";
                        $result['approved'] = false;
                        $result['others'] = array('0'=>$reg_catg_info, '1'=>$invoice_number_from_doc_title, '2'=>'doc_id', 'id'=>$doc_id_from_db);
                    }
                } else {
                    $reg_catg_info["is_admin_approved"] = "Approved";
                    $reg_catg_info["approved_to_exist"] = true;
                    $doc_id_from_db = $reg_catg_info["application_form_id"];
                    $reg_catg_info["name_of_approved_organization"] = $reg_catg_info["name_of_proposed_organization"];
                    $updated_ngo = $mtii_ngo_cbo_db_table->update($doc_id_from_db, $reg_catg_info);
                    if ($updated_ngo && !$wpdb->last_error) {
                        $meta_update =  update_post_meta($doc_id, 'is_admin_approved', 'Approved');
                    } else {
                        $result['status'] = "error";
                        $result['approved'] = false;
                        $result['others'] = array('0'=>$reg_catg_info, '1'=>$invoice_number_from_doc_title, '2'=>'doc_id', 'id'=>$doc_id_from_db);
                    }
                }
                delete_post_meta($doc_id, 'approval_status');
                delete_post_meta($doc_id, 'date_approved');
                $meta_update =  update_post_meta($doc_id, 'approval_status', 'Approval Active');
                date_default_timezone_set("Africa/Lagos");
                $date_approved = date("r");
                update_post_meta($doc_id, 'date_approved', $date_approved);
                if ($meta_update == false) {
                    $result['status'] = "error";
                } else {
                    $tasks_performer = new TasksPerformer;
                    $doc_author = get_post_meta($doc_id, 'user_id', single);
                    $auth = get_user_by('id', $doc_author);
                    $auth_id = $auth->data->ID;
                    $auth_email = $auth->data->user_email;
                    $f_name = get_the_author_meta('first_name', $auth_id);
                    $l_name = get_the_author_meta('last_name', $auth_id);
                    $full_name = $f_name." ".$l_name;
                    $created_image_info = $this->create_dummy_cert_and_save_as_file($invoice_number_from_doc_title, $reg_catg);
                    if ($created_image_info && $created_image_info!=null && is_array($created_image_info)) {
                        if ($created_image_info["success"]) {
                            if ($reg_catg=='Cooperative') {
                                $society_name = $reg_catg_info["name_of_approved_society"];
                                $tasks_performer->add_organization_to_db_list(strtoupper($society_name));
                            } else {
                                $society_name = $reg_catg_info["name_of_approved_organization"];
                                $tasks_performer->add_organization_to_db_list(strtoupper($society_name), true);
                            }
                            $author_message = 'Congratulations '.$full_name.',<br /><br />'.
                            'Your uploaded signed documents for the invoice number <strong>'.
                            $invoice_number_from_doc_title.'</strong> has just been approved by the site Administrator.<br /><br />'.
                            'Attached to this email is your dummy Certificate. You should download and bring your dummy certificate '.
                            'to MTII, Nasarawa office to get your original certificate.<br /><br />'.
                            'Thank you!';
                            $mail_content = $tasks_performer->create_email_from_template('Your Upload has been Approved!', $author_message);
                            $headers = array('Content-Type: text/html; charset=UTF-8');
                            wp_mail($auth_email, 'MTII Upload Approval', $mail_content, $headers, $created_image_info["dpath"]);
                            $result['status'] = "success and certificate sent";
                            $result['approved'] = true;
                            $result['mainother'] = $created_image_info["dpath"];
                        } else {
                            $author_message = 'Congratulations '.$full_name.',<br /><br />'.
                            'Your uploaded signed documents for the invoice number <strong>'.
                            $invoice_number_from_doc_title.'</strong> has just been approved by the site Administrator.<br /><br />'.
                            'You are supposed to have your dummy certificate in this email but we had a problem creating and sending '.
                            'your dummy certificate. Please login to <a href="'.site_url().'">Our Website</a> to print you dummy '.
                            'certificate and then bring it to MTII, Nasarawa office to get the original certificate.<br /><br />'.
                            'Thank you!';
                            $mail_content = $tasks_performer->create_email_from_template('Your Upload has been Approved!', $author_message);
                            $headers = array('Content-Type: text/html; charset=UTF-8');
                            wp_mail($auth_email, 'MTII Upload Approval but No Dummy Certificate', $mail_content, $headers);
                            $result['status'] = "success but certificate not done";
                            $result['approved'] = true;
                        }
                        unlink($created_image_info["dpath"]);
                    } else {
                        $author_message = 'Congratulations '.$full_name.',<br /><br />'.
                        'Your uploaded signed documents for the invoice number <strong>'.
                        $invoice_number_from_doc_title.'</strong> has just been approved by the site Administrator.<br /><br />'.
                        'You are supposed to have your dummy certificate in this email but we had a problem creating and sending '.
                        'your dummy certificate. Please login to <a href="'.site_url().'">Our Website</a> to print you dummy '.
                        'certificate and then bring it to MTII, Nasarawa office to get the original certificate.<br /><br />'.
                        'Thank you!';
                        $mail_content = $tasks_performer->create_email_from_template('Your Upload has been Approved!', $author_message);
                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        wp_mail($auth_email, 'MTII Upload Approval. No Dummy Certificate', $mail_content, $headers);
                        $result['status'] = "success but certificate not done";
                        $result['approved'] = true;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * A Function to approve document
     *
     * @param [string] $invoice_number_from_doc_title The title of the document to be approved
     * @param [string] $doc_id                        The id of the document to be approved
     *
     * @return [array] $result
     */
    private function mtii_biz_premises_approval($invoice_number_from_doc_title, $doc_id)
    {
        $result = array();
        global $mtii_biz_prem_db_main;
        $doc_info = $mtii_biz_prem_db_main->get_by('invoice_number_filled_against', $invoice_number_from_doc_title);
        $doc_info_from_cp = get_page_by_title($invoice_number_from_doc_title, OBJECT, 'mtii_biz_prem_reg');
        $doc_id_from_db = $doc_info->application_form_id;
        if ($doc_info_from_cp->ID == $doc_id) {
            $already_approved = $doc_info->is_admin_approved;
            if ($already_approved=="Approved") {
                $result['status'] = "Already Approved";
            } else {
                delete_post_meta($doc_info_from_cp->ID, 'is_admin_approved');
                update_post_meta($doc_info_from_cp->ID, 'is_admin_approved', 'Approved');
                $doc_info_array =  ( array ) $doc_info;
                $doc_info_array["is_admin_approved"] = "Approved";
                $updated_biz_premise = $mtii_biz_prem_db_main->update($doc_id_from_db, $doc_info_array);
                if ($updated_biz_premise == false) {
                    $result['status'] = "error";
                } else {
                    $tasks_performer = new TasksPerformer;
                    $doc_author = $doc_info->user_id;
                    $auth = get_user_by('id', $doc_author);
                    $auth_id = $auth->data->ID;
                    $auth_email = $auth->data->user_email;
                    $f_name = get_the_author_meta('first_name', $auth_id);
                    $l_name = get_the_author_meta('last_name', $auth_id);
                    $full_name = $f_name." ".$l_name;
                    $created_image_info = $this->create_biz_prem_dummy_cert_and_save_as_file($invoice_number_from_doc_title);
                    if ($created_image_info && $created_image_info!=null && is_array($created_image_info)) {
                        if ($created_image_info["success"]) {
                            $author_message = 'Congratulations '.$full_name.',<br /><br />'.
                            'Your Business Premise registration with the invoice number <strong>'.
                            $invoice_number_from_doc_title.'</strong> has just been approved by the site Administrator.<br /><br />'.
                            'Attached to this email is your dummy Certificate. You should download and bring your dummy certificate '.
                            'to MTII, Nasarawa office to get your original certificate.<br /><br />'.
                            'Thank you!';
                            $mail_content = $tasks_performer->create_email_from_template('Your Registration has been Approved!', $author_message);
                            $headers = array('Content-Type: text/html; charset=UTF-8');
                            wp_mail($auth_email, 'Business Premise Registration Approval', $mail_content, $headers, $created_image_info["dpath"]);
                            $result['status'] = "success and certificate sent";
                            $result['approved'] = true;
                        } else {
                            $author_message = 'Congratulations '.$full_name.',<br /><br />'.
                            'Your Business Premise registration with the invoice number <strong>'.
                            $invoice_number_from_doc_title.'</strong> has just been approved by the site Administrator.<br /><br />'.
                            'You are supposed to have your dummy certificate in this email but we had a problem creating and sending '.
                            'your dummy certificate. Please login to <a href="'.site_url().'">Our Website</a> to print you dummy '.
                            'certificate and then bring it to MTII, Nasarawa office to get the original certificate.<br /><br />'.
                            'Thank you!';
                            $mail_content = $tasks_performer->create_email_from_template('Your Registration has been Approved!', $author_message);
                            $headers = array('Content-Type: text/html; charset=UTF-8');
                            wp_mail($auth_email, 'Business Premise Approved without Dummy Certificate', $mail_content, $headers);
                            $result['status'] = "success but certificate not done";
                            $result['approved'] = true;
                        }
                        unlink($created_image_info["dpath"]);

                    } else {
                        $author_message = 'Congratulations '.$full_name.',<br /><br />'.
                        'Your Business Premise registration with the invoice number <strong>'.
                        $invoice_number_from_doc_title.'</strong> has just been approved by the site Administrator.<br /><br />'.
                        'You are supposed to have your dummy certificate in this email but we had a problem creating and sending '.
                        'your dummy certificate. Please login to <a href="'.site_url().'">Our Website</a> to print you dummy '.
                        'certificate and then bring it to MTII, Nasarawa office to get the original certificate.<br /><br />'.
                        'Thank you!';
                        $mail_content = $tasks_performer->create_email_from_template('Your Registration has been Approved!', $author_message);
                        $headers = array('Content-Type: text/html; charset=UTF-8');
                        wp_mail($auth_email, 'Business Premise Approved without Dummy Certificate', $mail_content, $headers);
                        $result['status'] = "success but certificate not done";
                        $result['approved'] = true;
                    }
                }
            }
        } else {
            $result['status'] = "Request Problem";
        }
        return $result;
    }

    public function create_biz_prem_dummy_cert_and_save_as_file($invoice_number)
    {
        global $mtii_biz_prem_db_main;
        $biz_prem_main_form = $mtii_biz_prem_db_main->get_by('invoice_number_filled_against', $invoice_number);

        if (!$biz_prem_main_form) {
            return null;
            exit;
        }

        include_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-parameters-setter-and-getters.php';

        $lga_and_wards = new MtiiRelatedInformation;

        $id = $biz_prem_main_form->application_form_id;
        $biz_name = $biz_prem_main_form->name_of_company;
        $lga = $biz_prem_main_form->lga_of_company;
        $lga_code = $lga_and_wards->get_lga_code($lga);
        $biz_nature = $biz_prem_main_form->nature_of_business;
        $biz_address = $biz_prem_main_form->address_of_premise;
        $time_of_declaration = $biz_prem_main_form->time_of_declaration;
        $day_of_declaration = $biz_prem_main_form->day_of_declaration;
        $month_of_declaration = $biz_prem_main_form->month_of_declaration;
        $year_of_declaration = $biz_prem_main_form->year_of_declaration;

        $id = str_pad($id, 4, '0', STR_PAD_LEFT);
        $coop_ref_no = '26/'.$lga_code.'/a94GQ/'.$id;

        $coop_name_lower_case = strtolower(str_replace(" ", "_", trim($coop_name)));

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

        $text_day = $this->ordinal($day_of_declaration);
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

        $dpath = WP_CONTENT_DIR . "/plugins/mtii-utilities/pdftojpeg/".$coop_name_lower_case.".jpg";

        imagejpeg($img_to_write_over, $dpath);

        imagedestroy($img_to_write_over);

        return array("success" => true, "dpath" => $dpath);
    }


    /**
     * A Function to approve document
     *
     * @param [string] $invoice_number_from_doc_title The title of the document to be approved
     * @param [string] $doc_id                        The id of the document to be approved
     *
     * @return [array] $result
     */
    private function mtii_decline_biz_premises_reg($invoice_number_from_doc_title, $doc_id)
    {
        $result = array();
        global $mtii_biz_prem_db_main;
        $doc_info = $mtii_biz_prem_db_main->get_by('invoice_number_filled_against', $invoice_number_from_doc_title);
        $doc_info_from_cp = get_page_by_title($invoice_number_from_doc_title, OBJECT, 'mtii_biz_prem_reg');

        $doc_id_from_db = $doc_info->application_form_id;
        if ($doc_info_from_cp->ID == $doc_id) {
            $already_approved = $doc_info->is_admin_approved;
            if ($already_approved=="Declined") {
                $result['status'] = "Already Declined";
            } else {
                delete_post_meta($doc_info_from_cp->ID, 'is_admin_approved');
                update_post_meta($doc_info_from_cp->ID, 'is_admin_approved', 'Declined');
                $doc_info_array =  ( array ) $doc_info;
                $doc_info_array["is_admin_approved"] = "Declined";
                $updated_biz_premise = $mtii_biz_prem_db_main->update($doc_id_from_db, $doc_info_array);
                if ($updated_biz_premise == false) {
                    $result['status'] = "error";
                } else {
                    $tasks_performer = new TasksPerformer;
                    $doc_author = $doc_info->user_id;
                    $auth = get_user_by('id', $doc_author);
                    $auth_id = $auth->data->ID;
                    $auth_email = $auth->data->user_email;
                    $f_name = get_the_author_meta('first_name', $auth_id);
                    $l_name = get_the_author_meta('last_name', $auth_id);
                    $full_name = $f_name." ".$l_name;
                    $author_message = 'Hello '.$full_name.',<br /><br />'.
                        'Your registration with the invoice number <strong>'.
                        $invoice_number_from_doc_title.'</strong> has just been declined by the site Administrator.<br /><br />'.
                        '<strong>Reason for Declining: '.$_REQUEST["reason_for_decline"].'</strong><br /><br />'.
                        'You can login to <a href="'.site_url().'">our website</a> '.
                        'to adjust registration records and resubmit for re-approval.'.
                        'Thank you!';
                    $mail_content = $tasks_performer->create_email_from_template('Business Premise Application Declined!', $author_message);
                    $headers = array('Content-Type: text/html; charset=UTF-8');
                    wp_mail($auth_email, 'Business Premises Application Declined', $mail_content, $headers);
                    $result['status'] = "success";
                    $result['approved'] = false;
                }
            }
        }
        return $result;
    }


    /**
     * A Function to approve document
     *
     * @param [string] $invoice_number_from_doc_title The title of the document to be approved
     * @param [string] $doc_id    The id of the document to be approved
     *
     * @return [array] $result
     */
    private function mtii_decline_uploaded_doc($invoice_number_from_doc_title, $doc_id)
    {
        $result = array();
        $doc_info = get_page_by_title($invoice_number_from_doc_title, OBJECT, 'mtii_signed_uploads');
        if ($doc_info->ID == $doc_id) {
            $already_approved = get_post_meta($doc_id, 'admin_approved', true);
            if ($already_approved=="not approved") {
                $result['status'] = "Already Declined";
            } else {
                global $mtii_db_coop_main_form;
                global $mtii_ngo_cbo_db_table;
                $reg_catg_info = $invoice_category=='Cooperative' ?
                $mtii_db_coop_main_form->get_by('invoice_number_filled_against', $invoice_number_from_doc_title)
                : $mtii_ngo_cbo_db_table->get_by('invoice_number_filled_against', $invoice_number_from_doc_title);
                $reg_catg_info = ( array ) $reg_catg_info;
                if ($reg_catg=="Cooperative") {
                    $reg_catg_info["admin_approved"] = "Declined";
                    $doc_id_from_db =  $reg_catg_info["application_form_id"];
                    $updated_coop_premise = $mtii_db_coop_main_form->update($doc_id_from_db, $reg_catg_info);
                    if (!$wpdb->last_error) {
                        $meta_update =  update_post_meta($doc_id, 'admin_approved', 'true');
                    } else {
                        $result['status'] = "error";
                        $result['approved'] = false;
                    }
                } else {
                }
                $meta_update = update_post_meta($doc_id, 'admin_approved', 'not approved');
                if ($meta_update == false) {
                    $result['status'] = "error";
                } else {
                    $tasks_performer = new TasksPerformer;
                    $doc_author = get_post_meta($doc_id, 'user_id', single);
                    $auth = get_user_by('id', $doc_author);
                    $auth_id = $auth->data->ID;
                    $auth_email = $auth->data->user_email;
                    $f_name = get_the_author_meta('first_name', $auth_id);
                    $l_name = get_the_author_meta('last_name', $auth_id);
                    $full_name = $f_name." ".$l_name;
                    $author_message = 'Hello '.$full_name.',<br /><br />'.
                    'Your signed documents for the invoice number <strong>'.
                    $invoice_number_from_doc_title.'</strong> has just been declined by the site Administrator. '.
                    'This is because your upload was either not signed properly or not clear enough. '.
                    'You can login to <a href="'.site_url().'">our website</a> '.
                    'to upload a better signed document to finalise your registration.'.
                    'Thank you!';
                    $mail_content = $tasks_performer->create_email_from_template(
                        'Your Upload has been Declined!', $author_message
                    );
                    $headers = array('Content-Type: text/html; charset=UTF-8');
                    wp_mail($auth_email, 'MTII Upload Declined', $mail_content, $headers);
                    $result['status'] = "success";
                    $result['approved'] = false;
                }
            }
        }
        return $result;
    }


    /**
     * Checks if User is an Administrator
     *
     * @return void
     */
    private function check_if_user_is_admin()
    {
        $user = wp_get_current_user();
        $tasks_performer = new TasksPerformer;
        if (!$tasks_performer->is_mtii_admin()) {
            exit("Woof Woof Woof");
        }
    }

    /**
     * Vreifies user request Nonce
     *
     * @param [string] $the_nonce   unique id of the generated nonce
     * @param [string] $nonce_value The value of the generated nonce
     *
     * @return void
     */
    private function mtii_verify_ajax_nonce($nonce_value, $nonce_action)
    {
        // nonce check for an extra layer of security, the function will exit if it fails
        if (!wp_verify_nonce($nonce_value, $nonce_action)) {
            exit("Woof Woof Woof");
        }
    }

    /**
     * Send the reposnse of the AJAX request
     *
     * @param [array] $result Response from the Ajax request
     *
     * @return void
     */
    private function send_ajax_request_info($result)
    {
        // Check if action was fired via Ajax call. If yes, JS code will be triggered, else the user is redirected to the user page
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        } else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        // don't forget to end your scripts with a die() function - very important
        die();
    }

    /**
     * Check if Admin is to approve a specific kind of approval
     *
     * @param [array] $ngo_and_coop_approval Determines if the approval is ngo or cooperative related
     * @param [array] $biz_prem_approval     Determines if the approval is Business Premises related
     * @param [array] $others_approval       Determines if the approval is for others

     * @return void
     */
    private function confirm_approval_right($ngo_and_coop_approval=false, $biz_prem_approval=false, $others_approval=false)
    {
        $tasks_performer = new TasksPerformer;
        if (($ngo_and_coop_approval && !$tasks_performer->is_coop_and_ngo_director())
            || ($biz_prem_approval && !$tasks_performer->is_business_premises_director())
            || ($others_approval && !$tasks_performer->is_director_for_others())
        ) {
            $result = array();
            $result["info"] = "Sorry! This action cannot be completed";
            $result["status"] = "success";
            $result = json_encode($result);
            echo $result;
            die();
        }
    }


    /**
     * A Method required for uploading avatars by users
     *
     * @return void
     */
    public function mtii_signed_doc_approval()
    {
        $tasks_performer = new TasksPerformer;
        $this->mtii_verify_ajax_nonce($_REQUEST['approval_nonce'], "doc-upload-approval-nonce");
        $this->check_if_user_is_admin();
        if (isset($_REQUEST["reg_catg"]) && ($_REQUEST["reg_catg"]=="Cooperative" || $_REQUEST["reg_catg"]=="ngoAndCbo")) {
            $this->confirm_approval_right(true);
            $invoice_info_from_db = $tasks_performer->get_invoice_details_from_db($_REQUEST["doc_title"]);
            $invoice_info_from_cp = $tasks_performer->get_invoice_as_cpt($_REQUEST["doc_title"]);
            $inv_sub_catg_from_cp = get_post_meta($invoice_info_from_cp->ID, 'invoice_sub_category', true);
            $invoice_sub_catg_db = isset($invoice_info_from_db->invoice_sub_category) ?
                $invoice_info_from_db->invoice_sub_category : null;
            if ($invoice_info_from_db && $invoice_info_from_cp
                && (($inv_sub_catg_from_cp==="replacement" && $invoice_sub_catg_db==="replacement")
                || ($inv_sub_catg_from_cp==="used-replacement" && $invoice_sub_catg_db==="used-replacement"))
            ) {
                $update_info = $this->mtii_approve_cert_replacement($_REQUEST["doc_title"]);
            } else if ($invoice_info_from_db && $invoice_info_from_cp
                && (($inv_sub_catg_from_cp==="legal-search" && $invoice_sub_catg_db==="legal-search")
                || ($inv_sub_catg_from_cp==="used-legal-search" && $invoice_sub_catg_db==="used-legal-search"))
            ) {
                $update_info = $this->mtii_approve_legal_search($_REQUEST["doc_title"]);
            } else {
                $update_info = $this->mtii_approve_uploaded_doc($_REQUEST["doc_title"], $_REQUEST["doc_id"], $_REQUEST["reg_catg"]);
            }
        } else if (isset($_REQUEST["reg_catg"]) && $_REQUEST["reg_catg"]=="Business Premise") {
            $this->confirm_approval_right(false, true);
            $update_info = $this->mtii_biz_premises_approval($_REQUEST["doc_title"], $_REQUEST["doc_id"]);
        }
        $this->prepare_to_send_response($update_info);
    }

    /**
     * A Method required for uploading avatars by users
     *
     * @return void
     */
    public function mtii_signed_doc_disapproval()
    {
        $this->mtii_verify_ajax_nonce($_REQUEST['approval_nonce'], "doc-upload-approval-nonce");
        $this->check_if_user_is_admin();
        if (isset($_REQUEST["reg_catg"]) && ($_REQUEST["reg_catg"]=="Cooperative" || $_REQUEST["reg_catg"]=="ngoAndCbo")) {
            $this->confirm_approval_right(true);
            $tasks_performer = new TasksPerformer;
            $invoice_info_from_db = $tasks_performer->get_invoice_details_from_db($_REQUEST["doc_title"]);
            $invoice_info_from_cp = $tasks_performer->get_invoice_as_cpt($_REQUEST["doc_title"]);
            $inv_sub_catg_from_cp = get_post_meta($invoice_info_from_cp->ID, 'invoice_sub_category', true);
            $invoice_sub_catg_db = isset($invoice_info_from_db->invoice_sub_category) ?
                $invoice_info_from_db->invoice_sub_category : null;
            if ($invoice_info_from_db && $invoice_info_from_cp
                && (($inv_sub_catg_from_cp==="replacement" && $invoice_sub_catg_db==="replacement")
                || ($inv_sub_catg_from_cp==="used-replacement" && $invoice_sub_catg_db==="used-replacement"))
            ) {
                $update_info = $this->mtii_decline_cert_replacement($_REQUEST["doc_title"]);
                $update_info["type"] = "Replacement";
            } else if ($invoice_info_from_db && $invoice_info_from_cp
                && (($inv_sub_catg_from_cp==="legal-search" && $invoice_sub_catg_db==="legal-search")
                || ($inv_sub_catg_from_cp==="used-legal-search" && $invoice_sub_catg_db==="used-legal-search"))
            ) {
                $update_info = $this->mtii_decline_legal_search($_REQUEST["doc_title"]);
                $update_info["type"] = "Legal Search";
            } else {
                $update_info = $this->mtii_decline_registration($_REQUEST["doc_title"], $_REQUEST["doc_id"], $_REQUEST["reg_catg"]);
            }
        } else if (isset($_REQUEST["reg_catg"]) && $_REQUEST["reg_catg"]=="Business Premise") {
            $this->confirm_approval_right(false, true);
            $update_info = $this->mtii_decline_biz_premises_reg($_REQUEST["doc_title"], $_REQUEST["doc_id"]);
        } else {
            $update_info = array("this_is_it"=>"Here we go", "status"=>"Approved");
        }
        $this->prepare_to_send_response($update_info);
    }

    public function get_org_details_coop_or_ngo()
    {
        $this->mtii_verify_ajax_nonce($_REQUEST['approval_nonce'], "doc-upload-approval-nonce");
        $this->check_if_user_is_admin();
        $returned = array();
        $the_details = array();
        if (isset($_REQUEST["reg_catg"]) && ($_REQUEST["reg_catg"]=="Cooperative")) {
            global $mtii_db_coop_main_form;
            $the_details = $mtii_db_coop_main_form->get_by(
                'name_of_approved_society', str_replace("_", " ", $_REQUEST['org_to_get'])
            );
            $returned['status'] = 'success';
            $returned['for_coop_or_ngo_info_redirect'] = true;
        } else if (isset($_REQUEST["reg_catg"]) && $_REQUEST["reg_catg"]=="ngoAndCbo") {
            global $mtii_ngo_cbo_db_table;
            $the_details = $mtii_ngo_cbo_db_table->get_by(
                'name_of_approved_organization', str_replace("_", " ", $_REQUEST['org_to_get'])
            );
            $returned['status'] = 'success';
            $returned['for_coop_or_ngo_info_redirect'] = true;
        } else if (isset($_REQUEST["reg_catg"]) && $_REQUEST["reg_catg"]=="Business Premise") {
            global $mtii_biz_prem_db_main;
            $the_details = $mtii_biz_prem_db_main->get_by(
                'name_of_company', str_replace("_", " ", $_REQUEST['org_to_get'])
            );
            $returned['status'] = 'success';
            $returned['for_coop_or_ngo_info_redirect'] = true;
        }
        $returned['org'] = $the_details;
        $this->prepare_to_send_response($returned);
    }

    private function prepare_to_send_response($update_info)
    {
        if ($_REQUEST["reg_catg"]=="Cooperative" && $update_info["type"] !=="Replacement"
            && $update_info["type"] !=="Legal Search"
        ) {
            $doc_or_reg = "Document";
        } else {
            $doc_or_reg = "Registration";
        }
        if ($update_info["status"] == "success" && $update_info["for_coop_or_ngo_info_redirect"]) {
            $update_info["info"] = "Records successfully obtained";
        } else if ($update_info["status"] == "no  coop or ngo info") {
            $update_info["info"] = "We cannot find this organization information in the db";
        } else if ($update_info["status"] == "success and certificate sent" && $update_info["approved"] == true) {
            $update_info["info"] = $doc_or_reg." Approval was Successful and Dummy Certificate has been Sent";
        } else if ($update_info["status"] == "success but certificate not done" && $update_info["approved"] == true) {
            $update_info["info"] = $doc_or_reg." Approval was Successful but Dummy Certificate not Sent";
        } else if ($update_info["status"] == "success" && $update_info["approved"] == false) {
            $update_info["info"] = $doc_or_reg." Successfully Declined";
        } else if ($update_info["status"] == "success" && $update_info["approved"] == "Replacement") {
            $update_info["info"] = "Certificate Replacement successfully approved";
        } else if ($update_info["status"] == "success" && $update_info["approved"] == "Legal Search") {
            $update_info["info"] = "Legal Search Registration successfully approved";
        } else if ($update_info["status"] == "error") {
            $update_info["info"] = "There was an Error Approving ".$doc_or_reg;
        } else if ($update_info["status"] == "Already Approved") {
            $update_info["info"] = "Oops! It seems this ".$doc_or_reg." has already being Approved.";
        } else if ($update_info["status"] == "Already Declined") {
            $update_info["info"] = "Oops! It seems this ".$doc_or_reg." has already being Declined.";
        } else if ($update_info['status'] == "Request Problem") {
            $update_info["info"] = "There seem to be an issue with this request! You should contact admin";
        } else {
            $update_info["info"] = "There is an issue with your request";
        }

        $this->send_ajax_request_info($update_info);
    }

    /**
     * A method to be fired for logged out users

     *
     * @return void
     */
    public function please_login()
    {
        echo json_encode(array("status"=>"success", "msg"=>"Naaaa"));
        die();
    }

    public function create_dummy_cert_and_save_as_file($invoice_number, $invoice_category)
    {
        global $mtii_db_coop_main_form;
        global $mtii_ngo_cbo_db_table;
        $coop_doc = get_page_by_title($invoice_number, OBJECT, 'mtii_signed_uploads');
        $ngo_doc = get_page_by_title($invoice_number, OBJECT, 'mtii_ngo_lists');

        $reg_catg_info = $invoice_category=='Cooperative' ?
            $mtii_db_coop_main_form->get_by('invoice_number_filled_against', $invoice_number)
            : $mtii_ngo_cbo_db_table->get_by('invoice_number_filled_against', $invoice_number);

        if (!$reg_catg_info) {
            return null;
            exit;
        }

        $id = $reg_catg_info->application_form_id;
        $lga_and_wards = new MtiiRelatedInformation;
        if ($invoice_category=='Cooperative') {
            $registered_name = $reg_catg_info->name_of_proposed_society;
            $the_ward = $reg_catg_info->ward_of_proposed_society;
            $ward_code = $lga_and_wards->get_ward_code($the_ward);
            $the_lga = $reg_catg_info->lga_of_proposed_society;
            $date_issued = get_post_meta($coop_doc->ID, 'date_approved', true);
            $image_path = WP_CONTENT_DIR . "/plugins/mtii-utilities/pdftojpeg/certificate_dummy_coop.jpeg";

        } else if ($invoice_category=='ngoAndCbo') {
            $mtii_ngo_cbo_db_table->update($id, array("is_admin_approved" => "Approved"));
            $registered_name = $reg_catg_info->name_of_proposed_organization;
            $ward_code = '';
            $the_lga = $reg_catg_info->lga_of_proposed_organization;
            $date_issued = get_post_meta($ngo_doc->ID, 'date_approved', true);
            $image_path = WP_CONTENT_DIR . "/plugins/mtii-utilities/pdftojpeg/certificate_dummy.jpg";
        }
        $lga_code = $lga_and_wards->get_lga_code($the_lga);

        $id = str_pad($id, 4, '0', STR_PAD_LEFT);
        $coop_ref_no = $invoice_category=='Cooperative' ?
            '26/'.$lga_code.'/'.$ward_code.'/'.$id : '26/'.$lga_code.'/'.$id;

        $registered_name_lower_case = strtolower(str_replace(" ", "_", trim($registered_name)));

        $img_to_write_over = imagecreatefromjpeg($image_path);

        $color = imagecolorallocate($img_to_write_over, 80, 80, 80);
        $color2 = imagecolorallocate($img_to_write_over, 0, 0, 0);


        $font_path = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/RedHatDisplay-Bold.ttf";
        $font_path_italic = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/RedHatDisplay-MediumItalic.ttf";
        $font_path_script = WP_CONTENT_DIR . "/plugins/mtii-utilities/fonts/FREEBSC.ttf";


        $texttopright = $coop_ref_no;
        $sizetopright =10;
        $lefttopright = $invoice_category=="Cooperative" ? 620 : 495;
        $toptopright = $invoice_category=="Cooperative" ? 156 : 105;


        imagettftext($img_to_write_over, $sizetopright, 0, $lefttopright, $toptopright, $color2, $font_path, $texttopright);

        if ($invoice_category=='Cooperative') {
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
        $size2 =10;
        $left2 = $invoice_category=="Cooperative" ? 330 : 282;
        $top2 = $invoice_category=="Cooperative" ? 464 : 437;

        $text3 = "mtii Nasarawa WebApp";
        $size3 = $invoice_category=="Cooperative" ? 8 :7;
        $left3 = $invoice_category=="Cooperative" ? 350 : 330;
        $top3 = $invoice_category=="Cooperative" ? 483 : 453;

        $text4 = "mtiiNasarawawebapp";
        $size4 =16;
        $left4 = $invoice_category=="Cooperative" ? 380 : 330;
        $top4 =  $invoice_category=="Cooperative" ? 520 : 487;

        imagettftext($img_to_write_over, $size2, $angle, $left2, $top2, $color, $font_path, $text2);

        imagettftext($img_to_write_over, $size3, $angle, $left3, $top3, $color, $font_path_italic, $text3);

        imagettftext($img_to_write_over, $size4, $angle, $left4, $top4, $color2, $font_path_script, $text4);

        /**
         * Other infos related to cooperative alone
         */
        if ($invoice_category=='Cooperative') {
            $date_updated = get_post_meta($coop_doc->ID, 'date_updated', true);
            if (!$date_updated) {
                $reg_date = get_post_meta($coop_doc->ID, 'date_created', true);
            } else {
                $reg_date = $date_updated;
            }
            $time = strtotime($reg_date);
            $month_to_use = date("F", $time);
            $year_to_use = date("y", $time);
            $day_to_use = $this->ordinal(date("d", $time));
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
            imagettftext($img_to_write_over, $lga_font_size, 0, $d_date_from_left_5, $d_date_from_top_5, $color, $font_path, $the_ward);
            imagettftext($img_to_write_over, $lga_font_size, 0, $d_date_from_left_6, $d_date_from_top_5, $color, $font_path, $the_lga);
            imagettftext($img_to_write_over, 12, 0, $d_date_from_left_6, $d_date_from_top_7, $color, $font_path, 'Cooperative');

        }


        $dpath = WP_CONTENT_DIR . "/plugins/mtii-utilities/pdftojpeg/".$registered_name_lower_case.".jpg";

        imagejpeg($img_to_write_over, $dpath);

        imagedestroy($img_to_write_over);

        return array("success" => true, "dpath" => $dpath);
    }

    /**
     * A Function to approve document
     *
     * @param [string] $invoice_number_from_doc_title The title of the document to be approved
     * @param [string] $reg_id                        The id of the document to be approved
     * @param [string] $reg_catg                      The category of invoice connected to the document to approved
     *
     * @return [array] $result
     */
    private function mtii_decline_registration($invoice_number_from_doc_title, $reg_id, $reg_catg)
    {
        $result = array();
        global $mtii_ngo_cbo_db_table;
        global $mtii_db_coop_main_form;


        $reg_catg_info = $reg_catg=="Cooperative" ?
            get_page_by_title($invoice_number_from_doc_title, OBJECT, 'mtii_signed_uploads') :
            get_page_by_title($invoice_number_from_doc_title, OBJECT, 'mtii_ngo_lists');
        $reg_catg_info_in_db = $mtii_ngo_cbo_db_table->get_by('invoice_number_filled_against', $invoice_number_from_doc_title);

        if (!$reg_catg_info) {
            return null;
            exit;
        }
        if ($reg_catg_info->ID == $reg_id) {
            // $already_approved = $reg_catg=="Cooperative" ? get_post_meta($reg_id, 'admin_approved', true) :
            // get_post_meta($reg_id, 'is_admin_approved', true);
            $reg_catg=="Cooperative" ? delete_post_meta($reg_id, 'admin_approved') :
                delete_post_meta($reg_id, 'is_admin_approved');

            $reg_catg_info = $reg_catg=='Cooperative' ?
            $mtii_db_coop_main_form->get_by('invoice_number_filled_against', $invoice_number_from_doc_title)
            : $mtii_ngo_cbo_db_table->get_by('invoice_number_filled_against', $invoice_number_from_doc_title);
            $reg_catg_info = ( array ) $reg_catg_info;
            if ($reg_catg=="Cooperative") {
                $reg_catg_info["admin_approved"] = "Declined";
                $doc_id_from_db =  $reg_catg_info["application_form_id"];
                $updated_data = $mtii_db_coop_main_form->update($doc_id_from_db, $reg_catg_info);
                if ($updated_data && !$wpdb->last_error) {
                    $meta_update =  update_post_meta($doc_id, 'admin_approved', 'true');
                } else {
                    $result['status'] = "error";
                    $result['approved'] = false;
                }
            }
            $meta_update = $reg_catg=="Cooperative" ? update_post_meta($reg_id, 'admin_approved', "not approved")
                : update_post_meta($reg_id, 'is_admin_approved', "Declined");
            if ($reg_catg=="ngoAndCbo") {
                $reg_catg_info_in_db->is_admin_approved = 'Declined';
                $update_approval_in_db = $mtii_ngo_cbo_db_table->update(
                    $reg_catg_info_in_db->application_form_id,  (array) $reg_catg_info_in_db
                );
            }
            if ($meta_update == false || ($reg_catg=="ngoAndCbo" && !$update_approval_in_db)) {
                $result['status'] = "error";
                $result['status4'] = $update_approval_in_db;
                $result['status5'] = $reg_catg_info_in_db;
            } else {
                $tasks_performer = new TasksPerformer;
                $doc_author = get_post_meta($reg_id, 'user_id', single);
                $auth = get_user_by('id', $doc_author);
                $auth_id = $auth->data->ID;
                $auth_email = $auth->data->user_email;
                $f_name = get_the_author_meta('first_name', $auth_id);
                $l_name = get_the_author_meta('last_name', $auth_id);
                $full_name = $f_name." ".$l_name;
                if ($reg_catg=="Cooperative") {
                    $author_message = 'Hello '.$full_name.',<br /><br />'.
                    'Your signed documents for the invoice number <strong>'.
                    $invoice_number_from_doc_title.'</strong> has just been declined by the site Administrator. <br /><br />'.
                    '<strong>Reason for Declining: '.$_REQUEST["reason_for_decline"].' </strong><br /><br />'.
                    'You can login to <a href="'.site_url().'">our website</a> '.
                    'to upload a better signed document and re-submit for approval.'.
                    'Thank you!';
                } else {
                    $author_message = 'Hello '.$full_name.',<br /><br />'.
                    'Your registration for the invoice number <strong>'.
                    $invoice_number_from_doc_title.'</strong> has just been declined by the site Administrator.<br /><br />'.
                    '<strong>Reason for Declining: '.$_REQUEST["reason_for_decline"].'</strong><br /><br />'.
                    'You can login to <a href="'.site_url().'">our website</a> '.
                    'to adjust registration records and resubmit for re-approval.'.
                    'Thank you!';
                }
                $declined_statement = $reg_catg=="Cooperative"  ? 'Your Upload has been Declined!'
                    : 'Your Registration has been Declined!';

                $mail_content = $tasks_performer->create_email_from_template($declined_statement, $author_message);
                $headers = array('Content-Type: text/html; charset=UTF-8');
                wp_mail($auth_email, 'MTII Declined Registration', $mail_content, $headers);
                $result['status'] = "success";
                $result['approved'] = false;
            }
        }
        // else {
        //     $result['status'] = "success";
        //     $result['approved'] = false;
        //     $result['creapy'] = "CREAAAAAPPPYYY";
        //     $result['reg_catg_info'] = $reg_catg_info;
        //     $result['id'] = $reg_id;
        // }
        return $result;
    }

    private function mtii_approve_cert_replacement($invoice_number)
    {
        global $mtii_cert_replacement_table;
        global $mtii_db_invoice;
        $tasks_performer = new TasksPerformer;
        $result = array();
        $replacement_info = $mtii_cert_replacement_table->get_by('invoice_number_filled_against', $invoice_number);
        if ($replacement_info && $replacement_info!="") {
            $already_approved = isset($replacement_info->is_admin_approved) ? $replacement_info->is_admin_approved : null;
            if ($already_approved=="Approved") {
                $result['status'] = "Already Approved";
            } else {
                $invoice_numb = $replacement_info->invoice_number_filled_against;
                $org_name = $replacement_info->name_of_society_or_organization;
                $d_email =  $replacement_info->email;

                $replacement_info_as_array = ( array ) $replacement_info;
                $replacement_info_as_array['is_admin_approved'] = "Approved";
                $replacement_info_as_array['allow_edit'] = "False";

                $mtii_cert_replacement_table->update($replacement_info->application_form_id, $replacement_info_as_array);

                if ($wpdb->last_error) {
                    $result['status'] = "error";
                    $result["d_error"] = $wpdb->last_error;
                } else {
                    $replacement_info = $mtii_cert_replacement_table->get_by('invoice_number_filled_against', $invoice_number);
                    $invoice_info = $mtii_db_invoice->get_by('invoice_number', $invoice_number);
                    $invoice_info_as_array = ( array ) $invoice_info;
                    $invoice_info_as_array["invoice_sub_category"] = "used-replacement";
                    $invoice_info_as_array["invoice_status"] = "expired";
                    $invoice_info_as_array["connected_org"] = $org_name;
                    $invoice_info_as_array["start_use"] = date("Y:m:d");
                    $mtii_db_invoice->update($invoice_info->invoice_id, $invoice_info_as_array);
                    $replacement_invoice_as_cp = get_page_by_title($invoice_number, OBJECT, 'mtii_cbs_invoice');
                    delete_post_meta($replacement_invoice_as_cp->ID, 'invoice_status');
                    delete_post_meta($replacement_invoice_as_cp->ID, 'connected_org');
                    delete_post_meta($replacement_invoice_as_cp->ID, 'start_use');
                    delete_post_meta($replacement_invoice_as_cp->ID, 'invoice_sub_category');

                    update_post_meta($replacement_invoice_as_cp->ID, 'invoice_status', 'expired');
                    update_post_meta($replacement_invoice_as_cp->ID, 'invoice_sub_category', 'used-replacement');
                    update_post_meta($replacement_invoice_as_cp->ID, 'start_use', date("Y:m:d"));
                    update_post_meta($replacement_invoice_as_cp->ID, 'connected_org', $org_name);

                    $author_message = 'Hello '.$replacement_info->applicant_full_name.',<br /><br />'.
                    'Your Certificate Replacement registration for for <strong>'.$org_name.
                    '</strong> has just been Approved by the site Administrator. '.
                    'You will be notified to come for collection at our office as soon your certificate is ready'.
                    '<br /><br />Thank you!';
                    $approval_statement =  'Certificate Replacement Aproved!';
                    $mail_content = $tasks_performer->create_email_from_template($approval_statement, $author_message);
                    $headers = array('Content-Type: text/html; charset=UTF-8');
                    wp_mail($d_email, 'Approved Certificate Replacement', $mail_content, $headers);

                    $result['status'] = "success";
                    $result['approved'] = "Replacement";
                }
            }
        }
        return $result;
    }


    private function mtii_decline_cert_replacement($invoice_number)
    {
        global $mtii_cert_replacement_table;
        global $mtii_db_invoice;
        $tasks_performer = new TasksPerformer;
        $result = array();
        $replacement_info = $mtii_cert_replacement_table->get_by('invoice_number_filled_against', $invoice_number);
        if ($replacement_info && $replacement_info!="") {
            $already_approved = isset($replacement_info->is_admin_approved) ? $replacement_info->is_admin_approved : null;
            if ($already_approved=="Declined") {
                $result['status'] = "Already Declined";
            } else {
                $invoice_numb = $replacement_info->invoice_number_filled_against;
                $org_name = $replacement_info->name_of_society_or_organization;
                $d_email =  $replacement_info->email;

                $replacement_info_as_array = ( array ) $replacement_info;
                $replacement_info_as_array['is_admin_approved'] = "Declined";
                $replacement_info_as_array['allow_edit'] = true;

                $mtii_cert_replacement_table->update($replacement_info->application_form_id, $replacement_info_as_array);

                if ($wpdb->last_error) {
                    $result['status'] = "error";
                    $result["d_error"] = $wpdb->last_error;
                } else {
                    $replacement_info = $mtii_cert_replacement_table->get_by('invoice_number_filled_against', $invoice_number);
                    $invoice_info = $mtii_db_invoice->get_by('invoice_number', $invoice_number);
                    $invoice_info_as_array = ( array ) $invoice_info;
                    $invoice_info_as_array["invoice_sub_category"] = 'replacement';
                    $invoice_info_as_array["invoice_status"] = 'active';
                    $invoice_info_as_array["connected_org"] = '';
                    $invoice_info_as_array["start_use"] = '';
                    $mtii_db_invoice->update($invoice_info->invoice_id, $invoice_info_as_array);
                    $replacement_invoice_as_cp = get_page_by_title($invoice_number, OBJECT, 'mtii_cbs_invoice');
                    delete_post_meta($replacement_invoice_as_cp->ID, 'invoice_status');
                    delete_post_meta($replacement_invoice_as_cp->ID, 'connected_org');
                    delete_post_meta($replacement_invoice_as_cp->ID, 'start_use');
                    delete_post_meta($replacement_invoice_as_cp->ID, 'invoice_sub_category');

                    update_post_meta($replacement_invoice_as_cp->ID, 'invoice_sub_category', 'replacement');
                    update_post_meta($replacement_invoice_as_cp->ID, 'invoice_status', 'active');

                    $author_message = 'Hello '.$replacement_info->applicant_full_name.',<br /><br />'.
                    'Your Certificate Replacement Registration for <strong>'.$org_name.
                    '</strong> has just been Declined by the site Administrator. <br /><br />'.
                    '<strong>Reason for Declining: '.$_REQUEST["reason_for_decline"].'</strong><br /><br />'.
                    'You should reach out to the Admin to know the next step to take or go online to edit your records.'.
                    '<br /><br />Thank you!';
                    $approval_statement =  'Certificate Replacement Declined!';
                    $mail_content = $tasks_performer->create_email_from_template($approval_statement, $author_message);
                    $headers = array('Content-Type: text/html; charset=UTF-8');
                    wp_mail($d_email, 'Replacement Certificate Declined', $mail_content, $headers);

                    $result['status'] = "success";
                    $result['approved'] = false;
                }
            }
        }
        return $result;
    }

    private function mtii_approve_legal_search($invoice_number)
    {
        global $mtii_legal_search_table;
        global $mtii_db_invoice;
        $tasks_performer = new TasksPerformer;
        $result = array();
        $legal_search_info = $mtii_legal_search_table->get_by('invoice_number_filled_against', $invoice_number);
        if ($legal_search_info && $legal_search_info!="") {
            $already_approved = isset($legal_search_info->is_admin_approved) ? $legal_search_info->is_admin_approved : null;
            if ($already_approved=="Approved") {
                $result['status'] = "Already Approved";
            } else {
                $invoice_numb = $legal_search_info->invoice_number_filled_against;
                $org_name = $legal_search_info->name_of_ngo_or_cooperative;
                $d_email =  $legal_search_info->email;

                $legal_search_info_as_array = ( array ) $legal_search_info;
                $legal_search_info_as_array['is_admin_approved'] = "Approved";
                $legal_search_info_as_array['allow_edit'] = "False";

                $mtii_legal_search_table->update($legal_search_info->application_form_id, $legal_search_info_as_array);

                if ($wpdb->last_error) {
                    $result['status'] = "error";
                    $result["d_error"] = $wpdb->last_error;
                } else {
                    $legal_search_info = $mtii_legal_search_table->get_by('invoice_number_filled_against', $invoice_number);
                    $invoice_info = $mtii_db_invoice->get_by('invoice_number', $invoice_number);
                    $invoice_info_as_array = ( array ) $invoice_info;
                    $invoice_info_as_array["invoice_sub_category"] = "used-legal-search";
                    $invoice_info_as_array["invoice_status"] = "expired";
                    $invoice_info_as_array["connected_org"] = $org_name;
                    $invoice_info_as_array["start_use"] = date("Y:m:d");
                    $mtii_db_invoice->update($invoice_info->invoice_id, $invoice_info_as_array);
                    $legal_search_invoice_as_cp = get_page_by_title($invoice_number, OBJECT, 'mtii_cbs_invoice');
                    delete_post_meta($legal_search_invoice_as_cp->ID, 'invoice_status');
                    delete_post_meta($legal_search_invoice_as_cp->ID, 'connected_org');
                    delete_post_meta($legal_search_invoice_as_cp->ID, 'start_use');
                    delete_post_meta($legal_search_invoice_as_cp->ID, 'invoice_sub_category');

                    update_post_meta($legal_search_invoice_as_cp->ID, 'invoice_status', 'expired');
                    update_post_meta($legal_search_invoice_as_cp->ID, 'invoice_sub_category', 'used-legal-search');
                    update_post_meta($legal_search_invoice_as_cp->ID, 'start_use', date("Y:m:d"));
                    update_post_meta($legal_search_invoice_as_cp->ID, 'connected_org', $org_name);

                    $author_message = 'Congratulations '.$legal_search_info->applicant_full_name.',<br /><br />'.
                    'The name <strong>'.$org_name.
                    '</strong> is free and has just been Approved for registration by the Administrator. '.
                    'You should register with the name soon to avoid it being used by another organization. Thank you!'.
                    '<br /><br />Thank you!';
                    $approval_statement =  'Legal Search Name Approved!';
                    $mail_content = $tasks_performer->create_email_from_template($approval_statement, $author_message);
                    $headers = array('Content-Type: text/html; charset=UTF-8');
                    wp_mail($d_email, 'Approved Legal Search Name', $mail_content, $headers);

                    $result['status'] = "success";
                    $result['approved'] = "Legal Search";
                }
            }
        }
        return $result;
    }


    private function mtii_decline_legal_search($invoice_number)
    {
        global $mtii_legal_search_table;
        global $mtii_db_invoice;
        $tasks_performer = new TasksPerformer;
        $result = array();
        $legal_search_info = $mtii_legal_search_table->get_by('invoice_number_filled_against', $invoice_number);
        if ($legal_search_info && $legal_search_info!="") {
            $already_approved = isset($legal_search_info->is_admin_approved) ? $legal_search_info->is_admin_approved : null;
            if ($already_approved=="Declined") {
                $result['status'] = "Already Declined";
            } else {
                $invoice_numb = $legal_search_info->invoice_number_filled_against;
                $org_name = $legal_search_info->name_of_ngo_or_cooperative;
                $d_email =  $legal_search_info->email;

                $legal_search_info_as_array = ( array ) $legal_search_info;
                $legal_search_info_as_array['is_admin_approved'] = "Declined";
                $legal_search_info_as_array['allow_edit'] = true;

                $mtii_legal_search_table->update($legal_search_info->application_form_id, $legal_search_info_as_array);

                if ($wpdb->last_error) {
                    $result['status'] = "error";
                    $result["d_error"] = $wpdb->last_error;
                } else {
                    $legal_search_info = $mtii_legal_search_table->get_by('invoice_number_filled_against', $invoice_number);
                    $invoice_info = $mtii_db_invoice->get_by('invoice_number', $invoice_number);
                    $invoice_info_as_array = ( array ) $invoice_info;
                    $invoice_info_as_array["invoice_sub_category"] = 'legal-search';
                    $invoice_info_as_array["invoice_status"] = 'active';
                    $invoice_info_as_array["connected_org"] = '';
                    $invoice_info_as_array["start_use"] = '';
                    $mtii_db_invoice->update($invoice_info->invoice_id, $invoice_info_as_array);
                    $legal_search_invoice_as_cp = get_page_by_title($invoice_number, OBJECT, 'mtii_cbs_invoice');
                    delete_post_meta($legal_search_invoice_as_cp->ID, 'invoice_status');
                    delete_post_meta($legal_search_invoice_as_cp->ID, 'connected_org');
                    delete_post_meta($legal_search_invoice_as_cp->ID, 'start_use');
                    delete_post_meta($legal_search_invoice_as_cp->ID, 'invoice_sub_category');

                    update_post_meta($legal_search_invoice_as_cp->ID, 'invoice_sub_category', 'legal-search');
                    update_post_meta($legal_search_invoice_as_cp->ID, 'invoice_status', 'active');

                    $author_message = 'Hello '.$legal_search_info->applicant_full_name.',<br /><br />'.
                    'Sorry the  name <strong>'.$org_name.
                    '</strong> has just been Declined by the site Administrator. <br /><br />'.
                    '<strong>Reason for Declining: '.$_REQUEST["reason_for_decline"].'</strong><br /><br />'.
                    'You can login again to edit the society name for a re-approval. Thank You!.'.
                    '<br /><br />Thank you!';
                    $approval_statement =  'Legal Search Name Declined!';
                    $mail_content = $tasks_performer->create_email_from_template($approval_statement, $author_message);
                    $headers = array('Content-Type: text/html; charset=UTF-8');
                    wp_mail($d_email, 'Declined Legal Search Name', $mail_content, $headers);

                    $result['status'] = "success";
                    $result['approved'] = false;
                }
            }
        }
        return $result;
    }
}
?>