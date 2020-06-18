<?php
/**
 * This file is the template for the approvals by Admin
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 *
 * $query_param_catg, $query_param, $query_param_catg_repl, repl_coop, repl_ngo, $coop, $ngo,
 * have all been defined in the file where this file is required.
 */
?>

<div class="section-body">
    <h2 class="section-heading">Approve Documents or Registration</h2>
    <hr class="header-lower-rule" />
    <?php
    if ($query_param=='approve' && ($query_param_catg==$coop)) :
        include_once "sign-uploads-coop.php";
    elseif ($query_param=='approve' && ($query_param_catg==$biz_prem)) :
        include_once "approve-biz-prem-reg.php";
    elseif ($query_param=='approve' && ($query_param_catg==$ngo)) :
        include_once "approve-ngo.php";
    elseif ($query_param=='replacements' && ($query_param_catg_repl==$repl_coop || $query_param_catg_repl==$repl_ngo)) :
        include_once "cert-replacement-approval.php";
    elseif ($query_param=='legal-search' && ($query_param_catg_repl==$repl_coop || $query_param_catg_repl==$repl_ngo)) :
        include_once "legal-search-approval.php";
    endif;
    ?>
</div>