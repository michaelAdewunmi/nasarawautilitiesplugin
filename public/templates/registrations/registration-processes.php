<?php
use MtiiUtilities\TasksPerformer;

$task_performer = new TasksPerformer;
$invoice_details = $task_performer->get_active_invoice_linked_to_user();
$errors_array = array();
if ($invoice_details) {
    $invoice_number = isset($invoice_details["invoice_number"]) ? $invoice_details["invoice_number"] : null;
    if ($invoice_number && $invoice_number!="" && $task_performer->paid_invoice_checker()==="paid") {
        $invoice_category = $invoice_details["invoice_category"];
        if ($invoice_category==='Cooperative') {
            $invoice_catg = $coop;
        } else if ($invoice_category==='Business Premise') {
            $invoice_catg = $biz_prem;
        } else if ($invoice_category==='NGOs and CBOs') {
            $invoice_catg = $ngo;
        }
        $date_paid = $invoice_details["date_paid"];
        $days_since_last_payment = $task_performer->check_date_difference($date_paid);
        echo '<div class="section-body">';
        if ($days_since_last_payment>365) {
            $task_performer->flag_invoice_as_expired();
            echo '<div class="notification-wrapper" style="max-width: 850px; color: red;">'.
                '<p class="err-notification" style="border-color: red;">'.
                'Oops! Sorry you cannot continue with this invoice as it has expired. '.
                'You should generate a new invoice and make new payment to continue. '.
                'Thank you!</p> &nbsp <a class="round-btn-mtii small-btn" href="'.
                site_url("/user-dashboard?").'do=pay&catg='.$invoice_catg.'" >Create Invoice for Payment</a>'.
                '&nbsp&nbsp<a class="round-btn-mtii small-btn" style="background:#34b38a;;" href="'.
                site_url("/user-dashboard?").'do=reg&reset=1">I have my New Invoice</a>';
            exit;
        } else {
                $add_invoice_to_db = $task_performer->update_active_invoice_b4_login();
            if ($add_invoice_to_db==='There is an Error') {
                echo '<p class="err-notification errored-text">Sorry! Your registration cannot proceed!'.
                    ' Please contact Admin with your invoice number for clarification and rectification. Thank you!</p>';
            } else {
                if (isset($_REQUEST['is-for-upload']) && $_REQUEST['is-for-upload']==1) {
                    echo '<script>window.location.href="'.site_url('/user-dashboard?do=upload').'"</script>';
                } else if ($invoice_category==='Cooperative') {
                    include_once "reg-cooperative/reg-coop-base.php";
                } else if ($invoice_category==='Business Premise') {
                    include_once "reg-business-premise/reg-bp-base.php";
                } else if ($invoice_category==='NGOs and CBOs') {
                    include_once "reg-ngo-and-cbo/reg-ngo-base.php";
                }
            }
        }
        echo '</div>';
    } else {
        $invoice_info = get_page_by_title($_POST["invoice_number"], OBJECT, 'mtii_cbs_invoice');
        if (!$invoice_info || !$invoice_info->ID) {
            $errors_array["general"] = "Sorry! We cannot find the information for this invoice number ".
            "and cannot continue with this registration. If this is a genuine invoice, Please ".
            "contact admin.";
        } else if ($task_performer->paid_invoice_checker()!=="paid") {
            $errors_array["general"] = "Oops! It seems the payment for this invoice has not been done. ".
            "We cannot continue with this registration. If you have however paid for this invoice, Please ".
            "contact admin for rectification. Thank You!";
        }
        include_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/templates/registrations/payment-verification-form.php';
    }
} else {
    if (isset($_POST["user_invoice_payment_nonce"]) && isset($_POST["submit"])
        && (trim($_POST["user_invoice_payment_nonce"])=='' || !$_POST["user_invoice_payment_nonce"])
    ) {
        $errors_array["general"] = "Invoice Number cannot be empty";
    }
    include_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/templates/registrations/payment-verification-form.php';
}
?>