<?/**
 * Summary.
 *
 * Description.
 *
 * @since Version 3 digits
 */
include_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/class-mtii-registration-utilities.php';
$all_input_names = array (
    'name_of_company', 'date_of_registration', 'nature_of_business', 'time_of_declaration',
    'address_of_premise', 'lga_of_company', 'director_one_name', 'director_one_number', 'director_two_name',
    'director_two_number', 'director_three_name', 'director_three_number',
    'director_four_name', 'director_four_number', 'director_five_name',
    'director_five_number', 'annual_turnover', 'is_premise_rented',
    'name_of_landlord', 'address_of_landlord', 'day_of_declaration',
    'month_of_declaration', 'year_of_declaration', 'name_of_declarator', 'position_of_declarator'
);
$nice_input_names_as_assoc_array = array ();

foreach ($all_input_names as $input_name) {
    $nice_input_names_as_assoc_array[$input_name] = ucwords(str_replace("_", " ", $input_name));
}

$reg_util = new Mtii_Registration_Utilities($all_input_names, $nice_input_names_as_assoc_array, 'business_premise');
$biz_prem_main_form = $reg_util->get_biz_prem_form_data();
echo $reg_util->get_all_form_errors(); //Show Errors if there is any error from the validated input.
$invoice_info = $reg_util->get_invoice_info_from_db();
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
                            "/user-dashboard?do=reg&catg=".$biz_prem."&is_preview=".
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_biz_prem=1"
                        ); ?>">Preview Records</a>
                    <?php else : ?>
                        <h5 style="color: #34b38a;">
                            Business Premise Records successfully Added and your registration is
                            presently undergoing assessement by the Admin. Please wait for approval
                            by Admin so that you can print your dummy certificate.
                        </h5>
                        <a class="round-btn-mtii" href="<?php echo site_url().$_SERVER['REQUEST_URI'];?>">Check Approval status</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($biz_prem_main_form && $biz_prem_main_form->is_admin_approved=="Approved" && !isset($_REQUEST["is_preview"])) :
    $lga_and_wards = new Mtii_Parameters_Setter_And_Getter;

    $id = $biz_prem_main_form->application_form_id;
    $biz_name = $biz_prem_main_form->name_of_company;
    $lga = $biz_prem_main_form->lga_of_company;
    $lga_code = $lga_and_wards->get_lga_code($lga);
    $biz_nature = $biz_prem_main_form->nature_of_business;
    $biz_address = $biz_prem_main_form->address_of_premise;
    $time_of_declaration = $biz_prem_main_form->time_of_declaration;
    $day_of_declaration = $biz_prem_main_form->day_of_declaration;
    $month_of_declaration = $biz_prem_main_form->month_of_declaration;
    $year_of_declaration = $biz_prem_main_form->year_of_declaration;

    $coop_info = array(
        "id"                    => $id,
        "lga"                   => $lga,
        "lga_code"              => $lga_code,
        "biz_name"              => $biz_name,
        "biz_nature"            => $biz_nature,
        "biz_address"           => $biz_address,
        "time_of_declaration"   => $time_of_declaration,
        "day_of_declaration"    => $day_of_declaration,
        "month_of_declaration"  => $month_of_declaration,
        "year_of_declaration"   => $year_of_declaration
    );

    $info_as_json = json_encode($coop_info);
    ?>
    <div class="payment-err">
        <div class="notification-wrapper">
            <div class="mtii_reg_errors">
                <?php if (!in_array('administrator', $user->roles)) : ?>
                    <h2 style="color: #34b38a;">Congratulations!</h2>
                    <p> Congratulations! Your registration is completed and has finally been approved by Admin!
                        Please use the buttons below to Print your Dummy Certificate or all filled pages
                    </p>
                <?php elseif (in_array('administrator', $user->roles)) : ?>
                    <h2 style="color: #34b38a;">Completed Registration!</h2>
                    <p>
                        The Registration with this invoice number has been Completed!
                    </p>
                <?php endif; ?>
                    <a target="_blank" href="<?php echo site_url('/download-dummy-certificate?n=').
                        urlencode(openssl_encrypt($info_as_json, 'AES-128-ECB', 'SECRET')).'&catg=biz_prem'; ?>"
                        class="round-btn-mtii">Preview Certificate</a>
                    <a target="_blank"
                        href="<?php echo site_url('/download-dummy-certificate?n=').
                            urlencode(openssl_encrypt($info_as_json, 'AES-128-ECB', 'SECRET'))."&downlfi=y&catg=biz_prem"; ?>"
                        class="round-btn-mtii blue">Download Certificate</a>
                <a href="<?php
                            echo site_url(
                                "/user-dashboard?do=reg&catg=".$biz_prem."&is_preview=".
                                urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_biz_prem=1"
                            )
                            ?>" class="round-btn-mtii blue"
                >Preview or Edit Filled Form</a>
            </div>
        </div>
    </div>
<?php elseif ($biz_prem_main_form && $biz_prem_main_form->is_admin_approved=="Declined" && !isset($_REQUEST["is_preview"])) : ?>
    <div class="payment-err">
        <div class="notification-wrapper">
            <div class="mtii_reg_errors">
                <h2 style="color: red;">OOPS! Registration Disapproved</h2>
                <p> Sorry! Your company's Registration for a business premise has been declined by the administrator.
                    You should return to the Registration page and edit in order to initiate re-approval.
                </p>
            </div>
            <div>
            <?php if (!in_array('administrator', $user->roles)) : ?>
                <a href="<?php
                            echo site_url(
                                "/user-dashboard?do=reg&catg=".$biz_prem."&is_preview=".
                                urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_biz_prem=1"
                            )
                            ?>" class="round-btn-mtii blue"
                >Preview or Edit Filled Form</a>
            <?php endif; ?>
            </div>
        </div>
    </div>
<?php elseif ($reg_util->get_biz_prem_form_data() && !isset($_REQUEST['is_preview'])) : ?>
    <div class="section-body">
        <h2 class="section-heading">Registration already done with this Invoice Number!</h2>
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
                    "/user-dashboard?do=reg&catg=".$biz_prem."&is_preview=".
                    urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_biz_prem=1"
                ); ?>">Preview Records</a>
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
    ?>
    <div id="section-wrapper" style="position: relative;">
        <form name="invoice-payment-verification-form" id="" action="" method="post" novalidate="novalidate">
            <div id="biz-prem-form-body">
                <div class="wrapper">
                    <h3 class="form-heading">APPLICATION FORM AS A BUSINESS PREMISE</h3>
                </div>
                <div class="wrapper">
                    <p class="inline-input body">
                        <span>
                            1. Name of Company/Firm/Partnership/Enterprises
                            <?php $reg_util->get_input_or_placeholder_text('name_of_company', 'text'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            2. Date of Registration
                            <?php $reg_util->get_input_or_placeholder_text('date_of_registration', 'date'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            3. Nature of Business
                            <?php $reg_util->get_input_or_placeholder_text('nature_of_business', 'select'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            4. Address to be registered
                            <?php $reg_util->get_input_or_placeholder_text('address_of_premise', 'text'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            5. LGA
                            <?php $reg_util->get_input_or_placeholder_text('lga_of_company', 'select'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            6. Names of Directors/Partners/Proprietor and contact phone numbers
                        </span>
                    </p>
                    <div id="table-holder" class="biz-prem">
                        <div class="flex-hor">
                            <p class="flexed-child serial-no">S/No</p>
                            <p class="flexed-child">NAME</p>
                            <p class="flexed-child">Phone Number</p>
                        </div>
                        <?php
                        $number = array('one', 'two', 'three', 'four', 'five');
                        for ($i=0; $i<5; $i++) :
                            ?>
                            <div class="flex-hor">
                                <p class="flexed-child serial-no"><?php echo $i+1; ?></p>
                                <p class="flexed-child inline-input">
                                    <?php $reg_util->get_input_or_placeholder_text('director_'.$number[$i].'_name', "text", "Type Name"); ?>
                                </p>
                                <p class="flexed-child inline-input">
                                    <?php $reg_util->get_input_or_placeholder_text('director_'.$number[$i].'_number', "text", "Type Phone Number"); ?>
                                </p>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <p class="inline-input body">
                        <span>
                            7. Annual Turnover of Business
                            <?php $reg_util->get_input_or_placeholder_text('annual_turnover', 'number'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            8. Is this premise a rented property?
                            <?php $reg_util->get_input_or_placeholder_text('is_premise_rented', 'number'); ?>
                        </span>
                    </p>

                    <p <?php echo !isset($_REQUEST["is_preview"]) || isset($_REQUEST["for_edit"])
                                ? 'id="landlord-name" ' : ''; ?> class="inline-input body"
                    >
                        <span>
                            8a. If Yes, Give Name of Landlord
                            <?php $reg_util->get_input_or_placeholder_text('name_of_landlord', 'text'); ?>
                        </span>
                    </p>
                    <p <?php echo !isset($_REQUEST["is_preview"]) || isset($_REQUEST["for_edit"])
                                ? 'id="landlord-address" ' : ''; ?> class="inline-input body"
                    >
                        <span>
                            8b. Address of Landlord
                            <?php $reg_util->get_input_or_placeholder_text('address_of_landlord', 'text'); ?>
                        </span>
                    </p>
                    <p style="font-weight: 700; color: #000; margin-bottom: 0px;" class="inline-input body"> 9. Declaration</p>
                    <p class="inline-input body" style="margin-top: 0;">
                        <span>
                            I/we hereby certify that the foregoing particulars are absolutely correct and undertake
                            to notify the Registrar of Business Premises of any change(s) that may occur. I/we
                            understand that any false declaration will disqualify this application in addition
                            to other penalties as provided in the governing law.
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            <?php
                                date_default_timezone_set("Africa/Lagos");
                                $time = !isset($_REQUEST["is_preview"]) || isset($_REQUEST["for_edit"]) ?
                                    'Dated at <span id="jstime"><strong>'.date('h:i:s A').'</strong></span>' :
                                    'Dated at <strong>'.$reg_util->get_input_or_placeholder_text('time_of_declaration', 'text').'</strong>';
                            ?>
                            <?php echo $time; ?>
                            today <?php $reg_util->get_input_or_placeholder_text('day_of_declaration', 'select', null, false, true); ?>
                            day of <?php $reg_util->get_input_or_placeholder_text('month_of_declaration', 'slect'); ?>
                            year <?php $reg_util->get_input_or_placeholder_text('year_of_declaration', 'select'); ?>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Name: <?php $reg_util->get_input_or_placeholder_text('name_of_declarator', 'text'); ?>
                            Position: <?php $reg_util->get_input_or_placeholder_text('position_of_declarator', 'text'); ?>
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
                <?php if (isset($_REQUEST['for_edit']) && $_REQUEST['for_edit']==1 && !in_array('administrator', $user->roles)) : ?>
                    <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Save Edit" />
                    <a class="round-btn-mtii" href="<?php echo site_url(
                        "/user-dashboard?do=reg&catg=".$biz_prem."&is_preview=".
                        urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_biz_prem=1"
                    ); ?>">Cancel Edit</a>
                <?php elseif (!isset($_REQUEST['is_preview']) && !in_array('administrator', $user->roles)) : ?>
                    <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Submit" />
                <?php else : ?>
                    <?php if (!in_array('administrator', $user->roles)) : ?>
                        <a class="round-btn-mtii" href="<?php echo site_url(
                            "/user-dashboard?do=reg&catg=".$biz_prem."&is_preview=".
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_biz_prem=1&for_edit=1"
                        ); ?>">Edit Form</a>
                    <?php endif; ?>
                    <p class="round-btn-mtii upload-btn" onClick="window.print()">Print this page</p>
                <?php endif; ?>
            <?php else : ?>
                <?php if (!in_array('administrator', $user->roles)) : ?>
                    <a href="<?php
                            echo site_url(
                                "/user-dashboard?do=reg&catg=".$biz_prem."&is_preview=".
                                urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "SECRET"))."&for_biz_prem=1&for_edit=1"
                            )
                                ?>" class="round-btn-mtii upload-btn blue"
                    >Edit form</a>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
</section>
<?php endif; ?>
