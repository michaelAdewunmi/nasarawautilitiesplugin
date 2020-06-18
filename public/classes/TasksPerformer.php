<?php
/**
 * This file basically performs any mundane task required
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
namespace MtiiUtilities;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * This class basically performs any general task required
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class TasksPerformer
{
    /**
     * Nasarawa's client ID required for authentication of every request by Parkway CBS
     *
     * @since  1.0.0
     * @access private
     * @var    string   $_client_id Required for authentication of every request by Parkway CBS

     */
    private $_client_id;

    /**
     * Nasarawa's client Secret required for authentication of every request to Parkway CBS
     *
     * @since  1.0.0
     * @access private
     * @var    string   $_client_secret Required for authentication of every request to Parkway CBS
     */
    private $_client_secret;

    /**
     * Determine wether site is used as a live site or in staging site (For Api purpose)
     *
     * @since  1.0.0
     * @access private
     * @var    string   $_client_secret Required for authentication of every request to Parkway CBS
     */
    private $_is_live;


    private $_expire_cookie = false;

    /**
     * Api Url to be called For payment purpose
     * @since  1.0.0
     * @access private
     * @var    string   $_client_secret Required for authentication of every request to Parkway CBS
     */
    public $payment_url;

    /**
     * Api Url For Previewing generated Invoice
     *
     * @since  1.0.0
     * @access private
     * @var    string   $_client_secret Required for authentication of every request to Parkway CBS
     */
    public $invoice_url;

    /**
     * Api Url to be called during Invoice Generation
     *
     * @since  1.0.0
     * @access private
     * @var    string   $_client_secret Required for authentication of every request to Parkway CBS
     */
    public $invoice_creation_url;

    /**
     * Api Url For Previewing generated Invoice
     *
     * @since  1.0.0
     * @access private
     * @var    string   $receipt_view_url Required for viewing receipts of payment
     */
    public $receipt_view_url;


    /**
     * Get the full names of the user if found in anu generated invoice
     *
     * @since  1.0.0
     * @access public
     * @var    string   $doc_id The id of the invoice as a custom post
     * @return string
     */
    public function get_user_info_from_invoice($post_id)
    {
        $doc_author = get_post_meta($post_id, 'invoice_created_by', single);
        $auth = get_user_by('id', $doc_author);
        $auth_id = $auth->data->ID;
        $auth_email = $auth->data->user_email;
        $f_name = get_the_author_meta('first_name', $auth_id);
        $l_name = get_the_author_meta('last_name', $auth_id);
        $full_name = $f_name." ".$l_name;
        return array("full_name" => $full_name, "user_email" => $auth_email);
    }


    /**
     * Api Url For Previewing generated Invoice
     *
     * @since  1.0.0
     * @access private
     *
     * @return boolean   Check if current user is an Admin
     */
    public function is_mtii_admin()
    {
        $user = wp_get_current_user();
        if (in_array('administrator', $user->roles) || in_array('mtii_coop_and_ngo_administrator', $user->roles)
            || in_array('mtii_biz_prem_administrator', $user->roles) || in_array('mtii_others_administrator', $user->roles)
        ) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Api Url For Previewing generated Invoice
     *
     * @since  1.0.0
     * @access private
     *
     * @return boolean   Check if current user is an Admin
     */
    public function is_coop_and_ngo_director()
    {
        $user = wp_get_current_user();
        if (in_array('administrator', $user->roles) || in_array('mtii_coop_and_ngo_administrator', $user->roles)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Api Url For Previewing generated Invoice
     *
     * @since  1.0.0
     * @access private
     *
     * @return boolean   Check if current user is an Admin
     */
    public function is_business_premises_director()
    {
        $user = wp_get_current_user();
        if (in_array('administrator', $user->roles) || in_array('mtii_biz_prem_administrator', $user->roles)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Api Url For Previewing generated Invoice
     *
     * @since  1.0.0
     * @access private
     *
     * @return boolean   Check if current user is an Admin
     */
    public function is_director_for_others()
    {
        $user = wp_get_current_user();
        if (in_array('administrator', $user->roles) || in_array('mtii_others_administrator', $user->roles)) {
            return true;
        } else {
            return false;
        }

    }


    /**
     * Runs functions on object Instantiation
     *
     * @return void
     */
    public function __construct()
    {
        $mtii_is_live = get_option('mtii_is_live');
        $is_live = isset($mtii_is_live) ? $mtii_is_live : 'off';
        if ($is_live==="on") {
            $this->_is_live = true;
        } else {
            $this->_is_live = false;
        }

        $this->get_api_keys();
        $this->set_api_urls();
    }


    /**
     * Renders the Navigation List items under the nav's unordered lists
     *
     * @param [string] $query_param      The request query parameter name
     * @param [string] $nav_param        The actual parameter for the said navigation
     * @param [string] $query_param_catg The Category from the request query
     * @param [string] $nav_catg         The Category from the request query
     * @param [string] $nav_slug         The Slug of the said navigation from which the frontend name is rendered
     * @param [bool]   $is_repl_or_legal State whether the navigation is for certificate replacement and legal search

     * @return [string] $list_item
     */
    public function show_navigation_list_item($query_param, $nav_param, $query_param_catg, $nav_catg, $nav_slug, $is_repl_or_legal=false)
    {   $query_name = $is_repl_or_legal ? 'repl_catg' : 'catg';
        $the_url = esc_url(site_url('/user-dashboard?do='.$nav_param.'&'.$query_name.'='.$nav_catg));
        $list_item = '<li class="child-nav '.($query_param===$nav_param && $query_param_catg===$nav_catg ? 'active' : '').'">';
        $list_item .= '<a href="'.$the_url.'">'.str_replace("_", " ", $nav_slug).'</a></li>';
        echo $list_item;
    }

    public function is_navigation_category_in_query() {
        $coop = urlencode(openssl_encrypt("cooperative-soc", "AES-128-ECB", "X340&2&230rTHJ34"));
        $ngo = urlencode(openssl_encrypt("ngo-cbo", "AES-128-ECB", "X340&2&230rTHJ34"));
        $biz_prem = urlencode(openssl_encrypt("business-premise", "AES-128-ECB", "X340&2&230rTHJ34"));
        $fertilizers = urlencode(openssl_encrypt("fertilizer-plants", "AES-128-ECB", "X340&2&230rTHJ34"));
        $packaging = urlencode(openssl_encrypt("sacks-packaging-akwanga", "AES-128-ECB", "X340&2&230rTHJ34"));
        $beef_processing = urlencode(openssl_encrypt("beef-proc-masaka-karu", "AES-128-ECB", "X340&2&230rTHJ34"));
        $haulage = urlencode(openssl_encrypt("haulage-fee-collection", "AES-128-ECB", "X340&2&230rTHJ34"));
        $others = urlencode(openssl_encrypt("others", "AES-128-ECB", "X340&2&230rTHJ34"));
        $query_param_catg = isset($_REQUEST['catg']) ? urlencode($_REQUEST['catg']) : null;

        //for replacements and legal search
        $repl_coop = urlencode(openssl_encrypt("Cooperative", "AES-128-ECB", "X340&2&230rTHJ34"));
        $repl_ngo = urlencode(openssl_encrypt("ngoAndCbo", "AES-128-ECB", "X340&2&230rTHJ34"));
        $query_param_catg_repl = isset($_REQUEST['repl_catg']) ? urlencode($_REQUEST['repl_catg']) : null;

        if ($query_param_catg===$coop || $query_param_catg===$ngo || $query_param_catg===$biz_prem
            || $query_param_catg===$fertilizers || $query_param_catg===$packaging || $query_param_catg===$beef_processing
            || $query_param_catg===$haulage || $query_param_catg===$others
            || $query_param_catg_repl===$repl_coop || $query_param_catg_repl===$repl_ngo
        ) {
            return true;
        } else {
            return false;
        }
    }

    public function get_active_navigation_class($nav_param)
    {
        $query_param = isset($_REQUEST['do']) ? urlencode($_REQUEST['do']) : null;
        if ($query_param===$nav_param && $this->is_navigation_category_in_query()) {
            return 'active';
        } else {
            return '';
        }
    }

    /**
     * Determines whether a string is a date
     *
     * @param [mixed] $date A string representing a dat string
     *
     * @return boolean
     */
    public function is_validate_date($date)
    {
        if (!$date) {
            return false;
        }

        try {
            new \DateTime($date);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Determines the difference time frame between two dates
     *
     * @param [mixed] $base_date A string representing a date string
     * @param [mixed] $now       A string representing a date string

     * @return boolean
     */
    public function check_date_difference($base_date=null, $now="now")
    {
        if ($this->is_validate_date($base_date) && $this->is_validate_date($now)) {
            $base_date = new \DateTime($base_date);
            $now = new \DateTime($now);
            $interval = $base_date->diff($now);
            return $interval->days;
        } else {
            return "Invalid Date";
        }
    }

    public function stripslashes_from_db_result($db_object)
    {
        if (is_object($db_object) || is_array($db_object)) {
            $db_object_array = (array) $db_object;
        } else {
            return $db_object;
        }
        return (object) array_map('stripslashes', $db_object_array);
    }


    private function set_api_urls()
    {
        if ($this->_is_live) {
            $this->payment_url = "https://nasarawaigr.com/c/make-payment";
            $this->invoice_url = "https://cbsapi.cashflow.ng/v2/ViewInvoice";
            $this->invoice_creation_url = "https://nasarawaigr.com/api/v1/invoice/create";
            $this->receipt_view_url = "https://nasarawaigr.com/c/invoice/receipts";
        } else {
            $this->payment_url = "http://uat.nasarawaigr.com/c/make-payment";
            $this->invoice_url = "http://cashflow.parkwayprojects.xyz/v2/ViewInvoice";
            $this->invoice_creation_url = "https://uat.nasarawaigr.com/api/v1/invoice/create";
            $this->receipt_view_url = "http://uat.nasarawaigr.com/c/invoice/receipts";
        }
    }

    /**
     * Get Nasarawa's client ID required for authentication of every request to Parkway CBS
     *
     * @since  1.0.0
     * @access public
     * @return string
     */
    public function get_mtii_client_id()
    {
        return $this->_client_id;
    }

    /**
     * Get Nasarawa's client Secret required for authentication of every request to Parkway CBS
     *
     * @since  1.0.0
     * @access public
     * @return string
     */
    public function get_mtii_client_secret()
    {
        return $this->_client_secret;
    }

    /**
     * Paste the active Invoice number in use and a reset button for when the invoice is to be changed
     * @param [mixed] $array Array or string to be sanitized
     *
     * @return mixed
     */
    public function show_invoice_number_and_reset_btn($heading)
    {
        $decoded_val = $this->get_active_invoice_linked_to_user();
        echo '<h2 class="section-heading">'.$heading.'</h2><hr class="header-lower-rule" />';
        echo '<span id="invoice-number-info">Invoice Number:'.$decoded_val["invoice_number"].
        '&nbsp;<a style="position: relative;top: -8px;" class="round-btn-mtii small-btn" '.
        'href="'.site_url().$_SERVER['REQUEST_URI'].'&reset=1'.
        '">Change Invoice</a></span>';
    }

    /**
     * Recursive sanitation for an array.
     * Thanks to @Broshi https://wordpress.stackexchange.com/questions/24736/wordpress-sanitize-array
     *
     * @param [mixed] $array Array or string to be sanitized
     *
     * @return mixed
     */
    public function recursive_sanitize_text_field( $array )
    {
        foreach ( $array as $key => &$value ) {
            if ( is_array($value) ) {
                $value = recursive_sanitize_text_field($value);
            } else {
                $value = sanitize_text_field($value);
            }
        }
        return $array;
    }

    public function get_invoice_details_from_db($invoice_number=null)
    {
        global $mtii_db_invoice;
        if (!$invoice_number) {
            return;
        }
        return $mtii_db_invoice->get_by('invoice_number', $invoice_number);
    }


    public function get_invoice_as_cpt($invoice_number=null)
    {
        if (!$invoice_number) {
            $invoice_details = $this->get_active_invoice_linked_to_user();
            $invoice_number = $invoice_details["invoice_number"];
        }
        return get_page_by_title($invoice_number, OBJECT, 'mtii_cbs_invoice');
    }

    public function get_active_invoice_linked_to_user()
    {
        $invoices_json = get_option('enc_invoices');
        $invoices_array = $invoices_json && $invoices_json!='' ? json_decode($invoices_json, true) : array();
        $invoice_enc = isset($invoices_array[get_current_user_id()]) ? $invoices_array[get_current_user_id()] : null;
        $invoice_decrypt = openssl_decrypt($invoice_enc, "AES-128-ECB", "X340&2&230rTHJ34");
        $decoded_val = json_decode($invoice_decrypt, true);
        return $decoded_val;
    }

    public function flag_invoice_as_expired()
    {
        $paid_invoice = $this->get_invoice_as_cpt();
        delete_post_meta($paid_invoice->ID, 'invoice_status');
        update_post_meta($paid_invoice->ID, 'invoice_status', 'expired');
        $add_invoice_to_db = $this->add_invoice_to_db();
    }

    public function update_active_invoice_b4_login()
    {
        $paid_invoice = $this->get_invoice_as_cpt();
        delete_post_meta($paid_invoice->ID, 'invoice_status');
        $connected_org = get_post_meta($paid_invoice->ID, 'connected_org', true);
        $invoice_sub_category = get_post_meta($paid_invoice->ID, 'invoice_sub_category', true);
        if ($invoice_sub_category==='re-certification' && !$connected_org) {
            update_post_meta($paid_invoice->ID, 'invoice_status', 'pending_use');
        } else {
            update_post_meta($paid_invoice->ID, 'invoice_status', 'active');
        }
        return $this->add_invoice_to_db();
    }

    public function paid_invoice_checker()
    {
        $invoice_details = $this->get_active_invoice_linked_to_user();

        $invoice_number = $invoice_details["invoice_number"];
        $invoice_amount_due = $invoice_details["invoice_amount_due"];
        $invoice_fully_paid = $invoice_details["fully_paid"];
        $request_reference = $invoice_details["request_reference"];
        $the_amount_paid = $invoice_details["the_amount_paid"];
        $invoice_amount = $invoice_details["invoice_amount"];
        $invoice_category = $invoice_details["invoice_category"];
        $invoice_sub_category = $invoice_details["invoice_sub_category"];
        $date_paid = $invoice_details["date_paid"];

        global $mtii_db_invoice;
        $invoice_details_db = $mtii_db_invoice->get_row_by_invoice($invoice_number, $request_reference);

        if ($invoice_fully_paid=="true" && $invoice_amount_due<1 && $the_amount_paid == $invoice_amount) {
            return "paid";
        } else {
            return "false";
        }
    }

    public function add_invoice_to_db()
    {
        $decoded_val = $this->get_active_invoice_linked_to_user();
        $paid_invoice = get_page_by_title($decoded_val["invoice_number"], OBJECT, 'mtii_cbs_invoice');
        $payer_email = $paid_invoice!="" ? get_post_meta($paid_invoice->ID, 'payer_email', true) : "";
        $invoice_expiry_timestamp = strtotime($decoded_val["date_paid"])+60*60*24*365;
        $invoice_expiry_date = date("Y-m-d\TH:i:s\Z", $invoice_expiry_timestamp);
        $invoice_sub_category = $decoded_val["invoice_sub_category"];
        $connected_org = get_post_meta($paid_invoice->ID, 'connected_org', true);
        if ($invoice_sub_category==='re-certification' && (!$connected_org || $connected_org=='')) {
            $invoice_status = $this->check_date_difference($decoded_val["date_paid"]) > 366 ? 'expired' : 'pending_use';
            $connected_org = '';
        } else {
            $invoice_status = $this->check_date_difference($decoded_val["date_paid"]) > 366 ? 'expired' : 'active';
        }
        $args = array (
            "invoice_number"            => $decoded_val["invoice_number"],
            "request_reference"         => $decoded_val["request_reference"],
            "invoice_category"          => $decoded_val["invoice_category"],
            "invoice_sub_category"      => $invoice_sub_category,
            "invoice_payer_email"       => $payer_email,
            "payment_date"              => $decoded_val["date_paid"],
            "invoice_expires"           => $invoice_expiry_date,
            "invoice_status"            => $invoice_status,
            "connected_org"             => $connected_org
        );

        global $wpdb;
        global $mtii_db_invoice;

        $wpdb->show_errors = false;

        $repl = $mtii_db_invoice->get_row_by_invoice($decoded_val["invoice_number"], $decoded_val["request_reference"]);
        if ($repl && $repl!=null && $repl!="") {
            $args["invoice_id"] = $repl->invoice_id;
            $inserted_id = $mtii_db_invoice->update($repl->invoice_id, $args);
        } else {
            $inserted_id = $mtii_db_invoice->insert($args);
        }

        if ($wpdb->last_error != '') {
            return 'There is an Error';
        } else if ($inserted_id) {
            return $inserted_id;
        }
    }

    /**
     * Creates a New post and add into the wordpress Database
     *
     * @param [string] $post_type     The Post type of the post to be added
     * @param [string] $title         The Title of the post
     * @param [string] $content       The Content of the post
     * @param [Array]  $custom_fields Array of custom fields and their values
     * @param [string] $post_id       The Id of the post to add if it is an update
     *
     * @return string
     */
    public function make_a_post($post_type, $title, $content, $custom_fields=array(), $post_id=null)
    {
        $custom_fields = count($custom_fields)>0 ? $this->recursive_sanitize_text_field($custom_fields) : array();
        if ($post_id!=null) {
            foreach ($custom_fields as $key => $value) {
                delete_post_meta($post_id, $key);
            }
        }
        $args = array (
            'ID'             => $post_id,
            'post_title'     => sanitize_text_field($title),
            'post_content'   => $content,
            'post_status'    => 'publish',
            'post_type'      => $post_type,
            'meta_input'     => $custom_fields
        );
        $result = wp_insert_post($args);
        if (is_wp_error($result)) {
            return "There is an Error";
        } else {
            return "Post added to Database";
        }
    }

    /**
     * Set a Cookie or delete a set cookie if some conditins are met
     *
     * @return void
     */
    public function set_a_cookie()
    {
        setcookie('mtii_payment_invoice', '', time() - ( 15 * 60 ), COOKIEPATH, COOKIE_DOMAIN);
        global $mtii_db_invoice;
        $invoices_json = get_option('enc_invoices');
        $invoices_array = $invoices_json && $invoices_json!='' ? json_decode($invoices_json, true) : array();
        if (!is_admin()) {
            if ($this->_expire_cookie || (isset($_REQUEST["reset"]) && $_REQUEST["reset"]==1)) {
                $extra_query = "";
                if (isset($_REQUEST["is-for-upload"]) && $_REQUEST["is-for-upload"]==1) {
                    $extra_query = "&is-for-upload=1";
                }
                if (isset($_REQUEST["org_source"]) && isset($_REQUEST["recert_red"]) && $_REQUEST["recert_red"]==1) {
                    $extra_query .= '&org_source='.urlencode($_REQUEST["org_source"]);
                }
                $invoices_array[get_current_user_id()] = null;
                update_option('enc_invoices', json_encode($invoices_array));
                echo die(
                    '<script>window.location.href="'.site_url("/user-dashboard?do=reg").$extra_query.'"</script>'
                );
            } else {
                if (isset($_REQUEST["org_source"])) {
                    $invoice_number = openssl_decrypt($_REQUEST["org_source"], "AES-128-ECB", "X340&2&230rTHJ34");
                } else if (isset($_POST["invoice_number"])) {
                    $invoice_number = $_POST["invoice_number"];
                } else if ($invoices_array && count($invoices_array)>0) {
                    $invoice_enc = isset($invoices_array[get_current_user_id()]) ? $invoices_array[get_current_user_id()] : null;
                    $invoice_decrypt = openssl_decrypt($invoice_enc, "AES-128-ECB", "X340&2&230rTHJ34");
                    $decoded_val = json_decode($invoice_decrypt, true);
                    $invoice_number = $decoded_val["invoice_number"];
                } else {
                    $invoice_number = null;
                }
                if (trim($invoice_number)!='' && $invoice_number) {
                    $invoice_info = get_page_by_title($invoice_number, OBJECT, 'mtii_cbs_invoice');
                    if ($invoice_info && $invoice_info->ID) {
                        $invoice_amount_due = $invoice_info!="" ? get_post_meta($invoice_info->ID, 'amount_due', true) : 999999;
                        $invoice_fully_paid = $invoice_info!="" ? get_post_meta($invoice_info->ID, 'invoice_fully_paid', true) : false;
                        $req_reference = $invoice_info!="" ? get_post_meta($invoice_info->ID, 'request_reference', true) : null;
                        $the_amount_paid = $invoice_info!="" ? get_post_meta($invoice_info->ID, 'amount_paid', true): 0;
                        $invoice_amount = $invoice_info!="" ? get_post_meta($invoice_info->ID, 'invoice_amount', true) : 999999;
                        $invoice_category = $invoice_info!="" ? get_post_meta($invoice_info->ID, 'invoice_category', true) : null;
                        $invoice_sub_category = $invoice_info!="" ? get_post_meta($invoice_info->ID, 'invoice_sub_category', true) : null;
                        $date_paid = $invoice_info!="" ? get_post_meta($invoice_info->ID, 'date_paid', true) : null;
                        $date_paid = $date_paid==null ? "00:00:00" : $date_paid;
                        $invoice_expiry_timestamp = strtotime($date_paid)+60*60*24*365;
                        $invoice_expiry_date = date("Y-m-d\TH:i:s\Z", $invoice_expiry_timestamp);
                        $invoice_status = $invoice_info!="" ? get_post_meta($invoice_info->ID, 'invoice_status', true) : null;
                        $connected_org = $invoice_info!="" ? get_post_meta($invoice_info->ID, 'connected_org', true) : null;
                        $payer_email = $invoice_info!="" ? get_post_meta($invoice_info->ID, 'payer_email', true) : "";


                        $invoice_info = array(
                            "invoice_number"        => $invoice_number,
                            "request_reference"     => $req_reference,
                            "date_paid"             => $date_paid,
                            "payment_date"          => $date_paid,
                            "fully_paid"            => $invoice_fully_paid,
                            "invoice_amount_due"    => $invoice_amount_due,
                            "invoice_amount"        => $invoice_amount,
                            "the_amount_paid"       => $the_amount_paid,
                            "invoice_category"      => $invoice_category,
                            "invoice_sub_category"  => $invoice_sub_category,
                            "user_id"               => get_current_user_id(),
                            "invoice_status"        => $invoice_status,
                            "connected_org"         => $connected_org,
                            "invoice_payer_email"   => $payer_email,
                        );
                        $value = openssl_encrypt(json_encode($invoice_info), "AES-128-ECB", "X340&2&230rTHJ34");
                        $invoices_array[get_current_user_id()] = $value;
                        update_option('enc_invoices', json_encode($invoices_array));
                        $existing_invoice_in_db = $mtii_db_invoice->get_row_by_invoice($invoice_number, $req_reference);
                        if ($existing_invoice_in_db && $existing_invoice_in_db!="") {
                            $invoice_info["invoice_id"] = $existing_invoice_in_db->invoice_id;
                            $inserted_id = $mtii_db_invoice->update($existing_invoice_in_db->invoice_id, $invoice_info);
                        } else {
                            $inserted_id = $mtii_db_invoice->insert($invoice_info);
                        }
                    }
                }
            }
        }
    }

    public function add_custom_fields_to_post($title=null, $post_type=null, $custom_fields=array())
    {
        if (!$title || !$post_type || !is_array($custom_fields)) {
            return;
        }
        $existing_post = get_page_by_title($title, OBJECT, $post_type);
        foreach ($custom_fields as $key => $value) {
            delete_post_meta($existing_post->ID, $key);
            update_post_meta($existing_post->ID, $key, $value);
        }

    }


    public function add_invoice_as_custom_post($user_invoice_details)
    {
        $existing_invoice = get_page_by_title($user_invoice_details->InvoiceNumber, OBJECT, 'mtii_cbs_invoice');

        if ($existing_invoice==null) {
            $content = json_encode($user_invoice_details);
            $main_meta = array(
                'payer_email'               => sanitize_text_field($user_invoice_details->Email),
                'request_reference'         => sanitize_text_field($user_invoice_details->RequestReference),
                'payer_id'                  => sanitize_text_field($user_invoice_details->PayerId),
                'invoice_created_by'        => get_current_user_id(),
                'recipient'                 => sanitize_text_field($user_invoice_details->Recipient),
                'invoice_amount'            => sanitize_text_field($user_invoice_details->AmountDue),
                'amount_due'                => sanitize_text_field($user_invoice_details->AmountDue),
                'invoice_category'          => sanitize_text_field($user_invoice_details->invoice_category),
                'invoice_sub_category'      => sanitize_text_field($user_invoice_details->invoice_sub_category),
                'date_created'              => Date("Y-m-d"),
                'amount_paid'               => 0,
                'date_paid'                 => null,
                'invoice_fully_paid'        => false,
                'additional_field'          => json_encode($user_invoice_details->additional_information)
            );
            $extra_meta = array();

            $meta_array = array_merge($main_meta, $extra_meta);
            $args = array(
                'post_title'     => sanitize_text_field($user_invoice_details->InvoiceNumber),
                'post_content'   => $content,
                'post_status'    => 'publish',
                'post_type'      => 'mtii_cbs_invoice',
                'meta_input'     => $meta_array
            );
            $result = wp_insert_post($args);
            if (is_wp_error($result)) {
                return "There is an Error";
            } else {
                return "Invoice added to Database";
            }
        } else {
            $invoice_paid = get_post_meta($existing_invoice->ID, 'amount_paid', true);
            if($invoice_paid==true) {
                return "Invoice is already Used";
            } else {
                $content = json_encode($user_invoice_details);
                $args = array(
                    'post_title'     => sanitize_text_field($user_invoice_details->InvoiceNumber),
                    'post_content'   => $content,
                    'post_status'    => 'publish',
                    'post_type'      => 'mtii_dup_invoice',
                    'meta_input'     => array(
                        'payer_email'               => sanitize_text_field($user_invoice_details->Email),
                        'request_reference'         => sanitize_text_field($user_invoice_details->RequestReference),
                        'payer_id'                  => sanitize_text_field($user_invoice_details->PayerId),
                        'recipient'                 => sanitize_text_field($user_invoice_details->Recipient),
                        'invoice_created_by'        => get_current_user_id(),
                        'invoice_amount'            => sanitize_text_field($user_invoice_details->AmountDue),
                        'amount_due'                => sanitize_text_field($user_invoice_details->AmountDue),
                        'invoice_category'          => sanitize_text_field($user_invoice_details->invoice_category),
                        'invoice_sub_category'      => sanitize_text_field($user_invoice_details->invoice_sub_category),
                        'date_created'              => Date("Y-m-d"),
                        'amount_paid'               => 0,
                        'date_paid'                 => null,
                        'invoice_fully_paid'        => false

                    )
                );
                $result = wp_insert_post($args);
                if (is_wp_error($result)) {
                    return "Duplicate Invoice couldn't save";
                } else {
                    return "Invoice saved as a duplicate";
                }
            }
        }
    }

    public function update_invoice_as_custom_post($user_invoice_details)
    {
        $saved_invoice_when_generated = get_page_by_title($user_invoice_details["InvoiceNumber"], OBJECT, 'mtii_cbs_invoice');

        if ($saved_invoice_when_generated!=null) {
            $invoice_amount = get_post_meta($saved_invoice_when_generated->ID, 'amount_due', true);
            $invoice_fully_paid = get_post_meta($saved_invoice_when_generated->ID, 'invoice_fully_paid', true);
            $req_reference = get_post_meta($saved_invoice_when_generated->ID, 'request_reference', true);
            $amount_paid = get_post_meta($saved_invoice_when_generated->ID, 'amount_paid', true);

            if ($invoice_fully_paid=="true" || $invoice_amount<1) {
                return "Invoice Already used";
            } else {
                //The variable below should be zero if the amount was paid in full
                $new_amount_due = $invoice_amount - $user_invoice_details["AmountPaid"];

                //To capture part payment scenario
                $total_amount_paid = $user_invoice_details["AmountPaid"]/*new_payment*/ + $amount_paid/*previous_amount_paid*/;

                $meta_update_amnt_paid = update_post_meta(
                    $saved_invoice_when_generated->ID, 'amount_paid', $total_amount_paid
                );
                $meta_update_date_paid = update_post_meta(
                    $saved_invoice_when_generated->ID, 'date_paid', Date("Y-m-d")
                );
                $meta_update_amount_due = update_post_meta(
                    $saved_invoice_when_generated->ID, 'amount_due', $new_amount_due
                );
                update_post_meta(
                    $saved_invoice_when_generated->ID, 'all_payment_info',  json_encode($user_invoice_details)
                );

                if ($invoice_amount==$user_invoice_details["AmountPaid"]
                    && $req_reference==$user_invoice_details["RequestReference"]
                ) {
                    $meta_update_invoice_used = update_post_meta(
                        $saved_invoice_when_generated->ID, 'invoice_fully_paid', 'true'
                    );
                    if ($meta_update_invoice_used == true && $meta_update_amnt_paid == true
                        && $meta_update_date_paid == true && $meta_update_amount_due == true
                    ) {
                        return "All Updated";
                    } else {
                        return "Info Partially Updated";
                    }
                } else {
                    return "Amount or Request Reference Error";
                }
            }
        } else {
            return "Invoice Not Found";
        }
    }

    public function send_payment_emails($user_invoice_details, $updated_invoice_response) {
        $saved_invoice_when_generated = get_page_by_title($user_invoice_details["InvoiceNumber"], OBJECT, 'mtii_cbs_invoice');
        $payer_name = $saved_invoice_when_generated!=null
            ? get_post_meta($saved_invoice_when_generated->ID, 'recipient', true) : '';
        $recipient = $saved_invoice_when_generated!=null
            ? get_post_meta($saved_invoice_when_generated->ID, 'payer_email', true) : '';
        $invoice_amount = $saved_invoice_when_generated!=null
            ? get_post_meta($saved_invoice_when_generated->ID, 'invoice_amount', true) : '';

        if ($updated_invoice_response=="Invoice Already used") {
            $payer_message = 'Hello '.$payer_name.',<br /><br />'.
            'Please be informed that the invoice you just paid for with the invoice number <strong>'.
            $user_invoice_details["InvoiceNumber"].'</strong> is a previously used Invoice.'.
            '. It is <strong>Strongly</strong>  advised that you reach the admin for clarifications and rectification. '.
            'We deeply regret any Inconvenience this might cause you. Thank you!';

            $mail_content = $this->create_email_from_template(
                'Invoice is already Used', "$payer_message"
            );

        } else if ($updated_invoice_response=="Amount or Request Reference Error") {
            $payer_message = 'Hello '.$payer_name.',<br /><br />'.
            'Your payment was successful but there is an amount error or a request refrence error. Your Invoice number is <strong>'.
            $user_invoice_details["InvoiceNumber"].'</strong>. It is <strong>Strongly</strong>  advised that you reach the '.
            'admin for clarifications and rectification. We deeply regret any Inconvenience this might cause you. Thank you!';

            $mail_content = $this->create_email_from_template(
                'Amount or Reference Error during Payment', $payer_message
            );
        } else if ($updated_invoice_response=="Info Partially Updated") {
            $payer_message = 'Hello '.$payer_name.',<br /><br />'.
            'Your payment was successful but there is an error when saving your payment information. Your Invoice number is <strong>'.
            $user_invoice_details["InvoiceNumber"].'</strong>. It is <strong>Strongly</strong>  advised that you contact the '.
            'admin with your invoice number and payment information for proper documentation. We deeply regret any Inconvenience this '.
            'might cause you. Thank you!';

            $mail_content = $this->create_email_from_template(
                'Invoice Update Error', $payer_message
            );
        } else if ($updated_invoice_response=="Invoice Not Found") {
            $payer_message = 'Hello Admin,<br /><br />'.
            'There is a payment with the Invoice number <strong> '.
            $user_invoice_details["InvoiceNumber"].'</strong>. This invoice is not recognized in the database. '.
            'Please make further investigations and clarifications.';

            $mail_content = $this->create_email_from_template(
                'Invoice not Found', $payer_message
            );
        } else if ($updated_invoice_response=="All Updated") {
            $payer_message = 'Congratulations '.$payer_name.',<br /><br />'.
            'Your payment was successful and Your invoice information has been saved. Your Invoice number is <strong>'.
            $user_invoice_details["InvoiceNumber"].'</strong>. You can login into the website to continue registration'.
            'Thank you!';

            $mail_content = $this->create_email_from_template(
                'Payment Successful!', $payer_message
            );
        }

        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail(array($recipient, 'devignersplacefornassarawa@gmail.com'), 'MTII Payment Notification', $mail_content, $headers);
    }

    public function mtii_send_email_to_address($email_body, $email_heading_inside, $heading_main, $recipient)
    {

        $mail_content = $this->create_email_from_template($email_heading_inside, $email_body);
        $headers = array('Content-Type: text/html; charset=UTF-8');
        return wp_mail($recipient, $heading_main, $mail_content, $headers);
    }

    public function add_signed_doc_as_custom_post($invoice_number, $cloudinary_return)
    {
        $existing_upload = get_page_by_title($invoice_number, OBJECT, 'mtii_signed_uploads');
        $content = json_encode($cloudinary_return);
        if ($existing_upload==null) {
            $args = array(
                'post_title'     => sanitize_text_field($invoice_number),
                'post_content'   => $content,
                'post_status'    => 'publish',
                'post_type'      => 'mtii_signed_uploads',
                'meta_input'     => array(
                    'secure_url'         => sanitize_text_field($cloudinary_return['secure_url']),
                    'signature'          => sanitize_text_field($cloudinary_return['signature']),
                    'public_id'          => sanitize_text_field($cloudinary_return['public_id']),
                    'user_id'            => sanitize_text_field(get_current_user_id()),
                    'admin_approved'     => "Awaiting Approval",
                    'approval_status'    => "Approval Active",
                    'date_created'       => Date("Y-m-d"),
                )
            );
            $result = wp_insert_post($args);
            if (is_wp_error($result)) {
                return "There is an Error during Addition";
            } else {
                $message_to_admin = 'Hello Admin <br /><br />'.
                'You have a pending application with invoice number '.$invoice_number.' waiting for approval. '.
                ' You should login to access and approve or decline registration'.
                'Thank you!';
                $this->mtii_send_email_to_address(
                    $message_to_admin, 'Pending Approval', 'New Registration requiring Approval', get_option('admin_email')
                );

                unset($_POST);
                unset($_FILES);
                return "Document uploaded and added to Database";
            }
        } else {
            delete_post_meta($existing_upload->ID, 'public_id');
            delete_post_meta($existing_upload->ID, 'secure_url');
            delete_post_meta($existing_upload->ID, 'signature');
            delete_post_meta($existing_upload->ID, 'admin_approved');
            delete_post_meta($existing_upload->ID, 'approval_status');
            $args = array(
                'ID'             => $existing_upload->ID,
                'post_title'     => sanitize_text_field($invoice_number),
                'post_content'   => $content,
                'post_status'    => 'publish',
                'post_type'      => 'mtii_signed_uploads',
                'meta_input'     => array(
                    'secure_url'         => sanitize_text_field($cloudinary_return['secure_url']),
                    'signature'          => sanitize_text_field($cloudinary_return['signature']),
                    'public_id'          => sanitize_text_field($cloudinary_return['public_id']),
                    'admin_approved'     => "Awaiting Approval",
                    'approval_status'    => "Approval Active",
                    'date_updated'       => Date("Y-m-d"),
                )
            );
            $result = wp_insert_post($args);
            if (is_wp_error($result)) {
                return "There is an Error during Update";
            } else {
                $message_to_admin = 'Hello Admin <br /><br />'.
                'You have a pending application with invoice number '.$invoice_number.' waiting for approval. '.
                ' You should login to access and approve or decline registration'.
                'Thank you!';
                $this->mtii_send_email_to_address(
                    $message_to_admin, 'Pending Approval', 'New Registration requiring Approval', get_option('admin_email')
                );

                unset($_POST);
                unset($_FILES);
                return "Document upload successfully updated";
            }
        }
    }



    public function create_email_from_template ($email_heading, $mail_content)
    {
        $output = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        $output .= '<html xmlns="http://www.w3.org/1999/xhtml">';
        $output .= '<head>';
        $output .= '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
        $output .= '<title>MTII</title>';
        $output .= '<meta name="viewport" content="width=device-width, initial-scale=1.0"/>';
        $output .= '<link href="https://fonts.googleapis.com/css?family=Cabin:400&display=swap" rel="stylesheet">';
        $output .= '</head>';
        $output .= '<body style="margin:0; padding: 0; font-family: '.'Cabin'.', sans-serif;">';
        $output .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="background: #fff; min-height: 100vh">';
        $output .= '<tr>';
        $output .= '<td style="display: block;">';
        $output .= '<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%"';
        $output .= 'style="background: #fff; min-height: 85vh; padding: 40px 0; border-radius: 0 40px;';
        $output .= 'margin: 20px auto; border-collapse: collapse;">';
        $output .= '<tr>';
        $output .= '<td>';
        $output .= '<img src="https://res.cloudinary.com/ministry-of-trade-industry-and-investment/image/upload/v1587394399/mtii_logo_lmgsnf.jpg"';
        $output .= 'alt="mtii-logo" width="200px" style="margin: 20px auto; display: block;"';
        $output .= '/>';
        $output .= '<h1 style="margin: 0; padding-left: 0px; font-size: 20px;">'.$email_heading.'</h1>';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '<tr>';
        $output .= '<td style="padding: 10px 0; font-size: 15px; font-family: '.'Cabin'.', sans-serif;">';
        $output .= $mail_content;
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '<tr>';
        $output .= '<td style="padding: 10px 0; font-size: 15px; font-family: '.'Cabin'.', sans-serif;">';
        $output .= 'Best Regards';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '<tr>';
        $output .= '<td style="display: block; margin-top:-22px; padding-left: 0px; font-size: 12px;">';
        $output .= '<em style="font-family: '.'Cabin'.', sans-serif;">Ministry of Trade, Industry and Investment</em>';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '</table>';
        $output .= '<table align="left" border="0" cellpadding="0" cellspacing="0" width="100%"';
        $output .= 'style="display: block; background: #f9f9f9; padding: 10px 0; border-radius: 10px; width:100%;';
        $output .= 'margin: 20px auto; border-collapse: collapse;">';
        $output .= '<img src="https://res.cloudinary.com/ministry-of-trade-industry-and-investment/image/upload/v1587394399/mtii_logo_lmgsnf.jpg"';
        $output .= 'alt="mtii-logo" width="50px" style="margin: 10px 20px; display: block;"';
        $output .= '/>';
        $output .= '<tr style="display:block; width: 100%; margin: 10px 20px; margin-bottom: 0px">';
        $output .= '<td style="padding: 10px; font-size: 12px; color: #c9c9c9;">';
        $output .= '<hr style="display: block; width: 100%; border: 0; height: 2px; margin: 5px auto; background: #c9c9c9;" />';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '<tr style="display:block; width: 100%; margin: 10px 20px; margin-bottom: 0px">';
        $output .= '<td style="padding: 10px; font-size: 12px; color: #c9c9c9;">';
        $output .= 'MTII - Beside St. William Cathedral, Jos Road, Lafia, Nasarawa State. | +2348060721155';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '<tr style="display:block; width: 100%; margin: 10px 20px; margin-top: 0px">';
        $output .= '<td style="padding: 0 10px; font-size: 12px; color: #c9c9c9;">';
        $output .= 'mtii@nasarawastate.gov.ng';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '</table>';
        $output .= '</td>';
        $output .= '</tr>';
        $output .= '</table>';
        $output .= '</body>';
        $output .= '</html>';

        return $output;
    }
    /**
     * Retrieve all NGOs from options table or create the new option if no such option exists
     *
     * @return [array]
     */
    public function get_all_ngos_and_coop($for_ngo=false)
    {
        $lists_from_option = $for_ngo ? get_option('all_ngos') : get_option('all_coops');
        if (!$lists_from_option) {
            if ($for_ngo) {
                $inputFileName = WP_CONTENT_DIR . "/plugins/".MTII_UTILITIES_PLUGIN_NAME."/pdftojpeg/ngo-list.xls";
            } else {
                $inputFileName = WP_CONTENT_DIR . "/plugins/".MTII_UTILITIES_PLUGIN_NAME."/pdftojpeg/coop-list.xls";
            }

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            $spreadsheet = $reader->load($inputFileName);

            $inputFileType = 'Xls';

            $reader = IOFactory::createReader($inputFileType);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($inputFileName);

            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $all_lists = array();
            // foreach ($i=1; $i<800; $i++) {
            foreach ($sheetData as $key => $value) {
                $val = $for_ngo ? $sheetData[$key]["B"] : $sheetData[$key]["A"];
                if ($val!=="NGO'S NAME" && $val!="" && $val!=="COOP NAMES") {
                    //echo $val."<br /><br />";
                    $all_lists[] = $val;
                }
            }

            if ($for_ngo) {
                !get_option('all_ngos') ? add_option('all_ngos', json_encode($all_lists)) : 'false';
                $lists_from_options = get_option('all_ngos');
            } else {
                !get_option('all_coops') ? add_option('all_coops', json_encode($all_lists)) : 'false';
                $lists_from_options = get_option('all_coops');
            }
        }
        return $lists_from_option;
    }

     /**
     * Retrieve all NGOs from options table or create the new option if no such option exists
     *
     * @return [array]
     */
    public function get_all_registered_business_premise()
    {
        $lists_from_option = get_option('all_biz_prem');
        if (!$lists_from_option) {

            $inputFileName = WP_CONTENT_DIR . "/plugins/".MTII_UTILITIES_PLUGIN_NAME."/pdftojpeg/business_premises.xls";

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            $spreadsheet = $reader->load($inputFileName);

            $inputFileType = 'Xls';

            $reader = IOFactory::createReader($inputFileType);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($inputFileName);

            $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

            $all_lists = array();
            foreach ($sheetData as $key => $value) {
                $val = $sheetData[$key]["A"];
                if ($val!="" && $val!=="Business Premise Names") {
                    $all_lists[] = $val;
                }
            }
            add_option('all_biz_prem', json_encode($all_lists));
            $lists_from_option = get_option('all_biz_prem');
        }
        return $lists_from_option;
    }

    private function get_api_keys()
    {
        $mtii_is_live = get_option('mtii_is_live');
        $mtii_live_api_key_client_id = get_option('mtii_live_api_key_client_id');
        $mtii_live_api_key_client_secret = get_option('mtii_live_api_key_client_secret');
        $mtii_test_api_key_client_id = get_option('mtii_test_api_key_client_id');
        $mtii_test_api_key_client_secret = get_option('mtii_test_api_key_client_secret');

        $the_client_id_live = isset($mtii_live_api_key_client_id) ? $mtii_live_api_key_client_id : '';
        $the_client_id_test = isset($mtii_test_api_key_client_id) ? $mtii_test_api_key_client_id : '';
        $the_client_secret_live = isset($mtii_live_api_key_client_secret) ? $mtii_live_api_key_client_secret : '';
        $the_client_secret_test = isset($mtii_test_api_key_client_secret)  ? $mtii_test_api_key_client_secret : '';

        $is_live = isset($mtii_is_live) ? $mtii_is_live : 'off';
        $this->_client_id = $is_live==='on' ? $the_client_id_live : $the_client_id_test;
        $this->_client_secret = $is_live==='on' ? $the_client_secret_live : $the_client_secret_test;
    }

    public function output_inline_notification( $title, $body, $notification_type=false )
    {
        $extra_class_text=''; $extra_class_bg='';
        if ($notification_type==='is-notification') {
            $extra_class_text = 'notification-text';
            $extra_class_bg = 'notification-bg';
        } else if ($notification_type==='is-error') {
            $extra_class_text = 'errored-text';
            $extra_class_bg = 'errored-bg';
        } else if ($notification_type==='is-success') {
            $extra_class_text = 'success-text';
            $extra_class_bg = 'success-bg';
        }

        $error_output = '<h2 class="section-heading '.$extra_class_text.'">'.$title.'</h2>';
        $error_output .= '<hr class="header-lower-rule '.$extra_class_bg.' " />';
        $error_output .= '<div class="payment-err">';
        $error_output .= '<div class="notification-wrapper">';
        $error_output .= '<div class="mtii_reg_errors">';
        $error_output .= '<h5 class="'.$extra_class_text.'">'.$body.'</h5>';
        $error_output .= '</div>';
        $error_output .= '</div>';
        $error_output .= '</div>';
        return $error_output;
    }

    public function in_arrayi($needle, $haystack)
    {
        $needle = strtoupper(str_replace('  ', ' ', $needle));
        return in_array(trim($needle), array_map(array($this, 'capitalize_it_and_trim'), $haystack));
    }

    public function capitalize_it_and_trim($val)
    {
        return trim(strtoupper(str_replace('  ', ' ', $val)));
    }

    public function add_organization_to_db_list($registered_organization, $is_ngo=false)
    {
        $all_org_array = json_decode($this->get_all_ngos_and_coop($is_ngo));
        $org = $this->capitalize_it_and_trim($registered_organization);
        if (!$this->in_arrayi($org, $all_org_array)) {
            $all_org_array[] = $org;
        } else {
            $org_name_index = array_search($org, array_map(array($this, 'capitalize_it_and_trim'), $all_org_array));
            $all_org_array[$org_name_index] = $org;
        }
        if ($is_ngo) {
            update_option('all_ngos', json_encode($all_org_array));
        } else {
            update_option('all_coops', json_encode($all_org_array));
        }
    }

    public function get_file_uploads_in_options()
    {
        $file_uploads = get_option('file_uploads_for_Replacement');
        return json_decode($file_uploads, true);
    }

    public function add_array_as_option($main_option_key, $invoice_number, $inner_option_key, $option_value)
    {
        $option_obtained = get_option($main_option_key);
        $option_obtained_as_array = $option_obtained ? json_decode($option_obtained, true) : array();
        $option_obtained_as_array[$invoice_number][$inner_option_key] = $option_value;
        $new_value_to_save = json_encode($option_obtained_as_array);
        $the_update = update_option($main_option_key, $new_value_to_save);
        if (!$the_update) {
            return "There is an Error during Addition";
        } else {
            unset($_FILES);
            return "Document uploaded and added to Database";
        }
    }
}