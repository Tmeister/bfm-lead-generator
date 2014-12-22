<?php

/**
 * The dashboard-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Bfm_Leads
 * @subpackage Bfm_Leads/admin
 */

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    Bfm_Leads
 * @subpackage Bfm_Leads/admin
 * @author     Enrique Chavez <noone@tmeister.net>
 */
class Bfm_Leads_Admin {

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

	private $db;

	private $settings_api;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $bfm_leads       The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $bfm_leads, $version ) {

		$this->bfm_leads = $bfm_leads;
		$this->version = $version;
		$this->db = new Bfm_Leads_Db();
		$this->settings_api = new WeDevs_Settings_API();

	}

	public function update_db_check(){

		$installed_ver = get_option( "bfm_leads_db_version" );

		if ( $installed_ver != BMF_LEADS_DB_VERSION ) {
			$this->db->run_db_installer();
		}
	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->bfm_leads, plugin_dir_url( __FILE__ ) . 'css/bfm-leads-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'flot', plugin_dir_url( __FILE__ ) . 'js/jquery.flot.js', array( 'jquery' ), '0.8.3' );

		wp_enqueue_script( 'flot-time', plugin_dir_url( __FILE__ ) . 'js/jquery.flot.time.js', array( 'jquery' ), '0.8.3' );

		wp_enqueue_script( 'flot-tooltip', plugin_dir_url( __FILE__ ) . 'js/jquery.flot.tooltip.min.js', array( 'jquery' ), '0.8.3' );

		wp_enqueue_script( $this->bfm_leads, plugin_dir_url( __FILE__ ) . 'js/min/bfm-leads-admin-min.js', array( 'jquery' ), $this->version, false );

	}

	public function add_menus(){

		add_menu_page( 'Leads', 'Leads', 'manage_options', 'bfm-leads', array( $this, 'page_main_leads'), '', '66' );

		add_submenu_page( 'bfm-leads', 'Analytics', 'Analytics', 'manage_options', 'bfm-analytics', array($this, 'page_analytics') );

		add_submenu_page( 'bfm-leads', 'Settings', 'Settings', 'manage_options', 'bfm-settings', array($this, 'page_settings') );
	}

	public function page_main_leads(){

		if( isset( $_GET['action'] ) && $_GET['action'] == 'view_profile' ){

			if( isset($_GET['lead'] ) && intval( $_GET['lead'] ) ){

				global $wpdb;

				$sql = sprintf(
					'SELECT * FROM %s WHERE id = %d',
					$this->db->get_table_prefix() . 'leads',
					$_GET['lead']
				);

				$lead = $this->get_lead_human_read( $wpdb->get_row( $sql ) );

				//var_dump($lead);

				include plugin_dir_path( __FILE__ ) . 'partials/bfm-leads-profile.php';

				return;

			}

		}

		include plugin_dir_path( __FILE__ ) . 'class-bfm-leads-table.php';

		include plugin_dir_path( __FILE__ ) . 'partials/bfm-leads-admin-display.php';

	}

	public function page_analytics(){


		$total_impressions = 0;

		$total_conversions = 0;

		$show = isset( $_GET['form'] ) ? $_GET['form'] : 'all';

		$period = isset( $_GET['period'] ) ? $_GET['period'] : 'today';

		$stats_path = plugin_dir_path( __FILE__ ) . 'partials/bfm-leads-stats.php';

		include plugin_dir_path( __FILE__ ) . 'partials/bfm-leads-analytics.php';

	}

	public function page_settings(){

		include plugin_dir_path( __FILE__ ) . 'partials/bfm-leads-settings.php';

	}

	/**
	 * Retrieve data to feed the graph.
	 *
	 * @since  1.0.0
	 * @return string Records encoded in JSON
	 */
	public function get_graph_data() {

		$query     = array( 'data_type' => 'any', 'limit' => -1 );
		$timeframe = unserialize( stripslashes( $_POST['bfm_analytics_time'] ) );
		$form     = isset( $_POST['bfm_analytics_form'] ) ? $_POST['bfm_analytics_form'] : 'all';
		$period    = isset( $_POST['bfm_analytics_period'] ) ? $_POST['bfm_analytics_period'] : 'today';

		/* Set the period */
		$query['period'] = $timeframe;

		/* Select the form */
		if( 'all' != $form ) {
			$query['form_id'] = intval( $form );
		}

		/* Separate impressions and conversions */
		$query_i = $query;
		$query_i['data_type'] = 'impression';

		$query_c = $query;
		$query_c['data_type'] = 'conversion';

		/* Get the datas */
		$impressions = $this->db->get_datas( $query_i, 'OBJECT' );
		$conversions = $this->db->get_datas( $query_c, 'OBJECT' );

		/* Set the scale */
		$scale  = date( 'Y-m-d' );

		switch( $period ):

			case 'today':
				$scale       = 'Y-m-d H:00:00';
				$timeformat  = '%d/%b';
				$minticksize =  array( 1, 'hour' );
				$min         = strtotime( date( 'Y-m-d 00:00:00' ) ) * 1000;
				$max         = strtotime( date( 'Y-m-d 23:59:59' ) ) * 1000;
			break;

			case 'this_week':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%a';
				$minticksize = array( 1, 'day' );
				$min         = strtotime( 'last monday' ) * 1000;
				$max         = strtotime( 'next sunday' ) * 1000;
			break;

			case 'last_week':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%a';
				$minticksize = array( 1, 'day' );
				$min         = strtotime( 'last monday -7 days' ) * 1000;
				$max         = strtotime( 'next sunday -7 days' ) * 1000;
			break;

			case 'this_month':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%a';
				$minticksize = array( 1, 'day' );
				$min         = strtotime( 'first day of this month' ) * 1000;
				$max         = strtotime( 'last day of this month' ) * 1000;
			break;

			case 'last_month':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%a';
				$minticksize = array( 1, 'day' );
				$min         = strtotime( 'first day of last month' ) * 1000;
				$max         = strtotime( 'last day of last month' ) * 1000;
			break;

			case 'this_quarter':

				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%b';
				$minticksize = array( 1, 'month' );
				$quarters    = array( 1, 4, 7, 10 );
				$month       = intval( date( 'm' ) );

				if( in_array( $month, $quarters ) ) {
					$current = date( 'Y-m-d', time() );
				} else {

					/* Get first month of this quarter */
					while( !in_array( $month, $quarters) ) {
						$month = $month-1;
					}

					$current = date( 'Y' ) . '-' . $month . '-' . '01';

				}

				$current = strtotime( $current );
				$min     = strtotime( 'first day of this month', $current ) * 1000;
				$max     = strtotime( 'last day of this month', strtotime( '+2 months', $current ) ) * 1000;

			break;

			case 'last_quarter':

				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%b';
				$minticksize = array( 1, 'month' );
				$quarters    = array( 1, 4, 7, 10 );
				$month       = intval( date( 'm' ) ) - 3;
				$rewind      = false;

				if( in_array( $month, $quarters ) ) {
					$current = date( 'Y-m-d', time() );
				} else {

					/* Get first month of this quarter */
					while( !in_array( $month, $quarters) ) {

						$month = $month-1;

						/* Rewind to last year after we passed January */
						if( 0 === $month )
							$month = 12;
					}

					$current = date( 'Y' ) . '-' . $month . '-' . '01';

				}

				/* Set the theorical current date */
				$current = false === $rewind ? strtotime( $current ) : strtotime( '-1 year', $current );
				$min     = strtotime( 'first day of this month', $current ) * 1000;
				$max     = strtotime( 'last day of this month', strtotime( '+2 months', $current ) ) * 1000;

			break;

			case 'this_year':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%b';
				$minticksize = array( 1, 'month' );
				$min         = strtotime( 'first day of January', time() ) * 1000;
				$max         = strtotime( 'last day of December', time() ) * 1000;
			break;

			case 'last_year':
				$scale       = 'Y-m-d 00:00:00';
				$timeformat  = '%b';
				$minticksize = array( 1, 'month' );
				$min         = strtotime( 'first day of January last year', time() ) * 1000;
				$max         = strtotime( 'last day of December last year', time() ) * 1000;
			break;

		endswitch;

		/* Propare global array */
		$datas = array(
			'impressionsData' => array(
				'label' => __( 'Impressions', 'bfm' ),
				'id'    => 'impressions',
				'data'  => array()
			),
			'conversionsData' => array(
				'label' => __( 'Conversions', 'bfm' ),
				'id'    => 'conversions',
				'data'  => array()
			),
			'scale' => array(
				'minTickSize' => $minticksize,
				'timeformat'  => $timeformat
			),
			'min' => $min,
			'max' => $max
		);

		/* Get the count on the scaled timestamp */
		$imp_array = $this->array_merge_combine( $impressions, $scale );
		$con_array = $this->array_merge_combine( $conversions, $scale );

		/**
		 * Fill the blanks!
		 *
		 * Both impressions and conversions array need to have the same number of entries
		 * (same number of timestamps) for the graph to work properly.
		 *
		 * We alternatively merge the impressions and conversions array. The only added keys
		 * must have a value of 0.
		 */
		$tmp_arr_imp = array_flip( array_keys( $imp_array ) );
		$tmp_arr_con = array_flip( array_keys( $con_array ) );

		/* Set all counts to 0 */
		$tmp_arr_imp = array_map( array( 'Bfm_Leads_Admin', 'return_zero' ), $tmp_arr_imp );
		$tmp_arr_con = array_map( array( 'Bfm_Leads_Admin', 'return_zero' ), $tmp_arr_con );

		/* Add missing values in both impressions and conversions arrays */
		$imp_array = $imp_array + $tmp_arr_con;
		$con_array = $con_array + $tmp_arr_imp;

		/* Convert the arrays to a format that Float can read. */
		$imp_array = $this->float_format( $imp_array );
		$con_array = $this->float_format( $con_array );

		/* Add the hits to datas array */
		$datas['impressionsData']['data'] = $imp_array;
		$datas['conversionsData']['data'] = $con_array;

		/* Return results to script */
		print_r( json_encode( $datas ) );

		die();

	}

	/**
	 * Prepare the hist array.
	 *
	 * The function takes an array of datas and then,
	 * based on the time scale, gets the number of hits
	 * in a specific timeframe (eg. number of hits per hour).
	 *
	 * @since  1.0.0
	 * @param  array  $array  An array of data
	 * @param  string $format A date format (as used in date())
	 * @return array          An array sorted by time and hits in a format compatible with Float for the graph
	 */
	public static function array_merge_combine( $array, $format ) {

		$parsed = array();
		$new    = array();

		/* Count the number of hits per timeframe */
		foreach( $array as $object ) {

			$date = strtotime( date( $format, strtotime( $object->time ) ) );

			if( !in_array( $date, $parsed ) ) {
				array_push( $parsed, $date );
				$new[$date] = 1;
			} else {
				++$new[$date];
			}

		}

		return $new;

	}

	public function float_format( $array ) {

		$new = array();

		/* Reorder the array */
		ksort( $array );

		/** Transform the array in a readable format for Float */
		foreach( $array as $key => $value ) {
			array_push( $new, array( $key * 1000, $value ) ); // Timestamp must be in miliseconds
		}

		return $new;

	}

	/**
	 * Return zero
	 *
	 * The function just returns 0 and is used for array_map.
	 * This function is required for PHP < 5.3 as anonymous functions
	 * are not yet supported.
	 *
	 * @since  1.0.1
	 * @see    Bfm_Leads_Admin::get_graph_data()
	 * @param  mixed   $item Array item to reset
	 * @return integer       Zero
	 */
	public static function return_zero( $item ) {
		return 0;
	}

	private function get_lead_human_read($lead){

		$best_time               = array('NA','8am to 10am','10am to 12pm','12pm to 2pm','2pm to 4pm','4pm to 6pm');

		$property_status         = array( 'NA', 'Vacant', 'Owner-occupied', 'Tenant-occupied' );

		$property_manager        = array('No', 'Yes');

		$lead->best_time_to_call = ($lead->best_time_to_call) ? $best_time[ $lead->best_time_to_call ] : 'NA';

		$lead->property_status   = ($lead->property_status) ? $property_status[ $lead->property_status ] : 'NA';

		$lead->property_manager  = ( $lead->property_manager ) ? $property_manager[ $lead->property_manager ] : 'NA';

		$lead->rent_price        = ($lead->rent_price) ? $lead->rent_price : '0.00';

		$lead->complete          = ( $lead->complete ) ? 'Complete' : 'Incomplete';

		$lead->form_id           = ( $lead->form_id == 1 ) ? '4 Steps Form' : '3 Steps Form';

		$lead->input_time        = get_date_from_gmt( date( 'Y-m-d H:i:s', strtotime( $lead->input_time ) ), 'M j, Y - H:i:s' );

		return $lead;

	}

	public function create_settings(){

		 $sections = array(
            array(
                'id' => 'bfm_global_settings',
                'title' => __( 'Global Settings', 'bfm' )
            ),
            array(
                'id' => 'bfm_form_colors',
                'title' => __( 'Form Colors', 'bfm' )
            ),
            array(
                'id' => 'bfm_get_response',
                'title' => __( 'Get Response', 'bfm' )
            )
        );

		$fields = array(
			'bfm_global_settings' => array(
				 array(
					'name'  => 'bfm_footer_posts',
					'label' => __( 'Show in Post Footer?', 'bfm' ),
					'desc'  => __( 'Check this option to show the form in all the footer posts', 'bfm' ),
					'type'  => 'checkbox'
                ),
				array(
					'name'    => 'bfm_footer_form',
					'label'   => __( 'Footer Form', 'bfm' ),
					'desc'    => __( 'Which form you want to show in the post footer.', 'bfm' ),
					'type'    => 'select',
					'default' => '1',
					'options' => array(
						'1' => '4 Steps Form',
						'2' => '3 Steps Form'
					)
				),
				array(
					'name'    => 'bfm_footer_form_layout',
					'label'   => __( 'Footer Form Layout', 'bfm' ),
					'desc'    => __( 'Which form layout you want to use in the post footer.', 'bfm' ),
					'type'    => 'select',
					'default' => 'horizontal',
					'options' => array(
						'vertical' => 'Vertical',
						'horizontal' => 'Horizontal'
					)
				),
				array(
					'name'    => 'bfm_thankyou_page',
					'label'   => __( 'Thank You Page', 'bfm' ),
					'desc'    => __( 'Where the user will be redirected after form completion? ', 'bfm' ),
					'type'    => 'text',
					'default' => get_option('siteurl')

				),
				array(
					'name'    => 'bfm_mailing_provider',
					'label'   => __( 'E-Mailing Provider', 'bfm' ),
					'desc'    => __( 'Which e-mailing provider do you use?', 'bfm' ),
					'type'    => 'select',
					//TODO GET PROVIDERS LIST....
					'options' => array(
						'none'         => __('None', 'bfm'),
						'get_response' => __('Get Response', 'bfm')
					)
				)
			),
			'bfm_form_colors' => array(
				array(
                    'name' => 'bfm_f_border_color',
                    'label' => __( 'Form Border Color', 'bfm' ),
                    'desc' => __( 'Color to use in the forms border.', 'bfm' ),
                    'type' => 'color',
                    'default' => '#f3f6f7'
                ),
                array(
                    'name' => 'bfm_f_left_inner_bg',
                    'label' => __( 'Fields Panel Background', 'bfm' ),
                    'desc' => __('This panel is where the form fields are shown.', 'bfm'),
                    'type' => 'color',
                    'default' => '#fff'
                ),
                array(
                    'name' => 'bfm_f_right_inner_bg',
                    'label' => __( 'Botton Panel Background', 'bfm' ),
                    'desc' => __('This panel is where the form bottom is shown.', 'bfm'),
                    'type' => 'color',
                    'default' => '#e7ebed'
                ),
                array(
                    'name' => 'bfm_f_button_bg',
                    'label' => __( 'Botton Background', 'bfm' ),
                    'type' => 'color',
                    'default' => '#2ab6d6'
                ),
                array(
                    'name' => 'bfm_f_button_color',
                    'label' => __( 'Botton Text', 'bfm' ),
                    'type' => 'color',
                    'default' => '#fff'
                ),
                array(
                    'name' => 'bfm_f_labels_color',
                    'label' => __( 'Labels Text', 'bfm' ),
                    'type' => 'color',
                    'default' => '#333'
                ),

			)
		);

		//Dynamic Fields...

		$fields['bfm_get_response'] = array();

		$api_key = array(
			'name'    => 'bfm_api_key',
			'label'   => __( 'Get Response API KEY', 'bfm' ),
			'desc'    => __( 'Please get your API Key in <a href="https://app.getresponse.com/account.html#api" target="_blank">THIS LINK</a>.', 'bfm' ),
			'type'    => 'text'
		);

		array_push( $fields['bfm_get_response'], $api_key);

		$api_key = $this->get_setting('bfm_get_response', 'bfm_api_key', false);

		if( $api_key ){

			$get_response = new Get_Response_Proxy( $api_key );

			$campaigns = $get_response->get_campaigns();

			if( $campaigns ){

				//var_dump($campaigns);

				$campaigns_list = array(
					'name'    => 'bfm_campaigns_list',
					'label'   => __( 'Campaign', 'bfm' ),
					'desc'    => __( 'Please select which campaign to use to add your leads.', 'bfm' ),
					'type'    => 'select',
					'options' => $this->parse_get_response_campaigns($campaigns)
				);

				array_push( $fields['bfm_get_response'], $campaigns_list);

			}

		}


        $this->settings_api->set_sections( $sections );

        $this->settings_api->set_fields( $fields );

        $this->settings_api->admin_init();

	}

	private function parse_get_response_campaigns($campaigns){

		$out = array();

		foreach ($campaigns as $key => $campaign) {

			$out[$key] = $campaign['name'];

		}

		return $out;

	}

	public function get_setting( $section, $option, $default = '' ) {

	    $options = get_option( $section );


	    if ( isset( $options[$option] ) ) {

	        return $options[$option];

	    }

	    return $default;
	}

}
