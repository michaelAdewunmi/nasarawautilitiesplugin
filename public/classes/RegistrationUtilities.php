<?php
/**
 * File that runs Class for handling tasks related to mtii regsistration
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

use MtiiUtilities\CloudinaryUpload;
use MtiiUtilities\TasksPerformer;
use MtiiUtilities\MtiiRelatedInformation;
/**
 * Class for handling tasks related to mtii regsistration
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public/templates
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class RegistrationUtilities
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
    protected $all_form_input_names;

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
    protected $form_nice_names;

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
    protected $reg_form_type;


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

    /**
     * Mtii_Registration_Utilities Construct
     */
    public function __construct($form_input_names=null, $form_nice_names_as_assoc=null, $reg_form_type=null, $is_signatory_template=null)
    {
        if (isset($this->populate_lga_and_ward_options) && $this->populate_lga_and_ward_options) {
            $this->_populate_lga_options_for_selects();
            $this->_populate_ward_options_for_selects();
        }

        if (isset($this->all_input_names) && isset($this->all_input_names_as_assoc) && isset($this->reg_form_type)) {
            $form_input_names = $this->all_input_names;
            $form_nice_names_as_assoc = $this->all_input_names_as_assoc;
            $reg_form_type = $this->reg_form_type;
        }
        $task_performer = new TasksPerformer;
        if ($form_input_names!=null && $form_nice_names_as_assoc!=null && $reg_form_type!=null) {
            $this->reg_form_type = $reg_form_type;
            $this->_set_errored_input_classes_default($form_input_names);
            $this->_set_form_input_names_as_array($form_input_names);
            $this->set_form_input_nice_names_as_assoc_array($form_nice_names_as_assoc);
            $this->load_possible_default_values_for_inputs();
            if (isset($_POST) && !empty($_POST)) {
                $this->_set_input_values_placeholder_from_gobal_post_var($form_input_names);
            }
            $this->mtii_save_reg_form_info();
        }

        if ($is_signatory_template==="true") {
            $this->_is_signatory_template = true;
        }
    }

    protected function load_possible_default_values_for_inputs()
    {
        $this->get_db_form_info_and_use_as_default_val();
    }

    public function allow_coop_name_edit()
    {
        $coop_form_info = $this->get_coop_main_form_data();
        $approved_name = isset($coop_form_info->name_of_approved_society) ? $coop_form_info->name_of_approved_society : null;
        $all_fields_filled = $this->all_coop_db_table_fields_filled();

        if ($approved_name || !$all_fields_filled) {
            return false;
        } else {
            return true;
        }

    }

    public function allow_ngo_name_edit()
    {
        $main_form_info = $this->get_ngo_cbo_form_data();
        $approved_name = isset($main_form_info->name_of_proposed_organization)
            ? $main_form_info->name_of_proposed_organization : null;
        $all_fields_filled = $this->all_ngo_db_table_fields_filled();

        if ($approved_name || !$all_fields_filled) {
            return false;
        } else {
            return true;
        }
    }

    public function allow_biz_prem_name_edit()
    {
        $main_form_info = $this->get_biz_prem_form_data();
        $approved_name = isset($main_form_info->name_of_company)
            ? $main_form_info->name_of_company : null;
        $all_fields_filled = $this->all_biz_prem_table_fields_filled();

        if ($approved_name || !$all_fields_filled) {
            return false;
        } else {
            return true;
        }
    }

    public function all_biz_prem_table_fields_filled()
    {
        $main_form = $this->get_biz_prem_form_data();
        if ($main_form && $main_form->is_admin_approved=='' && $main_form->lga_of_company==''
            && $main_form->address_of_premise=='' && $main_form->nature_of_business==''
        ) {
            return false;
        } else {
            return true;
        }
    }


    public function all_coop_db_table_fields_filled()
    {
        $main_form = $this->get_coop_main_form_data();
        if ($main_form && $main_form->admin_approved=='' && $main_form->lga_of_proposed_society==''
            && $main_form->ward_of_proposed_society=='' && $main_form->nature_of_proposed_banker==''
        ) {
            return false;
        } else {
            return true;
        }
    }

    public function all_ngo_db_table_fields_filled()
    {
        $main_form = $this->get_ngo_cbo_form_data();
        if ($main_form && $main_form->is_admin_approved=='' && $main_form->lga_of_proposed_organization==''
            && $main_form->address_of_proposed_organization=='' && $main_form->name_of_proposed_banker==''
        ) {
            return false;
        } else {
            return true;
        }
    }

    private function _populate_lga_options_for_selects()
    {
        $lga_and_wards = new MtiiRelatedInformation;
        $lga = $lga_and_wards->get_all_lga();
        foreach ($lga as $key => $value) :
            if (is_array($this->lga_select_name)) {
                foreach ($this->lga_select_name as $name) {
                    $this->options_for_select_inputs[$name][] = str_replace("_", " ", $key);
                }
            } else {
                $this->options_for_select_inputs[$this->lga_select_name][] = str_replace("_", " ", $key);
            }
        endforeach;
    }

    private function _populate_ward_options_for_selects()
    {
        $lga_and_wards = new MtiiRelatedInformation;
        $wards = $lga_and_wards->get_all_wards();
        foreach ($wards as $key => $value) {
            if ($value=="is_lga_parent") {
                $this->options_for_select_inputs["ward_of_proposed_society"][] = array (
                    "option"        => str_replace("_", " ", $key),
                    "option_style"  => 'style="background-color: #cfcfcf; color: #fff"',
                    "disabled"      => 'disabled'
                );
            } else {
                $this->options_for_select_inputs["ward_of_proposed_society"][] = str_replace("_", " ", $key);
            }
        }
    }

    protected function call_class_particular_methods()
    {
        return;
    }

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

    protected function create_select_input($options, $instruction, $input_name, $id=null, $make_ordinal=false)
    {
        $this->make_input_ordinal = $make_ordinal;
        $value_placeholder = isset($this->input_values_placeholder[$input_name])
            ? $this->input_values_placeholder[$input_name] : '';

        if ($this->make_input_ordinal && $value_placeholder!='') {
            $ordinal_val_if_number = $this->ordinal($value_placeholder);
        } else {
            $ordinal_val_if_number = $value_placeholder;
        }

        $options = is_array($options) ? $options : array();
        $add_id = '';
        $add_class = '';
        if ($id && $id!='reduced-width') {
            $add_id='id="'.$id.'" ';
        } else if ($id=='reduced-width') {
            $add_class=$id;
        }

        $errored_class = $this->errored_inputs_classes[$input_name];
        $option_val = isset($this->input_values_placeholder[$input_name]) ?
            $this->input_values_placeholder[$input_name] : '';
        $select = '<select '.$add_id.' class="mtii-inline-input main-form mtii-select '.$add_class.' '.$errored_class.'" name="'.$input_name.'">';
        $select .= $option_val!='' ? '<option value="'.$option_val.
                    '">'.$ordinal_val_if_number.'</option>' : '<option value="">'.$instruction.'</option>';
        $select .= join("", array_map(array($this, '_render_option_in_select'), $options));
        $select .= "</select>";
        return $select;
    }

    private function _render_option_in_select($option)
    {
        //function ($option) {
        if (is_array($option)) {
            return '<option '.$option["option_style"].' value="'.$option["option"].'" '.$option["disabled"].'>'.$option["option"].'</option>';
        } else {
            if ($this->make_input_ordinal) {
                return '<option value="'.$option.'">'.$this->ordinal($option).'</option>';
            } else {
                return '<option value="'.$option.'">'.$option.'</option>';
            }
        }
        //}
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

            $invoice_info = $this->get_invoice_info_from_db();
            $coop_signatories_data = $this->get_signatories_data();
            if ($show_fully_completed || $coop_signatories_data) {
                if (!$coop_signatories_data) {
                    $second_completion_text = 'Awaiting Completion';
                } else {
                    $second_completion_text = 'Completed';
                    $second_stage_extra_class = '';
                    $second_completed_extra_class = '';
                }
                $second_circle_extra_class = 'done';
                $colored_bar_extra_class = 'second-done';
            }
        }

        if ($is_signatories || $show_fully_completed || ($this->get_coop_main_form_data()
            && !isset($_REQUEST['offline_to_online']) && $this->all_coop_db_table_fields_filled())
        ) {
            $colored_bar_extra_class = 'first-done'; $first_circle_extra_class = 'done'; $first_stage_extra_class = '';
            $first_completion_text = 'Completed'; $first_completed_extra_class = '';
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

    public function select_input_creator($input_name, $instruction, $id=null, $make_ordinal=false)
    {
        if (isset($_REQUEST["is_preview"]) && $_REQUEST["is_preview"]==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34")
            && !isset($_REQUEST["for_edit"])
        ) {
            echo '<span class="as-placeholder">'.$this->input_values_placeholder[$input_name].'</span>';
        } else {
            echo $this->create_select_input(
                $this->options_for_select_inputs[$input_name], $instruction, $input_name, $id, $make_ordinal
            );
        }
    }


    public function create_files_input( $label, $file_name )
    {
        if ($_REQUEST["is_preview"]==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34") && !isset($_REQUEST["for_edit"])) {
                    echo '<span class="as-placeholder">'.$this->input_values_placeholder[$file_name].'</span>';
        } else {
            return '<label class="upload-label" for="uploaded_doc">'.$label.'</label>'.
            '<input  class="files-input"  type="file" name="'.$file_name.'" accept=".jpg,.png,.pdf" />';
        }
    }

    public function get_input_or_placeholder_text($input_name, $type, $read_only=null, $placeholder=null, $make_ordinal=false, $extra_class='') {
        if ($make_ordinal && $this->input_values_placeholder[$input_name]=='') {
            $ordinal_val_if_necessary = $this->ordinal($this->input_values_placeholder[$input_name]);
        } else if (isset($this->input_values_placeholder[$input_name])) {
            $ordinal_val_if_necessary = $this->input_values_placeholder[$input_name];
        }

        if ($read_only!=null && $read_only==true) {
            $read_only = 'readonly';
        }

        $placeholder = $placeholder!=null ? $placeholder : '';

        if (isset($_REQUEST["is_preview"]) && !isset($_REQUEST["for_edit"])) {
            if ($_REQUEST["is_preview"]==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34")) {
                if (isset($_REQUEST["for_edit"]) && $_REQUEST["for_edit"]==1) {
                    echo '<input class="mtii-inline-input main-form '.$extra_class.' '.$this->errored_inputs_classes[$input_name].
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
            $default_input =  isset($this->input_values_placeholder[$input_name])
                ? $this->input_values_placeholder[$input_name] : '';
            $err_class =  isset($this->errored_inputs_classes[$input_name])
                ? $this->errored_inputs_classes[$input_name] : '';

            echo '<input class="mtii-inline-input main-form '.$extra_class.' '.$err_class.
            '" name="'.$input_name.'" type="'.$type.'" value="'.$default_input.
            '" placeholder="'.$placeholder.'" '.$read_only.' />';
        }
    }

    protected function  get_db_form_info_and_use_as_default_val()
    {
        if (isset($_REQUEST["is_preview"]) && $_REQUEST["is_preview"]==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34")) {
            if (isset($_REQUEST["for_biz_prem"]) && $_REQUEST["for_biz_prem"]==1) {
                $db_info_to_get = $this->get_biz_prem_form_data();
            } else if (isset($_REQUEST["for_ngo"]) && $_REQUEST["for_ngo"]==1) {
                $db_info_to_get = $this->get_ngo_cbo_form_data();
            }
            if (!$db_info_to_get) {
                echo die('<script>window.location.href="'.site_url("/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D").'"</script>');
            }
            foreach ($this->all_form_input_names as $input_name) {
                $this->input_values_placeholder[$input_name] = isset($db_info_to_get->$input_name) ? $db_info_to_get->$input_name : '';
            }
        }
    }

    /**
     * Get all inputs available in form by their name property and
     * turn them into an array
     *
     */
    protected function _set_input_values_placeholder_from_gobal_post_var( $form_input_names )
    {
        foreach ($form_input_names as $input_name) {
            $this->input_values_placeholder[$input_name] = isset($_POST[$input_name]) ? $_POST[$input_name] : '';
        }

    }

    protected function _set_errored_input_classes_default($form_input_names)
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
    protected function _set_form_input_names_as_array($form_input_names)
    {
        $this->all_form_input_names = $form_input_names;
    }

    /**
     * Set the value to the variable that determines if info was successflly added to the DB
     *
     */
    protected function _set_added_records_success()
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
     * Get saved active invoice for present logged in user info and use to pull inovoice from the Invoice DB table
     *
     * @param [mixed] $invoice_number    The invoice number to search against
     * @param [mixed] $request_reference request reference to search against
     *
     * @return [array]
     */
    public function get_invoice_info_from_db($invoice_number=null, $request_reference=null)
    {
        global $mtii_db_invoice;
        $invoices_json = get_option('enc_invoices');
        $invoices_array = $invoices_json && $invoices_json!='' ? json_decode($invoices_json, true) : array();
        $invoice_enc = isset($invoices_array[get_current_user_id()]) ? $invoices_array[get_current_user_id()] : null;
        $invoice_decrypt = openssl_decrypt($invoice_enc, "AES-128-ECB", "X340&2&230rTHJ34");
        $decoded_val = json_decode($invoice_decrypt, true);
        $invoice_number = $invoice_number ? $invoice_number : $decoded_val["invoice_number"];
        $request_reference = $request_reference ? $request_reference : $decoded_val["request_reference"];
        return $mtii_db_invoice->get_row_by_invoice($invoice_number, $request_reference);
    }

    /**
     * Get saved invoice from the cookie and use the information to get cookie value from the database
     *
     * @param [mixed] $invoice_number    The invoice number to search against
     * @param [mixed] $request_reference request reference to search against
     */
    public function get_coop_main_form_data($invoice_number=null, $request_reference=null)
    {
        global $mtii_db_coop_main_form;
        $invoice_info  =  !$invoice_number ? $this->get_invoice_info_from_db() : null;
        $invoice_number = $invoice_number ? $invoice_number : $invoice_info->invoice_number;
        $request_reference = $request_reference ? $request_reference : $invoice_info->request_reference;
        return $mtii_db_coop_main_form->get_row_by_invoice($invoice_number, $request_reference, true);
    }


    /**
     * Get saved invoice from the cookie and use the information to get cookie value
     * from database and get the data for the signatories template.
     *
     *
     *
     */
    public function get_signatories_data()
    {
        global $mtii_signatories_template_db;
        $society_info = $this->get_coop_main_form_data();
        $main_form_info = isset($society_info) ? $society_info : null;
        $the_id = $main_form_info && $main_form_info!="" ? $main_form_info->application_form_id : null;
        return $mtii_signatories_template_db->get_by('main_coop_form_id', $the_id);
    }


    /**
     * Get saved invoice from the cookie and use the information to get business premise info
     * from database
     */
    public function get_biz_prem_form_data()
    {
        global $mtii_biz_prem_db_main;
        $invoice_info = $this->get_invoice_info_from_db();
        return $mtii_biz_prem_db_main->get_row_by_invoice($invoice_info->invoice_number, $invoice_info->request_reference, true);
    }

    /**
     * Get saved invoice from the cookie and use the information to get Ngo info
     * from database
     */
    public function get_ngo_cbo_form_data($invoice_number=null, $request_reference=null)
    {
        global $mtii_ngo_cbo_db_table;
        $invoice_info  =  !$invoice_number ? $this->get_invoice_info_from_db() : null;
        $invoice_number = $invoice_number ? $invoice_number : $invoice_info->invoice_number;
        $request_reference = $request_reference ? $request_reference : $invoice_info->request_reference;
        return $mtii_ngo_cbo_db_table->get_row_by_invoice($invoice_number, $request_reference, true);
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
                $approval_status = get_post_meta($existing_doc->ID, 'approval_status', true);
                if ($admin_approved=='true' && $approval_status!='Approval Expired') {
                    return true;
                } else if ($admin_approved=='Awaiting Approval') {
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
     *  Check if form has been completely filled and approved by the admin
     */
    public function check_if_application_is_completed($catg, $invoice_number) {
        $invoice_info = $this->get_invoice_info_from_db();
        if ($invoice_info->invoice_number==$invoice_number) {
            if ($catg==='business_premise') {
                $reg_info = $this->get_biz_prem_form_data();
            } else if ($catg==='ngo_and_cbo') {
                $reg_info = $this->get_ngo_cbo_form_data();
            }
            $admin_approved = isset($reg_info->is_admin_approved) ? $reg_info->is_admin_approved : null;
            if ($admin_approved == 'Approved') {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Get all inputs available in form by their name property and
     * turn them into an array
     *
     * @return $inputs_names_array
     */
    protected function set_form_input_nice_names_as_assoc_array($form_nice_names_as_assoc)
    {
        $this->form_nice_names = $form_nice_names_as_assoc;
    }

    /**
     * Get all inputs available in form by their name property and
     * turn them into an array
     *
     * @return $inputs_names_array
     */
    protected function get_form_data_as_assoc()
    {

        $inputs_names_array = $this->all_form_input_names;
        foreach ($inputs_names_array as $input_name) {
            if (isset($_REQUEST["is_preview"]) && $_REQUEST["is_preview"]==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34")) {
                $this->get_db_form_info_and_use_as_default_val();
            }
            if ($input_name=="area_of_operation" && $_POST[$input_name]=="Others (Specify Below)") {
                $this->form_values[$input_name] = 'Others - '.trim($_POST["area_of_operation_other"]);
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
        return isset(self::$wp_error) ? self::$wp_error : (self::$wp_error = new \WP_Error(null, null, null));
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
        //var_dump($form_error_codes);
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
        $all_input_names = $this->all_form_input_names;
        foreach ($all_input_names as $input_name) {
            $input_value = isset($_POST[$input_name]) ? $_POST[$input_name] : '';
                $this->mtii_validate_input($input_name, $input_value);
        }
        if ($this->no_form_errors() && !$this->error_output_all) {
            $this->add_all_info_to_db();
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

        if ($this->reg_form_type==='business_premise') {

        }

        $this->check_db_insertion_success($inserted_row_id);
    }

    protected function delete_post_from_title($title=null, $post_type=null)
    {
        if (!$title || !$post_type) {
            return;
        }
        $existing_post = get_page_by_title($title, OBJECT, $post_type);
        $postid = $existing_post!=null && isset($existing_post->ID) ? $existing_post->ID :  null;
        wp_delete_post($postid, true);
    }

    protected function create_registration_custom_post($content, $post_type)
    {
        $task_performer = new TasksPerformer;
        $invoice_info = $this->get_invoice_info_from_db();
        $title = isset($invoice_info->invoice_number) ? $invoice_info->invoice_number : null;
        $request_reference = isset($invoice_info->request_reference) ? $invoice_info->request_reference : null;
        $custom_fields = array (
            'is_admin_approved'     => 'Awaiting Approval',
            'request_reference'     => $request_reference,
            'user_id'               => get_current_user_id()
        );
        $existing_ngo = get_page_by_title($title, OBJECT, $post_type);
        $post_id = $existing_ngo!=null ? $existing_ngo->ID :  null;
        $task_performer->make_a_post($post_type, $title, $content, $custom_fields, $post_id);
    }

    protected function check_db_insertion_success($inserted_row_id)
    {
        global $wpdb;
        $task_performer = new TasksPerformer;
        //$form_values = $this->form_values;
        if ($inserted_row_id && $wpdb->last_error === '') {
            // $invoice_number = $form_values["invoice_number_filled_against"];
            $this->_set_added_records_success();
            unset($_POST);
            $_POST = array();
        } else {
            $mail_content = "InsertedRow: ".$inserted_row_id."\n\n\nError: ".$wpdb->last_error;
            //echo $wpdb->last_error."<br />";
            wp_mail('devignersplacefornassarawa@gmail.com', 'MTII Error', $mail_content);
            $error_output = '<h2 class="section-heading errored-text">Registration Error!</h2>';
            $error_output .= '<hr class="header-lower-rule errored-bg" />';
            $error_output .= '<div class="payment-err">';
            $error_output .= '<div class="notification-wrapper">';
            $error_output .= '<div class="mtii_reg_errors"><h2 style="color: red;">There was a Problem saving registration info. Please Contact Admin</h2>';
            $error_output .= '</div>';
            $error_output .= '</div>';
            $error_output .= '</div>';
            echo $error_output;
        }
    }

    protected function delete_expired_invoice_doc($invoice_number)
    {
        $cloudinary_util = new CloudinaryUpload;
        $expired_invoice_doc = get_page_by_title($invoice_number, OBJECT, 'mtii_signed_uploads');
        $expired_upload_public_id = !$expired_invoice_doc && $expired_invoice_doc!=""
            ? get_post_meta($expired_invoice_doc->ID, 'public_id', true) : null;
        $cloudinary_util->delete_uploaded_doc($expired_upload_public_id);
        wp_delete_post($expired_invoice_doc->ID, true);
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
        if (!$input_val || trim($input_val)=="") {
            $error_id = $input_name;
            $error_output = ucfirst($this->form_nice_names[$error_id])." cannot be blank";
            $this->add_to_wp_global_error($error_id, $error_output);
            $this->errored_inputs_classes[$input_name] = 'errored';
        }
    }

    protected function validate_area_of_operation( $input_name, $input_val )
    {
        if ($input_val=="Others (Specify Below)") {
            if (trim($_POST["area_of_operation_other"])=="") {
                $error_output1 = 'You have to specify the type of Area of Operation';
                $this->add_to_wp_global_error('area_of_operation_other', $error_output1);
                $this->errored_inputs_classes['area_of_operation_other'] = 'errored';
                $this->_defined_input_errors['area_of_operation_other'] = 'You did not specify the type of Area of Operation';
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
?>