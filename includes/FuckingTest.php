<?php
/**
 * This class defines all code necessary for handling Cron Jobs
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
namespace MtiiUtilities;

use MtiiUtilities\TasksPerformer;
/**
 * This class defines all code necessary for handling Cron Jobs
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class FuckingTest
{
    /**
     * The version of this plugin.
     *
     * @access private
     *
     * @var string $version    The ID of this plugin.
     *
     * @since 1.0.0
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @access private
     *
     * @var string $version    The current version of this plugin.
     *
     * @since 1.0.0
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
    }

    private function ordinal($number) {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13))
            return $number. 'th';
        else
            return $number. $ends[$number % 10];
    }

    private function unschedule_cron_jobs()
    {
        wp_clear_scheduled_hook('josbiz_mtii_daily_tasks');
    }

    public function mtii_daily_tasks_scheduler()
    {
        // wp_mail(
        //     'devignersplacefornassarawa@gmail.com',
        //     'This is a Scheduled Cron Job',
        //     'This is a Scheduled Cron Job. Reeeeeaaally Scheduled'
        // );
        $this->update_registered_cooperatives_invoices();
    }

    public function add_info_to_invoice($invoice_number, $invoice_id)
    {
        global $mtii_db_invoice;
        $tasks_performer = new TasksPerformer;
        $req_reference = get_post_meta($invoice_id, 'req_reference', true);
        $invoice_category = get_post_meta($invoice_id, 'invoice_category', true);
        $invoice_sub_category = get_post_meta($invoice_id, 'invoice_sub_category', true);
        $invoice_payer_email = get_post_meta($invoice_id, 'invoice_payer_email', true);
        $date_paid = get_post_meta($invoice_id, 'date_paid', true);
        $invoice_expiry_timestamp = strtotime($date_paid)+60*60*24*365;
        $invoice_expiry_date = date("Y-m-d\TH:i:s\Z", $invoice_expiry_timestamp);
        $connected_org = get_post_meta($invoice_id, 'connected_org', true);

        if ($tasks_performer->check_date_difference($date_paid) > 366) {
            $invoice_status =  'expired';
        } else {
            $invoice_status = get_post_meta($invoice_id, 'invoice_status', true);
        }

        $args = array (
            "invoice_number"            => $invoice_number,
            "request_reference"         => $req_reference,
            "invoice_category"          => $invoice_category,
            "invoice_sub_category"      => $invoice_sub_category,
            "invoice_payer_email"       => $invoice_payer_email,
            "payment_date"              => $date_paid,
            "invoice_expires"           => $invoice_expiry_date,
            "invoice_status"            => $invoice_status,
            "connected_org"             => $connected_org
        );

        $repl = $mtii_db_invoice->get_row_by_invoice($invoice_number, $req_reference);
        if ($repl && $repl!=null && $repl!="") {
            $args["invoice_id"] = $repl->invoice_id;
            $inserted_id = $mtii_db_invoice->update($repl->invoice_id, $args);
        } else {
            $inserted_id = $mtii_db_invoice->insert($args);
        }
    }

    private function update_registered_cooperatives_invoices()
    {
        global $mtii_db_coop_main_form;
        //global $mtii_db_invoice;
        // $tasks_performer = new TasksPerformer;
        $all_coop = $mtii_db_coop_main_form->get_all();

        foreach ($all_coop as $coop) {
            $content = "\n".$coop->invoice_number_filled_against;
            $this->write_to_log_invoice($content);
            echo $content;
            // $this->check_expiration_and_send_mails(
            //     $coop->invoice_number_filled_against, $coop->request_ref_filled_against, "Cooperative Society"
            // );
        }
        $this->write_to_log_invoice("\n=================================================================");
        //
        // $mtii_db_coop_main_form;
        // $mtii_signatories_template_db;
        // $mtii_biz_prem_db_main;
        // $mtii_ngo_cbo_db_table;
        // $mtii_cert_replacement_table;
        // $mtii_legal_search_table;
    }

    private function check_expiration_and_send_mails($invoice_number, $request_reference, $category)
    {
        //global $mtii_db_invoice;
        $tasks_performer = new TasksPerformer;
        $invoice_from_cp = get_page_by_title($invoice_number, OBJECT, 'mtii_cbs_invoice');
        $post_id = isset($invoice_from_cp->ID) ? $invoice_from_cp->ID : null;
        $date_paid_from_cp = get_post_meta($post_id, 'date_paid', true);
        $society_name = get_post_meta($post_id, 'connected_org', true);
        //$invoice_info_from_db = $mtii_db_invoice->get_row_by_invoice($invoice_number, $request_reference);
        $days_used = $tasks_performer->check_date_difference($date_paid_from_cp);

        $content = "\nThis is Me and I am here ".$days_used."\n"
            .$date_paid_from_cp."\n".json_encode($invoice_from_cp);

        $this->write_to_log($content);
        echo $content;

        if (!$invoice_from_cp || $invoice_from_cp=='' || !$post_id || $days_used=="Invalid Date") {
            $this->write_to_log_another($content);
            return;
        }

        if ($days_used > 366) {
            $sent_email = get_post_meta($post_id, 'certificate_expiry_notification', true);
            if (!$sent_email || $sent_email!="Sent") {
                $this->send_expiration_notification($invoice_number, $post_id, $society_name, $category);
                update_post_meta($post_id, 'certificate_expiry_notification', 'Sent');
            } else {
                echo "Sent for".$invoice_number;
            }
        } else if ($days_used > 360) {
            $sent_email = get_post_meta($post_id, 'one_week_notification_sent', true);
            if (!$sent_email || $sent_email!=="Sent") {
                $this->send_notification_before_expiry($invoice_number, $post_id, $society_name, "One Week");
                update_post_meta($post_id, 'one_month_notification_sent', 'Sent');
            } else {
                echo "Sent 2 for".$invoice_number;
            }
        } else if ($days_used > 330) {
            $sent_email = get_post_meta($post_id, 'one_month_notification_sent', true);
            if (!$sent_email || $sent_email!=="Sent") {
                $this->send_notification_before_expiry($invoice_number, $post_id, $society_name, "One Month");
                update_post_meta($post_id, 'one_month_notification_sent', 'Sent');
            } else {
                echo "Sent 3 for".$invoice_number;
            }
        } else if ($days_used > 306) {
            $sent_email = get_post_meta($post_id, 'two_months_notification_sent', true);
            if (!$sent_email || $sent_email!=="Sent") {
                $this->send_notification_before_expiry($invoice_number, $post_id, $society_name, "Two Months");
                update_post_meta($post_id, 'two_months_notification_sent', 'Sent');
            }  else {
                echo "Sent 4 for".$invoice_number;
            }
        } else if ($days_used > 276) {
            $sent_email = get_post_meta($post_id, 'three_months_notification_sent', true);
            if (!$sent_email || $sent_email!=="Sent") {
                $this->send_notification_before_expiry($invoice_number, $post_id, $society_name, "Three Months");
                update_post_meta($post_id, 'three_months_notification_sent', 'Sent');
            } else {
                echo "Sent 5 for".$invoice_number;
            }
        }
    }

    private function send_notification_before_expiry($invoice_number, $post_id, $society_name, $expiry_string)
    {

        $tasks_performer = new TasksPerformer;
        $user_info = $tasks_performer->get_user_info_from_invoice($post_id);
        $full_name = $user_info["full_name"];
        $recipient = $user_info["user_email"];
        $heading_main = "Registration expiry notification";
        $email_heading_inside = "Your Registration expires in 3 months";
        $email_body = 'Hello '.$full_name.',<br /><br />'.
                        'This is to inform you that the invoice number <strong>'.
                        $invoice_number.'</strong> associated with '.$society_name.
                        ' will be expiring in '.$expiry_string.' time. Please put this in mind so you can'.
                        ' update your registration upon expiry in order to renew your certificate. Thank you!';
        $tasks_performer->mtii_send_email_to_address($email_body, $email_heading_inside, $heading_main, $recipient);
    }

    private function send_expiration_notification($invoice_number, $post_id, $society_name, $reg_category)
    {
        $tasks_performer = new TasksPerformer;
        $user_info = $tasks_performer->get_user_info_from_invoice($post_id);
        $full_name = $user_info["full_name"];
        $recipient = $user_info["user_email"];

        $log =  "\nHere I am ".$full_name."\n".$recipient;
        $this->write_to_log($log);
        $heading_main = "Your ".$reg_category." Registration has expired";
        $email_heading_inside = "Your Registration has expired";
        $email_body = 'Hello '.$full_name.',<br /><br />'.
                    'This is to inform you that the invoice number <strong>'.
                    $invoice_number.'</strong> associated with '.$society_name.
                    ' has expired. Bear in mind that this has automatically made '.$society_name.
                    '\'s registered certificate invalid. You should make payment to renew your '.
                    'certificate as soon as possible. Thank you!';
        $tasks_performer->mtii_send_email_to_address($email_body, $email_heading_inside, $heading_main, $recipient);
    }

    private function write_to_log($log)
    {
        $dfile = WP_CONTENT_DIR.'/plugins/mtii-utilities/public/logs.txt';
        $myfile = fopen($dfile, "a");
        fwrite($myfile, $log);
        fclose($myfile);
    }

    private function write_to_log_another($log)
    {
        $dfile = WP_CONTENT_DIR.'/plugins/mtii-utilities/public/logs_another.txt';
        $myfile = fopen($dfile, "a");
        fwrite($myfile, $log);
        fclose($myfile);
    }

    private function write_to_log_invoice($log)
    {
        $dfile = WP_CONTENT_DIR.'/plugins/mtii-utilities/public/logs_invoice.txt';
        $myfile = fopen($dfile, "a");
        fwrite($myfile, $log);
        fclose($myfile);
    }
}
?>