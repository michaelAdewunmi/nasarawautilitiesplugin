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

class Generate_Invoice_Data_For_Api_Request
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
    protected $category;

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
     * The description of the Invoice
     *
     * @since  1.0.0
     * @access public
     * @var    string   $invoice_description The description of the Invoice
     */
    public $invoice_description;

    /**
     * Nassarawa's client ID required for authentication of every request by Parkway CBS
     *
     * @since  1.0.0
     * @access public
     * @var    string   $client_id Required for authentication of every request by Parkway CBS

     */
    public $client_id;

    /**
     * Nassarawa's client Secret required for authentication of every request by Parkway CBS
     *
     * @since  1.0.0
     * @access public
     * @var    string   $client_secret Required for authentication of every request by Parkway CBS
     */
    public $client_secret;

    /**
     * Nassarawa's Invoice sub category
     *
     * @since  1.0.0
     * @access public
     * @var    string   $sub_category Required for determining if form is required
     */
    public $sub_category = 'N/A';

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
        $coop = urlencode(openssl_encrypt("cooperative-soc", "AES-128-ECB", "secretecode"));
        $ngo = urlencode(openssl_encrypt("ngo-cbo", "AES-128-ECB", "secretecode"));
        $biz_prem = urlencode(openssl_encrypt("business-premise", "AES-128-ECB", "secretecode"));
        $lafia = urlencode(openssl_encrypt("fertilizer-lafia", "AES-128-ECB", "secretecode"));
        $akwanga = urlencode(openssl_encrypt("fertilizer-akwanga", "AES-128-ECB", "secretecode"));
        $keffi = urlencode(openssl_encrypt("fertilizer-keffi", "AES-128-ECB", "secretecode"));
        $packaging = urlencode(openssl_encrypt("sacks-packaging-akwanga", "AES-128-ECB", "secretecode"));
        $karu = urlencode(openssl_encrypt("beef-proc-masaka-karu", "AES-128-ECB", "secretecode"));
        $haulage = urlencode(openssl_encrypt("haulage-fee-collection", "AES-128-ECB", "secretecode"));
        $query_param = isset($_REQUEST['do']) ? urlencode($_REQUEST['do']) : null;
        $query_param_catg = isset($_REQUEST['catg']) ? urlencode($_REQUEST['catg']) : null;

        $this->client_id = get_option('live_or_staging')=='mtii_live' ? "SECRETFORANOTHERUSE"
                : "SECRETFORANOTHERUSE=";

        $this->client_secret = get_option('live_or_staging')=='mtii_live' ? "SECRETFORANOTHERUSE"
                : "SECRETFORANOTHERUSE";

        if ($request_info===$coop) {
            $this->category = 'cooperative';
            $this->category_nice_name = 'Cooperative';
        } else if ($request_info===$ngo) {
            $this->category = 'ngo';
        } else if ($request_info===$biz_prem) {
            $this->category = 'biz_prem';
            $this->category_nice_name = 'Business Premise';
        } else if ($request_info===$lafia) {
            $this->category = 'fert_lafia';
        } else if ($request_info===$akwanga) {
            $this->category = 'fert_akwanga';
        } else if ($request_info===$keffi) {
            $this->category = 'fert_keffi';
        }  else if ($request_info===$packaging) {
            $this->category = 'packaging';
        }

        if (get_option('live_or_staging')=='mtii_live') {
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
        if ('cooperative'===$this->category) {
            $this->revenue_head_id = 100;
            $this->set_invoice_data_for_coop_and_ngo($payment_category);
        } else if ('ngo'===$this->category) {
            $this->revenue_head_id = 100;
            $this->set_invoice_data_for_coop_and_ngo($payment_category);
        } else if ('biz_prem'===$this->category) {
            $this->revenue_head_id = 100;
            $this->set_invoice_data_for_biz_prem($payment_category);
        } else if ('fert_lafia'===$this->category) {
            $this->revenue_head_id = 100;
            $this->set_invoice_data_for_fert_lafia($payment_category);
        } else if ('fert_akwanga'===$this->category) {
            $this->revenue_head_id = 100;
            $this->set_invoice_data_for_fert_akwanga($payment_category);
        } else if ('fert_keffi'===$this->category) {
            $this->revenue_head_id = 100;
            $this->set_invoice_data_for_fert_keffi($payment_category);
        } else if ('packaging'===$this->category) {
            $this->revenue_head_id = 100;
            $this->set_invoice_data_for_sacks_and_packaging($payment_category);
        }
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
        if ('cooperative'===$this->category) {
            $this->revenue_head_id = 200;
            $this->set_invoice_data_for_coop_and_ngo($payment_category);
        } else if ('biz_prem'===$this->category) {
            $this->revenue_head_id = 200;
            $this->set_invoice_data_for_biz_prem($payment_category);
        }
        // else if ('ngo'===$this->category) {
        //     $this->revenue_head_id = 200;
        //     $this->set_invoice_data_for_coop_and_ngo($payment_category);
        // } else if ('fert_lafia'===$this->category) {
        //     $this->revenue_head_id = 200;
        //     $this->set_invoice_data_for_fert_lafia($payment_category);
        // } else if ('fert_akwanga'===$this->category) {
        //     $this->revenue_head_id = 200;
        //     $this->set_invoice_data_for_fert_akwanga($payment_category);
        // } else if ('fert_keffi'===$this->category) {
        //     $this->revenue_head_id = 200;
        //     $this->set_invoice_data_for_fert_keffi($payment_category);
        // } else if ('packaging'===$this->category) {
        //     $this->revenue_head_id = 200;
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
            $this->invoice_description = "Payment for Fresh Cooperative Registration";
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
        include_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-parameters-setter-and-getters.php';
        $biz_prem = new Mtii_Parameters_Setter_And_Getter;
        $biz_prem_new_reg = $biz_prem->get_all_biz_premises_amount('mtii_new_registration');
        $biz_prem_renewal = $biz_prem->get_all_biz_premises_amount('mtii_renewal');
        if ($_POST["payment-type-category"]==='Fresh Registration') {
            $this->amount_to_pay =isset($biz_prem_new_reg[$payment_category]) ? $biz_prem_new_reg[$payment_category] : 0;
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
        $this->amount_to_pay = 65000000;
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
        $this->amount_to_pay = 3000000;
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
        $this->amount_to_pay = 7000000;
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