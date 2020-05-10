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

require_once WP_CONTENT_DIR . '/plugins/mtii-utilities/lib/unirest-php/src/Unirest.php';
require_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-utilities-invoice-data-generator.php';
require_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-utilities-task-performer.php';
require_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-utilities-error-output.php';

use Unirest\Request\Body;
Unirest\Request::verifyPeer(false);
if (isset($_POST["submit"])) {
    if (!isset($_REQUEST['cbs_return']) && wp_verify_nonce($_POST['user_invoice_nonce'], 'user_invoice_nonce')) {

        $errors_array = array();
        if (!isset($_POST["payee_names"]) || !isset($_POST["payee_email"]) || !isset($_POST["payee_address"])
            || !isset($_POST["payee_phone"]) || !isset($_POST["payee_tax_number"]) || trim($_POST["payee_names"])==''
            || trim($_POST["payee_email"])=='' || trim($_POST["payee_address"])=='' || trim($_POST["payee_phone"])==''
            || trim($_POST["payee_tax_number"])=='' || trim($_POST["payment-type"])==''
        ) {
            $errors_array["general"] = "All Fields are compulsory! Please ensure all fields are filled and then try again";
        } else {
            if (isset($_REQUEST['catg'])) {
                $data_to_generate = new Generate_Invoice_Data_For_Api_Request(urlencode($_REQUEST['catg']), $_POST['payment-type']);
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