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

use MtiiUtilities\CloudinaryUpload;
use MtiiUtilities\RegistrationUtilities;
use MtiiUtilities\TasksPerformer;

$cloudinary_util = new CloudinaryUpload(false, array('uploaded_doc'));

$reg_util = new RegistrationUtilities;
$invoice_info = $reg_util->get_invoice_info_from_db();
$invoice_sub_catg = $invoice_info->invoice_sub_category;

$task_performer = new TasksPerformer;
$days_since_last_payment = $task_performer->check_date_difference($invoice_info->payment_date);

$the_reg_org=null;
if ($invoice_info->invoice_category==="Cooperative") {
    $the_reg_org = $reg_util->get_coop_main_form_data();
}// } else if ($invoice_info->invoice_category==="NGOs and CBOs") {
//     $the_reg_org = $reg_util->get_ngo_cbo_form_data();
// } else if ($invoice_info->invoice_category==="Business Premise") {
//     $the_reg_org = $reg_util->get_biz_prem_form_data();
// }
if ($days_since_last_payment>366 || !$the_reg_org) {
    echo '<div class="section-body">';
    $title = $days_since_last_payment>366 ? 'Expired Invoice' : 'Invoice not Registered';
    if ($days_since_last_payment>366) {
        $body = 'This Invoice seem to be an Expired Invoice! Sorry your upload cannot continue.';
    } else {
        $body = 'This Invoice is not registered and cannot be linked to any registration that requires document upload! Sorry your upload cannot continue.';
    }
    $body .= '&nbsp&nbsp <a class="round-btn-mtii small-btn" href="'.site_url('/user-dashboard?do=reg').'">Go Home</a>';
    echo $task_performer->output_inline_notification($title, $body, 'is-error');
    echo '</div>';
    ?>
    <?php
} else {
    if ($cloudinary_util->get_if_upload_is_completed()) : ?>
        <div class="section-body">
            <h2 class="section-heading">Upload Successful!</h2>
            <hr class="header-lower-rule" />
            <div class="payment-err">
                <div class="notification-wrapper">
                    <div class="mtii_reg_errors">
                        <h2 style="color: #34b38a;">Document successfully Uploaded. Please wait for approval by admin</h2>
                        <a class="round-btn-mtii" href="<?php echo site_url('/user-dashboard?do=reg');?>">Go Home</a>
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
            <?php if ($this->updating_coop_info=="Not Updated") : ?>
            <div class="payment-err">
                <div class="notification-wrapper">
                    <div class="mtii_reg_errors">
                        <h4 style="color: red;">
                            The document was successfully uploaded but your information could not be updated.
                            You should report admin this error to the Admin
                        </h4>
                    </div>
                    <p class="round-btn-mtii" onClick="window.location.reload()">Go Home</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    <?php else : ?>
    <div class="section-body">
        <h2 class="section-heading">Upload Signed Document</h2>
        <hr class="header-lower-rule" />
        <?php
            if (isset($invoice_info->invoice_number)) : ?>
        <span id="invoice-number-info">
            Invoice Number: <?php echo $invoice_info->invoice_number; ?>
            <a class="round-btn-mtii small-btn" href="<?php echo site_url().$_SERVER['REQUEST_URI'].'&reset=1&is-for-upload=1'; ?>">Change Invoice</a>
        </span>
            <?php
            else :
                echo die('<script>window.location.href="'.site_url("/user-dashboard?do=reg&is-for-upload=1").'"</script>');
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
    <?php
    endif;
}
?>