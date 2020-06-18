<?php
/**
 * The file to register all custom post types
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
 * The Class to register all custom post types
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class CustomPostTypesHandler
{

    /**
     * A Unique Identifier for the Plugin
     */
    protected $plugin_slug;

    /**
     * A Unique Identifier for the Plugin
     */
    protected $plugin_version;

    /**
     * A Reference to an instance of this class
     */
    private static $_instance;

    /**
     * A boolean to tell if user is logged in or not
     *
     * @var boolean
     */
    protected $is_user_logged_in;

    /**
     * Array of templates to be tracked by the plugin
     */
    protected $templates;

    /**
     * Initialize the plugin by setting filters and administration functions
     */
    public function __construct($plugin_name, $plugin_version)
    {
        $this->plugin_slug = $plugin_name;
        $this->plugin_version = $plugin_version;
    }

    public function register_the_custom_post_types() {
        $this->register_invoice_cpt();
        $this->register_duplicate_invoice_cpt();
        $this->register_docs_upload_cpt();
        $this->register_ngo_list_As_Cpt();
        $this->register_Cert_Replacements_As_Cpt();
        $this->register_Legal_Search_As_Cpt();
        $this->register_Business_Premises_As_Cpt();
    }

    /**
     * Register a Custom Post Type for holding users about info
     *
     * @return void
     * @since  1.0
     * @author Michael Adewumi
     */
    public function register_invoice_cpt()
    {
        $labels = array(
            'name'               => _x('Invoice', 'Post Type General Name', 'mtii-utilities-josbiz'),
            'singular_name'      => _x('Invoive', 'Post Type Singular Name', 'mtii-utilities-josbiz'),
            'menu_name'          => __('Invoice', 'mtii-utilities-josbiz'),
            'parent_item_colon'  => __('Parent Invoice:', 'mtii-utilities-josbiz'),
            'all_items'          => __('All Invoices', 'mtii-utilities-josbiz'),
            'view_item'          => __('View Invoice', 'mtii-utilities-josbiz'),
            'add_new_item'       => __('Add New Invoice', 'mtii-utilities-josbiz'),
            'add_new'            => __('New Invoice', 'mtii-utilities-josbiz'),
            'edit_item'          => __('Edit Invoice', 'mtii-utilities-josbiz'),
            'update_item'        => __('Update Invoice', 'mtii-utilities-josbiz'),
            'search_items'       => __('Search Invoices', 'mtii-utilities-josbiz'),
            'not_found'          => __('No Invoices found', 'mtii-utilities-josbiz'),
            'not_found_in_trash' => __('No Invoices found in Trash', 'mtii-utilities-josbiz'),
        );
        $args   = array(
            'labels'       => $labels,
            'supports'     => array( 'title', 'editor', 'author', 'custom-fields' ),
            'menu_icon'    => 'dashicons-welcome-learn-more',
            'public'       => true,
            'rewrite'      => array('slug' => _x('mtii_cbs_invoice', 'URL slug', 'invoices')),
            'has_archive'  => true,
            'show_ui'      => true

        );
        register_post_type('mtii_cbs_invoice', $args);
    }

    public function register_duplicate_invoice_cpt()
    {
        $labels = array(
            'name'               => _x('Duplicate Invoice', 'Post Type General Name', 'mtii-utilities-josbiz'),
            'singular_name'      => _x('Duplicate Invoice', 'Post Type Singular Name', 'mtii-utilities-josbiz'),
            'menu_name'          => __('Duplicate Invoice', 'mtii-utilities-josbiz'),
            'parent_item_colon'  => __('Parent Duplicate Invoice:', 'mtii-utilities-josbiz'),
            'all_items'          => __('All Duplicate Invoices', 'mtii-utilities-josbiz'),
            'view_item'          => __('View Duplicate Invoice', 'mtii-utilities-josbiz'),
            'add_new_item'       => __('Add New Duplicate Invoice', 'mtii-utilities-josbiz'),
            'add_new'            => __('New Duplicate Invoice', 'mtii-utilities-josbiz'),
            'edit_item'          => __('Edit Duplicate Invoice', 'mtii-utilities-josbiz'),
            'update_item'        => __('Update Duplicate Invoice', 'mtii-utilities-josbiz'),
            'search_items'       => __('SearchDuplicate  Invoices', 'mtii-utilities-josbiz'),
            'not_found'          => __('No Duplicate Invoices found', 'mtii-utilities-josbiz'),
            'not_found_in_trash' => __('No Duplicate Invoices found in Trash', 'mtii-utilities-josbiz'),
        );
        $args   = array(
            'labels'       => $labels,
            'supports'     => array( 'title', 'editor', 'author', 'custom-fields' ),
            'menu_icon'    => 'dashicons-welcome-learn-more',
            'public'       => true,
            'rewrite'      => array('slug' => _x('mtii_dup_invoice', 'URL slug', 'dup-invoices')),
            'has_archive'  => true,
            'show_ui'      => false
        );
        register_post_type('mtii_dup_invoice', $args);
    }

    public function register_docs_upload_cpt()
    {
        $labels = array(
            'name'               => _x('Signed Uploads', 'Post Type General Name', 'mtii-utilities-josbiz'),
            'singular_name'      => _x('Signed Upload', 'Post Type Singular Name', 'mtii-utilities-josbiz'),
            'menu_name'          => __('Signed Upload', 'mtii-utilities-josbiz'),
            'parent_item_colon'  => __('Parent Signed Upload:', 'mtii-utilities-josbiz'),
            'all_items'          => __('All Signed Uploads', 'mtii-utilities-josbiz'),
            'view_item'          => __('View Signed Upload', 'mtii-utilities-josbiz'),
            'add_new_item'       => __('Add New Signed Upload', 'mtii-utilities-josbiz'),
            'add_new'            => __('New Signed Upload', 'mtii-utilities-josbiz'),
            'edit_item'          => __('Edit Signed Upload', 'mtii-utilities-josbiz'),
            'update_item'        => __('Update Signed Upload', 'mtii-utilities-josbiz'),
            'search_items'       => __('Search Signed Uploads', 'mtii-utilities-josbiz'),
            'not_found'          => __('No Signed Uploads found', 'mtii-utilities-josbiz'),
            'not_found_in_trash' => __('No Signed Uploads found in Trash', 'mtii-utilities-josbiz'),
        );
        $args   = array(
            'labels'       => $labels,
            'supports'     => array( 'title', 'editor', 'author', 'custom-fields' ),
            'menu_icon'    => 'dashicons-welcome-learn-more',
            'public'       => true,
            // 'rewrite'      => array('slug' => _x('mtii_signed_uploads', 'URL slug', 'dup-invoices')),
            'has_archive'  => true,
            'show_ui'      => false
        );
        register_post_type('mtii_signed_uploads', $args);
    }

    public function register_ngo_list_As_Cpt()
    {
        $labels = array(
            'name'               => _x('NGO', 'Post Type General Name', 'mtii-utilities-josbiz'),
            'singular_name'      => _x('NGO', 'Post Type Singular Name', 'mtii-utilities-josbiz'),
            'menu_name'          => __('NGO', 'mtii-utilities-josbiz'),
            'parent_item_colon'  => __('Parent NGO:', 'mtii-utilities-josbiz'),
            'all_items'          => __('All NGOs', 'mtii-utilities-josbiz'),
            'view_item'          => __('View NGO', 'mtii-utilities-josbiz'),
            'add_new_item'       => __('Add New NGO', 'mtii-utilities-josbiz'),
            'add_new'            => __('New NGO', 'mtii-utilities-josbiz'),
            'edit_item'          => __('Edit NGO', 'mtii-utilities-josbiz'),
            'update_item'        => __('Update NGO', 'mtii-utilities-josbiz'),
            'search_items'       => __('Search NGOs', 'mtii-utilities-josbiz'),
            'not_found'          => __('No NGOs found', 'mtii-utilities-josbiz'),
            'not_found_in_trash' => __('No NGOs found in Trash', 'mtii-utilities-josbiz'),
        );
        $args   = array(
            'labels'       => $labels,
            'supports'     => array( 'title', 'editor', 'author', 'custom-fields' ),
            'menu_icon'    => 'dashicons-welcome-learn-more',
            'public'       => true,
            // 'rewrite'      => array('slug' => _x('mtii_signed_uploads', 'URL slug', 'dup-invoices')),
            'has_archive'  => true,
            'show_ui'      => true
        );
        register_post_type('mtii_ngo_lists', $args);
    }

    public function register_Cert_Replacements_As_Cpt()
    {
        $labels = array(
            'name'               => _x('Certificate Replacement', 'Post Type General Name', 'mtii-utilities-josbiz'),
            'singular_name'      => _x('Certificate Replacement', 'Post Type Singular Name', 'mtii-utilities-josbiz'),
            'menu_name'          => __('Certificate Replacement', 'mtii-utilities-josbiz'),
            'parent_item_colon'  => __('Parent Certificate Replacement:', 'mtii-utilities-josbiz'),
            'all_items'          => __('All Certificate Replacements', 'mtii-utilities-josbiz'),
            'view_item'          => __('View Certificate Replacement', 'mtii-utilities-josbiz'),
            'add_new_item'       => __('Add New Certificate Replacement', 'mtii-utilities-josbiz'),
            'add_new'            => __('New Certificate Replacement', 'mtii-utilities-josbiz'),
            'edit_item'          => __('Edit Certificate Replacement', 'mtii-utilities-josbiz'),
            'update_item'        => __('Update Certificate Replacement', 'mtii-utilities-josbiz'),
            'search_items'       => __('Search Certificate Replacements', 'mtii-utilities-josbiz'),
            'not_found'          => __('No Certificate Replacements found', 'mtii-utilities-josbiz'),
            'not_found_in_trash' => __('No Certificate Replacements found in Trash', 'mtii-utilities-josbiz'),
        );
        $args   = array(
            'labels'       => $labels,
            'supports'     => array( 'title', 'editor', 'author', 'custom-fields' ),
            'menu_icon'    => 'dashicons-welcome-learn-more',
            'public'       => true,
            // 'rewrite'      => array('slug' => _x('mtii_signed_uploads', 'URL slug', 'dup-invoices')),
            'has_archive'  => true,
            'show_ui'      => true
        );
        register_post_type('mtii_cert_replcmnt', $args);
    }

    public function register_Legal_Search_As_Cpt()
    {
        $labels = array(
            'name'               => _x('Legal Search Registration ', 'Post Type General Name', 'mtii-utilities-josbiz'),
            'singular_name'      => _x('Legal Search Registration', 'Post Type Singular Name', 'mtii-utilities-josbiz'),
            'menu_name'          => __('Legal Search Registration', 'mtii-utilities-josbiz'),
            'parent_item_colon'  => __('Parent Legal Search Registration:', 'mtii-utilities-josbiz'),
            'all_items'          => __('All Legal Search Registrations', 'mtii-utilities-josbiz'),
            'view_item'          => __('View Legal Search Registration', 'mtii-utilities-josbiz'),
            'add_new_item'       => __('Add New Legal Search Registration', 'mtii-utilities-josbiz'),
            'add_new'            => __('New Legal Search Registration', 'mtii-utilities-josbiz'),
            'edit_item'          => __('Edit Legal Search Registration', 'mtii-utilities-josbiz'),
            'update_item'        => __('Update Legal Search Registration', 'mtii-utilities-josbiz'),
            'search_items'       => __('Search all Legal Search Registrations', 'mtii-utilities-josbiz'),
            'not_found'          => __('No Legal Search Registrations found', 'mtii-utilities-josbiz'),
            'not_found_in_trash' => __('No Legal Search Registrations found in Trash', 'mtii-utilities-josbiz'),
        );
        $args   = array(
            'labels'       => $labels,
            'supports'     => array( 'title', 'editor', 'author', 'custom-fields' ),
            'menu_icon'    => 'dashicons-welcome-learn-more',
            'public'       => true,
            // 'rewrite'      => array('slug' => _x('mtii_signed_uploads', 'URL slug', 'dup-invoices')),
            'has_archive'  => true,
            'show_ui'      => true
        );
        register_post_type('mtii_legal_search', $args);
    }

    public function register_Business_Premises_As_Cpt()
    {
        $labels = array(
            'name'               => _x('Business Premise Registration ', 'Post Type General Name', 'mtii-utilities-josbiz'),
            'singular_name'      => _x('Business Premise Registration', 'Post Type Singular Name', 'mtii-utilities-josbiz'),
            'menu_name'          => __('Business Premise Registration', 'mtii-utilities-josbiz'),
            'parent_item_colon'  => __('Parent Business Premise Registration:', 'mtii-utilities-josbiz'),
            'all_items'          => __('All Business Premise Registrations', 'mtii-utilities-josbiz'),
            'view_item'          => __('View Business Premise Registration', 'mtii-utilities-josbiz'),
            'add_new_item'       => __('Add New Business Premise Registration', 'mtii-utilities-josbiz'),
            'add_new'            => __('New Business Premise Registration', 'mtii-utilities-josbiz'),
            'edit_item'          => __('Edit Business Premise Registration', 'mtii-utilities-josbiz'),
            'update_item'        => __('Update Business Premise Registration', 'mtii-utilities-josbiz'),
            'search_items'       => __('Search all Business Premise Registrations', 'mtii-utilities-josbiz'),
            'not_found'          => __('No Business Premise Registrations found', 'mtii-utilities-josbiz'),
            'not_found_in_trash' => __('No Business Premise Registrations found in Trash', 'mtii-utilities-josbiz'),
        );
        $args   = array(
            'labels'       => $labels,
            'supports'     => array( 'title', 'editor', 'author', 'custom-fields' ),
            'menu_icon'    => 'dashicons-welcome-learn-more',
            'public'       => true,
            'has_archive'  => true,
            'show_ui'      => true
        );
        register_post_type('mtii_biz_prem_reg', $args);
    }
}
?>