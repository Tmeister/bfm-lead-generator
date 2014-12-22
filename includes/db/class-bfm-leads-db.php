<?php
/**
 * Fired during plugin activation.
 *
 * @since      1.0.0
 * @package    Bfm_Leads
 * @subpackage Bfm_Leads/includes/db
 * @author     Enrique Chavez <noone@tmeister.net>
 */
class Bfm_Leads_Db{

	private $prefix = '';

	private $bfm_leads_db_version = '';

	private $charset_collate = '';

	function __construct(){

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php');

		global $wpdb;

		$this->bfm_leads_db_version = BMF_LEADS_DB_VERSION;

		$this->prefix = $wpdb->prefix . 'bfm_';

	}

	public function run_db_installer(){

		$installed_ver = get_option( "bfm_leads_db_version" );

		if ( $installed_ver != $this->bfm_leads_db_version ) {

			$this->set_charset();
			$this->create_form_table();
			$this->create_bfm_table();
			$this->create_log_table();
			update_option( 'bfm_leads_db_version', $this->bfm_leads_db_version );

		}

	}

	private function set_charset(){

		global $wpdb;

		if ( ! empty( $wpdb->charset ) ) {
		  $this->charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}

		if ( ! empty( $wpdb->collate ) ) {
		  $this->charset_collate .= " COLLATE {$wpdb->collate}";
		}

	}

	private function create_form_table(){

		global $wpdb;

		$sql = "CREATE TABLE %sforms (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					name varchar(20) NOT NULL,
					UNIQUE KEY id (id)
					) %s;";

		$this->do_table($sql);

		$sql = "INSERT INTO " . $this->get_table_prefix() . 'forms (id, name) VALUES ( "", "4 Steps" ), ("", "3 Steps")';

		$wpdb->query( $sql );

	}

	private function create_bfm_table(){

		$sql = "CREATE TABLE %sleads (
					id int    NOT NULL  AUTO_INCREMENT,
					email varchar(255)    NOT NULL,
					first_name varchar(60) NULL ,
					last_name varchar(60)  NULL ,
					best_time_to_call smallint(1) NULL,
					property_status smallint(1) NULL,
					rent_price decimal(10,2) NULL,
					property_manager smallint(1) NULL,
					comments text NULL,
					form_id smallint(1) NULL,
					complete smallint(1) NULL,
					phone varchar(15) NULL,
					input_time datetime NULL,
					PRIMARY KEY  (id),
					KEY id (id)
					) %s;";

		$this->do_table($sql);

	}

	private function create_log_table(){

		$sql = "CREATE TABLE %slogs (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
					data_type VARCHAR(20) COLLATE utf8_general_ci NOT NULL,
					form_id bigint(20) NOT NULL,
					user_agent VARCHAR(128) DEFAULT '' COLLATE utf8_general_ci NOT NULL,
					referer VARCHAR(256) DEFAULT '' COLLATE utf8_general_ci NOT NULL,
					ip_address VARCHAR(128) DEFAULT '0.0.0.0' COLLATE utf8_general_ci NOT NULL,
					UNIQUE KEY id (id)
					) %s;";

		$this->do_table($sql);

	}




	private function do_table($sql){

		$sql = sprintf( $sql, $this->prefix, $this->charset_collate);

		dbDelta( $sql );
	}

	/**
	 * Get a set of datas.
	 *
	 * Retrieve a set of datas based on the user
	 * criterias. This function can return one or
	 * more row(s) of data depending on the arguments;
	 *
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public function get_datas( $args, $output = 'OBJECT' ) {

		global $wpdb;

		$table_name = $this->get_table_prefix() . 'logs';
		$query      = array();

		$defaults = array(
			'data_type'  => 'any',
			'user_agent' => '',
			'referer'    => '',
			'form_id'   => '',
			'date'       => array(),
			'limit'      => 5,
			'period'     => ''
		);

		extract( array_merge( $defaults, $args ) );

		/**
		 * Handle the limit
		 */
		if( -1 === $limit )
			$limit = 1000;

		/**
		 * Handle data type first
		 */
		if( is_array( $data_type ) ) {

			$relation = ( isset( $data_type['relation'] ) && in_array( $data_type['relation'], array( 'IN', 'NOT IN' ) ) ) ? $data_type['relation'] : 'IN';
			$types    = array();

			foreach( $data_type['type'] as $type ) {
				array_push( $types, "'$type'" );
			}

			$types = implode( ',', $types );
			array_push( $query, "data_type $relation ($types)" );

		} elseif( '' != $data_type ) {
			if( 'any' == $data_type ) {
				array_push( $query, "data_type IN ( 'impression', 'conversion' )" );
			} else {
				array_push( $query, "data_type = '$data_type'" );
			}
		}

		/**
		 * Handle the form_id
		 *
		 * @todo test
		 */
		if( is_array( $form_id ) ) {

			$relation = ( isset( $form_id['relation'] ) && in_array( $form_id['relation'], array( 'IN', 'NOT IN' ) ) ) ? $form_id['relation'] : 'IN';
			$forms    = array();

			foreach( $form_id['ids'] as $form ) {
				array_push( $forms, "$form" );
			}

			$forms = implode( ',', $forms );
			array_push( $query, "form_id $relation ($forms)" );

		} elseif( '' != $form_id ) {
			array_push( $query, "form_id = $form_id" );
		}

		/**
		 * Handle the period.
		 */
		if( '' != $period ) {

			if( is_array( $period ) ) {

				$start = isset( $period['from'] ) ? date( "Y-m-d", $period['from'] ) : date( "Y-m-d", time() );
				$end   = isset( $period['to'] ) ? date( "Y-m-d", $period['to'] ) : date( "Y-m-d", time() );

				$start = ( true === $this->check_date( $start ) ) ? $start . ' 00:00:00' : date( "Y-m-d", time() ) . ' 00:00:00';
				$end   = ( true === $this->check_date( $end ) ) ? $end . ' 23:59:59' : date( "Y-m-d", time() ) . ' 23:59:59';

				array_push( $query, "time BETWEEN '$start' AND '$end'" );

			} else {

				/* Get datetime format */
				$date  = date( "Y-m-d", $period );
				$start = "$date 00:00:00";
				$end   = "$date 23:59:59";

				array_push( $query, "time BETWEEN '$start' AND '$end'" );

			}

		}

		/* Merge the query */
		$query = implode( ' AND ', $query );

		$sql = "SELECT * FROM $table_name WHERE $query LIMIT $limit";

		$rows = $wpdb->get_results( $sql , $output );

		return $rows;

	}

	public function get_impressions($form_id = '', $period = ''){

		$data = array(
			'data_type' => 'impression',
			'form_id'   => $form_id,
			'period'    => $period,
			'limit'     => -1
		);

		return $this->get_datas( $data );

	}

	public function get_conversions($form_id = '', $period = ''){

		$data = array(
			'data_type' => 'conversion',
			'form_id'   => $form_id,
			'period'    => $period,
			'limit'     => -1
		);

		return $this->get_datas( $data );

	}

	/**
	 * Check Gregorian date.
	 *
	 * @param  string $time  Date to check
	 * @return boolean       True if date is valid
	 * @see /wp-includes/post.php
	 */
	public function check_date( $time ) {

		/**
		 * Validate the date
		 *
		 * @see /wp-includes/post.php
		 */
		$mm         = substr( $time, 5, 2 );
		$jj         = substr( $time, 8, 2 );
		$aa         = substr( $time, 0, 4 );
		$valid_date = wp_checkdate( $mm, $jj, $aa, $time );

		return $valid_date;

	}

	public function get_table_prefix(){
		return $this->prefix;
	}

	public function update_ajax_data( $table, $data, $where ){

		global $wpdb;

		$table = $this->get_table_prefix() . $table;

		$update = $wpdb->update( $table, $data, $where );

		if( false === $update ){

			echo json_encode(array(

				'status' => 'fail',

				'message' => 'Can\'t Update the record.'

			));

			die();

		}

		echo json_encode(array(

			'status' => 'success'

		));

		die();

	}

	public function get_forms_summary($period = array()){

		global $wpdb;

		$sql = 'SELECT * FROM ' . $this->get_table_prefix() . 'forms';

		$forms = $wpdb->get_results( $sql );

		$forms_array = [];

		foreach ($forms as $form) {

			$impressions =  count( $this->get_impressions($form->id, $period));

			$conversions = count( $this->get_conversions( $form->id, $period ));

			$form_object = new stdClass();

			$form_object->id = $form->id;

			$form_object->name = $form->name;

			$form_object->impressions = $impressions;

			$form_object->conversions = $conversions;

			//$form_object->conversion_rate = number_format( ( $conversions * 100 ) / $impressions, 2 ) ;

			$forms_array[] = $form_object;

		}

		return $forms_array;

	}

}