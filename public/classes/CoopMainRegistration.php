<?php
/**
 * Class for handling cooperative Main Registration
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
 * Class for handling cooperative Main Registration
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public/templates
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class CoopMainRegistration extends RegistrationUtilities
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
    protected $reg_form_type = "coop_reg_main_form";


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
    protected $lga_select_name = "lga_of_proposed_society";

    /**
     * An array of all input_names
     *
     * @var array
     */
    protected $all_input_names = array (
        'name_of_proposed_society', 'ward_of_proposed_society', 'lga_of_proposed_society',
        'date_of_establisment', 'address_of_proposed_society', 'area_of_operation', 'area_of_operation_other',
        'specific_objectives_of_society', 'value_of_share_holding', 'number_of_shares_per_member',
        'total_shared_capital_paid', 'total_deposit_savings', 'nature_of_proposed_banker',
        'nature_of_coop_society', 'entrance_fee_payable_per_member', 'number_of_memb_at_appl_time',
        'name_of_president', 'number_of_president', 'name_of_vice', 'number_of_vice',
        'name_of_secretary', 'number_of_secretary', 'name_of_treasurer',
        'number_of_treasurer', 'brief_description_of_society_activity',
    );

    /**
     * An array of all options for the position select inputs
     *
     * @var array
     */
    protected $options_for_select_inputs = array (
        "lga_of_proposed_society"   => array(),
        "ward_of_proposed_society"  => array(),
        "area_of_operation"         => array(
            "Agriculture", "Marketing", "Mining", "Thrift and Loan", "Insurance",
            "Estate Development", "Others (Specify Below)"
        ),
        "nature_of_proposed_banker" =>  array(
            "ACCESS BANK PLC", "CITIBANK NIGERIA LIMITED", "ECOBANK NIGERIA PLC", "FIDELITY BANK PLC",
            "FIRST BANK NIGERIA LIMITED", "FIRST CITY MONUMENT BANK PLC", "GLOBUS BANK LIMITED",
            "GUARANTY TRUST BANK PLC", "HERITAGE BANKING COMPANY LTD.", "KEY STONE BANK", "POLARIS BANK", "PROVIDUS BANK",
            "STANBIC IBTC BANK LTD", "STANDARD CHARTERED BANK NIGERIA LTD.", "STERLING BANK PLC",
            "SUNTRUST BANK NIGERIA LIMITED", "TITAN TRUST BANK LTD", "UNION BANK OF NIGERIA PLC",
            "UNITED BANK FOR AFRICA PLC", "UNITY  BANK PLC", "WEMA BANK PLC",  "ZENITH BANK PLC",
        ),
        "nature_of_coop_society"   => array("Liability Limited", "Liability Unlimited"),
    );


    /**
     * An array of all input_names and their frontend readable names
     *
     * @var array
     */
    protected $all_input_names_as_assoc = array();


    /**
     * Class Construct
     *
     * @return null
     */
    public function __construct()
    {
        foreach ($this->all_input_names as $input_name) {
            $this->all_input_names_as_assoc[$input_name] = ucwords(str_replace("_", " ", $input_name));
        }

        parent::__construct();
    }

    protected function load_possible_default_values_for_inputs()
    {
        $coop_form_info = $this->get_coop_main_form_data();
        $name = isset($coop_form_info->name_of_proposed_society) ? $coop_form_info->name_of_proposed_society : null;

        if ($this->all_coop_db_table_fields_filled() || $name) {
            $this->get_db_form_info_and_use_as_default_val();
        }
    }

    public function can_edit_cooperative_form()
    {
        $existing_coop_info = $this->get_coop_main_form_data();
        return isset($existing_coop_info->admin_approved) && $existing_coop_info->admin_approved !="Approved" ? "Can Edit" : false;
    }

    protected function get_db_form_info_and_use_as_default_val()
    {
        $db_info_to_get=$this->get_coop_main_form_data();
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
        $invoice_info = $this->get_invoice_info_from_db();
        $main_form = $this->get_coop_main_form_data();
        if (isset($_REQUEST['offline_to_online']) && $_REQUEST['offline_to_online']==1) {
            $form_values["name_of_proposed_society"] = $main_form->name_of_proposed_society;
        }
        if (!isset($main_form->approved_to_exist) || (isset($main_form->approved_to_exist) && $main_form->approved_to_exist==0)) {
            $form_values["name_of_approved_society"] = '';
        }
        $form_values["invoice_number_filled_against"] = $invoice_info->invoice_number;
        $form_values["request_ref_filled_against"] = $invoice_info->request_reference;
        $form_values["user_id"] = get_current_user_id();
        $form_values["start_edits"] = "Start";

        $task_performer->add_custom_fields_to_post(
            $invoice_info->invoice_number, 'mtii_cbs_invoice',
            array('connected_org' => $form_values["name_of_proposed_society"])
        );
        if ($main_form) {
            $main_form_id = $main_form->application_form_id;
            $inserted_row_id = $mtii_db_coop_main_form->update($main_form_id, $form_values);
        } else {
            $inserted_row_id = $mtii_db_coop_main_form->insert($form_values);
        }
        $this->check_db_insertion_success($inserted_row_id);
    }

    public function get_upload_and_reg_success()
    {
        return $this->_records_addition_status;
    }

    public function mtii_save_reg_form_info()
    {
        if (isset($_POST["mtii_form_submit"])
            && ((isset($_POST['main_registration_nonce']) && wp_verify_nonce($_POST['main_registration_nonce'], 'main-registration-nonce'))
            || (isset($_POST['signatories_template_nonce']) && wp_verify_nonce($_POST['signatories_template_nonce'], 'signatories-template-nonce')))
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