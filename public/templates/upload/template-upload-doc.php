<?php
/**
 * This file is the template for the file upload page
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */

require_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/class-mtii-utilities-cloudinary-upload.php';
include_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/class-mtii-registration-utilities.php';


$cloudinary_util = new Mtii_Utilities_Cloudinary_Upload;

$reg_util = new Mtii_Registration_Utilities;
$invoice_info = $reg_util->get_invoice_info_from_db();

/**
 * Output an image in HTML along with provided caption and public_id
 *
 * @param        $img
 * @param array  $options
 * @param string $caption
 */
if ($cloudinary_util->get_if_upload_is_completed()) : ?>
    <div class="section-body">
        <h2 class="section-heading">Upload Successful!</h2>
        <hr class="header-lower-rule" />
        <div class="payment-err">
            <div class="notification-wrapper">
                <div class="mtii_reg_errors">
                    <h2 style="color: #34b38a;">Document successfully Uploaded. Please wait for approval by admin</h2>
                    <a class="round-btn-mtii" href="<?php echo site_url('/user-dashboard');?>">Go Home</a>
                </div>
            </div>
        </div>
    </div>
<?php elseif($cloudinary_util->get_upload_failed_status()) : ?>
    <div class="section-body">
        <h2 class="section-heading errored-text">Error!</h2>
        <hr class="header-lower-rule errored-bg" />
        <div class="payment-err">
            <div class="notification-wrapper">
                <div class="mtii_reg_errors">
                    <h4 style="color: red;">
                        There was an Error while saving upload Information. You can retry or contact admin
                    </h4>
                </div>
                <p class="round-btn-mtii" onClick="window.location.reload()">Go Home</p>
            </div>
        </div>
    </div>
<?php else : ?>
<div class="section-body">
    <h2 class="section-heading">Upload Signed Document</h2>
    <hr class="header-lower-rule" />
    <?php
        if (isset($invoice_info->invoice_number)) : ?>
    <span id="invoice-number-info">
        Invoice Number: <?php echo $invoice_info->invoice_number; ?>
        <a class="round-btn-mtii small-btn" href="<?php echo site_url().$_SERVER['REQUEST_URI'].'&reset=1'; ?>">Change Invoice</a>
    </span>
        <?php
        else :
            echo die(
                '<script>window.location.href="'.
                site_url("/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D")
                .'"</script>'
            );
        endif;
        echo $cloudinary_util->check_and_get_errors();
        $cloudinary_util->get_existing_doc_and_show_thumbnail();
    ?>
    <p class="upload-prompt upload-instruction">
        <?php
            esc_html_e('Upload the signed document below and click the Submit button', 'mtii-utilities');
        ?>
    </p>
    <form name="upload_docs" action="" method="POST" ENCTYPE="multipart/form-data">
        <div class ="files-wrapper">
            <div class="input-and-label">
                <div class="upload-errs"></div>
                <label class="upload-label" for="uploaded_doc">Select File <em style="color: #5d47e6; font-weight: 700;">
                    Please Note: Uploaded File Must not be greater than 500kb</em></label>
                <input  id="doc-upload" class="files-input"  type="file" name="uploaded_doc" accept=".jpg,.png,.pdf" />
            </div>
            <input
                type="hidden" name="form_register_upload_nonce"
                value="<?php echo wp_create_nonce('form-register-upload-nonce') ?>"
            />
            <input class="round-btn-mtii"  name="mtii_upload_submit" type="submit" value="Upload Document" />
        </div>
    </form>
</div>
<?php endif; ?>