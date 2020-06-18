<?php
/**
 * The file that handles outputting Errrors
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
namespace MtiiUtilities;

/**
 * The file that handles outputting Errrors
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class OutputErrors
{
    protected function get_error_text()
    {
        return '<p>Your Invoice was generated but there was a problem and this process cannot continue with payment.'.
                ' Please note the heading of this error page and save your invoice number somewhere. '.
                'It is advised that you contact the Admin with your invoice number and inform them of this error '.
                'before continuing with payment. Sorry for the Inconvenience. Thank you!'.
            '</p>';
    }

    protected function get_server_error_text()
    {
        return '<p>Your Invoice was generated but there was a problem from our server. Please DO NOT continue'.
                ' with payment. Note the heading on this error page and save your invoice number somewhere. '.
                'It is advised that you contact the Admin with your invoice number and inform them of this error '.
                '. Sorry for the Inconvenience. Thank you!'.
            '</p>';
    }

    protected function get_invoice_addition_error_text()
    {
        return '<p>Your Invoice was generated but wasnt saved on our server. Please DO NOT continue'.
                ' with payment. Note the heading on this error page and save your invoice number somewhere. '.
                'It is advised that you contact the Admin with your invoice number before proceeding for payment'.
                '. Sorry for the Inconvenience. Thank you!'.
            '</p>';
    }

    protected function get_invoice_duplication_error_text()
    {
        return '<p>Your Invoice was generated but it was discovered that it is a duplicate and has '.
                'previously been generated. Pease do not proceed with payment.'.
                '. Note the heading on this error page and save your invoice number somewhere. '.
                'It is advised that you contact the Admin with your invoice number before proceeding for payment'.
                '. Sorry for the Inconvenience. Thank you!'.
            '</p>';
    }

    protected function get_invoice_used_error_text()
    {
        return '<p>Your Invoice was generated but it was discovered that it is a used invoiced and has '.
                'previously been generated and paid for. Pease reach out to the Admin before proceeding to'.
                'generate a new invoice. Sorry for the Inconvenience. Thank you!'.
            '</p>';
    }

    public function duplicate_request_reference_error($invoice_number)
    {
        echo '<div class="section-body">'.
            '<h2 class="section-heading errored-text">Error! Used Ref</h2>'.
            '<hr class="header-lower-rule errored-bg" />'.
            '<div class="payment-err">'.
                '<div class="notification-wrapper">'.
                    '<h2 class="section-heading errored-text">Your invoice Number is '.
                        $invoice_number.'</h2>'.
                    $this->get_error_text().
                '</div>'.
            '</div>'.
        '</div>';
    }

    public function strange_request_reference_error($invoice_number)
    {
        echo '<div class="section-body">'.
            '<h2 class="section-heading errored-text">Error! Unexpected Ref</h2>'.
            '<hr class="header-lower-rule errored-bg" />'.
            '<div class="payment-err">'.
                '<div class="notification-wrapper">'.
                    '<h2 class="section-heading errored-text">Your invoice Number is '.
                        $invoice_number.'</h2>'.
                    $this->get_error_text().
                '</div>'.
            '</div>'.
        '</div>';
    }

    public function unknown_request_reference_error($invoice_number)
    {
        echo '<div class="section-body">'.
            '<h2 class="section-heading errored-text">Error! Unknown Ref</h2>'.
            '<hr class="header-lower-rule errored-bg" />'.
            '<div class="payment-err">'.
                '<div class="notification-wrapper">'.
                    '<h2 class="section-heading errored-text">Your invoice Number is '.
                        $invoice_number.'</h2>'.
                    $this->get_error_text().
                '</div>'.
            '</div>'.
        '</div>';
    }

    public function server_reference_error($invoice_number)
    {
        echo '<div class="section-body">'.
            '<h2 class="section-heading errored-text">Error! Server Error</h2>'.
            '<hr class="header-lower-rule errored-bg" />'.
            '<div class="payment-err">'.
                '<div class="notification-wrapper">'.
                    '<h2 class="section-heading errored-text">Your invoice Number is '.
                        $invoice_number.'</h2>'.
                    $this->get_server_error_text().
                '</div>'.
            '</div>'.
        '</div>';
    }

    public function invoice_addition_error($invoice_number)
    {
        echo '<div class="section-body">'.
            '<h2 class="section-heading errored-text">Error! Invoice Not Added!</h2>'.
            '<hr class="header-lower-rule errored-bg" />'.
            '<div class="payment-err">'.
                '<div class="notification-wrapper">'.
                    '<h2 class="section-heading errored-text">Your invoice Number is '.
                        $invoice_number.'</h2>'.
                    $this->get_invoice_addition_error_text().
                '</div>'.
            '</div>'.
        '</div>';
    }

    public function invoice_duplication_error($invoice_number)
    {
        echo '<div class="section-body">'.
            '<h2 class="section-heading errored-text">Error! Duplicate Invoice!</h2>'.
            '<hr class="header-lower-rule errored-bg" />'.
            '<div class="payment-err">'.
                '<div class="notification-wrapper">'.
                    '<h2 class="section-heading errored-text">Your invoice Number is '.
                        $invoice_number.'</h2>'.
                    $this->get_invoice_duplication_error_text().
                '</div>'.
            '</div>'.
        '</div>';
    }

    public function invoice_used_error($invoice_number)
    {
        echo '<div class="section-body">'.
            '<h2 class="section-heading errored-text">Error! Used Invoice!</h2>'.
            '<hr class="header-lower-rule errored-bg" />'.
            '<div class="payment-err">'.
                '<div class="notification-wrapper">'.
                    '<h2 class="section-heading errored-text">The Invoice Generated is already Used. The invoice number is '.
                        $invoice_number.'</h2>'.
                    $this->get_invoice_used_error_text().
                '</div>'.
            '</div>'.
        '</div>';
    }
}
?>
