<?php
/**
 * This file is the template for the Business Premises Registration Approval by Admin
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}
global $mtii_biz_prem_db_main;
//$last_page_id = get_option('last_page_id');
$last_page_id =!isset($_REQUEST["gtd"]) ? null : $_REQUEST["gtd"];

$all = $mtii_biz_prem_db_main->get_multiple_rows_by('is_admin_approved', 'Awaiting Approval', null, null, false, true);
$num_rows_all = count($all);
$first_data_id = ($all[0])->application_form_id;
$last_data_id = (end($all))->application_form_id;
if (!$last_page_id) {
    $biz_premises = $mtii_biz_prem_db_main->get_multiple_rows_by('is_admin_approved', 'Awaiting Approval', 3);
} else {
    $last_page_id = openssl_decrypt(urldecode($last_page_id), "AES-128-ECB", "XJ34");
    if (isset($_REQUEST["mv"]) && $_REQUEST["mv"]=='prev') {
        $biz_premises = $mtii_biz_prem_db_main->get_multiple_rows_by('is_admin_approved', 'Awaiting Approval', 3, $last_page_id, true);
    } else {
        $biz_premises = $mtii_biz_prem_db_main->get_multiple_rows_by('is_admin_approved', 'Awaiting Approval', 3, $last_page_id);
    }
}
?>
<div id="notification">
    <div class="notifier">
        <div id="msg"></div>
        <div id="notification-btn" class="mkinnig-rounded-btn close-notify">Close Notification</div>
    </div>
</div>
<div class="">
    <h5 class="">Approved Business Premise Applications</h5>
<?php
//echo is_object($biz_premises) . "<br /><br />";
$biz_premises = (is_object($biz_premises)) ? array($biz_premises) : $biz_premises;
//var_dump(end($biz_premises));


if($biz_premises) :
    foreach($biz_premises as $biz_premise) :
        ?>
        <div class="parent-wrapper">
            <div class="doc-single-wrapper">
                <div class="coop-info-wrapper">
                    <div class="doc-info">
                        <input type="hidden" class="mtii-doc-id" value="<?php echo $biz_premise->application_form_id; ?>" />
                        <input type="hidden" class="mtii-doc-title" value="<?php echo $biz_premise->invoice_number_filled_against; ?>" />
                    </div>
                    <?php
                    if ($biz_premise->is_admin_approved=="Awaiting Approval") {
                        echo 'Status: <p class="dstatus awaiting-appr">Awaiting Approval</p>';
                    } else if ($biz_premise->is_admin_approved=="Approved") {
                        echo 'Status:  <p class="dstatus appr">Approved</p>';
                    } else if ($biz_premise->is_admin_approved=="Declined") {
                        echo 'Status: <p class="dstatus declined">Declined</p>';
                    }
                    ?>
                    <div>
                        <p class="doc-apprv-btn round-btn-mtii small-btn for-appr is-biz-prem">Approve Doc</a>
                        <p class="doc-decl-btn round-btn-mtii small-btn for-decl is-biz-prem">Decline Doc</a>
                    </div>
                    <h3 style="margin-top: 10px;"><?php echo $biz_premise->name_of_company; ?> (<?php echo $biz_premise->invoice_number_filled_against; ?>)</h3>
                    <p class="inline-input body">
                        <span>
                            Name of Company
                            <span class="as-placeholder"><?php echo $biz_premise->name_of_company; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Date of Registration
                            <span class="as-placeholder"><?php echo $biz_premise->date_of_registration; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Nature of Business
                            <span class="as-placeholder"><?php echo $biz_premise->nature_of_business?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Address of Premises to be Registered
                            <span class="as-placeholder"><?php echo $biz_premise->address_of_premise?></span>
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
                                    <span class="as-placeholder">
                                        <?php
                                            $name = 'director_'.$number[$i].'_name';
                                            echo $biz_premise->$name;
                                        ?>
                                    </span>
                                </p>
                                <p class="flexed-child inline-input">
                                    <span class="as-placeholder">
                                        <?php
                                            $name = 'director_'.$number[$i].'_number';
                                            echo $biz_premise->$name;
                                        ?>
                                    </span>
                                </p>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <p class="inline-input body">
                        <span>
                            Annual turnover of Business
                            <span class="as-placeholder"><?php echo $biz_premise->annual_turnover; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Is the Premises a Rented Property
                            <span class="as-placeholder"><?php echo $biz_premise->is_premise_rented; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Name of Landlord
                            <span class="as-placeholder"><?php echo $biz_premise->name_of_landlord; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Address of Landlord
                            <span class="as-placeholder"><?php echo $biz_premise->address_of_landlord; ?></span>
                        </span>
                    </p>
                    <p style="font-weight: 700; color: #000; margin-bottom: 0px;" class="inline-input body"> 8. Declaration</p>
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
                            Dated at <span class="as-placeholder"><?php echo $biz_premise->time_of_declaration; ?></span>
                            today <span class="as-placeholder"><?php echo ordinal($biz_premise->day_of_declaration); ?></span>
                            day of <span class="as-placeholder"><?php echo $biz_premise->month_of_declaration; ?></span>
                            <span class="as-placeholder"><?php echo $biz_premise->year_of_declaration; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Name: <span class="as-placeholder"><?php echo $biz_premise->name_of_declarator; ?></span>
                            Position: <span class="as-placeholder"><?php echo $biz_premise->position_of_declarator; ?></span>
                        </span>
                    </p>
                </div>
            </div>
            <div class="open-and-close-icon">Click Here to Expand</div>
        </div>
    <?php
    endforeach;
        $the_last_id = (end($biz_premises))->application_form_id;
        $the_last_id_prev = ($biz_premises[0])->application_form_id;
        $biz_prem = urlencode(openssl_encrypt("business-premise", "AES-128-ECB", "X340&2&230rTHJ34"));
        ?>
    <input type="hidden" id="mtii-doc-nonce" value="<?php echo wp_create_nonce('doc-upload-approval-nonce') ?>" />
    <input type="hidden" id="reg_catg" name="reg_catg" value="Business Premise" />

    <div class="pagination">
        <?php if ($the_last_id_prev!=$last_data_id) : ?>
            <a class="round-btn-mtii"
                href="<?php echo site_url('user-dashboard/?mv=prev&do=approve&catg=').$biz_prem.'&gtd='
                    .urlencode(openssl_encrypt($the_last_id_prev, "AES-128-ECB", "XJ34"));?>"
            >Previous</a>
        <?php endif; ?>
        <?php if ($the_last_id==$first_data_id) : ?>
            <a class="round-btn-mtii"
                href="<?php echo site_url('user-dashboard/?do=approve&catg=').$biz_prem;?>"
            >Return to first Page</a>
        <?php else : ?>
        <a class="round-btn-mtii"
                href="<?php echo site_url('user-dashboard/?do=approve&catg=').$biz_prem.'&gtd='
                    .urlencode(openssl_encrypt($the_last_id, "AES-128-ECB", "XJ34"));?>"
            >Next</a>
        <?php endif; ?>
    <?php
    ?>
    </div>
    <?php
else :
    ?>
    <div style="min-height: 100vh;">
        <div class="doc-single-wrapper">
            <div class="doc-info" style="max-width: 100%; width: auto;">
                <h1>OOps! It seems there are no Finished Registrations yet!</h1>
            </div>
        </div>
    </div>
<?php endif;?>
</div>
