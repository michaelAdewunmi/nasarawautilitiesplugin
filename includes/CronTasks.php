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
use MtiiUtilities\MtiiRelatedInformation;
use MtiiUtilities\RegistrationUtilities;
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
class CronTasks
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
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    private function unschedule_cron_jobs()
    {
        wp_clear_scheduled_hook('josbiz_mtii_daily_tasks');
    }

    public function mtii_schedule_daily_cron_task()
    {
        //$this->unschedule_cron_jobs();
        if (!wp_next_scheduled('josbiz_mtii_daily_tasks')) {
            date_default_timezone_set("Africa/Lagos");
            wp_schedule_event(strtotime("21:35:00"), 'mtii_five_mins', 'josbiz_mtii_daily_tasks');
            //wp_schedule_event(strtotime('tomorrow midnight'), 'daily', 'josbiz_mtii_daily_tasks');
            //wp_schedule_event(time(), 'mtii_five_mins', 'josbiz_mtii_daily_tasks');
        }
    }

    public function mtii_custom_cron_schedules($schedules)
    {
        if (!isset($schedules["mtii_five_mins"])) {
            $schedules["mtii_five_mins"] = array(
                'interval' => 5*60,
                'display' => __('Once every 5 minutes')
            );
        }
        if (!isset($schedules["mtii_thirty_mins"])) {
            $schedules["mtii_thirty_mins"] = array(
                'interval' => 30*60,
                'display' => __('Once every 30 minutes')
            );
        }
        return $schedules;
    }

    public function mtii_daily_tasks_scheduler()
    {
        wp_mail(
            'devignersplacefornassarawa@gmail.com',
            'This is a Scheduled Cron Job',
            'This is a Scheduled Cron Job. Reeeeeaaally Scheduled'
        );
        $this->update_invoice_info_in_db_table();
        $this->check_cooperatives_invoices_and_send_expiry_mails();
        $this->check_ngo_invoices_and_send_expiry_mails();
        $this->check_biz_prem_invoices_and_send_expiry_mails();
    }

    public function update_invoice_info_in_db_table()
    {
        // 'date_query' => array(
            //     array(
            //         'year'  => date('Y'),
            //         'month' => date('m'),
            //         'day'   => 14,
            //     )
            // ),

        $args = array (
            'post_type'         => 'mtii_cbs_invoice',
            'meta_query'        => array(
                array (
                    'key'       => 'invoice_fully_paid',
                    'value'     => 'true',
                    'compare'   => '='
                )
            ),
            'posts_per_page'    => -1,
        );

        $invoice_from_cp = new \WP_Query($args);
        //$this->write_to_log_another(json_encode($invoice_from_cp));

        while ($invoice_from_cp->have_posts()) {
            $invoice_from_cp->the_post();
            $this->write_to_log_another(get_the_ID());
            $this->update_invoice_in_db(get_the_ID(), get_the_title());
        }
    }

    private function update_invoice_in_db($invoice_id, $invoice_number)
    {
        global $mtii_db_invoice;
        global $wpdb;
        $tasks_performer = new TasksPerformer;

        $req_reference = get_post_meta($invoice_id, 'request_reference', true);
        $invoice_category = get_post_meta($invoice_id, 'invoice_category', true);
        $invoice_sub_category = get_post_meta($invoice_id, 'invoice_sub_category', true);
        $invoice_payer_email = get_post_meta($invoice_id, 'payer_email', true);
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
            "connected_org"             => $connected_org ? $connected_org : ''
        );

        $invoice_in_db = $mtii_db_invoice->get_row_by_invoice($invoice_number, $req_reference);
        if ($invoice_in_db && $invoice_in_db!=null && $invoice_in_db!="") {
            $args["invoice_id"] = $invoice_in_db->invoice_id;
            $inserted_id = $mtii_db_invoice->update($invoice_in_db->invoice_id, $args);
        } else {
            $inserted_id = $mtii_db_invoice->insert($args);
        }

        if (!$inserted_id || $wpdb->last_error != '') {
            $mail_content = "InsertedRow: ".$inserted_id."\n\n\nError: ".$wpdb->last_error;
            echo $wpdb->last_error."<br />";
            wp_mail('devignersplacefornassarawa@gmail.com', 'MTII CRON Error', $mail_content);
        } else {
            $write = "\n".$inserted_id."\n".json_encode($args)."\n".json_encode($invoice_in_db)."\n";
            $this->write_to_log_another($write);
        }
    }

    private function check_cooperatives_invoices_and_send_expiry_mails()
    {
        global $mtii_db_coop_main_form;
        $all_coop = $mtii_db_coop_main_form->get_all();

        $separator = "\n=================================================================\n".
            "COOPERATIVE\n=================================================================\n";
        $this->write_to_log_invoice($separator);
        foreach ($all_coop as $coop) {
            $content = $coop->invoice_number_filled_against."\n";
            $this->write_to_log_invoice($content);
            $this->check_expiration_and_send_mails(
                $coop->invoice_number_filled_against, $coop->request_ref_filled_against,
                "Cooperative Society", $coop->name_of_proposed_society
            );
        }
    }

    private function check_ngo_invoices_and_send_expiry_mails()
    {
        global $mtii_ngo_cbo_db_table;
        $all_ngo = $mtii_ngo_cbo_db_table->get_all();

        $separator = "\n=================================================================\n".
            "NGO/CBO\n=================================================================\n";
        $this->write_to_log_invoice($separator);
        foreach ($all_ngo as $ngo) {
            $content = $ngo->invoice_number_filled_against."\n";
            $this->write_to_log_invoice($content);
            $this->check_expiration_and_send_mails(
                $ngo->invoice_number_filled_against, $ngo->request_ref_filled_against,
                "NGO/CBO",  $ngo->name_of_proposed_organization
            );
        }
    }

    private function check_biz_prem_invoices_and_send_expiry_mails()
    {
        global $mtii_biz_prem_db_main;
        $all_biz_prem = $mtii_biz_prem_db_main->get_all();

        $separator = "\n=================================================================\n".
        "BUSINESS PREMISE\n=================================================================\n";
        $this->write_to_log_invoice($separator);
        foreach ($all_biz_prem as $biz_prem) {
            $content = $biz_prem->invoice_number_filled_against."\n";
            $this->write_to_log_invoice($content);
            $this->check_expiration_and_send_mails(
                $biz_prem->invoice_number_filled_against, $biz_prem->request_ref_filled_against,
                "Business Premise", $biz_prem->name_of_company
            );
        }
    }

    private function check_expiration_and_send_mails($invoice_number, $request_reference, $category, $org_name)
    {
        $tasks_performer = new TasksPerformer;
        $invoice_from_cp = get_page_by_title($invoice_number, OBJECT, 'mtii_cbs_invoice');
        $post_id = isset($invoice_from_cp->ID) ? $invoice_from_cp->ID : null;
        $date_paid_from_cp = get_post_meta($post_id, 'date_paid', true);
        $days_used = $tasks_performer->check_date_difference($date_paid_from_cp);

        $content = $date_paid_from_cp."\n".$days_used."\n".$post_id."\n"
            .json_encode($invoice_from_cp)."\n".$invoice_number."\n";
        if (!$invoice_from_cp || $invoice_from_cp=='' || !$post_id || $days_used=="Invalid Date") {
            return;
        }
        $this->write_to_log($content);

        if ($days_used > 366) {
            $sent_email = get_post_meta($post_id, 'certificate_expiry_notification', true);
            if (!$sent_email || $sent_email!="Sent") {
                $this->send_expiration_notification($invoice_number, $post_id, $org_name, $category);
                update_post_meta($post_id, 'certificate_expiry_notification', 'Sent');
            }
        } else if ($days_used > 360) {
            $sent_email = get_post_meta($post_id, 'one_week_notification_sent', true);
            if (!$sent_email || $sent_email!=="Sent") {
                $this->send_notification_before_expiry($invoice_number, $post_id, $org_name, "One Week");
                update_post_meta($post_id, 'one_month_notification_sent', 'Sent');
            }
        } else if ($days_used > 330) {
            $sent_email = get_post_meta($post_id, 'one_month_notification_sent', true);
            if (!$sent_email || $sent_email!=="Sent") {
                $this->send_notification_before_expiry($invoice_number, $post_id, $org_name, "One Month");
                update_post_meta($post_id, 'one_month_notification_sent', 'Sent');
            }
        } else if ($days_used > 306) {
            $sent_email = get_post_meta($post_id, 'two_months_notification_sent', true);
            if (!$sent_email || $sent_email!=="Sent") {
                $this->send_notification_before_expiry($invoice_number, $post_id, $org_name, "Two Months");
                update_post_meta($post_id, 'two_months_notification_sent', 'Sent');
            }
        } else if ($days_used > 276) {
            $sent_email = get_post_meta($post_id, 'three_months_notification_sent', true);
            if (!$sent_email || $sent_email!=="Sent") {
                $this->send_notification_before_expiry($invoice_number, $post_id, $org_name, "Three Months");
                update_post_meta($post_id, 'three_months_notification_sent', 'Sent');
            }
        }
    }

    private function send_notification_before_expiry($invoice_number, $post_id, $org_name, $expiry_string)
    {

        $tasks_performer = new TasksPerformer;
        $user_info = $tasks_performer->get_user_info_from_invoice($post_id);
        $full_name = $user_info["full_name"];
        $recipient = $user_info["user_email"];
        $heading_main = "Registration expiry notification";
        $email_heading_inside = "Your Registration expires in ".$expiry_string;
        $email_body = 'Hello '.$full_name.',<br /><br />'.
                        'This is to inform you that the invoice number <strong>'.
                        $invoice_number.'</strong> associated with <strong>'.$org_name.
                        '</strong> will expire in '.$expiry_string.' time. Please put this in mind so you can'.
                        ' update your registration upon expiry in order to renew your certificate. Thank you!';
        $tasks_performer->mtii_send_email_to_address($email_body, $email_heading_inside, $heading_main, $recipient);
    }

    private function send_expiration_notification($invoice_number, $post_id, $org_name, $reg_category)
    {
        $tasks_performer = new TasksPerformer;
        $user_info = $tasks_performer->get_user_info_from_invoice($post_id);
        $full_name = $user_info["full_name"];
        $recipient = $user_info["user_email"];

        $heading_main = "Your ".$reg_category." Registration has expired";
        $email_heading_inside = "Your Registration has expired";
        $email_body = 'Hello '.$full_name.',<br /><br />'.
                'This is to inform you that the invoice number <strong>'.
                $invoice_number.'</strong> associated with <strong>'.$org_name.
                '</strong> has expired. Bear in mind that this <strong>has automatically made the'.
                ' registration certificate invalid</strong>. You should make payment to renew your '.
                'certificate as soon as possible. Thank you!';
        $tasks_performer->mtii_send_email_to_address($email_body, $email_heading_inside, $heading_main, $recipient);
    }

    private function write_to_log($log)
    {
        $dfile = WP_CONTENT_DIR.'/plugins/mtii-utilities/public/mtii-logs/logs.txt';
        $myfile = fopen($dfile, "a");
        fwrite($myfile, $log);
        fclose($myfile);
    }

    private function write_to_log_another($log)
    {
        $dfile = WP_CONTENT_DIR.'/plugins/mtii-utilities/public/mtii-logs/logs_another.txt';
        $myfile = fopen($dfile, "a");
        fwrite($myfile, $log);
        fclose($myfile);
    }

    private function write_to_log_invoice($log)
    {
        $dfile = WP_CONTENT_DIR.'/plugins/mtii-utilities/public/mtii-logs/logs_invoice.txt';
        $myfile = fopen($dfile, "a");
        fwrite($myfile, $log);
        fclose($myfile);
    }
}
?>