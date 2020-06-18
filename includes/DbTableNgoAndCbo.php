<?php
/**
 * The file to handle table creation for Ngo and Cbo
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
 * The Class to handle table creation for Ngo and Cbo
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class DbTableNgoAndCbo extends DbTableUtilities
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

        $this->table_name  = $wpdb->prefix . 'mtii_ngo_and_cbo_info';
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
            user_id bigint(20) NOT NULL,
            invoice_number_filled_against varchar(255) UNIQUE NOT NULL,
            request_ref_filled_against varchar(255) UNIQUE NOT NULL,
            name_of_proposed_organization varchar(255) NOT NULL,
            name_of_approved_organization varchar(255) UNIQUE DEFAULT NULL,,
            lga_of_proposed_organization varchar(255) NOT NULL,
            date_of_establishment date NOT NULL,
            address_of_proposed_organization varchar(255) NOT NULL,
            area_of_operation varchar(255) NOT NULL DEFAULT 'Raise Capital',
            specific_objectives_of_organization text NOT NULL,
            donor_support_agency text NOT NULL,
            proposed_project text NOT NULL,
            name_of_proposed_banker varchar(255) NOT NULL,
            name_of_coordinator varchar(255) NOT NULL,
            number_of_coordinator varchar(255) NOT NULL,
            name_of_assistant_coordinator varchar(255) NOT NULL,
            number_of_assistant_coordinator varchar(255) NOT NULL,
            name_of_secretary varchar(255) NOT NULL,
            number_of_secretary varchar(255) NOT NULL,
            brief_description_of_activity text NOT NULL,
            name_of_attester varchar(255) NOT NULL,
            date_of_attestation varchar(255) NOT NULL,
            is_admin_approved ENUM('Approved', 'Declined', 'Awaiting Approval') NOT NULL DEFAULT 'Awaiting Approval',
            approved_to_exist tinyint(1) NOT NULL DEFAULT false,
            PRIMARY KEY  (".$this->primary_key.")
        ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta($sql);
        update_option($this->table_name . '_db_version', $this->version);
    }

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
            if ($field->Type == "bignint(20)" || $field->Type == "tinyint(1)"  || $field->Type == "tinyint(3)") {
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
            } else {
                $my_fields[$field->Field] = "";
            }
        }
        return $my_fields;
    }
}
?>