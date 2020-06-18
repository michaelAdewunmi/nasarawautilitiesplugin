<?
/**
 * Summary.
 *
 * Description.
 *
 * @since Version 3 digits
 */
use MtiiUtilities\TasksPerformer;

if($_SERVER['REQUEST_METHOD'] == "POST") {

    $data_from_cbs = file_get_contents("php://input");
    $data_array = json_decode($data_from_cbs, true);

    $invoice_number = $data_array["InvoiceNumber"];
    $payment_ref = $data_array["PaymentRef"];
    $amount_paid = $data_array["AmountPaid"];
    $request_ref = $data_array["RequestReference"];

    $task_performer = new TasksPerformer;

    $client_id = $task_performer->get_mtii_client_id();
    $client_secret = $task_performer->get_mtii_client_secret();

    $string_to_hash = $invoice_number.$payment_ref.sprintf("%.2f", $amount_paid).$request_ref;

    $mtii_cbs_mac = base64_encode(hash_hmac('sha256', $string_to_hash, $client_secret, true));

    $request_header = getallheaders();
    $send_header = json_encode($request_header);
    $data_new = json_encode($data_array);
    $msg = $send_header."\n\r\r\n\n".$data_new."\n\r\r\n\n".json_encode($_REQUEST)."\n\r\r\n\n".json_encode($_POST);


    if ($mtii_cbs_mac==$data_array["Mac"]) {
        wp_mail('devignersplacefornassarawa@gmail.com', 'New Notification', $msg);

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
