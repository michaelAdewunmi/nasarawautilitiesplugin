<?php
use MtiiUtilities\RegistrationUtilities;

$decoded_val = $task_performer->get_active_invoice_linked_to_user();
$paid_invoice = get_page_by_title($decoded_val["invoice_number"], OBJECT, 'mtii_cbs_invoice');
$user = wp_get_current_user();
$invoice_creator = get_post_meta($paid_invoice->ID, 'invoice_created_by', true);
if ($invoice_creator==get_current_user_id() || in_array('administrator', $user->roles)) {
    if ($query_param_catg==null || $query_param_catg!=$coop) {
        echo die('<script>window.location.href="'.site_url("/user-dashboard?do=reg&catg=$coop").'"</script>');
    }
    $reg_util = new RegistrationUtilities();
    $final_reg_stage_attained = $reg_util->check_if_invoice_has_signed_documents($decoded_val["invoice_number"]);
    if ($decoded_val["invoice_sub_category"]=="fresh" || $decoded_val["invoice_sub_category"]==="used-coop-recertification") {
        $task_performer->show_invoice_number_and_reset_btn('Cooperative Registration');

        if (in_array('administrator', $user->roles) && ($final_reg_stage_attained==="No Signed Upload"
            || $final_reg_stage_attained!="true" || $final_reg_stage_attained!="false")
        ) {
            echo '<div class="notification-wrapper"><p class="err-notification errored-text">'.
                'Oops! It seems this registration is still ongoing and cannot be previewed'.
                ' by admin! You should check back later. Thank you!</p></div>';
        } else {
            if ((!isset($_REQUEST['is_preview']))
                || (isset($_REQUEST['is_preview']) && $_REQUEST['is_preview']==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34")
                && ((isset($_REQUEST['for_main']) && $_REQUEST['for_main']==1)
                || (isset($_REQUEST['for_signatories_template']) && $_REQUEST['for_signatories_template']==1)))
            ) {
                global $mtii_db_coop_main_form;
                $main_reg_form = $mtii_db_coop_main_form->get_row_by_invoice($decoded_val["invoice_number"], $decoded_val["request_reference"], true);
                if (!$main_reg_form || ($main_reg_form && ((isset($_REQUEST['for_main']) && $_REQUEST['for_main']==1)
                    || ($main_reg_form->admin_approved=='' && $main_reg_form->lga_of_proposed_society=='')))
                ) {
                    include_once "reg-form-main.php";
                } else {
                    include_once "reg-signatories-template.php";
                }
            } else {
                echo die('<script>window.location.href="'.site_url("/user-dashboard?do=reg&catg=$coop").'"</script>');
            }
        }
    } else if ($decoded_val["invoice_sub_category"]=="replacement") {
        $task_performer->show_invoice_number_and_reset_btn('Certificate Replacement (Cooperative)');
        include_once WP_CONTENT_DIR.
            '/plugins/mtii-utilities/public/templates/certificate-replacement/cert-replacement.php';
    } else if ($decoded_val["invoice_sub_category"]=="legal-search") {
        $task_performer->show_invoice_number_and_reset_btn('Legal Search (Cooperative)');
        include_once WP_CONTENT_DIR.
        '/plugins/mtii-utilities/public/templates/legal-search/legal-search.php';
    } else if ($decoded_val["invoice_sub_category"]=="re-certification") {
        $task_performer->show_invoice_number_and_reset_btn('Recertification (Cooperative)');
        include_once "re-certification/reg-recertification.php";
    } else if ($decoded_val["invoice_sub_category"]=="used-replacement" || $decoded_val["invoice_sub_category"]=="used-legal-search") {
        $task_performer->show_invoice_number_and_reset_btn('Used Invoice (Cooperative)');
        echo '<div class="notification-wrapper" style="max-width: 850px;"><p class="err-notification" '.
        'style="border-color: #95ffdd;">This is invoice is already used</p></div>';
    } else {
        $task_performer->show_invoice_number_and_reset_btn("Error");
        echo '<div class="notification-wrapper" style="max-width: 850px;"><p class="err-notification" style="border-color: #95ffdd;">'.
        'There seem to be a problem. Please contact Administrator</p></div>';
    }
} else {
    echo '<div class="notification-wrapper"><p class="err-notification errored-text">'.
    'Sorry You do not have the permission to view this page!'.
    ' You should <a href="'.site_url().'">Return Home</a>. Thank you!</p></div>';
}
?>