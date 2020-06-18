<?php
use MtiiUtilities\CoopMainRegistration;

$reg_util = new CoopMainRegistration;
echo $reg_util->get_all_form_errors(); //Show Errors if there is any error from the validated input.
$invoice_info = $reg_util->get_invoice_info_from_db();

$read_only = null;
if (!$reg_util->allow_coop_name_edit()) {
    $read_only = true;
}
if ($reg_util->records_successfully_added()) :
    $society_info = $reg_util->get_coop_main_form_data();
    $approval_info = $society_info->admin_approved;
    if ($approval_info=="Declined") {
        $additional_query = '&is_preview='.urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34")).
            '&for_signatories_template=1&for_edit=1';
    } else {
        $additional_query = '';
    }
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
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_main=1"
                        ); ?>">Preview Records</a>
                        <a class="round-btn-mtii blue" href="<?php echo site_url(
                            "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D"
                        ).$additional_query; ?>">Go to Next Page</a>
                    <?php else : ?>
                        <h2 style="color: #34b38a;">Cooperative Records successfully Added</h2>
                        <a class="round-btn-mtii" href="<?php echo site_url().$_SERVER['REQUEST_URI'];?>">Go to Next Stage</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php
    elseif ($reg_util->get_coop_main_form_data() && !isset($_REQUEST['is_preview']) && !isset($_REQUEST['offline_to_online'])
        && $reg_util->all_coop_db_table_fields_filled()
    ) :
        ?>
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
                            <?php $reg_util->get_input_or_placeholder_text('name_of_proposed_society', 'text', $read_only); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            2. LGA
                            <?php
                                $reg_util->select_input_creator(
                                    'lga_of_proposed_society', 'Select LGA From list', 'lga-list'
                                );
                            ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            3. Ward
                            <?php
                                $reg_util->select_input_creator(
                                    'ward_of_proposed_society', 'Select Ward From list', 'all-wards'
                                );
                            ?>
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
                            <?php $reg_util->select_input_creator('area_of_operation', 'Select Area of Operation');?>
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
                            <?php $reg_util->select_input_creator('nature_of_proposed_banker', 'Select Banker');?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            13. Nature of Cooperative Society ( Liability Limited or Unlimited)
                            <?php $reg_util->select_input_creator('nature_of_coop_society', 'Liability Limited or Unlimited');?>
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
            $coop_main_info = $reg_util->get_coop_main_form_data();
            $lga = isset($coop_main_info->lga_of_proposed_society) ? $coop_main_info->lga_of_proposed_society : null;
            $lga = isset($coop_main_info->admin_approved) ? $coop_main_info->admin_approved : null;

            if (!isset($_REQUEST['is_preview']) || (isset($_REQUEST['is_preview']) && $_REQUEST['is_preview']==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))) : ?>
                <input
                type="hidden" name="main_registration_nonce"
                value="<?php echo wp_create_nonce('main-registration-nonce') ?>"
                />
                <?php if (!$reg_util->get_coop_main_form_data()) : ?>
                    <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Submit" />
                <?php endif;
                if ($reg_util->get_coop_main_form_data()) {
                    if (isset($_REQUEST['for_edit']) && $_REQUEST['for_edit']==1  && !in_array('administrator', $user->roles) && $reg_util->can_edit_cooperative_form()==="Can Edit"
                    ) :

                    ?>
                        <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Save Edit" />

                        <a class="round-btn-mtii" href="<?php echo site_url(
                            "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_main=1"
                        ); ?>">Cancel Edit</a>
                    <?php elseif (!isset($_REQUEST['is_preview']) && !in_array('administrator', $user->roles) && $reg_util->can_edit_cooperative_form()==="Can Edit") : ?>
                        <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Submit" />
                    <?php else : ?>
                        <?php if(!in_array('administrator', $user->roles) && $reg_util->can_edit_cooperative_form()==="Can Edit") : ?>
                            <a class="round-btn-mtii" href="<?php echo site_url(
                                "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                                urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_main=1&for_edit=1"
                            ); ?>">Edit Form</a>
                        <?php endif; ?>
                        <a class="round-btn-mtii blue" href="<?php echo site_url(
                            "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_signatories_template=1"
                        ); ?>">View Signatories Form</a>
                        <p class="round-btn-mtii upload-btn" onClick="window.print()">Print this page</p>
                    <?php endif;
                }
                ?>
            <?php else : ?>
                <?php
                if(!in_array('administrator', $user->roles) && $reg_util->can_edit_cooperative_form()==="Can Edit") : ?>
                    <a href="<?php
                            echo site_url(
                                "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                                urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_main=1&for_edit=1"
                            )
                                ?>" class="round-btn-mtii upload-btn blue"
                    >Edit form</a>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
</section>
<?php endif; ?>