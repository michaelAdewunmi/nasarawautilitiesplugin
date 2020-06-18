<?php
/**
 * The file that handles outputting all Sucess Notifications
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */

use MtiiUtilities\TasksPerformer;
if (isset($_REQUEST['succ'])) :
    $task_performer = new TasksPerformer();
    if ($_REQUEST['succ']=='gotopay') :
        if (isset($_REQUEST['invnum'])) : //NbdFf97gUuFE599xVk7ynQ%3D%3D
            $dc = $_REQUEST['invnum'];

            $invnum = openssl_decrypt($dc, "AES-128-ECB", "0jQkL&5S");
            $payment_url =  $task_performer->payment_url."/".$invnum;
            $invoice_url =  $task_performer->invoice_url."/".$invnum;
            ?>
            <div class="section-body">
            <div id="notification">
                <div class="notifier dlarge">
                    <div id="msg"></div>
                    <div id="notification-btn" class="mkinnig-rounded-btn close-notify">Close Notification</div>
                </div>
            </div>
                <h2 class="section-heading">Success!</h2>
                <hr class="header-lower-rule" />
                <div class="notification-wrapper">
                    <?php
                    $inv_num_enc = urlencode(openssl_encrypt($invnum, "AES-128-ECB", "X340&2&230rTHJ34"));
                    ?>
                    <p>
                        Your Invoice Generation was successful. Please click the link below to proceed
                        to the payment page. You can click the blue button to print the Invoice on a new page
                        and come back here to make payment
                    </p>
                    <a class="round-btn-mtii blue-bg" target="_blank"
                        href="<?php echo $invoice_url; ?>/Html">
                        Print Invoice
                    </a>
                    <a class="round-btn-mtii" target="_blank"
                        href="<?php echo $payment_url; ?>">
                        Proceed to Payment
                    </a>
                    <a id="go-reg" data-orgsource="<?php echo $inv_num_enc; ?>"
                        data-thenonce="<?php echo wp_create_nonce('ajax-payment-verify-nonce') ?>"
                        class="round-btn-mtii" href="<?php echo site_url().'?do=reg';?>">Proceed to Registration
                    </a>
                </div>
            </div>
        <?php else : ?>
            <div class="section-body">
                <h2 class="section-heading errored-text">Something has gone wrong</h2>
                <hr class="header-lower-rule" />
                <div class="notification-wrapper">
                    <p>Something went wrong and You should not be here. If you were directed
                        here from the invoice page, Please contact Admin Immediately.
                    </p>
                    <a class="round-btn-mtii"  href="">Return Home</a>
                </div>
            </div>
        <?php endif; ?>
    <?php else :
        echo "<h1>We are here</h1>";
        $payer_message = 'Hello Demilade Young,<br /><br />'.
        'You Have successfully generated an Invoice for the Fresh Cooperative Registration payment'.
        '. Your Invoice Number is <strong>10034897239.</strong> <br /><br />'.
        'You can <a href="https://chckinvoice.xyz/100245959">Click Here</a> to print your invoice '.
        'or simply <a href="https://chckinvoice.xyz/100245959">Click This link</a> to make payment. Thank you.';

        $mail_content = $task_performer->create_email_from_template('Invoice creation Notification Success', $payer_message);

        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail('demmy.young.writes@gmail.com', 'Invoice Notification', $mail_content, $headers);
    ?>
    <?php endif; ?>
<?php endif; ?>