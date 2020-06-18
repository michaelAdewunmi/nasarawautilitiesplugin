<?php
function is_mtii_ajax() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

if(!is_user_logged_in()) {
    header('HTTP/1.1 500 nonce error');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array('status' => 'error','message' => 'Login Error!', 'code' => 201908)));
}

if (wp_verify_nonce($_POST['ajax_nonce'], 'ajax-payment-verify-nonce')===false) {
    header('HTTP/1.1 500 nonce error');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array('status' => 'error', 'message' => 'NONCE Error!', 'code' => 201908)));
}

if(!is_mtii_ajax()) {
    header('HTTP/1.1 500 Not Ajax');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array('status' => 'error', 'message' => 'Not Ajax!', 'code' => 201908)));
}


if ($_POST['action']==='verify_inv_payment_for_reg') {
    if (isset($_POST["org_source"])) {
        $org_source = openssl_decrypt(urldecode($_POST["org_source"]), "AES-128-ECB", "X340&2&230rTHJ34");
        $invoice_number = $org_source && $org_source!='' ? $org_source : null;
    } else {
        $org_source = "me";
        $invoice_number = null;
    }
    $the_invoice = get_page_by_title($invoice_number, OBJECT, 'mtii_cbs_invoice');
    $invoice_amount_due = $the_invoice!="" ? get_post_meta($the_invoice->ID, 'amount_due', true) : 999999;
    $invoice_fully_paid = $the_invoice!="" ? get_post_meta($the_invoice->ID, 'invoice_fully_paid', true) : false;
    $the_amount_paid = $the_invoice!="" ? get_post_meta($the_invoice->ID, 'amount_paid', true): 0;
    $invoice_amount = $the_invoice!="" ? get_post_meta($the_invoice->ID, 'invoice_amount', true) : 999999;
    $result = array();

    if ($invoice_fully_paid=="true" && $invoice_amount_due<1 && $the_amount_paid == $invoice_amount) {
        $result["status"] = "success";
        $result["info"] = "Please wait while we redirect you to the registration page";
        $result["for_payment_redirect"] = "true";
        $result["org_source"] = $_POST["org_source"];
    } else {
        $result["status"] = "error";
        $result["info"] = "Oops! It seems you have not paid for this Invoice yet.";
        $result["org_source"] = $_POST["org_source"];
        $result["new_nonce"] = wp_create_nonce('ajax-payment-verify-nonce');
        $result["inv"] = $invoice_number;
        $result["invbdjf"] = $org_source;
    }
    header('HTTP/1.1 200 OK');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($result);
} else {
    header('HTTP/1.1 200 OK');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('message' => 'Job Successfully Posted!', 'code' => 202005, 'magicNumber' => 23));
}
?>