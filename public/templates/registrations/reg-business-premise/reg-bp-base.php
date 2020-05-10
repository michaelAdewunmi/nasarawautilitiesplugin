<?php
if (!isset($_COOKIE["mtii_payment_invoice"])) :
    include_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/templates/registrations/payment-verification-form.php';
else :
    $task_performer = new Mtii_Utilities_Tasks_Performer;
    $decoded_val = $task_performer->get_saved_invoice_from_cookie();

    $paid_invoice = get_page_by_title($decoded_val["invoice_number"], OBJECT, 'mtii_cbs_invoice');

    if ($task_performer->add_invoice_to_db() === 'There is an Error' ) {
        echo '<p class="err-notification errored-text">Sorry! Your registration cannot proceed!'.
            ' Please contact Admin with your invoice number for clarification and rectification. Thank you!</p>';
    } else if ($task_performer->add_invoice_to_db()) {
        $user = wp_get_current_user();
        $invoice_creator = get_post_meta($paid_invoice->ID, 'invoice_created_by', true);
        if ($invoice_creator==get_current_user_id() || in_array('administrator', $user->roles)) {
            include_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/class-mtii-registration-utilities.php';
            $reg_util = new Mtii_Registration_Utilities();
            $final_reg_stage_attained = $reg_util->check_if_invoice_has_signed_documents($decoded_val["invoice_number"]);
            if ($decoded_val["invoice_category"]=="Business Premise") {
                if (in_array('administrator', $user->roles) && ($final_reg_stage_attained==="No Signed Upload"
                    || $final_reg_stage_attained!="true" || $final_reg_stage_attained!="false") ) {
                    echo '<span id="invoice-number-info">Invoice Number: '.$decoded_val["invoice_number"].
                    '<a class="round-btn-mtii small-btn" href="'.site_url().$_SERVER['REQUEST_URI'].'&reset=1'.
                    '">Change Invoice</a><div class="notification-wrapper"><p class="err-notification errored-text">'.
                    '</span>Oops! It seems this registration is still ungoing and cannot be previewed'.
                        ' by admin! You should check back later. Thank you!</p></div>';
                } else {
                    if ((!isset($_REQUEST['is_preview']))
                        || (isset($_REQUEST['is_preview']) && $_REQUEST['is_preview']==openssl_encrypt("is_preview", "AES-128-ECB", "SECRET")
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
            } else {
                echo "<h1>Oops! Sorry. This seem to be a wrong or invalid invoice for this registration</h1>";
                echo '<a class="round-btn-mtii small-btn" href="'.
                    site_url().$_SERVER['REQUEST_URI'].'&reset=1'.'">Change Invoice</a>';
            }
        } else {
            echo '<div class="notification-wrapper"><p class="err-notification errored-text">'.
            'Sorry You do not have the permission to view this page!'.
            ' You should <a href="'.site_url().'">Return Home</a>. Thank you!</p></div>';
        }
    }
endif;