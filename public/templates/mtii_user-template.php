<?php

$user = wp_get_current_user();
$f_name = get_the_author_meta('first_name', $user->ID);
$l_name = get_the_author_meta('last_name', $user->ID);
$full_name = $f_name." ".$l_name;
if (isset($_REQUEST["do"]) && $_REQUEST["do"]=="logout") {
    wp_logout();
    wp_redirect(esc_url(home_url('/')));
} else if (!is_user_logged_in()
    || (isset($_REQUEST["do"]) && $_REQUEST["do"]=="approve" && !in_array('administrator', $user->roles))
    || (isset($_REQUEST["do"]) && $_REQUEST["do"]=="viewallcoop" && !in_array('administrator', $user->roles))
) {
    wp_redirect(esc_url(home_url('/')));
    exit;
}
wp_head();
?>
<div id="user-section">
    <div id="mobile-bar">
        <p class="brand-link">
            <a href="<?php echo esc_url(home_url('/'))?>"><?php bloginfo('name'); ?></a>
        </p>
        <div class="mobile-hamburger-toggle"><div class="hamburger-box"><div class="hamburger-inner"></div></div></div>
    </div>
    <?php
        $coop = urlencode(openssl_encrypt("cooperative-soc", "AES-128-ECB", "secretecode"));
        $ngo = urlencode(openssl_encrypt("ngo-cbo", "AES-128-ECB", "secretecode"));
        $biz_prem = urlencode(openssl_encrypt("business-premise", "AES-128-ECB", "secretecode"));
        $lafia = urlencode(openssl_encrypt("fertilizer-lafia", "AES-128-ECB", "secretecode"));
        $akwanga = urlencode(openssl_encrypt("fertilizer-akwanga", "AES-128-ECB", "secretecode"));
        $keffi = urlencode(openssl_encrypt("fertilizer-keffi", "AES-128-ECB", "secretecode"));
        $packaging = urlencode(openssl_encrypt("sacks-packaging-akwanga", "AES-128-ECB", "secretecode"));
        $karu = urlencode(openssl_encrypt("beef-proc-masaka-karu", "AES-128-ECB", "secretecode"));
        $haulage = urlencode(openssl_encrypt("haulage-fee-collection", "AES-128-ECB", "secretecode"));
        $query_param = isset($_REQUEST['do']) ? urlencode($_REQUEST['do']) : null;
        $query_param_catg = isset($_REQUEST['catg']) ? urlencode($_REQUEST['catg']) : null;
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
            <?php
            if ($query_param==='pay' && ($query_param_catg===$coop || $query_param_catg===$ngo || $query_param_catg===$biz_prem
                || $query_param_catg===$lafia || $query_param_catg===$akwanga || $query_param_catg===$keffi
                || $query_param_catg===$packaging || $query_param_catg===$karu || $query_param_catg===$haulage)
            ) {
                $active_class = 'active';
            } else {
                $active_class = '';
            }
            ?>
            <li class="nav-list contains-children <?php echo $active_class; ?>">
                <i class="icon flaticon-team"></i> Make Payment <span class="arrow-down"></span>
                <ul class="children-nav">
                    <?php
                    $all_child_nav = array (
                        'Cooperative_Society' => $coop, 'NGO/CBO' => $ngo, 'Business_Premises' => $biz_prem,
                        'Fert._Plant_Lafia' => $lafia, 'Fert._Plant_Akwanga' => $akwanga, 'Fert._Plant_Keffi' => $keffi,
                        'Sacks_&_Packaging' => $packaging, 'Beef_Processing' => $karu, 'Haulage_Fee' => $haulage
                    );
                    foreach ($all_child_nav as $key => $value) :
                        ?>
                        <li class="child-nav <?php echo $query_param==='pay' && $query_param_catg===$value ? 'active' : '' ?>">
                            <a href="<?php echo esc_url(site_url('/user-dashboard?do=pay&catg='.$value)); ?>">
                                <?php echo str_replace("_", " ", $key); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php
            if ( $query_param==='reg' && ($query_param_catg===$coop || $query_param_catg===$ngo || $query_param_catg===$biz_prem
                || $query_param_catg===$lafia || $query_param_catg===$akwanga || $query_param_catg===$keffi
                || $query_param_catg===$packaging || $query_param_catg===$karu || $query_param_catg===$haulage)
            ) {
                $active_class_reg = 'active';
            } else {
                $active_class_reg = '';
            }
            ?>
            <li class="nav-list contains-children <?php echo $active_class_reg; ?>">
                <i class="icon flaticon-team"></i> Registration <span class="arrow-down"></span>
                <ul class="children-nav">
                <?php
                foreach ($all_child_nav as $key => $value) :
                    ?>
                    <li class="child-nav <?php echo $query_param==='reg' && $query_param_catg===$value ? 'active' : '' ?>">
                        <a href="<?php echo esc_url(site_url('/user-dashboard?do=reg&catg='.$value)); ?>">
                            <?php echo str_replace("_", " ", $key); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            </li>
            <li class="nav-list <?php echo $query_param==='upload' ? 'active' : ''; ?>">
                <i class="icon flaticon-aim"></i>
                <a href="<?php echo esc_url(site_url('/user-dashboard?do=upload')); ?>">Upload Documents</a>
            </li>
            <?php if (in_array('administrator', $user->roles)) :   ?>
                <?php
                if ( $query_param==='approve' && ($query_param_catg===$coop || $query_param_catg===$ngo || $query_param_catg===$biz_prem
                    || $query_param_catg===$lafia || $query_param_catg===$akwanga || $query_param_catg===$keffi
                    || $query_param_catg===$packaging || $query_param_catg===$karu || $query_param_catg===$haulage)
                ) {
                    $active_class_reg = 'active';
                } else {
                    $active_class_reg = '';
                }
                ?>
                <li class="nav-list contains-children <?php echo $active_class_reg; ?>">
                    <i class="icon flaticon-team"></i>Admin Approvals <span class="arrow-down"></span>
                    <ul class="children-nav">
                    <?php
                    foreach ($all_child_nav as $key => $value) :
                        ?>
                        <li class="child-nav <?php echo $query_param==='approve' && $query_param_catg===$value ? 'active' : '' ?>">
                            <a href="<?php echo esc_url(site_url('/user-dashboard?do=approve&catg='.$value)); ?>">
                                <?php echo str_replace("_", " ", $key); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </li>
                <?php
                if ( $query_param==='adminview' && ($query_param_catg===$coop || $query_param_catg===$ngo || $query_param_catg===$biz_prem
                    || $query_param_catg===$lafia || $query_param_catg===$akwanga || $query_param_catg===$keffi
                    || $query_param_catg===$packaging || $query_param_catg===$karu || $query_param_catg===$haulage)
                ) {
                    $active_class_reg = 'active';
                } else {
                    $active_class_reg = '';
                }
                ?>
                <li class="nav-list contains-children <?php echo $active_class_reg; ?>">
                    <i class="icon flaticon-team"></i> Admin View <span class="arrow-down"></span>
                    <ul class="children-nav">
                    <?php
                    foreach ($all_child_nav as $key => $value) :
                        ?>
                        <li class="child-nav <?php echo $query_param==='adminview' && $query_param_catg===$value ? 'active' : '' ?>">
                            <a href="<?php echo esc_url(site_url('/user-dashboard?do=adminview&catg='.$value)); ?>">
                                <?php echo str_replace("_", " ", $key); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    </ul>
                </li>
            <?php endif;?>
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
</div>