<?php
require_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/class-mtii-registration-utilities.php';
$all_input_names = array (
    'coop_ref_no', 'coop_mtii_area_office', 'coop_reg_date',
    'coop_society_name', 'coop_society_birth_date',
    'coop_society_initial_memb', 'visits_of_instruction_number', 'visits_of_instruction_text', 'coop_lga',
    'coop_district', 'coop_nearest_reg_society_name', 'coop_nearest_reg_society_dist',
    'present_memb_no', 'shared_capital_subscribed', 'entrance_fee_subscribed',
    'capital_sub_deposited_with', 'equipment_purchased', 'marketed_produce',
    'marketed_produce_weight', 'reason_for_app_recommendation', 'invoice_number_filled_against',
    'request_ref_filled_against'
);
$nice_input_names_as_assoc_array =  array (
    'coop_ref_no'                => 'Reference Number', 'coop_mtii_area_office'      => 'Area Office',
    'coop_reg_date'              => 'Registration Date', 'coop_society_name'         => 'Society Name',
    'coop_society_birth_date'        => 'Birth Date', 'coop_society_initial_memb'    => 'Initial member Number',
    'visits_of_instruction' => 'Visits of Instruction', 'coop_lga'                   => 'Local Government Area',
    'coop_district'       => 'Society District', 'coop_nearest_reg_society_name' => 'Nearest Society Name',
    'coop_nearest_reg_society_dist' => 'Nearest Society District',
    'present_memb_no'               => 'Present Member Number',
    'shared_capital_subscribed'     => 'Shared Capital Subscribed',
    'entrance_fee_subscribed'       => 'Entrance Fee Subscribed',
    'capital_sub_deposited_with'    => 'Capital Sub Deposuted',
    'equipment_purchased'           => 'Equipment Purchased', 'marketed_produce'    => 'Marketed Produce',
    'marketed_produce_weight'       => 'Marketed Produce', 'reason_for_app_recommendation' => 'App Reccommendation',
    'visits_of_instruction_number'  => 'Visits of Instructionn Number',
    'visits_of_instruction_text'    => 'Visits of Instruction Text'
);
$reg_util = new Mtii_Registration_Utilities($all_input_names, $nice_input_names_as_assoc_array, 'cooperative_dcs_form');
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
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_dcs=1"
                        ); ?>">Preview Info</a>
                    <?php else : ?>
                        <h2 style="color: #34b38a;">Info successfully Added</h2>
                        <a class="round-btn-mtii" href="<?php echo site_url().$_SERVER['REQUEST_URI'];?>">Go to Next Stage</a>
                    <?php endif; ?>
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
        $show_fully_completed = isset($_REQUEST["is_preview"]) ? true : false;
        echo $reg_util->show_status_bar($show_fully_completed, true)

    ?>
    <div id="section-wrapper" style="position: relative;">
        <form name="invoice-payment-verification-form" id="" action="" method="post" novalidate="novalidate">
            <div class="top-right">
                <p class="inline-input">
                    <span class="input-label">Ref. No</span>
                    <?php $reg_util->get_input_or_placeholder_text('coop_ref_no', 'text');?>
                </p>
                <p class="inline-input">
                    <span class="input-label">Ministry of Trade, lndustry and Investment Area Office</span>
                    <?php $reg_util->get_input_or_placeholder_text('coop_mtii_area_office', 'text');?>
                </p>
                <p class="inline-input">
                    <span class="input-label">Date:</span>
                    <?php $reg_util->get_input_or_placeholder_text('coop_reg_date', 'date');?>
                </p>
            </div>
            <div id="to-the-commissioner">
                <div class="wrapper">
                    <p class="salute">The Hon. Commissioner,</p>
                    <p class="salute">Ministry of Trade, lndustry and lnvestment,</p>
                    <p class="salute">Lafia.</p>
                </div>
            </div>
            <div id="dcs-form-body">
                <div class="wrapper">
                    <p class="dcs-attention">Attention of DCS</p>
                    <h3 class="form-heading">APPLICATION FOR REGISTRATION OF COOPERATIVE SOCIETY</h3>
                </div>
                <div class="wrapper">
                    <p class="inline-input body">
                        <span>
                            1. I forward herewith the complete application form for registration of
                            a Cooperative Society, and three copies of the Bye-Laws of the
                            <?php $reg_util->get_input_or_placeholder_text('coop_society_name', 'text');?>
                            Cooperative Society Ltd. which was formed on
                            <?php $reg_util->get_input_or_placeholder_text('coop_society_birth_date', 'date');?>
                            with an initial membership of
                            <?php $reg_util->get_input_or_placeholder_text('coop_society_initial_memb', 'number');?>
                            persons, and
                            <?php $reg_util->get_input_or_placeholder_text('visits_of_instruction_number', 'number');?>
                            visits of instructions have been made
                            <?php $reg_util->get_input_or_placeholder_text('visits_of_instruction_text', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            2. Situation of the Society:
                            <br />
                            Local Government Area
                            <?php $reg_util->get_input_or_placeholder_text('coop_lga', 'text');?>
                            <br />
                            District
                            <?php $reg_util->get_input_or_placeholder_text('coop_district', 'text');?>
                            <br />
                            Nearest Registered or proposed Society to it is
                            <?php $reg_util->get_input_or_placeholder_text('coop_nearest_reg_society_name', 'text');?>
                            which is approximately
                            <?php $reg_util->get_input_or_placeholder_text('coop_nearest_reg_society_dist', 'number');?>
                            Kilometres(km)
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            3. Capital and Membership is:
                            <br />
                            The present Membership is
                            <?php $reg_util->get_input_or_placeholder_text('present_memb_no', 'number');?>
                            <br />
                            The Share Capital Subscribed is
                            <?php $reg_util->get_input_or_placeholder_text('shared_capital_subscribed', 'number');?>
                            <br />
                            The Entrance fees Subscribed is
                            <?php $reg_util->get_input_or_placeholder_text('entrance_fee_subscribed', 'number');?>
                            <br />
                            The Capital Subscribed has been deposited with
                            <?php $reg_util->get_input_or_placeholder_text('capital_sub_deposited_with', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            4. Number of Equipment Purchased or Ordered
                            <?php $reg_util->get_input_or_placeholder_text('equipment_purchased', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            5. Types of Produce that will be Marketed
                            <?php $reg_util->get_input_or_placeholder_text('marketed_produce', 'text');?>
                    </p>
                    <p class="inline-input body">
                        <span>
                            6. Approximate tonnages of Produce that will be Marketed
                            <?php $reg_util->get_input_or_placeholder_text('marketed_produce_weight', 'text');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            7. The application is recommended for the following reasons
                            <?php $reg_util->get_input_or_placeholder_text('reason_for_app_recommendation', 'text');?>
                        </span>
                    </p>
                </div>
            </div>
            <input
                type="hidden" name="form_register_nonce"
                value="<?php echo wp_create_nonce('form-register-nonce') ?>"
            />
            <?php if (!isset($_REQUEST['is_preview'])) : ?>
                <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Continue to Next Step" />
            <?php
                elseif (isset($_REQUEST['is_preview']) && $_REQUEST['is_preview']==openssl_encrypt("is_preview", "AES-128-ECB", "SECRET")
                    && isset($_REQUEST['for_edit']) && $_REQUEST['for_edit']==1
                ) : ?>
                <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Save Form Update" />
            <?php else : ?>
                <p class="round-btn-mtii upload-btn" onClick="window.print()">Click here to print this page</p>
                <a href="<?php
                        echo site_url(
                            "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_main=1"
                        );
                    ?>"
                class="round-btn-mtii upload-btn blue"
                >Load Next Filled Form</a>
                <a href="<?php
                        echo site_url(
                            "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_dcs=1&for_edit=1"
                        )
                            ?>" class="round-btn-mtii upload-btn"
                >Edit form</a>
            <?php endif; ?>
        </form>
    </div>
</section>
<?php endif; ?>