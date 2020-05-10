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

require_once WP_CONTENT_DIR . '/plugins/mtii-utilities/lib/cloudinary/autoload.php';
require_once WP_CONTENT_DIR . '/plugins/mtii-utilities/lib/cloudinary/src/Helpers.php';
// require_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-utilities-task-performer.php';
// require_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/class-mtii-registration-utilities.php';

class Cloudinary_Upload
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

    public function __construct()
    {
        $this->_upload_file_to_cloudinary();
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
        $reg_util = new Mtii_Registration_Utilities();
        $invoice_info = $reg_util->get_invoice_info_from_db();
        return isset($invoice_info->invoice_number) ? $invoice_info->invoice_number : '';
    }

    private function _check_existing_upload($invoice_number=null)
    {
        if ($invoice_number=="check invoice from cookie") {
            $invoice_number = $this->_get_invoice_number();
        }
        if ($invoice_number!=null) {
            return get_page_by_title($invoice_number, OBJECT, 'mtii_signed_uploads');
        } else {
            return null;
        }
    }

    private function _show_image($img, $options = array(), $caption = '', $for_approval=false, $is_approved)
    {
        $options['format'] = $img['format'];
        $transformation_url = cloudinary_url($img['public_id'], $options);

        if ($is_approved=="true") {
            $class_info = 'class="is-appproved"';
        } else if ($is_approved=="not approved") {
            $class_info = 'class="not-appproved"';
        } else {
            $class_info = '';
        }
        echo !$for_approval ?
            '<h3>There is an Existing signed document for this registration (You can scroll down to change document)</h3>' : '';
        echo '<div class="item">';
        echo '<div class="caption">' . $caption . '</div>';
        echo '<a href="' . $img['url'] . '" target="_blank" '.$class_info.'>' . cl_image_tag($img['public_id'], $options) . '</a>';
        echo !$for_approval ?
                //'<div class="link"><a target="_blank" href="' . $transformation_url . '">Click Here to Download</a></div>'
                ''
                :
                '<div><p class="doc-apprv-btn round-btn-mtii small-btn for-appr">Approve Doc</a>'.
                '<p class="doc-decl-btn round-btn-mtii small-btn for-decl">Decline Doc</a></div>';
        echo '</div>';
    }

    public function get_existing_doc_and_show_thumbnail($invoice_number=null, $for_approval=false, $is_approved=null)
    {
        $invoice_number = $invoice_number==null ? "check invoice from cookie" : $invoice_number;
        $existing_doc =$this->_check_existing_upload($invoice_number);
        if ($existing_doc) {
            $parsed_existing_doc = json_decode($existing_doc->post_content, true);
            $secure_url = get_post_meta($existing_doc->ID, 'secure_url', true);
            if ($parsed_existing_doc["secure_url"] === $secure_url) {
                $this->_do_cloudinary_config();
                $path = parse_url($secure_url, PHP_URL_PATH);
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                $name = pathinfo($path, PATHINFO_FILENAME);
                if ($extension=="png" || $extension=="jpg" || $extension=="jpeg") {
                    $this->_show_image(
                        $parsed_existing_doc,
                        array(
                            'width' => !$for_approval ? $parsed_existing_doc['width'] : 300,
                            'crop' => 'fill'
                        ), '', $for_approval, $is_approved
                    );
                } else {
                    //lee-campbell-GI6L2pkiZgQ-unsplash.jpg
                    $dest_loc= WP_CONTENT_DIR . "/plugins/mtii-utilities/pdftojpeg/"; //.$name.".".$extension;
                    //$new_file = $this->downloadFile($secure_url, $dest_loc);
                    //echo $this->show_pdf_thumbnail($dest_loc, $name);
                    //echo $this->genPdfThumbnail($dest_loc, $name);
                    // if (file_exists($new_file)) {
                    //     echo "Hell Yeah!";
                    // }
                    get_post_meta($existing_doc->ID, 'secure_url', true);
                    echo '<div class="item">';
                    if (!$for_approval) :
                        echo '<h3>There is an Existing signed document for this registration (You can click to '.
                                'preview or scroll down to change document)</h3>';
                    endif;
                    echo '<a class="pdf-wrapper" href="'.$secure_url.'">PDF</a>';
                    if ($for_approval) :
                        echo '<div><p class="doc-apprv-btn round-btn-mtii small-btn for-appr">Approve Doc</a>';
                        echo '<p class="doc-decl-btn round-btn-mtii small-btn for-decl">Decline Doc</a></div>';
                    endif;
                    echo '</div>';
                }
            }
        } else {
            return null;
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
        $im = new Imagick($f); // 0-first page, 1-second page
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
            $im = new imagick();
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

            $im = new imagick($loc.$pdf.'.pdf'.'[0]');
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

    private function _check_extension($file) {
        $temp = explode(".", $file["name"]);
        if(in_array(end($temp), $this->_allowed_extensions)) {
            return true;
        }else{
            return false;
        }
    }

    private function _check_file_size($file) {
        if($file["size"] < 500000) {
            return true;
        }else{
            return false;
        }
    }

    private function _validate_file_before_upload()
    {
        if (isset($_FILES) AND !empty($_FILES)) {
            if (!$this->_validate_file_type($_FILES['uploaded_doc'], $this->_supported_files)) {
                $this->errors[] = "Invalid File Type for  uploaded File";
            }

            if (!$this->_check_extension($_FILES['uploaded_doc'], $this->_allowed_extensions)) {
                $this->errors[] = "Invalid extension for the uploaded File";
            }

            if (!$this->_check_file_size($_FILES['uploaded_doc'])) {
                $this->errors[] = "File too Large (File should not be more than 500kb)";
            }
        }
    }

    private function _add_uploaded_doc_info_to_db($invoice_number, $file_info_array)
    {
        $task_performer = new Mtii_Utilities_Tasks_Performer;
        return $task_performer->add_signed_doc_as_custom_post($invoice_number, $file_info_array);
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
        if (isset($_POST["mtii_upload_submit"]) AND isset($_POST['form_register_upload_nonce'])
            AND wp_verify_nonce($_POST['form_register_upload_nonce'], 'form-register-upload-nonce')
        ) {
            $current_user = wp_get_current_user();
            $current_user_meta = get_user_meta($current_user->ID);
            $this->_validate_file_before_upload();

            if (!empty($_FILES) && count($this->errors) !== 0) {
                //print_r($this->errors);
                $this->check_and_get_errors();
            } else {
                $temp = explode(".", $_FILES['uploaded_doc']["name"]);
                $extension = end($temp);
                $user = $current_user_meta['first_name'][0] . " " . $current_user_meta['last_name'][0];
                $newname=  'uploaded_document' . "_" . rand(10, 500000000) . "_" . str_replace(" ", "_", $user) . ".".$extension;
                rename($_FILES['uploaded_doc']["tmp_name"], $newname);
                $attachments[] = $newname;
                if (!empty($attachments)) {
                    //delete file on the server
                    $this->_do_cloudinary_config();
                    //echo $this->_invoice_number_against_doc;
                    $invoice_number = $this->_get_invoice_number();
                    $existing_upload = $this->_check_existing_upload($invoice_number);
                    if ($existing_upload!=null) {
                        $existing_upload_public_id = $this->_get_existing_upload_pub_id($existing_upload->ID);
                        \Cloudinary\Uploader::destroy($existing_upload_public_id);
                    }

                    foreach ($attachments as $att) {
                        $files = array();
                        $files['unnamed_local'] = \Cloudinary\Uploader::upload(realpath($att));
                        $upload_doc = $this->_add_uploaded_doc_info_to_db($invoice_number, $files['unnamed_local']);
                        if ($upload_doc=="Document uploaded and added to Database" || $upload_doc=="Document upload successfully updated") {
                            $this->_upload_completed_successfully = true;
                            $this->_adding_upload_to_db_failed = false;
                        } else if ($upload_doc=="There is an Error") {
                            $this->_adding_upload_to_db_failed = true;
                            $this->_upload_completed_successfully = false;
                        }
                        unlink($att);
                    }
                }
            }
        }
    }
}