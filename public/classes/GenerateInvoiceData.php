<?php
/**
 * This file basically generates the data being sent to the Parkway Api
 * for Invoice generation
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

use MtiiUtilities\MtiiRelatedInformation;
/**
 * This class basically generates the data being sent to the Parkway Api
 * for Invoice generation
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class GenerateInvoiceData
{
    /**
     * The Revenue Head Id of the invoice to be generated
     *
     * @since  1.0.0
     * @access public
     * @var    string   $revenue_head_id The Revenue Head Id of the invoice to be generated.
     */
    public $revenue_head_id;

    /**
     * The category the payment is found e.g NGO, Cooperative e.t.c.
     *
     * @since  1.0.0
     * @access public
     * @var    string   $category The category the payment is found e.g NGO, Cooperative e.t.c.
     */
    protected $_category;

    /**
     * The Nice Name for category the payment is found e.g NGO, Cooperative e.t.c.
     *
     * @since  1.0.0
     * @access public
     * @var    string   $category_nice_name The category the payment is found e.g NGO, Cooperative e.t.c.
     */
    protected $category_nice_name;

    /**
     * The Amount to reflect in the Invoice
     *
     * @since  1.0.0
     * @access public
     * @var    string   $amount_to_pay The Amount to reflect in the Invoice
     */
    public $amount_to_pay;


    /**
     * Other Necesssary Information
     *
     * @since  1.0.0
     * @access public
     * @var    string   $purpose_of_payment Other information as regards the invoice
     */
    public $other_info = array();

    /**
     * The description of the Invoice
     *
     * @since  1.0.0
     * @access public
     * @var    string   $invoice_description The description of the Invoice
     */
    public $invoice_description;

    /**
     * Nasarawa's Invoice sub category
     *
     * @since  1.0.0
     * @access public
     * @var    string   $sub_category Required for determining if form is required
     */
    public $sub_category = 'N/A';

    /**
     * Organization ID meant to be defined if the invoice is for recertification.
     *
     * @since  1.0.0
     * @access public
     * @var    string   $org_id_for_recertification Required for determining the organization the recertification is meant for
     */
    //public $org_id_for_recertification = null;

    /**
     * Organization Name meant to be defined if the invoice is for recertification.
     *
     * @since  1.0.0
     * @access public
     * @var    string   $org_name_for_recertification Required for determining the organization the recertification is meant for
     */
    //public $org_name_for_recertification = null;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $request_info     The information about the categrory the payment is to be made (embedded in Query Params)
     * @param string $payment_category The category payment falls into under the registration category

     * @since  1.0.0
     * @return void
     */
    public function __construct( $request_info, $payment_category )
    {
        $coop = urlencode(openssl_encrypt("cooperative-soc", "AES-128-ECB", "X340&2&230rTHJ34"));
        $ngo = urlencode(openssl_encrypt("ngo-cbo", "AES-128-ECB", "X340&2&230rTHJ34"));
        $biz_prem = urlencode(openssl_encrypt("business-premise", "AES-128-ECB", "X340&2&230rTHJ34"));
        $fertilizers = urlencode(openssl_encrypt("fertilizer-plants", "AES-128-ECB", "X340&2&230rTHJ34"));
        $packaging = urlencode(openssl_encrypt("sacks-packaging-akwanga", "AES-128-ECB", "X340&2&230rTHJ34"));
        $beef_processing = urlencode(openssl_encrypt("beef-proc-masaka-karu", "AES-128-ECB", "X340&2&230rTHJ34"));
        $haulage = urlencode(openssl_encrypt("haulage-fee-collection", "AES-128-ECB", "X340&2&230rTHJ34"));
        $others = urlencode(openssl_encrypt("others", "AES-128-ECB", "X340&2&230rTHJ34"));
        $query_param = isset($_REQUEST['do']) ? urlencode($_REQUEST['do']) : null;
        $query_param_catg = isset($_REQUEST['catg']) ? urlencode($_REQUEST['catg']) : null;

        if ($request_info===$coop) {
            $this->_category = 'cooperative';
            $this->category_nice_name = 'Cooperative';
        } else if ($request_info===$ngo) {
            $this->_category = 'ngo';
            $this->category_nice_name = 'NGOs and CBOs';
        } else if ($request_info===$biz_prem) {
            $this->_category = 'biz_prem';
            $this->category_nice_name = 'Business Premise';
        } else if ($request_info===$fertilizers) {
            $this->_category = 'fertilizer_plants';
            $this->category_nice_name = 'Fertilizer Plants';
        } else if ($request_info===$packaging) {
            $this->_category = 'packaging';
            $this->category_nice_name = 'Packaging';
        } else if ($request_info===$haulage) {
            $this->_category = 'haulage';
            $this->category_nice_name = 'Haulage';
        } else if ($request_info===$others) {
            $this->_category = 'others';
            $this->category_nice_name = 'Other Payments';
        }

        $mtii_is_live = get_option('mtii_is_live');
        $is_live = isset($mtii_is_live) ? $mtii_is_live : 'off';
        if ($is_live==='on') {
            $this->set_necessary_invoice_data_for_live_env($payment_category);
        } else {
            $this->set_necessary_invoice_data($payment_category);
        }
    }

    public function get_category_nice_name() {
        return $this->category_nice_name;
    }

    /**
     * Set All Necessary data required for generation of invoice with respect to the category the registration is to be done
     *
     * @param string $payment_category The category payment falls into under the registration category
     *
     * @return void
     */
    protected function set_necessary_invoice_data($payment_category)
    {
        if ('cooperative'===$this->_category) {
            $this->revenue_head_id = 112;
            $this->set_invoice_data_for_coop_and_ngo($payment_category);
        } else if ('ngo'===$this->_category) {
            $this->revenue_head_id = 129;
            $this->set_invoice_data_for_coop_and_ngo($payment_category);
        } else if ('biz_prem'===$this->_category) {
            $this->revenue_head_id = 106;
            $this->set_invoice_data_for_biz_prem($payment_category);
        } else if ('fertilizer_plants'===$this->_category) {
            if (!isset($_POST['plant_location']) || $_POST['plant_location']=='') {
                return;
            }
            $this->other_info["plant_location"] = $_POST["plant_location"];
            $this->other_info["payee_organization"] = $_POST["payee_organization"];
            $this->amount_to_pay = $_POST["payment_fee_user_typed"];
            if ('fert_lafia'===$_POST['plant_location']) {
                $this->revenue_head_id = 503;
                $this->set_invoice_data_for_fert_lafia($payment_category);
            } else if ('fert_akwanga'===$_POST['plant_location']) {
                $this->revenue_head_id = 504;
                $this->set_invoice_data_for_fert_akwanga($payment_category);
            } else if ('fert_keffi'===$_POST['plant_location']) {
                $this->revenue_head_id = 505;
                $this->set_invoice_data_for_fert_keffi($payment_category);
            }
        } else if ('packaging'===$this->_category) {
            $this->revenue_head_id = 111;
            $this->set_invoice_data_for_sacks_and_packaging($payment_category);
        } else if ('haulage'===$this->_category) {
            $this->revenue_head_id = 138;
            $this->set_invoice_data_for_haulage_fee($payment_category);
        }  else if ('others'===$this->_category) {
            $this->revenue_head_id = 1308;
            $this->set_invoice_data_for_others($payment_category);
        }

        // else if ('fert_lafia'===$this->_category) {
        //     $this->revenue_head_id = 503;
        //     $this->set_invoice_data_for_fert_lafia($payment_category);
        // } else if ('fert_akwanga'===$this->_category) {
        //     $this->revenue_head_id = 504;
        //     $this->set_invoice_data_for_fert_akwanga($payment_category);
        // } else if ('fert_keffi'===$this->_category) {
        //     $this->revenue_head_id = 505;
        //     $this->set_invoice_data_for_fert_keffi($payment_category);
        // }
    }

    /**
     * Set All Necessary data required for generation of invoice with respect to the category the registration is to be done
     *
     * @param string $payment_category The category payment falls into under the registration category
     *
     * @return void
     */
    protected function set_necessary_invoice_data_for_live_env($payment_category)
    {
        if ('cooperative'===$this->_category) {
            $this->revenue_head_id = 169;
            $this->set_invoice_data_for_coop_and_ngo($payment_category);
        } else if ('biz_prem'===$this->_category) {
            $this->revenue_head_id = 177;
            $this->set_invoice_data_for_biz_prem($payment_category);
        } else if ('ngo'===$this->_category) {
            $this->revenue_head_id = 129;
            $this->set_invoice_data_for_coop_and_ngo($payment_category);
        } else if ('haulage'===$this->_category) {
            $this->revenue_head_id = 138;
            $this->set_invoice_data_for_haulage_fee($payment_category);
        } else if ('others'===$this->_category) {
            $this->revenue_head_id = 1003;
            $this->set_invoice_data_for_others($payment_category);
        }
        //else if ('fert_lafia'===$this->_category) {
        //     $this->revenue_head_id = 503;
        //     $this->set_invoice_data_for_fert_lafia($payment_category);
        // } else if ('fert_akwanga'===$this->_category) {
        //     $this->revenue_head_id = 504;
        //     $this->set_invoice_data_for_fert_akwanga($payment_category);
        // } else if ('fert_keffi'===$this->_category) {
        //     $this->revenue_head_id = 505;
        //     $this->set_invoice_data_for_fert_keffi($payment_category);
        // } else if ('packaging'===$this->_category) {
        //     $this->revenue_head_id = 111;
        //     $this->set_invoice_data_for_sacks_and_packaging($payment_category);
        // }
    }


    /**
     * Set All Necessary data required for generation of invoice as per cooperative registration
     *
     * @param string $payment_category The category payment falls into under the registration category
     *
     * @return void
     */
    protected function set_invoice_data_for_coop_and_ngo($payment_category) {
        if ($payment_category === "fresh") {
            $this->invoice_description = "Payment for Fresh Registration";
            $this->amount_to_pay = 10000;
            $this->sub_category = "fresh";
        } else if ($payment_category === "re-certification") {
            $this->invoice_description = "Payment for Re Certification";
            $this->amount_to_pay = 5000;
            $this->sub_category = "re-certification";
        } else if ($payment_category === "replacement") {
            $this->invoice_description = "Payment for Certificate Replacement";
            $this->amount_to_pay = 5000;
            $this->sub_category = "replacement";
        } else if ($payment_category === "legal-search") {
            $this->invoice_description = "Payment for Legal Search";
            $this->amount_to_pay = 3000;
            $this->sub_category = "legal-search";
        } else {
            $this->amount_to_pay = null;
        }
    }

    /**
     * Set All Necessary data required for generation of invoice as per Business Premises registration
     *
     * @param string $payment_category The category payment falls into under the registration category
     *
     * @return void
     */
    protected function set_invoice_data_for_biz_prem($payment_category) {
        $biz_prem = new MtiiRelatedInformation;
        $biz_prem_new_reg = $biz_prem->get_all_biz_premises_amount('mtii_new_registration');
        $biz_prem_renewal = $biz_prem->get_all_biz_premises_amount('mtii_renewal');
        if ($_POST["payment-type-category"]==='Fresh Registration') {
            $this->sub_category = "fresh";
            $this->invoice_description = "Payment for Business Premise Fresh Registration";
            $this->amount_to_pay =isset($biz_prem_new_reg[$payment_category]) ? $biz_prem_new_reg[$payment_category] : 0;
        } else {
            $this->invoice_description = "Payment for Business Premise Registration Renewal";
            $this->sub_category = "renewal";
            $this->amount_to_pay =isset($biz_prem_renewal[$payment_category]) ? $biz_prem_renewal[$payment_category] : 0;
        }
    }

    /**
     * Set All Necessary data required for generation of invoice as per Fertilizer Lafia registration
     *
     * @param string $payment_category The category payment falls into under the registration category
     *
     * @return void
     */
    protected function set_invoice_data_for_fert_lafia($payment_category)
    {
        $this->invoice_description = "Payment for Fertilizer, Lafia Registration";
        $this->sub_category = "fert_lafia";
        // $this->amount_to_pay = 65000000;
    }

    /**
     * Set All Necessary data required for generation of invoice as per Fertilizer Lafia registration
     *
     * @param string $payment_category The category payment falls into under the registration category
     *
     * @return void
     */
    protected function set_invoice_data_for_fert_akwanga($payment_category)
    {
        $this->invoice_description = "Payment for Fertilizer, Akwanga Registration";
        //$this->amount_to_pay = 3000000;
        $this->sub_category = "fert_akwanga";

    }


    /**
     * Set All Necessary data required for generation of invoice as per Fertilizer Lafia registration
     *
     * @param string $payment_category The category payment falls into under the registration category
     *
     * @return void
     */
    protected function set_invoice_data_for_fert_keffi($payment_category)
    {
        $this->invoice_description = "Payment for Fertilizer, Keffi Registration";
        //$this->amount_to_pay = 7000000;
        $this->sub_category = "fert_keffi";
    }

    /**
     * Set All Necessary data required for generation of invoice as per Fertilizer Lafia registration
     *
     * @param string $payment_category The category payment falls into under the registration category
     *
     * @return void
     */
    protected function set_invoice_data_for_sacks_and_packaging($payment_category)
    {
        $this->invoice_description = "Payment for Sacks and Packaging Registration, Akwanga";
        $this->amount_to_pay = 8912485;
    }

    /**
     * Set All Necessary extra data required for generation of invoice for haulage fee
     *
     * @param string $payment_category The category payment falls into under the registration category
     *
     * @return void
     */
    protected function set_invoice_data_for_haulage_fee($payment_category)
    {
        $this->invoice_description = "Payment for Haulage Fee";
        $this->amount_to_pay = $_POST["payment_fee_user_typed"];
        $this->other_info["senatorial_zone"] = $_POST["senatorial_zone"];
        $this->other_info["payee_organization"] = $_POST["payee_organization"];
    }

    /**
     * Set All Necessary extra data required for generation of invoice for haulage fee
     *
     * @param string $payment_category The category payment falls into under the registration category
     *
     * @return void
     */
    protected function set_invoice_data_for_others($payment_category)
    {
        $this->invoice_description = "Payment for Other Fees";
        $this->amount_to_pay = $_POST["payment_fee_user_typed"];
        $this->other_info["purpose_of_payment"] = $_POST["purpose_of_payment"];
    }

    /**
     * A Function to generate to generate random strings
     *
     * @param integer $length The Length of the Random String to be generated
     *
     * @return string $randomString The random String generated
     */
    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}