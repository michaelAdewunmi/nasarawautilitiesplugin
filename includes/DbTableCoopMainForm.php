<?php
/**
 * The file to handle table creation for cooperative's main form
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
use MtiiUtilities\DbTableUtilities;

/**
 * The class to handle table creation for certificate replacements
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class DbTableCoopMainForm extends DbTableUtilities
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

        $this->table_name  = $wpdb->prefix . 'mtii_coop_application_mainform';
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
            name_of_proposed_society varchar(255) NOT NULL,
            name_of_approved_society varchar(255) UNIQUE,
            ward_of_proposed_society varchar(255) NOT NULL,
            lga_of_proposed_society varchar(255) NOT NULL,
            date_of_establisment date NOT NULL,
            address_of_proposed_society varchar(255) NOT NULL,
            area_of_operation varchar(255) NOT NULL DEFAULT 'Raise Capital',
            specific_objectives_of_society text NOT NULL,
            value_of_share_holding varchar(255) NOT NULL,
            number_of_shares_per_member varchar(255) NOT NULL,
            total_shared_capital_paid varchar(255) NOT NULL,
            total_deposit_savings varchar(255) NOT NULL,
            nature_of_proposed_banker varchar(255) NOT NULL,
            nature_of_coop_society ENUM('Liability Limited', 'Liability Unlimited') NOT NULL DEFAULT 'Liability Limited',
            entrance_fee_payable_per_member varchar(255) NOT NULL,
            number_of_memb_at_appl_time varchar(255) NOT NULL,
            name_of_president varchar(255) NOT NULL,
            number_of_president varchar(255) NOT NULL,
            name_of_vice varchar(255) NOT NULL,
            number_of_vice varchar(255) NOT NULL,
            name_of_secretary varchar(255) NOT NULL,
            number_of_secretary varchar(255) NOT NULL,
            name_of_treasurer varchar(255) NOT NULL,
            number_of_treasurer varchar(255) NOT NULL,
            brief_description_of_society_activity text NOT NULL,
            approved_to_exist tinyint(1) NOT NULL DEFAULT false,
            admin_approved ENUM('Awaiting Approval', 'Approved', 'Declined') NOT NULL DEFAULT 'Awaiting Approval',
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