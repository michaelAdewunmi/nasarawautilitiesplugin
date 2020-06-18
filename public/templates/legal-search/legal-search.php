<?php
use MtiiUtilities\LegalSearchRegistration;

$reg_util = new LegalSearchRegistration;
echo $reg_util->get_all_form_errors(); //Show Errors if there is any error from the validated input.
$invoice_error = $reg_util->check_if_legal_search_invoice_is_used();
if ($invoice_error && $reg_util->get_upload_and_reg_success()!="All Processes Done") :
    echo $invoice_error;
else :
    if ($reg_util->get_upload_and_reg_success()==="All Processes Done") {
        ?>
        <div class="section-body">
            <h2 class="section-heading">Records successfully Added</h2>
            <hr class="header-lower-rule" />
            <div class="payment-err">
                <div class="notification-wrapper">
                    <div class="mtii_reg_errors">
                        <h2 style="color: #34b38a;">
                            You have successfully registered for a Legal Search.
                            You will be notified with search details as soon as it is approved by the admin.
                            Thank you!
                        </h2>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        ?>
        <form name="" id="paymentform" action="" method="post" enctype="multipart/form-data" novalidate="novalidate">
            <?php
            if (isset($errors_array) && count($errors_array)>0) {
                echo '<p class="err-notification errored-text">'.$errors_array["general"].'</p>';
            } else {
                ?>
                <p class="inline-input">
                    <label for="payee_names">Your Full Name</label>
                        <?php $reg_util->get_input_or_placeholder_text('applicant_full_name', 'text'); ?>
                    <input
                        type="hidden" name="main_registration_nonce"
                        value="<?php echo wp_create_nonce('main-registration-nonce') ?>"
                    />
                </p>
                <p class="inline-input">
                    <label for="payee_names">Phone Number</label>
                    <?php $reg_util->get_input_or_placeholder_text('phone_number', 'text'); ?>
                </p>
                <p class="inline-input">
                    <label for="payee_names">Email Adress</label>
                    <?php $reg_util->get_input_or_placeholder_text('email', 'text'); ?>
                </p>
                <p class="inline-input">
                    <label for="payee_names">Organization</label>
                    <?php $reg_util->get_input_or_placeholder_text('organization', 'text'); ?>
                </p>
                <p class="inline-input">
                    <label for="payee_names">Name of Ngo/Cooperative</label>
                    <?php $reg_util->get_input_or_placeholder_text('name_of_ngo_or_cooperative', 'text'); ?>
                </p>
                <p class="inline-input">
                    <label for="payee_names">Certificate Number</label>
                    <?php $reg_util->get_input_or_placeholder_text('certificate_number', 'text'); ?>
                </p>
                <p class="inline-input">
                    <label for="payee_names">Date Certificate was Issued</label>
                    <?php $reg_util->get_input_or_placeholder_text('date_cert_was_issued', 'date'); ?>
                </p>
                <p class="inline-input" style="display: block;">
                    <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Submit" />
                </p>
                <?php
            }
            ?>
        </form>
        <?php
    }
endif;
?>