<?php
use MtiiUtilities\CertificateReplacementReg;

$reg_util = new CertificateReplacementReg;
echo $reg_util->get_all_form_errors(); //Show Errors if there is any error from the validated input.
$reg_catg = $reg_util->get_replacement_category();
$invoice_error = $reg_util->check_if_replacement_invoice_is_used();
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
                            You have successfully registered for certificate replacement.
                            You will be notified as soon as the registration is approved by the admin.
                            Thank you!
                        </h2>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        ?>
        <p class="dashboard-p verify-first">
            NOTICE: Please, You are to take note that lost or damaged certificate can only be reproduced once.
            Any further loss will warrant fresh registration. Thank you for understanding.
        </p>
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
                <p><em style="color: #5d47e6; font-weight: 700;">Please Ensure Each Upload is not greater than 500KB</em></p>
                <p class="inline-input" style="display: block;">
                    <?php echo $reg_util->create_files_input('Upload Police Extract', 'police_extract'); ?>
                </p>
                <p>
                    <?php echo $reg_util->create_files_input('Upload Court Affidavit', 'court_affidavit'); ?>
                </p>
                <p class="inline-input">
                    <label for="payee_names">Position/Rank in Society</label>&nbsp;&nbsp;
                    <?php echo $reg_util->select_input_creator('position_rank_in_the_society', 'Please Select Position'); ?>
                </p>
                <p class="inline-input">
                    <label for="payee_names">Name of <?php echo $reg_catg; ?></label>
                    <?php $reg_util->get_input_or_placeholder_text('name_of_society_or_organization', 'text'); ?>
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