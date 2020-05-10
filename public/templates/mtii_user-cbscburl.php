<?
/**
 * Summary.
 *
 * Description.
 *
 * @since Version 3 digits
 */
include_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-utilities-task-performer.php';
// $our_sec = "SECRETFORANOTHERUSE";
// $me = "1000133637"."JC-0000621000133599".sprintf("%.2f", 10000)."LaCckIkmtP";
// echo base64_encode(hash_hmac('sha256', $me, $our_sec, true)) . "<br /><br />";

if($_SERVER['REQUEST_METHOD'] == "POST") {

    $data_from_cbs = file_get_contents("php://input");
    $data_array = json_decode($data_from_cbs, true);

    // $data_array = array(
    //     "InvoiceNumber"         => "1000133637",
    //     "PaymentRef"            => "JC-0000621000133599",
    //     "AmountPaid"            => 10000,
    //     "RequestReference"      => "LaCckIkmtP",
    //     "Mac"                   => "fSSIJ+ZDBqwjF9stmXg9uQ6uwIRVvK/+zaWjgwtKQ5o=" //mac for 337 "Ftww3SDJMUhXb/CjoAKt/jaXnmG6Zo8MDYwBbBc8U3U="
    // );

    //$data_array = $_POST;

    $invoice_number = $data_array["InvoiceNumber"];
    $payment_ref = $data_array["PaymentRef"];
    $amount_paid = $data_array["AmountPaid"];
    $request_ref = $data_array["RequestReference"];


    $string_to_hash = $invoice_number.$payment_ref.sprintf("%.2f", $amount_paid).$request_ref;
    $client_id = get_option('live_or_staging')=='mtii_live' ? "SECRETFORANOTHERUSE"
                : "SECRETFORANOTHERUSE=";

// $client_secret = get_option('live_or_staging')=='mtii_live' ? "SECRETFORANOTHERUSE"
//                 : "SECRETFORANOTHERUSE";
    $our_sec = "SECRETFORANOTHERUSE"; //"SECRETFORANOTHERUSE";
    $mtii_cbs_mac = base64_encode(hash_hmac('sha256', $string_to_hash, $our_sec, true));

    $request_header = getallheaders();
    $send_header = json_encode($request_header);
    $data_new = json_encode($data_array);
    $msg = $send_header."\n\r\r\n\n".$data_new."\n\r\r\n\n".json_encode($_REQUEST)."\n\r\r\n\n".json_encode($_POST);


    if ($mtii_cbs_mac==$data_array["Mac"]) {
        wp_mail('devignersplacefornassarawa@gmail.com', 'New Notification', $msg);

        $task_performer = new Mtii_Utilities_Tasks_Performer();
        $updated_invoice_response = $task_performer->update_invoice_as_custom_post($data_array);
        $task_performer->send_payment_emails($data_array, $updated_invoice_response);
        //wp_mail('devignersplacefornassarawa.com', 'New Notification', $msg);
        header('HTTP/1.1 200 OK');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('status' => 'success', 'message' => 'Successfully Done!', 'code' => 202005));
        die();
    } else {
        wp_mail('devignersplacefornassarawa@gmail.com', 'New Error Notificcation', $msg);
        $return = 'Sorry! There was a problem with the data submitted';
        header('HTTP/1.1 403 REQUEST ERROR');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'Request Verification Error!', 'code' => 201907, $return)));
    }
} else {
    $send_this = "There was an error in the request from CBS"."\n\r\n\r".json_encode($_REQUEST)."\n\r\n\r";
    wp_mail('devignersplacefornassarawa@gmail.com', 'New Error Notificcation', $send_this);
    $return = 'Sorry! This is a wrong request';
    header('HTTP/1.1 403 REQUEST ERROR');
    header('Content-Type: application/json; charset=UTF-8');
    die(json_encode(array('message' => 'User Verification Error!', 'code' => 201907)));
}
