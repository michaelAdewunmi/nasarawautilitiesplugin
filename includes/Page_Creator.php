<?php
/**
 * A class to create required pages
 *
 * @category   Plugins
 * @package    MakeInNigeria_Community
 * @subpackage Makeinnigeria_Community/includes
 * @author     Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    MIT https://licemseme.org
 * @link       http://example.com
 * @since      1.0.0
 */
namespace MtiiUtilities;

class Page_Creator
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
     * Initialize the class constructor
     */
    public function __construct($plugin_name, $plugin_version)
    {
        $this->plugin_slug = $plugin_name;
        $this->plugin_version = $plugin_version;
    }

    /**
     * Create a user profile page and only do it once
     *
     * @return void
     */
    public function create_mtii_user_page_once() {
        $page = get_page_by_path('user-dashboard');
        $new_page_template = '../public/templates/mtii_user-template.php';

        if (!isset($page)) :
            $new_page_id = wp_insert_post(
                array (
                    'post_type'     => 'page',
                    'post_title'     => 'User Profile Page',
                    'post_content'  => "[mtii_user_dahsboard]",
                    'post_status'   => 'publish',
                    'guid'          => 'mtii_user_dashboard',
                    'post_name'     => 'user-dashboard'
                )
            );
            if (!empty($new_page_template)) {
                update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
            }
        endif;

        $page_cbs = get_page_by_path('cbscburl');
        $new_page_template_cbs = '../public/templates/mtii_user-cbscburl.php';
        if (!isset($page_cbs)) :
            $new_page_id_cbs = wp_insert_post(
                array (
                    'post_type'     => 'page',
                    'post_title'     => 'cbscburl',
                    'post_content'  => "",
                    'post_status'   => 'publish',
                    'guid'          => 'cbscburl',
                    'post_name'     => 'cbscburl'
                )
            );
            if (!empty($new_page_template_cbs)) {
                update_post_meta($new_page_id_cbs, '_wp_page_template', $new_page_template_cbs);
            }
        endif;

        $page_upload_dummy_cert = get_page_by_path('download-dummy-certificate');
        $new_page_cert_download = '../public/templates/create-dummy-certificates/create-certificate-dummy.php';
        if (!isset($page_upload_dummy_cert)) :
            $page_upload_dummy_cert = wp_insert_post(
                array (
                    'post_type'     => 'page',
                    'post_title'    => 'Download Dummy Certificate',
                    'post_content'  => '',
                    'post_status'   => 'publish',
                    'guid'          => 'download-dummy-certificate',
                    'post_name'     => 'download-dummy-certificate'
                )
            );
            if (!empty($new_page_cert_download)) {
                update_post_meta($page_upload_dummy_cert, '_wp_page_template', $new_page_cert_download);
            }
        endif;
    }
}
