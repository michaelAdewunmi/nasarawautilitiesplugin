<?php
/**
 * This file basically performs tasks related to uploading images via cloudinary
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
namespace MtiiUtilities;

use MtiiUtilities\RegistrationUtilities;
use MtiiUtilities\TasksPerformer;

/**
 * This class is used to perform tasks related to uploading images via cloudinary
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */

class CloudinaryUpload
{
    private $_supported_files = array(
        'image/jpeg',
        'image/pjpeg',
        'image/png',
        "application/pdf",
    );

    public $errors = array();

    private $_allowed_extensions = array("png", "jpg", "jpeg", "pdf");

    private $_existing_upload_public_id;

    private $_invoice_number_against_doc;

    private $_upload_completed_successfully = false;

    private $_adding_upload_to_db_failed = false;

    public $updating_coop_info = false;

    private $_multiple_uploads = false;

    private $_files_key_array = null;

    private $_uploaded_file_info = array();

    public function __construct($multiple_uploads=false, $files_key_array=null)
    {
        $this->_multiple_uploads = $multiple_uploads;
        $this->_files_key_array = $files_key_array;
        $this->_upload_file_to_cloudinary();
    }

    public function get_uploaded_file_info()
    {
        return $this->_uploaded_file_info;
    }

    public function get_if_upload_is_completed()
    {
        return $this->_upload_completed_successfully;
    }

    public function get_upload_failed_status()
    {
        return $this->_adding_upload_to_db_failed;
    }

    private function _do_cloudinary_config()
    {
        \Cloudinary::config(
            array(
                "cloud_name"    => "ministry-of-trade-industry-and-investment",
                "api_key"       => "231631216494489",
                "api_secret"    => "WGnKK5KEBvF3ezUzU8BOcReZhUo",
                "secure"        => true
            )
        );
    }

    private function _get_invoice_number()
    {
        $reg_util = new RegistrationUtilities;
        $invoice_info = $reg_util->get_invoice_info_from_db();
        return isset($invoice_info->invoice_number) ? $invoice_info->invoice_number : '';
    }

    private function _check_existing_upload($invoice_number=null)
    {
        if ($invoice_number=="check invoice from cookie") {
            $invoice_number = $this->_get_invoice_number();
        }
        if ($invoice_number!=null) {
            if (!$this->_multiple_uploads) {
                return get_page_by_title($invoice_number, OBJECT, 'mtii_signed_uploads');
            } else {
                $task_performer = new TasksPerformer;
                $file_uploads = $task_performer->get_file_uploads_in_options();
                return isset($file_uploads[$invoice_number]) ? $file_uploads[$invoice_number] : null;
            }
        } else {
            return null;
        }
    }

    private function _show_approval_and_decline_btns( $for_approval )
    {
        $d_output = !$for_approval ? '' :
            '<div><p class="doc-apprv-btn round-btn-mtii small-btn for-appr">Approve Doc</a>'.
            '<p class="doc-decl-btn round-btn-mtii small-btn for-decl">Decline Doc</a></div>';
        echo $d_output;
    }

    private function _show_image($img, $options = array(), $caption = '', $for_approval=false, $is_approved=false)
    {

        if ($is_approved=="true") {
            $class_info = 'class="is-appproved"';
        } else if ($is_approved=="not approved") {
            $class_info = 'class="not-appproved"';
        } else {
            $class_info = '';
        }
        if (!$for_approval) :
            echo '<h3>There is an Existing signed document for this registration (You can click to preview or scroll down to change document)</h3>';
        endif;
        $options['format'] = $img['format'];
        $transformation_url = cloudinary_url($img['public_id'], $options);
        $d_output = '<div class="item" style="margin-bottom: 20px; margin-top: 20px;">';
        $d_output .= '<div class="caption">' . $caption . '</div>';
        $d_output .= '<a href="' . $img['secure_url'] . '" target="_blank" '.$class_info.'>';
        $d_output .= cl_image_tag($img['public_id'], $options) . '</a>';
        $d_output .= '</div>';
        echo $d_output;
    }

    public function get_existing_doc_and_show_thumbnail($invoice_number=null, $for_approval=false, $is_approved=null, $is_replacement=false)
    {
        $invoice_number = $invoice_number==null ? "check invoice from cookie" : $invoice_number;
        if ($is_replacement) {
            $existing_doc = get_page_by_title($invoice_number, OBJECT, 'mtii_cert_replcmnt');
            $secure_url = null;
        } else {
            $existing_doc = $this->_check_existing_upload($invoice_number);
            $doc_id = isset($existing_doc->ID) ? $existing_doc->ID : null;
            $secure_url = get_post_meta($doc_id, 'secure_url', true);
        }
        if ($existing_doc) {
            $parsed_existing_doc = json_decode($existing_doc->post_content, true);
            if (!$is_replacement && $parsed_existing_doc["secure_url"] === $secure_url) {
                $this->parse_for_thumbnail_rendering($parsed_existing_doc, $for_approval, $is_approved);
            } else {
                foreach ($this->_files_key_array as $file_key) {
                    $parsed_existing_doc_inner = $parsed_existing_doc[$file_key];
                    $title = str_replace("_", " ", $file_key);
                    $this->parse_for_thumbnail_rendering($parsed_existing_doc_inner, $for_approval, $is_approved, $title);
                }
            }
            $this->_show_approval_and_decline_btns($for_approval);
        } else {
            return null;
        }
    }

    private function parse_for_thumbnail_rendering($parsed_existing_doc, $for_approval, $is_approved, $title=false)
    {
        $this->_do_cloudinary_config();
        $secure_url = $parsed_existing_doc["secure_url"];
        $path = parse_url($secure_url, PHP_URL_PATH);
        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $name = pathinfo($path, PATHINFO_FILENAME);
        if ($extension=="png" || $extension=="jpg" || $extension=="jpeg") {
            echo $title ? '<p class="doc-title">'.$title.'</p>' : '';
            $this->_show_image(
                $parsed_existing_doc,
                array(
                    'width' => !$for_approval ? $parsed_existing_doc['width'] : 300,
                    'crop' => 'fill'
                ), '', $for_approval, $is_approved
            );
        } else {
            echo '<div class="item" style="margin-bottom: 20px">';
            if (!$for_approval) :
                echo '<h3>There is an Existing signed document for this registration (You can click to '.
                        'preview or scroll down to change document)</h3>';
            endif;
            echo $title ? '<p class="doc-title">'.$title.'</p>' : '';
            echo '<a class="pdf-wrapper" href="'.$secure_url.'" target="_blank">PDF</a>';
            echo '</div>';
        }
    }

    private function downloadFile($url, $path)
    {
        $newfname = $path;
        $file = fopen ($url, 'rb');
        if ($file) {
            $newf = fopen ($newfname, 'wb');
            if ($newf) {
                while(!feof($file)) {
                    fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
                }
            }
        }
        if ($file) {
            fclose($file);
        }
        if ($newf) {
            fclose($newf);
        }

        return $path;
    }

    private function genPdfThumbnail($source, $target)
	{
		//$source = realpath($source);
		$target = dirname($source).DIRECTORY_SEPARATOR.$target;
        //$im     = new Imagick($source."[0]"); // 0-first page, 1-second page
        echo file_exists($source);
        $f = fopen($source, 'r');
        //echo $f;
        //$f = fseek($f, 0);
        $im = new \Imagick($f); // 0-first page, 1-second page
		$im->setImageColorspace(255); // prevent image colors from inverting
		$im->setimageformat("jpeg");
		$im->thumbnailimage(160, 120); // width and height
		$im->writeimage($target);
		$im->clear();
		$im->destroy();
	}

    private function show_pdf_thumbnail($path, $file_name) {
        $loc = $path;
        $pdf = $file_name;
        $format = "jpg";
        $dest = "$loc$pdf.$format";

        if (file_exists($dest)) {
            $im = new \imagick();
            $im->readImage($dest);
            header( "Content-Type: image/jpg" );
            echo $im;
            exit;
        } else {
            // echo $loc.$pdf.'.pdf';
            // echo file_exists($loc.$pdf.'.pdf');
            // $f = fopen($loc.$pdf.'.pdf', 'r');
            // fseek($f, 0);
            // echo $f;

            //$loc.$pdf.'.pdf'.'[0]'

            $im = new \imagick($loc.$pdf.'.pdf'.'[0]');
            $im->setImageFormat($format);
            $width = $im->getImageheight();
            $im->cropImage($width, $width, 0, 0);
            $im->scaleImage(110, 167, true);

            $im->writeImage($dest);

            header( "Content-Type: image/jpg" );
            echo $im;
        }

    }

    private function _get_existing_upload_pub_id($upload_id)
    {
        return get_post_meta($upload_id, 'public_id', true);
    }

    private function _validate_file_type($file, $allowed_files) {
        if (!in_array($file["type"], $allowed_files)) {
            return false;
        } else {
            return true;
        }
    }

    // private function _check_extension($file) {
    //     $temp = explode(".", $file["name"]);
    //     if(in_array(end($temp), $this->_allowed_extensions)) {
    //         return true;
    //     }else{
    //         return false;
    //     }
    // }

    private function _check_file_size($file) {
        if ($file["size"] < 500000) {
            return true;
        } else {
            return false;
        }
    }

    private function _validate_file_before_upload()
    {
        if ($this->_files_key_array ) {
            if (isset($_FILES) AND !empty($_FILES)) {
                foreach ($this->_files_key_array as $file_name) {
                    if (!$this->_validate_file_type($_FILES[$file_name], $this->_supported_files)) {
                        $this->errors[] = "Invalid File Type for  ".ucwords(str_replace("_", " ", $file_name)).". Only Pdfs and Images are allowed";
                    }
                    if (!$this->_check_file_size($_FILES[$file_name])) {
                        $this->errors[] = ucwords(str_replace("_", " ", $file_name))." File too Large (File should not be more than 500kb)";
                    }
                }
            }
        }
    }

    private function _add_uploaded_doc_info_to_db($invoice_number, $file_info_array, $file_key=null)
    {
        $task_performer = new TasksPerformer;
        $this->_uploaded_file_info[$file_key] = $file_info_array;
        if (!$this->_multiple_uploads) {
            return $task_performer->add_signed_doc_as_custom_post($invoice_number, $file_info_array);
        } else {
            return $task_performer->add_array_as_option(
                'file_uploads_for_Replacement', $invoice_number, $file_key, $file_info_array['secure_url']
            );
        }
    }

    public function check_and_get_errors()
    {
        if (!empty($_FILES) AND count($this->errors) !== 0) {
            $error_output = '';
            $error_output.= '<div class="section-body">';
            $error_output.= '<h2 class="section-heading errored-text">Error!</h2>';
            $error_output.= '<hr class="header-lower-rule errored-bg" />';
            $error_output.= '<div class="payment-err">';
            $error_output.= '<div class="notification-wrapper">';
            $error_output.= '<div class="mtii_reg_errors"><h4 style="color: red;">Please fix the following errors</h4>';
            $error_output.= '<ul style="padding: 0;">';
            foreach ($this->errors as $error) {
                $error_output .= '<li class="error">'.$error.'</li>';
            }
            $error_output.= '</ul>';
            $error_output .= '</div>';
            $error_output .= '</div>';
            $error_output .= '</div>';
            $error_output .= '</div>';
            return $error_output;
        }
    }

    private function _upload_file_to_cloudinary()
    {
        if ((isset($_POST["mtii_upload_submit"]) && isset($_POST['form_register_upload_nonce'])
            && wp_verify_nonce($_POST['form_register_upload_nonce'], 'form-register-upload-nonce'))
            || (isset($_POST["mtii_form_submit"]) && isset($_POST['main_registration_nonce'])
            && wp_verify_nonce($_POST['main_registration_nonce'], 'main-registration-nonce'))
        ) {
            $current_user = wp_get_current_user();
            $current_user_meta = get_user_meta($current_user->ID);
            $this->_validate_file_before_upload();

            if (!empty($_FILES) && count($this->errors)>0) {
                //print_r($this->errors);
                $the_errors = $this->check_and_get_errors();
            } else {
                $attachments = array();
                foreach ($this->_files_key_array as $file_name) {
                    $temp = explode(".", $_FILES[$file_name]["name"]);
                    $extension = end($temp);
                    $user = $current_user_meta['first_name'][0] . " " . $current_user_meta['last_name'][0];
                    $newname=  $file_name. "_" . rand(10, 500000000) . "_" . str_replace(" ", "_", $user) . ".".$extension;
                    rename($_FILES[$file_name]["tmp_name"], $newname);
                    $attachments[$file_name] = $newname;
                }

                if (!empty($attachments)) {
                    $this->_do_cloudinary_config();
                    //echo $this->_invoice_number_against_doc;
                    $invoice_number = $this->_get_invoice_number();
                    $existing_upload = $this->_check_existing_upload($invoice_number);

                    if ($existing_upload!=null && $existing_upload!="") {
                        if (!$this->_multiple_uploads) {
                            $existing_upload_public_id = $this->_get_existing_upload_pub_id($existing_upload->ID);
                            $this->delete_uploaded_doc($existing_upload_public_id);
                        } else {
                            foreach ($existing_upload as $key => $val) {
                                $this->delete_uploaded_doc($val);
                            }
                        }
                    }

                    foreach ($attachments as $key => $att) {
                        $files = array();
                        try {
                            $files[$key] = \Cloudinary\Uploader::upload(realpath($att));
                            $upload_doc = $this->_add_uploaded_doc_info_to_db($invoice_number, $files[$key], $key);
                            if ($upload_doc=="Document uploaded and added to Database" || $upload_doc=="Document upload successfully updated") {
                                $this->_upload_completed_successfully = true;
                                $this->_adding_upload_to_db_failed = false;
                                if (!$this->_multiple_uploads) {
                                    $this->update_coop_info_as_awaiting_approval($invoice_number);
                                }
                            } else if ($upload_doc=="There is an Error") {
                                $this->_adding_upload_to_db_failed = true;
                                $this->_upload_completed_successfully = false;
                            }
                            unlink($att);
                        } catch (Exception $e) {
                            echo '<p class="single-paragraph-error">There was a problem! Please Refresh your browser and try again</p>';
                            unlink($att);
                        }
                    }
                }
            }
        }
    }

    private function update_coop_info_as_awaiting_approval($invoice_number)
    {
        global $mtii_db_coop_main_form;
        global $wpdb;
        $uploaded_coop_info = $mtii_db_coop_main_form->get_by('invoice_number_filled_against', $invoice_number);
        $uploaded_coop_info_array = (array) $uploaded_coop_info;
        $uploaded_coop_info_array["admin_approved"] = "Awaiting Approval";
        $mtii_db_coop_main_form->update($uploaded_coop_info->application_form_id, $uploaded_coop_info_array);
        if ($wpdb->last_error) {
            $this->updating_coop_info = "Not Updated";
        }
    }

    public function delete_uploaded_doc($link_or_id=null)
    {
        $this->_do_cloudinary_config();
        if (!$link_or_id || $link_or_id==='') {
            return;
        } else {
            if (wp_http_validate_url($link_or_id)) {
                $existing_upload_public_id = explode(".", basename($link_or_id))[0]; //Gets the publicid from the url
            } else {
                $existing_upload_public_id = $link_or_id;
            }
            try {
                \Cloudinary\Uploader::destroy($existing_upload_public_id);
            } catch (\Throwable $th) {
                echo "There was an Error deleting old uploads";
            }
        }
    }
}