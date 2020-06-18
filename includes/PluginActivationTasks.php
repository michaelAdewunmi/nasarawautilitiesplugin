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


/**
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
class PluginActivationTasks
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
        global $mtii_db_coop_main_form;
        global $mtii_signatories_template_db;
        global $mtii_biz_prem_db_main;
        global $mtii_ngo_cbo_db_table;
        global $mtii_cert_replacement_table;
        global $mtii_legal_search_table;

        // $wp_roles = new \WP_Roles(); // create new role object
        // $wp_roles->
        remove_role('mini_admin');

        $args = array(
            'read' => true, 'edit_posts' => true, 'edit_pages' => true, 'edit_others_posts' => true,
            'create_posts' => true, 'publish_posts' => true, 'edit_themes' => false, 'install_plugins' => false,
            'update_plugin' => false, 'update_core' => false
        );

        add_role('mtii_coop_and_ngo_administrator', __('Director for Cooperatives and NGOs'), $args);
        add_role('mtii_biz_prem_administrator', __('Director for Business Premises'), $args);
        add_role('mtii_others_administrator', __('Director for Others'), $args);

        $mtii_db_invoice->create_table();
        $mtii_db_coop_main_form->create_table();
        $mtii_signatories_template_db->create_table();
        $mtii_biz_prem_db_main->create_table();
        $mtii_ngo_cbo_db_table->create_table();
        $mtii_cert_replacement_table->create_table();
        $mtii_legal_search_table->create_table();

        return;
    }
}
?>