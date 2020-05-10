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
class Admin_Utils
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
            $this->_plugin_name,
            plugin_dir_url(__FILE__) . 'css/mtii-utilities-admin-style.css',
            array(),
            $this->_version,
            'all'
        );

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since 1.0.0
     * @return void
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
            $this->_plugin_name.'-admin-main-script',
            plugin_dir_url(__FILE__) . 'js/mtii-utilities-admin-script.js',
            array('jquery'),
            $this->_version, false
        );
    }

    /**
     * Change the sender email address.
     *
     * @since  1.0.0
     * @return void
     */
    public function wpb_sender_email( $original_email_address ) {
        return 'noreply@mtiinassarrawa.com';
    }

    /**
     * Change the sender name shown un the header of sender emails.
     *
     * @since  1.0.0
     * @return void
     */
    public function wpb_sender_name( $original_email_from ) {
        return 'MTII - Nassarrawa';
    }

    /**
     * Hide the WordPress Admin Bar
     *
     * @since  1.0.0
     * @return void
     */
    public function remove_admin_bar() {
        //if (!current_user_can('administrator') && !is_admin()) {
            show_admin_bar(false);
        //}
    }
}
