<?php
/**
 * Fired during plugin activation.
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
namespace MtiiUtilities;

class Activation_Utils
{
    /**
     * Run these codes during plugin activation
     *
     * This might be used for creating options tables and
     * doing other plugin specific functionalities towards
     * setting posts, pages, options default
     *
     * @since  1.0.0
     * @return void
     */
    public static function activate()
    {
        global $mtii_db_invoice;
        global $mtii_db_coop_reg;
        global $mtii_db_coop_main_form;
        global $mtii_signatories_template_db;
        global $mtii_biz_prem_db_main;

        $coop_dcs->create_table();
        $invoice_db->create_table();
        $coop_main_form->create_table();
        $mtii_signatories_template_db->create_table();
        $mtii_biz_prem_db_main->create_table();

        return;
    }
}
