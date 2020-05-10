<?php
$task_performer = new Mtii_Utilities_Tasks_Performer;
if (isset($_POST["user_invoice_payment_nonce"]) && isset($_POST["submit"])) {
    $errors_array = array();
    if (isset($_POST["invoice_number"])) {
        $invoice_number = $_POST["invoice_number"];
        if (trim($invoice_number)=='' || !$invoice_number) {
            $errors_array["general"] = "Invoice Number cannot be empty";
        } else {
            $paid_invoice = get_page_by_title($invoice_number, OBJECT, 'mtii_cbs_invoice');
            $invoice_amount_due = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'amount_due', true) : 999999;
            $invoice_fully_paid = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'invoice_fully_paid', true) : false;
            $req_reference = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'request_reference', true) : null;
            $the_amount_paid = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'amount_paid', true): 0;
            $invoice_amount = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'invoice_amount', true) : 999999;
            if ($invoice_fully_paid=="true" && $invoice_amount_due<1 && $the_amount_paid == $invoice_amount) {
                echo '<script>window.location.href="'.
                    site_url('/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D')
                .'"</script>';
            } else {
                $errors_array["general"] = "Sorry We cannot continue with this registration because there seem to ".
                    "be a problem with this Invoice. Please see admin for clarification. Thank You!";
            }
        }
    }
}

