<?php
if (!is_user_logged_in()  || !isset($_REQUEST["n"])) {
    wp_redirect(site_url('/'));
    exit;
}

if (isset($_REQUEST["catg"]) && $_REQUEST["catg"]==="cooperative") :
    include_once "dummy-certificate-cooperative.php";
elseif (isset($_REQUEST["catg"]) && $_REQUEST["catg"]==="biz_prem") :
    include_once "dummy-certificate-biz-prem.php";
endif;
