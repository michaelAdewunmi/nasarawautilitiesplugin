<?php
/**
 * File for handling certificate replacements
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
use MtiiUtilities\CloudinaryUpload;
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
class CertificateReplacementReg extends RegistrationUtilities
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
    protected $replacement_category = null;

    /**
     * This holds the police extract upload url
     *
     * @var string
     */
    private $_police_extract = null;

    /**
     * This holds the police extract upload url
     *
     * @var string
     */
    private $_court_affidavit = null;

    /**
     * An array of all input_names
     *
     * @var array
     */
    protected $all_input_names = array (
        'name_of_society_or_organization', 'applicant_full_name', 'phone_number', 'email',
        'position_rank_in_the_society', 'certificate_number', 'date_cert_was_issued'
    );

    /**
     * An array of all options for the position select inputs
     *
     * @var array
     */
    protected $options_for_select_inputs = array (
        'position_rank_in_the_society' => array (
            "President", "Vice President", "Secretary", "Assitant Secretary",
            "Treasurer", "Assitant Treasurer", "Member"
        )
    );

    /**
     * Ids for the uploaded files input
     *
     * @var array
     */
    private $_files_name = array ('police_extract', 'court_affidavit');


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
    protected $reg_form_type = "is_cert_replacement";


    /**
     * Class' Construct
     *
     * @return null
     */
    public function __construct()
    {
        $invoice_info = $this->get_invoice_info_from_db();
        $invoice_catg = isset($invoice_info) ? $invoice_info->invoice_category : null;

        if ($invoice_catg && $invoice_catg!='') {
            $this->replacement_category = $invoice_catg;
        }

        foreach ($this->all_input_names as $input_name) {
            $this->all_input_names_as_assoc[$input_name] = ucwords(str_replace("_", " ", $input_name));
        }

        parent::__construct();
    }

    protected function  get_db_form_info_and_use_as_default_val()
    {
        $db_info_to_get=$this->get_cert_replacement_info();
        if (!$db_info_to_get) {
            return;
        }
        foreach ($this->all_form_input_names as $input_name) {
            $this->input_values_placeholder[$input_name] = isset($db_info_to_get->$input_name) ? $db_info_to_get->$input_name : '';
        }
    }

    // public function select_input_creator($input_name) {
    //     return $this->create_select_input($this->_options_for_select_inputs[$input_name], "Please Select Position", $input_name);
    // }


    public function create_files_input( $label, $file_name )
    {
        return '<label class="upload-label" for="uploaded_doc">'.$label.'</label>'.
        '<input  class="files-input"  type="file" name="'.$file_name.'" accept=".jpg,.png,.pdf" />';

    }

    public function get_replacement_category()
    {
        return $this->replacement_category;
    }


    public function check_if_replacement_invoice_is_used()
    {
        global $mtii_cert_replacement_table;
        $task_performer = new TasksPerformer;
        $replacement_invoice = $this->get_invoice_info_from_db();
        $invoice_sub_catg = $replacement_invoice->invoice_sub_category;
        $linked_coop = $mtii_cert_replacement_table->get_by('invoice_number_filled_against', $replacement_invoice->invoice_number);
        $allow_edit = isset($linked_coop->allow_edit) ? $linked_coop->allow_edit : null;
        if ($allow_edit==false && ($linked_coop || isset($linked_coop->name_of_society_or_organization))) {
            $coop = urlencode(openssl_encrypt("cooperative-soc", "AES-128-ECB", "X340&2&230rTHJ34"));
            $ngo = urlencode(openssl_encrypt("ngo-cbo", "AES-128-ECB", "X340&2&230rTHJ34"));

            $encoded_payment_catg = $replacement_invoice->invoice_category=="Cooperative" ? $coop : $ngo;

            $error_title = 'Used replacement Invoice';
            $error_body = 'This Invoice has already being used for ';
            $error_body .= 'certificate replacement. You should raise a new Invoice and make payment';
            $error_body .= ' <a class="round-btn-mtii small-btn" href="'.
                    site_url("/user-dashboard?").'do=pay&catg='.$encoded_payment_catg.'" >Create New Invoice</a>';
            return $task_performer->output_inline_notification($error_title, $error_body, 'is-error');
        } else if ($allow_edit==true && $linked_coop && $invoice_sub_catg=="replacement") {
            $this->get_db_form_info_and_use_as_default_val();
        } else {
            return null;
        }
    }

    public function mtii_validate_input($input_name, $input_val)
    {
        // $task_performer = new TasksPerformer;
        // $all_coops = json_decode($task_performer->get_all_ngos_and_coop());
        // if ($input_name=='name_of_society_or_organization' && !$task_performer->in_arrayi($input_val, $all_coops)) {
        //     $error_id = $input_name;
        //     $error_output = ucfirst($this->form_nice_names[$error_id])." cannot be blank";
        //     $this->add_to_wp_global_error($error_id, $error_output);
        //     $this->errored_inputs_classes[$input_name] = 'errored';
        //     $title = "Society name not Found!";
        //     $body = "Sorry! We cannot find any Registered organization. ";
        //     $body .= "with this name. Please Ensure your cooperative name is well spelt as it is in previous certificates. ";
        //     $body .= "You can contact Admin if you think this could be a mix up. Thank you. <br /><br />";
        //     echo $task_performer->output_inline_notification($title, $body, 'is-error');
        // } else
        if (!$input_val || trim($input_val)=="") {
            $error_id = $input_name;
            $error_output = ucfirst($this->form_nice_names[$error_id])." cannot be blank";
            $this->add_to_wp_global_error($error_id, $error_output);
            $this->errored_inputs_classes[$input_name] = 'errored';
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
        foreach ($all_input_names as $input_name) {
            $input_value = isset($_POST[$input_name]) ? $_POST[$input_name] : '';
                $this->mtii_validate_input($input_name, $input_value);
        }
        if ($this->no_form_errors() && !$this->error_output_all) {
            $cloudinary_util = new CloudinaryUpload(true, $this->_files_name);
            $file_validation_errors = $cloudinary_util->check_and_get_errors();
            if ($file_validation_errors) {
                echo $file_validation_errors;
            } else {
                $option_obtained_as_array = $task_performer->get_file_uploads_in_options();
                $invoice_info = $this->get_invoice_info_from_db();
                $uploaded_file_option = $option_obtained_as_array[$invoice_info->invoice_number];
                $validate_police_extract = wp_http_validate_url($uploaded_file_option["police_extract"]);
                $validate_court_affidavit = wp_http_validate_url($uploaded_file_option["court_affidavit"]);
                if ($validate_police_extract && $validate_court_affidavit) {
                    $this->_police_extract = $validate_police_extract;
                    $this->_court_affidavit = $validate_court_affidavit;
                    $cpt_content = json_encode($cloudinary_util->get_uploaded_file_info());
                    $reg_as_cp = get_page_by_title($invoice_info->invoice_number, OBJECT, 'mtii_cert_replcmnt');
                    $post_id = null;
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
                    if ($reg_as_cp && $reg_as_cp!="") {
                        $post_id = $reg_as_cp->ID;
                    }
                    $add_reg_as_cp = $task_performer->make_a_post(
                        'mtii_cert_replcmnt', $invoice_info->invoice_number, $cpt_content, $custom_fields, $post_id
                    );
                    if ($add_reg_as_cp=="There is an Error") {
                        $error_body = 'Your registration could not continue due to an Error! You should try again.';
                        $error_body .= 'If the Error persists, then you should contact Admin.';
                        echo $task_performer->output_inline_notification('Upload Error!', $error_body, 'is-error');
                    } else {
                        $this->add_all_info_to_db();
                    }
                } else {
                    $error_body = 'Your registration could not continue due to upload Error! You should try again.';
                    $error_body .= 'If the Error persists, then you should contact Admin.';
                    echo $task_performer->output_inline_notification('Upload Error!', $error_body, 'is-error');
                }
            }
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

    protected function add_all_info_to_db()
    {
        if (!$this->_police_extract || !$this->_court_affidavit) {
            return;
        }
        global $mtii_cert_replacement_table;
        global $wpdb;
        $task_performer = new TasksPerformer;
        $invoice_info = $this->get_invoice_info_from_db();
        $cert_replacement_form = $this->get_cert_replacement_info();
        $this->get_form_data_as_assoc();
        $form_values = $this->form_values;
        $form_values["police_extract"] = $this->_police_extract;
        $form_values["court_affidavit"] = $this->_court_affidavit;
        $form_values["invoice_number_filled_against"] = $invoice_info->invoice_number;
        $form_values["request_ref_filled_against"] = $invoice_info->request_reference;
        $form_values["user_id"] = get_current_user_id();
        $form_values["application_date"] = date("Y:m:d");
        $form_values["is_admin_approved"] = "Awaiting Approval";
        if ($cert_replacement_form) {
            $cert_replacement_id = $cert_replacement_form->application_form_id;
            $inserted_row_id = $mtii_cert_replacement_table->update($cert_replacement_id, $form_values);
        } else {
            $inserted_row_id = $mtii_cert_replacement_table->insert($form_values);
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
            if ($this->check_if_replacement_invoice_is_used()) {
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