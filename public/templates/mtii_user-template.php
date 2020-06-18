<?php
use MtiiUtilities\TasksPerformer;
$tasks_performer = new TasksPerformer;
$user = wp_get_current_user();
$f_name = get_the_author_meta('first_name', $user->ID);
$l_name = get_the_author_meta('last_name', $user->ID);
$full_name = $f_name." ".$l_name;
if (isset($_REQUEST["do"]) && $_REQUEST["do"]=="logout") {
    wp_logout();
    wp_redirect(esc_url(home_url('/wp-login')));
} else if (!is_user_logged_in() || (isset($_REQUEST["do"]) && $_REQUEST["do"]=="approve" && !$tasks_performer->is_mtii_admin())
    || (isset($_REQUEST["do"]) && $_REQUEST["do"]=="viewallcoop" && !$tasks_performer->is_coop_and_ngo_director())
    || (isset($_REQUEST["do"]) && $_REQUEST["do"]=="replacements" && !$tasks_performer->is_coop_and_ngo_director())
    || (isset($_REQUEST["do"]) && $_REQUEST["do"]=="legal-search" && !$tasks_performer->is_coop_and_ngo_director())
) {
    wp_redirect(esc_url(home_url('/wp-login')));
    exit;
}
wp_head();
?>
<div id="user-section">
    <?php
    if (!$tasks_performer->get_mtii_client_id() || trim($tasks_performer->get_mtii_client_id())==''
        || !$tasks_performer->get_mtii_client_secret() || trim($tasks_performer->get_mtii_client_secret())==""
    ) {
        ?>
        <div class="section-body"
        style="min-height: 100vh; background: #eee; display: flex; flex-direction: column; justify-content: center; align-items: center;"
        >
            <h2 class="section-heading errored-text">Oops! How about a Minute</h2>
            <hr class="header-lower-rule errored-bg" />
            <div class="payment-err">
                <div class="notification-wrapper">
                    <p>
                        Sorry! We are presently performing some routine clean ups!
                        Please check back in a minute
                    </p>
                </div>
            </div>
        </div>
    <?php
    } else {
    ?>
        <div id="mobile-bar">
            <p class="brand-link">
                <a href="<?php echo esc_url(home_url('/'))?>"><?php bloginfo('name'); ?></a>
            </p>
            <div class="mobile-hamburger-toggle"><div class="hamburger-box"><div class="hamburger-inner"></div></div></div>
        </div>
        <?php
            $coop = urlencode(openssl_encrypt("cooperative-soc", "AES-128-ECB", "X340&2&230rTHJ34"));
            $ngo = urlencode(openssl_encrypt("ngo-cbo", "AES-128-ECB", "X340&2&230rTHJ34"));
            $biz_prem = urlencode(openssl_encrypt("business-premise", "AES-128-ECB", "X340&2&230rTHJ34"));
            $fertilizers = urlencode(openssl_encrypt("fertilizer-plants", "AES-128-ECB", "X340&2&230rTHJ34"));
            $packaging = urlencode(openssl_encrypt("sacks-packaging-akwanga", "AES-128-ECB", "X340&2&230rTHJ34"));
            $beef_processing = urlencode(openssl_encrypt("beef-proc-masaka-karu", "AES-128-ECB", "X340&2&230rTHJ34"));
            $haulage = urlencode(openssl_encrypt("haulage-fee-collection", "AES-128-ECB", "X340&2&230rTHJ34"));
            $others = urlencode(openssl_encrypt("others", "AES-128-ECB", "X340&2&230rTHJ34"));
            $query_param = isset($_REQUEST['do']) ? urlencode($_REQUEST['do']) : null;
            $query_param_catg = isset($_REQUEST['catg']) ? urlencode($_REQUEST['catg']) : null;
            $all_child_nav = array (
                'Cooperative_Society' => $coop, 'NGO/CBO' => $ngo, 'Business_Premises' => $biz_prem,
                'Fertilizer Plants' => $fertilizers, 'Sacks_&_Packaging' => $packaging,
                'Beef_Processing' => $beef_processing, 'Haulage_Fee' => $haulage, 'Others' => $others
            );

            //For certificate replacements and Legal Search
            $repl_coop = urlencode(openssl_encrypt("Cooperative", "AES-128-ECB", "X340&2&230rTHJ34"));
            $repl_ngo = urlencode(openssl_encrypt("ngoAndCbo", "AES-128-ECB", "X340&2&230rTHJ34"));
            $query_param_catg_repl = isset($_REQUEST['repl_catg']) ? urlencode($_REQUEST['repl_catg']) : null;
            $replacement_child_nav = array ('Cooperative_Society' =>$repl_coop, 'NGO/CBO' =>$repl_ngo);
        ?>
        <div id="side-nav">
            <ul id="user-navigations-list">
                <li class="nav-list user-avatar">
                    <p><?php echo mb_substr($user->user_login, 0, 1); ?></p>
                </li>
                <li class="nav-list <?php echo $query_param==='profile' ? 'active' : ''; ?>">
                    <i class="icon flaticon-embassy"></i>
                    <a href="<?php echo esc_url(site_url('/user-dashboard?do=profile')); ?>">Profile</a>
                </li>
                <?php if (!$tasks_performer->is_mtii_admin()) : ?>
                    <li class="nav-list contains-children <?php echo $tasks_performer->get_active_navigation_class("pay"); ?>">
                        <i class="icon flaticon-team"></i> Make Payment <span class="arrow-down"></span>
                        <ul class="children-nav">
                            <?php
                            foreach ($all_child_nav as $key => $value) :
                                $tasks_performer->show_navigation_list_item($query_param, "pay", $query_param_catg, $value, $key);
                            endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-list contains-children <?php echo $tasks_performer->get_active_navigation_class("reg"); ?>">
                        <i class="icon flaticon-team"></i> Registration <span class="arrow-down"></span>
                        <ul class="children-nav">
                        <?php
                        foreach ($all_child_nav as $key => $value) :
                            $tasks_performer->show_navigation_list_item($query_param, "reg", $query_param_catg, $value, $key);
                        endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-list <?php echo $query_param==='upload' ? 'active' : ''; ?>">
                        <i class="icon flaticon-aim"></i>
                        <a href="<?php echo esc_url(site_url('/user-dashboard?do=upload')); ?>">Upload Documents</a>
                    </li>
                    <?php
                endif;
                if ($tasks_performer->is_mtii_admin()) : ?>
                    <li class="nav-list contains-children <?php echo $tasks_performer->get_active_navigation_class("approve"); ?>">
                        <i class="icon flaticon-team"></i>Admin Approvals <span class="arrow-down"></span>
                        <ul class="children-nav">
                        <?php
                        foreach ($all_child_nav as $key => $value) :
                            if (($value===$coop || $value===$ngo) && $tasks_performer->is_coop_and_ngo_director()) {
                                $tasks_performer->show_navigation_list_item($query_param, "approve", $query_param_catg, $value, $key);
                            }
                            if ($value===$biz_prem && $tasks_performer->is_business_premises_director()) {
                                $tasks_performer->show_navigation_list_item($query_param, "approve", $query_param_catg, $value, $key);
                            }
                            if ($value!==$coop && $value!==$ngo && $value!==$biz_prem && $tasks_performer->is_director_for_others()) {
                                $tasks_performer->show_navigation_list_item($query_param, "approve", $query_param_catg, $value, $key);
                            }
                        endforeach; ?>
                        </ul>
                    </li>
                    <?php if ($tasks_performer->is_coop_and_ngo_director()) : ?>
                    <li class="nav-list contains-children <?php echo $tasks_performer->get_active_navigation_class("replacements"); ?>">
                        <i class="icon flaticon-team"></i>Cert. Replacements <span class="arrow-down"></span>
                        <ul class="children-nav">
                        <?php
                        foreach ($replacement_child_nav as $key => $value) :
                            $tasks_performer->show_navigation_list_item($query_param, "replacements", $query_param_catg_repl, $value, $key, true);
                        endforeach; ?>
                        </ul>
                    </li>
                    <li class="nav-list contains-children <?php echo $tasks_performer->get_active_navigation_class("legal-search"); ?>">
                        <i class="icon flaticon-team"></i>Legal Search <span class="arrow-down"></span>
                        <ul class="children-nav">
                        <?php
                        foreach ($replacement_child_nav as $key => $value) :
                            $tasks_performer->show_navigation_list_item($query_param, "legal-search", $query_param_catg_repl, $value, $key, true);
                        endforeach; ?>
                        </ul>
                    </li>
                        <?php
                    endif;
                    if (!in_array('administrator', $user->roles)
                        && ($tasks_performer->is_business_premises_director() || $tasks_performer->is_director_for_others())
                    ) {
                        echo "<script>window.isNotNgoDirector='Yes'</script>";
                    }
                    ?>
                    <li class="nav-list contains-children <?php echo $tasks_performer->get_active_navigation_class("adminview"); ?>">
                        <i class="icon flaticon-team"></i> Admin View <span class="arrow-down"></span>
                        <ul class="children-nav">
                        <?php
                        foreach ($all_child_nav as $key => $value) :
                            if (($value===$coop || $value===$ngo) && $tasks_performer->is_coop_and_ngo_director()) {
                                $tasks_performer->show_navigation_list_item($query_param, "adminview", $query_param_catg, $value, $key);
                            }
                            if ($value===$biz_prem && $tasks_performer->is_business_premises_director()) {
                                $tasks_performer->show_navigation_list_item($query_param, "adminview", $query_param_catg, $value, $key);
                            }
                            if ($value!==$coop && $value!==$ngo && $value!==$biz_prem && $tasks_performer->is_director_for_others()) {
                                $tasks_performer->show_navigation_list_item($query_param, "adminview", $query_param_catg, $value, $key);

                            }
                        endforeach;
                        ?>
                        </ul>
                    </li>
                <?php endif; ?>
                <li class="nav-list">
                    <i class="icon flaticon-bus"></i>
                    <a href="<?php echo esc_url(site_url('/user-dashboard?do=logout')); ?>">Sign Out</a>
                </li>
            </ul>
        </div>
        <div class="section-content">
            <div class="content-wrapper">
                <h2 class="welcome-heading">Welcome <?php echo $full_name; ?></h2>
                <?php
                if ($query_param=='err') :
                    include_once "mtii-error-notification-template.php";
                elseif ($query_param=='succ') :
                    include_once "mtii-success-notification-template.php";
                elseif ($query_param=='reg') :
                    include_once "registrations/template-registration.php";
                elseif ($query_param=='pay') :
                    include_once "payments/template-payment.php";
                elseif ($query_param=='upload') :
                    include_once "upload/template-upload-doc.php";
                elseif ($query_param=='replacements') :
                    include_once "foradmin/adminapproval/base.php";
                elseif ($query_param=='legal-search') :
                    include_once "foradmin/adminapproval/base.php";
                elseif ($query_param=='adminview') :
                    include_once "foradmin/adminview/base.php";
                elseif ($query_param=='approve') :
                    include_once "foradmin/adminapproval/base.php";
                endif;
                ?>
            </div>
            <?php
                get_footer();
            ?>
        </div>
    <?php } ?>
</div>