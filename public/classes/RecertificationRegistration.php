<?php
/**
 * Class for handling certificate replacements
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public/templates
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */

namespace MtiiUtilities;

use MtiiUtilities\RegistrationUtilities;
use MtiiUtilities\TasksPerformer;

/**
 * Class for handling certificate replacements
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public/templates
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class RecertificationRegistration extends RegistrationUtilities
{

    /**
     * A handler to determine if records were successfully added to DB
     *
     * @var string
     */
    private $_records_addition_status = false;

    /**
     * A handler to determine if records were successfully submitted
     *
     * @var boolean
     */
    protected $reg_form_type = "coop_reg_certification";


    /**
     * A handler to determine if there is need to populate the select options for LGA and ward
     *
     * @var boolean
     */
    protected $populate_lga_and_ward_options = true;


    /**
     * A handler to hold the name property of the select Input
     *
     * @var boolean
     */
    protected $lga_select_name = array();

    /**
     * An array of all input_names
     *
     * @var array
     */
    protected $all_input_names = array ();

    /**
     * An array of all options for the position select inputs to be populated from parent or child construct
     *
     * @var array
     */
    protected $options_for_select_inputs = array ();


    /**
     * An array of all input_names and their frontend readable names
     *
     * @var array
     */
    protected $all_input_names_as_assoc = array();

    /**
     * A handler to hide or show the input for recertification
     *
     * @var boolean
     */
    public $hide_recertification_input = false;

    /**
     * A handler to tell if its a new successful recertification registration
     *
     * @var boolean
     */
    public $recertification_successfully_done = false;



    /**
     * Class Instantioation
     *
     * @return mixed
     */
    public function __construct()
    {
        if (isset($_REQUEST["is_new"]) && $_REQUEST["is_new"]==1) {
            $this->all_input_names = array('cooperative_name');
        } else {
            $this->all_input_names = array('expired_invoice_number');
        }
        foreach ($this->all_input_names as $input_name) {
            $this->all_input_names_as_assoc[$input_name] = ucwords(str_replace("_", " ", $input_name));
        }
        parent::__construct();
    }

    public function check_if_recertification_invoice_is_used()
    {
        global $mtii_db_coop_main_form;
        $task_performer = new TasksPerformer;
        $recertification_invoice = $this->get_invoice_info_from_db();
        $linked_coop = $mtii_db_coop_main_form->get_by('invoice_number_filled_against', $recertification_invoice->invoice_number);
        $recertification_invoice_as_post = get_page_by_title($recertification_invoice->invoice_number, OBJECT, 'mtii_cbs_invoice');
        $recertification_connected_org = get_post_meta($recertification_invoice_as_post->ID, 'connected_org', true);
        $recertification_start_use = get_post_meta($recertification_invoice_as_post->ID, 'start_use', true);

        if ($recertification_connected_org || $recertification_start_use || $linked_coop) {
            $encoded_payment_catg = "AScTltDXpUOy0owVUBq5DA%3D%3D";
            $error_title = 'Used Recertification Invoice';
            $error_body = 'This Invoice has already being used for '.$linked_coop->name_of_approved_society;
            $error_body .= '\'s Recertification. You should raise a new Invoice and make payment';
            $error_body .= '<a class="round-btn-mtii small-btn" href="';
            $error_body .= site_url().'?do=reg">Log in to connected Organization</a> &nbsp';
            $error_body .= ' <a class="round-btn-mtii small-btn" href="'.
                    site_url("/user-dashboard?").'do=pay&catg='.$encoded_payment_catg.'" >Create New Invoice</a>';
            return $task_performer->output_inline_notification($error_title, $error_body, 'is-error');
        } else {
            return null;
        }
    }


    /**
     * Validate the input by checking if it is not empty
     *
     * @param [string] $input_name The name property of the html input
     * @param [string] $input_val  The value of the input
     *
     * @return void
     */
    public function mtii_validate_input($input_name, $input_val)
    {
        if ( ($input_name!="value_of_share_holding" && $input_name!="number_of_shares_per_member"
            && $input_name!="total_shared_capital_paid" && $input_name!="area_of_operation"
            && $input_name!="area_of_operation_other") && (!$input_val || trim($input_val)=="")
        ) {
            $error_id = $input_name;
            $error_output = ucfirst($this->form_nice_names[$error_id])." cannot be blank";
            $this->add_to_wp_global_error($error_id, $error_output);
            $this->errored_inputs_classes[$input_name] = 'errored';
        } else {
            if ($input_name!="area_of_operation_other") {
                $this->errored_inputs_classes[$input_name] = '';
            }
        }

        if ($input_name=="area_of_operation") {
            $this->validate_area_of_operation($input_name, $input_val);
        }
    }

    protected function update_recert_invoice_db_table($invoice_as_post, $connected_name, $linked_society=null, $invoice_in_db=null, $new_coop=true)
    {
        global $mtii_db_coop_main_form;
        delete_post_meta($invoice_as_post->ID, 'connected_org');
        delete_post_meta($invoice_as_post->ID, 'start_use');
        delete_post_meta($invoice_as_post->ID, 'invoice_status');
        delete_post_meta($invoice_as_post->ID, 'invoice_sub_category');
        update_post_meta($invoice_as_post->ID, 'invoice_status', 'active');
        update_post_meta($invoice_as_post->ID, 'invoice_sub_category', 'used-coop-recertification');
        update_post_meta($invoice_as_post->ID, 'start_use', date("Y:m:d"));
        update_post_meta($invoice_as_post->ID, 'connected_org', $connected_name);
        if (!$new_coop && $invoice_in_db) {
            $linked_coop_array = (array)$linked_society;
            $linked_coop_array["invoice_number_filled_against"] = $invoice_in_db->invoice_number;
            $linked_coop_array["request_ref_filled_against"] = $invoice_in_db->request_reference;
            $linked_coop_array["admin_approved"] = "Awaiting Approval";
            $main_form_id = $linked_society->application_form_id;
            $update_coop_table = $mtii_db_coop_main_form->update($main_form_id, $linked_coop_array);
        }
    }

    /**
     * Add all inputs into the database
     *
     * @return void
     */
    protected function add_all_info_to_db()
    {
        global $mtii_db_coop_main_form;
        global $mtii_signatories_template_db;
        global $wpdb;
        $task_performer = new TasksPerformer;
        $this->get_form_data_as_assoc();
        $form_values = $this->form_values;
        $error_output = '';

        if (isset($_REQUEST["is_new"]) && $_REQUEST["is_new"]==1) {
            $this->_register_new_online_coop();
        } else {
            $linked_coop = $mtii_db_coop_main_form->get_by('invoice_number_filled_against', $form_values["expired_invoice_number"]);
            $recertification_invoice = $this->get_invoice_info_from_db();
            $recertification_invoice_as_post = get_page_by_title($recertification_invoice->invoice_number, OBJECT, 'mtii_cbs_invoice');
            $recertification_connected_org = get_post_meta($recertification_invoice_as_post->ID, 'connected_org', true);
            $recertification_start_use = get_post_meta($recertification_invoice_as_post->ID, 'start_use', true);

            if (!$linked_coop || $linked_coop=='') {
                $error_title = 'No Previous Records Found';
                $error_body = 'Sorry! We cannot find any records associated with this Invoice. ';
                $error_body .= 'If you are a new Cooperative member, please register first as a new cooperative.';
                $error_body .= 'If you think this is an error, Please contact Admin immediately. Thank you!';
                echo $task_performer->output_inline_notification($error_title, $error_body, 'is-error');
            } else {
                $linked_coop_invoice_number = isset($linked_coop->invoice_number_filled_against) ?
                    $linked_coop->invoice_number_filled_against : null;
                $linked_coop_req_ref = isset($linked_coop->request_ref_filled_against) ?
                    $linked_coop->request_ref_filled_against : null;
                $expired_invoice_in_db = $this->get_invoice_info_from_db($linked_coop_invoice_number, $linked_coop_req_ref);
                $inv_num_enc = urlencode(openssl_encrypt($linked_coop_invoice_number, "AES-128-ECB", "X340&2&230rTHJ34"));
                if ($expired_invoice_in_db && $expired_invoice_in_db!=null) {
                    $expired_invoice = get_page_by_title($linked_coop_invoice_number, OBJECT, 'mtii_cbs_invoice');
                    $date_paid = get_post_meta($expired_invoice->ID, 'date_paid', 'is-notification');
                    $days_since_last_payment = $task_performer->check_date_difference($date_paid);
                    if ($days_since_last_payment<366) {
                        $title = 'Previous Registration still Valid';
                        $body = 'It seems the previous registration for '.strtoupper($linked_coop->name_of_proposed_society);
                        $body .= ' is still valid. You should wait till old invoice has expired! &nbsp';
                        $body .= '<a class="round-btn-mtii small-btn" href="';
                        $body .= site_url().$_SERVER['REQUEST_URI'].'&reset=1&org_source='.$inv_num_enc.'&recert_red=1';
                        $body .= '">Log in with Old invoice</a>';
                        delete_post_meta($recertification_invoice_as_post->ID, 'invoice_status');
                        delete_post_meta($recertification_invoice_as_post->ID, 'connected_org');
                        delete_post_meta($recertification_invoice_as_post->ID, 'start_use');
                        update_post_meta($recertification_invoice_as_post->ID, 'invoice_status', 'pending_use');
                        echo $task_performer->output_inline_notification($title, $body, 'is-notification');
                    } else {
                        $linked_name_from_db = $linked_coop->name_of_approved_society;
                        global $mtii_db_invoice;
                        $recertification_invoice_db = $mtii_db_invoice->get_by(
                            'invoice_number', $recertification_invoice->invoice_number
                        );
                        $recertification_invoice_db_array = (array) $recertification_invoice_db;
                        $recertification_invoice_db_array["start_use"] = date("Y:m:d");
                        $recertification_invoice_db_array["connected_org"] = $linked_name_from_db;
                        $recertification_invoice_db_array["invoice_status"] = "active";
                        $recertification_invoice_db_array["invoice_sub_category"] = "used-coop-recertification";
                        $inv_form_id = $recertification_invoice_db->invoice_id;
                        $update_inv_table = $mtii_db_invoice->update($inv_form_id, $recertification_invoice_db_array);
                        $error_one = $wpdb->last_error;
                        if ($update_inv_table && (!$error_one || $error_one=='')) {
                            $this->update_recert_invoice_db_table(
                                $recertification_invoice_as_post, $linked_name_from_db, $linked_coop, $recertification_invoice_db, false
                            );
                            $error_two = $wpdb->last_error;
                        }
                        if (!$error_one && !$error_two) {
                            $this->delete_expired_invoice_doc($expired_invoice_in_db->invoice_number);
                            $title = 'Recertification successful';
                            $body = 'Your recertification for '.strtoupper($linked_coop->name_of_approved_society);
                            $body .= ' was successful. You should login and upload another signatories form for new approval by Admin. Thank you!';
                            $body .= '<a class="round-btn-mtii small-btn" href="'.site_url('/user-dashboard').'?do=reg';
                            $body .= '">Continue</a>';
                            echo $task_performer->output_inline_notification($title, $body, 'is-success');
                            $this->recertification_successfully_done = "Yes";
                            unset($_POST);
                        } else if ($error_one && $error_two && $error_one!="" && $error_two!="") {
                            $title = 'Recertification Not Successful';
                            $body = 'Your recertification for '.strtoupper($linked_coop->name_of_proposed_society);
                            $body .= ' was not successful. Please contact Admin! Thank you!';
                            echo $task_performer->output_inline_notification($title, $body, 'is-error');
                        } else {
                            $title = 'Recertification successful with errors';
                            $body = 'Your recertification for '.strtoupper($linked_coop->name_of_proposed_society);
                            $body .= ' was successful but there were some errors. Please contact Admin! Thank you!';
                            echo $task_performer->output_inline_notification($title, $body, 'is-error');
                        }
                    }
                } else {
                    $title = "No Registered Invoice for this organization";
                    $body = "There is presently no Registered invoice for this organization. ";
                    $body .= "You should try and login with your organization's expired invoice to clear this error. ";
                    $body .= "If this error persists, then you should contact Admin immediately<br /><br />";
                    $body .= '<a class="round-btn-mtii small-btn" href="';
                    $body .= site_url().$_SERVER['REQUEST_URI'].'&reset=1&org_source='.$inv_num_enc.'&recert_red=1';
                    $body .= '">Log in with expired invoice</a>';
                    echo $task_performer->output_inline_notification($title, $body, 'is-error');
                }
            }
        }
    }

    private function _register_new_online_coop()
    {
        global $mtii_db_coop_main_form;
        global $wpdb;
        $task_performer = new TasksPerformer;
        $all_coops = json_decode($task_performer->get_all_ngos_and_coop());
        $form_values = $this->form_values;

        if (!$task_performer->in_arrayi($form_values["cooperative_name"], $all_coops)) {
            $title = "Society name not Found!";
            $body = "Oops! There is presently no offline Registered organization ";
            $body .= "with this name. Please make payment as a fresh society if you have not registered";
            $body .= " offline. If you have however registered offline, then please contact Admin. Thank you. <br /><br />";
            echo $task_performer->output_inline_notification($title, $body, 'is-error');
        } else {
            $invoice_info = $this->get_invoice_info_from_db();
            $invoice_info_as_post = get_page_by_title($invoice_info->invoice_number, OBJECT, 'mtii_cbs_invoice');
            $linked_coop = $mtii_db_coop_main_form->get_by('invoice_number_filled_against', $invoice_info->invoice_number);
            $linked_coop_from_name = $mtii_db_coop_main_form->get_by('name_of_approved_society', $form_values["cooperative_name"]);
            if ($linked_coop_from_name) {
                $title = "Cooperative already Registered on Portal!";
                $body = "You appear to have had a previous registration on this portal! ";
                $body .= "Pleae contact Admin if you think this could be a mix up. If it is not a mixup";
                $body .= " and you have previously registered online, then please <a style=\"text-decoration: underline\"";
                $body .= "href=\"".site_url('/user-dashboard?do=reg')."\">";
                $body .= "CLICK HERE</a>.";
                $this->hide_recertification_input = true;
                echo $task_performer->output_inline_notification($title, $body, 'is-error');
            } else {
                $form_values["invoice_number_filled_against"] = $invoice_info->invoice_number;
                $form_values["request_ref_filled_against"] = $invoice_info->request_reference;
                $form_values["name_of_proposed_society"] = strtoupper($form_values["cooperative_name"]);
                $form_values["user_id"] = get_current_user_id();
                $form_values["start_edits"] = "False";
                $inserted_row_id = $mtii_db_coop_main_form->insert($form_values);
                if (!$wpdb->last_error) {
                    $this->update_recert_invoice_db_table($invoice_info_as_post, strtoupper($form_values["cooperative_name"]));
                    $title = "Recertification Successful!";
                    $body = "Your recertification was successful. Click the button below to fill in other details <br /><br />";
                    $body .= '<a class="round-btn-mtii small-btn" href="';
                    $body .= site_url('/user-dashboard?do=reg');
                    $body .= '">Continue Registration</a>';
                    $this->hide_recertification_input = true;
                    $this->recertification_successfully_done = "Yes";
                    echo $task_performer->output_inline_notification($title, $body, 'is-success');
                } else {
                    $title = 'We encountered an Error!';
                    $body = 'You should refresh and try again. If this error persists, please contact Admin';
                    echo $task_performer->output_inline_notification($title, $body, 'is-success');
                }
            }
        }
    }

    public function get_upload_and_reg_success()
    {
        return $this->_records_addition_status;
    }

    public function mtii_save_reg_form_info()
    {
        if (isset($_POST["mtii_form_submit"]) && isset($_POST['main_registration_nonce'])
            && wp_verify_nonce($_POST['main_registration_nonce'], 'main-registration-nonce')
        ) :
            if ($this->check_if_recertification_invoice_is_used()) {
                unset($_POST);
                return;
            } else {
                $this->mtii_validate_reg_form();
                if ($this->no_form_errors()) :
                    $this->error_output_all = "There is no Error";
                else :
                    $this->error_output_all = $this->error_output_all;
                endif;
            }
        endif;
    }
}