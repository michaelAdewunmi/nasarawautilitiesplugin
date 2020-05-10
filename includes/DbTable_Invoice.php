<?php
namespace MtiiUtilities;

use MtiiUtilities\Db_Utilities;

class DbTable_Invoice extends Db_Utilities
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

        $this->table_name  = $wpdb->prefix . 'mtii_cbs_fully_paid_invoices';
        $this->primary_key = 'invoice_id';
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
            invoice_id bigint(20) NOT NULL AUTO_INCREMENT,
            invoice_number varchar(100) UNIQUE NOT NULL,
            request_reference varchar(20) UNIQUE NOT NULL,
            invoice_payer_email varchar(100) NOT NULL,
            invoice_category ENUM('Cooperative', 'Business Premise') NOT NULL,
            invoice_sub_category varchar(255) NOT NULL,
            payment_date date NOT NULL,
            invoice_expires timestamp NOT NULL,
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
            if ($field->Type == "bigint(20)") {
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
            if ($field->Type != "mediumtext") {
                $my_fields[$field->Field] = 0;
            } else {
                $my_fields[$field->Field] = "";
            }
        }
        return $my_fields;
    }

    /**
     * Retrieve a row by a specific column / value
     *
     * @access public
     * @since  2.1
     * @return object
     */
    public function get_row_by_data( $invoice_number, $reqeust_reference )
    {
        global $wpdb;
        $invoice_number = esc_sql($invoice_number);
        $reqeust_reference = esc_sql($reqeust_reference);
        $column_invoice = "invoice_number";
        $column_ref = "request_reference";
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $this->table_name WHERE $column_invoice = %s AND $column_ref = %s LIMIT 1;",
                array($invoice_number, $reqeust_reference)
            )
        );
    }
}
