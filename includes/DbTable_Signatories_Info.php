<?php
namespace MtiiUtilities;
use MtiiUtilities\Db_Utilities;


class DbTable_Signatories_Info extends Db_Utilities
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

        $this->table_name  = $wpdb->prefix . 'mtii_coop_signatories_template';
        $this->primary_key = 'signatories_form_id';
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
            signatories_form_id bigint(20) NOT NULL AUTO_INCREMENT,
            main_coop_form_id bigint(20) UNIQUE NOT NULL,
            name_one varchar(255) NOT NULL,
            occupation_one varchar(255) NOT NULL,
            village_one varchar(255) NOT NULL,
            lga_one varchar(255) NOT NULL,
            name_two varchar(255) NOT NULL,
            occupation_two varchar(255) NOT NULL,
            village_two varchar(255) NOT NULL,
            lga_two varchar(255) NOT NULL,
            name_three varchar(255) NOT NULL,
            occupation_three varchar(255) NOT NULL,
            village_three varchar(255) NOT NULL,
            lga_three varchar(255) NOT NULL,
            name_four varchar(255) NOT NULL,
            occupation_four varchar(255) NOT NULL,
            village_four varchar(255) NOT NULL,
            lga_four varchar(255) NOT NULL,
            name_five varchar(255) NOT NULL,
            occupation_five varchar(255) NOT NULL,
            village_five varchar(255) NOT NULL,
            lga_five varchar(255) NOT NULL,
            name_six varchar(255) NOT NULL,
            occupation_six varchar(255) NOT NULL,
            village_six varchar(255) NOT NULL,
            lga_six varchar(255) NOT NULL,
            name_seven varchar(255) NOT NULL,
            occupation_seven varchar(255) NOT NULL,
            village_seven varchar(255) NOT NULL,
            lga_seven varchar(255) NOT NULL,
            name_eight varchar(255) NOT NULL,
            occupation_eight varchar(255) NOT NULL,
            village_eight varchar(255) NOT NULL,
            lga_eight varchar(255) NOT NULL,
            name_nine varchar(255) NOT NULL,
            occupation_nine varchar(255) NOT NULL,
            village_nine varchar(255) NOT NULL,
            lga_nine varchar(255) NOT NULL,
            name_ten varchar(255) NOT NULL,
            occupation_ten varchar(255) NOT NULL,
            village_ten varchar(255) NOT NULL,
            lga_ten varchar(255) NOT NULL,
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