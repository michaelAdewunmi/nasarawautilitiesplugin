<?php
/**
 * Fired during plugin activation.
 * This Class runs different action and filters used in the plugin
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

use MtiiUtilities\Hooks_Util;
use MtiiUtilities\I18n;
use MtiiUtilities\Admin_Utils;
use MtiiUtilities\Register_Cpt;
use MtiiUtilities\User_Functions;
use MtiiUtilities\Public_Utilities;
use MtiiUtilities\Login_Handlers;
use MtiiUtilities\FrontEnd_Signup;
use MtiiUtilities\Page_Templater;
use MtiiUtilities\Page_Creator;
use MtiiUtilities\Tasks_Performer;


class Loaders_Hooks_And_Filters
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    Mtii_Utilities_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since  1.0.0
     * @access protected
     * @var    string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function __construct() {
        if (defined('MTII_UTILITIES_VERSION')) {
            $this->version = MTII_UTILITIES_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'mtii_utilities';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Mtii_Utilities_Loader. Orchestrates the hooks of the plugin.
     * - Mtii_Utilities_i18n. Defines internationalization functionality.
     * - Mtii_Utilities_Admin. Defines all hooks for the admin area.
     * - Mtii_Utilities_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since  1.0.0
     * @access private
     */
    private function load_dependencies()
    {

        $this->loader = new Hooks_Util();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Mtii_Utilities_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since  1.0.0
     * @access private
     */
    private function set_locale()
    {
        $plugin_i18n = new I18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since 1.0.0
     * @access private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new Admin_Utils($this->get_mtii_utilities_plugin_name(), $this->get_version());
        $reg_utilities_admin = new User_Functions($this->get_mtii_utilities_plugin_name(), $this->get_version());
        $reg_cust_post_type = new Register_Cpt($this->get_mtii_utilities_plugin_name(), $this->get_version());
        $ajax_class = new AjaxCall_Handler($this->get_mtii_utilities_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('after_setup_theme', $plugin_admin, 'remove_admin_bar');
        $this->loader->add_action('user_new_form', $reg_utilities_admin, 'mtii_utilities_admin_reg_form');
        $this->loader->add_action('user_profile_update_errors', $reg_utilities_admin, 'mtii_utilities_profile_update_errors', 10, 3);
        $this->loader->add_action('show_user_profile', $reg_utilities_admin, 'mtii_utilities_show_extra_profile_fields');
        $this->loader->add_action('edit_user_profile', $reg_utilities_admin, 'mtii_utilities_show_extra_profile_fields');
        $this->loader->add_action('init', $reg_cust_post_type, 'register_the_custom_post_types');
        $this->loader->add_action("wp_ajax_mtii_signed_doc_approval", $ajax_class, "mtii_signed_doc_approval");
        $this->loader->add_action("wp_ajax_mtii_signed_doc_disapproval", $ajax_class, "mtii_signed_doc_disapproval");
        $this->loader->add_action("wp_ajax_nopriv_mtii_signed_doc_approval", $ajax_class, "please_login");





        /**
         * ============================================================
         *  Filters
         * ============================================================
         */
        $this->loader->add_filter('wp_mail_from', $plugin_admin, 'wpb_sender_email');
        $this->loader->add_filter('wp_mail_from_name', $plugin_admin, 'wpb_sender_name');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since  1.0.0
     * @access private
     */
    private function define_public_hooks()
    {
        $plugin_public = new Public_Utilities($this->get_mtii_utilities_plugin_name(), $this->get_version());
        $login_handlers = new Login_Handlers($this->get_mtii_utilities_plugin_name(), $this->get_version());
        $signup_public = new FrontEnd_Signup($this->get_mtii_utilities_plugin_name(), $this->get_version());
        $page_templater = new Page_Templater($this->get_mtii_utilities_plugin_name(), $this->get_version());
        $page_creation_class = new Page_Creator($this->get_mtii_utilities_plugin_name(), $this->get_version());
        $task_performer = new Tasks_Performer();

        /**
         * ===============================
         *  Actions
         * ===============================
         */
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
        $this->loader->add_action('login_enqueue_scripts', $login_handlers, 'mtii_utilities_login_enqueue_style');
        $this->loader->add_action('login_enqueue_scripts', $login_handlers, 'mtii_utilities_login_enqueue_script');
        $this->loader->add_action('login_message', $login_handlers, 'mtii_utilities_login_message');
        $this->loader->add_action('admin_init', $login_handlers, 'mtii_utilities_redirect_subs_to_frontend');
        $this->loader->add_action('register_form', $signup_public, 'mtii_utilities_reg_form');
        $this->loader->add_action('user_register', $signup_public, 'mtii_utilities_user_register_meta');
        $this->loader->add_action('plugins_loaded', $page_templater, 'load_all_page_templater_settings_and_filters');
        $this->loader->add_action('admin_init', $page_creation_class, 'create_mtii_user_page_once');
        $this->loader->add_action('init', $task_performer, 'set_a_cookie');

        /**
         * The WordPress action below is actually fired in the admin area and should have been placed
         * in the *define_admin_hooks* (above this method). However, this hooks is also equivalent to
         * the [user_register] action hook above (fired in the frontend). The only difference is that
         * it is fired only for admin update in the admin area while creating users. So, The reason for placing
         * the action here is to utilized the same function as that use in the frontend for
         * registering user meta since its still the same thing I will love to acheieve
         * (which is to register extra fields as sson as the user is added to the DB)
         */
        $this->loader->add_action('edit_user_created_user', $signup_public, 'mtii_utilities_user_register');


        /**
         * ===============================
         *  Filters
         * ===============================
         */
        $this->loader->add_filter('wp_login_errors', $login_handlers, 'mtii_utilities_success_reg_msg', 10, 2);
        $this->loader->add_filter('registration_errors', $signup_public, 'mtii_utilities_reg_errors', 10, 3);

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since 1.0.0
     * @access private
     * @return void
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since  1.0.0
     * @return string    The name of the plugin.
     */
    public function get_mtii_utilities_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since  1.0.0
     * @return Mtii_Utilities_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since  1.0.0
     * @return string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
?>
