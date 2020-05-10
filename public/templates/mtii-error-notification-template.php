<?php
/**
 * The file that handles outputting all errors
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
if (isset($_REQUEST['err']) && $_REQUEST['err']=='general') :
    ?>
<div class="section-body">
    <h2 class="section-heading errored-text">Error!</h2>
    <hr class="header-lower-rule errored-bg" />
    <div class="payment-err">
        <div class="notification-wrapper">
            <p>There is a problem and This process cannot continue.
                Please Contact Admin for further Assistance. Thank you.
            </p>
        </div>
    </div>
</div>
<?php elseif (isset($_REQUEST['err']) && $_REQUEST['err']=='invoicereqref') : ?>
    <div class="section-body">
        <h2 class="section-heading errored-text">Error! Used Ref</h2>
        <hr class="header-lower-rule errored-bg" />
        <div class="payment-err">
            <div class="notification-wrapper">
                <p>
                    Sorry! There was a request Reference Error and we cannot proceed with invoice generation!
                    You can return to the payment Page, refresh and try again. Sorry for the inconvenience
                </p>
            </div>
        </div>
    </div>
<?php elseif (isset($_REQUEST['err']) && $_REQUEST['err']=='Invoice added to Database') : ?>
    <div class="section-body">
        <h2 class="section-heading errored-text">Error! Please contact Admin Now</h2>
        <hr class="header-lower-rule errored-bg" />
        <div class="payment-err">
            <div class="notification-wrapper">
                <p>Your Invoice was generated but There is a problem and This process cannot continue with payment.
                    It is advised that you contact Admin before continuing with payment. Sorry for the Inconvenience.
                    Thank you!
                </p>
            </div>
        </div>
    </div>
<?php endif;