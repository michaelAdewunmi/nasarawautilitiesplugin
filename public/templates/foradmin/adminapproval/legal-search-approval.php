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
        'post_type'         => 'mtii_legal_search',
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
            global $mtii_legal_search_table;
            $legal_search_info = $mtii_legal_search_table->get_by('invoice_number_filled_against', get_the_title());

            if ($legal_search_info && $legal_search_info!='') :
                $invoice_detail = ($task_performer->get_invoice_as_cpt(get_the_title()));
                $invoice_catg = get_post_meta($invoice_detail->ID, "invoice_sub_category", true);
                $is_approved = $legal_search_info->is_admin_approved;
                ?>
                <div class="parent-wrapper">
                    <div class="doc-single-wrapper" style="display: block;">
                        <div class="doc-info">
                            <input type="hidden" class="mtii-doc-id" value="<?php the_ID(); ?>" />
                            <input type="hidden" class="mtii-doc-title" value="<?php the_title(); ?>" />
                        </div>
                        <div class="coop-info-wrapper">
                            <h3 style="margin-top: 20px;"><?php echo $legal_search_info->name_of_ngo_or_cooperative; ?></h3>
                            <div>
                                <h6 class="info-approval">Invoice Number: <?php the_title(); ?></h6>
                                <h6 class="info-approval">Invoice Category: <?php echo $invoice_catg; ?></h6>
                                <a class="round-btn-mtii small-btn" target="_blank"
                                    href="<?php echo $task_performer->receipt_view_url.'/'.get_the_title(); ?>">
                                    View Receipt
                                </a>
                                <!-- <div> -->
                                <p class="doc-apprv-btn round-btn-mtii small-btn for-appr" style="margin: 10px 20px; background: #34b38a;">
                                    Approve Doc
                                </p>
                                <p class="doc-decl-btn round-btn-mtii small-btn for-decl" style="margin: 10px 20px">
                                    Decline Doc
                                </p>
                                <!-- </div> -->
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
                                    Name of Society or Organization
                                    <span class="as-placeholder"><?php echo $legal_search_info->name_of_ngo_or_cooperative; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Certificate Number
                                    <span class="as-placeholder"><?php echo $legal_search_info->certificate_number; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Date Certificate was Issued
                                    <span class="as-placeholder"><?php echo $legal_search_info->date_cert_was_issued; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Applicant's Full Name
                                    <span class="as-placeholder"><?php echo $legal_search_info->applicant_full_name; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Phone Number
                                    <span class="as-placeholder"><?php echo $legal_search_info->phone_number; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Email Address
                                    <span class="as-placeholder"><?php echo $legal_search_info->email; ?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Application Date
                                    <span class="as-placeholder"><?php echo $legal_search_info->application_date?></span>
                                </span>
                            </p>
                            <p class="inline-input body">
                                <span>
                                    Name of Organization
                                    <span class="as-placeholder"><?php echo $legal_search_info->organization?></span>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <?php
            else :
                echo "<h1>There is an issue with this one. Contact Super Admin</h1>";
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
                    <h1>Oops! It seems there are no Legal Search registration yet!</h1>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>