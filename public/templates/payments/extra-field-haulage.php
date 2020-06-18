
<?php if ($query_param_catg===$haulage) : ?>
    <p class="" id="cooperative-payment-type">
        <label for="senatorial_zone">Pick Senatorial zone</label>
        <select name="senatorial_zone" id="senatorial_zone" class="input">
            <option value="">Pick Senatorial zone</option>
            <option value="nasarawa-south">Nasarawa South</option>
            <option value="nasarawa-north">Nasarawa North</option>
            <option value="nasarawa-west">Nasarawa West</option>
        </select>
    </p>
<?php endif; ?>
<p class="" id="cooperative-payment-type">
    <label for="payment_fee_user_typed">Type in the Amount</label>
    <input
        type="number" name="payment_fee_user_typed" id="payment_fee_user_typed"
        class="input" size="20" autocapitalize="off"
        value="<?php echo isset($_POST["payment_fee_user_typed"]) ? $_POST["payment_fee_user_typed"] : ""; ?>"
    >
</p>
<?php if ($query_param_catg==$others) : ?>
<p>
    <label for="purpose_of_payment">Purpose of Payment</label>
    <input
        type="text" name="purpose_of_payment" id="purpose_of_payment"
        class="input" size="20" autocapitalize="off"
        value="<?php echo isset($_POST["purpose_of_payment"]) ? $_POST["purpose_of_payment"] : ""; ?>"
    >
</p>
<?php endif; ?>