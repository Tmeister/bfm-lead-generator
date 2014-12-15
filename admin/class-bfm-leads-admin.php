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

	}

	/**
	 * Register the stylesheets for the Dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bfm_Leads_Admin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bfm_Leads_Admin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->bfm_leads, plugin_dir_url( __FILE__ ) . 'css/bfm-leads-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the dashboard.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Bfm_Leads_Admin_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Bfm_Leads_Admin_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->bfm_leads, plugin_dir_url( __FILE__ ) . 'js/bfm-leads-admin.js', array( 'jquery' ), $this->version, false );

	}

}
