<?php
use MtiiUtilities\RegistrationUtilities;
use MtiiUtilities\TasksPerformer;

$task_performer = new TasksPerformer;
$decoded_val = $task_performer->get_active_invoice_linked_to_user();

$paid_invoice = get_page_by_title($decoded_val["invoice_number"], OBJECT, 'mtii_cbs_invoice');
$user = wp_get_current_user();
$invoice_creator = get_post_meta($paid_invoice->ID, 'invoice_created_by', true);
if ($invoice_creator==get_current_user_id() || in_array('administrator', $user->roles)) {
    if ($query_param_catg==null || $query_param_catg!=$ngo) {
        echo die('<script>window.location.href="'.site_url("/user-dashboard?do=reg&catg=$ngo").'"</script>');
    }
    $reg_util = new RegistrationUtilities;
    $final_reg_stage_attained = $reg_util->check_if_application_is_completed('ngo_and_cbo', $decoded_val["invoice_number"]);
    if ($decoded_val["invoice_sub_category"]=="fresh" || $decoded_val["invoice_sub_category"]==="used-ngo-recertification") {
        $task_performer->show_invoice_number_and_reset_btn('NGO/CBO Registration');
        if (in_array('administrator', $user->roles) && ($final_reg_stage_attained!=true || !$final_reg_stage_attained) ) {
            echo '<div class="notification-wrapper"><p class="err-notification errored-text">'.
                'Oops! It seems this registration is still ongoing and cannot be previewed'.
                ' by admin! You should check back later. Thank you!</p></div>';
        } else {
            if ((!isset($_REQUEST['is_preview']))
                || (isset($_REQUEST['is_preview']) && $_REQUEST['is_preview']==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34")
                && (isset($_REQUEST['for_ngo']) && $_REQUEST['for_ngo']==1))
            ) {
                include_once "reg-ngo-main.php";
            } else {
                echo die(
                    '<script>window.location.href="'.
                        site_url("/user-dashboard?do=reg&catg=".$ngo).'"</script>'
                );
            }
        }
    } else if ($decoded_val["invoice_sub_category"]=="replacement") {
        $task_performer->show_invoice_number_and_reset_btn('Certificate Replacement (NGO/CBO)');
        include_once WP_CONTENT_DIR.
            '/plugins/mtii-utilities/public/templates/certificate-replacement/cert-replacement.php';
    } else if ($decoded_val["invoice_sub_category"]=="legal-search") {
        $task_performer->show_invoice_number_and_reset_btn('Legal Search (NGO/CBO)');
        include_once WP_CONTENT_DIR.
        '/plugins/mtii-utilities/public/templates/legal-search/legal-search.php';
    } else if ($decoded_val["invoice_sub_category"]=="re-certification") {
        $task_performer->show_invoice_number_and_reset_btn('Recertification (NGO/CBO)');
        include_once "re-certification/reg-recertification.php";
    } else {
        echo '<div class="notification-wrapper" style="max-width: 850px;"><p class="err-notification" style="border-color: #95ffdd;">'.
        'There seem to be a problem. Please contact Administrator</p></div>';
    }
} else {
    echo '<div class="notification-wrapper"><p class="err-notification errored-text">'.
    'Sorry You do not have the permission to view this page!'.
    ' You should <a href="'.site_url().'">Return Home</a>. Thank you!</p></div>';
}