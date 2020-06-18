<?php
/**
 * The file to handle-facing functionality of the plugin.
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
 * The Class to handle using the settings Api and creation of various Admin pages
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class AdminPages
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

    public function mtii_utilities_create_menu_page()
    {
        add_menu_page(
            'Mtii Utilities', 'Nasarawa-Mtii', 'manage_options', 'mtii-utilities',
            array($this, 'sub_menu_settings_page_display'), 'dashicons-admin-settings', 2
        );

        add_submenu_page(
            'mtii-utilities', 'General Settings', 'General Settings', 'manage_options',
            'mtii-utilities', array($this, 'sub_menu_settings_page_display')
        );

        add_submenu_page(
            'mtii-utilities', 'Admin View', 'Admin Views', 'manage_options',
            'mtii-utilities-views', array($this, 'mtii_utilities_sub_menu_page_display')
        );
    }

    public function sub_menu_settings_page_display()
    {
        include_once WP_CONTENT_DIR . '/plugins/'.MTII_UTILITIES_PLUGIN_NAME.'/admin/views/mtii-admin-general-settings.php';
    }

    public function mtii_utilities_sub_menu_page_display()
    {
        include_once WP_CONTENT_DIR . '/plugins/'.MTII_UTILITIES_PLUGIN_NAME.'/admin/views/mtii-admin-view.php';
    }

    public function set_environment_as_live()
    {
        echo '';
    }

    public function mtii_utilities_admin_settings()
    {
        add_settings_section(
            'general_settings_section', 'All general settings',
            array($this, 'set_environment_as_live'), 'mtii-utilities'
        );
        add_settings_field(
            'mtii_is_live',  'Go Live',
            array($this, 'set_as_live'), 'mtii-utilities', 'general_settings_section'
        );
        $mtii_is_live = get_option('mtii_is_live');
        $value_mode = isset($mtii_is_live) ? $mtii_is_live : '';
        add_settings_field(
            'mtii_live_api_key_client_id',  'Live Client ID',
            array($this, 'set_live_api_key_client_id'), 'mtii-utilities', 'general_settings_section'
        );
        add_settings_field(
            'mtii_live_api_key_client_secret',  'Live Client Secret',
            array($this, 'set_live_api_key_client_secret'), 'mtii-utilities', 'general_settings_section'
        );
        add_settings_field(
            'mtii_test_api_key_client_id',  'Test Client ID',
            array($this, 'set_test_api_key_client_id'), 'mtii-utilities', 'general_settings_section'
        );
        add_settings_field(
            'mtii_test_api_key_client_secret',  'Test Client Secret',
            array($this, 'set_test_api_key_client_secret'), 'mtii-utilities', 'general_settings_section'
        );

        register_setting('mtii-general-settings', 'mtii_is_live');
        register_setting('mtii-general-settings', 'mtii_live_api_key_client_id');
        register_setting('mtii-general-settings', 'mtii_live_api_key_client_secret');
        register_setting('mtii-general-settings', 'mtii_test_api_key_client_id');
        register_setting('mtii-general-settings', 'mtii_test_api_key_client_secret');
    }

    public function set_as_live()
    {
        $mtii_is_live = get_option('mtii_is_live');
        $value = isset($mtii_is_live) ? $mtii_is_live : '';
        $checked = $value==='on' ? 'checked' : '';
        echo '<input type="checkbox" name="mtii_is_live" id="set-as-live" '.$checked.'/>';
    }

    public function set_live_api_key_client_id()
    {
        $mtii_live_api_key_client_id = get_option('mtii_live_api_key_client_id');
        $value = isset($mtii_live_api_key_client_id) ? $mtii_live_api_key_client_id : '';
        $readonly = $value && $value!="" ? "readonly" : "";
        echo '<input style="min-width: 300px;" type="text"
                name="mtii_live_api_key_client_id" id="" value="'.$value.'" '.$readonly.' />
                <span class="edit-option">Edit</span>';
    }

    public function set_live_api_key_client_secret()
    {
        $mtii_live_api_key_client_secret = get_option('mtii_live_api_key_client_secret');
        $value = isset($mtii_live_api_key_client_secret) ? $mtii_live_api_key_client_secret : '';
        $readonly = $value && $value!="" ? "readonly" : "";
        echo '<input style="min-width: 300px;" type="text"
                name="mtii_live_api_key_client_secret" value="'.$value.'" '.$readonly.' />
                <span class="edit-option">Edit</span>';
    }

    public function set_test_api_key_client_id()
    {
        $mtii_test_api_key_client_id = get_option('mtii_test_api_key_client_id');
        $value = isset($mtii_test_api_key_client_id) ? $mtii_test_api_key_client_id : '';
        $readonly = $value && $value!="" ? "readonly" : "";
        echo '<input style="min-width: 300px;" type="text"
            name="mtii_test_api_key_client_id" value="'.$value.'" '.$readonly.' />
            <span class="edit-option">Edit</span>';
    }

    public function set_test_api_key_client_secret()
    {
        $mtii_test_api_key_client_secret = get_option('mtii_test_api_key_client_secret');
        $value = isset($mtii_test_api_key_client_secret) ? $mtii_test_api_key_client_secret : '';
        $readonly = $value && $value!="" ? "readonly" : "";
        echo '<input style="min-width: 300px;" type="text"
                name="mtii_test_api_key_client_secret" value="'.$value.'" '.$readonly.' />
                <span class="edit-option">Edit</span>';
    }
}
?>