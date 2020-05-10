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
 * Defines and register all custom post types and their properties
 *
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/public
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 */
class Register_Cpt
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
            'show_ui'      => true
        );
        register_post_type('mtii_signed_uploads', $args);
    }
}