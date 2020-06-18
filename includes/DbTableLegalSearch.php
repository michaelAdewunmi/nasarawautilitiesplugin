<?php
/**
 * The file to handle table creation for Legal Search
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
 * The Class to handle table creation for Legal Search
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class DbTableLegalSearch extends DbTableUtilities
{
    /**
     * Get things started
     *
     * @access public
     * @since  1.0
    */
    public function __construct()
    {
        global $wpdb;

        $this->table_name  = $wpdb->prefix . 'mtii_legal_search';
        $this->primary_key = 'application_form_id';
        $this->version     = '1.0';
    }

    /**
     * Create the table
     *
     * @access public
     * @since  1.0
    */
    public function create_table()
    {

        global $wpdb;


        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "CREATE TABLE " . $this->table_name . " (
            application_form_id bigint(20) NOT NULL AUTO_INCREMENT,
            invoice_number_filled_against varchar(255) UNIQUE NOT NULL,
            request_ref_filled_against varchar(255) UNIQUE NOT NULL,
            organization varchar(255) NOT NULL,
            name_of_ngo_or_cooperative varchar(255) NOT NULL,
            applicant_full_name varchar(255) NOT NULL,
            phone_number varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            user_id bigint(20) NOT NULL,
            application_date DATE NOT NULL,
            certificate_number varchar(255) NOT NULL,
            date_cert_was_issued DATE NOT NULL,
            allow_edit tinyint(2) NOT NULL DEFAULT 0,
            is_admin_approved ENUM('Awaiting Approval', 'Approved', 'Declined') NOT NULL DEFAULT 'Awaiting Approval',
            PRIMARY KEY  (".$this->primary_key.")
        ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta($sql);
        update_option($this->table_name . '_db_version', $this->version);
    }

    //awaiting_recertification_approval ENUM('NO', 'YES') NOT NULL,

    /**
     * Get columns and formats
     *
     * @access public
     * @since  .0
    */
    public function get_columns()
    {
        global $wpdb;

        $fields = $wpdb->get_results("SHOW COLUMNS FROM $this->table_name");
        $my_fields = array();
        foreach ($fields as $field) {
            if ($field->Type == "bignint(20)" || $field->Type == "tinyint(1)") {
                $my_fields[$field->Field] = "%d";
            } else {
                $my_fields[$field->Field] = "%s";
            }
        }
        return $my_fields;
    }

    /**
     * Get default column values
     *
     * @access public
     * @since  1.0
    */
    public function get_column_defaults()
    {
        global $wpdb;

        $fields = $wpdb->get_results("SHOW COLUMNS FROM $this->table_name");
        $my_fields = array();
        foreach ($fields as $field) {
            if ($field->Type == "bignint(20)" || $field->Type == "tinyint(1)"  || $field->Type == "tinyint(3)") {
                $my_fields[$field->Field] = 0;
            } else if ($field->Field == "nature_of_coop_society" ) {
                $my_fields[$field->Field] = 'Liability Limited';
            } else {
                $my_fields[$field->Field] = "";
            }
        }
        return $my_fields;
    }
}
?>