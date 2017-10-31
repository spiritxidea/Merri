<?php
namespace WPO\WC\PDF_Invoices\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( !class_exists( '\\WPO\\WC\\PDF_Invoices\\Documents\\Sequential_Number_Store' ) ) :

/**
 * Class handling database interaction for sequential numbers
 * 
 * @class       \WPO\WC\PDF_Invoices\Documents\Sequential_Number_Store
 * @version     2.0
 * @category    Class
 * @author      Ewout Fernhout
 */

class Sequential_Number_Store {
	/**
	 * Name of the table that stores the number sequence (without the wp_wcpdf_ table prefix)
	 * @var String
	 */
	public $table_name;

	public function __construct( $table_name ) {
		global $wpdb;
		$this->table_name = "{$wpdb->prefix}wcpdf_{$table_name}"; // i.e. wp_wcpdf_invoice_number

		$this->init();
	}

	public function init() {
		global $wpdb;
		// check if table exists
		if( $wpdb->get_var("SHOW TABLES LIKE '{$this->table_name}'") == $this->table_name) {
			return; // no further business
		}

		// create table (in case of concurrent requests, this does no harm if it already exists)
		$charset_collate = $wpdb->get_charset_collate();
		// dbDelta is a sensitive kid, so we omit indentation
$sql = "CREATE TABLE {$this->table_name} (
  id int(16) NOT NULL AUTO_INCREMENT,
  order_id int(16),
  date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  PRIMARY KEY  (id)
) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$result = dbDelta( $sql );

		return $result;
	}

	/**
	 * Consume/create the next number and return it
	 * @param  integer $order_id WooCommerce Order ID
	 * @param  string  $date     Local date, formatted as Y-m-d H:i:s
	 * @return int               Number that was consumed/created
	 */
	public function increment( $order_id = 0, $date = null ) {
		global $wpdb;
		if ( empty( $date ) ) {
			$date = get_date_from_gmt( date( 'Y-m-d H:i:s' ) );
		}

		$data = array(
			'order_id'	=> (int) $order_id,
			'date'		=> $date,
		);
		$wpdb->insert( $this->table_name, $data );
		
		// return generated number
		return $wpdb->insert_id;
	}

	/**
	 * Get the number that will be used on the next increment
	 * @return int next number
	 */
	public function get_next() {
		global $wpdb;
		$table_status = $wpdb->get_row("SHOW TABLE STATUS LIKE '{$this->table_name}'");

		// return next auto_increment value
		return $table_status->Auto_increment;
	}

	/**
	 * Set the number that will be used on the next increment
	 */
	public function set_next( $number = 1 ) {
		global $wpdb;

		// delete all rows
		$delete = $wpdb->query("TRUNCATE TABLE {$this->table_name}");

		// set auto_increment
		$wpdb->query("ALTER TABLE {$this->table_name} AUTO_INCREMENT={$number};");
	}

	public function get_last_date( $format = 'Y-m-d H:i:s' ) {
		global $wpdb;
		$row = $wpdb->get_row( "SELECT * FROM {$this->table_name} WHERE id = ( SELECT MAX(id) from {$this->table_name} )" );
		$date = isset( $row->date ) ? $row->date : 'now';
		$formatted_date = date( $format, strtotime( $date ) );

		return $formatted_date;
	}
}

endif; // class_exists
