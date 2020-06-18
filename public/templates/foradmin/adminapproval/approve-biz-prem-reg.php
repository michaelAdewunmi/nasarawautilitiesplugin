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
use MtiiUtilities\TasksPerformer;

function ordinal($number) {
    $ends = array('th','st','nd','rd','th','th','th','th','th','th');
    if ((($number % 100) >= 11) && (($number%100) <= 13))
        return $number. 'th';
    else
        return $number. $ends[$number % 10];
}
$task_performer = new TasksPerformer;
$args = array (
    'post_type'         => 'mtii_biz_prem_reg',
    'posts_per_page'    => 10
);

$all_biz_prem = new WP_Query($args);
?>
<div id="notification">
    <div class="notifier">
        <div id="msg"></div>
        <div id="notification-btn" class="mkinnig-rounded-btn close-notify">Close Notification</div>
    </div>
</div>
<div id="search-wrapper">
    <div id="searched-details"></div>
    <input class="long-wide-sharp" id="coop-search" type="text" name="" placeholder="Click Here to search Business premise">
    <div id="members-wrapper"></div>
</div>
<?php
if($all_biz_prem->have_posts()) :
    while($all_biz_prem->have_posts()) :
        $all_biz_prem->the_post();
        global $mtii_biz_prem_db_main;
        $biz_premise = $mtii_biz_prem_db_main->get_by('invoice_number_filled_against', get_the_title());

        $is_approved = get_post_meta(get_the_id(), 'is_admin_approved', true);
        if ($biz_premise && $biz_premise!='') :
            $invoice_detail = ($task_performer->get_invoice_as_cpt(get_the_title()));
            $invoice_catg = ucwords(get_post_meta($invoice_detail->ID, "invoice_sub_category", true));

            if ($invoice_catg=="Used-bp-renewal") {
                $invoice_catg="Premise Renewal";
            }
            ?>
            <div class="parent-wrapper">
                <div class="doc-single-wrapper">
                    <div class="coop-info-wrapper">
                        <h2 style="margin-top: 20px;"><?php echo $biz_premise->name_of_company.' ('.get_the_title().')'; ?></h2>
                        <div class="doc-info" style="margin: 0;">
                            <input type="hidden" class="mtii-doc-id" value="<?php the_ID(); ?>" />
                            <input type="hidden" class="mtii-doc-title" value="<?php the_title(); ?>" />
                            <input type="hidden" class="mtii-doc-id"
                                value="<?php echo $biz_premise->application_form_id; ?>"
                            />
                            <input type="hidden" class="mtii-doc-title"
                                value="<?php echo $biz_premise->invoice_number_filled_against; ?>"
                            />
                        </div>
                        <?php
                        echo '<h6 style="margin-top: 30px;" class="info-approval">Invoice Category: '.$invoice_catg.'</h6>';
                        if ($is_approved=="Awaiting Approval") {
                            echo 'Status: <p class="dstatus awaiting-appr">Awaiting Approval</p>';
                        } else if ($is_approved=="Approved") {
                            echo 'Status:  <p class="dstatus appr">Approved</p>';
                        } else if ($is_approved=="Declined") {
                            echo 'Status: <p class="dstatus declined">Declined</p>';
                        }
                        ?>
                        <div>
                            <p class="doc-apprv-btn round-btn-mtii small-btn for-appr is-biz-prem">Approve Doc</p>
                            <p class="doc-decl-btn round-btn-mtii small-btn for-decl is-biz-prem">Decline Doc</p>
                            <a class="round-btn-mtii small-btn" target="_blank"
                                href="<?php echo $task_performer->receipt_view_url.'/'.get_the_title(); ?>">
                                View Receipt
                            </a>
                        </div>
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
        endif;
    endwhile;
        ?>
        <input type="hidden" id="mtii-doc-nonce" value="<?php echo wp_create_nonce('doc-upload-approval-nonce') ?>" />
        <input type="hidden" id="reg_catg" name="reg_catg" value="Business Premise" />
    <div class="pagination">
    <?php
        echo paginate_links(
            array(
                'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                'total'        => $all_biz_prem->max_num_pages,
                'current'      => max(1, get_query_var('paged')),
                'format'       => '?paged=%#%',
                'show_all'     => false,
                'type'         => 'plain',
                'end_size'     => 2,
                'mid_size'     => 1,
                'prev_next'    => true,
                'prev_text'    => sprintf('<i></i> %1$s', __('<p id="doc-apprv-btn" class="round-btn-mtii small-btn for-appr">Previous Page</p>', 'mtii-utilities')),
                'next_text'    => sprintf('%1$s <i></i>', __('<p id="doc-apprv-btn" class="round-btn-mtii small-btn for-appr">Next Page</p>', 'mtii-utilities')),
                'add_args'     => false,
                'add_fragment' => '',
            )
        );
    ?>

    </div>
    <?php
else :
    ?>
    <div style="min-height: 100vh;">
        <div class="doc-single-wrapper">
            <div class="doc-info">
                <h1>Oops! It seems there are no Registrations yet!</h1>
            </div>
        </div>
    </div>
<?php endif; ?>
