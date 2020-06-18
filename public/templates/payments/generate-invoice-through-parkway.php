<?php
/**
 * This is the file that handles the request with the cbs
 * Please Note: The [$user] Object is gotten from the file where this file
 * was included. Also, any other varible used without being declared is already
 * declared in that same file.
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */

use Unirest\Request\Body;
use MtiiUtilities\GenerateInvoiceData;
use MtiiUtilities\TasksPerformer;
use MtiiUtilities\OutputErrors;

Unirest\Request::verifyPeer(false);
if (isset($_POST["submit"])) {
    if (!isset($_REQUEST['cbs_return']) && wp_verify_nonce($_POST['user_invoice_nonce'], 'user_invoice_nonce')) {

        $errors_array = array();
        if (!isset($_POST["payee_names"]) || !isset($_POST["payee_email"]) || !isset($_POST["payee_address"])
            || !isset($_POST["payee_phone"]) || trim($_POST["payee_names"])==''
            || trim($_POST["payee_email"])=='' || trim($_POST["payee_address"])=='' || trim($_POST["payee_phone"])==''
            || (isset($_POST["payee_tax_number"]) && trim($_POST["payee_tax_number"])=='')
            || (isset($_POST["payment-type"]) && trim($_POST["payment-type"])=='')
            || (isset($_POST["payment_fee_user_typed"]) && $_POST["payment_fee_user_typed"]=='')
            || (isset($_POST["purpose_of_payment"]) && $_POST["purpose_of_payment"]=='')
            || (isset($_POST["senatorial_zone"]) && $_POST["senatorial_zone"]=='')
            || (isset($_POST["payee_organization"]) && $_POST["payee_organization"]=='')
            || (isset($_POST["plant_location"]) && $_POST["plant_location"]=='')
        ) {
            $errors_array["general"] = "All Fields are compulsory! Please ensure all fields are filled and then try again";
        } else if (isset($_POST["payment_fee_user_typed"]) && !is_numeric($_POST["payment_fee_user_typed"])) {
            $errors_array["general"] = "Amount should be strictly Numbers. please remove any comma or special character";
        } else {
            $payment_type = isset($_POST["payment-type"]) ? trim($_POST["payment-type"]) : null;
            if (isset($_REQUEST['catg'])) {
                $data_to_generate = new GenerateInvoiceData(urlencode($_REQUEST['catg']), $payment_type);
            } else {
                echo die(
                    '<script>window.location.href="'.
                        site_url('/user-dashboard?do=err&err=general').'"</script>'
                );
            }
            $used_reference = get_option('mtii_request_references');
            $used_reference = $used_reference=='' ? array() : $used_reference;
            $existing_reference = array();
            $ref_to_use = '';
            foreach ($used_reference as $key => $value) {
                $existing_reference[] = $key;
                if ($value == 'not yet used') {
                    $ref_to_use = $key;
                }
            }

            if ($ref_to_use=='') {
                $ref_to_use = $data_to_generate->generateRandomString();
                while (in_array($ref_to_use, $existing_reference)) {
                    $ref_to_use = $data_to_generate->generateRandomString();
                }
                $used_reference[$ref_to_use] = "not yet used";
                $existing_reference[] = $ref_to_use;
                update_option('mtii_request_references', $used_reference);
                include_once 'send_request_to_parkway_cbs.php';
            } else if (in_array($ref_to_use, $existing_reference) && $used_reference[$ref_to_use] = "not yet used") {
                include_once 'send_request_to_parkway_cbs.php';
            } else {
                '<script>window.location.href="'.site_url('/user-dashboard?do=err&err=invoicereqref1').'"</script>';
            }
        }
    }
}