<?php
/**
 * The public-facing functionality of the plugin.
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

use MtiiUtilities\TasksPerformer;
use MtiiUtilities\MtiiRelatedInformation;


/**
 * A Class to handle public-facing functionality of the plugin.
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class TasksForPublic
{

    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string    $plugin_name    The ID of this plugin.
     */
    private $_plugin_name;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var    string  $version  The current version of this plugin.
     */
    private $_version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of this plugin.
     *
     * @since 1.0.0
     */
    public function __construct( $plugin_name, $version )
    {
        $this->_plugin_name = $plugin_name;
        $this->_version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since  1.0.0
     * @return void
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Mtii_Utilities_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Mtii_Utilities_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style(
            $this->_plugin_name.'-public-main-style',
            WP_CONTENT_URL.'/plugins/'.MTII_UTILITIES_PLUGIN_NAME.'/public/css/mtii-utilities-public-style.css',
            array(), 73, 'all'
        );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        /*
        * This function is provided for demonstration purposes only.
        *
        * An instance of this class should be passed to the run() function
        * defined in Mtii_Utilities_Loader as all of the hooks are defined
        * in that particular class.
        *
        * The Mtii_Utilities_Loader will then create the relationship
        * between the defined hooks and the functions defined in this
        * class.
        */
        wp_enqueue_script(
            $this->_plugin_name.'-public-main-script',
            WP_CONTENT_URL.'/plugins/'.MTII_UTILITIES_PLUGIN_NAME.'/public/js/mtii-utilities-public-script.js',
            array('jquery'),
            4121979, false
        );
        wp_register_script(
            $this->_plugin_name.'-public-user-script',
            WP_CONTENT_URL.'/plugins/'.MTII_UTILITIES_PLUGIN_NAME.'/public/js/user-dashboard.js',
            array('jquery'),
            90129390, false
        );
        wp_register_script(
            $this->_plugin_name.'-doc-approval-script',
            WP_CONTENT_URL.'/plugins/'.MTII_UTILITIES_PLUGIN_NAME.'/public/js/doc-approval.js',
            array('jquery'),
            88192251, false
        );

        $biz_prem = new MtiiRelatedInformation;
        $biz_prem_new_reg = $biz_prem->get_all_biz_premises_amount('mtii_new_registration');
        $biz_prem_renewal = $biz_prem->get_all_biz_premises_amount('mtii_renewal');

        wp_localize_script(
            $this->_plugin_name.'-public-user-script',
            'varsMtii',
            array(
                'siteBaseUrl'           => esc_url(site_url()),
                'bizPremNewRegPrice'    => $biz_prem_new_reg,
                'bizPremRenewalPrice'   => $biz_prem_renewal,
                'ajaxurl'               => admin_url('admin-ajax.php')
            )
        );

        if (is_page('user-dashboard')) {
            wp_enqueue_script($this->_plugin_name.'-public-user-script');
            if (isset($_REQUEST["do"]) && ($_REQUEST["do"]=="approve" || $_REQUEST["do"]=="replacements" || $_REQUEST["do"]=="legal-search")) {
                wp_enqueue_script($this->_plugin_name.'-doc-approval-script');
            }

            $base_array = array('ajaxurl' => admin_url('admin-ajax.php'));
            $admin_add = array();
            $task_performer = new TasksPerformer;
            if ($task_performer->is_mtii_admin()) {
                $coop = $task_performer->get_all_ngos_and_coop();
                $ngo = $task_performer->get_all_ngos_and_coop(true);
                $biz_prem = $task_performer->get_all_registered_business_premise();
                $admin_add = array('ngo_and_coop_list' => array('ngo' => $ngo, 'coop' => $coop, 'biz_prem' => $biz_prem));
            }
            $localized_data = array_merge($base_array, $admin_add);
            wp_localize_script(
                $this->_plugin_name.'-doc-approval-script',
                'mtiiAdminData', $localized_data
                //'bizPremNewRegPrice', $biz_prem_new_reg
            );
        }
    }
}
?>