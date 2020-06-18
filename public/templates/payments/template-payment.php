<?php
if (!is_user_logged_in()) {
    wp_redirect(esc_url(site_url()));
    exit;
}

//use MtiiUtilities\FuckingTest;

$user = wp_get_current_user();
$f_name = get_the_author_meta('first_name', $user->ID);
$l_name = get_the_author_meta('last_name', $user->ID);
$phone = get_the_author_meta('phone_number', $user->ID);
$full_name = $f_name." ".$l_name;
require_once 'generate-invoice-through-parkway.php';

//$coop = urlencode(openssl_encrypt("cooperative-soc", "AES-128-ECB", "X340&2&230rTHJ34"));

//$invoice_number = urlencode(openssl_encrypt("1000134031", "AES-128-ECB", "0jQkL&5S"));

//$fs = new FuckingTest;

//$fs->mtii_daily_tasks_scheduler();
// function bl_print_tasks() {
//     echo '<pre>'; print_r(_get_cron_array()); echo '</pre>';
// }
// bl_print_tasks();

// global $mtii_db_coop_main_form;
// $all_coop = $mtii_db_coop_main_form->get_all();
// // echo '<pre>';  var_dump($all_coop); echo '</pre>';
// foreach ($all_coop as $coop) {
//     $invoice_info = $mtii_db_invoice->get_row_by_invoice(
//         $coop->invoice_number_filled_against, $coop->request_ref_filled_against
//     );
//     $invoice_from_cp = get_page_by_title($coop->invoice_number_filled_against, OBJECT, 'mtii_cbs_invoice');
//     $invoice_from_cp_id = isset($invoice_from_cp->ID) ? $invoice_from_cp->ID : null;
//     $date_paid_from_cp = get_post_meta($invoice_from_cp_id, 'date_paid', true);
//     echo isset($invoice_info->payment_date) ? $invoice_info->payment_date."<br /><br />" : "Naaaa <br /><br />";
//     echo $date_paid_from_cp."<br /><br />";
// }
// global $mtii_db_invoice;
// // echo '<pre>';  var_dump($mtii_db_invoice->get_all()); echo '</pre>';
// $all_invoices = $mtii_db_invoice->get_all();

// foreach ($all_invoices as $invoice) {
//     $invoice_from_cp = get_page_by_title($invoice->invoice_number, OBJECT, 'mtii_cbs_invoice');
//     $invoice_id = isset($invoice_from_cp->ID) ? $invoice_from_cp->ID : null;
//     echo $invoice_id."<br />".$invoice->invoice_number."<br /><br />";
// }
// $today = getdate();
// $args = array (
//     'post_type'         => 'mtii_cbs_invoice',
//     'post_status'       => 'publish',

//     // 'date_query' => array(
//     //     // array(
//     //     //     'year' => date('Y'),
//     //     //     'week' => date('W'),
//     //     // ),
//     //     array(
//     //         'year'  => date('Y'),
//     //         'month' => date('m'),
//     //         'day'   => 14,
//     //     )
//     // ),
//     'meta_query'        => array(
//         array (
//             'key'       => 'invoice_fully_paid',
//             'value'     => 'true',
//             'compare'   => '='
//         )
//     ),
//     'posts_per_page'    => -1,
// );

// $invoice_from_cp = new WP_Query($args);
// while ( $invoice_from_cp->have_posts() ) {
//     $invoice_from_cp->the_post();
//     echo get_the_title() . '<br />';
// }


?>
<div class="section-body <?php echo isset($no_redirect) && $no_redirect==true && isset($_POST["submit"]) ? 'hide' : ''; ?>">
    <h2 class="section-heading">Payment Page</h2>
    <hr class="header-lower-rule" />
    <div class="payment-info">
        <p class="dashboard-p">
            <em>Note: </em>This page will generate invoice for you before proceeding to the
            payment page. If you already have your invoice, then please
            <a class="inline-btn" href="<?php echo esc_url(site_url('/user-dashboard?do=reg')); ?>">
                click here
            </a>
            to verify invoice and make payment.
        </p>
    </div>
    <form name="" id="paymentform" action="" method="post" novalidate="novalidate">
        <?php
        if (isset($errors_array) && count($errors_array)>0) {
            echo '<p class="err-notification errored-text">'.$errors_array["general"].'</p>';
        }
        ?>
        <p>
            <label for="payee_names">Full Name</label>
            <input
                type="text" name="payee_names" id="payee_names"
                class="input" value="<?php echo $full_name; ?>"
                size="20" autocapitalize="off"
            >
        </p>
        <p>
            <label for="payee_email">Email</label>
            <input
                type="text" name="payee_email" id="payee_email"
                class="input" value="<?php echo $user->user_email; ?>"
                size="20" autocapitalize="off"
            >
        </p>
        <p>
            <label for="payee_phone">Phone</label>
            <input
                type="text" name="payee_phone" id="payee_phone"
                class="input" value="<?php echo $phone; ?>"
                size="20" autocapitalize="off"
            >
        </p>
        <?php
        if ($query_param_catg===$haulage || $query_param_catg===$fertilizers || $query_param_catg===$packaging
            || $query_param_catg===$beef_processing
        ) :
            ?>
        <p>
            <label for="payee_organization">Organization</label>
            <input
                type="text" name="payee_organization" id="payee_organization"
                class="input" size="20" autocapitalize="off"
                value="<?php echo isset($_POST["payee_organization"]) ? $_POST["payee_organization"] : ""; ?>"
            >
        </p>
        <?php endif; ?>
        <p>
            <label for="payee_address">Address</label>
            <input
                type="text" name="payee_address" id="payee_address"
                class="input" size="20" autocapitalize="off"
                value="<?php echo isset($_POST["payee_address"]) ? $_POST["payee_address"] : ""; ?>"
            >
        </p>
        <?php if ($query_param_catg===$fertilizers) : ?>
        <p class="" id="cooperative-payment-type">
            <label for="plant_location">Fertilizer Plant Location</label>
            <select name="plant_location" id="plant_location" class="input">
                <option value="">Pick Plant Location</option>
                <option value="fert_akwanga">Akwanga</option>
                <option value="fert_keffi">Keffi</option>
                <option value="fert_lafia">Lafia</option>
            </select>
        </p>
        <?php endif; ?>
        <?php if ($query_param_catg!=$haulage && $query_param_catg!=$others && $query_param_catg!=$fertilizers) : ?>
        <p>
            <label for="payee_tax_number">Tax Payer Identification Number</label>
            <input
                type="text" name="payee_tax_number" id="payee_tax_number"
                class="input" size="20" autocapitalize="off"
                value="<?php echo isset($_POST["payee_tax_number"]) ? $_POST["payee_tax_number"] : ""; ?>"
            >
        </p>
        <?php endif; ?>
        <?php
        if ($query_param=='pay' && ($query_param_catg==$coop || $query_param_catg==$ngo)) :
            include_once "extra-field-cooperative.php";
        elseif ($query_param=='pay' && ($query_param_catg==$biz_prem)) :
            include_once "extra-field-biz-prem.php";
        elseif ($query_param=='pay' && ($query_param_catg==$haulage || $query_param_catg==$others || $query_param_catg==$fertilizers)) :
            include_once "extra-field-haulage.php";
        endif;
        ?>
        <input
            type="hidden" name="user_invoice_nonce" class="input" value="<?php echo wp_create_nonce('user_invoice_nonce') ?>"
        >
        <input class="round-btn-mtii"  name="submit" type="submit" value="Submit" />
    </form>
</div>

