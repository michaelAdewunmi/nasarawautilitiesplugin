<?php
if(!is_user_logged_in()) {
    wp_redirect(esc_url(site_url()));
    exit;
}

get_header();

use Unirest\Request\Body;
use MtiiUtilities\TasksPerformer;
Unirest\Request::verifyPeer(false);

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
$existing_reference = get_option('requestreference');
$ref_to_use = '';
$new_request_ref = generateRandomString();

while (in_array($new_request_ref, $existing_reference)) {
    $new_request_ref = generateRandomString();
}

$existing_reference[] = $new_request_ref;
update_option('requestreference', $existing_reference);

$revenue_head_id = 112;
$amount_to_pay = 100000;

$data = array(
    "RevenueHeadId"             => 112,
    "TaxEntityInvoice"          => array (
        "TaxEntity" => array(
            "Recipient"                         => "Michael Adewunmi",
            "Email"                             => "devignersplacefornassarawa@gmail.com",
            "Address"                           => "20, Olaoluwa close, Ibeju Lekki, Lagos",
            "PhoneNumber"                       => "07056438222",
            "TaxPayerIdentificationNumber"      => "7777711",
            "RCNumber"                          => null,
            "PayerId"                           => null,
        ),
        "Amount"                    => number_format((float)$amount_to_pay, 2, '.', ''),
        "InvoiceDescription"        => $invoice_description,
        "AdditionalDetails"         => array(array()),
        "CategoryId"                => 1
    ),
    "ExternalRefNumber"             => null,
    "RequestReference"              => $ref_to_use,
    "CallBackURL"                   => "https://mtii.josbiz.com/cbscburl"
);

$data_to_send = new ArrayObject($data);

$task_performer = new TasksPerformer;

$client_id = $task_performer->get_mtii_client_id();
$client_secret = $task_performer->get_mtii_client_secret();
$amount_to_hash = sprintf("%.2f", $amount_to_pay);
$string_to_hash = $revenue_head_id.$amount_to_hash."https://mtii.josbiz.com/cbscburl".$client_id;

$mtii_cbs_signature = base64_encode(hash_hmac('sha256', $string_to_hash, $client_secret, true));

// make request to endpoint using unirest.
$headers = array(
    'Content-Type'  => 'application/json', //'application/x-www-form-urlencoded',
    'Signature'     => $mtii_cbs_signature,
    'ClientID'      => $client_id
);
$headers_to_send = new ArrayObject($headers);
$body = Unirest\Request\Body::json($data_to_send);


//please make sure to change this to production url when you go live
$url = $task_performer->invoice_url;

// Make `POST` request and handle response with unirest
$response = Unirest\Request::post($url, $headers_to_send, $body);

if ($response->body->Error==false && $responsee->body->ErrorCode==null) {
    $user_id = get_current_user_id();
    $user_cbs_payer_id = get_user_meta($user_id, 'user_cbs_payer_id', true);
    if ($user_cbs_payer_id && $user_cbs_payer_id!=$response->body->ResponseObject->PayerId) {
        update_user_meta($user_id, 'user_cbs_payer_id');
    }
    $user_invoice_details = $response->body->ResponseObject;
    $info_to_json = json_encode($user_invoice_details);
    wp_mail('devignersplacefornassarawa@gmail.com', 'New Invoice creation Notification Success', $info_to_json);
    echo "<h1>YAAAAAAAAAAAAAAAAAAYYYYYYY!!!! We DID IT!</h1>";
} else {
    $info_to_json = json_encode($response);
    wp_mail('devignersplacefornassarawa@gmail.com', 'New Invoice creation Notification Error', $info_to_json);
    echo "<h1>There was a problem</h1>";
}