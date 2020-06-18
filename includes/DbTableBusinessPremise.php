<?php
/**
 * The file to handle table creation for business premises
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
 * The Class to handle table creation for business premises
 *
 * @category   Plugins
 * @package    Mtii_Utilities
 * @subpackage Mtii_Utilities/includes
 * @author     Josbiz - Michael Adewunmi <d.devignersplace@gmail.com>
 * @license    GPL-2.0+ http://www.gnu.org/licenses/gpl-2.0.txt
 * @link       http://josbiz.com.ng
 * @since      1.0.0
 */
class DbTableBusinessPremise extends DbTableUtilities
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

        $this->table_name  = $wpdb->prefix . 'mtii_biz_prem_mainform';
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
            name_of_company varchar(255) UNIQUE NOT NULL,
            lga_of_company varchar(255) NOT NULL,
            date_of_registration DATE NOT NULL,
            nature_of_business varchar(255) NOT NULL,
            address_of_premise varchar(255) NOT NULL,
            director_one_name varchar(255) NOT NULL,
            director_one_number varchar(255) NOT NULL,
            director_two_name varchar(255) NOT NULL,
            director_two_number varchar(255) NOT NULL,
            director_three_name varchar(255) NOT NULL,
            director_three_number varchar(255) NOT NULL,
            director_four_name varchar(255) NOT NULL,
            director_four_number varchar(255) NOT NULL,
            director_five_name varchar(255) NOT NULL,
            director_five_number varchar(255) NOT NULL,
            annual_turnover varchar(255) NOT NULL,
            is_premise_rented ENUM('Yes', 'No') NOT NULL DEFAULT 'Yes',
            name_of_landlord varchar(255) NOT NULL,
            address_of_landlord varchar(255) NOT NULL,
            time_of_declaration varchar(50) NOT NULL,
            day_of_declaration varchar(255) NOT NULL,
            month_of_declaration varchar(255) NOT NULL,
            year_of_declaration varchar(255) NOT NULL,
            name_of_declarator varchar(255) NOT NULL,
            position_of_declarator varchar(255) NOT NULL,
            is_admin_approved ENUM('Approved', 'Declined', 'Awaiting Approval') NOT NULL DEFAULT 'Awaiting Approval',
            PRIMARY KEY  (".$this->primary_key.")
        ) CHARACTER SET utf8 COLLATE utf8_general_ci;";

        dbDelta($sql);
        update_option($this->table_name . '_db_version', $this->version);
        // place_of_declaration varchar(255) NOT NULL,
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
            } else if ($field->Field == "liability_limited_or_unlimited" ) {
                $my_fields[$field->Field] = 'Limited Liability';
            } else {
                $my_fields[$field->Field] = "";
            }
        }
        return $my_fields;
    }

    /**
     * Retrieve rows by a specific column / value
     *
     * @access public
     * @since  2.1
     * @return object
     */
    public function get_multiple_rows_by( $column, $column_val, $limit, $last_id=0, $is_prev=false, $get_all_data=null )
    {
        global $wpdb;
        $column = esc_sql($column);
        if ($get_all_data) {
            return $wpdb->get_results("SELECT * FROM $this->table_name ORDER BY $this->primary_key ASC;");
        } else {
            if ($last_id==0) {
                return $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM $this->table_name ORDER BY $this->primary_key DESC LIMIT %d;",
                        array ($limit)
                    )
                );
            } else {
                if ($is_prev) {
                    return array_reverse(
                        $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT * FROM $this->table_name WHERE $this->primary_key > %s ORDER BY $this->primary_key ASC LIMIT %d;",
                                array ($last_id, $limit)
                            )
                        )
                    );
                } else {
                    return $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM $this->table_name WHERE $this->primary_key < %s ORDER BY $this->primary_key DESC LIMIT %d;",
                            array ($last_id, $limit)
                        )
                    );
                }
            }
        }
    }
}
?>