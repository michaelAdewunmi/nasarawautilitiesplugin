<?php
/**
 * This file is the template for the file upload Approval by Admin
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
$args = array (
    'post_type'         => 'mtii_signed_uploads',
    'posts_per_page'    => 10
);

$all_docs = new WP_Query($args);
require_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/class-mtii-utilities-cloudinary-upload.php';
include_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/class-mtii-registration-utilities.php';

$cloudinary_util = new Mtii_Utilities_Cloudinary_Upload;
?>
<div id="notification">
    <div class="notifier">
        <div id="msg"></div>
        <div id="notification-btn" class="mkinnig-rounded-btn close-notify">Close Notification</div>
    </div>
</div>
<div class="">
    <h5 class="">Approved Signed Document</h5>
<?php
if($all_docs->have_posts()) :
    while($all_docs->have_posts()) :
        $all_docs->the_post();
        ?>
        <div class="parent-wrapper">
            <div class="doc-single-wrapper">
                <div class="doc-info">
                    <h1><?php the_title(); ?></h1>
                    <?php
                    $doc_url = get_post_meta(get_the_id(), 'secure_url', true);
                    $is_approved = get_post_meta(get_the_id(), 'admin_approved', true);
                    $cloudinary_util->get_existing_doc_and_show_thumbnail(get_the_title(), true, $is_approved); ?>
                    <input type="hidden" class="mtii-doc-id" value="<?php the_ID(); ?>" />
                    <input type="hidden" class="mtii-doc-title" value="<?php the_title(); ?>" />
                </div>
                <div class="coop-info-wrapper">
                    <h3 style="margin-top: 10px;">Cooperative Society Info</h3>
                    <?php
                        global $mtii_db_coop_main_form;
                        $coop_info = $mtii_db_coop_main_form->get_by('invoice_number_filled_against', get_the_title());
                    ?>
                    <p class="inline-input body">
                        <span>
                            Name of proposed Society
                            <span class="as-placeholder"><?php echo $coop_info->name_of_proposed_society; ?> Cooperative Society</span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Brief description of the proposed working of Society
                            <span class="as-placeholder"><?php echo $coop_info->brief_description_of_society_activity; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Cooperative Society Local Government Area
                            <span class="as-placeholder"><?php echo $coop_info->lga_of_proposed_society?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Cooperative Society Ward
                            <span class="as-placeholder"><?php echo $coop_info->ward_of_proposed_society?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Area of Operation
                            <span class="as-placeholder"><?php echo $coop_info->area_of_operation?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Specific Objectives of the proposed Society
                            <span class="as-placeholder"><?php echo $coop_info->specific_objectives_of_society; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Total Deposit / Savings
                            <span class="as-placeholder"><?php echo $coop_info->total_deposit_savings; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Name of President
                            <span class="as-placeholder"><?php echo $coop_info->name_of_president; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Phone Number of President
                            <span class="as-placeholder"><?php echo $coop_info->number_of_president; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Name of Secretary
                            <span class="as-placeholder"><?php echo $coop_info->name_of_secretary; ?></span>
                        </span>
                    </p>
                    <p class="inline-input body">
                        <span>
                            Phone Number of Secretary
                            <span class="as-placeholder"><?php echo $coop_info->number_of_secretary; ?></span>
                        </span>
                    </p>
                </div>
            </div>
            <div class="open-and-close-icon">Click Here to Expand</div>
        </div>
    <?php
    endwhile;
        ?>
        <input type="hidden" id="mtii-doc-nonce" value="<?php echo wp_create_nonce('doc-upload-approval-nonce') ?>" />
        <input type="hidden" id="reg_catg" name="reg_catg" value="Cooperative" />
    <div class="pagination">
    <?php
        echo paginate_links(
            array(
                'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                'total'        => $all_docs->max_num_pages,
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
