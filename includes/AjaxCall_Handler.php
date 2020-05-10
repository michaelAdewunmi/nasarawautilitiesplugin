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

use MtiiUtilities\Tasks_Performer;

class AjaxCall_Handler
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


    private $_tasks_performer = null;

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
        $this->_tasks_performer = new Tasks_Performer;
    }


    /**
     * A Function to approve document
     *
     * @param [string] $invoice_number_from_doc_title The title of the document to be approved
     * @param [string] $doc_id    The id of the document to be approved
     *
     * @return [array] $result
     */
    private function mtii_approve_uploaded_doc($invoice_number_from_doc_title, $doc_id)
    {
        $result = array();
        $doc_info = get_page_by_title($invoice_number_from_doc_title, OBJECT, 'mtii_signed_uploads');
        if ($doc_info->ID == $doc_id) {
            $already_approved = get_post_meta($doc_id, 'admin_approved', true);

            if ($already_approved=="true") {
                $result['status'] = "Already Approved";
            } else {
                $meta_update = update_post_meta($doc_id, 'admin_approved', 'true');
                if ($meta_update == false) {
                    $result['status'] = "error";
                } else {
                    $task_performer = $this->_tasks_performer;
                    $doc_author = get_post_meta($doc_id, 'user_id', single);
                    $auth = get_user_by('id', $doc_author);
                    $auth_id = $auth->data->ID;
                    $auth_email = $auth->data->user_email;
                    $f_name = get_the_author_meta('first_name', $auth_id);
                    $l_name = get_the_author_meta('last_name', $auth_id);
                    $full_name = $f_name." ".$l_name;
                    $created_image_info = $this->create_coop_dummy_cert_and_save_as_file($invoice_number_from_doc_title);
                    if ($created_image_info && $created_image_info!=null && is_array($created_image_info)) {
                        if ($created_image_info["success"]) {
                            $author_message = 'Congratulations '.$full_name.',<br /><br />'.
                            'Your uploaded signed documents for the invoice number <strong>'.
                            $invoice_number_from_doc_title.'</strong> has just been approved by the site Administrator.<br /><br />'.
                            'Attached to this email is your dummy Certificate. You should download and bring your dummy certificate '.
                            'to MTII, Nassarawa office to get your original certificate.<br /><br />'.
                            'Thank you!';
                            $mail_content = $task_performer->create_email_from_template('Your Upload has been Approved!', $author_message);
                            $headers = array('Content-Type: text/html; charset=UTF-8');
                            wp_mail($auth_email, 'MTII Upload Approval', $mail_content, $headers, $created_image_info["dpath"]);
                            $result['status'] = "success and certificate sent";
                            $result['approved'] = true;
                        } else {
                            $author_message = 'Congratulations '.$full_name.',<br /><br />'.
                            'Your uploaded signed documents for the invoice number <strong>'.
                            $invoice_number_from_doc_title.'</strong> has just been approved by the site Administrator.<br /><br />'.
                            'You are supposed to have your dummy certificate in this email but we had a problem creating and sending '.
                            'your dummy certificate. Please login to <a href="'.site_url().'">Our Website</a> to print you dummy '.
                            'certificate and then bring it to MTII, Nassarawa office to get the original certificate.<br /><br />'.
                            'Thank you!';
                            $mail_content = $task_performer->create_email_from_template('Your Upload has been Approved!', $author_message);
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
                        'certificate and then bring it to MTII, Nassarawa office to get the original certificate.<br /><br />'.
                        'Thank you!';
                        $mail_content = $task_performer->create_email_from_template('Your Upload has been Approved!', $author_message);
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
        $doc_id_from_db = $doc_info->application_form_id;
        if ($doc_id_from_db == $doc_id) {
            $already_approved = $doc_info->is_admin_approved;
            if ($already_approved=="Approved") {
                $result['status'] = "Already Approved";
            } else {
                $doc_info_array =  ( array ) $doc_info;
                $doc_info_array["is_admin_approved"] = "Approved";
                $updated_biz_premise = $mtii_biz_prem_db_main->update($doc_id_from_db, $doc_info_array);
                if ($updated_biz_premise == false) {
                    $result['status'] = "error";
                } else {
                    $task_performer = $this->_tasks_performer;
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
                            'to MTII, Nassarawa office to get your original certificate.<br /><br />'.
                            'Thank you!';
                            $mail_content = $task_performer->create_email_from_template('Your Registration has been Approved!', $author_message);
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
                            'certificate and then bring it to MTII, Nassarawa office to get the original certificate.<br /><br />'.
                            'Thank you!';
                            $mail_content = $task_performer->create_email_from_template('Your Registration has been Approved!', $author_message);
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
                        'certificate and then bring it to MTII, Nassarawa office to get the original certificate.<br /><br />'.
                        'Thank you!';
                        $mail_content = $task_performer->create_email_from_template('Your Registration has been Approved!', $author_message);
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

        /**
     * A Function to approve document
     *
     * @param [string] $invoice_number_from_doc_title The title of the document to be approved
     * @param [string] $doc_id    The id of the document to be approved
     *
     * @return [array] $result
     */
    private function mtii_decline_biz_premises_reg($invoice_number_from_doc_title, $doc_id)
    {
        $result = array();
        global $mtii_biz_prem_db_main;
        $doc_info = $mtii_biz_prem_db_main->get_by('invoice_number_filled_against', $invoice_number_from_doc_title);
        $doc_id_from_db = $doc_info->application_form_id;
        if ($doc_info->application_form_id == $doc_id) {
            $already_approved = $doc_info->is_admin_approved;
            if ($already_approved=="Declined") {
                $result['status'] = "Already Declined";
            } else {
                $doc_info_array =  ( array ) $doc_info;
                $doc_info_array["is_admin_approved"] = "Declined";
                $updated_biz_premise = $mtii_biz_prem_db_main->update($doc_id_from_db, $doc_info_array);
                if ($updated_biz_premise == false) {
                    $result['status'] = "error";
                } else {
                    $task_performer = $this->_tasks_performer;
                    $doc_author = $doc_info->user_id;
                    $auth = get_user_by('id', $doc_author);
                    $auth_id = $auth->data->ID;
                    $auth_email = $auth->data->user_email;
                    $f_name = get_the_author_meta('first_name', $auth_id);
                    $l_name = get_the_author_meta('last_name', $auth_id);
                    $full_name = $f_name." ".$l_name;
                    $author_message = 'Hello '.$full_name.',<br /><br />'.
                    'Your Registration with the invoice number <strong>'.
                    $invoice_number_from_doc_title.'</strong> has just been declined by the site Administrator. '.
                    'This could be due to a problem with your application. '.
                    'You can login to <a href="'.site_url().'">our website</a> '.
                    'to edit your Application in order to reinitiate the approval process by Admin.'.
                    'Thank you!';
                    $mail_content = $task_performer->create_email_from_template(
                        'Business Premise Application Declined!', $author_message
                    );
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
                $meta_update = update_post_meta($doc_id, 'admin_approved', 'not approved');
                if ($meta_update == false) {
                    $result['status'] = "error";
                } else {
                    $task_performer = $this->_tasks_performer;
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
                    $mail_content = $task_performer->create_email_from_template(
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
        if (!in_array('administrator', $user->roles)) {
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
    private function mtii_verify_ajax_nonce($the_nonce, $nonce_value)
    {
        // nonce check for an extra layer of security, the function will exit if it fails
        if ( !wp_verify_nonce($the_nonce, $nonce_value)) {
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
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $result = json_encode($result);
            echo $result;
        } else {
            header("Location: ".$_SERVER["HTTP_REFERER"]);
        }

        // don't forget to end your scripts with a die() function - very important
        die();
    }


    /**
     * A Method required for uploading avatars by users
     *
     * @return void
     */
    public function mtii_signed_doc_approval()
    {
        $this->mtii_verify_ajax_nonce($_REQUEST['approval_nonce'], "doc-upload-approval-nonce");
        $this->check_if_user_is_admin();
        if (isset($_REQUEST["reg_catg"])=="Cooperative" && $_REQUEST["reg_catg"]=="Cooperative") {
            $update_info = $this->mtii_approve_uploaded_doc($_REQUEST["doc_title"], $_REQUEST["doc_id"]);
        } else if (isset($_REQUEST["reg_catg"]) && $_REQUEST["reg_catg"]=="Business Premise") {
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
        if (isset($_REQUEST["reg_catg"])=="Cooperative" && $_REQUEST["reg_catg"]=="Cooperative") {
            $update_info = $this->mtii_decline_uploaded_doc($_REQUEST["doc_title"], $_REQUEST["doc_id"]);
        } else if (isset($_REQUEST["reg_catg"]) && $_REQUEST["reg_catg"]=="Business Premise") {
            $update_info = $this->mtii_decline_biz_premises_reg($_REQUEST["doc_title"], $_REQUEST["doc_id"]);
        } else {
            $update_info = array("this_is_it"=>"Here we go", "status"=>"Approved");
        }
        $this->prepare_to_send_response($update_info);
    }

    private function prepare_to_send_response($update_info)
    {
        $doc_or_reg = $_REQUEST["reg_catg"]=="Cooperative" ? "Document" : "Registration";
        if ($update_info["status"] == "success and certificate sent" && $update_info["approved"] == true) {
            $update_info["info"] = $doc_or_reg." Approval was Successful and Dummy Certificate has been Sent";
        } else if ($update_info["status"] == "success but certificate not done" && $update_info["approved"] == true) {
            $update_info["info"] = $doc_or_reg." Approval was Successful but Dummy Certificate not Sent";
        } else if ($update_info["status"] == "success" && $update_info["approved"] == false) {
            $update_info["info"] = $doc_or_reg." Successfully disapproved";
        } else if ($update_info["status"] == "error") {
            $update_info["info"] = "There was an Error Approving ".$doc_or_reg;
        } else if ($update_info["status"] == "Already Approved") {
            $update_info["info"] = "Oops! It seems this ".$doc_or_reg." has already being Approved.";
        } else if ($update_info["status"] == "Already Declined") {
            $update_info["info"] = "Oops! It seems this ".$doc_or_reg." has already being Declined.";
        } else if ($update_info['status'] == "Request Problem") {
            $update_info["info"] = "There seem to be an issue with this request! You should contact admin";
        }

        $this->send_ajax_request_info($update_info);
    }

    public function create_coop_dummy_cert_and_save_as_file($invoice_number)
    {
        global $mtii_db_coop_main_form;

        $reg_catg_info = $mtii_db_coop_main_form->get_by('invoice_number_filled_against', $invoice_number);

        if (!$reg_catg_info) {
            return null;
            exit;
        }

        include_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-parameters-setter-and-getters.php';

        $lga_and_wards = new Mtii_Parameters_Setter_And_Getter;

        $id = $reg_catg_info->application_form_id;
        $coop_name = $reg_catg_info->name_of_proposed_society;
        $ward_code = $lga_and_wards->get_ward_code($reg_catg_info->ward_of_proposed_society);
        $lga_code = $lga_and_wards->get_lga_code($reg_catg_info->lga_of_proposed_society);

        $id = str_pad($id, 4, '0', STR_PAD_LEFT);
        $coop_ref_no = '26/'.$lga_code.'/'.$ward_code.'/'.$id;

        $coop_name_lower_case = strtolower(str_replace(" ", "_", trim($coop_name)));


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


        $dpath = WP_CONTENT_DIR . "/plugins/mtii-utilities/pdftojpeg/".$coop_name_lower_case.".jpg";

        imagejpeg($img_to_write_over, $dpath);

        imagedestroy($img_to_write_over);

        return array("success" => true, "dpath" => $dpath);
    }

    public function ordinal($number) {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13))
            return $number. 'th';
        else
            return $number. $ends[$number % 10];
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

        $lga_and_wards = new Mtii_Parameters_Setter_And_Getter;

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
     * A method to be fired for logged out users

     *
     * @return void
     */
    public function please_login()
    {
        echo "You must logged in to Approve an image";
        die();
    }
}

