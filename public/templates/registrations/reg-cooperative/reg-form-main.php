<?php
include_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/class-mtii-registration-utilities.php';
$all_input_names = array (
    'name_of_proposed_society', 'ward_of_proposed_society', 'lga_of_proposed_society',
    'date_of_establisment', 'address_of_proposed_society', 'area_of_operation', 'area_of_operation_other',
    'specific_objectives_of_society', 'value_of_share_holding', 'number_of_shares_per_member',
    'total_shared_capital_paid', 'total_deposit_savings', 'nature_of_proposed_banker',
    'nature_of_coop_society', 'entrance_fee_payable_per_member', 'number_of_memb_at_appl_time',
    'name_of_president', 'number_of_president', 'name_of_vice', 'number_of_vice',
    'name_of_secretary', 'number_of_secretary', 'name_of_treasurer',
    'number_of_treasurer', 'brief_description_of_society_activity',
);
$nice_input_names_as_assoc_array =  array ();

foreach ($all_input_names as $input_name) {
    $nice_input_names_as_assoc_array[$input_name] = ucwords(str_replace("_", " ", $input_name));
}

$reg_util = new Mtii_Registration_Utilities($all_input_names, $nice_input_names_as_assoc_array, 'coop_reg_main_form');
echo $reg_util->get_all_form_errors(); //Show Errors if there is any error from the validated input.
$invoice_info = $reg_util->get_invoice_info_from_db();
if ($reg_util->records_successfully_added()) :
    ?>
    <div class="section-body">
        <h2 class="section-heading">Success!</h2>
        <hr class="header-lower-rule" />
        <div class="payment-err">
            <div class="notification-wrapper">
                <div class="mtii_reg_errors">
                    <?php if (isset($_REQUEST["for_edit"]) && $_REQUEST["for_edit"]==1) : ?>
                        <h2 style="color: #34b38a;">Your Edit was successful</h2>
                        <a class="round-btn-mtii" href="<?php echo site_url(
                            "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_main=1"
                        ); ?>">Preview Records</a>
                        <a class="round-btn-mtii blue" href="<?php echo site_url(
                            "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D"
                        ); ?>">Go to Next Page</a>
                    <?php else : ?>
                        <h2 style="color: #34b38a;">Cooperative Records successfully Added</h2>
                        <a class="round-btn-mtii" href="<?php echo site_url().$_SERVER['REQUEST_URI'];?>">Go to Next Stage</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php elseif($reg_util->get_coop_main_form_data() && !isset($_REQUEST['is_preview'])) : ?>
    <div class="section-body">
        <h2 class="section-heading">Registration already done with this Invoice Number!</h2>
        <hr class="header-lower-rule" />
        <div class="payment-err">
            <div class="notification-wrapper">
                <div class="mtii_reg_errors">
                    <h2 style="color: #34b38a;">If you have not uploaded a document, do that by clicking here!</h2>
                    <input type="submit" name="submit" value="Go to Next Stage"
                        onClick="window.location.reload()"
                    />
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
<section id="dcs-section">
    <?php
        if (isset($invoice_info->invoice_number)) : ?>
    <span id="invoice-number-info">
        Invoice Number: <?php echo $invoice_info->invoice_number; ?>
        <a class="round-btn-mtii small-btn" href="<?php echo site_url().$_SERVER['REQUEST_URI'].'&reset=1'; ?>">Change Invoice</a>
    </span>
    <?php
        endif;
        $show_fully_completed = $reg_util->check_if_invoice_has_signed_documents($invoice_info->invoice_number)=="true"  ? true : false;
        echo $reg_util->show_status_bar($show_fully_completed, true)
    ?>
    <div id="section-wrapper" style="position: relative;">
        <form name="invoice-payment-verification-form" id="" action="" method="post" novalidate="novalidate">
            <div id="dcs-form-body">
                <div class="wrapper">
                    <h3 class="form-heading">APPLICATION FORM AS A COOPERATIVE SOCIETY</h3>
                </div>
                <div class="wrapper">
                    <p class="inline-input body">
                        <span>
                            1. Name of proposed Society
                            <?php $reg_util->get_input_or_placeholder_text('name_of_proposed_society', 'text'); ?>
                            Cooperative Society Limited
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            2. LGA
                            <?php $reg_util->get_input_or_placeholder_text('lga_of_proposed_society', 'text'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            3. Ward
                            <?php $reg_util->get_input_or_placeholder_text('ward_of_proposed_society', 'text'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            4. Date of Establishment
                            <?php $reg_util->get_input_or_placeholder_text('date_of_establisment', 'date'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            5. Address of proposed Society
                            <?php $reg_util->get_input_or_placeholder_text('address_of_proposed_society', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            6A. Area of Operation
                            <?php $reg_util->get_input_or_placeholder_text('area_of_operation', 'select');?>
                        </span>
                    </p>
                    <p class="inline-input body hide-for-print">
                        <span>
                            6B. if you picked others in 6A above, Please specify the type here. Otherwise leave it blank
                            <?php $reg_util->get_input_or_placeholder_text('area_of_operation_other', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            7. Specific Objectives the proposed Society intends to achieve
                            <?php $reg_util->get_input_or_placeholder_text('specific_objectives_of_society', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            8. Value of share holding (optional)
                            <?php $reg_util->get_input_or_placeholder_text('value_of_share_holding', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            9. Minimum Share Holding per Member (optional)
                            <?php $reg_util->get_input_or_placeholder_text('number_of_shares_per_member', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            10. Total Shared Capital Paid up to date (optional)
                            <?php $reg_util->get_input_or_placeholder_text('total_shared_capital_paid', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            11. Total Deposit /Savings
                            <?php $reg_util->get_input_or_placeholder_text('total_deposit_savings', 'number');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            12. Nature of proposed Banker
                            <?php $reg_util->get_input_or_placeholder_text('nature_of_proposed_banker', 'select');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            13. Nature of Cooperative Society ( Liability Limited or Unlimited)
                            <?php $reg_util->get_input_or_placeholder_text('nature_of_coop_society', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            14. Entrance Fee payable per Member
                            <?php $reg_util->get_input_or_placeholder_text('entrance_fee_payable_per_member', 'number');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            15. Total Number of Members at the time of Application
                            <?php $reg_util->get_input_or_placeholder_text('number_of_memb_at_appl_time', 'number');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            16. Name of President
                            <?php $reg_util->get_input_or_placeholder_text('name_of_president', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            17. Phone Number of President
                            <?php $reg_util->get_input_or_placeholder_text('number_of_president', 'number');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            18. Name of Vice President
                            <?php $reg_util->get_input_or_placeholder_text('name_of_vice', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            19. Phone Number of Vice President
                            <?php $reg_util->get_input_or_placeholder_text('number_of_vice', 'number');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            20. Name of Secretary
                            <?php $reg_util->get_input_or_placeholder_text('name_of_secretary', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            21. Phone Number of Secretary
                            <?php $reg_util->get_input_or_placeholder_text('number_of_secretary', 'number');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            22. Name of Treasurer
                            <?php $reg_util->get_input_or_placeholder_text('name_of_treasurer', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            23. Phone Number of Treasurer
                            <?php $reg_util->get_input_or_placeholder_text('number_of_treasurer', 'number');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            24. Brief description of the proposed working of Society
                            <?php $reg_util->get_input_or_placeholder_text('brief_description_of_society_activity', 'text');?>
                        </span>
                    </p>
                </div>
            </div>
            <?php
            if (!isset($_REQUEST['is_preview']) || (isset($_REQUEST['is_preview'])
                && $_REQUEST['is_preview']==openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))
            ) :
                ?>
                <input
                type="hidden" name="main_registration_nonce"
                value="<?php echo wp_create_nonce('main-registration-nonce') ?>"
                />
                <?php if (isset($_REQUEST['for_edit']) && $_REQUEST['for_edit']==1  && !in_array('administrator', $user->roles)) : ?>
                    <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Save Edit" />
                    <a class="round-btn-mtii" href="<?php echo site_url(
                        "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                        urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_main=1"
                    ); ?>">Cancel Edit</a>
                <?php elseif (!isset($_REQUEST['is_preview']) && !in_array('administrator', $user->roles)) : ?>
                    <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Submit" />
                <?php else : ?>
                    <?php if(!in_array('administrator', $user->roles)) : ?>
                        <a class="round-btn-mtii" href="<?php echo site_url(
                            "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_main=1&for_edit=1"
                        ); ?>">Edit Form</a>
                    <?php endif; ?>
                    <a class="round-btn-mtii blue" href="<?php echo site_url(
                        "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                        urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_signatories_template=1"
                    ); ?>">Go to Signatories Form</a>
                    <p class="round-btn-mtii upload-btn" onClick="window.print()">Print this page</p>
                <?php endif; ?>
            <?php else : ?>
                <?php if(!in_array('administrator', $user->roles)) : ?>
                    <a href="<?php
                            echo site_url(
                                "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                                urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_main=1&for_edit=1"
                            )
                                ?>" class="round-btn-mtii upload-btn blue"
                    >Edit form</a>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
</section>
<?php endif; ?>