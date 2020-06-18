<div class="section-body">
    <?php if (isset($_REQUEST['is-for-upload']) && $_REQUEST['is-for-upload']=1) : ?>
    <p class="dashboard-p verify-first">
        Note: Please Paste Invoice Number here before you continue your upload!
    </p>
    <?php else: ?>
    <p class="dashboard-p verify-first">
        Note: Paste the Invoice number in the input below to verify your payment before proceeding with
        the registration.
    </p>
    <?php endif; ?>
    <form name="invoice-payment-verification-form" id="paymentform" action="" method="post" novalidate="novalidate">
        <?php
        if (isset($errors_array) && count($errors_array)>0) {
            echo '<p class="err-notification errored-text">'.$errors_array["general"].'</p>';
        }
        ?>
        <p>
            <label for="payee_names">Invoice Number</label>
            <input
                type="text" name="invoice_number" id="payee_names"
                class="input" value="<?php echo isset($_POST["invoice_number"]) ? $_POST["invoice_number"] : ""; ?>"
                size="20" autocapitalize="off"
            >
        </p>
        <input
            type="hidden" name="user_invoice_payment_nonce" class="input"
            value="<?php echo wp_create_nonce('user_invoice_payment_nonce') ?>"
        >
        <input class="round-btn-mtii"  name="submit" type="submit" value="Submit" />
    </form>
</div>