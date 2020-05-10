<?php
if (!is_user_logged_in()) {
    wp_redirect(esc_url(site_url()));
    exit;
}

$user = wp_get_current_user();
$f_name = get_the_author_meta('first_name', $user->ID);
$l_name = get_the_author_meta('last_name', $user->ID);
$phone = get_the_author_meta('phone_number', $user->ID);
$full_name = $f_name." ".$l_name;

// global $wpdb;
// $fields = $wpdb->get_results("SHOW COLUMNS FROM  wp_mtii_coop_application_dcs ");
// $my_fields = array();
// foreach ($fields as $field) {
//     $my_fields[$field->Field] = "%d";
// }

// echo "<pre>";
// var_dump($fields);
// echo "</pre>";
require_once 'generate-invoice-through-parkway.php';
$coop = urlencode(openssl_encrypt("cooperative-soc", "AES-128-ECB", "secretecode"));

?>
<div class="section-body <?php echo $no_redirect==true && isset($_POST["submit"]) ? 'hide' : ''; ?>">
    <h2 class="section-heading">Payment Page</h2>
    <hr class="header-lower-rule" />
    <div class="payment-info">
        <p class="dashboard-p">
            <em>Note: </em>This page will generate invoice for you before proceeding to the
            payment page. If you already have your invoice, then please
            <a class="inline-btn" href="<?php echo esc_url(site_url('/user-dashboard?do=reg&catg='.$coop)); ?>">
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
        <p>
            <label for="payee_address">Address</label>
            <input
                type="text" name="payee_address" id="payee_address"
                class="input" size="20" autocapitalize="off"
                value="<?php echo isset($_POST["payee_address"]) ? $_POST["payee_address"] : ""; ?>"
            >
        </p>
        <p>
            <label for="payee_tax_number">Tax Payer Identification Number</label>
            <input
                type="text" name="payee_tax_number" id="payee_tax_number"
                class="input" size="20" autocapitalize="off"
                value="<?php echo isset($_POST["payee_tax_number"]) ? $_POST["payee_tax_number"] : ""; ?>"
            >
        </p>
        <?php
        if ($query_param=='pay' && ($query_param_catg==$coop || $query_param_catg==$ngo)) :
            include_once "payment-no-invoice-cooperative.php";
        elseif ($query_param=='pay' && ($query_param_catg==$biz_prem)) :
                include_once "payment-no-invoice-biz-prem.php";
        elseif ($query_param=='pay' && ($query_param_catg==$biz_prem)) :
            include_once "payment-no-invoice-biz-prem.php";
        endif;
        ?>
        <input
            type="hidden" name="user_invoice_nonce" class="input" value="<?php echo wp_create_nonce('user_invoice_nonce') ?>"
        >
        <input class="round-btn-mtii"  name="submit" type="submit" value="Submit" />
    </form>
</div>

