<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://www.bellflowermedia.com/
 * @since      1.0.0
 *
 * @package    Bfm_Leads
 * @subpackage Bfm_Leads/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Bfm_Leads
 * @subpackage Bfm_Leads/public
 * @author     Enrique Chavez <noone@tmeister.net>
 */
class Bfm_Leads_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $bfm_leads    The ID of this plugin.
	 */
	private $bfm_leads;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Database Class
	 *
	 */
	private $db;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $bfm_leads       The name of the plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $bfm_leads, $version ) {

		$this->bfm_leads = $bfm_leads;

		$this->version   = $version;

		$this->db        = new Bfm_Leads_Db();

	}

	/**
	 * Init Setup
	 *
	 * @since    1.0.0
	 */
	public function init(){

		add_shortcode('bfm-form', array( $this, 'parse_shortcode' ) );

		add_filter('widget_text', 'do_shortcode');

	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( 'select2', plugin_dir_url( __FILE__ ) . 'css/select2.css', array(), '3.5.2', 'all' );

		wp_enqueue_style( $this->bfm_leads, plugin_dir_url( __FILE__ ) . 'css/bfm-leads-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'maskinput', plugin_dir_url( __FILE__ ) . 'js/jquery.maskedinput.min.js', array( 'jquery' ), '1.3.1', false );

		wp_enqueue_script( 'parsley', plugin_dir_url( __FILE__ ) . 'js/parsley.min.js', array( 'jquery' ), '2.0.6', false );

		wp_enqueue_script( 'select2', plugin_dir_url( __FILE__ ) . 'js/select2.js', array( 'jquery' ), '3.5.2', false );

		wp_enqueue_script( $this->bfm_leads, plugin_dir_url( __FILE__ ) . 'js/min/bfm-leads-public-min.js', array( 'jquery' ), $this->version, false );

		$data = array(

			'ajax_url' => admin_url('admin-ajax.php'),

			'nonce' => wp_create_nonce( "bfmleads" )

		);

		wp_localize_script( $this->bfm_leads, 'bfm', $data );

	}

	public function parse_shortcode($atts){

		$defaults = array(

			'layout' => 'horizontal',

			'steps' => '3'

		);

		$atts = shortcode_atts( $defaults, $atts );

		$layout = $atts['layout'];

		$steps = $atts['steps'];

		$border_color = $this->get_setting('bfm_form_colors', 'bfm_f_border_color');

		$left_inner = $this->get_setting('bfm_form_colors', 'bfm_f_left_inner_bg');

		$right_inner = $this->get_setting('bfm_form_colors', 'bfm_f_right_inner_bg');

		$button_bg = $this->get_setting('bfm_form_colors', 'bfm_f_button_bg');

		$button_color = $this->get_setting('bfm_form_colors', 'bfm_f_button_color');

		$labels_color = $this->get_setting('bfm_form_colors', 'bfm_f_labels_color');

		ob_start();

		if( '3' == $steps ){

			include plugin_dir_path( __FILE__ ) . 'partials/bfm-3-steps-form.php';

		} else if( '4' == $steps ){

			include plugin_dir_path( __FILE__ ) . 'partials/bfm-4-steps-form.php';

		}

		return ob_get_clean();

	}


	public function add_email(){

		global $wpdb;

		check_ajax_referer('bfmleads', 'nonce');

		$email = sanitize_email( $_POST['email'] );

		$leads_table = $this->db->get_table_prefix() . 'leads';

		/**
		 * Check if already exist in the DB
		 */

		$row = $wpdb->get_row( $wpdb->prepare(

			'SELECT id FROM ' . $leads_table . ' WHERE email = %s',

			$email

		) );

		if( $row ){

			echo json_encode(array(

				'status' => 'fail',

				'message' => 'Email already exist in our records.'

			));

			die();
		}

		/**
		 * OK, Insert the Email.
		 */

		$insert = $wpdb->insert( $leads_table, array( 'email' => $email, 'form_id' => 1, 'input_time' => date("Y-m-d H:i:s") ) );

		if( $insert ){

			$this->send_data_to_mailing_provider( array('email' => $email) );

			$out = array(

				'status' => 'success',

				'user_id' => $wpdb->insert_id

			);

			$this->add_form_conversion( 1 );

		} else {

			$out = array(

				'status' => 'fail',

				'message' => 'Error inserting email'

			);

		}

		echo json_encode( $out );

		die();

	}

	public function add_name_phone_best(){

		check_ajax_referer('bfmleads', 'nonce');

		$name = sanitize_text_field( $_POST['name'] );

		$phone = sanitize_text_field( $_POST['phone'] );

		$best_time = intval( $_POST['best_time'] );

		$user_id = intval( $_POST['user_id'] );

		$names = explode(' ', $name);

		$firstname = ( isset( $names[0] ) ) ? $names[0] : '';

		$lastname = ( isset( $names[1] ) ) ? $names[1] : '';

		$data = array(

			'first_name' => $firstname,

			'last_name' => $lastname,

			'best_time_to_call' => $best_time,

			'phone' => $phone

		);

		$where = array( 'id' => $user_id );

		$this->db->update_ajax_data( 'leads', $data, $where );

	}

	public function add_status_rent_manager(){

		check_ajax_referer('bfmleads', 'nonce');

		$status = intval( $_POST['status'] );

		$rent = floatval( $_POST['rent'] );

		$user_id = intval( $_POST['user_id'] );

		$manager = sanitize_text_field( $_POST['manager'] );

		$manager = ( $manager === 'yes' ) ? 1 : 0;

		$data = array(

			'property_status' => $status,

			'rent_price' => $rent,

			'property_manager' => $manager

		);

		$where =array( 'id' => $user_id );

		$this->db->update_ajax_data( 'leads', $data, $where );

	}

	public function add_comments(){


		check_ajax_referer('bfmleads', 'nonce');

		$user_id = intval( $_POST['user_id'] );

		$comments = esc_textarea( $_POST['comments'] );

		$data = array(

			'comments' => $comments,

			'complete' => 1

		);

		$where = array( 'id' => $user_id );

		$thanks_url = $this->get_setting('bfm_global_settings', 'bfm_thankyou_page');

		$this->db->update_ajax_data( 'leads', $data, $where, $thanks_url );


	}

	public function add_email_and_name(){

		global $wpdb;

		check_ajax_referer('bfmleads', 'nonce');

		$email = sanitize_email( $_POST['email'] );

		$name = sanitize_text_field( $_POST['name'] );

		$names = explode(' ', $name);

		$firstname = ( isset( $names[0] ) ) ? $names[0] : '';

		$lastname = ( isset( $names[1] ) ) ? $names[1] : '';

		$leads_table = $this->db->get_table_prefix() . 'leads';

		/**
		 * Check if already exist in the DB
		 */

		$row = $wpdb->get_row( $wpdb->prepare(

			'SELECT id FROM ' . $leads_table . ' WHERE email = %s',

			$email

		) );

		if( $row ){

			echo json_encode(array(

				'status' => 'fail',

				'message' => 'Email already exist in our records.'

			));

			die();
		}

		$insert = $wpdb->insert(

			$leads_table,

			array(

				'email' => $email,

				'first_name' => $firstname,

				'last_name' => $lastname,

				'form_id' => 2,

				'input_time' => date("Y-m-d H:i:s"),
			)
		);

		if( $insert ){

			$this->send_data_to_mailing_provider( array('email' => $email, 'name' => $name) );

			$out = array(

				'status' => 'success',

				'user_id' => $wpdb->insert_id

			);

			$this->add_form_conversion( 2 );

		} else {

			$out = array(

				'status' => 'fail',

				'message' => 'Error inserting email'

			);

		}

		echo json_encode( $out );

		die();

	}

	public function add_phone_best(){

		check_ajax_referer('bfmleads', 'nonce');

		$user_id = intval( $_POST['user_id'] );

		$phone = sanitize_text_field( $_POST['phone'] );

		$best_time = intval( $_POST['best_time'] );

		$data = array(

			'phone' => $phone,

			'best_time_to_call' => $best_time

		);

		$where = array( 'id' => $user_id );

		$this->db->update_ajax_data( 'leads', $data, $where );

	}

	public function add_aditional_fields(){

		check_ajax_referer('bfmleads', 'nonce');

		$user_id = intval( $_POST['user_id'] );

		$status = intval( $_POST['status'] );

		$rent = floatval( $_POST['rent'] );

		$user_id = intval( $_POST['user_id'] );

		$manager = sanitize_text_field( $_POST['manager'] );

		$manager = ( $manager === 'yes' ) ? 1 : 0;

		$comments = esc_textarea( $_POST['comments'] );

		$data = array(

			'property_status' => $status,

			'rent_price' => $rent,

			'property_manager' => $manager,

			'comments' => $comments,

			'complete' => 1,

		);

		$where = array( 'id' => $user_id );

		$thanks_url = $this->get_setting('bfm_global_settings', 'bfm_thankyou_page');

		$this->db->update_ajax_data( 'leads', $data, $where, $thanks_url );

	}

	public function add_form_hit(){

		global $wpdb;

		check_ajax_referer('bfmleads', 'nonce');

		$logs_table = $this->db->get_table_prefix() . 'logs';

		$form_id = intval( $_POST['form_id'] );

		$data = array(

			'time' => date("Y-m-d H:i:s"),

			'data_type' => 'impression',

			'form_id' => $form_id,

			'user_agent' => $_SERVER['HTTP_USER_AGENT'],

			'referer' => esc_url( $_SERVER['HTTP_REFERER'] ),

			'ip_address' => $this->get_ip_address()
		);

		$insert = $wpdb->insert( $logs_table, $data );

		if( $insert ){

			$out = array( 'status' => 'success');

		} else {

			$out = array( 'status' => 'fail', 'message' => 'Error inserting impression' );

		}

		echo json_encode( $out );

		die();

	}

	public function add_form_conversion($form_id){

		global $wpdb;

		$logs_table = $this->db->get_table_prefix() . 'logs';

		$data = array(

			'time' => date("Y-m-d H:i:s"),

			'data_type' => 'conversion',

			'form_id' => $form_id,

			'user_agent' => $_SERVER['HTTP_USER_AGENT'],

			'referer' => esc_url( $_SERVER['HTTP_REFERER'] ),

			'ip_address' => $this->get_ip_address()
		);

		$insert = $wpdb->insert( $logs_table, $data );

	}

	public function add_form_to_posts($content){

		global $post;

		$show_form = $this->get_setting('bfm_global_settings', 'bfm_footer_posts');

		if( 'post' == $post->post_type && is_single() && $show_form == 'on'){

			$steps = $this->get_setting('bfm_global_settings', 'bfm_footer_form');

			$steps = ( $steps == '1' ) ? '4' : '3';

			$layout = $this->get_setting('bfm_global_settings', 'bfm_footer_form_layout');

			$shortcode = sprintf( '[bfm-form layout="%s" steps="%s"]', $layout, $steps );

			$content .=  '<hr>' . do_shortcode( $shortcode );

		}

		return $content;
	}

	/**
	 * Get visitor IP address.
	 *
	 * @since  1.0.0
	 * @return string IP address
	 * @see    http://stackoverflow.com/a/15699314
	 */
	public static function get_ip_address() {

		$env = array(
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'REMOTE_ADDR'
		);

		foreach( $env as $key ) {

			if( array_key_exists( $key, $_SERVER ) === true ) {

				foreach( explode( ',', $_SERVER[$key] ) as $ipaddress ) {

                	$ipaddress = trim( $ipaddress ); // Just to be safe

					if( filter_var( $ipaddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false )
						return $ipaddress;
				}
			}
		}

		return 'unknown';

	}

	public function get_setting( $section, $option, $default = '' ) {

	    $options = get_option( $section );


	    if ( isset( $options[$option] ) ) {

	        return $options[$option];

	    }

	    return $default;
	}

	private function send_data_to_mailing_provider($data){

		$provider = $this->get_setting('bfm_global_settings', 'bfm_mailing_provider', false);

		$api_key = $this->get_setting('bfm_get_response', 'bfm_api_key', false);

		$campaign_id = $this->get_setting('bfm_get_response', 'bfm_campaigns_list', false);

		if( !$api_key || $provider == 'none' ){return;}

		switch ($provider) {

			case 'get_response':

				$get_response = new Get_Response_Proxy( $api_key );

				if( isset( $data['name'] ) ){

					$contact = array(

						'campaign' => $campaign_id,

						'name' => $data['name'],

						'email' => $data['email']

					);

				}else{

					$contact = array(

						'campaign' => $campaign_id,

						'email' => $data['email']

					);

				}

				$result = $get_response->add_contact($contact);

				break;
		}

	}

}
