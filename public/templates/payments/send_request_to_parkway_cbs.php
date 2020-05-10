<?php
/**
 * This is the file that handles the sending of the request to the cbs
 * Please Note: The $user object and Many other Variables used here were already declared and set
 * in the parent files that included this file.
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */

$payer_id = get_the_author_meta('cbs_payer_id', $user->ID, true);
$payer_id = !$payer_id || $payer_id=='' ? null : $payer_id;
$amount_to_pay = $data_to_generate->amount_to_pay;
$invoice_category = $data_to_generate->get_category_nice_name();
$invoice_sub_category = $data_to_generate->sub_category;


$request_payload = array(
    "RevenueHeadId"             => $data_to_generate->revenue_head_id,
    "TaxEntityInvoice"          => array (
        "TaxEntity" => array(
            "Recipient"                         => $_POST["payee_names"],
            "Email"                             => $_POST["payee_email"],
            "Address"                           => $_POST["payee_address"],
            "PhoneNumber"                       => $_POST["payee_phone"],
            "TaxPayerIdentificationNumber"      => $_POST["payee_tax_number"],
            "RCNumber"                          => null,
            "PayerId"                           => $payer_id,
        ),
        "Amount"                    => number_format((float)$amount_to_pay, 2, '.', ''),
        "InvoiceDescription"        => $data_to_generate->invoice_description,
        "AdditionalDetails"         => array(array()),
        "CategoryId"                => 1
    ),
    "ExternalRefNumber"             => null,
    "RequestReference"              => $ref_to_use,
    "CallBackURL"                   => site_url()."/cbscburl"
);

$amount_to_hash = sprintf("%.2f", $amount_to_pay);
$string_to_hash = $data_to_generate->revenue_head_id.$amount_to_hash.site_url()."/cbscburl".$data_to_generate->client_id;

$mtii_cbs_signature = base64_encode(hash_hmac('sha256', $string_to_hash, $data_to_generate->client_secret, true));

// make request to CBS endpoint using unirest.
$headers = array(
    'Content-Type'  => 'application/json',
    'Signature'     => $mtii_cbs_signature,
    'ClientID'      => $data_to_generate->client_id
);
$headers_to_send = new ArrayObject($headers);
$body = Unirest\Request\Body::json($request_payload);

//please make sure to change this to production url when you go live
$url = get_option('live_or_staging')=='mtii_live' ? "https://nasarawaigr.com/api/v1/invoice/create"
        : "https://uat.nasarawaigr.com/api/v1/invoice/create";


// Make `POST` request and handle response with unirest
$response = Unirest\Request::post($url, $headers_to_send, $body);

if (isset($response->code) && isset($response->body) && isset($response->body->Error)
    && isset($response->body->ResponseObject) && isset($response->body->ResponseObject->PayerId)
    && isset($response->body->ResponseObject->RequestReference)
    && isset($response->body->ResponseObject->InvoiceNumber)
    && $response->code==200 && $response->body->Error==false && $response->body->ErrorCode==null
) {
    //$user_id = get_current_user_id();
    $payer_id_from_parkway = $response->body->ResponseObject->PayerId;
    $user_cbs_payer_id = get_user_meta($user->ID, 'cbs_payer_id', true);
    if ($user_cbs_payer_id=='' || $user_cbs_payer_id!=$payer_id_from_parkway) {
        update_user_meta($user->ID, 'cbs_payer_id', $payer_id_from_parkway);
    }

    $user_invoice_details = $response->body->ResponseObject;
    $ref_used_at_parkway = $user_invoice_details->RequestReference;

    $info_to_json = json_encode($user_invoice_details);
    wp_mail('devignersplacefornassarawa@gmail.com', 'New Invoice creation Notification Success', $info_to_json);

    $task_performer = new Mtii_Utilities_Tasks_Performer();

    $payer_message = 'Hello '.$user_invoice_details->Recipient.',<br /><br />'.
        'You Have successfully generated an Invoice for the '.$user_invoice_details->Description.
        '. Your Invoice Number is <strong>'.$user_invoice_details->InvoiceNumber.'.</strong> <br /><br />'.
        'You can <a href="'.$user_invoice_details->InvoicePreviewUrl.'">Click Here</a> to print your '.
        'invoice or simply <a href="'.$user_invoice_details->PaymentURL.'">Click This link</a> to make '.
        'payment. Thank you.';

    $mail_content = $task_performer->create_email_from_template('Invoice creation Successful', $payer_message);

    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail($user_invoice_details->Email, 'Invoice creation Notification Success', $mail_content, $headers);
    $output_errors = new Mtii_Utilities_Output_Errors();
    $no_redirect = false;
    if (count($used_reference)>0) {
        if (in_array($ref_used_at_parkway, $existing_reference)) {
            if ($used_reference[$ref_used_at_parkway] == "used") {
                $used_reference[$ref_used_at_parkway] = "used but duplicated";
                update_option('mtii_request_references', $used_reference);
                $output_errors->duplicate_request_reference_error($user_invoice_details->InvoiceNumber);
                $no_redirect = true;
            } else if ($used_reference[$ref_used_at_parkway] == "not yet used") {
                $used_reference[$ref_used_at_parkway] = "used";
                update_option('mtii_request_references', $used_reference);
            } else {
                $strange_references = get_option('mtii_strange_request_references');
                $strange_references = $strange_references =='' ? array() : $strange_references;
                $strange_references[] = $ref_used_at_parkway;
                update_option('mtii_strange_request_references');
                $output_errors->strange_request_reference_error($user_invoice_details->InvoiceNumber);
                $no_redirect = true;
            }
        } else {
            $unknown_references = get_option('mtii_unknown_request_references');
            $unknown_references = $unknown_references =='' ? array() : $unknown_references;
            $unknown_references[] = $ref_used_at_parkway;
            update_option('mtii_unknown_request_references', array($ref_used_at_parkway));
            $output_errors->unknown_request_reference_error($user_invoice_details->InvoiceNumber);
            $no_redirect = true;
        }
    } else {
        $server_errorred_ref = get_option('mtii_server_error_request_references');
        $server_errorred_ref = $server_errorred_ref =='' ? array() : $server_errorred_ref;
        $server_errorred_ref[] = $ref_used_at_parkway;
        update_option('mtii_server_error_request_references', array($ref_used_at_parkway));
        $output_errors->server_reference_error($user_invoice_details->InvoiceNumber);
        $no_redirect = true;
    }
    $user_invoice_details->invoice_category = $invoice_category;
    $user_invoice_details->invoice_sub_category = $invoice_sub_category;

    $added_invoice = $task_performer->add_invoice_as_custom_post($user_invoice_details);
    if ($added_invoice=='There is an Error') {
        $unsaved_invoice_errored = get_option('mtii_unsaved_invoices');
        $unsaved_invoice_errored = $unsaved_invoice_errored =='' ? array() : $unsaved_invoice_errored;
        $unsaved_invoice_errored[] = $user_invoice_details->InvoiceNumber;
        update_option('mtii_unsaved_invoices', array($ref_used_at_parkway));
        $output_errors->invoice_addition_error($user_invoice_details->InvoiceNumber);
        $no_redirect = true;
    } else if ($added_invoice=="Duplicate Invoice couldn't save") {
        $output_errors->invoice_duplication_error($user_invoice_details->InvoiceNumber);
        $no_redirect = true;
    } else if ($added_invoice=="Invoice is already Used") {
        $output_errors->invoice_used_error($user_invoice_details->InvoiceNumber);
        $no_redirect = true;
    } else if ($added_invoice=='Invoice added to Database') {
        if ($no_redirect != true) {
            unset($_POST);
            $_POST = array();
            $invoice_number = urlencode(
                openssl_encrypt($user_invoice_details->InvoiceNumber, "AES-128-ECB", "0jQkL&5S")
            );
            $url_with_query = '/user-dashboard?do=succ&succ=gotopay&invnum='.$invoice_number;
            echo die(
                '<script>window.location.href="'.site_url($url_with_query).'"</script>'
            );
        } else {
            echo "<h1>There were some Errors</h1>";
        }

    }
} else {
    $info_to_json = json_encode($response);
    wp_mail('devignersplacefornassarawa@gmail.com', 'New Invoice creation Notification Error', $info_to_json);
    echo "<h1>There was a problem. Please Contact Admin</h1><pre>";
}