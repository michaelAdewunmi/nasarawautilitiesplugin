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

use MtiiUtilities\TasksPerformer;
use MtiiUtilities\CloudinaryUpload;

if (!isset($_REQUEST["repl_catg"])) :
    echo "<h1>Sorry! There is an error with this request</h1>";
else :
    $args = array (
        'post_type'         => 'mtii_cert_replcmnt',
        'posts_per_page'    => 10,
        'meta_query'        => array (
            array (
                'key'       => 'invoice_category',
                'value'     => openssl_decrypt($_REQUEST["repl_catg"], "AES-128-ECB", "X340&2&230rTHJ34"),
                'compare'   => '='
            )
        )
    );

    if (openssl_decrypt($_REQUEST["repl_catg"], "AES-128-ECB", "X340&2&230rTHJ34")==="ngoAndCbo") {
        $coop_or_ngo = "NGOs/CBOs";
    } else {
        $coop_or_ngo = "Cooperatives";
    }

    $all_docs = new WP_Query($args);

    $cloudinary_util = new CloudinaryUpload(false, array('police_extract', 'court_affidavit'));
    $task_performer = new TasksPerformer;
    ?>
    <div id="notification">
        <div class="notifier">
            <div id="msg"></div>
            <div id="notification-btn" class="mkinnig-rounded-btn close-notify">Close Notification</div>
        </div>
    </div>
    <div id="search-wrapper">
        <div id="searched-details"></div>
        <input class="long-wide-sharp" id="coop-search" type="text"
            name="" placeholder="Click Here to search existing <?php echo $coop_or_ngo; ?>">
        <div id="members-wrapper"></div>
    </div>
    <?php
    if($all_docs->have_posts()) :
        while($all_docs->have_posts()) :
            $all_docs->the_post();
            global $mtii_cert_replacement_table;
            $replacement_info = $mtii_cert_replacement_table->get_by('invoice_number_filled_against', get_the_title());

            if ($replacement_info && $replacement_info!='') :
                $invoice_detail = ($task_performer->get_invoice_as_cpt(get_the_title()));
                $invoice_catg = get_post_meta($invoice_detail->ID, "invoice_sub_category", true);
                $is_approved = $replacement_info->is_admin_approved;
                ?>
                <div class="parent-wrapper">
                    <div class="doc-single-wrapper">
                        <div class="doc-info">
                            <input type="hidden" class="mtii-doc-id" value="<?php the_ID(); ?>" />
                            <input type="hidden" class="mtii-doc-title" value="<?php the_title(); ?>" />
                        <?php
                            $cloudinary_util->get_existing_doc_and_show_thumbnail(get_the_title(), true, $is_approved, true);
                        ?>
                        </div>
                        <div class="coop-info-wrapper">
                            <h3 style="margin-top: 20px;"><?php echo $replacement_info->name_of_society_or_organization; ?></h3>
                            <div>
                                <h6 class="info-approval">Invoice Number: <?php the_title(); ?></h6>
                                <h6 class="info-approval">Invoice Category: <?php echo $invoice_catg; ?></h6>
                                <a class="round-btn-mtii small-btn" target="_blank"
                                    href="<?php echo $task_performer->receipt_view_url.'/'.get_the_title(); ?>">
                                    View Receipt
                                </a>
                            </div>
                            <?php
                            if ($is_approved=="Awaiting Approval") {
                                echo 'Status: <p class="dstatus awaiting-appr">Awaiting Approval</p>';
                            } else if ($is_approved=="Approved") {
                                echo 'Status:  <p class="dstatus appr">Approved</p>';
                            } else if ($is_approved=="Declined") {
                                echo 'Status: <p class="dstatus declined">Declined</p>';
                            }
                            ?>
                            <p class="inline-input body">
                                <span>
                                    Name of Cooperative Society
                                    <span class="as-placeholder"><?php echo $replacement_info->name_of_society_or_organization; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Certificate Number
                                    <span class="as-placeholder"><?php echo $replacement_info->certificate_number; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Date Certificate was Issued
                                    <span class="as-placeholder"><?php echo $replacement_info->date_cert_was_issued; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Applicant's Full Name
                                    <span class="as-placeholder"><?php echo $replacement_info->applicant_full_name; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Phone Number
                                    <span class="as-placeholder"><?php echo $replacement_info->phone_number; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Email Address
                                    <span class="as-placeholder"><?php echo $replacement_info->email; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Application Date
                                    <span class="as-placeholder"><?php echo $replacement_info->application_date?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Position or Rank in Society
                                    <span class="as-placeholder"><?php echo $replacement_info->position_rank_in_the_society; ?></span>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <?php
            else :
                echo "<h1>There are presently no registered replacements. You should check back.</h1>";
            endif;
        endwhile;
        ?>
            <input type="hidden" id="mtii-doc-nonce" value="<?php echo wp_create_nonce('doc-upload-approval-nonce') ?>" />
            <input type="hidden" id="reg_catg" name="reg_catg"
                value="<?php echo openssl_decrypt($_REQUEST["repl_catg"], "AES-128-ECB", "X340&2&230rTHJ34"); ?>"
            />
        <div class="pagination">
        <?php
            echo paginate_links(
                array(
                    'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
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
                    <h1>Sorry! It seems there are no Registrations yet!</h1>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>