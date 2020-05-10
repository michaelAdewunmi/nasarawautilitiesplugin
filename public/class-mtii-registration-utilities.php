<?php
require_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-utilities-task-performer.php';
require_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-parameters-setter-and-getters.php';
namespace MtiiUtilities;

class Mtii_Registration_Utilities
{
    /**
     * Hold the global variable safely for error holding
     */
    static $wp_error;

    /**
     * An object-like array to hold a properity for each errored input name
     * which is passed to the form to show that there is an error in the input
     *
     * @var array
     */
    public $errored_inputs_classes = array ();

    /**
     * A variable to hold a boolean value to tell if the info was successfully added to the Database.
     *
     * @var bool
     */
    private $_records_successfully_added = false;

    /**
     * An object-like array to hold a properity for each input name and it's placeholder
     *
     * @var array
     */
    public $input_values_placeholder = array();

    /**
     * An array of all input names present from the form
     *
     * @var array
     */
    private $_all_form_input_names;

    /**
     * A boolean to tell if template is signatory template
     *
     * @var array
     */
    private $_is_signatory_template;

    /**
     * An array of all input names present in their nicely written form
     *
     * @var array
     */
    private $_form_nice_names;

    /**
     * An array of all input names and their defined errors.
     *
     * @var array
     */
    private $_defined_input_errors = array();

    /**
     * An string to hold the kind of registration form
     *
     * @var string
     */
    private $_reg_form_type;


    /**
     * An string to tell if any number for selecr inputs needs to be an ordinal number prefixed
     * with th, or nd, rd i.e 1st, 2nd, 3rd, e.t.c;
     *
     * @var string
     */
    private $make_input_ordinal = null;


    /**
     * An object-like array of all form values
     *
     * @var array
     */
    public $form_values = array();

    /**
     * An html output of all errors
     *
     * @var array
     */
    public $error_output_all = null;

    public function delete_main_form() {
        $main = $this->get_coop_main_form_data();
        global $mtii_db_coop_main_form;
        $mtii_db_coop_main_form->delete($main->application_form_id);
    }

    public function delete_signatories_form() {
        $signatories_data = $this->get_signatories_data();
        global $mtii_signatories_template_db;
        $mtii_signatories_template_db->delete($signatories_data->application_form_id);
    }

    public function ordinal($number) {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13))
            return $number. 'th';
        else
            return $number. $ends[$number % 10];
    }

    private function _create_select_input($options, $instruction, $input_name, $id=null, $make_ordinal=false)
    {
        $this->make_input_ordinal = $make_ordinal;

        if ($this->make_input_ordinal && $this->input_values_placeholder[$input_name]!='') {
            $ordinal_val_if_number = $this->ordinal($this->input_values_placeholder[$input_name]);
        } else {
            $ordinal_val_if_number = $this->input_values_placeholder[$input_name];
        }

        $options = is_array($options) ? $options : array();
        $add_id = '';
        if ($id) {
            $add_id='id="'.$id.'" ';
        }
        $select = '<select '.$add_id.' class="mtii-inline-input main-form mtii-select '.$this->errored_inputs_classes[$input_name].'" name="'.$input_name.'">';
        $select .= $this->input_values_placeholder[$input_name]!='' ? '<option value="'.$this->input_values_placeholder[$input_name].
                    '">'.$ordinal_val_if_number.'</option>' : '<option value="">'.$instruction.'</option>';
        $select .= join(
            "",
            array_map(
                function ($option) {
                    if(is_array($option)) {
                        return '<option '.$option["option_style"].' value="'.$option["option"].'" '.$option["disabled"].'>'.$option["option"].'</option>';
                    } else {
                        if ( $this->make_input_ordinal) {
                            return '<option value="'.$option.'">'.$this->ordinal($option).'</option>';
                        } else {
                            return '<option value="'.$option.'">'.$option.'</option>';
                        }
                    }
                },
                $options
            )
        );
        $select .= "</select>";
        return $select;
    }

    /**
     * Mtii_Registration_Utilities Construct
     */
    public function __construct($form_input_names=null, $form_nice_names_as_assoc=null, $reg_form_type=null, $is_signatory_template=null)
    {
        $task_performer = new Mtii_Utilities_Tasks_Performer;
        $task_performer->set_a_cookie();
        if ($form_input_names!=null && $form_nice_names_as_assoc!=null && $reg_form_type!=null) {
            $this->_reg_form_type = $reg_form_type;
            $this->_set_input_values_placeholder($form_input_names);
            $this->_set_errored_input_classes_default($form_input_names);
            $this->_set_form_input_names_as_array($form_input_names);
            $this->set_form_input_nice_names_as_assoc_array($form_nice_names_as_assoc);
            $this->mtii_save_reg_form_info();
            $this->get_db_form_info_and_use_as_default_val();
        }

        if ($is_signatory_template==="true") {
            $this->_is_signatory_template = true;
        }
    }

    public function show_status_bar($show_fully_completed=false, $is_mainform=false, $is_signatories=false)
    {
        if ($is_mainform) {
            $colored_bar_extra_class = 'none-done'; $first_circle_extra_class = ''; $first_stage_extra_class = 'awaiting-top';
            $first_completion_text = 'Awaiting Completion'; $second_completion_text = 'Not Started';
        }
        if ($is_mainform || $is_signatories) {
            $second_circle_extra_class = ''; $second_stage_extra_class = 'awaiting-top';
            $first_completed_extra_class = 'awaiting-bottom'; $second_completed_extra_class = 'awaiting-bottom';
            $final_circle_extra_class = ''; $final_stage_extra_class = 'awaiting-top'; $final_completed_extra_class = 'awaiting-bottom';
            $final_completion_text = 'No Uploads yet';
        }

        if ($is_signatories || $show_fully_completed || $this->get_coop_main_form_data()) {
            $colored_bar_extra_class = 'first-done'; $first_circle_extra_class = 'done'; $first_stage_extra_class = '';
            $first_completion_text = 'Completed'; $first_completed_extra_class = '';
        }

        if ($is_signatories) {
            $invoice_info = $this->get_invoice_info_from_db();
            $coop_main_form = $this->get_signatories_data();
            if (!$this->get_signatories_data()) {
                $second_completion_text = 'Awaiting Completion';
            } else {
                $second_completion_text = 'Completed';
                $second_stage_extra_class = '';
                $second_completed_extra_class = '';
            }
            $second_circle_extra_class = 'done';
            $colored_bar_extra_class = 'second-done';
        }

        if ($show_fully_completed) {
            $colored_bar_extra_class = 'third-done'; $second_circle_extra_class = 'done'; $second_stage_extra_class = '';
            $second_completion_text = 'Completed'; $second_completed_extra_class = '';
            $final_circle_extra_class = 'done'; $final_stage_extra_class = ''; $final_completed_extra_class = '';
            $final_completion_text = 'Uploads Approved';
        }

        return '<div class="status">'.
            '<div class="bar">'.
                '<div class="colored-bar active '.$colored_bar_extra_class.'"></div>'.
            '</div>'.
            '<div class="circle '.$first_circle_extra_class.'">'.
                '<span class="stage '.$first_stage_extra_class.'">Stage 1</span>'.
                '<span class="completed '.$first_completed_extra_class.'">'.$first_completion_text.'</span>'.
            '</div>'.
            '<div class="circle two '.$second_circle_extra_class.'">'.
                '<span class="stage '.$second_stage_extra_class.'">Stage 2</span>'.
                '<span class="completed '.$second_completed_extra_class.'">'.$second_completion_text.'</span>'.
            '</div>'.
            '<div class="circle three '.$final_circle_extra_class.'">'.
                '<span class="stage '.$final_stage_extra_class.'">Approval</span>'.
                '<span class="completed '.$final_completed_extra_class.'">'.$final_completion_text.'</span>'.
            '</div>'.
        '</div>';
    }

    public function get_input_or_placeholder_text($input_name, $type, $placeholder=null, $is_signatories_lga=false, $make_ordinal=false)
    {
        if ($make_ordinal && $this->input_values_placeholder[$input_name]=='') {
            $ordinal_val_if_necessary = $this->ordinal($this->input_values_placeholder[$input_name]);
        } else {
            $ordinal_val_if_necessary = $this->input_values_placeholder[$input_name];
        }

        if (isset($_REQUEST["is_preview"]) && !isset($_REQUEST["for_edit"])) {
            if ($_REQUEST["is_preview"]==openssl_encrypt("is_preview", "AES-128-ECB", "SECRET")) {
                if (isset($_REQUEST["for_edit"]) && $_REQUEST["for_edit"]==1) {
                    echo '<input class="mtii-inline-input main-form '.$this->errored_inputs_classes[$input_name].
                        '" name="'.$input_name.'" type="'.$type.'" value="'.$this->input_values_placeholder[$input_name].
                        '" />';
                } else {
                    if ($input_name=="time_of_declaration") {
                        return '<span class="as-placeholder">'.$ordinal_val_if_necessary.'</span>';
                    } else {
                        echo '<span class="as-placeholder">'.$ordinal_val_if_necessary.'</span>';
                    }
                }
            } else {
                echo "<h2>There was a problem</h2>";
            }
        } else {
            if ($input_name=='nature_of_coop_society') {
                $options = array("Liability Limited", "Liability Unlimited");
                echo $this->_create_select_input($options, "Select Ward", $input_name);
            } else if ( $input_name=='ward_of_proposed_society') {
                $add_id = 'all-wards';
                $lga_and_wards = new Mtii_Parameters_Setter_And_Getter;
                $wards = $lga_and_wards->get_all_wards();
                $options = array();
                foreach ($wards as $key => $value) {
                    if ($value=="is_lga_parent") {
                        $options[] = array (
                            "option"        => str_replace("_", " ", $key),
                            "option_style"  => 'style="background-color: #cfcfcf; color: #fff"',
                            "disabled"      => 'disabled'
                        );
                    } else {
                        $options[] = str_replace("_", " ", $key);
                    }
                }
                echo $this->_create_select_input($options, "Select Ward", $input_name, $add_id);
            } else if ($input_name=='lga_of_proposed_society' || $input_name=='lga_of_company' || $is_signatories_lga) {
                $add_id = !$is_signatories_lga ? 'lga-list' : '';
                $lga_and_wards = new Mtii_Parameters_Setter_And_Getter;
                $lga = $lga_and_wards->get_all_lga();
                $options = array();
                foreach ($lga as $key => $value) : $options[] = str_replace("_", " ", $key);
                endforeach;
                echo $this->_create_select_input($options, "Select Local Government Area", $input_name, $add_id);
            } else if ($input_name=='area_of_operation') {
                $options = array(
                    "Agriculture", "Marketing", "Mining", "Thrift and Loan", "Insurance",
                    "Estate Development", "Others (Specify Below)"
                );
                echo $this->_create_select_input($options, "Select an Area of Operation", $input_name);
            } else if ($input_name=='nature_of_proposed_banker') {
                $options = array(
                    "ACCESS BANK PLC", "CITIBANK NIGERIA LIMITED", "ECOBANK NIGERIA PLC", "FIDELITY BANK PLC", "GLOBUS BANK LIMITED", "GUARANTY TRUST BANK PLC",
                    "HERITAGE BANKING COMPANY LTD.", "KEY STONE BANK", "PROVIDUS BANK", "STANBIC IBTC BANK LTD", "STANDARD CHARTERED BANK NIGERIA LTD.", "STERLING BANK PLC",
                    "SUNTRUST BANK NIGERIA LIMITED", "TITAN TRUST BANK LTD", "UNION BANK OF NIGERIA PLC", "UNITED BANK FOR AFRICA PLC", "UNITY  BANK PLC", "WEMA BANK PLC",
                    "ZENITH BANK PLC"
                );
                echo $this->_create_select_input($options, "Select Proposed Bank", $input_name);
            } else if ($input_name=='nature_of_business') {
                $options = array (
                    "Medical & Hospitality", "Energy,Oil & Gas", "Automobile & BuildingMaterial",
                    "Academics Institution", "Financial Institution", "Wholesale and Retail Business",
                    "Café", "Eatry & Fast Food Center", "Garments & Fashion Design", "Soft Drinks & Water Processing",
                    "Agro-Allied Business", "Business Ceter & Secretarial Services", "Workshops & Garage",
                    "Cinematography", "Communication & Allied Business", "Construction", "Extraction & Allied Business"
                );
                echo $this->_create_select_input($options, "Select Proposed Bank", $input_name);
            } else if ($input_name=='is_premise_rented') {
                $options = array ( "Yes", "No" );
                echo $this->_create_select_input($options, "Is Premise a Rented Property?", $input_name, 'is-premise-rented');

            } else if ($input_name=='day_of_declaration') {
                $options = array();
                for ($i=1; $i<32; $i++) : $options[] = $i;
                endfor;
                echo $this->_create_select_input($options, "Day of Declaration?", $input_name, null, true);

            } else if ($input_name=='month_of_declaration') {
                $options = array(
                    "January", "February", "March", "April", "May", "June",
                    "July", "August", "Spetember", "October", "November", "December"
                );
                echo $this->_create_select_input($options, "Day of Declaration?", $input_name);
            }  else if ($input_name=='year_of_declaration') {
                $options = array("2020", "2021", "2022", "2023", "2024", "2025");
                echo $this->_create_select_input($options, "Year of Declaration?", $input_name);
            } else {
                if ($placeholder!=null) {
                    echo '<input class="mtii-inline-input main-form '.$this->errored_inputs_classes[$input_name].
                        '" name="'.$input_name.'" type="'.$type.'" value="'.$this->input_values_placeholder[$input_name].
                    '" placeholder="'.$placeholder.'" />';
                } else {
                    echo '<input class="mtii-inline-input main-form '.$this->errored_inputs_classes[$input_name].
                    '" name="'.$input_name.'" type="'.$type.'" value="'.$this->input_values_placeholder[$input_name].
                    '" />';
                }
            }
        }
    }

    private function  get_db_form_info_and_use_as_default_val()
    {
        if (isset($_REQUEST["is_preview"]) && $_REQUEST["is_preview"]==openssl_encrypt("is_preview", "AES-128-ECB", "SECRET")) {
            if (isset($_REQUEST["for_dcs"]) && $_REQUEST["for_dcs"]==1) {
                $db_info_to_get = $this->get_dcs_form_info_from_invoice();
            } else if (isset($_REQUEST["for_main"]) && $_REQUEST["for_main"]==1) {
                $db_info_to_get = $this->get_coop_main_form_data();
            } else if (isset($_REQUEST["for_signatories_template"]) && $_REQUEST["for_signatories_template"]==1) {
                $db_info_to_get = $this->get_signatories_data();
            } else if (isset($_REQUEST["for_biz_prem"]) && $_REQUEST["for_biz_prem"]==1) {
                $db_info_to_get = $this->get_biz_prem_form_data();
            }
            if (!$db_info_to_get) {
                echo die('<script>window.location.href="'.site_url("/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D").'"</script>');
            }
            foreach ($this->_all_form_input_names as $input_name) {
                $this->input_values_placeholder[$input_name] = isset($db_info_to_get->$input_name) ? $db_info_to_get->$input_name : '';
            }
        }
    }

    /**
     * Get all inputs available in form by their name property and
     * turn them into an array
     *
     */
    private function _set_input_values_placeholder( $form_input_names )
    {
        //$inputs_names_array = $this->_all_form_input_names;
        foreach ($form_input_names as $input_name) {
            $this->input_values_placeholder[$input_name] = isset($_POST[$input_name]) ? $_POST[$input_name] : '';
        }
    }

    private function _set_errored_input_classes_default($form_input_names)
    {
        foreach ($form_input_names as $input_name) {
            $this->errored_inputs_classes[$input_name] = '';
        }
    }

    /**
     * Get all inputs available in form by their name property and
     * turn them into an array
     *
     * @return $inputs_names_array
     */
    private function _set_form_input_names_as_array($form_input_names)
    {
        $this->_all_form_input_names = $form_input_names;
    }

    /**
     * Set the value to the variable that determines if info was successflly added to the DB
     *
     */
    private function _set_added_records_success()
    {
        $this->_records_successfully_added = true;
    }

    /**
     * Set the value to the variable that determines if info was successflly added to the DB
     *
     */
    public function records_successfully_added()
    {
        return $this->_records_successfully_added;
    }


    /**
     * Get saved invoice from the cookie and use the information to get cookie value from the database
     *
     */
    public function get_invoice_info_from_db()
    {
        global $mtii_db_invoice;
        $inv = isset($_COOKIE["mtii_payment_invoice"]) ? $_COOKIE["mtii_payment_invoice"] : null;
        $value = openssl_decrypt($inv, "AES-128-ECB", "secretecode");
        $decoded_val = json_decode($value, true);
        $invoice_number = $decoded_val["invoice_number"];
        $request_reference = $decoded_val["request_reference"];
        return $mtii_db_invoice->get_row_by_data($invoice_number, $request_reference);
    }

    /**
     * Get saved invoice from the cookie and use the information to get dcs info
     * from database
     */
    public function get_dcs_form_info_from_invoice()
    {
        global $mtii_db_coop_reg;
        $inv = isset($_COOKIE["mtii_payment_invoice"]) ? $_COOKIE["mtii_payment_invoice"] : null;
        $value = openssl_decrypt($inv, "AES-128-ECB", "secretecode");
        $decoded_val = json_decode($value, true);
        $invoice_number = $decoded_val["invoice_number"];
        $request_reference = $decoded_val["request_reference"];
        return $mtii_db_coop_reg->get_row_by_data($invoice_number, $request_reference);
    }

    /**
     * Get saved invoice from the cookie and use the information to get business premise info
     * from database
     */
    public function get_biz_prem_form_data()
    {
        global $mtii_biz_prem_db_main;
        $invoice_info = $this->get_invoice_info_from_db();
        return $mtii_biz_prem_db_main->get_row_by_data($invoice_info->invoice_number, $invoice_info->request_reference);
    }

    /**
     * Get saved invoice from the cookie and use the information to get cookie value from the database
     *
     */
    public function check_if_invoice_has_signed_documents($invoice_number)
    {
        $invoice_info = $this->get_invoice_info_from_db();
        if ($invoice_info->invoice_number==$invoice_number) {
            $existing_doc = get_page_by_title($invoice_number, OBJECT, 'mtii_signed_uploads');
            if ($existing_doc) {
                $admin_approved = get_post_meta($existing_doc->ID, 'admin_approved', true);
                if ($admin_approved == 'true') {
                    return true;
                } else if ($admin_approved == 'Awaiting Approval') {
                    return 'Awaiting Approval';
                } else {
                    return false;
                }
            } else {
                return "No Signed Upload";
            }
        }
    }

    /**
     * Get saved invoice from the cookie and use the information to get cookie value from the database
     *
     */
    public function get_coop_main_form_data()
    {
        global $mtii_db_coop_main_form;
        $invoice_info = $this->get_invoice_info_from_db();
        return $mtii_db_coop_main_form->get_row_by_data($invoice_info->invoice_number, $invoice_info->request_reference);
    }


    /**
     * Get saved invoice from the cookie and use the information to get cookie value
     * from database and get the data for the signatories template.
     *
     */
    public function get_signatories_data()
    {
        global $mtii_signatories_template_db;
        $main_form_info = $this->get_coop_main_form_data();
        return $mtii_signatories_template_db->get_by('main_coop_form_id', $main_form_info->application_form_id);
    }

    /**
     * Get all inputs available in form by their name property and
     * turn them into an array
     *
     * @return $inputs_names_array
     */
    private function set_form_input_nice_names_as_assoc_array($form_nice_names_as_assoc)
    {
        $this->_form_nice_names = $form_nice_names_as_assoc;
    }

    /**
     * Get all inputs available in form by their name property and
     * turn them into an array
     *
     * @return $inputs_names_array
     */
    private function _get_form_data_as_assoc()
    {

        $inputs_names_array = $this->_all_form_input_names;
        foreach ($inputs_names_array as $input_name) {
            if (isset($_REQUEST["is_preview"]) && $_REQUEST["is_preview"]==openssl_encrypt("is_preview", "AES-128-ECB", "SECRET")) {
                $this->get_db_form_info_and_use_as_default_val();
            }
            if ($input_name=="name_of_proposed_society") {
                $words_to_strip = array("cooperative", "society", "cooperatives", "societies");
                if (isset($_POST[$input_name])) {
                    $input_val = $this->strip_words_from_string($words_to_strip, $_POST[$input_name]);
                    $this->form_values[$input_name] = trim($input_val);
                } else {
                    $this->form_values[$input_name] = "";
                }
            } else if ($input_name=="area_of_operation") {
                if ($_POST[$input_name]=="Others (Specify Below)") {
                    $this->form_values[$input_name] = 'Others - '.trim($_POST["area_of_operation_other"]);
                }
            } else {
                $this->form_values[$input_name] = isset($_POST[$input_name]) ? trim($_POST[$input_name]) : "";
            }
        }
        return $inputs_names_array;
    }



    /**
     * Description - Track and return any Error Message from form
     *
     * @return $wp_error
     */
    public function form_errors()
    {
        return isset(self::$wp_error) ? self::$wp_error : (self::$wp_error = new WP_Error(null, null, null));
    }

    /**
     * Description - Add to the WP Error object
     *
     * @param string $error_id     this is the id which the error will be saved with
     * @param string $error_output this is the error itself that will be displayed in the frontend
     *
     * @return null
     */
    public function add_to_wp_global_error($error_id, $error_output)
    {
        $this->form_errors()->add($error_id, $error_output);

    }

    /**
     * Check if there are form errors on form submit
     *
     * @return bool
     */
    public function no_form_errors()
    {
        return empty($this->form_errors()->get_error_messages()) ? true : false;
    }

    /**
     * Description - Get all the Error Messages from the form
     *
     * @return null | $error_output;
     */
    public function get_all_form_errors()
    {
        $form_error_codes = $this->form_errors()->get_error_codes();

        if ($form_error_codes) {
            $error_output = '';
            $error_output.= '<div class="section-body">';
            $error_output.= '<h2 class="section-heading errored-text">Error!</h2>';
            $error_output.= '<hr class="header-lower-rule errored-bg" />';
            $error_output.= '<div class="payment-err">';
            $error_output.= '<div class="notification-wrapper">';
            $error_output.= '<div class="mtii_reg_errors"><h2 style="color: red; font-size: 36px;">';
            $error_output.= 'Please Fix the inputs highlighted in red...</h2>';
            if (count($this->_defined_input_errors)>0) {
                $error_output.= '<h2 style="font-size: 28px; color: red">And Note the following Errors!</h2>';
                foreach ($this->_defined_input_errors as $key => $value) {
                    $error_output .= '<span style="color: red; font-size: 20px; margin-top: 10px; display: block;">'.$value.'</span><br/>';
                }
            }
            $error_output .= '</div>';
            $error_output .= '</div>';
            $error_output .= '</div>';
            $error_output .= '</div>';
            $this->error_output_all = $error_output;
            return $error_output;
        }
    }

    public function strip_words_from_string($word_list=array(), $string='')
    {
        if (is_array($word_list)) {
            foreach ($word_list as &$word) {
                $word = '/\b' . preg_quote($word, '/') . '\b/';
            }
            $string = preg_replace($word_list, '', strtolower($string));
            return ucwords($string);
        }
    }

    /**
     * Validate the form by validating all individual inputs
     *
     * @return void
     */
    public function mtii_validate_reg_form()
    {
        $all_input_names = $this->_all_form_input_names;
        foreach ($all_input_names as $input_name) {
            $input_value = isset($_POST[$input_name]) ? $_POST[$input_name] : '';
                $this->mtii_validate_input($input_name, $input_value);
        }
        if ($this->no_form_errors() && !$this->error_output_all) {
            $this->_add_all_info_to_db();
        }
    }

    /**
     * Add all inputs into the database
     *
     * @return void
     */
    private function _add_all_info_to_db()
    {
        global $mtii_db_coop_main_form;
        global $mtii_signatories_template_db;
        global $wpdb;
        $this->_get_form_data_as_assoc();
        $form_values = $this->form_values;
        if ($this->_reg_form_type==='coop_reg_main_form') {
            $invoice_info = $this->get_invoice_info_from_db();
            $main_form = $this->get_coop_main_form_data();
            $form_values["invoice_number_filled_against"] = $invoice_info->invoice_number;
            $form_values["request_ref_filled_against"] = $invoice_info->request_reference;
            $form_values["user_id"] = get_current_user_id();
            if ($main_form) {
                $main_form_id = $main_form->application_form_id;
                $inserted_row_id = $mtii_db_coop_main_form->update($main_form_id, $form_values);
            } else {
                $inserted_row_id = $mtii_db_coop_main_form->insert($form_values);
            }
        } else if ($this->_reg_form_type==='signatories_template') {
            $main_form = $this->get_coop_main_form_data();
            $signatories_form = $this->get_signatories_data();
            $form_values["main_coop_form_id"] = $main_form->application_form_id;
            if ($signatories_form) {
                $signatories_form_id = $signatories_form->signatories_form_id;
                $inserted_row_id = $mtii_signatories_template_db->update($signatories_form_id, $form_values);
            } else {
                $inserted_row_id = $mtii_signatories_template_db->insert($form_values);
            }
        } else if ($this->_reg_form_type==='business_premise') {
            global $mtii_biz_prem_db_main;
            $invoice_info = $this->get_invoice_info_from_db();
            $biz_premise_form = $this->get_biz_prem_form_data();
            $form_values["invoice_number_filled_against"] = $invoice_info->invoice_number;
            $form_values["request_ref_filled_against"] = $invoice_info->request_reference;
            $form_values["user_id"] = get_current_user_id();
            $form_values["is_admin_approved"] = "Awaiting Approval";
            date_default_timezone_set("Africa/Lagos");
            $form_values["time_of_declaration"] = date("h:i:s A");
            if ($biz_premise_form) {
                $biz_premise_form_id = $biz_premise_form->application_form_id;
                $inserted_row_id = $mtii_biz_prem_db_main->update($biz_premise_form_id, $form_values);
            } else {
                $inserted_row_id = $mtii_biz_prem_db_main->insert($form_values);
            }
        }

        if ($inserted_row_id && $wpdb->last_error === '') {
            $this->_set_added_records_success();
            unset($_POST);
            $_POST = array();
        } else {
            echo $inserted_row_id."<br />";
            echo $wpdb->last_error."<br />";
            $error_output = '';
            $error_output.= '<div class="section-body">';
            $error_output.= '<h2 class="section-heading errored-text">Registration Error!</h2>';
            $error_output.= '<hr class="header-lower-rule errored-bg" />';
            $error_output.= '<div class="payment-err">';
            $error_output.= '<div class="notification-wrapper">';
            $error_output.= '<div class="mtii_reg_errors"><h2 style="color: red;">There was a Problem saving registration info. Please Contact Admin</h2>';
            $error_output .= '</div>';
            $error_output .= '</div>';
            $error_output .= '</div>';
            $error_output .= '</div>';

            echo $error_output;
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
        if ( ($input_name!="value_of_share_holding" && $input_name!="number_of_shares_per_member" && $input_name!="total_shared_capital_paid"
            && $input_name!="area_of_operation" && $input_name!="area_of_operation_other" & $input_name!="time_of_declaration"
            && $input_name!="name_of_landlord" && $input_name!="address_of_landlord"
            ) && (!$input_val || trim($input_val)=="")
        ) {
            $error_id = $input_name;
            $error_output = ucfirst($this->_form_nice_names[$error_id])." cannot be blank";
            $this->add_to_wp_global_error($error_id, $error_output);
            $this->errored_inputs_classes[$input_name] = 'errored';
        } else {
            $this->errored_inputs_classes[$input_name] = '';
        }

        if ($input_name=='name_of_proposed_society') {
            global $mtii_db_coop_main_form;
            $existing_coop_name = $mtii_db_coop_main_form->get_by('name_of_proposed_society', $input_val);

            if ($existing_coop_name) {
                $coop_society_name = $existing_coop_name->name_of_proposed_society;
                if (($this->get_invoice_info_from_db())->invoice_number!=($this->get_coop_main_form_data())->invoice_number_filled_against) {
                    if (trim($coop_society_name)==trim($input_val)) {
                        $error_id = $input_name;
                        $error_output = "This Society name has already being used. Please Consider another name";
                        $this->add_to_wp_global_error($error_id, $error_output);
                        $this->errored_inputs_classes[$input_name] = 'errored';
                        $this->_defined_input_errors[$input_name] = 'This Society name has already being used by another cooperative society';
                    } else {
                        $this->errored_inputs_classes[$input_name] = '';
                        unset($this->_defined_input_errors[$input_name]);
                    }
                }
            }
        } else if ($input_name=="area_of_operation") {
            if ($input_val=="Others (Specify Below)") {
                if(trim($_POST["area_of_operation_other"])=="") {
                    $error_output1 = 'You have to specify the type of Area of Operation in 6B';
                    $this->add_to_wp_global_error('area_of_operation_other', $error_output1);
                    $this->errored_inputs_classes['area_of_operation_other'] = 'errored';
                    $this->_defined_input_errors['area_of_operation_other'] = 'You did not specify the type of Area of Operation in 6B';
                } else {
                    $this->errored_inputs_classes['area_of_operation_other'] = '';
                    $this->errored_inputs_classes[$input_name] = '';
                    unset($this->_defined_input_errors[$input_name]);
                }
            } else {
                $this->errored_inputs_classes['area_of_operation_other'] = '';
                unset($this->_defined_input_errors['area_of_operation_other']);
                $this->errored_inputs_classes[$input_name] = '';

            }
        } else if ($input_name = "is_premise_rented") {
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
     * Calls inputs validation and registers new user for the event if there is no error found
     *
     * @return void
     */
    public function mtii_save_reg_form_info()
    {
        if (isset($_POST["mtii_form_submit"])
            && ((isset($_POST['form_register_nonce']) && wp_verify_nonce($_POST['form_register_nonce'], 'form-register-nonce'))
            || (isset($_POST['main_registration_nonce']) && wp_verify_nonce($_POST['main_registration_nonce'], 'main-registration-nonce'))
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