<?php
/**
 * This file is the template for the NGO/CBO Registration Approval by Admin
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
$task_performer = new TasksPerformer;
$args = array (
    'post_type'         => 'mtii_ngo_lists',
    'posts_per_page'    => 10
);

$all_ngos = new WP_Query($args);
?>
<div id="notification">
    <div class="notifier">
        <div id="msg"></div>
        <div id="notification-btn" class="mkinnig-rounded-btn close-notify">Close Notification</div>
    </div>
</div>
<div id="search-wrapper">
    <div id="searched-details"></div>
    <input class="long-wide-sharp" id="coop-search" type="text" name="" placeholder="Click Here to search existing NGOs">
    <div id="members-wrapper"></div>
</div>
<?php
if($all_ngos->have_posts()) :
    while($all_ngos->have_posts()) :
        $all_ngos->the_post();
        global $mtii_ngo_cbo_db_table;
        $ngo_and_cbo = $mtii_ngo_cbo_db_table->get_by('invoice_number_filled_against', get_the_title());

        $is_approved = get_post_meta(get_the_id(), 'is_admin_approved', true);
        if ($ngo_and_cbo && $ngo_and_cbo!='') :
            $invoice_detail = ($task_performer->get_invoice_as_cpt(get_the_title()));
            $invoice_catg = get_post_meta($invoice_detail->ID, "invoice_sub_category", true);
            if ($invoice_catg=="used-ngo-recertification") {
                $invoice_catg="Recertification";
            }
            ?>
            <div class="parent-wrapper">
                <div class="doc-single-wrapper">
                    <div class="coop-info-wrapper">
                        <h2 style="margin-top: 20px;"><?php echo $ngo_and_cbo->name_of_proposed_organization.' ('.get_the_title().')'; ?></h2>
                        <div class="doc-info" style="margin: 0;">
                            <input type="hidden" class="mtii-doc-id" value="<?php the_ID(); ?>" />
                            <input type="hidden" class="mtii-doc-title" value="<?php the_title(); ?>" />
                            <input type="hidden" class="mtii-doc-id"
                                value="<?php echo $ngo_and_cbo->application_form_id; ?>"
                            />
                            <input type="hidden" class="mtii-doc-title"
                                value="<?php echo $ngo_and_cbo->invoice_number_filled_against; ?>"
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
                                2. LGA of proposed Organization
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->lga_of_proposed_organization; ?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                3. Date of Establishment
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->date_of_establishment; ?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                4. Address of proposed Society
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->address_of_proposed_organization; ?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                5A. Area of Operation
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->area_of_operation; ?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                6. Specific Objectives which the proposed Organization intends to achieve:
                                    <span class="as-placeholder"><?php echo $ngo_and_cbo->specific_objectives_of_organization; ?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                7. Donor Support Agency (Optional):
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->donor_support_agency;?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                8. Proposed project (Optional):
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->proposed_project;?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                9. Name of proposed Banker
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->name_of_proposed_banker;?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                10. Name of Coordinator
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->name_of_coordinator;?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                11. Phone Number of Coordinator
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->number_of_coordinator; ?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                12. Name of Assistant Coordinator
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->name_of_assistant_coordinator; ?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                13. Phone Number of Assistant Coordinator
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->number_of_assistant_coordinator; ?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                14. Name of Secretary
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->name_of_secretary; ?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                15. Phone Number of Secretary
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->number_of_secretary; ?></span>
                            </span>
                        </p>
                        <p class="inline-input body">
                            <span>
                                16. Brief descriptions of the proposed activity of the Organization:
                                <span class="as-placeholder"><?php echo $ngo_and_cbo->brief_description_of_activity; ?></span>
                            </span>
                        </p>
                        <h4>17. ATTESTATION</h4>
                        <p class="inline-input body">
                            <span>
                                I/We, <span class="as-placeholder"><?php echo $ngo_and_cbo->name_of_attester; ?></span>
                                hereby certify that the foregoing particulars are absolutely correct and undertake to notify
                                the Coordinator of NGOs and CBOs of any change (s) that may occur. I/We understand that may
                                false declaration will disqualify this application in addition to other penalties.
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
        <input type="hidden" id="reg_catg" name="reg_catg" value="ngoAndCbo" />
    <div class="pagination">
    <?php
        echo paginate_links(
            array(
                'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                'total'        => $all_ngos->max_num_pages,
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
                <h1>OOps! It seems there are no Uploads yet!</h1>
            </div>
        </div>
    </div>
<?php endif; ?>
