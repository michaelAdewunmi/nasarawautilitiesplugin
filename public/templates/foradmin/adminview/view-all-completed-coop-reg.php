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
require_once WP_CONTENT_DIR.'/plugins/mtii-utilities/public/class-mtii-registration-utilities.php';

$args = array (
    'post_type'         => 'mtii_signed_uploads',
    'posts_per_page'    => 10,
    'meta_query'        => array (
        array (
            'key'       => 'admin_approved',
            'value'     => 'true',
            'compare'   => '='
        )
    )
);

$all_docs = new WP_Query($args);
global $mtii_db_coop_main_form;
?>
<div class="" style="min-height: 100vh;">
    <h5 class="">All Completed Cooperative Registrations</h5>
    <div id="table-holder" class="admin-preview">
    <?php if ($all_docs->have_posts()) : ?>
        <div class="flex-hor">
            <p class="flexed-child serial-no">S/No</p>
            <p class="flexed-child">NAMES OF COOPERATIVES</p>
            <p class="flexed-child">ADDRESS</p>
            <p class="flexed-child">OFFICIALS</p>
            <p class="flexed-child">GSM NUMBER</p>
        </div>
        <?php
        // $pagenumber = get_query_var( 'paged', 1 );
        // $number = $pagenumber
        $i = 1;
        while ($all_docs->have_posts()) :
            $all_docs->the_post();
            $cooop_info = $mtii_db_coop_main_form->get_by('invoice_number_filled_against', get_the_title());
            ?>

            <div class="flex-hor">
                <p class="flexed-child serial-no"><?php echo $i; ?></p>
                <p class="flexed-child inline-input">
                    <?php echo isset($cooop_info->name_of_proposed_society)
                        ? $cooop_info->name_of_proposed_society : ''; ?>
                </p>
                <p class="flexed-child inline-input">
                    <?php echo isset($cooop_info->address_of_proposed_society)
                        ? $cooop_info->address_of_proposed_society : ''; ?>
                </p>
                <p class="flexed-child inline-input">
                    <span class="single-official">
                        1. PRESIDENT:
                        <?php echo isset($cooop_info->name_of_president)
                            ? $cooop_info->name_of_president : ''; ?>
                    </span>
                    <span class="single-official">
                        2. SECRETARY:
                        <?php echo isset($cooop_info->name_of_secretary)
                            ? $cooop_info->name_of_secretary : ''; ?>
                    </span>
                    <span class="single-official">
                        3. TREASURER:
                        <?php echo isset($cooop_info->name_of_treasurer)
                            ? $cooop_info->name_of_treasurer : ''; ?>
                    </span>
                </p>
                <p class="flexed-child inline-input">
                    <span class="single-official">
                        1. PRESIDENT:
                        <?php echo isset($cooop_info->number_of_president)
                            ? $cooop_info->number_of_president : ''; ?>
                    </span>
                    <span class="single-official">
                        2. SECRETARY:
                        <?php echo isset($cooop_info->number_of_secretary)
                            ? $cooop_info->number_of_secretary : ''; ?>
                    </span>
                    <span class="single-official">
                        3. TREASURER:
                        <?php echo isset($cooop_info->number_of_treasurer)
                            ? $cooop_info->number_of_treasurer : ''; ?>
                    </span>
                </p>
            </div>
            <?php
            $i++;
        endwhile;   ?>
    </div>
    <div class="pagination">
        <?php
            echo paginate_links(
                array (
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

endif; ?>
