<?php
use MtiiUtilities\RegistrationUtilities;
use MtiiUtilities\TasksPerformer;
$task_performer = new TasksPerformer;
$decoded_val = $task_performer->get_active_invoice_linked_to_user();

$paid_invoice = get_page_by_title($decoded_val["invoice_number"], OBJECT, 'mtii_cbs_invoice');
$user = wp_get_current_user();
$invoice_creator = get_post_meta($paid_invoice->ID, 'invoice_created_by', true);
if ($invoice_creator==get_current_user_id() || in_array('administrator', $user->roles)) {
    if ($query_param_catg==null || $query_param_catg!=$biz_prem) {
        echo die('<script>window.location.href="'.site_url("/user-dashboard?do=reg&catg=$biz_prem").'"</script>');
    }
    if ($decoded_val["invoice_sub_category"]=="fresh" || $decoded_val["invoice_sub_category"]==="used-bp-renewal") {
        $reg_util = new RegistrationUtilities;
        $final_reg_stage_attained = $reg_util->check_if_application_is_completed('business_premise', $decoded_val["invoice_number"]);
        $task_performer->show_invoice_number_and_reset_btn('Registration (Business Premise)');
        if (in_array('administrator', $user->roles) && ($final_reg_stage_attained!=true || !$final_reg_stage_attained) ) {
            echo '<div class="notification-wrapper"><p class="err-notification errored-text">'.
                'Oops! It seems this registration is still ungoing and cannot be previewed'.
                ' by admin! You should check back later. Thank you!</p></div>';
        } else {
            if ((!isset($_REQUEST['is_preview']))
                || (isset($_REQUEST['is_preview']) && $_REQUEST['is_preview']==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34")
                && (isset($_REQUEST['for_biz_prem']) && $_REQUEST['for_biz_prem']==1))
            ) {
                include_once "reg-biz-prem-main.php";
            } else {
                echo die(
                    '<script>window.location.href="'.
                        site_url("/user-dashboard?do=reg&catg=".$biz_prem).'"</script>'
                );
            }
        }
    } else if ($decoded_val["invoice_sub_category"]=="renewal") {
        $task_performer->show_invoice_number_and_reset_btn('Business Premise Registration renewal');
        //echo '<h1 style="font-size: 8em; line-height: 0.7;">Thank You!!!</h1>';
        include_once WP_CONTENT_DIR.
        '/plugins/mtii-utilities/public/templates/registrations/reg-business-premise/renewal/bp-renewal.php';
    }
} else {
    echo '<div class="notification-wrapper"><p class="err-notification errored-text">'.
    'Sorry You do not have the permission to view this page!'.
    ' You should <a href="'.site_url().'">Return Home</a>. Thank you!</p></div>';
}