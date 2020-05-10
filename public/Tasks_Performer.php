<?php
/**
 * This file basically performs any mundane task required
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
namespace MtiiUtilities;

class Tasks_Performer
{

    private $_expire_cookie = false;

    public function get_saved_invoice_from_cookie()
    {
        $inv = $_COOKIE["mtii_payment_invoice"];
        $value = openssl_decrypt($inv, "AES-128-ECB", "secretecode");
        $decoded_val = json_decode($value, true);
        return $decoded_val;
    }

    public function add_invoice_to_db()
    {
        $decoded_val = $this->get_saved_invoice_from_cookie();
        $paid_invoice = get_page_by_title($decoded_val["invoice_number"], OBJECT, 'mtii_cbs_invoice');
        $payer_email = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'payer_email', true) : "";
        $invoice_expiry_timestamp = strtotime($decoded_val["date_paid"])+60*60*24*365;
        $invoice_expiry_date = date("Y-m-d\TH:i:s\Z", $invoice_expiry_timestamp);

        $args = array (
            "invoice_number"            => $decoded_val["invoice_number"],
            "request_reference"         => $decoded_val["request_reference"],
            "invoice_category"          => $decoded_val["invoice_category"],
            "invoice_sub_category"      => $decoded_val["invoice_sub_category"],
            "invoice_payer_email"       => $payer_email,
            "payment_date"              => $decoded_val["date_paid"],
            "invoice_expires"           => $invoice_expiry_date
        );

        global $wpdb;
        global $mtii_db_invoice;

        $wpdb->show_errors = false;

        $repl = $mtii_db_invoice->get_row_by_invoice($decoded_val["invoice_number"], $decoded_val["request_reference"]);
        if ($repl && $repl!=null && $repl!="") {
            $args["invoice_id"] = $repl->invoice_id;
            $inserted_id = $mtii_db_invoice->update($repl->invoice_id, $args, 'invoice_id');
        } else {
            $inserted_id = $mtii_db_invoice->insert($args);
        }

        if ($wpdb->last_error != '') {
            return 'There is an Error';
        } else if ($inserted_id) {
            return $inserted_id;
        }
    }

    /**
     * Set a Cookie or delete a set cookie if some conditins are met
     *
     * @return void
     */
    public function set_a_cookie()
    {
        if ($this->_expire_cookie || (isset($_REQUEST["reset"]) && $_REQUEST["reset"]==1)) {
            setcookie('mtii_payment_invoice', '', time() - ( 15 * 60 ), COOKIEPATH, COOKIE_DOMAIN);
            echo die(
                '<script>window.location.href="'.
                    site_url("/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D").'"</script>'
            );
            // wp_redirect(site_url("/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D"));

        } else {
            if (isset($_POST["invoice_number"])) {
                $invoice_number = $_POST["invoice_number"];
                if (trim($invoice_number)!='' && $invoice_number) {
                    $paid_invoice = get_page_by_title($invoice_number, OBJECT, 'mtii_cbs_invoice');
                    $invoice_amount_due = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'amount_due', true) : 999999;
                    $invoice_fully_paid = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'invoice_fully_paid', true) : false;
                    $req_reference = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'request_reference', true) : null;
                    $the_amount_paid = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'amount_paid', true): 0;
                    $invoice_amount = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'invoice_amount', true) : 999999;
                    $invoice_category = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'invoice_category', true) : null;
                    $invoice_sub_category = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'invoice_sub_category', true) : null;
                    $date_paid = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'date_paid', true) : null;

                    if ($invoice_fully_paid=="true" && $invoice_amount_due<1 && $the_amount_paid == $invoice_amount) {
                        $invoice_info = array(
                            "invoice_number"        => $invoice_number,
                            "request_reference"     => $req_reference,
                            "date_paid"             => $date_paid,
                            "fully_paid"            => $invoice_fully_paid,
                            "invoice_category"      => $invoice_category,
                            "invoice_sub_category"  => $invoice_sub_category,
                            "user_id"               => get_current_user_id()
                        );
                        $encrypted_invoice = urlencode(openssl_encrypt($invoice_number, "AES-128-ECB", "secretecode"));
                        $value = openssl_encrypt(json_encode($invoice_info), "AES-128-ECB", "secretecode");
                        setcookie(
                            'mtii_payment_invoice',  json_encode($value),
                            time()+60*60*24*365, COOKIEPATH, COOKIE_DOMAIN
                        );
                        if ($invoice_category=="Cooperative") {
                            wp_redirect(site_url("/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D"));
                        }// } else {
                        //     wp_redirect(site_url("/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D"));
                        // }
                    }
                }
            }
        }
    }


    public function add_invoice_as_custom_post($user_invoice_details)
    {
        $existing_invoice = get_page_by_title($user_invoice_details->InvoiceNumber, OBJECT, 'mtii_cbs_invoice');

        if ($existing_invoice==null) {
            $content = json_encode($user_invoice_details);
            $args = array(
                'post_title'     => sanitize_text_field($user_invoice_details->InvoiceNumber),
                'post_content'   => $content,
                'post_status'    => 'publish',
                'post_type'      => 'mtii_cbs_invoice',
                'meta_input'     => array(
                    'payer_email'               => sanitize_text_field($user_invoice_details->Email),
                    'request_reference'         => sanitize_text_field($user_invoice_details->RequestReference),
                    'payer_id'                  => sanitize_text_field($user_invoice_details->PayerId),
                    'invoice_created_by'        => get_current_user_id(),
                    'recipient'                 => sanitize_text_field($user_invoice_details->Recipient),
                    'invoice_amount'            => sanitize_text_field($user_invoice_details->AmountDue),
                    'amount_due'                => sanitize_text_field($user_invoice_details->AmountDue),
                    'invoice_category'          => sanitize_text_field($user_invoice_details->invoice_category),
                    'invoice_sub_category'      => sanitize_text_field($user_invoice_details->invoice_sub_category),
                    'date_created'              => Date("Y-m-d"),
                    'amount_paid'               => 0,
                    'date_paid'                 => null,
                    'invoice_fully_paid'        => false
                )
            );
            $result = wp_insert_post($args);
            if (is_wp_error($result)) {
                return "There is an Error";
            } else {
                return "Invoice added to Database";
            }
        } else {
            $invoice_paid = get_post_meta($existing_invoice->ID, 'amount_paid', true);
            if($invoice_paid==true) {
                return "Invoice is already Used";
            } else {
                $content = json_encode($user_invoice_details);
                $args = array(
                    'post_title'     => sanitize_text_field($user_invoice_details->InvoiceNumber),
                    'post_content'   => $content,
                    'post_status'    => 'publish',
                    'post_type'      => 'mtii_dup_invoice',
                    'meta_input'     => array(
                        'payer_email'               => sanitize_text_field($user_invoice_details->Email),
                        'request_reference'         => sanitize_text_field($user_invoice_details->RequestReference),
                        'payer_id'                  => sanitize_text_field($user_invoice_details->PayerId),
                        'recipient'                 => sanitize_text_field($user_invoice_details->Recipient),
                        'invoice_created_by'        => get_current_user_id(),
                        'invoice_amount'            => sanitize_text_field($user_invoice_details->AmountDue),
                        'amount_due'                => sanitize_text_field($user_invoice_details->AmountDue),
                        'invoice_category'          => sanitize_text_field($user_invoice_details->invoice_category),
                        'invoice_sub_category'      => sanitize_text_field($user_invoice_details->invoice_sub_category),
                        'date_created'              => Date("Y-m-d"),
                        'amount_paid'               => 0,
                        'date_paid'                 => null,
                        'invoice_fully_paid'        => false

                    )
                );
                $result = wp_insert_post($args);
                if (is_wp_error($result)) {
                    return "Duplicate Invoice couldn't save";
                } else {
                    return "Invoice saved as a duplicate";
                }
            }
        }
    }

    public function update_invoice_as_custom_post($user_invoice_details)
    {
        $saved_invoice_when_generated = get_page_by_title($user_invoice_details["InvoiceNumber"], OBJECT, 'mtii_cbs_invoice');

        if ($saved_invoice_when_generated!=null) {
            $invoice_amount = get_post_meta($saved_invoice_when_generated->ID, 'amount_due', true);
            $invoice_fully_paid = get_post_meta($saved_invoice_when_generated->ID, 'invoice_fully_paid', true);
            $req_reference = get_post_meta($saved_invoice_when_generated->ID, 'request_reference', true);
            $amount_paid = get_post_meta($saved_invoice_when_generated->ID, 'amount_paid', true);

            if ($invoice_fully_paid=="true" || $invoice_amount<1) {
                return "Invoice Already used";
            } else {
                //The variable below should be zero if the amount was paid in full
                $new_amount_due = $invoice_amount - $user_invoice_details["AmountPaid"];
                $the_amount_paid = $user_invoice_details["AmountPaid"] + $amount_paid; //To capture part payment scenario

                $meta_update_amnt_paid = update_post_meta(
                    $saved_invoice_when_generated->ID, 'amount_paid', $the_amount_paid
                );
                $meta_update_date_paid = update_post_meta(
                    $saved_invoice_when_generated->ID, 'date_paid', Date("Y-m-d")
                );
                $meta_update_amount_due = update_post_meta(
                    $saved_invoice_when_generated->ID, 'amount_due', $new_amount_due
                );
                update_post_meta(
                    $saved_invoice_when_generated->ID, 'all_payment_info',  json_encode($user_invoice_details)
                );

                if ($invoice_amount==$user_invoice_details["AmountPaid"]
                    && $req_reference==$user_invoice_details["RequestReference"]
                ) {
                    $meta_update_invoice_used = update_post_meta(
                        $saved_invoice_when_generated->ID, 'invoice_fully_paid', 'true'
                    );
                    if ($meta_update_invoice_used == true && $meta_update_amnt_paid == true
                        && $meta_update_date_paid == true && $meta_update_amount_due == true
                    ) {
                        return "All Updated";
                    } else {
                        return "Info Partially Updated";
                    }
                } else {
                    return "Amount or Request Reference Error";
                }
            }
        } else {
            return "Invoice Not Found";
        }
    }

    public function send_payment_emails($user_invoice_details, $updated_invoice_response) {
        $saved_invoice_when_generated = get_page_by_title($user_invoice_details["InvoiceNumber"], OBJECT, 'mtii_cbs_invoice');
        $payer_name = $saved_invoice_when_generated!=null
            ? get_post_meta($saved_invoice_when_generated->ID, 'recipient', true) : '';
        $recipient = $saved_invoice_when_generated!=null
            ? get_post_meta($saved_invoice_when_generated->ID, 'payer_email', true) : '';
        $invoice_amount = $saved_invoice_when_generated!=null
            ? get_post_meta($saved_invoice_when_generated->ID, 'invoice_amount', true) : '';

        if ($updated_invoice_response=="Invoice Already used") {
            $payer_message = 'Hello '.$payer_name.',<br /><br />'.
            'Please be informed that the invoice you just paid for with the invoice number <strong>'.
            $user_invoice_details["InvoiceNumber"].'</strong> is a previously used Invoice.'.
            '. It is <strong>Strongly</strong>  advised that you reach the admin for clarifications and rectification. '.
            'We deeply regret any Inconvenience this might cause you. Thank you!';

            $mail_content = $this->create_email_from_template(
                'Invoice is already Used', "$payer_message"
            );

        } else if ($updated_invoice_response=="Amount or Request Reference Error") {
            $payer_message = 'Hello '.$payer_name.',<br /><br />'.
            'Your payment was successful but there is an amount error or a request refrence error. Your Invoice number is <strong>'.
            $user_invoice_details["InvoiceNumber"].'</strong>. It is <strong>Strongly</strong>  advised that you reach the '.
            'admin for clarifications and rectification. We deeply regret any Inconvenience this might cause you. Thank you!';

            $mail_content = $this->create_email_from_template(
                'Amount or Reference Error during Payment', $payer_message
            );
        } else if ($updated_invoice_response=="Info Partially Updated") {
            $payer_message = 'Hello '.$payer_name.',<br /><br />'.
            'Your payment was successful but there is an error when saving your payment information. Your Invoice number is <strong>'.
            $user_invoice_details["InvoiceNumber"].'</strong>. It is <strong>Strongly</strong>  advised that you contact the '.
            'admin with your invoice number and payment information for proper documentation. We deeply regret any Inconvenience this '.
            'might cause you. Thank you!';

            $mail_content = $this->create_email_from_template(
                'Invoice Update Error', $payer_message
            );
        } else if ($updated_invoice_response=="Invoice Not Found") {
            $payer_message = 'Hello Admin,<br /><br />'.
            'There is a payment with the Invoice number <strong> '.
            $user_invoice_details["InvoiceNumber"].'</strong>. This invoice is not recognized in the database. '.
            'Please make further investigations and clarifications.';

            $mail_content = $this->create_email_from_template(
                'Invoice not Found', $payer_message
            );
        } else if ($updated_invoice_response=="All Updated") {
            $payer_message = 'Congratulations '.$payer_name.',<br /><br />'.
            'Your payment was successful and Your invoice has been updated. Your Invoice number is <strong>'.
            $user_invoice_details["InvoiceNumber"].'</strong>. You can login into the website to continue registration'.
            'Thank you!';

            $mail_content = $this->create_email_from_template(
                'Payment Successful!', $payer_message
            );
        }

        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail(array($recipient, 'devignersplacefornassarawa@gmail.com'), 'MTII Payment Notification', $mail_content, $headers);
    }

    public function add_signed_doc_as_custom_post($invoice_number, $cloudinary_return)
    {
        $existing_invoice = get_page_by_title($invoice_number, OBJECT, 'mtii_signed_uploads');
        $content = json_encode($cloudinary_return);
        if ($existing_invoice==null) {
            $args = array(
                'post_title'     => sanitize_text_field($invoice_number),
                'post_content'   => $content,
                'post_status'    => 'publish',
                'post_type'      => 'mtii_signed_uploads',
                'meta_input'     => array(
                    'secure_url'         => sanitize_text_field($cloudinary_return['secure_url']),
                    'signature'          => sanitize_text_field($cloudinary_return['signature']),
                    'public_id'          => sanitize_text_field($cloudinary_return['public_id']),
                    'user_id'            => sanitize_text_field(get_current_user_id()),
                    'admin_approved'     => "Awaiting Approval",
                    'date_created'       => Date("Y-m-d"),
                )
            );
            $result = wp_insert_post($args);
            if (is_wp_error($result)) {
                return "There is an Error during Addition";
            } else {
                return "Document uploaded and added to Database";
            }
        } else {
            delete_post_meta($existing_invoice->ID, 'public_id');
            delete_post_meta($existing_invoice->ID, 'secure_url');
            delete_post_meta($existing_invoice->ID, 'signature');
            delete_post_meta($existing_invoice->ID, 'admin_approved');
            $args = array(
                'ID'             => $existing_invoice->ID,
                'post_title'     => sanitize_text_field($invoice_number),
                'post_content'   => $content,
                'post_status'    => 'publish',
                'post_type'      => 'mtii_signed_uploads',
                'meta_input'     => array(
                    'secure_url'         => sanitize_text_field($cloudinary_return['secure_url']),
                    'signature'          => sanitize_text_field($cloudinary_return['signature']),
                    'public_id'          => sanitize_text_field($cloudinary_return['public_id']),
                    'admin_approved'     => "Awaiting Approval",
                    'date_updated'       => Date("Y-m-d"),
                )
            );
            $result = wp_insert_post($args);
            if (is_wp_error($result)) {
                return "There is an Error during Update";
            } else {
                return "Document upload successfully updated";
            }
        }
    }



    public function create_email_from_template ($email_heading, $mail_content)
    {
        $output = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        $output .= '<html xmlns="http://www.w3.org/1999/xhtml">';
        $output .= '<head>';
        $output .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $output .= '<title>MTII</title>';
        $output .= '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
        $output .= '<link href="https://fonts.googleapis.com/css?family=Cabin:400&display=swap" rel="stylesheet">';
        $output .= '</head>';
        $output .= '<body style="margin:0; padding: 0; font-family: '.'Cabin'.', sans-serif;">';
        $output .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #fff; min-height: 100vh">';
        $output .= '<tr>';
        $output .= '<td style="display: block;">';
        $output .= '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%"';
        $output .= 'style="background: #fff; min-height: 85vh; padding: 40px 0; border-radius: 0 40px;';
        $output .= 'margin: 20px auto; border-collapse: collapse;">';
        $output .= '<tr>';
        $output .= '<td>';
        $output .= '<img src="https://res.cloudinary.com/ministry-of-trade-industry-and-investment/image/upload/v1587394399/mtii_logo_lmgsnf.jpg"';
        $output .= 'alt="mtii-logo" width="200px" style="margin: 20px auto; display: block;"';
        $output .= '/>';
        $output .= '<h1 style="margin: 0; padding-left: 0px; font-size: 20px;">'.$email_heading.'</h1>';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '<tr>';
        $output .= '<td style="padding: 10px 0; font-size: 15px; font-family: '.'Cabin'.', sans-serif;">';
        $output .= $mail_content;
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '<tr>';
        $output .= '<td style="padding: 10px 0; font-size: 15px; font-family: '.'Cabin'.', sans-serif;">';
        $output .= 'Best Regards';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '<tr>';
        $output .= '<td style="display: block; margin-top:-22px; padding-left: 0px; font-size: 12px;">';
        $output .= '<em style="font-family: '.'Cabin'.', sans-serif;">Ministry of Trade, Industry and Investment</em>';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '</table>';
        $output .= '<table align="left" border="0" cellpadding="0" cellspacing="0" width="100%"';
        $output .= 'style="display: block; background: #f9f9f9; padding: 10px 0; border-radius: 10px; width:100%;';
        $output .= 'margin: 20px auto; border-collapse: collapse;">';
        $output .= '<img src="https://res.cloudinary.com/ministry-of-trade-industry-and-investment/image/upload/v1587394399/mtii_logo_lmgsnf.jpg"';
        $output .= 'alt="mtii-logo" width="50px" style="margin: 10px 20px; display: block;"';
        $output .= '/>';
        $output .= '<tr style="display:block; width: 100%; margin: 10px 20px; margin-bottom: 0px">';
        $output .= '<td style="padding: 10px; font-size: 12px; color: #c9c9c9;">';
        $output .= '<hr style="display: block; width: 100%; border: 0; height: 2px; margin: 5px auto; background: #c9c9c9;" />';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '<tr style="display:block; width: 100%; margin: 10px 20px; margin-bottom: 0px">';
        $output .= '<td style="padding: 10px; font-size: 12px; color: #c9c9c9;">';
        $output .= 'MTII - Beside St. William Cathedral, Jos Road, Lafia, Nasarawa State. | +2348060721155';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '<tr style="display:block; width: 100%; margin: 10px 20px; margin-top: 0px">';
        $output .= '<td style="padding: 0 10px; font-size: 12px; color: #c9c9c9;">';
        $output .= 'mtii@nasarawastate.gov.ng';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '</table>';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '</table>';
        $output .= '</body>';
        $output .= '</html>';

        return $output;
    }

}