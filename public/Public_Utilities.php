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

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 */
class Public_Utilities
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
            plugin_dir_url(__FILE__) . 'css/mtii-utilities-public-style.css',
            array(), 203597, 'all'
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
            plugin_dir_url(__FILE__) . 'js/mtii-utilities-public-script.js',
            array('jquery'),
            11979, false
        );
        wp_register_script(
            $this->_plugin_name.'-public-user-script',
            plugin_dir_url(__FILE__) . 'js/user-dashboard.js',
            array('jquery'),
            1109, false
        );
        wp_register_script(
            $this->_plugin_name.'-doc-approval-script',
            plugin_dir_url(__FILE__) . 'js/doc-approval.js',
            array('jquery'),
            11989, false
        );

        include_once WP_CONTENT_DIR . '/plugins/mtii-utilities/public/class-mtii-parameters-setter-and-getters.php';
        $biz_prem = new Mtii_Parameters_Setter_And_Getter;
        $biz_prem_new_reg = $biz_prem->get_all_biz_premises_amount('mtii_new_registration');
        $biz_prem_renewal = $biz_prem->get_all_biz_premises_amount('mtii_renewal');

        wp_localize_script(
            $this->_plugin_name.'-public-user-script',
            'varsMtii',
            array(
                'siteBaseUrl'               => esc_url(site_url()),
                'bizPremNewRegPrice'        => $biz_prem_new_reg,
                'bizPremRenewalPrice'        => $biz_prem_renewal
            )
        );

        if (is_page('user-dashboard')) {
            wp_enqueue_script($this->_plugin_name.'-public-user-script');
            if (isset($_REQUEST["do"]) && $_REQUEST["do"]=="approve") {
                wp_enqueue_script($this->_plugin_name.'-doc-approval-script');
            }

            wp_localize_script(
                $this->_plugin_name.'-doc-approval-script',
                'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ))
                //'bizPremNewRegPrice', $biz_prem_new_reg
            );
        }
    }

}
