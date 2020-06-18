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
class NgoAndCboRegistration extends RegistrationUtilities
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
    protected $reg_form_type = "ngo_and_cbo";


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
    protected $lga_select_name = "lga_of_proposed_organization";

    /**
     * An array of all input_names
     *
     * @var array
     */
    protected $all_input_names =  array (
        'name_of_proposed_organization', 'date_of_establishment',
        'address_of_proposed_organization', 'lga_of_proposed_organization', 'area_of_operation',
        'area_of_operation_other', 'specific_objectives_of_organization', 'donor_support_agency',
        'proposed_project', 'name_of_proposed_banker', 'name_of_coordinator', 'number_of_coordinator',
        'name_of_assistant_coordinator', 'number_of_assistant_coordinator', 'name_of_secretary',
        'number_of_secretary', 'brief_description_of_activity', 'name_of_attester'
    );
    /**
     * An array of all options for the position select inputs
     *
     * @var array
     */
    protected $options_for_select_inputs = array (
        "lga_of_proposed_organization"   => array(),
        "area_of_operation"              => array(
            "Education", "Health", "Social Services", "Faith Based", "Community Development",
            "Financial Aid", "Agriculture", "Vocational and Skill Acquisition", "Others (Specify Below)"
        ),
        // "nature_of_business"            => array (
        //     "Medical & Hospitality", "Energy,Oil & Gas", "Automobile & BuildingMaterial",
        //     "Academics Institution", "Financial Institution", "Wholesale and Retail Business",
        //     "Café", "Eatery & Fast Food Center", "Garments & Fashion Design", "Soft Drinks & Water Processing",
        //     "Agro-Allied Business", "Business Ceter & Secretarial Services", "Workshops & Garage",
        //     "Cinematography", "Communication & Allied Business", "Construction", "Extraction & Allied Business"
        // ),
        "name_of_proposed_banker"     =>  array(
            "ACCESS BANK PLC", "CITIBANK NIGERIA LIMITED", "ECOBANK NIGERIA PLC", "FIDELITY BANK PLC",
            "FIRST BANK NIGERIA LIMITED", "FIRST CITY MONUMENT BANK PLC", "GLOBUS BANK LIMITED",
            "GUARANTY TRUST BANK PLC", "HERITAGE BANKING COMPANY LTD.", "KEY STONE BANK", "POLARIS BANK", "PROVIDUS BANK",
            "STANBIC IBTC BANK LTD", "STANDARD CHARTERED BANK NIGERIA LTD.", "STERLING BANK PLC",
            "SUNTRUST BANK NIGERIA LIMITED", "TITAN TRUST BANK LTD", "UNION BANK OF NIGERIA PLC",
            "UNITED BANK FOR AFRICA PLC", "UNITY  BANK PLC", "WEMA BANK PLC",  "ZENITH BANK PLC",
        ),
        // "is_premise_rented"             => array("Yes", "No"),
        // 'month_of_declaration'          => array(
        //         "January", "February", "March", "April", "May", "June",
        //         "July", "August", "Spetember", "October", "November", "December"
        // ),
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
        //$this->add_select_inputs_options();
        foreach ($this->all_input_names as $input_name) {
            $this->all_input_names_as_assoc[$input_name] = ucwords(str_replace("_", " ", $input_name));
        }

        parent::__construct();
    }

     protected function load_possible_default_values_for_inputs()
    {
        $ngo_form_info = $this->get_ngo_cbo_form_data();
        $name = isset($ngo_form_info->name_of_proposed_organization)
            ? $ngo_form_info->name_of_proposed_organization : null;

        if ($this->all_ngo_db_table_fields_filled() || $name) {
            $this->get_db_form_info_and_use_as_default_val();
        }
    }

    public function registration_approved()
    {
        $ngo_info = $this->get_ngo_cbo_form_data();
        if ($ngo_info->is_admin_approved==="Approved") {
            return true;
        } else {
            return false;
        }
    }

    // private function add_select_inputs_options()
    // {
    //     for ($i=1; $i<32; $i++) :
    //         $this->options_for_select_inputs["day_of_declaration"] = $i;
    //     endfor;
    // }

    public function can_edit_cooperative_form() {
        $existing_coop_info = $this->get_coop_main_form_data();
        return isset($existing_coop_info->admin_approved) && $existing_coop_info->admin_approved !="Approved" ? "Can Edit" : false;
    }

    protected function get_db_form_info_and_use_as_default_val()
    {
        $db_info_to_get=$this->get_ngo_cbo_form_data();
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
        if ( ($input_name!="donor_support_agency" && $input_name!="proposed_project" && $input_name!="area_of_operation"
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
        global $mtii_ngo_cbo_db_table;
        global $wpdb;
        $tasks_performer = new TasksPerformer;
        $this->get_form_data_as_assoc();
        $form_values = $this->form_values;
        $error_output = '';
        $invoice_info = $this->get_invoice_info_from_db();
        $ngo_cbo_form = $this->get_ngo_cbo_form_data();
        //isset($_REQUEST['offline_to_online']) && $_REQUEST['offline_to_online']==1
        if (!$this->all_ngo_db_table_fields_filled()) {
            $form_values["name_of_proposed_organization"] = $main_form->name_of_proposed_organization;
        }
        if (!isset($main_form->approved_to_exist) || (isset($main_form->approved_to_exist) && $main_form->approved_to_exist==0)) {
            $form_values["name_of_approved_organization"] = null;
        }
        $form_values["invoice_number_filled_against"] = $invoice_info->invoice_number;
        $form_values["request_ref_filled_against"] = $invoice_info->request_reference;
        $form_values["user_id"] = get_current_user_id();
        $form_values["is_admin_approved"] = "Awaiting Approval";
        date_default_timezone_set("Africa/Lagos");
        $form_values["date_of_attestation"] = date("Y:m:d, h:i:s A");
        $tasks_performer->add_custom_fields_to_post(
            $invoice_info->invoice_number, 'mtii_cbs_invoice',
            array('connected_org' => $form_values["name_of_proposed_organization"])
        );
        if ($ngo_cbo_form) {
            $ngo_cbo_form_id = $ngo_cbo_form->application_form_id;
            $inserted_row_id = $mtii_ngo_cbo_db_table->update($ngo_cbo_form_id, $form_values);
        } else {
            $inserted_row_id = $mtii_ngo_cbo_db_table->insert($form_values);
        }
        $this->create_registration_custom_post($form_values["name_of_proposed_organization"], 'mtii_ngo_lists');
        $this->check_db_insertion_success($inserted_row_id);
    }

    public function get_upload_and_reg_success()
    {
        return $this->_records_addition_status;
    }

    public function mtii_save_reg_form_info()
    {
        if (isset($_POST["mtii_form_submit"]) && ((isset($_POST['main_registration_nonce'])
            && wp_verify_nonce($_POST['main_registration_nonce'], 'main-registration-nonce')))
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