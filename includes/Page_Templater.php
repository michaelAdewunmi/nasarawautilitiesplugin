<?php
/**
 * A class to define custom templates to be used in pages and posts
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    MIT https://licemseme.org
 * @link       http://example.com
 * @since      1.0.0
 */
namespace MtiiUtilities;

class Page_Templater
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
     * Array of templates to be tracked by the plugin
     */
    protected $templates;

    // /**
    //  * Returns an Instance of the Class.
    //  *
    //  * @return self
    //  */
    // public static function get_instance($plugin_name, $plugin_version)
    // {
    //     if (null==self::$_instance) {
    //         self::$_instance = new self($plugin_name, $plugin_version);
    //     }
    //     return self::$_instance;
    // }

    /**
     * Initialize the class constructor
     *
     * @param string $plugin_name    Unique Identifier of the plugin
     * @param string $plugin_version Version of plugin
     *
     * @since  1.0.0
     * @return void
     */
    public function __construct($plugin_name, $plugin_version)
    {
        $this->plugin_slug = $plugin_name;
        $this->plugin_version = $plugin_version;
        $this->templates = array();
    }

    /**
     * Initialize the plugin by setting filters and administration functions
     *
     * @since  1.0.0
     * @return void
     */
    public function load_all_page_templater_settings_and_filters() {
        // Add a filter to the attributes metabox which injects template
        // into the cache
        if (version_compare(floatval(get_bloginfo('version')), '4.7', '<') ) {
            // Version 4.6 and older
            add_filter('page_attributes_dropdown_pages_args', array($this, 'register_project_templates'));
        } else {
            // Add Filter to wp 4.7 version attr metabox
            add_filter('theme_page_templates', array($this, 'add_new_template'));
        }

        //Add a filter to save the post to inject out template into the page cache
        add_filter('wp_insert_post_data', array($this, 'register_project_templates'));

        //Add a filter to the template_include to determine if the page has
        // our custom template assigned and return the custom template path
        add_filter('template_include', array($this, 'view_project_template'));

        //Add the custom Template to the template Array.
        $this->templates = array (
            // We get the relative path of the template. This file (that holds this code i.e this page_templater.php)
            // is presently in the includes folder, so we move backwards to lead us to the public, then templates folder
            // and then grab all required templates.
            '../public/templates/mtii_user-template.php' => 'Registered User Page Template',
            '../public/templates/mtii_user-cbs.php' => 'CBS Api Call',
            '../public/templates/mtii_user-cbscburl.php' => 'CBS URL CallBacks',
            '../public/templates/create-dummy-certificates/create-certificate-dummy.php' => 'Dummy Certificate Printing',
            //'../public/templates/mtii_user-upload-doc.php' => 'Document upload template',
        );
    }


    /**
     * Adds our template to the page dropdown for v4.7+
     *
     * @param array $posts_templates A list of all registered templates
     *
     * @since  1.0.0
     * @return void
     */
    public function add_new_template( $posts_templates )
    {
        $posts_templates = array_merge($posts_templates, $this->templates);
        return $posts_templates;
    }

    /**
     * Registers custom Templates into the list of templates in the cache
     *
     * @param [array] $atts This is also returned by the function
     *
     * @return $atts
     */
    public function register_project_templates($atts)
    {
        //create the key used for the themes cache
        $cache_key = 'page_templates-'.md5(get_theme_root().'/'.get_stylesheet());

        // Retrieve the cache list  and save in $templates.
        // If it doesnt exist, or it's empty, prepare an array to hold custom templates
        $templates = wp_get_theme()->get_page_templates();
        if (empty($templates)) {
            $templates = array();
        }

        // We will create a New cache, therefore remove the old one
        wp_cache_delete($cache_key, 'themes');

        // Now Add our template to the list of templates by merging our templates
        // with the existing templates array from the cache
        $templates = array_merge($templates, $this->templates);

        // Now, Add a new cache whicn includes the default theme templates and
        // our custom template to allow WordPress pick it up when Listing all available templates
        wp_cache_add($cache_key, $templates, 'themes', 1800);

        return $atts;
    }

    /**
     * Check if the Template is assigned to the Page
     *
     * @param [array] $template An array of custom templates and default templates
     *
     * @return $template
     */
    public function view_project_template( $template )
    {
        // Return the search template if we're searching (instead of the template for the first result)
        if (is_search()) {
            return $template;
        }

        // Get global post variable
        global $post;

        // Return template if post is empty
        if (!$post) {
            return $template;
        }

        //Return default template if we dont have a custom one defined
        if (!isset($this->templates[get_post_meta($post->ID, '_wp_page_template', true)]) ) {

            return $template;
        }

        // Allows filtering of file path
        $file_path = apply_filters('page_templater_plugin_dir_path', plugin_dir_path(__FILE__));

        $file = $file_path.get_post_meta($post->ID, '_wp_page_template', true);

        //Just to be safe, we check if the file exist first
        if (file_exists($file)) {
            return $file;
        } else {
            echo $file;
        }

        return $template;
    }
}