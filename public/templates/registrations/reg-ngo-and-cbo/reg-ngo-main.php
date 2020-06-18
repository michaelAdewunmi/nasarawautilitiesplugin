<?/**
 * Summary.
 *
 * Description.
 *
 * @since Version 3 digits
 */

use MtiiUtilities\NgoAndCboRegistration;
use MtiiUtilities\TasksPerformer;
use MtiiUtilities\MtiiRelatedInformation;

$reg_util = new NgoAndCboRegistration;
$ngo_main_form = $reg_util->get_ngo_cbo_form_data();
echo $reg_util->get_all_form_errors(); //Show Errors if there is any error from the validated input.
$invoice_info = $reg_util->get_invoice_info_from_db();

$read_only = null;
if (!$reg_util->allow_ngo_name_edit()) {
    $read_only = true;
}
if ($reg_util->records_successfully_added()) : ?>
    <div class="section-body">
        <h2 class="section-heading">Registration Successful!</h2>
        <hr class="header-lower-rule" />
        <div class="payment-err">
            <div class="notification-wrapper">
                <div class="mtii_reg_errors">
                    <?php if (isset($_REQUEST["for_edit"]) && $_REQUEST["for_edit"]==1) : ?>
                        <h2 style="color: #34b38a;">Your Edit was successful</h2>
                        <a class="round-btn-mtii" href="<?php echo site_url(
                            "/user-dashboard?do=reg&catg=".$ngo."&is_preview=".
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_ngo=1"
                        ); ?>">Preview Records</a>
                    <?php else : ?>
                        <h5 style="color: #34b38a;">
                            NGO/CBO Records successfully Added and your registration is
                            presently undergoing assessement by the Admin. Please wait for approval
                            by Admin so that you can print your dummy certificate.
                        </h5>
                        <a class="round-btn-mtii" href="<?php echo site_url().$_SERVER['REQUEST_URI'];?>">
                            Check Approval status
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($ngo_main_form && $ngo_main_form->is_admin_approved=="Approved" && !isset($_REQUEST["is_preview"])) :
    $lga_and_wards = new MtiiRelatedInformation;

    $id = $ngo_main_form->application_form_id;
    $registered_name = $ngo_main_form->name_of_proposed_organization;
    $lga = $ngo_main_form->lga_of_proposed_organization;
    $lga_code = $lga_and_wards->get_lga_code($lga);


    $registration_info = array(
        "id"                => $id,
        "lga"               => $lga,
        "lga_code"          => $lga_code,
        "registered_name"   => $registered_name,
        "invoice_number"    => $invoice_info->invoice_number
    );


    $info_as_json = json_encode($registration_info);
    ?>
    <div class="payment-err">
        <div class="notification-wrapper">
            <div class="mtii_reg_errors">
                <?php if (!in_array('administrator', $user->roles)) : ?>
                    <h2 style="color: #34b38a;">Congratulations!</h2>
                    <p> Congratulations! Your registration is completed and has finally been approved by Admin!
                        Please use the buttons below to Print your Dummy Certificate or to print registration information
                    </p>
                <?php elseif (in_array('administrator', $user->roles)) : ?>
                    <h2 style="color: #34b38a;">Completed Registration!</h2>
                    <p>
                        The Registration with this invoice number has been Completed!
                    </p>
                <?php endif; ?>
                    <a target="_blank" href="<?php echo site_url('/download-dummy-certificate?n=').
                        urlencode(openssl_encrypt($info_as_json, 'AES-128-ECB', 'XJ34')).'&catg=ngo'; ?>"
                        class="round-btn-mtii">Preview Certificate</a>
                    <a target="_blank"
                        href="<?php echo site_url('/download-dummy-certificate?n=').
                            urlencode(openssl_encrypt($info_as_json, 'AES-128-ECB', 'XJ34'))."&downlfi=y&catg=ngo"; ?>"
                        class="round-btn-mtii blue">Download Certificate</a>
                <a href="<?php
                            echo site_url(
                                "/user-dashboard?do=reg&catg=".$ngo."&is_preview=".
                                urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_ngo=1"
                            )
                            ?>" class="round-btn-mtii blue"
                >Preview or Edit Filled Form</a>
            </div>
        </div>
    </div>
<?php elseif ($ngo_main_form && $ngo_main_form->is_admin_approved=="Declined" && !isset($_REQUEST["is_preview"])) : ?>
    <div class="payment-err">
        <div class="notification-wrapper">
            <div class="mtii_reg_errors">
                <h2 style="color: red;">Registration Declined</h2>
                <p> Sorry! Your company's Registration as an NGO/CBO has been declined by the administrator.
                    You should return to the Registration page and edit in order to initiate re-approval.
                </p>
            </div>
            <div>
            <?php if (!in_array('administrator', $user->roles)) : ?>
                <a href="<?php
                            echo site_url(
                                "/user-dashboard?do=reg&catg=".$ngo."&is_preview=".
                                urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_ngo=1"
                            )
                            ?>" class="round-btn-mtii blue"
                >Preview or Edit Filled Form</a>
            <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
        elseif ($reg_util->get_ngo_cbo_form_data()
            && $reg_util->all_ngo_db_table_fields_filled() && !isset($_REQUEST['is_preview'])
        ) : ?>
    <div class="section-body">
        <h2 class="section-heading">Registration undergoing Assessment!</h2>
        <hr class="header-lower-rule" />
        <div class="payment-err">
            <div class="notification-wrapper">
                <div class="mtii_reg_errors">
                    <h2 style="color: #34b38a;">
                        Your registration is still undergoing assessment. Please wait for approval
                        by Admin so that you can print your dummy certificate. Thank you!
                    </h2>
                </div>
                <a class="round-btn-mtii" href="<?php echo site_url(
                    "/user-dashboard?do=reg&catg=".$ngo."&is_preview=".
                    urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_ngo=1"
                ); ?>">Preview Records</a>
            </div>
        </div>
    </div>
<?php else : ?>
<section id="dcs-section">
    <?php
        $show_fully_completed = $reg_util->check_if_invoice_has_signed_documents($invoice_info->invoice_number)=="true"  ? true : false;
    ?>
    <div id="section-wrapper" style="position: relative;">
        <form name="invoice-payment-verification-form" id="" action="" method="post" novalidate="novalidate">
            <div id="biz-prem-form-body">
                <div class="wrapper">
                    <h3 class="form-heading">APPLICATION FORM AS AN NGO/CBO</h3>
                </div>
                <div class="wrapper">
                    <p class="inline-input body">
                        <span>
                            1. Name of proposed Organization
                            <?php
                                $reg_util->get_input_or_placeholder_text('name_of_proposed_organization', 'text', $read_only);
                            ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            2. LGA of proposed Organization
                            <?php $reg_util->select_input_creator('lga_of_proposed_organization', 'Select Local Government', 'lga-list'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            3. Date of Establishment
                            <?php $reg_util->get_input_or_placeholder_text('date_of_establishment', 'date'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            4. Address of proposed Society
                            <?php $reg_util->get_input_or_placeholder_text('address_of_proposed_organization', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            5A. Area of Operation
                            <?php $reg_util->select_input_creator('area_of_operation', 'select');?>
                        </span>
                    </p>
                    <p class="inline-input body hide-for-print">
                        <span>
                            5B. if you picked <strong>Others</strong> in 5A above, Please specify the type here. Otherwise leave it blank
                            <?php $reg_util->get_input_or_placeholder_text('area_of_operation_other', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            6. Specific Objectives which the proposed Organization intends to achieve:
                            <?php $reg_util->get_input_or_placeholder_text('specific_objectives_of_organization', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            7. Donor Support Agency (Optional):
                            <?php $reg_util->get_input_or_placeholder_text('donor_support_agency', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            8. Proposed project (Optional):
                            <?php $reg_util->get_input_or_placeholder_text('proposed_project', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            9. Name of proposed Banker
                            <?php $reg_util->select_input_creator('name_of_proposed_banker', 'select Proposed Banker');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            10. Name of Coordinator
                            <?php $reg_util->get_input_or_placeholder_text('name_of_coordinator', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            11. Phone Number of Coordinator
                            <?php $reg_util->get_input_or_placeholder_text('number_of_coordinator', 'number');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            12. Name of Assistant Coordinator
                            <?php $reg_util->get_input_or_placeholder_text('name_of_assistant_coordinator', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            13. Phone Number of Assistant Coordinator
                            <?php $reg_util->get_input_or_placeholder_text('number_of_assistant_coordinator', 'number');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            14. Name of Secretary
                            <?php $reg_util->get_input_or_placeholder_text('name_of_secretary', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            15. Phone Number of Secretary
                            <?php $reg_util->get_input_or_placeholder_text('number_of_secretary', 'number');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            16. Brief descriptions of the proposed activity of the Organization:
                            <?php $reg_util->get_input_or_placeholder_text('brief_description_of_activity', 'text');?>
                        </span>
                    </p>
                    <h4>17. ATTESTATION</h4>
                    <p class="inline-input body">
                        <span>
                            I/We, <?php $reg_util->get_input_or_placeholder_text('name_of_attester', 'text');?>
                            hereby certify that the foregoing particulars are absolutely correct and undertake to notify
                            the Coordinator of NGOs and CBOs of any change (s) that may occur. I/We understand that may
                            false declaration will disqualify this application in addition to other penalties.
                        </span>
                    </p>
                </div>
            </div>
            <?php
            if (!isset($_REQUEST['is_preview']) || (isset($_REQUEST['is_preview'])
                && $_REQUEST['is_preview']==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))
            ) :
                ?>
                <input
                type="hidden" name="main_registration_nonce"
                value="<?php echo wp_create_nonce('main-registration-nonce') ?>"
                />
                <?php if (isset($_REQUEST['for_edit']) && $_REQUEST['for_edit']==1 && !in_array('administrator', $user->roles)) : ?>
                    <?php if (!$reg_util->registration_approved()) : ?>
                        <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Save Edit" />
                    <?php endif; ?>
                    <a class="round-btn-mtii" href="<?php echo site_url(
                        "/user-dashboard?do=reg&catg=".$ngo."&is_preview=".
                        urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_ngo=1"
                    ); ?>">Cancel Edit</a>
                <?php elseif (!isset($_REQUEST['is_preview']) && !in_array('administrator', $user->roles)) : ?>
                    <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Submit" />
                <?php else : ?>
                    <?php if (!in_array('administrator', $user->roles)) : ?>
                        <?php if (!$reg_util->registration_approved()) : ?>
                            <a class="round-btn-mtii" href="<?php echo site_url(
                                "/user-dashboard?do=reg&catg=".$ngo."&is_preview=".
                                urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_ngo=1&for_edit=1"
                            ); ?>">Edit Form</a>
                        <?php endif; ?>
                    <?php endif; ?>
                    <p class="round-btn-mtii upload-btn" onClick="window.print()">Print this page</p>
                <?php endif; ?>
            <?php else : ?>
                <?php if (!in_array('administrator', $user->roles)) : ?>
                    <a href="<?php
                            echo site_url(
                                "/user-dashboard?do=reg&catg=".$ngo."&is_preview=".
                                urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_ngo=1&for_edit=1"
                            )
                                ?>" class="round-btn-mtii upload-btn blue"
                    >Edit form</a>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
</section>
<?php endif; ?>
