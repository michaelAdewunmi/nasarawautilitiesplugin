<?php
use MtiiUtilities\CoopSignatoriesRegistration;
use MtiiUtilities\MtiiRelatedInformation;

$reg_util = new CoopSignatoriesRegistration;
echo $reg_util->get_all_form_errors(); //Show Errors if there is any error from the validated input.
$invoice_info = $reg_util->get_invoice_info_from_db();

$society_info = $reg_util->get_coop_main_form_data();

if (isset($invoice_info->invoice_number)
    && $reg_util->check_if_invoice_has_signed_documents($invoice_info->invoice_number)=="true" && !isset($_REQUEST["is_preview"])
) :

    $main_form_info = $reg_util->get_coop_main_form_data();
    $coop_name = $main_form_info->name_of_proposed_society;
    $lga = $main_form_info->lga_of_proposed_society;
    $ward = $main_form_info->ward_of_proposed_society;
    $id = $main_form_info->application_form_id;

    $lga_and_wards = new MtiiRelatedInformation;
    $lga_code = $lga_and_wards->get_lga_code($lga);
    $ward_code = $lga_and_wards->get_ward_code($ward);


    $coop_info = array(
        "id"                => $id,
        "ward"              => $ward,
        "lga"               => $lga,
        "ward_code"         => $ward_code,
        "lga_code"          => $lga_code,
        "registered_name"   => $coop_name,
        "invoice_number"    => $invoice_info->invoice_number
    );

    $info_as_json = json_encode($coop_info);
?>
<div class="payment-err">
    <div class="notification-wrapper">
        <div class="mtii_reg_errors">
            <?php if (!in_array('administrator', $user->roles)) : ?>
                <h2 style="color: #34b38a;">Congratulations!</h2>
                <p> Congratulations! Your registration is completed and your document has finally been approved!
                    Please Click the button below to Print your Dummy Certificate and all filled pages
                </p>
            <?php elseif (in_array('administrator', $user->roles)) : ?>
                <h2 style="color: #34b38a;">Completed Registration!</h2>
                <p>
                    The Registration with this invoice number has been Completed!
                </p>
                <?php
            endif;
            ?>
                <a target="_blank" href="<?php echo site_url('/download-dummy-certificate?n=').
                    urlencode(openssl_encrypt($info_as_json, 'AES-128-ECB', 'XJ34')).'&catg=cooperative'; ?>"
                    class="round-btn-mtii">Preview Dummy Certificate</a>
                <a target="_blank"
                    href="<?php echo site_url('/download-dummy-certificate?n=').
                    urlencode(openssl_encrypt($info_as_json, 'AES-128-ECB', 'XJ34'))."&downlfi=y&catg=cooperative"; ?>"
                    class="round-btn-mtii blue">Download Dummy Certificate</a>
            <a href="<?php
                        echo site_url(
                            "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_main=1"
                        )
                        ?>" class="round-btn-mtii blue"
            >Preview Filled Form</a>
        </div>
    </div>
</div>
<?php
    elseif (isset($invoice_info->invoice_number) && $society_info->admin_approved=="Declined"
        && !$reg_util->check_if_invoice_has_signed_documents($invoice_info->invoice_number)
    ) :
        ?>
<div class="payment-err">
    <div class="notification-wrapper">
        <div class="mtii_reg_errors">
            <h2 style="color: red;">OOPS! Uploaded Document Declined</h2>
            <p> Sorry! The Document you Uploaded for your registration has been declined by the administrator.
                You should return to the uploading page and reupload a new document
            </p>
            <a href="<?php
                        echo site_url('/user-dashboard/?do=upload') ?>" class="round-btn-mtii blue"
            >Return to Upload Page</a>
            <a href="<?php
                echo site_url(
                    "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                    urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_main=1&for_edit=1"
                )
                    ?>" class="round-btn-mtii upload-btn blue"
            >Edit form</a>
        </div>
    </div>
</div>
<?php
    elseif (isset($invoice_info->invoice_number) && $society_info->admin_approved!="Declined"
        && ($reg_util->check_if_invoice_has_signed_documents($invoice_info->invoice_number))==="Awaiting Approval"
    ) :
        ?>
<div class="payment-err">
    <div class="notification-wrapper">
        <div class="mtii_reg_errors">
            <h2 style="color: blue;">Document Awaiting Approval</h2>
            <p> You have gotten to the final phase of your registration and your Upload
                is presently awaiting Approval by the admin. You will be notified once your
                upload has been approved.
            </p>
        </div>
    </div>
</div>
<?php elseif ($reg_util->records_successfully_added()) :
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
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_signatories_template=1"
                        ); ?>">Preview Records and print</a>
                    <?php else : ?>
                        <h2 style="color: #34b38a;">Signatories records successfully Registered</h2>
                        <a class="round-btn-mtii" href="<?php echo site_url(
                            "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                            urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_signatories_template=1"
                        ); ?>">Print Signatories form</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php elseif ($reg_util->get_signatories_data() && (!isset($_REQUEST["for_edit"]) && !isset($_REQUEST["is_preview"]))) :
    ?>
    <div class="section-body">
        <h2 class="section-heading">Awaiting Upload!</h2>
        <hr class="header-lower-rule" />
        <div class="payment-err">
            <div class="notification-wrapper">
                <div class="mtii_reg_errors">
                    <h2 style="color: #34b38a;">
                        You have successfully filled all online forms. Please upload a signatories form and wait for Admin approval.
                    </h2>
                    <a class="round-btn-mtii" href="<?php echo site_url(
                        "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                        urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_signatories_template=1"
                    ); ?>">Preview signatories form and print</a>
                    <a class="round-btn-mtii blue" href="<?php echo site_url("/user-dashboard?do=upload"); ?>">Upload Filled form</a>
                </div>
            </div>
        </div>
    </div>
<?php else : ?>
<div class="upload-notification">
    <p>
        PLEASE NOTE: You are to complete this page and print so that signatures can be appended offline.
        After the signatures are appended, come back and upload the signed form and await admin approval
        which is the final process of the registration.
    </p>
</div>

<?php
    $show_fully_completed = $reg_util->check_if_invoice_has_signed_documents($invoice_info->invoice_number)=="true"  ? true : false;
    echo $reg_util->show_status_bar($show_fully_completed, true, true);
?>

<section id="dcs-section">
    <div id="section-wrapper" style="position: relative;">
        <form name="invoice-payment-verification-form" id="" action="" method="post" novalidate="novalidate">
        <p style="font-size: 12px;">
            We the undersigned certify that the details given overleaf are correct and
            apply that the Society described may be registered as a Co-operative Society
            under section two of the Nigeria Co-operative Society Act No. 90 as amended (2014)
        </p>
        <p style="font-size: 12px;">
            Date_______________________________________
        </p>
        <div id="table-holder">
            <div class="flex-hor">
                <p class="flexed-child serial-no">S/No</p>
                <p class="flexed-child">NAME (SUNA)</p>
                <p class="flexed-child">OCCUPATION (SANA'A)</p>
                <p class="flexed-child">VILLAGE OR TOWN (GAR|)</p>
                <p class="flexed-child">LGA (KARAMAR HUKUMA)</p>
                <p class="flexed-child">SIGNATURE (SA HANNU)</p>
            </div>
            <?php
            $names = $reg_util->names; $numbers = $reg_util->numbers;
            for ($i=0; $i<10; $i++) :
                ?>
                <div class="flex-hor">
                    <p class="flexed-child serial-no"><?php echo $i+1; ?></p>
                    <p class="flexed-child inline-input">
                        <?php $reg_util->get_input_or_placeholder_text($names[0].'_'.$numbers[$i], "text", null, "Type Name"); ?>
                    </p>
                    <p class="flexed-child inline-input">
                        <?php $reg_util->get_input_or_placeholder_text($names[1].'_'.$numbers[$i], "text", null, "Type Occupation"); ?>
                    <p class="flexed-child inline-input">
                        <?php $reg_util->get_input_or_placeholder_text($names[2].'_'.$numbers[$i], "text", null, "Type Village"); ?>
                    <p class="flexed-child inline-input">
                        <?php $reg_util->select_input_creator($names[3].'_'.$numbers[$i], "Select LGA", "reduced-width"); ?>
                    <p class="flexed-child"></p>
                </div>
            <?php endfor; ?>
        </div>
        <p style="margin: 0; font-size: 12px;">
            At least Ten members are required by section 4(2) (a) of the Co-operative Act to sign the application.
            In case of illiterate persons, the names should be written by a writer and the applicant's thumb print added.
            I certify that the above Society has got all books and documents required by the Registrar.
        </p>
        <p class="move-right">
            <span>------------------------------</span>
            <span>Cooperative Officer (i/o)</span>
        </p>
        <div id="not-shown-for-print">
            <input
                type="hidden" name="signatories_template_nonce"
                value="<?php echo wp_create_nonce('signatories-template-nonce') ?>"
            />
            <?php
                if (isset($_REQUEST['is_preview']) && $_REQUEST['is_preview']==openssl_encrypt("is_preview", "AES-128-ECB", "XJ34")
                    && isset($_REQUEST['for_edit']) && $_REQUEST['for_edit']==1 &&  !in_array('administrator', $user->roles)
                    && $reg_util->can_edit_cooperative_form()==="Can Edit"
                ) : ?>
                <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Save Edits" />
                <a class="round-btn-mtii" href="<?php echo site_url(
                    "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                    urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_signatories_template  =1"
                ); ?>">Cancel Edit</a>
            <?php
                elseif (!isset($_REQUEST['is_preview']) && !in_array('administrator', $user->roles)
                    && $reg_util->can_edit_cooperative_form()==="Can Edit"
                ) :
                    ?>
                <input class="round-btn-mtii"  name="mtii_form_submit" type="submit" value="Submit and Print" />
                <a class="round-btn-mtii" href="<?php echo site_url(
                    "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                    urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_main=1&for_edit=1"
                ); ?>">Edit Coopertaive Records</a>
            <?php else : ?>
                <p class="round-btn-mtii blue" onClick="window.print()">Print form</p>
                <?php
                if (!in_array('administrator', $user->roles) && $reg_util->can_edit_cooperative_form()==="Can Edit") :
                    ?>
                    <a class="round-btn-mtii" href="<?php echo site_url(
                        "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                        urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_signatories_template=1&for_edit=1"
                    ); ?>">Edit Form</a>
                    <a class="round-btn-mtii" style="background: #0912ba" href="<?php echo site_url("/user-dashboard?do=upload"); ?>">
                        Upload Form (If Signed)
                    </a>
                <?php endif; ?>
                <a class="round-btn-mtii" href="<?php echo site_url(
                    "/user-dashboard?do=reg&catg=AScTltDXpUOy0owVUBq5DA%3D%3D&is_preview=".
                    urlencode(openssl_encrypt("is_preview", "AES-128-ECB", "XJ34"))."&for_main=1"
                ); ?>">View Cooperative Records</a>
            <?php endif; ?>
        </div>
        </form>
    <div>
</section>
<?php endif; ?>