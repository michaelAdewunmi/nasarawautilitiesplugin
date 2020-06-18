<?php
use MtiiUtilities\BusinessPremiseRenewal;
$reg_util = new BusinessPremiseRenewal;
echo $reg_util->get_all_form_errors(); //Show Errors if there is any error from the validated input.
$invoice_error = $reg_util->check_if_renewal_invoice_is_used();
if ($invoice_error && $reg_util->recertification_successfully_done!="Yes") :
    echo $invoice_error;
elseif (isset($_REQUEST["is_new"]) && $_REQUEST["is_new"]==1) :
    if (!$reg_util->hide_recertification_input) :
    ?>
    <p class="dashboard-p verify-first">
        Note: Please be informed that this Registration is ONLY for organizations that
        are already registered (online or offline). If you are a new Organization without any prior registration,
        please return to the invoice page and generate invoice for fresh registration. Also, If you
        have already registered offline, please <a style="text-decoration: underline"
        href="<?php echo site_url('/user-dashboard?&do=reg'); ?>">CLICK HERE</a>
    </p>
    <form name="" id="paymentform" action="" method="post" novalidate="novalidate">
        <label for="payee_names">Please type in Your company Name</label>
                <?php $reg_util->get_input_or_placeholder_text('company_name', 'text', '', '', 0, 'show-border'); ?>
        <input
            type="hidden" name="main_registration_nonce"
            value="<?php echo wp_create_nonce('main-registration-nonce') ?>"
        />
        <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Submit" />
    </form>
    <?php endif; ?>
<?php else : ?>
    <p class="dashboard-p verify-first">Please type in the expired invoice Number if you previously registered online
        or <a style="text-decoration: underline"
            href="<?php echo site_url().$_SERVER['REQUEST_URI'].'&is_new=1'?>">
            CLICK HERE</a> if have you only registered offline.
    </p>
    <form name="" id="paymentform" action="" method="post" novalidate="novalidate">
        <?php
        if (isset($errors_array) && count($errors_array)>0) {
            echo '<p class="err-notification errored-text">'.$errors_array["general"].'</p>';
        } else {
            ?>
        <p>
            <label for="payee_names">Expired Invoice Number</label>
                <?php $reg_util->get_input_or_placeholder_text('expired_invoice_number', 'text'); ?>
            <input
                type="hidden" name="main_registration_nonce"
                value="<?php echo wp_create_nonce('main-registration-nonce') ?>"
            />
            <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Submit" />
        </p>
            <?php
        }
        ?>
    </form>
    <?php
endif;
    ?>
