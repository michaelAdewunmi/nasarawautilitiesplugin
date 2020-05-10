<?php

/**
 * The file handling the actions and filters hooks specific to login page
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */

/**
 * Enqueues the stylesheet for wordpress login page and also takes care of
 * the filter functions related to the page
 *
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/inlcudes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 */
namespace MtiiUtilities;

class Login_Handlers
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


    // add_filter( 'register_url', 'custom_register_url' );
    // function custom_register_url( $register_url )
    // {
    //     $register_url = get_permalink(9);
    //     return $register_url;
    // }



    /**
     * Enqueues the styles into the login page
     *
     * @since  1.0.0
     * @return void
     */
    public function mtii_utilities_login_enqueue_style() {
        if (function_exists('thegov_fonts_url')) {
            wp_enqueue_style('mtii_utilities-fonts', thegov_fonts_url());
        }
        wp_enqueue_style(
            $this->_plugin_name.'-public-main-style',
            WP_CONTENT_URL . '/plugins/mtii-utilities/public/css/mtii-utilities-public-style.css',
            array(), 17200, 'all'
        );
    }

    /**
     * Register the JavaScript for the login page of the site.
     *
     * @since  1.0.0
     * @return void
     */
    public function mtii_utilities_login_enqueue_script()
    {
        wp_register_script(
            $this->_plugin_name.'_general_script',
            WP_CONTENT_URL . '/plugins/mtii-utilities/public/js/mtii-utilities-public-script.js',
            array( 'jquery' ), 17200, true
        );

        // localize the script to your domain name, so that you can reference the url to admin-ajax.php file easily
        wp_localize_script(
            $this->_plugin_name.'_general_script',
            'myAjax', array( 'ajaxurl' => admin_url('admin-ajax.php'))
        );
        wp_enqueue_script($this->_plugin_name.'_general_script');

    }


    /**
     * Redirect Users that are not administrators on the website
     *
     * @since  1.0.0
     * @return void
     */
    public function mtii_utilities_redirect_subs_to_frontend()
    {
        $currentUser = wp_get_current_user();
        if (!in_array('administrator', $currentUser->roles)) {
            wp_redirect(site_url('/user-dashboard?do=profile'));
            exit;
        }
    }

    /**
     * Adjust registration message in the registration page
     *
     * @since  1.0.0
     * @return void
     */
    public function mtii_utilities_login_message($message)
    {
        if (strpos($message, 'Register') !== FALSE) {
            $new_message = 'Sign up to start your Registration process';
            return '<p id="reg-notifier" class="message register">' . $new_message . '</p>';
        } else {
            return $message;
        }
    }

    /**
     * Successful registration notification that shows on the frontend
     *
     * @since  1.0.0
     * @return [array] $errors An array of wp errors and registration status
     */
    public function mtii_utilities_success_reg_msg($errors, $redirect_to)
    {

        if (isset($errors->errors['registered'])) {

            $tmp = $errors->errors;

            $old_msg = __('Registration complete. Please check your email.');
            $new_msg = 'Your Registration was successful! We have sent you a confirmation email. Please check your email and click the link to change the preset password';

            foreach ($tmp['registered'] as $index=>$msg) {
                if ($msg === $old_msg) {
                    $tmp['registered'][$index] = $new_msg;
                }
            }

            $errors->errors = $tmp;

            unset($tmp);
        }

        return $errors;
    }
}