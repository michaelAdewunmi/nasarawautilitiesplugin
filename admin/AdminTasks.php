<?php
/**
 * The File to handle the admin-facing functionality of the plugin.
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
 * The Class to handle the admin-facing functionality of the plugin.
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class AdminTasks
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
            200,
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
            2900, false
        );
    }

    /**
     * Change the sender email address.
     *
     * @since  1.0.0
     * @return void
     */
    public function wpb_sender_email( $original_email_address ) {
        return 'noreply@mtiinasarawa.com';
    }

    /**
     * Change the sender name shown un the header of sender emails.
     *
     * @since  1.0.0
     * @return void
     */
    public function wpb_sender_name( $original_email_from ) {
        return 'MTII - Nasarawa';
    }

    /**
     * Hide the WordPress Admin Bar
     *
     * @since  1.0.0
     * @return void
     */
    public function remove_admin_bar()
    {
        //if (!current_user_can('administrator') && !is_admin()) {
            show_admin_bar(false);
        //}
    }

    /**
     * Add plugin action links.
     *
     * Add a link to the settings page on the plugins.php page.
     *
     * @since 1.0.0
     *
     * @param  array  $links List of existing plugin action links.
     * @return array         List of modified plugin action links.
     */
    public function mtii_utilities_action_links( $links )
    {
        global $pagenow;
        $mylinks = array(
            '<a href="' . esc_url(admin_url('/admin.php?page=mtii-utilities')) . '">' . __( 'Settings', 'mtii-utilities-josbiz' ) . '</a>'
        );
        return array_merge($mylinks, $links);
    }

    /**
     * Add Notice to the Admin Pages.
     *
     * @since 1.0.0
     *
     */
    public function author_admin_notice()
    {
        global $pagenow;
        $url = esc_url(admin_url('/admin.php?page=mtii-utilities'));
        $mtii_is_live = get_option('mtii_is_live');
        $is_live = isset($mtii_is_live) ? $mtii_is_live : 'off';
        if (is_plugin_active(MTII_UTILITIES_PLUGIN_NAME.'/'.MTII_UTILITIES_PLUGIN_NAME.'.php')) {
            if ($pagenow == 'admin.php' || $pagenow == 'plugins.php') {
                if ($pagenow == 'admin.php') {
                    $link_or_text = 'this Page';
                } else {
                    $link_or_text = 'the <a href="'.$url.'">Settings Page</a>';
                }
                if ($is_live=="on") {
                    echo '<div class="notice notice-success is-dismissible">
                            <h1 style="font-weight: 700; line-height: 1.2;">
                                MTII Nasarawa Notification: You are presently in <span style="color: #04e604;">LIVE MODE</span>
                            </h1>
                        </div>';
                } else {
                    echo '<div class="notice notice-info is-dismissible">
                    <h1 style="font-weight: 700; line-height: 1.2;">
                        MTII Nasarawa Notification: You are presently in <span style="color: red;">TEST MODE</span>
                    </h1>
                    <p style="font-size: 1.3em;"><strong>Please Ensure you switch to Live Mode on
                        '.$link_or_text.' once you are sure you want to go live!</strong>
                    <p>
                    </div>';
                }
            }
        }

    }
}
?>