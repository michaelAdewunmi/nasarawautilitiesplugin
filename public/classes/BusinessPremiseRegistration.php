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
class BusinessPremiseRegistration extends RegistrationUtilities
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
    protected $reg_form_type = "business_premise";


    /**
     * A handler to determine if there is need to populate the select options for LGA and ward
     *
     * @var boolean
     */
    protected $populate_lga_and_ward_options = true;


    /**
     * An array of some input names and their defined errors.
     *
     * @var array
     */
    protected $_defined_input_errors = array();



    /**
     * A handler to hold the name property of the select Input
     *
     * @var boolean
     */
    protected $lga_select_name = "lga_of_company";

    /**
     * An array of all input_names
     *
     * @var array
     */
    protected $all_input_names =  array (
        'name_of_company', 'date_of_registration', 'nature_of_business', 'time_of_declaration',
        'address_of_premise', 'lga_of_company', 'director_one_name', 'director_one_number', 'director_two_name',
        'director_two_number', 'director_three_name', 'director_three_number', 'director_four_name',
        'director_four_number', 'director_five_name', 'director_five_number', 'annual_turnover', 'is_premise_rented',
        'name_of_landlord', 'address_of_landlord', 'day_of_declaration', 'month_of_declaration',
        'year_of_declaration', 'name_of_declarator', 'position_of_declarator'
    );

    /**
     * An array of all options for the position select inputs
     *
     * @var array
     */
    protected $options_for_select_inputs = array (
        "lga_of_company"   => array(),
        "nature_of_business"            => array (
            "Medical & Hospitality", "Energy,Oil & Gas", "Automobile & BuildingMaterial",
            "Academics Institution", "Financial Institution", "Wholesale and Retail Business",
            "Café", "Eatery & Fast Food Center", "Garments & Fashion Design", "Soft Drinks & Water Processing",
            "Agro-Allied Business", "Business Ceter & Secretarial Services", "Workshops & Garage",
            "Cinematography", "Communication & Allied Business", "Construction", "Extraction & Allied Business"
        ),
        "name_of_proposed_banker"     =>  array(
            "ACCESS BANK PLC", "CITIBANK NIGERIA LIMITED", "ECOBANK NIGERIA PLC", "FIDELITY BANK PLC",
            "FIRST BANK NIGERIA LIMITED", "FIRST CITY MONUMENT BANK PLC", "GLOBUS BANK LIMITED",
            "GUARANTY TRUST BANK PLC", "HERITAGE BANKING COMPANY LTD.", "KEY STONE BANK", "POLARIS BANK", "PROVIDUS BANK",
            "STANBIC IBTC BANK LTD", "STANDARD CHARTERED BANK NIGERIA LTD.", "STERLING BANK PLC",
            "SUNTRUST BANK NIGERIA LIMITED", "TITAN TRUST BANK LTD", "UNION BANK OF NIGERIA PLC",
            "UNITED BANK FOR AFRICA PLC", "UNITY  BANK PLC", "WEMA BANK PLC",  "ZENITH BANK PLC",
        ),
        "is_premise_rented"             => array("Yes", "No"),
        'month_of_declaration'          => array(
                "January", "February", "March", "April", "May", "June",
                "July", "August", "Spetember", "October", "November", "December"
        ),
        'year_of_declaration'            => array("2020", "2021", "2022", "2023", "2024", "2025"),
        'position_of_declarator'         => array(
            "CEO", "COO", "Manager", "General Manager", "Managing Director", "Company Secretary", "Shareholder"
        )
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
        $this->add_select_inputs_options();
        foreach ($this->all_input_names as $input_name) {
            $this->all_input_names_as_assoc[$input_name] = ucwords(str_replace("_", " ", $input_name));
        }

        parent::__construct();
    }

    protected function load_possible_default_values_for_inputs()
    {
        $this->get_db_form_info_and_use_as_default_val();
    }

    public function registration_approved()
    {
        $ngo_info = $this->get_biz_prem_form_data();
        if ($ngo_info->is_admin_approved==="Approved") {
            return true;
        } else {
            return false;
        }
    }

    private function add_select_inputs_options()
    {
        $days = array();
        for ($i=1; $i<32; $i++) :
            $days[] = $i;
        endfor;
        $this->options_for_select_inputs["day_of_declaration"] = $days;
    }

    public function can_edit_biz_prem_form() {
        $existing_biz_prem_info = $this->get_biz_prem_form_data();
        return isset($existing_biz_prem_info->is_admin_approved)
            && $existing_biz_prem_info->is_admin_approved !="Approved" ? "Can Edit" : false;
    }

    protected function get_db_form_info_and_use_as_default_val()
    {
        $db_info_to_get=$this->get_biz_prem_form_data();
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
        if ($input_name!="is_premise_rented" && $input_name!="name_of_landlord" && $input_name!="time_of_declaration"
            && $input_name!="address_of_landlord" && (!$input_val || trim($input_val)=="")
        ) {
            $error_id = $input_name;
            $error_output = ucfirst($this->form_nice_names[$error_id])." cannot be blank";
            $this->add_to_wp_global_error($error_id, $error_output);
            $this->errored_inputs_classes[$input_name] = 'errored';
        }
        if ($input_name = "is_premise_rented") {
            if ($input_val=="Yes") {
                if (trim($_POST["name_of_landlord"])=="") {
                    $error_output1 = 'You have to specify the Name of the landlord';
                    $this->add_to_wp_global_error('name_of_landlord', $error_output1);
                    $this->errored_inputs_classes['name_of_landlord'] = 'errored';
                    $this->_defined_input_errors['name_of_landlord'] = "You did not specify the Landlord's Name in 8a";
                } else {
                    $this->errored_inputs_classes['name_of_landlord'] = '';
                    unset($this->_defined_input_errors['address_of_landlord']);
                }

                if (trim($_POST["address_of_landlord"])=="") {
                    $error_output1 = 'You have to specify the Name of the landlord';
                    $this->add_to_wp_global_error('address_of_landlord', $error_output1);
                    $this->errored_inputs_classes['address_of_landlord'] = 'errored';
                    $this->_defined_input_errors['address_of_landlord'] = "You did not specify the Landlord's Address in 8b";
                } else {
                    $this->errored_inputs_classes['address_of_landlord'] = '';
                    unset($this->_defined_input_errors['address_of_landlord']);
                }
            }
        }
    }

    /**
     * Add all inputs into the database
     *
     * @return void
     */
    protected function add_all_info_to_db()
    {
        global $mtii_biz_prem_db_main;
        global $wpdb;
        $tasks_performer = new TasksPerformer;
        $this->get_form_data_as_assoc();
        $form_values = $this->form_values;
        $error_output = '';
        $invoice_info = $this->get_invoice_info_from_db();
        $biz_prem_form = $this->get_biz_prem_form_data();

        $form_values["invoice_number_filled_against"] = $invoice_info->invoice_number;
        $form_values["request_ref_filled_against"] = $invoice_info->request_reference;
        $form_values["user_id"] = get_current_user_id();
        $form_values["is_admin_approved"] = "Awaiting Approval";
        date_default_timezone_set("Africa/Lagos");
        $form_values["time_of_declaration"] = date("h:i:s A");
        $form_values["day_of_declaration"] = date("j");
        $form_values["month_of_declaration"] = date("F");
        $form_values["year_of_declaration"] = date("Y");

        $tasks_performer->add_custom_fields_to_post(
            $invoice_info->invoice_number, 'mtii_cbs_invoice',
            array('connected_org' => $form_values["name_of_company"])
        );
        if ($biz_prem_form) {
            $biz_premise_form_id = $biz_prem_form->application_form_id;
            $inserted_row_id = $mtii_biz_prem_db_main->update($biz_premise_form_id, $form_values);
        } else {
            $inserted_row_id = $mtii_biz_prem_db_main->insert($form_values);
        }
        $this->create_registration_custom_post($form_values["name_of_company"], 'mtii_biz_prem_reg');
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