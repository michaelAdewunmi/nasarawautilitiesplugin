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
class CoopSignatoriesRegistration extends RegistrationUtilities
{
    /**
     * A handler to hold an array of names of inputs in the signatories form
     *
     * @var string
     */
    public $names = array('name', 'occupation', 'village', 'lga');

    /**
     * A handler to hold an afrray of numbers of inputs for each name property in the signatories form.
     *
     * @var string
     */
    public $numbers = array('one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten');

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
    protected $reg_form_type = "signatories_template";


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
     * Perform some needed tasks immeidately after instantiation- This is called from the parent's construct
     *
     * @return mixed
     */
    public function __construct()
    {

        if (isset($_REQUEST["is_preview"]) && $_REQUEST["is_preview"]==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34")) {
            global $mtii_db_coop_main_form;
            $this->get_invoice_info_from_db();
            $reg_form_info = $this->get_coop_main_form_data();
            if ($reg_form_info->admin_approved=="Declined") {
                $reg_info_array = array($reg_form_info);
                $reg_info_array["admin_approved"] = "Awaiting Approval";
                $mtii_db_coop_main_form->update($reg_form_info->application_form_id, $reg_info_array);
            }
        }

        foreach ($this->numbers as $count) {
            foreach ($this->names as $name) {
                $this->all_input_names[] =  $name."_".$count;
                if ($name=="lga") {
                    $this->lga_select_name[] = $name."_".$count;
                }
            }
        }
        foreach ($this->all_input_names as $input_name) {
            $this->all_input_names_as_assoc[$input_name] = ucwords(str_replace("_", " ", $input_name));
        }
        parent::__construct();
    }

    public function can_edit_cooperative_form() {
        $existing_coop_info = $this->get_coop_main_form_data();
        return isset($existing_coop_info->admin_approved) && $existing_coop_info->admin_approved !="Approved" ? "Can Edit" : false;
    }

    protected function get_db_form_info_and_use_as_default_val()
    {
        $db_info_to_get=$this->get_signatories_data();
        if (!$db_info_to_get) {
            return;
        }
        foreach ($this->all_form_input_names as $input_name) {
            $this->input_values_placeholder[$input_name] = isset($db_info_to_get->$input_name) ? $db_info_to_get->$input_name : '';
        }
    }


    public function get_cert_replacement_info($invoice_number=null, $request_reference=null)
    {
        global $mtii_cert_replacement_table;
        $invoice_info  =  !$invoice_number ? $this->get_invoice_info_from_db() : null;
        $invoice_number = $invoice_number ? $invoice_number : $invoice_info->invoice_number;
        $request_reference = $request_reference ? $request_reference : $invoice_info->request_reference;
        return $mtii_cert_replacement_table->get_row_by_invoice($invoice_number, $request_reference, true);
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

        $main_form = $this->get_coop_main_form_data();
        $signatories_form = $this->get_signatories_data();
        $form_values["main_coop_form_id"] = $main_form->application_form_id;
        if ($signatories_form) {
            $signatories_form_id = $signatories_form->signatories_form_id;
            $inserted_row_id = $mtii_signatories_template_db->update($signatories_form_id, $form_values);
        } else {
            $inserted_row_id = $mtii_signatories_template_db->insert($form_values);
        }
        $this->check_db_insertion_success($inserted_row_id);
    }

    public function get_upload_and_reg_success()
    {
        return $this->_records_addition_status;
    }

    public function mtii_save_reg_form_info()
    {
        if (isset($_POST["mtii_form_submit"]) && isset($_POST['signatories_template_nonce'])
            && wp_verify_nonce($_POST['signatories_template_nonce'], 'signatories-template-nonce')
        ) :
            $this->mtii_validate_reg_form();
            if ($this->no_form_errors()) :
                $this->error_output_all = "There is no Error";
            else :
                $this->error_output_all = $this->error_output_all;
            endif;
        endif;
    }
}