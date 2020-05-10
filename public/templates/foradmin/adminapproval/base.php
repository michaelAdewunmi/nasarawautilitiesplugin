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
 */

require_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-utilities-task-performer.php';
?>

<div class="section-body">
    <h2 class="section-heading">Approve Documents or Registration</h2>
    <hr class="header-lower-rule" />
    <?php
    if ($query_param=='approve' && ($query_param_catg==$coop)) :
        include_once "sign-uploads-coop.php";
    elseif ($query_param=='approve' && ($query_param_catg==$biz_prem)) :
        include_once "approve-biz-prem-reg.php";
    endif;
    ?>
</div>