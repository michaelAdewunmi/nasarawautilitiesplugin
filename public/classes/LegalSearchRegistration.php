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
class LegalSearchRegistration extends RegistrationUtilities
{
    /**
     * A handler to determine if records were successfully added to DB
     *
     * @var string
     */
    private $_records_addition_status = false;

    /**
     * A handler to hold the invoice category
     *
     * @var boolean
     */
    protected $the_invoice_category = null;

    /**
     * An array of all input_names
     *
     * @var array
     */
    protected $all_input_names = array (
        'name_of_ngo_or_cooperative', 'organization', 'applicant_full_name', 'phone_number', 'email',
        'certificate_number', 'date_cert_was_issued'
    );

    /**
     * An array of all input_names and their frontend readable names
     *
     * @var array
     */
    protected $all_input_names_as_assoc = array();

    /**
     * An array of all input_names and their frontend readable names
     *
     * @var array
     */
    protected $reg_form_type = "is_legal_search";


    /**
     * Class' Construct
     *
     * @return mixed
     */
    public function __construct()
    {
        $invoice_info = $this->get_invoice_info_from_db();
        $invoice_catg = isset($invoice_info) ? $invoice_info->invoice_category : null;

        if ($invoice_catg && $invoice_catg!='') {
            $this->the_invoice_category = $invoice_catg;
        }

        foreach ($this->all_input_names as $input_name) {
            $this->all_input_names_as_assoc[$input_name] = ucwords(str_replace("_", " ", $input_name));
        }

        parent::__construct();
    }

    protected function  get_db_form_info_and_use_as_default_val()
    {
        $db_info_to_get=$this->get_legal_search_reg_info();
        if (!$db_info_to_get) {
            return;
        }
        foreach ($this->all_form_input_names as $input_name) {
            $this->input_values_placeholder[$input_name] = isset($db_info_to_get->$input_name) ? $db_info_to_get->$input_name : '';
        }
    }

    public function get_the_invoice_category()
    {
        return $this->the_invoice_category;
    }


    public function check_if_legal_search_invoice_is_used()
    {
        global $mtii_legal_search_table;
        $task_performer = new TasksPerformer;
        $legal_search_invoice = $this->get_invoice_info_from_db();
        $invoice_sub_catg = $legal_search_invoice->invoice_sub_category;
        $linked_coop = $mtii_legal_search_table->get_by(
            'invoice_number_filled_against', $legal_search_invoice->invoice_number
        );
        $allow_edit = isset($linked_coop->allow_edit) ? $linked_coop->allow_edit==false : null;

        if ($allow_edit && ($linked_coop || isset($linked_coop->name_of_ngo_or_cooperative))) {
            $coop = urlencode(openssl_encrypt("cooperative-soc", "AES-128-ECB", "X340&2&230rTHJ34"));
            $ngo = urlencode(openssl_encrypt("ngo-cbo", "AES-128-ECB", "X340&2&230rTHJ34"));

            $encoded_payment_catg = $legal_search_invoice->invoice_category=="Cooperative" ? $coop : $ngo;

            $error_title = 'Already Used Invoice';
            $error_body = 'This Invoice has already being used for ';
            $error_body .= 'legal search. You should raise a new Invoice and make payment';
            $error_body .= ' <a class="round-btn-mtii small-btn" href="'.
                    site_url("/user-dashboard?").'do=pay&catg='.$encoded_payment_catg.'" >Create New Invoice</a>';
            return $task_performer->output_inline_notification($error_title, $error_body, 'is-error');
        } else if ( $allow_edit==true && $linked_coop && $invoice_sub_catg=="lega_search") {
            $this->get_db_form_info_and_use_as_default_val();
        } else {
            return null;
        }
    }

    public function mtii_validate_input($input_name, $input_val)
    {
        if ($input_name!='certificate_number' && $input_name!='date_cert_was_issued') {
            if (!$input_val || trim($input_val)=="") {
                $error_id = $input_name;
                $error_output = ucfirst($this->form_nice_names[$error_id])." cannot be blank";
                $this->add_to_wp_global_error($error_id, $error_output);
                $this->errored_inputs_classes[$input_name] = 'errored';
            }
        }
    }

    /**
     * Validate the form by validating all individual inputs
     *
     * @return void
     */
    public function mtii_validate_reg_form()
    {
        $task_performer = new TasksPerformer;
        $all_input_names = $this->all_form_input_names;
        $all_info_as_arr = array ();
        foreach ($all_input_names as $input_name) {
            $input_value = isset($_POST[$input_name]) ? $_POST[$input_name] : '';
                $this->mtii_validate_input($input_name, $input_value);
                $all_info_as_arr[$input_name] = $input_value;
        }
        $invoice_info = $this->get_invoice_info_from_db();
        if ($this->no_form_errors() && !$this->error_output_all) {
            $reg_as_cp = get_page_by_title($invoice_info->invoice_number, OBJECT, 'mtii_legal_search');
            $post_id = null;
            if ($reg_as_cp && $reg_as_cp!="") {
                $post_id = isset($reg_as_cp->ID) ? $reg_as_cp->ID : null;
            }
            $cpt_content = json_encode($all_info_as_arr);
            if ($invoice_info->invoice_category==="NGOs and CBOs") {
                $custom_fields = array(
                    "invoice_category"      => "ngoAndCbo",
                    "invoice_sub_category"  => $invoice_info->invoice_sub_category
                );
            } else {
                $custom_fields = array(
                    "invoice_category"      => "Cooperative",
                    "invoice_sub_category"  => $invoice_info->invoice_sub_category
                );
            }
            $add_reg_as_cp = $task_performer->make_a_post(
                'mtii_legal_search', $invoice_info->invoice_number, $cpt_content, $custom_fields, $post_id
            );
            if ($add_reg_as_cp=="There is an Error") {
                $error_body = 'Your registration could not continue due to an Error! You should try again.';
                $error_body .= 'If the Error persists, then you should contact Admin.';
                echo $task_performer->output_inline_notification('Upload Error!', $error_body, 'is-error');
            } else {
                $this->add_all_info_to_db();
            }
        }
    }

    public function get_legal_search_reg_info($invoice_number=null, $request_reference=null)
    {
        global $mtii_legal_search_table;
        $invoice_info  =  !$invoice_number ? $this->get_invoice_info_from_db() : null;
        $invoice_number = $invoice_number ? $invoice_number : $invoice_info->invoice_number;
        $request_reference = $request_reference ? $request_reference : $invoice_info->request_reference;
        return $mtii_legal_search_table->get_row_by_invoice($invoice_number, $request_reference, true);
    }

    protected function add_all_info_to_db()
    {
        global $mtii_legal_search_table;
        global $wpdb;
        $task_performer = new TasksPerformer;
        $invoice_info = $this->get_invoice_info_from_db();
        $legal_search_form = $this->get_legal_search_reg_info();
        $this->get_form_data_as_assoc();
        $form_values = $this->form_values;
        $form_values["invoice_number_filled_against"] = $invoice_info->invoice_number;
        $form_values["request_ref_filled_against"] = $invoice_info->request_reference;
        $form_values["user_id"] = get_current_user_id();
        $form_values["application_date"] = date("Y:m:d");
        $form_values["is_admin_approved"] = "Awaiting Approval";
        if ($legal_search_form) {
            $legal_search_id = $legal_search_form->application_form_id;
            $inserted_row_id = $mtii_legal_search_table->update($legal_search_id, $form_values);
        } else {
            $inserted_row_id = $mtii_legal_search_table->insert($form_values);
        }

        if ($wpdb->last_error != '') {
            $error_body = 'Your registration cannnot continue. There was a problem adding your records.';
            $error_body .= 'If the Error persists, please contact Admin.';
            echo $task_performer->output_inline_notification('Could not add Records!', $error_body, 'is-error');
        } else {
            unset($_POST);
            $this->_records_addition_status = "All Processes Done";
        }
    }

    public function get_upload_and_reg_success()
    {
        return $this->_records_addition_status;
    }

    public function mtii_save_reg_form_info()
    {
        if (isset($_POST["mtii_form_submit"])
            && ((isset($_POST['form_register_nonce']) && wp_verify_nonce($_POST['form_register_nonce'], 'form-register-nonce'))
            || (isset($_POST['main_registration_nonce']) && wp_verify_nonce($_POST['main_registration_nonce'], 'main-registration-nonce'))
            || (isset($_POST['signatories_template_nonce']) && wp_verify_nonce($_POST['signatories_template_nonce'], 'signatories-template-nonce')))

        ) :
            if ($this->check_if_legal_search_invoice_is_used()) {
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